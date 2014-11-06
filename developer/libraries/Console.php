<?php if (!defined("DEVPATH")) exit("No direct script access allowed");
/**
 * Developer
 *
 * Application development debugger for PHP 5.1.6 or newer
 *
 * @package      O2System
 * @author       Steeven Andrian Salim
 * @copyright    Copyright (c) 2010 - 2011 PT. Lingkar Kreasi (Circle Creative)
 * @license      http://www.o2system.net/developer/license.html
 * @link         http://www.o2system.net | http://www.circle-creative.com
 */
// ------------------------------------------------------------------------
/**
 * Console
 * Output to browser console using javascript.
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

class Console {
     
    function __construct() {
        if (!defined("LOG"))    define("LOG",1);
        if (!defined("INFO"))   define("INFO",2);
        if (!defined("WARN"))   define("WARN",3);
        if (!defined("ERROR"))  define("ERROR",4);
     
        define("NL","\r\n");
        echo '<script type="text/javascript">'.NL;
         
        /// this is for IE and other browsers w/o console
        echo 'if (!window.console) console = {};';
        echo 'console.log = console.log || function(){};';
        echo 'console.warn = console.warn || function(){};';
        echo 'console.error = console.error || function(){};';
        echo 'console.info = console.info || function(){};';
        echo 'console.debug = console.debug || function(){};';
        echo '</script>';
        /// end of IE    
    }
     
    function debug($name, $var = null, $type = LOG) {
        echo '<script type="text/javascript">'.NL;
        switch($type) {
            case LOG:
                echo 'console.log("'.$name.'");'.NL;    
            break;
            case INFO:
                echo 'console.info("'.$name.'");'.NL;    
            break;
            case WARN:
                echo 'console.warn("'.$name.'");'.NL;    
            break;
            case ERROR:
                echo 'console.error("'.$name.'");'.NL;    
            break;
        }
         
        if (!empty($var)) {
            if (is_object($var) || is_array($var)) {
                $object = json_encode($var);
                echo 'var object'.preg_replace('~[^A-Z|0-9]~i',"_",$name).' = \''.str_replace("'","\'",$object).'\';'.NL;
                echo 'var val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).' = eval("(" + object'.preg_replace('~[^A-Z|0-9]~i',"_",$name).' + ")" );'.NL;
                switch($type) {
                    case LOG:
                        echo 'console.debug(val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).');'.NL;    
                    break;
                    case INFO:
                        echo 'console.info(val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).');'.NL;
                    break;
                    case WARN:
                        echo 'console.warn(val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).');'.NL;        
                    break;
                    case ERROR:
                        echo 'console.error(val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).');'.NL;    
                    break;
                }
            } else {
                switch($type) {
                    case LOG:
                        echo 'console.debug("'.str_replace('"','\\"',$var).'");'.NL;
                    break;
                    case INFO:
                        echo 'console.info("'.str_replace('"','\\"',$var).'");'.NL;
                    break;
                    case WARN:
                        echo 'console.warn("'.str_replace('"','\\"',$var).'");'.NL;    
                    break;
                    case ERROR:
                        echo 'console.error("'.str_replace('"','\\"',$var).'");'.NL;
                    break;
                }
            }
        }
        echo '</script>'.NL;
    }
}