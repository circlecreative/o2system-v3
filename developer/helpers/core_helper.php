<?php if (!defined("DEVPATH")) exit("No direct script access allowed");
/**
 * O2System
 * 
 * Application development framework for PHP 5.1.6 or newer
 *
 * @package     O2System
 * @author      Steeven Andrian Salim (www.steevenz.com)
 * @copyright   Copyright (c) 2011, Circle Creative - Unlimited Digital Solutions.
 * @license     http://www.o2system.net/license.html
 * @link        http://www.o2system.net | http://www.circle-creative.com
 * @filesource
 */
// ------------------------------------------------------------------------
/**
 * Developer Common Helper Functions
 *
 * Loads the core functions.
 *
 * @package     Developer
 * @category    Developer Functions
 * @author      Steeven Andrian Salim (www.steevenz.com)
 * @copyright   Copyright (c) 2011, Circle Creative - Unlimited Digital Solutions.
 * @license     http://www.o2system.net/license.html
 * @link        http://www.o2system.net | http://www.circle-creative.com
 */
// ------------------------------------------------------------------------

if (!function_exists('print_out'))
{
    function print_out($data = '', $trace = true, $die = true)
    {
        $data = print_r($data,true);
        $data = htmlentities($data);
        $data = htmlspecialchars( htmlspecialchars_decode($data, ENT_QUOTES), ENT_QUOTES, 'UTF-8' );
        $data = str_replace("&nbsp;", "", $data);
        $data = trim($data);

        if($trace == true) $tracer = print_trace();

        ob_start();
        // Load print out template
        include DEVPATH . 'templates/print_out' . EXT;
        $buffer = ob_get_contents();
        ob_end_clean();

        echo $buffer;

        // Die
        if($die) die();
    }
}

if(!function_exists('print_lines'))
{
    function print_lines($line = '', $end_line = false, $trace = true, $die = true)
    {
        if (session_status() == PHP_SESSION_NONE OR session_id() == '') 
        {
            session_start();
        }

        $line = print_r($line,true);
        $line = htmlentities($line);
        $line = htmlspecialchars( htmlspecialchars_decode($line, ENT_QUOTES), ENT_QUOTES, 'UTF-8' );
        $line = str_replace("&nbsp;", "", $line);
        $line = trim($line);

        $_SESSION['DEV_PRINT_LINES'][] = $line;

        if($end_line == true)
        {
            $data = '';
            $data = implode(PHP_EOL, $_SESSION['DEV_PRINT_LINES']);

            session_destroy();

            print_out($data, $trace, $die);
        }
    }
}

if (!function_exists('print_trace'))
{
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
}

if (!function_exists('print_array'))
{
    function print_array($array, $die = true)
    {
        echo '<pre>';
            print_r($array);
        echo '</pre>';
        
        // Die
        if($die) die();
    }
}

if (!function_exists('base_uri'))
{
    function base_uri()
    {
        if (isset($_SERVER['HTTP_HOST']))
        {
            $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $base_url .= '://' . $_SERVER['HTTP_HOST'];
            $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        }
        else
        {
            $base_url = 'http://localhost/';
        }

        return $base_url;
    }
}

if (!function_exists('print_console'))
{
    function print_console($data)
    {
        if(is_array($data) OR is_object($data))
        {
            echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
        } 
        else 
        {
            echo("<script>console.log('PHP: ".$data."');</script>");
        }
    }
}

    /**
     * Logs messages/variables/data to browser console from within php
     *
     * @param $name: message to be shown for optional data/vars
     * @param $data: variable (scalar/mixed) arrays/objects, etc to be logged
     * @param $jsEval: whether to apply JS eval() to arrays/objects
     *
     * @return none
     * @author Sarfraz
     */
     function logConsole($name, $data = NULL, $jsEval = FALSE)
     {
          if (! $name) return false;
 
          $isevaled = false;
          $type = ($data || gettype($data)) ? 'Type: ' . gettype($data) : '';
 
          if ($jsEval && (is_array($data) || is_object($data)))
          {
               $data = 'eval(' . preg_replace('#[\s\r\n\t\0\x0B]+#', '', json_encode($data)) . ')';
               $isevaled = true;
          }
          else
          {
               $data = json_encode($data);
          }
 
          # sanitalize
          $data = $data ? $data : '';
          $search_array = array("#'#", '#""#', "#''#", "#\n#", "#\r\n#");
          $replace_array = array('"', '', '', '\\n', '\\n');
          $data = preg_replace($search_array,  $replace_array, $data);
          $data = ltrim(rtrim($data, '"'), '"');
          $data = $isevaled ? $data : ($data[0] === "'") ? $data : "'" . $data . "'";
 
$js = <<<JSCODE
\n<script>
     // fallback - to deal with IE (or browsers that don't have console)
     if (! window.console) console = {};
     console.log = console.log || function(name, data){};
     // end of fallback
 
     console.log('$name');
     console.log('------------------------------------------');
     console.log('$type');
     console.log($data);
     console.log('\\n');
</script>
JSCODE;
 
          echo $js;
     } # end logConsole