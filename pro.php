<html dir="rtl">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Arabic Text Summerization</title>

<?php
set_time_limit(0);

class Summerize{
	var $docid;
	var $docsno;
	var $doc;
	var $para = array();
	var $sentc = array();
	var $words = array();
	var $wordRanks = array();
	var $rankedSent = array();
	var $dranks = 0;
	var $prevSent = 0;
	
	var $rate;
	var $number;
	
	
	function Summerize(){
		$this->rate = ($_REQUEST['rate'])?$_REQUEST['rate']:0;
		$this->number = ($_REQUEST['number'])?$_REQUEST['number']:0;
	
		//getting data i work on
		$this->docid = intval($_GET['id']);
		
		$sql = mysql_query("select * from docs where docid = '$this->docid'");
		$row = mysql_fetch_assoc($sql);
		$this->doc = $row['document'];
		$this->prevWords = $this->count_words($this->doc);
		
		$all = mysql_query("select count(docid) AS N from docs");
		$c = mysql_fetch_assoc($all);
		$this->docsno = $c['N'];
		
		$this->display = "";
		
		
		// start operating - spiltting and cleaning
		$this->_splitPara();
		$this->_splitSentc();
		$this->_splitWords();
		
		// start ranking - ranking calcluates ranks ( no. occurences )
		$this->rankWords();
		
		// create the TF-IDF
		$this->gettfidf();
		
		// rank senteces
		$this->rankSentc();
		
		// print sentences
		$this->summ();
		
		//output
		$this->_echo();
		
		
		//echo "done .. <a href='./?x=home'>back</a>";
		
		
	}
	
	// splitting paragraphs
	function _splitPara(){
		$this->para = explode("." , $this->doc);
	}
	
	// splitting sentences, output : array[0] => ( array[0] = sentence , array[1] = sentence , array[2] ) , array[1] => ( array[0], array[1] ) .. etc
	function _splitSentc(){
		$sentc = array();
		$n = count($this->para);
		for($i=0; $i<$n; $i++){
			if (! empty($this->para[$i])){
				$sentc[$i] = array();
				$sentc[$i] = preg_split("/،|:|>|<|\|/", $this->para[$i]);
				$x = count($sentc[$i]);
				for($j=0; $j<$x; $j++){
					$this->prevSent++;
					$sentc[$i][$j] = str_replace(array("&", "#"), " ", $sentc[$i][$j]);
					$sentc[$i][$j] = trim($sentc[$i][$j]);
					if (strlen($sentc[$i][$j]) > 10){
						if (! empty($sentc[$i][$j])) array_push($this->sentc, $sentc[$i][$j]);
					}
				}
			}
		}
		unset($sentc);
	}
	
	// splitting words, output : array[0] => ( array[0] =>  ( array[0] = word, array[1] = word ), array[1] => ( array[0], array[1]) ), array[1] => ... etc
	function _splitWords(){
		$words = array();

		$x = count($this->sentc);
		for($j=0; $j<$x; $j++){
		if ($this->sentc[$j]){
				$words[$j] = array();
				$words[$j] = explode(" ", $this->sentc[$j]);
				$b = count($words[$j]);
				$this->words[$j] = array();
				for($z=0; $z<$b; $z++){
					$words[$j][$z] = trim($words[$j][$z]);
					
					if (! empty($words[$j][$z])){
						array_push($this->words[$j], $words[$j][$z]);
					}
						
				}
			}
		}
		unset($words);
	}
	
	function rankWords(){
		$x = count($this->sentc);
		for($j=0; $j<$x; $j++){
			$c = count($this->words[$j]);
			for($z=0; $z<$c; $z++){
				@preg_match_all("/".$this->words[$j][$z]."/", $this->sentc[$j], $s);
				@preg_match_all("/".$this->words[$j][$z]."/", $this->doc, $d);
				
				$this->wordRanks[$j][$z]['word'] = $this->words[$j][$z];
				$this->wordRanks[$j][$z]['srank'] = count($s[0]);
				$this->wordRanks[$j][$z]['drank'] = count($d[0]);
				$this->wordRanks[$j][$z]['sentcid'] = $j;
				
				$this->insert2db($this->wordRanks[$j][$z]);
			}
		}
	}
	
	//insert one word into db
	function insert2db($w){
		$word = $w['word'];
		if (!empty($word)){
			if($word != " "){
				if ($word != "."){
					// check it's not exist for the same document
					$ck = mysql_query("select count(docid) AS N from words where word = '$word' AND docid = '$this->docid'");
					$check = mysql_fetch_assoc($ck);
					if ($check['N'] == 0){
						mysql_query("insert into words set word = '$word', docid = '$this->docid', sentc = '$w[sentcid]', srank = '$w[srank]', drank = '$w[drank]'");
					}
				}
			}
		}				
	}
	
	
	function gettfidf(){
		$sql = mysql_query("select sum(drank) AS S from words where docid = '$this->docid'");
		$s = mysql_fetch_assoc($sql);
		$sum = $s['S'];
		
		$sql2 = mysql_query("select * from words where docid = '$this->docid'");
		while($row = mysql_fetch_assoc($sql2)){
			$tf = $row['drank'] / $sum;
			if (strpos($tf, "E")){
				$tf = substr($tf,0,-3);
			}
			
			$word = $row['word'];
			$sql3 = mysql_query("select count(docid) AS N from words where word = '$word'");
			$g = mysql_fetch_assoc($sql3);
			$noc = $g['N'];
			
			
			$idf = log($this->docsno/ $noc+1 );
			if (strpos($idf, "E")){
				$idf = substr($idf,0,-3);
			}
			$tfidf = $idf * $tf;
			
			
			mysql_query("update words set tf = '$tf', idf = '$idf', tfidf = '$tfidf' where word = '$word' AND docid = '$this->docid'");
			
			
		}
	}
	
	function rankSentc(){
		$x = count($this->sentc);
		for($j=0; $j<$x; $j++){
			$sql = @mysql_query("select sum(tfidf) AS SUM from words where sentc = '$j'");
			$get = mysql_fetch_assoc($sql);
			$sum = $get['SUM'];
			
			$sql2 = @mysql_query("select count(docid) AS N from words where sentc = '$j'");
			$get2 = mysql_fetch_assoc($sql2);
			$count = $get2['N'];
			if ($count){
				$sentRank = @$sum / $count;
				$sentRank *= 100;
				
				$this->rankedSent[$j] = $sentRank;
			}
		}
	}
	
	function summ(){
		rsort($this->rankedSent);
		$n = count($this->rankedSent);
		if ($this->rate) $no = intval($n * $this->rate / 100);
		$this->raw = null;
		$this->afterWords = 0;
		for($i=0; $i<$n; $i++){
			if ($this->rate){
				$this->sentc[$i] = $this->doSpaces($this->sentc[$i]);
				$this->afterWords += $this->count_words($this->sentc[$i]);
				
				if ($no == $i) break;
				$this->raw .= " ".$this->sentc[$i];
				$this->display .= "<tr><td bgcolor='#F1F1F1' style='font-size: 15px; font-family: arial; font-weight: bold;'>".$this->sentc[$i]."</td></tr>";
			}else{
				$this->sentc[$i] = $this->doSpaces($this->sentc[$i]);
				$this->afterWords += $this->count_words($this->sentc[$i]);
				
				$this->raw .= " ".$this->sentc[$i];
				if ($this->number == $i) break;
				$coom = $this->count_words($this->sentc[$i]);
				$this->display .= "<tr><td bgcolor='#F1F1F1' style='font-size: 15px; font-family: arial; font-weight: bold;'>".$this->sentc[$i]."</td></tr>";
			}
		}
		$this->sts = array('nsent'=>$no);
		
		$this->check_stats();
		mysql_query("update docs set status = 2 where docid = '$this->docid'");
	}
	
	function count_words($doc){
		$arr = explode(" ", $doc);
		return count($arr);
	}
	
	function check_stats(){
		$sql = mysql_query("select count(docid) AS N from stats where docid = '$this->docid'");
		$row = mysql_fetch_assoc($sql);

		
		
		if ($row['N'] > 0){
			mysql_query("update stats set words = '$this->prevWords', sents = '$this->prevSent' where docid = '$this->docid'");
		}else{
			mysql_query("insert into stats set words = '$this->prevWords', sents = '$this->prevSent', docid = '$this->docid'");
			}
	}
	
	function doSpaces($str){
		while(strstr($str, "  "))
			$str = str_replace("  ", " ", $str);
		return $str;
	}
	
	function _echo(){
		echo "
		<b>عدد الكلمات قبل التلخيص : {$this->prevWords} <br/>
		عدد الكلمات بعد التلخيص : {$this->afterWords} </b><br/><br/>
		نسبة التلخيص : {$this->rate}% <br/>
		عدد الجمل بعد التلخيص : {$this->sts[nsent]} <br/>
		المقال بعد التلخيص : ";
		echo "<table cellpadding='5'>";
		echo $this->display;
		echo "</table>";
		echo "<br/><br/><a href='./?x=home'>العودة</a>";
	}
}

$obj = new Summerize;