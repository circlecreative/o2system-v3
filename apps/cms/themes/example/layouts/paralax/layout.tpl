<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <title>{$browser_title}</title>

    <!-- Google Font -->
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{system_url()}assets/packages/bootstrap/bootstrap.min.css">

    <!-- Bootstrap Optional theme -->
    <link rel="stylesheet" href="{system_url()}assets/packages/bootstrap/themes/default/default.min.css">

    <style type="text/css">
        @charset "utf-8";
        /* CSS Document */
        body { background: #333; font-family: 'Ubuntu', sans-serif; margin:25px; }
        * { margin:  0px; padding: 0px; }
        a, a:hover { text-decoration: none !important; color: #333; }
        ul, ol { margin: 0px; }
        .container { max-width: 960px; }
        .top50 { margin-top: 50px; }
        .row { margin-bottom: 50px; }
        .panel-box { width: 100%; height: auto; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; }
        .panel-header { width: 100%; height: 170px; background-color: #373e4a  ; text-align: center; -webkit-border-top-left-radius: 5px; -webkit-border-top-right-radius: 5px; -moz-border-radius-topleft: 5px; -moz-border-radius-topright: 5px; border-top-left-radius: 5px; border-top-right-radius: 5px;   }
        .panel-header img { background-color: #373e4a  ; border-radius: 50%; border: 7px solid transparent; }
        .panel-header h1 { font-size: 115px; color: #fff; font-weight: bold; margin: 0px; }
        .panel-header h2 { font-size: 20px; margin: 0px; color: #fff; font-weight: bold; text-transform: uppercase; }
        .panel-messages { background-color: #fff; color: #333; text-align: left; width: 100%; height: auto; padding: 65px 40px 25px 40px; }
        .panel-footer { background-color: #373e4a; color: #fff; width: 100%; height: auto; padding: 20px 0px; text-align: center; text-transform: uppercase; -webkit-border-bottom-right-radius: 5px; -webkit-border-bottom-left-radius: 5px; -moz-border-radius-bottomright: 5px; -moz-border-radius-bottomleft: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px; }
        .panel-footer a, .panel-footer a:hover, .panel-footer a:visited { color:#fff; }
        pre { margin: 15px 0px; font-family: Monaco, Consolas, "Lucida Console", monospace; }
    </style>

    <!-- Include jQuery -->
    <script type="text/javascript" src="{system_url()}assets/js/jquery.min.js"></script>

    <!-- Include Bootstrap -->
    <script src="{system_url()}assets/packages/bootstrap/bootstrap.min.js"></script>
</head>
<body>
<div class="container top50">
    <div class="row">
        <div class="panel-box">
            <div class="panel-header">
                {$blocks->header}
                <img src="{system_url()}assets/images/logo-50.png" alt="">
            </div>

            <div class="panel-messages" style="text-align:center;">
                <h4>Paralax Layout</h4>
                {$blocks->content}
            </div>
            <div class="panel-footer">
                {$blocks->footer}
            </div>
        </div>
    </div>
</div>

</body>
</html>