<?php if (!defined("DEVPATH")) exit("No direct script access allowed");
/**
 * O2System
 *
 * Application development framework for PHP 5.1.6 or newer
 *
 * @package      O2System
 * @author       Steeven Andrian Salim
 * @copyright    Copyright (c) 2010 - 2011 PT. Lingkar Kreasi (Circle Creative)
 * @license      http://www.o2system.net/license.html
 * @link         http://www.o2system.net | http://www.circle-creative.com
 */
// ------------------------------------------------------------------------
/**
 * O2System Developer
 *
 * @package       Libraries
 * @version       1.0 Build 19.09.2013
 * @author        Steeven Andrian Salim
 * @contributor   
 * @copyright     Copyright (c) 2010 - 2011 PT. Lingkar Kreasi (Circle Creative)
 * @license       http://www.o2system.net/license.html
 * @link          http://www.o2system.net | http://www.circle-creative.com
 */
// ------------------------------------------------------------------------
class Developer 
{
    public $version = '3.0';

    public function __construct()
    {
        //session_start();     
    }

    public function print_kint($data, $trace = true)
    {
        $ob_level = ob_get_level();

        if (ob_get_level() > $ob_level + 1)
        {
            ob_end_flush();
        }
        ob_start();

        // Include Print Out Template
        include ( DEVPATH . 'templates/print_out' . EXT );
        $buffer = ob_get_contents();
        ob_end_clean();

        echo $buffer;

        if($trace == true)
        {
            $this->print_trace();        
        }
        
        die();
    }
    
    public function print_out($data = '', $trace = true, $die = true)
    {
        $ob_level = ob_get_level();

        $data = print_r($data,true);
        $data = htmlentities($data);
        $data = htmlspecialchars( htmlspecialchars_decode($data, ENT_QUOTES), ENT_QUOTES, 'UTF-8' );
        $data = str_replace("&nbsp;", "", $data);
        $data = trim($data);

        ob_start();

        // Include Print Out Template
        include ( DEVPATH . 'templates/print_out' . EXT );
        $buffer = ob_get_contents();
        ob_end_clean();

        echo $buffer;

        if($trace == true)
        {
            $this->print_trace();        
        }
        
        if($die) die();
    }

    public function print_trace()
    {
        echo '<br /><strong style="color:#f00">Debug Tracer:</strong><br />';    

        static $start_time = NULL;
        static $start_code_line = 0;

        $call_info = array_shift( debug_backtrace() );
        $code_line = $call_info['line'];
        $file = array_pop( explode('/', $call_info['file']));

        if( $start_time === NULL )
        {
            print $file." > initialize<br />";
            $start_time = time() + microtime();
            $start_code_line = $code_line;
        }

        printf("%s > code-lines: %d lines / loaded time: %.4f / memory usage: %d KB<br />", $file, $start_code_line, $code_line, (time() + microtime() - $start_time), memory_get_usage()/1024);
        $start_time = time() + microtime();
        $start_code_line = $code_line;    

        echo '<ol class="tracer">';
        //array_walk( array_reverse( debug_backtrace() ),create_function('$a,$b','print "<li>Function: <strong>{$a[\'function\']}()</strong> <span><br />File: ".$a[\'file\']." <br />Line: {$a[\'line\']}</span></li>";'));
        array_walk( array_reverse( debug_backtrace() ),'print_trace');
        echo '</ol>';

        list($usec, $sec) = explode(' ', microtime());
        $script_start = (float) $sec + (float) $usec;
        list($usec, $sec) = explode(' ', microtime());
        $script_end = (float) $sec + (float) $usec;
        $elapsed_time = round($script_end - $script_start, 5);

        echo '<span style="color:#666">';
        echo 'Page Rendered ' . pow(10, $elapsed_time) . ' seconds<br />';
        echo 'Memory Usage ' . memory_get_usage() / 1000000 . ' MB';
        echo '</span>';  
    }
}