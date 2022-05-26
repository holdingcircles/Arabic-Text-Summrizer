<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Arabic Text Summerization</title>
<?php
$id = intval($_GET['id']);
$sql = mysql_query("select * from docs where docid = '$id'");
$row = mysql_fetch_assoc($sql);

echo " <table cellpadding='8'>
<tr>
	<td>link :</td>
	<td>$row[doclink]</td>
</tr>
<tr>
	<td>Statistics :</td>
	<td>";
	if ($row['status'] == 1){
		echo "Not summarized yet 
		<form action='./?x=pro&id=$row[docid]' method='post'>
		<select name='rate'>
		<option value='0'>النسبة</option>
		<option value='10'>10%</option>
		<option value='20'>20%</option>
		<option value='30'>30%</option>
		<option value='40'>40%</option>
		<option value='50'>50%</option>
		<option value='60'>60%</option>
		<option value='70'>70%</option>
		<option value='80'>80%</option>
		<option value='90'>90%</option>
		<option value='100'>100%</option>
	</select>
	 or
	 <input type='text' name='number' size='2' />
	 <input type='submit' name='go' value='Summaerize' />
	 </form>";
	}
	if ($row['status'] == 2){
		$sq = mysql_query("select * from stats where docid = '$row[docid]'");
		$s = mysql_fetch_assoc($sq);
		
		echo "
		No. of words: $s[words]; No. Sentences: $s[sents];
		";
	}
echo "</td></tr>
<tr>
	<td>Document before summerization :</td>
	<td><textarea cols='100' rows='40' dir='rtl' name='doc'>$row[document]</textarea></td>
</tr>
</table>";
	