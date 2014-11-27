<!DOCTYPE html>
<html lang="{$lang}">
<head>
	{$metadata}
	<title>{$browser_title}</title>
	
	{$assets->header}

	<!--[if lt IE 9]><script src="assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	
</head>
<body>
	<div class="wrap">
		{$header}
		
		{$content}

		{$footer}	
	</div>

	{$assets->footer}
</body>
</html>