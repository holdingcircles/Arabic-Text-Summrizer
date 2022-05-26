<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Arabic Text Summerization</title>

hello !
<br/><br/>

<a href="./?x=add">add document</a>
<br/><br/>
<table cellpadding="5">
<?php
$sql = mysql_query("select docid, doclink from docs order by docid desc");
while($row = mysql_fetch_assoc($sql)){
	echo "<tr><td style='color: #A50010; font-weight: bold;'>$row[doclink]</td>
	<td><a href='./?x=review&id=$row[docid]' style='font-size: 11pt;'>Review</a></td>
	<td><a href='./?x=presum&id=$row[docid]' style='font-size: 11pt;'>Summerize</a></td>
	<td><a href='./?x=del&id=$row[docid]' style='font-size: 11pt;'>Delete</a></td>
	</tr>";
}
?>
</table>