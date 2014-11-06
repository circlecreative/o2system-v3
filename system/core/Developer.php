<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * O2System
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		O2System
 * @author		Steeven Andrian Salim
 * @copyright	Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license		http://circle-creative.com/products/o2system/license.html
 * @link		http://circle-creative.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * System Developer
 *
 * Add development functions and class.
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Developer
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/developer.html
 */

function print_out($data = '', $trace = true, $die = true)
{
    $data = print_r($data,true);
    $data = htmlentities($data);
    $data = htmlspecialchars( htmlspecialchars_decode($data, ENT_QUOTES), ENT_QUOTES, 'UTF-8' );
    $data = str_replace("&nbsp;", "", $data);
    $data = trim($data);

    $assets_url = O2WP_BASEURL . 'assets/';
    if($trace == true) $tracer = print_trace();

    ob_start();
    // Load print out template
    include O2WP_SYSTEMPATH.'views/developer/print_out.php';
    $buffer = ob_get_contents();
    ob_end_clean();

    echo $buffer;
	
	// Die
    if($die) die();
}

function print_trace()
{
    static $script_start = 0;

    list($usec, $sec) = explode(' ', microtime());
    $script_start = (float) $sec + (float) $usec;

    $debug_info = debug_backtrace();
    $debug_info = array_slice($debug_info, 1);
    $debug_info = array_reverse($debug_info);

    $output =  '<ol>';
        foreach($debug_info as $info_line)
        {
            $output.= '<li>';
                $output.= 'Called Function: <strong>'.@$info_line['function'].'()</strong><br />';
                $output.= 'Executed File: '.@$info_line['file'].'<br />';

                
                list($usec, $sec) = explode(' ', microtime());
                $script_end = (float) $sec + (float) $usec;
                $elapsed_time = round($script_end - $script_start, 5);

                $output.= 'Code Lines: '.@$info_line['line'].' / Loaded Time: '.pow(10, $elapsed_time).' seconds / Memory Usage: '. (memory_get_usage() / 1000000) . 'MB<br>';
            
                if(!empty($info_line['args']))
                {
                    $output.= '<pre>';
                        $output.= print_r($info_line['args'], true);
                    $output.= '</pre>';
                }

            $output.= '</li>';
        }
    $output.= '</ol>';
    
    list($usec, $sec) = explode(' ', microtime());
    $script_end = (float) $sec + (float) $usec;
    $elapsed_time = round($script_end - $script_start, 5);

    $output.= '<span style="color:#666">';
    $output.= 'Page Rendered ' . pow(10,  $elapsed_time) . ' seconds / ';
    $output.= 'Memory Usage ' . memory_get_usage() / 1000000 . ' MB';
    $output.= '</span>'; 

    return $output;
}

function print_code($data, $die = true)
{
    echo '<pre>';
        $data = print_r($data,true);
	    $data = htmlentities($data);
	    $data = htmlspecialchars( htmlspecialchars_decode($data, ENT_QUOTES), ENT_QUOTES, 'UTF-8' );
	    $data = str_replace("&nbsp;", "", $data);
	    $data = trim($data);
	    echo $data;
    echo '</pre>';
    
    // Die
    if($die) die();
}

function print_firebug($data, $type = 'log')
{
    $_firebug =& load_class('Firebug', 'libraries');
    $_firebug->$type($data);
}     
// ------------------------------------------------------------------------
/* End of file Developer.php */
/* Location: ./system/Core/Developer.php */