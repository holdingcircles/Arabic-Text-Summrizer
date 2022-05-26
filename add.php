<html dir="rtl">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Arabic Text Summerization</title>
<?php
if ($_POST){
	@ini_set('zend.ze1_compatibility_mode', '1');
	
	ini_set ('display_errors', 1);
	
	$rate = $_POST['rate'];
	$number = $_POST['number'];
	
	$file_name = $_POST['name'];
	
	$str = file_get_contents($file_name, "r");
	
	$new_str        = edit($str);
	$normalizedStr1 = doNormalize($new_str);
	$cleanedStr1    = cleanCommon($normalizedStr1);
	$cleanedStr1 = trim($cleanedStr1);
	
	mysql_query("insert into docs set document = '$cleanedStr1',  doclink = '$file_name', status = 1");
	$id = mysql_insert_id();
	header("location: ./?x=pro&id=$id&rate=$rate&number=$number");
}else{
	echo "
	<script type='text/javascript'>
	function egCheck(){
		if (document.getElementById('name').value == ''){
			alert('برجاء ادخال الرابط');
			return false;
		}
		if (document.getElementById('rate').value == 0 && document.getElementById('number').value == ''){
			alert('برجاء اختيار النسبة او تحديد عدد الجمل');
			return false;
		}
		
		
	}
	</script>
	";
	echo "<br/><center><form method='post' onSubmit='return egCheck();'>
	رابط الموقع <input type='text' name='name' id='name' size='50' dir='ltr' />
	<select name='rate' id='rate'>
		<option value='0'>اختر نسبة</option>
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
	&nbsp; او عدد الجمل المرادة : 
	<input type='text' name='number' size='2' />

		
	&nbsp;<input type='submit' name='submit' value='تلخيص' /></form>";
}
 

function edit($document){
  $search = array(
                 '@<![\s\S]*?--[ \t\n\r]*>@',
                 '@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                 '@<style[^>]*?>.*?</style>@siU'  ,  // Strip style tags properly
                 '@<noscript[^>]*?>.*?</noscript>@si',
                 '@<[\/\!]*?[^<>]*?>@si',
                 '@<a[^>]*?>.*?</a>@si',
                 '@<img[^>]*?>.*?@si',
                 '@<option[^>]*?>.*?</option>@si',
                 '@<select[^>]*?>.*?</select>@si',
                 '@<font[^>]*?>.*?</font>@si',
                 '@<form[^>]*?>.*?</form>@si'
                );




  preg_match_all( '@<font[^>]*?>.*?</font>@si' ,$document, $matches1);
  $document=  delete_unwanted($document, $matches1[0] );

  preg_match_all( '@<a[^>]*?>.*?</a>@si' ,$document, $matches2);
  $document=  delete_unwanted($document, $matches2[0] );

  preg_match_all( '@<img[^>]*?>.*?@si',$document, $matches3);
  $document=  delete_unwanted($document, $matches3[0] );

  preg_match_all( '@<select[^>]*?>.*?</select>@si',$document, $matches4);
  $document=  delete_unwanted($document, $matches4[0] );

  preg_match_all( '/<title>(.*)<\/title>/imsU',$document, $matches4);
  $document=  delete_unwanted($document, $matches4[0] );
  


  $text = preg_replace($search, ' ', $document);
  $text = preg_replace('/&nbsp;/', ' ', $text);
  $text = preg_replace('/&quot;/', ' ', $text);

  
  return $text;
}


function doNormalize($str){
  $patterns     = array();
  $replacements = array();

  array_push($patterns, '/أ|اً|اٌ|اٍ|إ|آ/');
  array_push($patterns, '/ة/');

  array_push($replacements, 'ا');
  array_push($replacements, 'ه');

  $str = preg_replace($patterns, $replacements, $str);

  return $str;
}

function cleanCommon($str){
  $str = preg_replace('/|،|1|2|3|4|5|6|7|8|9|0|;|,|-|_|ـ|/', '', $str);
  $patterns = array('/\s+\w{1,2}(\s+)/');

  $words    = file('stopUTF.txt');
  $max      = count($words);
  
  for($i=0; $i<$max; $i++)
    $words[$i] = trim($words[$i]);

  $str    = preg_replace('/\s(' . implode('|', $words) . ')(\s+)/', '\\2', $str, -1);
  
  return $str;
}

function delete_unwanted($str, $unwanted_sen ){
  $temp_arr;
  $temp_str;


  foreach($unwanted_sen as $key=>$val)
  {
    $split_val=str_split($val);
    $count=count($split_val);

    if($count>=15)
    {
      for($i=0; $i<15; $i++)
       $temp_arr[$i]= $split_val[$i];

      $temp_str= implode('',$temp_arr);
      $start_pos= strpos($str,$temp_str);

      $j=0;
      unset($temp_arr);

      $from= $count-15 ;
      for($i=$from; $i<$count; $i++)
     {
      $temp_arr[$j]= $split_val[$i];
       ++$j;
     }

     $temp_str= implode('',$temp_arr);

     $last_pos= strpos($str,$temp_str);
     $last_pos+=15;

     $new= $last_pos-$start_pos;
     $str=str_split($str);

     for($i=0; $i<$new;$i++)
    {
     unset($str[$start_pos]);
     ++$start_pos;
    }
    $str = implode('',$str);

   }
   else
   {
     $pos= strpos($str,$val);
     $str=str_split($str);

     for($i=0; $i<$count;$i++)
     {
      unset($str[$pos]);
      ++$pos;
     }
     $str = implode('',$str);
   }
 }

 return $str;
}
?>
