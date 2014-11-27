<html>
    <head>
        <title>
            Developer Print Out - O2System Framework
        </title>

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo system_url(); ?>assets/packages/bootstrap/bootstrap.min.css">

        <!-- Bootstrap Optional theme -->
        <link rel="stylesheet" href="<?php echo system_url(); ?>assets/packages/bootstrap/themes/default/default.min.css">
        <link rel="stylesheet" href="<?php echo system_url(); ?>assets/packages/pretiffy/pretiffy.css">

        <!-- Font Icon CSS -->
        <link rel="stylesheet" href="<?php echo system_url(); ?>assets/fonts/font-awesome/font-awesome.min.css">

        <style type="text/css">

            body { font-family: Monaco, Consolas, "Lucida Console", monospace; margin:25px; }
            .copyright { padding-top:10px; color:#790000; }
            pre { font-family: Monaco, Consolas, "Lucida Console", monospace; margin-top:20px; margin-bottom: 20px; }        
        </style>
    </head>
    <body>
        <div class="page-header">
            <h1>O2System Developer Tools</h1>
        </div>

        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="text-transform:uppercase">Print Out</h3>
                    <button onclick="javacript:selectCode('code')" class="btn btn-xs pull-right">Select Code</button> 
                </div>

                <div class="panel-body">
                    <pre id="code" class="prettyprint linenums lang-html"><?php echo $data; ?></pre>
                </div>
            </div>
        </div>

        <?php if(isset($tracer)): ?>
        <div class="row">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title" style="text-transform:uppercase">Debug Backtrace</h3>
                </div>

                <div class="panel-body">
                    <?php echo $tracer; ?>
                </div>
            </div>
        </div>            
        <?php endif; ?>
                
        <div class="copyright">Copyright &copy 2009 - <?php echo date('Y'); ?> PT. Lingkar Kreasi (Circle Creative) :: O2System Framework</div>        
    </body>

    <!-- Include jQuery -->
    <script type="text/javascript" src="<?php echo system_url(); ?>assets/js/jquery.js"></script>

    <!-- Include Prettify JS -->
    <script type="text/javascript" src="<?php echo system_url(); ?>assets/packages/pretiffy/pretiffy.js"></script>

    <!-- Include Bootstrap -->
    <script src="<?php echo system_url(); ?>assets/packages/bootstrap/bootstrap.min.js"></script>

    <!-- Initialise jQuery Syntax Highlighter -->
    <script type="text/javascript">
        $(document).ready(function(){
            prettyPrint();

            $('.toggle-args').click(function(){
                var iRel = $(this).attr('rel');
                $('#'+iRel).toggle('slow');
            });
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