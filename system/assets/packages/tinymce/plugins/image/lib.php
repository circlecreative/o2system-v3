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
	if(preg_match("/(.*)\/lib\.php/",$pageURL,$matches)){
		define('LIBRARY_FOLDER_URL', $matches[1] . '/uploads/');
	}
}

if(isset($_GET["toggle"]) AND $_GET["toggle"] != ""){
	if(isset($_SESSION['tinymce_toggle_view'])){
		if($_SESSION['tinymce_toggle_view'] == 'grid'){
			$_SESSION['tinymce_toggle_view'] = 'list';	
		}else{
			$_SESSION['tinymce_toggle_view'] = 'grid';	
		}
	}else{
		$_SESSION['tinymce_toggle_view'] = 'list';	
	}
}

$output = array();

$output["success"] = 1;

if(isset($_GET["path"]) AND $_GET["path"] != ""){
	if(!startsWith(urldecode($_GET["path"]), LIBRARY_FOLDER_PATH)){
		$current_folder = LIBRARY_FOLDER_PATH;
	}else{
		$current_folder = urldecode(clean($_GET["path"]));
	}
}else{
	$current_folder = LIBRARY_FOLDER_PATH;
}

include 'contents.php';


header("Content-type: text/plain;");
echo json_encode($output);
exit();
