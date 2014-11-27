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
	if(preg_match("/(.*)\/image\.php/",$pageURL,$matches)){
		define('LIBRARY_FOLDER_URL', $matches[1] . '/uploads/');
	}
}

$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

if(isset($_GET['src'])){
	$source = clean($_GET['src']);
}else{
	$source = "";
}

if(isset($_GET['title'])){
	$title = clean($_GET['title']);
}else{
	$title = "";
}

if(isset($_GET['alt'])){
	$alt = clean($_GET['alt']);
}else{
	$alt = "";
}

if(isset($_GET['width'])){
	$width = clean($_GET['width']);
}else{
	$width = "";
}

if(isset($_GET['height'])){
	$height = clean($_GET['height']);
}else{
	$height = "";
}

if(isset($_GET['align'])){
	$align = clean($_GET['align']);
}else{
	$align = "";
}


?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>TinyMCE 4 Image Manager</title>
		<link href="bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
		<script src="bootstrap/js/jquery.js"></script>
		<script src="bootstrap/js/bootstrap.min.js"></script>
		
		<link href="bootstrap/blueimp/css/style.css" rel="stylesheet" />
		<script src="bootstrap/blueimp/js/jquery.ui.widget.js"></script>
		<script src="bootstrap/blueimp/js/jquery.iframe-transport.js"></script>
		<script src="bootstrap/blueimp/js/jquery.fileupload.js"></script>
		
<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="bootstrap/js/html5shiv.js"></script>
<![endif]-->
<style>
.library-item div.item{
	margin: 9px;
	display: block;
	float: left;
	width: 90px;
	height: 108px;
	margin-bottom: 12px;
	margin-right: 27px;
}

.transparent {
	zoom: 1;
	filter: alpha(opacity=50);
	opacity: 0.5;
}

.transparent:hover {
	zoom: 1;
	filter: alpha(opacity=90);
	opacity: 0.9;
}
			
.img-polaroid:hover{
	border-color: #0088cc;
	-webkit-box-shadow: 0 1px 4px rgba(0, 105, 214, 0.25);
	-moz-box-shadow: 0 1px 4px rgba(0, 105, 214, 0.25);
	box-shadow: 0 1px 4px rgba(0, 105, 214, 0.25);
}
			
#ajax-loader-div {
    height: 400px;
    position: relative;
}
.ajax-loader {
    position: absolute;
    left: 50%;
    top: 50%;
    margin-left: -16px; /* -1 * image width / 2 */
    margin-top: -16px;  /* -1 * image height / 2 */
    display: block;     
}
<?php
if(!CanDeleteFiles()){
?>
.delete-file{
	display: none; 
}
<?php
}
?>
<?php
if(!CanDeleteFolder()){
?>
.delete-folder{
	display: none; 
}
<?php
}
?>

<?php
if(!CanRenameFiles()){
?>
.change-file{
	display: none; 
}
<?php
}
?>
<?php
if(!CanRenameFolder()){
?>
.change-folder{
	display: none; 
}
<?php
}
?>
</style>		
<script>
$(document).ready(function(){
	
	var originalWidth, originalHeight, loaded = false;
	
	<?php
	if(isset($_GET['src']) AND trim($_GET['src']) != ""){
		 echo 'var newImage = false;
		 ';
	}else{
		 echo 'var newImage = true;
		 ';
	}
	
	?>
	
	function MySerach(needle, haystack){
		var results = new Array();
		var counter = 0;
		var rgxp = new RegExp(needle, "g");
		var temp = new Array();
		for(i=0;i<haystack.length;i++){
			temp = haystack[i][1].match(rgxp)
			if(temp && temp.length > 0){
				results[counter] = haystack[i];
				counter = counter + 1;
			}
		}
		return results;
	}
	
	function getArray(object){
		var array = [];
		for(var key in object){
			var item = object[key];
			array[parseInt(key)] = (typeof(item) == "object")?getArray(item):item;
		}
		return array;
	}
	
	var search_haystack = new Array();
	
	$("#search").focus(function () {
		$("#lib-back").attr('disabled','disabled');
		$("#newfolder_name").attr('disabled','disabled');
		$("#newfolder_btn").attr('disabled','disabled');
		
		$("#refresh").attr("rel", "searching");
		
		$('#lib-title').empty();
		$('#lib-title').append('Searching... <a href="" id="clear-search">clear</a>');
		
		$.getJSON('search.php',{}, function(returned){ 
			search_haystack = getArray(returned);
		});
	});
	
	$(document).on('click', 'a#clear-search', function () {
		$('#lib-title').empty();
		$('#lib-title').append("Home");
		
		$("#newfolder_name").removeAttr("disabled", "disabled");
		$("#newfolder_btn").removeAttr("disabled", "disabled");
		
		$("#refresh").attr("rel", "<?php echo LIBRARY_FOLDER_PATH; ?>");
		
		$("#search").val("");
    			
    		$('#gallery-images').empty();
		$('#gallery-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
		$.getJSON('lib.php' + '?dummy=' + new Date().getTime(),{}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				$('#gallery-images').empty();
				$('#gallery-images').append('<center>No images in library.</center>');
			}
		});
		return false;
	});
	
	$("#search").keyup(function(event) {
    		if(this.value.length > 1){
    			
    			
    			$('#gallery-images').empty();
			$('#gallery-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
			
			var results = MySerach(this.value, search_haystack);
			$('#gallery-images').empty();
			if(results.length > 0){
				for(i=0;i<results.length;i++){
					$('#gallery-images').append('<a href="" class="img-thumbs" rel="' + results[i][0] + '"><img src="' + results[i][0] + '&w=90&h=90" class="img-polaroid" width="90" height="90"></a>');
				}
			}else{
				$('#gallery-images').append('<center>No images match the search.</center>');
			}
    		}else if(this.value.length == 0){
    			$('#lib-title').empty();
			$('#lib-title').append("Home");
			
			$("#newfolder_name").removeAttr("disabled", "disabled");
			$("#newfolder_btn").removeAttr("disabled", "disabled");
			
			$("#refresh").attr("rel", "<?php echo LIBRARY_FOLDER_PATH; ?>");
    			
    			$('#gallery-images').empty();
			$('#gallery-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
			$.getJSON('lib.php' + '?dummy=' + new Date().getTime(),{}, function(returned){ 
				if(returned.success == 1){
					$('#gallery-images').empty();
					$('#gallery-images').append(returned.html);
				}else{
					$('#gallery-images').empty();
					$('#gallery-images').append('<center>No images in library.</center>');
				}
			});
    		}
    	});
	
	$("#preview").bind("load", function () {
		if(newImage){
			if ($("#preview").get(0).naturalWidth) {
				$("#width").val($("#preview").get(0).naturalWidth);
				$("#height").val($("#preview").get(0).naturalHeight);
					
				originalWidth = $("#preview").get(0).naturalWidth;
				originalHeight = $("#preview").get(0).naturalHeight;
			} else if ($("#preview").attr("naturalWidth")) {
				$("#width").val($("#preview").attr("naturalWidth"));
				$("#height").val($("#preview").attr("naturalHeight"));
					
				originalWidth = $("#preview").attr("naturalWidth");
				originalHeight = $("#preview").attr("naturalHeight");
			}
		
			parent.document.getElementById("width").value= originalWidth;
			parent.document.getElementById("height").value= originalHeight;
		}else{
			newImage = true;
			if ($("#preview").get(0).naturalWidth) {
				originalWidth = $("#preview").get(0).naturalWidth;
				originalHeight = $("#preview").get(0).naturalHeight;
			} else if ($("#preview").attr("naturalWidth")) {
				originalWidth = $("#preview").attr("naturalWidth");
				originalHeight = $("#preview").attr("naturalHeight");
			}
		}
	});
	
	$(document).on('click', 'a.mi-close', function () {
		$(this).parent().hide();
		return false;
	});
	
	$(document).on('click', 'a.img-thumbs', function () {
		$("#preview").attr("src", "");
		$("#width").val();
		$("#height").val();
		$("#source").val($(this).attr("rel"));
        	$("#preview").attr("src", $(this).attr("rel") + '?dummy=' + new Date().getTime());
        	$('#myTab a[href="#tab1"]').tab('show');
        	parent.document.getElementById("src").value= $(this).attr("rel");
        	$.post("update_recent.php" + "?dummy=" + new Date().getTime(), { src: $(this).attr("rel") } );
		return false;
	});
	
	$("#source").bind("change", function () {
		$.post("update_recent.php" + "?dummy=" + new Date().getTime(), { src: this.value } );
		$("#preview").attr("src", this.value + '?dummy=' + new Date().getTime());
		parent.document.getElementById("src").value= this.value;
	});
	
	$("#alt").bind("change", function () {
		parent.document.getElementById("alt").value= this.value;
	});
	
	$("#title").bind("change", function () {
		parent.document.getElementById("title").value= this.value;
	});
	
	$("#width").keyup(function(event) {
    		parent.document.getElementById("width").value= this.value;
		if($('#constrain').is(':checked') && this.value != originalWidth){
			parent.document.getElementById("height").value= Math.round((this.value / originalWidth) * originalHeight);
			$("#height").val(Math.round((this.value / originalWidth) * originalHeight));
		}else if(this.value == originalWidth){
			parent.document.getElementById("height").value= originalHeight;
			$("#height").val(originalHeight);
		}
    	});
    	
    	$("#height").keyup(function(event) {
    		parent.document.getElementById("height").value= this.value;
		if($('#constrain').is(':checked') && this.value != originalHeight){
			parent.document.getElementById("width").value= Math.round((this.value / originalHeight) * originalWidth);
			$("#width").val(Math.round((this.value / originalHeight) * originalWidth));
		}else if(this.value == originalHeight){
			parent.document.getElementById("width").value= originalWidth;
			$("#width").val(originalWidth);
		}
    	});
    	
    	$("#width").bind("change", function () {
    		parent.document.getElementById("width").value= this.value;
		if($('#constrain').is(':checked') && this.value != originalWidth){
			parent.document.getElementById("height").value= Math.round((this.value / originalWidth) * originalHeight);
			$("#height").val(Math.round((this.value / originalWidth) * originalHeight));
		}else if(this.value == originalWidth){
			parent.document.getElementById("height").value= originalHeight;
			$("#height").val(originalHeight);
		}
    	});
    	
    	$("#height").bind("change", function () {
    		parent.document.getElementById("height").value= this.value;
		if($('#constrain').is(':checked') && this.value != originalHeight){
			parent.document.getElementById("width").value= Math.round((this.value / originalHeight) * originalWidth);
			$("#width").val(Math.round((this.value / originalHeight) * originalWidth));
		}else if(this.value == originalHeight){
			parent.document.getElementById("width").value= originalWidth;
			$("#width").val(originalWidth);
		}
    	});
    	
	$(".dimensions").keydown(function(event) {
		if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || 
			// Allow: Ctrl+A
			(event.keyCode == 65 && event.ctrlKey === true) || 
			// Allow: home, end, left, right
			(event.keyCode >= 35 && event.keyCode <= 39)) {
			// let it happen, don't do anything
			return;
		}else {
            // Ensure that it is a number and stop the keypress
			if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
				event.preventDefault(); 
			} 
		}
    	});
    	
	$("#do_link").bind("change", function () {
		if($(this).is(':checked')){
			$("#link_url").removeAttr('disabled'); 
			$("#target").removeAttr('disabled'); 
		}else{
			$("#link_url").attr('disabled','disabled');
			parent.document.getElementById("linkURL").value= "";
			
			$("#target").attr('disabled','disabled');
			parent.document.getElementById("target").value= "";
		}
	});
	
	$("#link_url").bind("change", function () {
		parent.document.getElementById("linkURL").value= this.value;
	});
	
	$("#target").bind("change", function () {
		parent.document.getElementById("target").value= this.value;
	});
	
	$("#float").bind("change", function () {
		parent.document.getElementById("align").value= this.value;
	});
	
	
	$("#get-recent").bind("click", function () {
		$('#recent-images').empty();
		$('#recent-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
		$.getJSON('recent.php',{}, function(returned){ 
			if(returned.success == 1){
				$('#recent-images').empty();
				$('#recent-images').append(returned.html);
			}else{
				$('#recent-images').empty();
				$('#recent-images').append('<center>No recent images found.</center>');
			}
		});
	});
	
	$("#refresh").bind("click", function () {
		if($(this).attr("rel") == 'searching'){
			return false;
		}
		
		$('#gallery-images').empty();
		$('#gallery-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
		$.getJSON('lib.php' + '?dummy=' + new Date().getTime(),{path: $(this).attr("rel")}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				$('#gallery-images').empty();
				$('#gallery-images').append('<center>No images in the folder.</center>');
			}
		});
		
		
		
		return false;
	});
	
	$("#toggle-layout").bind("click", function () {
		if($(this).attr("rel") == 'searching'){
			return false;
		}
		
		$('#gallery-images').empty();
		$('#gallery-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
		$.getJSON('lib.php' + '?dummy=' + new Date().getTime(),{path: $(this).attr("rel"), toggle: 1}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				$('#gallery-images').empty();
				$('#gallery-images').append('<center>No images in the folder.</center>');
			}
		});
		
		
		
		return false;
	});
	
	$("#get-lib").bind("click", function () {
		if(loaded == false){
			$('#gallery-images').empty();
			$('#gallery-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
			$.getJSON('lib.php' + '?dummy=' + new Date().getTime(),{}, function(returned){ 
				if(returned.success == 1){
					$('#gallery-images').empty();
					$('#gallery-images').append(returned.html);
				}else{
					$('#gallery-images').empty();
					$('#gallery-images').append('<center>No images in library.</center>');
				}
			});
			loaded = true;
		}
	});
	
	$(document).on('click', '#newfolder_btn', function () {
		if($('#newfolder_name').val() == ""){
			alert('Please provide a name for the new folder');
			return false;
		}
		
		$('#new-folder-msg').empty();
		$('#new-folder-msg').append('Creating...&nbsp;&nbsp;&nbsp;');
		
		$.getJSON('new_folder.php' + '?dummy=' + new Date().getTime(),{path: $("#refresh").attr("rel"), folder: $('#newfolder_name').val()}, function(returned){ 
			if(returned.success == 1){
				$('#newfolder_name').val("");
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
				$('#new-folder-msg').empty();
				$('#new-folder-msg').append('<span style="color: green;">Done...&nbsp;&nbsp;&nbsp;</span>');
				setTimeout(function(){ $('#new-folder-msg').empty() }, 5000);
			}else{
				$('#new-folder-msg').empty();
				$('#new-folder-msg').append('<span style="color: red;">Error...&nbsp;&nbsp;&nbsp;</span>');
				setTimeout(function(){ $('#new-folder-msg').empty() }, 5000);
				if(returned.msg != ""){
					alert(returned.msg);
				}
			}
		});
		
		
		
		return false;
	});
	
	$(document).on('click', 'a.delete-file', function () {
		var content = $(this).parent().parent().html();
		var the_parent = $(this).parent().parent();
		var r=confirm("Are you sure you want to delete this file?");
		if(r==false){
			return false;
		}
		$(this).parent().parent().empty().append('<p>Deleting...</p>');
		$.getJSON('delete_file.php' + '?dummy=' + new Date().getTime(),{path: $("#refresh").attr("rel"),file: $(this).attr("rel")}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				the_parent.empty();
				the_parent.html(content);
				if(returned.msg != ""){
					alert(returned.msg);
				}
			}
		});
		return false;
	});
	
	$(document).on('click', 'a.delete-folder', function () {
		var content = $(this).parent().parent().html();
		var the_parent = $(this).parent().parent();
		var r=confirm("Are you sure you want to delete this folder and it's contents?");
		if(r==false){
			return false;
		}
		$(this).parent().parent().empty().append('<p>Deleting...</p>');
		$.getJSON('delete_folder.php' + '?dummy=' + new Date().getTime(),{path: $("#refresh").attr("rel"),folder: $(this).attr("rel")}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				the_parent.empty();
				the_parent.html(content);
				if(returned.msg != ""){
					alert(returned.msg);
				}
			}
		});
		return false;
	});
	
	
	$(document).on('click', 'a.change-folder', function () {
		var current_value = $(this).attr("rel");
		var content = $(this).parent().parent().html();
		var the_parent = $(this).parent().parent();
		var r=prompt("Please enter the new name",current_value);
		if(r==null || r==""){
			return false;
		}
		
		if(r==current_value){
			return false;
		}
		
		$(this).parent().parent().empty().append('<p>Saving...</p>');
		
		
		$.getJSON('rename_folder.php' + '?dummy=' + new Date().getTime(),{path: $("#refresh").attr("rel"),new_name: r,current_name: current_value}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				the_parent.empty();
				the_parent.html(content);
				if(returned.msg != ""){
					alert(returned.msg);
				}
			}
		});
		return false;
	});
	
	function getExtension(filename) {
		return filename.split('.').pop().toLowerCase();
	}
	
	$(document).on('click', 'a.change-file', function () {
		var current_value = $(this).attr("rel");
		var content = $(this).parent().parent().html();
		var the_parent = $(this).parent().parent();
		var extension = getExtension(current_value);
		var current_file_name = current_value.substr(0, current_value.lastIndexOf('.')) || current_value;
		
		var r=prompt("Please enter the new name",current_file_name);
		if(r==null || r==""){
			return false;
		}
		
		if((r + "." + extension) ==current_value){
			return false;
		}
		
		$(this).parent().parent().empty().append('<p>Saving...</p>');
		
		$.getJSON('rename_file.php' + '?dummy=' + new Date().getTime(),{path: $("#refresh").attr("rel"),new_name: (r + "." + extension),current_name: current_value}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				the_parent.empty();
				the_parent.html(content);
				if(returned.msg != ""){
					alert(returned.msg);
				}
			}
		});
		return false;
	});
	
	$(document).on('click', '#refresh-dirs', function () {
		$('#select-dir-msg').empty();
		$('#select-dir-msg').append('Loading...&nbsp;&nbsp;&nbsp;');
		
		$.getJSON('refresh_dir_list.php' + '?dummy=' + new Date().getTime(),{}, function(returned){ 
			if(returned.success == 1){
				$('#select-dir-msg').empty();
				$('#select-dir-msg').append('<span style="color: green;">Done...&nbsp;&nbsp;&nbsp;</span>');
				setTimeout(function(){ $('#select-dir-msg').empty() }, 5000);
				$('#select-dir').empty();
				$('#select-dir').append(returned.html);
			}
		});
		return false;
	});
	
	$(document).on('change', '#select-dir', function () {
		$('#select-dir-msg').empty();
		$('#select-dir-msg').append('Sending...&nbsp;&nbsp;&nbsp;');
		
		$.getJSON('set_upload_directory.php' + '?dummy=' + new Date().getTime(),{path:$(this).val() }, function(returned){ 
			if(returned.success == 1){
				$('#select-dir-msg').empty();
				$('#select-dir-msg').append('<span style="color: green;">Done...&nbsp;&nbsp;&nbsp;</span>');
				setTimeout(function(){ $('#select-dir-msg').empty() }, 5000);
			}
		});
		return false;
	});
	
	$(document).on('click', 'a.lib-folder', function () {
		var str =  decodeURIComponent($(this).attr("rel"));
			
		var stringArray = str.split("/");
			
		stringArray.pop();
			
			
		var current_folder = stringArray[stringArray.length-1];
		if((current_folder + "/") == '<?php echo LIBRARY_FOLDER_PATH; ?>'){
			current_folder = "Home";
		}
		$('#lib-title').empty();
		$('#lib-title').append(current_folder);
		
		$("#refresh").attr("rel", $(this).attr("rel"));
		
		if($("#lib-back").is(":disabled")){
			$("#lib-back").removeAttr('disabled'); 
			
		}else{
			stringArray.pop();
			
			$("#lib-back").attr('rel', stringArray.join("/") + "/");
			
			
			
		}
		$('#gallery-images').empty();
		$('#gallery-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
		$.getJSON('lib.php' + '?dummy=' + new Date().getTime(),{path: $(this).attr("rel")}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				$('#gallery-images').empty();
				$('#gallery-images').append('<center>No images in the folder.</center>');
			}
		});
		
		
		
		return false;
	});
	
	$(document).on('click', 'button#lib-back', function () {
		if($(this).is(":disabled")){
			return false;
		}
		
		if($(this).attr("rel") == '<?php echo LIBRARY_FOLDER_PATH; ?>'){
			$(this).attr('disabled','disabled');
		}
		
		$("#refresh").attr("rel", $(this).attr("rel"));
		
		$('#gallery-images').empty();
		$('#gallery-images').append('<div id="ajax-loader-div"><img src="bootstrap/img/ajax-loader.gif" alt="Loading..." class="ajax-loader"></div>');
		$.getJSON('lib.php' + '?dummy=' + new Date().getTime(),{path: $(this).attr("rel")}, function(returned){ 
			if(returned.success == 1){
				$('#gallery-images').empty();
				$('#gallery-images').append(returned.html);
			}else{
				$('#gallery-images').empty();
				$('#gallery-images').append('<center>No images in the folder.</center>');
			}
		});
		
		var str =  $(this).attr("rel");
		var stringArray = str.split("/");
		
		stringArray.pop();
		
		var current_folder = stringArray.pop();
		
		if((current_folder + "/") == '<?php echo LIBRARY_FOLDER_PATH; ?>'){
			current_folder = "Home";
			$(this).attr("rel", "<?php echo LIBRARY_FOLDER_PATH; ?>");
		}else{
			$(this).attr("rel", stringArray.join("/") + "/");
		}
		
		$('#lib-title').empty();
		$('#lib-title').append(current_folder);
		
		return false;
	});
});
</script>
	</head>
	<body>
		<div class="container-fluid">
			<div class="row-fluid">
			
				<div class="span12" style="margin-top: 20px;">
					
					
					<div class="tabbable tabs-left">
						<ul class="nav nav-tabs" id="myTab">
							<li><a href="#tab1" data-toggle="tab"><i class="icon-globe"></i> Insert from URL</a></li>
							<?php if(CanAcessLibrary()){?>
							<li><a href="#tab2" data-toggle="tab" id="get-lib"><i class="icon-folder-open"></i> Get from Library</a></li>
							<?php }?>
							<?php if(CanAcessUploadForm()){?>
							<li><a href="#tab3" data-toggle="tab"><i class="icon-upload"></i> Upload Now</a></li>
							<?php }?>
							<li><a href="#tab4" data-toggle="tab" id="get-recent"><i class="icon-time"></i> Recent</a></li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane" id="tab1">
								
<div class="row-fluid" style="padding-top: 5px;">
			
				<div class="pull-left" style="width: 50%;">								
							<form class="form-horizontal" action="" method="">
<p>
<input type="text" id="source" name="source" value="<?php echo $source;?>" placeholder="URL" title="URL">
</p>

<p>
<input type="text" id="title" name="title" value="<?php echo $title;?>" placeholder="Title" title="Title">
</p>

<p>
<input type="text" id="alt" name="alt" value="<?php echo $alt;?>" placeholder="Description" title="Description">
</p>
<br/>
<p>
<input type="text" id="width" name="width" class="input-small dimensions" placeholder="Width" title="Width" value="<?php echo $width;?>"> &times; <input type="text" id="height" name="height" class="input-small dimensions" placeholder="Height" title="Height" value="<?php echo $height;?>"> <br/><input type="checkbox" id="constrain" name="constrain" checked="checked"> Force original aspect ratio
</p>
<br/>
<p>
<select id="float" name="float">
<option value="">Alignment: None</option>
<option value="left" <?php echo ($align == 'left' ? 'selected="selected"' : '');?>>Left</option>
<option value="right" <?php echo ($align == 'right' ? 'selected="selected"' : '');?>>Right</option>
</select>
</p>

<?php if(!isset($_GET['src']) OR trim($_GET['src']) == ""){?>
<br/>
<p>
<input type="checkbox" id="do_link" name="do_link"> Wrap image in a link
</p>

<p>
<input type="text" id="link_url" name="link_url" disabled placeholder="Link URL" title="Link URL">
</p>

<p>
<select id="target" name="target" disabled>
<option value="_self">Target: None</option>
<option value="_blank">New window</option>
</select>
</p>

<?php }?>

</form>	
</div>
<div class="pull-right" style="width: 50%; height: 70%;">						
<img id="preview" src="<?php echo $source;?>" alt="Preview" style="margin: 2px; padding: 5px; max-width: 300px; overflow:hidden; max-height: 400px; border: 1px solid rgb(192, 192, 192);"/>			
						
						
						
						</div>
						<div style="clear: both;"></div>
						</div>

								
							
								
							</div>
							<div class="tab-pane" id="tab2">
								<div>
								<div class="pull-left" style="padding-left: 11px;">
									<button class="btn" disabled id="lib-back" rel="<?php echo LIBRARY_FOLDER_PATH; ?>"><i class="icon-hand-left"></i> Back</button>&nbsp;&nbsp;&nbsp;<a href="" title="refresh" rel="<?php echo LIBRARY_FOLDER_PATH; ?>" id="refresh"><i class="icon-refresh"></i></a>
					
								</div>
								
								<div class="pull-right" style="padding-right: 12px;">
									<input type="text" class="input-medium" id="search" placeholder="Search">
								</div>
								<?php if(CanCreateFolders()){?>
								<div class="pull-right" style="padding-right: 12px;">
									<span id="new-folder-msg"></span>
									<div class="input-append">
										<input class="input-medium" id="newfolder_name" type="text" placeholder="Create folder here">
										<button id="newfolder_btn" class="btn" type="button"><i class="icon-plus"></i></button>
									</div>
								</div>
								<?php }?>
								<div style="clear: both;"></div>
								</div>
								<div>
								<p class="pull-left muted" id="lib-title" style="padding-left: 12px;">Home</p>
								
								<p style="padding-right: 20px;" class="pull-right transparent"><a id="toggle-layout" href="" title="Toggle List/Grid Views"><i class="icon-th-list"></i></a></p>
								<div style="clear: both;"></div>
								</div>
								<div class="library-item" id="gallery-images"></div>
							</div>
							<div class="tab-pane" id="tab3">
<script>
$(function(){

    var ul = $('#upload ul');

    $('#drop a').click(function(){
        // Simulate a click on the file input button
        // to show the file browser dialog
        $(this).parent().find('input').click();
    });

    // Initialize the jQuery File Upload plugin
    $('#upload').fileupload({
	dataType: 'json',
	acceptFileTypes: /(\.|\/)(<?php echo implode("|", explode(",", ALLOWED_IMG_EXTENSIONS));?>)$/i,
        maxFileSize: <?php echo MBToBytes($upload_mb);?>,
	
        // This element will accept file drag/drop uploading
        dropZone: $('#drop'),

        // This function is called when a file is added to the queue;
        // either via the browse button, or via drag/drop:
        add: function (e, data) {

            var tpl = $('<li><div class="alert alert-info"><img class="loader" src="bootstrap/blueimp/img/ajax-loader.gif"> <a class="close" data-dismiss="alert">×</a></div></li>');

            // Append the file name and file size
           // Append the file name and file size
            tpl.find('div').append(data.files[0].name + ' <small>[<i>' + formatFileSize(data.files[0].size) + '</i>]</small>');

            // Add the HTML to the UL element
            data.context = tpl.appendTo(ul);

            // Automatically upload the file once it is added to the queue
            var jqXHR = data.submit();
        },
        
        done: function (e, data) {
            if(data.result.success == true){
        		data.context.remove();
        		$("#uploaded-images").append('<a style="margin: 9px; margin-right: 27px;" href="" class="img-thumbs" rel="' + data.result.file + '"><img src="' + encodeURIComponent(data.result.file) + '&w=90&h=90" class="img-polaroid" width="90" height="90"></a>');
        	}else{
        		data.context.empty();
            		var tpl = $('<li><div class="alert alert-error"><a class="close" data-dismiss="alert">×</a></div></li>');
			tpl.find('div').append('<b>Error:</b> ' + data.files[0].name + ' <small>[<i>' + formatFileSize(data.files[0].size) + '</i>]</small> ' + data.result.reason);
			data.context.append(tpl);
        	}
        },
         fail: function (e, data) {
            data.context.empty();
            		var tpl = $('<li><div class="alert alert-error"><a class="close" data-dismiss="alert">×</a></div></li>');
			tpl.find('div').append('<b>Error:</b> ' + data.files[0].name + ' <small>[<i>' + formatFileSize(data.files[0].size) + '</i>]</small> ' + data.errorThrown);
			data.context.append(tpl);
        }
    });


    // Prevent the default action when a file is dropped on the window
    $(document).on('drop dragover', function (e) {
        e.preventDefault();
    });

    // Helper function that formats the file sizes
    function formatFileSize(bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }

        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }

        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }

        return (bytes / 1000).toFixed(2) + ' KB';
    }

});
</script>
<div>
<div class="pull-left">
	<p class="muted">Maximum upload file size: <?php echo $upload_mb;?> MB</p>
</div>
<div class="pull-right">
<p>
<span id="select-dir-msg"></span>
<select  id="select-dir" class="input-medium">
<?php echo Dirtree(LIBRARY_FOLDER_PATH);?>
</select>&nbsp;&nbsp;&nbsp;<a href="" title="refresh folders list" id="refresh-dirs"><i class="icon-refresh"></i></a>
</p>
</div>
<div class="clearfix"></div>
</div>
<form id="upload" method="post" action="upload.php" enctype="multipart/form-data">
			
			<div id="drop">
				

				<a class="btn">Click Or Drop</a>
				<input type="file" name="upl" multiple />
			</div>
			<br/>
			<ul id="upload-msg">
				<!-- The file uploads will be shown here -->
			</ul>

		</form>
<br/>
<div class="library-item" id="uploaded-images"></div>

							
							</div>
							
							<div class="tab-pane" id="tab4">
								<div class="library-item" id="recent-images"></div>
							</div>
						</div>
					</div> <!-- /tabbable -->
<script>
  $(function () {
    $('#myTab a[href="#tab1"]').tab('show');
  })
</script>					
				</div>
			</div>
		</div> <!-- /container -->	
	</body>
</html>
