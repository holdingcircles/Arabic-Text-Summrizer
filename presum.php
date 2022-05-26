<html dir="rtl">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Arabic Text Summerization</title>
<script type='text/javascript'>
	function egCheck(){
		if (document.getElementById('rate').value == 0 && document.getElementById('number').value == ''){
			alert('برجاء اختيار النسبة او تحديد عدد الجمل');
			return false;
		}
		
		
	}
	</script>
<?php
$id = intval($_GET['id']);
$sql = mysql_query("select * from docs where docid = '$id'");
$row = mysql_fetch_assoc($sql);

echo " <table cellpadding='8'>
<tr>
	<td width='100%' align='center'>
		
		<form action='./?x=pro&id=$row[docid]' method='post' onSubmit='return egCheck();'>
		اختر نسبة التلخيص
		<select name='rate' id='rate'>
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
	 او عدد الجمل المرادة
	 <input type='text' name='number' id='number' size='2' />
	 <input type='submit' name='go' value='تلخيص المقال' />
	 </form>
	 <br/>
	 <br/>
	 المقال قبل التلخيص :";
echo "</td></tr>
<tr>
	<td><textarea cols='100' rows='40' dir='rtl' name='doc'>$row[document]</textarea></td>
</tr>
</table>";
		