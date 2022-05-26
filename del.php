<html dir="rtl">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Arabic Text Summerization</title>
<?php
$id = intval($_GET['id']);
mysql_query("delete from words where docid = '$id'");
mysql_query("delete from docs where docid = '$id'");

echo "<center>تم حذف المقال بنجاح</center>";
header("location: ./?action=home");
