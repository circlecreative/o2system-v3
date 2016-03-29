<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * System Initialization
 *
 * Loads the base classes and executes the request.
 * The system Initialization sequence concept process is based on CodeIgniter 3.0-dev
 *
 * @package        O2System
 * @subpackage     system/core
 * @category       Bootstrap
 * @author         Steeven Andrian Salim
 * @link           http://circle-creative.com/products/o2system/user-guide/core/system.html
 */

/*
 * ---------------------------------------------------------------
 * ERROR REPORTING
 * ---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch ( ENVIRONMENT )
{
	case 'development':
		error_reporting( -1 );
		ini_set( 'display_errors', 1 );
		break;
	case 'testing':
	case 'production':
		ini_set( 'display_errors', 0 );
		error_reporting( E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED );
		break;
	default:
		header( 'HTTP/1.1 503 Service Unavailable.', TRUE, 503 );
		echo 'The application environment is not set correctly.';
		exit( 1 ); // EXIT_ERROR
}

/*
 *---------------------------------------------------------------
 * SYSTEM PATH
 *---------------------------------------------------------------
 */
// Is the system path correct?
if ( ! is_dir( ROOTPATH . DIR_SYSTEM . DIRECTORY_SEPARATOR ) )
{
	header( 'HTTP/1.1 503 Service Unavailable.', TRUE, 503 );
	echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: ' . pathinfo( __FILE__, PATHINFO_BASENAME );
	exit( 3 ); // EXIT_CONFIG
}

// Path to the system folder
if ( ! defined( 'SYSTEMPATH' ) )
{
	define( 'SYSTEMPATH', str_replace( "\\", DIRECTORY_SEPARATOR, ROOTPATH . DIR_SYSTEM . DIRECTORY_SEPARATOR ) );
}

// Is the apps path correct?
if ( ! is_dir( ROOTPATH . DIR_APPLICATIONS . DIRECTORY_SEPARATOR ) )
{
	header( 'HTTP/1.1 503 Service Unavailable.', TRUE, 503 );
	echo 'Your apps folder path does not appear to be set correctly. Please open the following file and correct this: ' . pathinfo( __FILE__, PATHINFO_BASENAME );
	exit( 3 ); // EXIT_CONFIG
}

/*
 *---------------------------------------------------------------
 * APPLICATIONS PATH
 *---------------------------------------------------------------
 */
if ( defined( 'STDIN' ) )
{
	chdir( dirname( __FILE__ ) );
}

// Path to the Apps folder
if ( ! defined( 'APPSPATH' ) )
{
	define( 'APPSPATH', ROOTPATH . DIR_APPLICATIONS . DIRECTORY_SEPARATOR );
}


/*
 * ------------------------------------------------------
 *  Load the system constants
 * ------------------------------------------------------
 */
require( SYSTEMPATH . 'config/constants.php' );

/**
 * System Version
 *
 * @var string
 *
 */
define( 'SYSTEM_NAME', 'O2System' );
define( 'SYSTEM_VERSION', '3.0.0' );

/*
 * ------------------------------------------------------
 *  Load O2System Composer Autoload
 * ------------------------------------------------------
 */
require( SYSTEMPATH . 'vendor/autoload.php' );

/*
 * ------------------------------------------------------
 *  Load O2System Loader Class
 * ------------------------------------------------------
 */
require( SYSTEMPATH . 'core/Loader.php' );

/*
 * ------------------------------------------------------
 *  Load O2System Gears Helper
 * ------------------------------------------------------
 */
require( SYSTEMPATH . 'helpers/gears_helper.php' );

/**
 * O2System Core Class Initializer
 *
 * @package        O2System
 * @subpackage     system/core
 * @category       Bootstrap
 * @author         Steeven Andrian Salim
 * @link           http://circle-creative.com/products/o2system/user-guide/core/system.html
 */
class O2System extends \O2System\Glob
{
	/**
	 * Constants of Logger Types
	 *
	 * @access  public
	 * @var     integer
	 */
	const L_DISABLED  = 0;
	const L_DEBUG     = 1;
	const L_INFO      = 2;
	const L_NOTICE    = 3;
	const L_WARNING   = 4;
	const L_ALERT     = 5;
	const L_ERROR     = 6;
	const L_EMERGENCY = 7;
	const L_CRITICAL  = 8;
	const L_ALL       = 9;

	public static $registry;
	public static $active;

	protected $_object_maps = array(
		'loader' => 'load',
		'logger' => 'log',
	);

	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __reconstruct()
	{
		parent::__reconstruct();

		/*
		 * ------------------------------------------------------
		 *  Set Default Active Language by Config
		 * ------------------------------------------------------
		 */
		static::$language->setActive( \O2System::$config[ 'language' ] );

		/*
		 * ------------------------------------------------------
		 *  Initialized System Cache
		 * ------------------------------------------------------
		 */
		$this->cache = new \O2System\Cache( \O2System::$config[ 'cache' ] );

		// Register Application Namespace
		$this->load->addNamespace( $this->config[ 'namespace' ], APPSPATH );

		/*
		 * ------------------------------------------------------
		 *  Initialized System Core Registry
		 * ------------------------------------------------------
		 */
		static::$registry = new \O2System\Registry();

		/*
		 * ------------------------------------------------------
		 *  Initialized System Core Active Registry
		 * ------------------------------------------------------
		 */
		static::$active = new \O2System\Glob\ArrayObject();

		/*
		 * ------------------------------------------------------
		 *  Initialized System Benchmarking Class
		 * ------------------------------------------------------
		 */
		$this->benchmark = new \O2System\Gears\Benchmark;
		$this->benchmark->start();

		// Mark o2system start point
		$this->benchmark->start( 'o2system_core' );

		/*
		 * ------------------------------------------------------
		 *  Instantiate the URI class
		 * ------------------------------------------------------
		 */
		$this->utf8 = new \O2System\UTF8;

		/*
		 * ------------------------------------------------------
		 *  Instantiate the URI class
		 * ------------------------------------------------------
		 */
		$this->uri = new \O2System\URI;

		/*
		 * ------------------------------------------------------
		 *  Instantiate the Security class
		 * ------------------------------------------------------
		 */
		$this->security = new \O2System\Security;

		/*
		 * ------------------------------------------------------
		 *  Instantiate the Input class
		 * ------------------------------------------------------
		 */
		$this->input = new \O2System\Input;

		/*
		 * ------------------------------------------------------
		 *  Reset Environment based on Debug Ips
		 * ------------------------------------------------------
		 */
		$this->exceptions->setDebugIps( \O2System::$config[ 'debug_ips' ], $this->input->ip_address() );

		/*
		 * ------------------------------------------------------
		 *  Initialized Loader Autoload
		 * ------------------------------------------------------
		 */
		$this->load->__autoload();
	}

	/**
	 * Boot
	 *
	 * Boot the system process
	 *
	 * @access  public
	 */
	public function boot()
	{
		/*
		 * ------------------------------------------------------
		 *  We Doesn't need to boot-up anything else in console
		 * ------------------------------------------------------
		 */
		if ( defined( 'CONSOLE_PATH' ) ) return;

		/*
		 * ------------------------------------------------------
		 *  Set Active Domain and Sub Domain
		 * ------------------------------------------------------
		 */

		$parse_domain = parse_domain();

		static::$active[ 'domain' ] = $parse_domain->domain;
		static::$active['sub_domain'] = $parse_domain->sub_domain;

		/*
		 * ------------------------------------------------------
		 *  Set Active System Language
		 * ------------------------------------------------------
		 */
		if ( static::$active->offsetExists( 'language' ) )
		{
			static::$language->setActive( static::$active[ 'language' ]->parameter );
		}

		/*
		 * ------------------------------------------------------
		 *  Instantiate the Hooks class
		 * ------------------------------------------------------
		 */
		$this->hooks = new \O2System\Hooks;

		/*
		 * ------------------------------------------------------
		 *  Initialized System Database
		 * ------------------------------------------------------
		*/
		$this->load->database();

		/*
		 * ------------------------------------------------------
		 *  Is there a "pre_system" hook?
		 * ------------------------------------------------------
		 */
		$this->hooks->call( 'pre_system' );

		/*
		 * ------------------------------------------------------
		 *  Initialized Session Class
		 * ------------------------------------------------------
		 */
		$this->session = new \O2System\Session( array(
			                                        'storage' => static::$config[ 'session' ],
			                                        'cookie'  => static::$config[ 'cookie' ],
		                                        ) );

		/*
		 * ------------------------------------------------------
		 *  Re-load and Load the rest of core classes
		 * ------------------------------------------------------
		 */
		$namespaces = [ static::$config[ 'namespace' ] . 'Core\\', 'O2System\\' ];

		if ( static::$active->offsetExists( 'module' ) )
		{
			$parents = static::$registry->find_parents( static::$active[ 'module' ] );

			if ( ! empty( $parents ) )
			{
				foreach ( $parents as $parent )
				{
					array_unshift( $namespaces, $parent->namespace . 'Core\\' );
				}
			}

			array_unshift( $namespaces, static::$active[ 'module' ]->namespace . 'Core\\' );
		}

		$core_classes = array(
			'Router',
			'Security',
			'Input',
			'View',
		);

		foreach ( $core_classes as $core_class )
		{
			foreach ( $namespaces as $namespace )
			{
				$namespace = '\\' . $namespace;
				$core_class_name = $namespace . $core_class;
				$core_object_name = strtolower( $core_class );

				if ( class_exists( $core_class_name ) )
				{
					$this->{$core_object_name} = new $core_class_name;
					break;
				}
			}

			if ( isset( $this->{$core_object_name} ) ) continue;
		}

		/*
		 * ------------------------------------------------------
		 *  Is there any server cache file? If so, we're done...
		 * ------------------------------------------------------
		 */
		if ( $this->view->cache() )
		{
			exit();
		}

		// Mark o2system benchmark end point
		$this->benchmark->stop( 'o2system_core' );

		/*
		 * ------------------------------------------------------
		 *  Is there a "pre_controller" hook?
		 * ------------------------------------------------------
		 */
		$this->hooks->call( 'pre_controller' );

		/*
		 * ------------------------------------------------------
		 *  Instantiate the requested controller
		 * ------------------------------------------------------
		 */
		// Mark a controller benchmark
		$this->benchmark->start( 'post_controller' );

		if ( empty( static::$active[ 'controller' ] ) )
		{
			if ( static::$router->show_404() === FALSE )
			{
				$this->exceptions->show404();
			}
		}

		$class = static::$active[ 'controller' ]->class;
		$method = static::$active[ 'controller' ]->method;
		$params = static::$active[ 'controller' ]->params;

		$this->controller = new $class();

		/*
		 * ------------------------------------------------------
		 *  Is there a "post_controller_constructor" hook?
		 * ------------------------------------------------------
		 */
		$this->hooks->call( 'post_controller_constructor' );

		/*
		 * ------------------------------------------------------
		 *  Call the requested method
		 * ------------------------------------------------------
		 */
		if ( method_exists( $this->controller, '_route' ) )
		{
			if ( $method === '_route' )
			{
				if ( reset( $params ) === static::$active[ 'controller' ]->parameter )
				{
					array_shift( $params ); // remove controller params
				}

				$method = reset( $params );
				array_shift( $params ); // remove method params
				$params = array_values( $params );

				if ( $this->input->is_ajax_request() )
				{
					$method = '__' . $method;
				}

				$this->controller->_route( $method, $params );
			}
		}
		else
		{
			if ( $this->input->is_ajax_request() )
			{
				$method = '__' . $method;
			}

			call_user_func_array( array( &$this->controller, $method ), $params );
		}

		// Mark a controller benchmark end point
		$this->benchmark->stop( 'post_controller' );

		/*
		 * ------------------------------------------------------
		 *  Is there a "post_controller" hook?
		 * ------------------------------------------------------
		 */
		$this->hooks->call( 'post_controller' );

		/*
		 * ------------------------------------------------------
		 *  Send the final rendered output to the browser
		 * ------------------------------------------------------
		 */
		$this->view->output();

		/*
		 * ------------------------------------------------------
		 *  Is there a "post_system" hook?
		 * ------------------------------------------------------
		 */
		$this->hooks->call( 'post_system' );
	}
}
