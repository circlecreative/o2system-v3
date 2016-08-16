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
switch ( strtoupper( ENVIRONMENT ) )
{
	case 'DEVELOPMENT':
		error_reporting( -1 );
		ini_set( 'display_errors', 1 );
		break;
	case 'TESTING':
	case 'PRODUCTION':
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

// Path to the Public folder
if ( ! defined( 'PUBLICPATH' ) )
{
	define( 'PUBLICPATH', ROOTPATH . DIR_PUBLIC . DIRECTORY_SEPARATOR );
}

/*
 * ------------------------------------------------------
 *  Load Config Constants
 * ------------------------------------------------------
 */
require( SYSTEMPATH . 'Config/constants.php' );

/*
 * ------------------------------------------------------
 *  Load Common Helper
 * ------------------------------------------------------
 */
require_once( SYSTEMPATH . '/Helpers/' . 'Common.php' );

/*
 * ------------------------------------------------------
 *  Load Inflector Helper
 * ------------------------------------------------------
 */
require_once( SYSTEMPATH . '/Helpers/' . 'Inflector.php' );

/**
 * System Version
 *
 * @var string
 *
 */
define( 'SYSTEM_NAME', 'O2System PHP Framework' );
define( 'SYSTEM_VERSION', '4.0.0-dev' );

/*
 * ------------------------------------------------------
 *  Load O2System\Core\Loader Class
 * ------------------------------------------------------
 */
require( SYSTEMPATH . 'Core/Loader.php' );

/*
 * ------------------------------------------------------
 *  Load Gears Helper
 * ------------------------------------------------------
 */
require( SYSTEMPATH . 'Gears/Helper.php' );

/**
 * O2System Core Class Initializer
 *
 * @package        O2System
 * @subpackage     system/core
 * @category       Bootstrap
 * @author         Steeven Andrian Salim
 * @link           http://circle-creative.com/products/o2system/user-guide/core/system.html
 */
class O2System
{
	/**
	 * O2System Core Loader
	 *
	 * @type \O2System\Core\Loader
	 */
	public static $load;
	/**
	 * O2System Core Config
	 *
	 * @type \O2System\Core\Config
	 */
	public static $config;
	/**
	 * O2System Core Language
	 *
	 * @type \O2System\Core\Language
	 */
	public static $language;
	/**
	 * O2System Core Environment
	 *
	 * @type \O2System\Core\Environment
	 */
	public static $environment;
	/**
	 * O2System Loader
	 *
	 * @var \O2System\Core\Logger
	 */
	public static $log;
	/**
	 * O2System Benchmark
	 *
	 * @var \O2System\Core\Benchmark
	 */
	public static $benchmark;
	/**
	 * O2System Cache
	 *
	 * @var \O2System\Cache
	 */
	public static $cache;
	/**
	 * O2System DB
	 *
	 * @var \O2System\DB
	 */
	public static $db;
	/**
	 * O2System Session
	 *
	 * @var \O2System\Session
	 */
	public static $session;
	/**
	 * O2System Security
	 *
	 * @var \O2System\Security
	 */
	public static $security;
	/**
	 * O2System Registry
	 *
	 * @var \O2System\Registry
	 */
	public static $registry;
	/**
	 * O2System Request
	 *
	 * @var \O2System\Core\Request
	 */
	public static $request;
	/**
	 * O2System Active Registry
	 *
	 * @var \O2System\Registry\Collections\Active
	 */
	public static $active;
	/**
	 * O2System Models
	 *
	 * @var \O2System\Registry\Collections\Models
	 */
	public static $models;
	/**
	 * O2System Libraries
	 *
	 * @var \O2System\Registry\Collections\Libraries
	 */
	public static $libraries;
	/**
	 * O2System Hooks
	 *
	 * @var \O2System\Core\Hooks
	 */
	public static $hooks;
	/**
	 * O2System View
	 *
	 * @var \O2System\Core\View
	 */
	public static $view;
	/**
	 * O2System View
	 *
	 * @var \O2System\Core\Controller
	 */
	public static $controller;
	/**
	 * Instance
	 *
	 * @access  protected
	 * @type    O2System   The reference to *Singleton* instance of this class
	 */
	protected static $instance;
	/**
	 * O2System Object Maps
	 *
	 * @type array
	 */
	protected static $objectMaps = [
		'loader' => 'load',
		'logger' => 'log',
	];

	/**
	 * O2System constructor.
	 */
	public function __construct()
	{
		/**
		 * ------------------------------------------------------
		 *  Set Instance by Class Reference
		 * ------------------------------------------------------
		 */
		static::$instance =& $this;

		/**
		 * ------------------------------------------------------
		 *  Instantiate Core Loader
		 * ------------------------------------------------------
		 */
		static::$load = new O2System\Core\Loader;

		// Reconstruct
		$this->__reconstruct();
	}

	private function __reconstruct()
	{
		/**
		 * ------------------------------------------------------
		 *  Instantiate System Benchmarking Class
		 * ------------------------------------------------------
		 */
		static::$benchmark = new \O2System\Core\Benchmark;

		// Start Benchmarking
		static::$benchmark->start();

		// Create O2System Core Benchmarking Starting Point
		static::$benchmark->start( 'core' );

		/**
		 * ------------------------------------------------------
		 *  Instantiate Core Config
		 * ------------------------------------------------------
		 */
		static::$config = new O2System\Core\Config;

		/**
		 * ------------------------------------------------------
		 *  Instantiate Core Logger
		 * ------------------------------------------------------
		 */
		static::$log = new O2System\Core\Logger;

		/**
		 * ------------------------------------------------------
		 *  Instantiate Core Language
		 * ------------------------------------------------------
		 */
		static::$language = new O2System\Core\Language;

		/**
		 * ------------------------------------------------------
		 *  Instantiate Core Environment
		 * ------------------------------------------------------
		 */
		static::$environment = new O2System\Core\Environment;

		/**
		 * ------------------------------------------------------
		 *  Instantiate System Cache
		 * ------------------------------------------------------
		 */
		static::$cache = new \O2System\Cache();

		/**
		 * ------------------------------------------------------
		 *  Instantiate System Database Class
		 * ------------------------------------------------------
		 */
		static::$db = new \O2System\DB();

		/**
		 * ------------------------------------------------------
		 *  Instantiate System Core Registry
		 * ------------------------------------------------------
		 */
		static::$registry = new \O2System\Registry();

		/**
		 * ------------------------------------------------------
		 *  Instantiate System Session Class
		 * ------------------------------------------------------
		 */

		if ( is_cli() === FALSE )
		{
			static::$session = new \O2System\Session();

			/**
			 * ------------------------------------------------------
			 *  Performing Session Start
			 * ------------------------------------------------------
			 */
			static::$session->start();

			/**
			 * ------------------------------------------------------
			 *  Instantiate the Security class
			 * ------------------------------------------------------
			 */
			static::$security = new \O2System\Security;

			/**
			 * ------------------------------------------------------
			 *  Instantiate System Core Request
			 * ------------------------------------------------------
			 */
			static::$request = new \O2System\Core\Request();

			/**
			 * ------------------------------------------------------
			 *  Performing System Environment Debug Stage Event Listener
			 * ------------------------------------------------------
			 */
			static::$environment->debugStageEventListener();

			/**
			 * ------------------------------------------------------
			 *  Instantiate System Core Active Registry
			 * ------------------------------------------------------
			 */
			static::$active = new \O2System\Registry\Collections\Active();

			/**
			 * ------------------------------------------------------
			 *  Instantiate System Core Models Collections
			 * ------------------------------------------------------
			 */
			static::$models = new \O2System\Registry\Collections\Models();

			/**
			 * ------------------------------------------------------
			 *  Instantiate System Core Libraries Collections
			 * ------------------------------------------------------
			 */
			static::$libraries = new \O2System\Registry\Collections\Libraries();

			/**
			 * ------------------------------------------------------
			 *  Initialize System Request URI
			 * ------------------------------------------------------
			 */
			static::$request->uri->initialize();

			/**
			 * ------------------------------------------------------
			 *  Instantiate System View
			 * ------------------------------------------------------
			 */
			static::$view = new \O2System\Core\View();
		}
		else
		{
			/**
			 * ------------------------------------------------------
			 *  Instantiate System Core Registry
			 * ------------------------------------------------------
			 */
			static::$registry = new \O2System\Registry();
		}
	}

	public static function __callStatic( $method, $args = [ ] )
	{
		return static::instance()->__call( $method, $args );
	}

	public function __call( $method, $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( [ $this, $method ], $args );
		}
		elseif ( method_exists( $this, camelcase( $method ) ) )
		{
			return call_user_func_array( [ $this, camelcase( $method ) ], $args );
		}
		elseif ( method_exists( $this, 'get' . studlycapcase( $method ) ) )
		{
			return call_user_func_array( [ $this, 'get' . studlycapcase( $method ) ], $args );
		}

		return NULL;
	}

	final public static function instance()
	{
		if ( is_null( static::$instance ) )
		{
			static::$instance = new static;
		}

		return static::$instance;
	}

	// ------------------------------------------------------------------------

	public function __isset( $property )
	{
		return (bool) isset( $this->{$property} );
	}

	public function __get( $key )
	{
		$key = camelcase( $key );

		if ( property_exists( $this, $key ) )
		{
			if ( isset( static::${$key} ) )
			{
				return static::${$key};
			}

			return $this->{$key};
		}

		return NULL;
	}

	// ------------------------------------------------------------------------

	public function __set( $property, $value )
	{
		$this->{$property} =& $value;
	}

	// ------------------------------------------------------------------------

	/**
	 * Boot
	 *
	 * Boot the system process
	 *
	 * @access  public
	 */
	public function start()
	{
		/**
		 * ------------------------------------------------------
		 *  We Doesn't need to do anything else in cli
		 * ------------------------------------------------------
		 */
		if ( is_cli() )
		{
			return;
		}

		/**
		 * ------------------------------------------------------
		 *  Try to send view output from cache
		 * ------------------------------------------------------
		 */
		if ( static::$view->output->cache() )
		{
			exit( 0 );
		}
		else
		{
			/**
			 * ------------------------------------------------------
			 *  Performing System Loader Autoload
			 * ------------------------------------------------------
			 */
			static::$load->__autoload();

			/**
			 * ------------------------------------------------------
			 *  Instantiate the Hooks class
			 * ------------------------------------------------------
			 */
			static::$hooks = new \O2System\Core\Hooks;

			/**
			 * ------------------------------------------------------
			 *  Try to reload the core classes using applications/core
			 * ------------------------------------------------------
			 */
			$namespaces = static::$request->namespaces->getArrayReverse();

			// Load Overriding Core Classes Configuration
			$core_classes = require( SYSTEMPATH . 'Config/overriding_core_classes.php' );

			foreach ( $core_classes as $core_class )
			{
				foreach ( $namespaces as $namespace )
				{
					$namespace        = '\\' . $namespace . 'Core\\';
					$core_class_name  = $namespace . $core_class;
					$core_object_name = underscore( $core_class );

					$core_object_name = str_replace(
						[
							'_handlers_',
						],
						[
							'->',
						],
						$core_object_name
					);

					if ( class_exists( $core_class_name ) )
					{
						if ( strpos( $core_object_name, '->' ) )
						{
							$x_core_object_name = explode( '->', $core_object_name );

							static::${$x_core_object_name[ 0 ]}->{$x_core_object_name[ 1 ]} = new $core_class_name;
						}
						else
						{
							static::${$core_object_name} = new $core_class_name;
						}

						break;
					}
				}

				if ( isset( $this->{$core_object_name} ) )
				{
					continue;
				}
			}

			/**
			 * ------------------------------------------------------
			 *  Initialize System Core View
			 * ------------------------------------------------------
			 */
			static::$view->initialize();
		}

		// Create O2System Core Benchmarking Ending Point
		static::$benchmark->stop( 'core' );

		/**
		 * ------------------------------------------------------
		 *  Is there a "pre_controller" hook?
		 * ------------------------------------------------------
		 */
		static::$hooks->call( 'pre_controller' );

		// Validating requested controller
		if ( empty( static::$request->controller->method ) )
		{
			if ( static::$request->routing->showError( 404 ) === FALSE )
			{
				static::$request->triggerResponse( 404 );
			}
		}

		if ( isset( static::$request->controller ) )
		{
			$class = static::$request->controller->class;

			if ( class_exists( $class ) )
			{
				//pre_close( TRUE );
				// Create Controller Benchmarking Starting Point
				static::$benchmark->start( 'post_controller' );

				/**
				 * ------------------------------------------------------
				 *  Instantiate the requested controller
				 * ------------------------------------------------------
				 */
				static::$controller = new $class();

				/**
				 * ------------------------------------------------------
				 *  Is there a "post_controller_constructor" hook?
				 * ------------------------------------------------------
				 */
				static::$hooks->call( 'post_controller_constructor' );

				if ( static::$request->controller->params instanceof ArrayObject )
				{
					$params = static::$request->controller->params->getArrayCopy();
				}
				else
				{
					$params = (array) static::$request->controller->params;
				}

				/**
				 * ------------------------------------------------------
				 *  Call the requested method
				 * ------------------------------------------------------
				 */
				static::$controller->__call( static::$request->controller->method, $params );

				// Create Controller Benchmarking Ending Point
				static::$benchmark->stop( 'post_controller' );

				/**
				 * ------------------------------------------------------
				 *  Is there a "post_controller" hook?
				 * ------------------------------------------------------
				 */
				static::$hooks->call( 'post_controller' );

				/**
				 * ------------------------------------------------------
				 *  Send the final rendered output to the browser
				 * ------------------------------------------------------
				 */
				static::$view->output->render();

				/**
				 * ------------------------------------------------------
				 *  Is there a "post_system" hook?
				 * ------------------------------------------------------
				 */
				static::$hooks->call( 'post_system' );
			}
			else
			{
				throw new \O2System\Core\Exception( 'E_SYSTEM_CANNOT_FIND_CONTROLLER', 101, [ $class ] );
			}
		}
		else
		{
			//pre_close( TRUE );
			throw new \O2System\Core\Exception( 'E_SYSTEM_CANNOT_FIND_CONTROLLER', 101 );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @access  private
	 * @return  void
	 */
	private function __clone()
	{

	}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @access  private
	 * @return  void
	 */
	private function __wakeup()
	{

	}
}