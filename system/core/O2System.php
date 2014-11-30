<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package		O2System
 * @author		Steeven Andrian Salim
 * @copyright	Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license		http://circle-creative.com/products/o2system/license.html
 * @license	    http://opensource.org/licenses/MIT	MIT License
 * @link		http://circle-creative.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * System Initialization
 *
 * Loads the base classes and executes the request.
 * The system Initialization sequence concept process is based on CodeIgniter 3.0-dev
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Bootstrap
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/system.html
 */

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;
	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;
	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */
	require(APPSPATH.'config/constants.php');

/**
 * System Version
 *
 * @var string
 *
 */
	define('SYSTEM_VERSION', '2.0');

/*
 * ------------------------------------------------------
 *  Load the developer functions
 * ------------------------------------------------------
 */
	require(SYSPATH.'core/Developer.php');	

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */
	require(SYSPATH.'core/Common.php');	

/*
 * ------------------------------------------------------
 * Security procedures
 * ------------------------------------------------------
 */
if ( ! is_php('5.4'))
{
	ini_set('magic_quotes_runtime', 0);
	if ((bool) ini_get('register_globals'))
	{
		$_protected = array(
			'_SERVER',
			'_GET',
			'_POST',
			'_FILES',
			'_REQUEST',
			'_SESSION',
			'_ENV',
			'_COOKIE',
			'GLOBALS',
			'HTTP_RAW_POST_DATA',
			'system_path',
			'application_folder',
			'view_folder',
			'_protected',
			'_registered'
		);

		$_registered = ini_get('variables_order');
		foreach (array('E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER') as $key => $superglobal)
		{
			if (strpos($_registered, $key) === FALSE)
			{
				continue;
			}

			foreach (array_keys($$superglobal) as $var)
			{
				if (isset($GLOBALS[$var]) && ! in_array($var, $_protected, TRUE))
				{
					$GLOBALS[$var] = NULL;
				}
			}
		}
	}
}	

/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
	register_shutdown_function('_shutdown_handler');

/*
 * ------------------------------------------------------
 *  Load the Benchmark Class
 * ------------------------------------------------------
 */
	$BM =& load_class('Benchmark', 'core');	

	// Mark a o2system benchmark start point
	$BM->start('total_execution');
	$BM->start('o2system');

/*
 * ------------------------------------------------------
 *  Load the O2System Class
 * ------------------------------------------------------
 */
	$O2SYSTEM =& load_class('System', 'core');
	//print_out($O2SYSTEM);
/*
 * ------------------------------------------------------
 *  Load the URI Class
 * ------------------------------------------------------
 */
	$URI =& load_class('URI', 'core');
	//print_out($URI);
/*
 * ------------------------------------------------------
 *  Instantiate the config class
 * ------------------------------------------------------
 */
	$CFG =& load_class('Config', 'core');		
	//print_out($CFG);

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class
 * ------------------------------------------------------
 */
	$EXT =& load_class('Hooks', 'core', 'CI');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('pre_system');

/*
 * ------------------------------------------------------
 * Important charset-related stuff
 * ------------------------------------------------------
 *
 * Configure mbstring and/or iconv if they are enabled
 * and set MB_ENABLED and ICONV_ENABLED constants, so
 * that we don't repeatedly do extension_loaded() or
 * function_exists() calls.
 *
 * Note: UTF-8 class depends on this. It used to be done
 * in it's constructor, but it's _not_ class-specific.
 *
 */
	$charset = strtoupper(config_item('charset'));
	ini_set('default_charset', $charset);
	if (extension_loaded('mbstring'))
	{
		define('MB_ENABLED', TRUE);
		// mbstring.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('mbstring.internal_encoding', $charset);
		// This is required for mb_convert_encoding() to strip invalid characters.
		// That's utilized by CI_Utf8, but it's also done for consistency with iconv.
		mb_substitute_character('none');
	}
	else
	{
		define('MB_ENABLED', FALSE);
	}
	// There's an ICONV_IMPL constant, but the PHP manual says that using
	// iconv's predefined constants is "strongly discouraged".
	if (extension_loaded('iconv'))
	{
		define('ICONV_ENABLED', TRUE);
		// iconv.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('iconv.internal_encoding', $charset);
	}
	else
	{
		define('ICONV_ENABLED', FALSE);
	}
	if (is_php('5.6'))
	{
		ini_set('php.internal_encoding', $charset);
	}

/*
 * ---------------------------------------------------------------
 *  Load compatibility features
 * ---------------------------------------------------------------
 */
	require_once(SYSPATH.'core/compat/mbstring.php');
	require_once(SYSPATH.'core/compat/hash.php');
	require_once(SYSPATH.'core/compat/password.php');
	require_once(SYSPATH.'core/compat/standard.php');	
/*
 * ---------------------------------------------------------------
 *  Instantiate the UTF-8 class
 * ---------------------------------------------------------------
 *
 * Note: Order here is rather important as the UTF-8
 * class needs to be used very early on, but it cannot
 * properly determine if UTf-8 can be supported until
 * after the Config class is instantiated.
 *
 */
	$UNI =& load_class('UTF8', 'core', 'CI');	
	//print_out($UNI);
/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
	$RTR =& load_class('Router', 'core');
	//print_out($RTR);
/*
 * ------------------------------------------------------
 * Load the security class for xss and csrf support 
 * ------------------------------------------------------
 */
	$SEC =& load_class('Security', 'core', 'CI');
	//print_out($SEC);
/*
 * ------------------------------------------------------
 * Load the security class for xss and csrf support
 * ------------------------------------------------------
 */
	$IN =& load_class('Input', 'core', 'CI');	
	//print_out($IN);
/*
 * ------------------------------------------------------
 *  Instantiate the output class
 * ------------------------------------------------------
 */
	$OUT =& load_class('Output', 'core', 'CI');
	//print_out($OUT);

/*
 * ------------------------------------------------------
 *	Is there a valid cache file? If so, we're done...
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('cache_override') === FALSE && $OUT->_display_cache($CFG, $URI) === TRUE)
	{
		exit;
	}

/*
 * ------------------------------------------------------
 *  Load the Language class
 * ------------------------------------------------------
 */
	$LANG =& load_class('Lang', 'core');	
	//print_out($LANG);

	// Mark a o2system benchmark end point
	$BM->end('o2system');

/*
 * Borrowed concept from CodeIgniter 3.0-dev 
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 */
	function &get_instance()
	{
		return O2_Controller::get_instance();
	}

	$request_controller = $URI->request->controller->name;
	$request_method = $URI->request->method;

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('pre_controller');	

/* 
 * ------------------------------------------------------
 *  Instantiate the requested controller
 * ------------------------------------------------------
 */
	// Mark a start point so we can benchmark the controller
	$BM->start('controller');

	$O2 = new $request_controller();
	//print_out($BM);

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller_constructor');

/*
 * ------------------------------------------------------
 *  Call the requested method
 * ------------------------------------------------------
 */	
	call_user_func_array(array(&$O2, $request_method), $URI->request->params);

	// Mark a benchmark end point
	$BM->end('controller');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('display_override') === FALSE)
	{
		$OUT->_display();
	}

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_system');	
	
/*
 * ------------------------------------------------------
 *  Close the DB connection if one exists
 * ------------------------------------------------------
 */
	if (class_exists('CI_DB') AND isset($O2->db))
	{
		$O2->db->close();
	}

/* End of file O2System.php */
/* Location: ./system/core/O2System.php */