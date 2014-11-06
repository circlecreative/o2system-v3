<html>
    <head>
        <title>
            Developer Tools - Print Out :: O2System Framework
        </title>

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo base_uri(); ?>developer/assets/bootstrap/bootstrap.min.css">

        <!-- Bootstrap Optional theme -->
        <link rel="stylesheet" href="<?php echo base_uri(); ?>developer/assets/bootstrap/bootstrap-theme.min.css">
        <link rel="stylesheet" href="<?php echo base_uri(); ?>developer/assets/prettify/prettify.css">

        <style type="text/css">

            body { font-family: Monaco, Consolas, "Lucida Console", monospace; margin:25px; }
            .copyright { padding-top:10px; color:#790000; }
            pre { margin-top:20px; margin-bottom: 20px; }            
        </style>
    </head>
    <body>
        <div class="page-header">
            <h1>Developer Tools</h1>
        </div>

        <div class="row">
            <section class="panel">
                <header class="panel-heading">
                    Print Out
                    <button onclick="javacript:selectCode('code')" class="btn btn-xs pull-right">Select Code</button> 
                </header>

                <div class="panel-body">
                    <pre id="code" class="prettyprint linenums lang-html"><?php echo $data; ?></pre>
                </div>
            </section>
        </div>

        <?php if(isset($tracer)): ?>
        <div class="row">
            <?php echo $tracer; ?>
        </div>
        <?php endif; ?>
                
        <div class="copyright">Copyright &copy 2009 - <?php echo date('Y'); ?> PT. Lingkar Kreasi (Circle Creative) :: o2System Framework, HTML and CSS 2.0 Compatible</div>        
    </body>

    <!-- Include jQuery -->
    <script type="text/javascript" src="<?php echo base_uri(); ?>developer/assets/jquery/jquery.js"></script>

    <!-- Include Prettify JS -->
    <script type="text/javascript" src="<?php echo base_uri(); ?>developer/assets/prettify/prettify.js"></script>

    <!-- Include Bootstrap -->
    <script src="<?php echo base_uri(); ?>developer/assets/bootstrap/bootstrap.min.js"></script>

    <!-- Initialise jQuery Syntax Highlighter -->
    <script type="text/javascript">
        $(document).ready(function(){
            prettyPrint();
        });

        function selectCode(id) {
            if (document.selection) {
                var div = document.body.createTextRange();
                div.moveToElementText(document.getElementById(id));
                div.select();
            }
            else {
                var div = document.createRange();
                div.setStartBefore(document.getElementById(id));
                div.setEndAfter(document.getElementById(id));
                window.getSelection().addRange(div);
            }
        }
    </script>
</html>