<?php

require_once('config.php');
require_once('functions.php');

if(!defined('LIBRARY_FOLDER_PATH')){
	define('LIBRARY_FOLDER_PATH', 'uploads/');
}

if(!defined('LIBRARY_FOLDER_PATH')){
	$pageURL = 'http';
	if(isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on"){
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if($_SERVER["SERVER_PORT"] != "80"){
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}else{
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	if(preg_match("/(.*)\/set_upload_directory\.php/",$pageURL,$matches)){
		define('LIBRARY_FOLDER_URL', $matches[1] . '/uploads/');
	}
}

$output = array();

$output["success"] = 1;
$output["msg"] = "";

if(isset($_GET["path"]) AND $_GET["path"] != ""){
	$current_folder = urldecode(clean($_GET["path"]));
}else{
	$current_folder = LIBRARY_FOLDER_PATH;
}

if(!startsWith($current_folder, LIBRARY_FOLDER_PATH)){
	$output["success"] = 0;
	$output["msg"] = "You can not upload to this folder";
	header("Content-type: text/plain;");
	echo json_encode($output);
	exit();
}

$_SESSION["tinymce_upload_directory"] = $current_folder;

header("Content-type: text/plain;");
echo json_encode($output);
exit();
