<?php
$server = "localhost";
$database_user = "root";
$database_password = "";
$database_name = "summ";

$link = mysql_pconnect($server, $database_user, $database_passwrod);
mysql_select_db($database_name, $link);
mysql_query("SET NAMES 'UTF8'");