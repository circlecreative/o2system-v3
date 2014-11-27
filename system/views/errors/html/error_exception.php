<!DOCTYPE html>
<html lang="en">
	<head>
		<title>An uncaught Exception was encountered</title>
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="<?php echo system_url(); ?>assets/packages/bootstrap/bootstrap.min.css">

		<!-- Bootstrap Optional theme -->
		<link rel="stylesheet" href="<?php echo system_url(); ?>assets/packages/bootstrap/themes/default/default.min.css">

		<style type="text/css">
			@charset "utf-8";
			/* CSS Document */
			body { background: #333; font-family: Monaco, Consolas, "Lucida Console", monospace; margin:25px; }
			* { margin:  0px; padding: 0px; }
			a { text-decoration: none !important; color: #333; }
			ul, ol { margin: 0px; }
			.container { max-width: 960px; }
			.top50 { margin-top: 50px; }
			.row { margin-bottom: 50px; }
			.panel-box { width: 100%; height: auto; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; }
			.panel-header { width: 100%; height: 170px; background-color: #e53120  ; text-align: center; -webkit-border-top-left-radius: 5px; -webkit-border-top-right-radius: 5px; -moz-border-radius-topleft: 5px; -moz-border-radius-topright: 5px; border-top-left-radius: 5px; border-top-right-radius: 5px;	}
			.panel-header img { background-color: #e53120  ; border-radius: 50%; border: 7px solid transparent; }
			.panel-header h1 { font-size: 115px; color: #fff; font-weight: bold; margin: 0px; }
			.panel-header h2 { font-size: 20px; margin: 0px; color: #fff; font-weight: bold; text-transform: uppercase; }
			.panel-messages { background-color: #fff; color: #333; text-align: left; width: 100%; height: auto; padding: 65px 40px 25px 40px; }
			.panel-footer { background-color: #e53120; color: #fff; width: 100%; height: auto; padding: 20px 0px; text-align: center; text-transform: uppercase; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px; }
			.panel-footer a, a:hover, a:visited { color:#fff; }
			pre { margin: 15px 0px; font-family: Monaco, Consolas, "Lucida Console", monospace; }
		</style>

		<!-- Include jQuery -->
		<script type="text/javascript" src="<?php echo system_url(); ?>assets/js/jquery.min.js"></script>

		<!-- Include Bootstrap -->
		<script src="<?php echo system_url(); ?>assets/packages/bootstrap/bootstrap.min.js"></script>
	</head>
	<body>
		<div class="container top50">
			<div class="row">
				<div class="panel-box">
					<div class="panel-header">
						<h1>ERROR</h1>
						<h2>An uncaught Exception was encountered</h2>
						<img src="<?php echo system_url(); ?>assets/images/logo-50.png" alt="">
					</div>
					
					<div class="panel-messages">
						<p><?php echo $message; ?></p>
					</div>
					<div class="panel-footer">
						Powered by O2SYSTEM Framework by <a href="www.circle-creative.com">Circle Creative</a>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>