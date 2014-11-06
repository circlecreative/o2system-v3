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
 * System Bootstrap
 *
 * Loads the base classes and executes the request.
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Bootstrap
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/system.html
 */

if (defined('ENVIRONMENT'))
{
	switch (ENVIRONMENT)
	{
		case 'development':
			ini_set('display_errors','on');
			error_reporting(E_ALL);
		break;
	
		case 'testing':
			ini_set('display_errors','on');
			error_reporting(0);
		break;

		case 'production':
			ini_set('display_errors','off');
			error_reporting(0);
		break;

		default:
			exit('The application environment is not set correctly.');
	}
}

/**
 * System Initialization File
 *
 * Loads the base classes and executes the request.
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Bootstrap
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide.html
 */

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
	define('SYS_VERSION', '2.0');

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
 *  Load the Benchmark Class
 * ------------------------------------------------------
 */
	$BM =& load_class('Benchmark', 'core');	
	$BM->mark('core_system:execute_time_start');

/*
 * ------------------------------------------------------
 *  Load the Path Class
 * ------------------------------------------------------
 */
	$PATH =& load_class('Path', 'core');
	//print_code($PATH);
/*
 * ------------------------------------------------------
 *  Load the URI Class
 * ------------------------------------------------------
 */
	$URI =& load_class('URI', 'core');
	//print_code($URI);

/*
 * ------------------------------------------------------
 *  Instantiate the config class
 * ------------------------------------------------------
 */
	$CFG =& load_class('Config', 'core');		
	//print_code($CFG);
/*
 * ------------------------------------------------------
 *  Instantiate the UTF-8 class
 * ------------------------------------------------------
 *
 * Note: Order here is rather important as the UTF-8
 * class needs to be used very early on, but it cannot
 * properly determine if UTf-8 can be supported until
 * after the Config class is instantiated.
 *
 */
	$UNI =& load_class('UTF8', 'core');	
	//print_code($UNI);
/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
	$RTR =& load_class('Router', 'core');
	//print_code($RTR);
/*
 * -----------------------------------------------------
 * Load the security class for xss and csrf support
 * -----------------------------------------------------
 */
	$SEC =& load_class('Security', 'core');
	//print_code($SEC);
/*
 * -----------------------------------------------------
 * Load the security class for xss and csrf support
 * -----------------------------------------------------
 */
	$IN =& load_class('Input', 'core');	
	//print_code($IN);
/*
 * -----------------------------------------------------
 * Load the Session Class
 * -----------------------------------------------------
 */
	$SES =& load_class('Session', 'core');			
	//print_code($SES);
/*
 * ------------------------------------------------------
 *  Instantiate the output class
 * ------------------------------------------------------
 */
	$OUT =& load_class('Output', 'core');
	//print_code($OUT);
/*
 * ------------------------------------------------------
 *  Load the Language class
 * ------------------------------------------------------
 */
	$LANG =& load_class('Lang', 'core');	
	print_code($LANG);

	// Mark a benchmark end point
	$BM->mark('core_system:execute_time_end');

	$SES->set_userdata('testing','et');

	print_code($SES);

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 */
	// Load the base controller class
	require BASEPATH.'core/Controller.php';

	function &get_instance()
	{
		return CI_Controller::get_instance();
	}


	if (file_exists(APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php'))
	{
		require APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php';
	}

	// Load the local application controller
	// Note: The Router class automatically validates the controller path using the router->_validate_request().
	// If this include fails it means that the default controller in the Routes.php file is not resolving to something valid.
	if ( ! file_exists(APPPATH.'controllers/'.$RTR->fetch_directory().$RTR->fetch_class().'.php'))
	{
		show_error('Unable to load your default controller. Please make sure the controller specified in your Routes.php file is valid.');
	}

	include(APPPATH.'controllers/'.$RTR->fetch_directory().$RTR->fetch_class().'.php');

	// Set a mark point for benchmarking
	$BM->mark('loading_time:_base_classes_end');

/* End of file O2System.php */
/* Location: ./system/core/O2System.php */