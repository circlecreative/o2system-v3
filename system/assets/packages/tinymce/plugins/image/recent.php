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
	if(preg_match("/(.*)\/recent\.php/",$pageURL,$matches)){
		define('LIBRARY_FOLDER_URL', $matches[1] . '/uploads/');
	}
}

$output = array();

$output["success"] = 1;

if(isset($_SESSION['SimpleImageManager']) AND count($_SESSION['SimpleImageManager']) > 0){
	$html = '';
	foreach($_SESSION['SimpleImageManager'] as $s){
		$me = false;
		$exists = is_url_exist($s);
		$url_host = parse_url($s, PHP_URL_HOST);
		if($url_host == $_SERVER['HTTP_HOST']){
			$me = true;
		}
		
		if($me){
			$html .= '<div class="item"><a href="" class="img-thumbs" rel="' .$s . '"><img src="' . $s . '&w=90&h=90" class="img-polaroid" width="90" height="90"></a></div>';
		}elseif($exists){
			$html .= '<div class="item"><a href="" class="img-thumbs" rel="' .$s . '"><img src="' . $s . '" class="img-polaroid" width="90" height="90"></a></div>';
		}
	}
	if($html != ''){
		$output["html"] = $html;
	}else{
		$output["success"] = 0;
	}
}else{
	$output["success"] = 0;
}

header("Content-type: text/plain;");
echo json_encode($output);
exit();
