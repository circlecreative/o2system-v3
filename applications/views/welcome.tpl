<!DOCTYPE html>
<html lang="en">
<head>
    <title>Welcome</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/system/fonts/roboto/roboto.css">

    <style type="text/css">
        @charset "utf-8";
        /* CSS Document */
        body {
            font-family: 'Roboto';
            font-size: 12px;
        }

        .page-wrapper {
            font-family: 'Roboto';
            font-size: 12px;
            color:#303641;
            margin: 50px;
        }


        * {
            margin: 0px;
            padding: 0px;
        }

        a {
            color: #e73d2f;
            text-decoration: none !important;
        }

        h1 {
            font-size: 28px;
            color: #e73d2f;
            text-transform: uppercase;
            padding: 20px 0px 0px 0px;
        }

        h2 {
            font-size: 16px;
            text-transform: uppercase;
        }

        p {
            font-size: 14px;
            padding: 10px 0px;
            font-weight: 400;
        }

        .copyright {
            font-weight: 400;
            font-size: 10px;
            text-transform: uppercase;
            margin-top: 15px;
        }

        small {
            font-size: 8px;
            text-transform: uppercase;
        }

        pre {
            padding: 10px 20px;
            background-color: #ebebeb;
            margin-bottom: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <img src="assets/system/images/logo.png" alt="" height="50">

    <h1>Welcome</h1>
    <p>The page you are looking at is being generated dynamically by O2System.</p>

    <p>If you would like to edit this page you'll find it located at:</p>
    <pre>{$controller.view}</pre>

    <p>The corresponding controller for this page is found at:</p>
    <pre>{$controller.file}</pre>

    <p>If you are exploring O2System for the very first time, you should start by reading the <a href="wiki">User Guide</a>.</p>

    <small>Page rendered in [elapsed_time] - Memory Usage. [memory_usage] - Memory Peak Usage. [memory_peak_usage]</small>

    <div class="copyright">
        POWERED BY<br>
        {$powered_by.line}<br><br>

        <small>
            {$copyright.year}<br>
            {$copyright.company}<br>
            All Rights Reserved
        </small>
    </div>
</div>
</body>
</html>