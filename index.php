<?php
include('conf/config.php');
switch($_GET['x']){
	case "add":
	include('add.php');
	break;
	
	case "review":
	include('review.php');
	break;
	
	case "pro":
	include('pro.php');
	break;
	
	case "presum":
	include('presum.php');
	break;
	
	case "del":
	include('del.php');
	break;
	
	default:
	include('home.php');
	break;
}
?>