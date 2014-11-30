<!DOCTYPE html>
<html lang="en">
<head>
    <title>Welcome to O2System Framework</title>

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
                <h1>Examples</h1>
                <h2>O2System Framework</h2>
                <img src="{system_url()}assets/images/logo-50.png" alt="">
            </div>

            <div class="panel-messages" style="text-align:center;">
                <h3>Loader</h3>
                <p>The O2System Framework can help you to perform load cross apps and cross modules</p>

                <p>For Standard Load:</p>
                <pre>$this->load->library('class_name');</pre>

                <p>For Cross App:</p>
                <pre>$this->load->library('app_name/class_name');</pre>

                <p>For Cross Module App:</p>
                <pre>$this->load->library('app_name/module_name/class_name');</pre>

                <p>
                    <h4>Theming Support</h4>
                    Chaining Method for Loading View / Theme:<br>
                    Automatic view replacement with theming view without changing the original view, for example:<br>
                    original: module_name/views/view_name.tpl<br>
                    replacement: theme_name/modules/modules_name/view_name.tpl<br>
                </p>
                <pre>$this->load->theme('theme_name')->layout('layout_name')->view('view_name');</pre>

                <p>
                    <h4>Compatible with Composer</h4>
                    Load third party with composer to create composer autoload<br>
                    O2System Framework by default it automatic load composer vendor at system, apps, and active app<br>
                    For adding composer at the app_name/modules/module_name/vendor<br>
                    Step 1. Run Shell<br>
                    Step 2. Go to desired module folder:
                </p>
                <pre>cd project_name/apps/app_name/modules/module_name/</pre>
                <p>Step 3. Compose your composer vendor</p>
                <pre>$this->load->library('app_name/module_name/class_name');</pre>
                <p>
                    When you accessing the module by URL<br>
                    O2System Framework will automaticly load the composer vendor at the requested module<br>
                    If you want to load the composer vendor from other app or module, same like to load library only the method called is composer<br>
                </p>
                <pre>$this->load->composer('app_name/module_name');</pre>

                <p>
                    Avaliable load method: helper, library, driver, composer, model, view, theme, layout, config, lang<br>
                    Coming soon method: module, controller
                </p>

                <h3>Developer Helper</h3>
                <p>Print Out everything to your screen:</p>
                <pre>print_out($object_or_array_or_string);</pre>
                <p>
                    Print Lines everything to your screen:<br>
                    Step 1: Start the lines
                </p>
                <pre>print_lines($something);</pre>
                <p>
                    If you want to ended the print lines:<br>
                    Step 2: End the lines
                </p>
                <pre>print_lines($something, TRUE);</pre>

                <p>
                    Avaliable developer function: print_out, print_lines, print_line_marker, print_dump, print_code, print_console<br>
                    Coming soon function: print_firebugs
                </p>



                <p>Page rendered in <strong>{$O2->benchmark->elapsed_time('total_execution')}</strong> seconds</p>
                <p>Memory Usage is <strong>{memory_get_usage()}</strong> bytes</p>
            </div>
            <div class="panel-footer">
                POWERED BY O2SYSTEM 2.0 BY PT. LINGKAR KREASI (CIRCLE CREATIVE)
            </div>
        </div>
    </div>
</div>
</body>
</html>