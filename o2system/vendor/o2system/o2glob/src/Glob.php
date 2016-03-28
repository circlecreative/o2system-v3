<?php
/**
 * O2Glob
 *
 * Global Common Class Libraries for PHP 5.4 or newer
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
 * @license        http://circle-creative.com/products/o2glob/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------
namespace O2System
{
	if ( ! defined( 'GLOB_PATH' ) )
	{
		define( 'GLOB_PATH', __DIR__ . DIRECTORY_SEPARATOR );
	}

	require_once( GLOB_PATH . 'Helpers/' . 'Common.php' );
	require_once( GLOB_PATH . 'Helpers/' . 'Inflector.php' );

	/**
	 * Class Glob
	 *
	 * @package O2System
	 */
	class Glob
	{
		use Glob\Interfaces\SingletonInterface;
		use Glob\Interfaces\StorageInterface;

		static $config;
		static $language;

		// ------------------------------------------------------------------------

		/**
		 * Glob Class Constructor
		 *
		 * @access public
		 */
		public function __reconstruct()
		{
			foreach ( [ 'Loader', 'Config', 'Language', 'Exceptions', 'Logger' ] as $class )
			{
				$class_name = $class;
				$object_name = strtolower( $class );

				if ( isset( $this->_object_maps[ $object_name ] ) )
				{
					$object_name = $this->_object_maps[ $object_name ];
				}

				if ( class_exists( 'O2System', FALSE ) )
				{
					$class_name = '\O2System\\' . $class;
				}

				if ( class_exists( $class_name ) )
				{
					if ( in_array( $class, [ 'Config', 'Language' ] ) )
					{
						static::${$object_name} = new $class_name();
					}
					else
					{
						$this->{$object_name} = new $class_name();

						if ( in_array( $class, [ 'Loader', 'Exceptions' ] ) )
						{
							$this->{$object_name}->registerHandler();
						}
					}
				}
			}
		}
	}
}

namespace O2System\Glob
{
	/**
	 * Class ArrayObject
	 *
	 * @package O2System\Glob
	 */
	class ArrayObject extends \ArrayObject
	{
		/**
		 * ArrayObject constructor.
		 *
		 * @param array $array
		 * @param int   $option
		 */
		public function __construct( $array = array(), $option = \ArrayObject::ARRAY_AS_PROPS )
		{
			parent::__construct( $array, $option );
		}

		// ------------------------------------------------------------------------

		/**
		 * __set
		 *
		 * ArrayObject __set Magic Method
		 *
		 * @param $index
		 * @param $value
		 */
		public function __set( $index, $value )
		{
			return $this->offsetSet( $index, $value );
		}

		// ------------------------------------------------------------------------

		/**
		 * ArrayObject __get Magic method
		 *
		 * @param $property
		 *
		 * @return mixed
		 */
		public function __get( $property )
		{
			return $this->offsetGet( $property );
		}

		// ------------------------------------------------------------------------

		/**
		 * __call
		 *
		 * ArrayObject __call Magic method
		 *
		 * @param       $method
		 * @param array $args
		 *
		 * @return array|mixed|object
		 */
		public function __call( $method, $args = array() )
		{
			if ( method_exists( $this, $method ) )
			{
				return call_user_func_array( array( $this, $method ), $args );
			}
			elseif ( empty( $args ) )
			{
				return $this->offsetGet( $method );
			}
			elseif ( $this->offsetExists( $method ) )
			{
				// Let's get the registry values
				$registry = $this->offsetGet( $method );

				// List arguments
				@list( $index, $action ) = $args;

				if ( isset( $registry->{$index} ) )
				{
					$value = $registry->{$index};
				}
				elseif ( isset( $registry[ $index ] ) )
				{
					$value = $registry[ $index ];
				}

				if ( isset( $action ) )
				{
					if ( is_callable( $action ) )
					{
						return $action( $value );
					}
					elseif ( function_exists( $action ) )
					{
						$value = is_object( $value ) ? get_object_vars( $value ) : $value;

						return array_map( $action, $value );
					}
					elseif ( in_array( $action, [ 'array', 'object', 'keys', 'values' ] ) )
					{
						switch ( $action )
						{
							default:
							case 'array':
								$value = ( is_array( $value ) ? $value : (array) $value );
								break;
							case 'object':
								$value = ( is_object( $value ) ? $value : (object) $value );
								break;
							case 'keys':
								$value = is_object( $value ) ? get_object_vars( $value ) : $value;
								$value = array_keys( $value );
								break;
							case 'values':
								$value = is_object( $value ) ? get_object_vars( $value ) : $value;
								$value = array_values( $value );
								break;
						}

						if ( isset( $args[ 2 ] ) )
						{
							if ( is_callable( $args[ 2 ] ) )
							{
								return $args[ 2 ]( $value );
							}
							elseif ( function_exists( $args[ 2 ] ) )
							{
								return array_map( $args[ 2 ], $value );
							}
						}
						else
						{
							return $value;
						}
					}
					elseif ( in_array( $action, [ 'json', 'serialize', 'flatten', 'flatten_keys', 'flatten_values' ] ) )
					{
						switch ( $action )
						{
							default:
							case 'json':
								return json_encode( $value );
								break;
							case 'serialize':
								return serialize( $value );
								break;
							case 'flatten':
								$value = is_object( $value ) ? get_object_vars( $value ) : $value;
								$glue = isset( $args[ 2 ] ) ? $args[ 2 ] : ', ';

								foreach ( $value as $key => $val )
								{
									if ( is_bool( $val ) )
									{
										$val = $val === TRUE ? 'true' : 'false';
									}

									if ( is_numeric( $key ) )
									{
										$result[] = $val;
									}
									elseif ( is_string( $key ) )
									{
										if ( is_array( $val ) )
										{
											$val = implode( $glue, $val );
										}

										$result[] = $key . ' : ' . $val;
									}
								}

								return implode( $glue, $result );

								break;
							case 'flatten_keys':
								$value = is_object( $value ) ? get_object_vars( $value ) : $value;
								$glue = isset( $args[ 2 ] ) ? $args[ 2 ] : ', ';

								return implode( $glue, array_keys( $value ) );
								break;
							case 'flatten_values':
								$value = is_object( $value ) ? get_object_vars( $value ) : $value;
								$glue = isset( $args[ 2 ] ) ? $args[ 2 ] : ', ';

								foreach ( array_values( $value ) as $val )
								{
									if ( is_bool( $val ) )
									{
										$val = $val === TRUE ? 'true' : 'false';
									}

									$result[] = $val;
								}

								return implode( $glue, $result );
								break;
						}
					}
				}
				elseif ( isset( $value ) )
				{
					return $value;
				}
				else
				{
					return $registry;
				}
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * __callStatic
		 *
		 * ArrayObject __callStatic Magic method
		 *
		 * @param $method
		 * @param $args
		 *
		 * @return array|mixed|object
		 */
		public static function __callStatic( $method, $args )
		{
			return self::__call( $method, $args );
		}

		// ------------------------------------------------------------------------

		/**
		 * __isEmpty
		 *
		 * Validate ArrayObject is empty
		 *
		 * @return bool
		 */
		public function __isEmpty()
		{
			return (bool) ( count( $this->getArrayCopy() ) == 0 ) ? TRUE : FALSE;
		}

		// ------------------------------------------------------------------------

		/**
		 * __toString
		 *
		 * Returning JSON Encode array copy of storage ArrayObject
		 *
		 * @return string
		 */
		public function __toString()
		{
			return json_encode( $this->getArrayCopy() );
		}

		// ------------------------------------------------------------------------

		/**
		 * __toArray
		 *
		 * Returning array copy of storage ArrayObject
		 *
		 * @return string
		 */
		public function __toArray()
		{
			return $this->getArrayCopy();
		}
	}

	/**
	 * Class Loader
	 *
	 * @package O2System\Glob
	 */
	class Loader
	{
		/**
		 * Loader Configurations
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_config = array(
			'class_paths' => array(
				'interfaces',
				'metadata',
				'factory',
				'drivers',
			),
		);

		/**
		 * List of Loaded Libraries
		 *
		 * @static
		 * @access  protected
		 * @type    array
		 */
		protected static $_libraries_classes = array();

		/**
		 * List of Loaded Models
		 *
		 * @static
		 * @access  protected
		 * @type    array
		 */
		protected static $_models_classes = array();

		/**
		 * List of Loaded Controllers
		 *
		 * @static
		 * @access  protected
		 * @type    array
		 */
		protected static $_controllers_classes = array();

		/**
		 * List of Loaded Helper Files
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_helpers = array();

		/**
		 * Holds all the Prefix Class maps.
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_prefixes_maps = array();

		/**
		 * Holds all the PSR-4 compliant namespaces maps.
		 * These namespaces should be loaded according to the PSR-4 standard.
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_psr_namespace_maps = array();

		/**
		 * List of Packages Paths
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_packages_paths = array();

		// ------------------------------------------------------------------------

		public function __construct()
		{
			$this->addNamespace( 'O2System\\Glob', __DIR__ . DIRECTORY_SEPARATOR );
		}

		// ------------------------------------------------------------------------

		/**
		 * Register
		 *
		 * Register SPL Autoloader
		 *
		 * @param bool $throw
		 * @param bool $prepend
		 *
		 * @access  public
		 */
		public function registerHandler( $throw = TRUE, $prepend = TRUE )
		{
			// Register Autoloader
			spl_autoload_register( array( $this, 'findClass' ), $throw, $prepend );
		}

		// ------------------------------------------------------------------------

		/**
		 * Adds a namespace search path.  Any class in the given namespace will be
		 * looked for in the given path.
		 *
		 * @access  public
		 *
		 * @param   string  the namespace
		 * @param   string  the path
		 *
		 * @return  void
		 */
		public function addNamespace( $namespaces, $path )
		{
			if ( is_array( $namespaces ) )
			{
				foreach ( $namespaces as $namespace => $path )
				{
					$this->addNamespace( $namespace, $path );
				}
			}
			elseif ( is_dir( $path ) )
			{
				$path = str_replace( '/', DIRECTORY_SEPARATOR, $path );

				$namespaces = rtrim( $namespaces, '\\' ) . '\\';

				$path = rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

				$this->_packages_paths[ $namespaces ] = $path;
				$this->_psr_namespace_maps[ $namespaces ] = $path . 'core' . DIRECTORY_SEPARATOR;
				$this->_psr_namespace_maps[ $namespaces . 'Core\\' ] = $path . 'core' . DIRECTORY_SEPARATOR;

				foreach ( $this->_config[ 'class_paths' ] as $class_path )
				{
					if ( is_dir( $path . $class_path ) )
					{
						$this->_psr_namespace_maps[ $namespaces . ucfirst( $class_path ) . '\\' ] = $path . rtrim( $class_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
					}
				}

				// Load Composer
				if ( is_file( $path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' ) )
				{
					require( $path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' );
				}
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Adds a namespace search path.  Any class in the given namespace will be
		 * looked for in the given path.
		 *
		 * @access  public
		 *
		 * @param   string  the namespace
		 * @param   string  the path
		 *
		 * @return  void
		 */
		public function addPrefix( $prefixes, $path )
		{
			if ( is_array( $prefixes ) )
			{
				foreach ( $prefixes as $prefix => $path )
				{
					static::addPrefix( $prefix, $path );
				}
			}
			elseif ( is_dir( $path ) )
			{
				$prefixes = trim( $prefixes, '_' ) . '_';

				$this->_packages_paths[ $prefixes ] = $path;
				$this->_prefixes_maps[ $prefixes ] = $path . 'core' . DIRECTORY_SEPARATOR;

				foreach ( $this->_config[ 'class_paths' ] as $class_path )
				{
					if ( is_dir( $path . $class_path ) )
					{
						$this->_prefixes_maps[ prepare_class_name( $prefixes . '_' . Inflector::singularize( $class_path ) ) . '_' ] = $path . $class_path . DIRECTORY_SEPARATOR;
					}
				}
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Autoload Class APP O2
		 *
		 * @access  public
		 *
		 * @param   string $class the path
		 * @param   string $path  the class
		 *
		 * @return  boolean
		 */
		public function findClass( $class )
		{
			if ( ! class_exists( $class, FALSE ) )
			{
				$namespace = get_namespace( $class );
				$class = get_class_name( $class );

				if ( isset( $this->_psr_namespace_maps[ $namespace ] ) )
				{
					$path = $this->_psr_namespace_maps[ $namespace ];
				}
				else
				{
					$x_namespace = explode( '\\', $namespace );
					$num_namespace = count( $x_namespace );

					for ( $i = 0; $i < $num_namespace; $i++ )
					{
						$slice_namespace = array_slice( $x_namespace, 0, ( $num_namespace - $i ) );
						if ( isset( $this->_psr_namespace_maps[ implode( '\\', $slice_namespace ) . '\\' ] ) )
						{
							$path = $this->_psr_namespace_maps[ implode( '\\', $slice_namespace ) . '\\' ];
							$path = $path . implode( DIRECTORY_SEPARATOR, array_diff( $x_namespace, $slice_namespace ) );
							break;
						}
					}
				}

				if ( isset( $path ) )
				{
					$path = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $path );
					$filename = prepare_filename( $class );

					$filepaths = array(
						$path . $filename . '.php',
						$path . ucfirst( strtolower( $filename ) ) . '.php',
						$path . strtolower( $filename ) . DIRECTORY_SEPARATOR . $filename . '.php',
						$path . $filename . DIRECTORY_SEPARATOR . $filename . '.php',
					);

					$filepaths = array_unique( $filepaths );

					foreach ( $filepaths as $filepath )
					{
						if ( is_file( $filepath ) )
						{
							require_once( $filepath );
							break;
						}
					}
				}
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Init Class
		 *
		 * Create class object using native __constructor method or using initialize() method.
		 *
		 * @param   string $class  Class Name
		 * @param   array  $params Class Constructor Parameters
		 *
		 * @uses-by Loader::library
		 * @uses-by Loader::driver
		 *
		 * @access  private
		 * @return mixed
		 * @throws \Exception
		 */
		protected function _initClass( $class, array $params = array() )
		{
			if ( class_exists( $class ) )
			{
				if ( method_exists( $class, 'initialize' ) and is_callable( $class . '::initialize' ) )
				{
					return $class::initialize( $params );
				}
				else
				{
					return new $class( $params );
				}
			}
			// or an interface...
			elseif ( interface_exists( $class, FALSE ) )
			{
				// nothing to do here
			}
			// or a trait if you're not on 5.3 anymore...
			elseif ( function_exists( 'trait_exists' ) and trait_exists( $class, FALSE ) )
			{
				// nothing to do here
			}
			else
			{
				throw new \Exception( 'Loader: Cannot find requested class: ' . $class );
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Get Package Paths
		 *
		 * @param string $sub_path Sub Directory Package Path
		 *
		 * @access  public
		 * @return  array
		 */
		public function getPackagePaths( $sub_path = NULL, $root_only = FALSE )
		{
			$package_paths = array();

			if ( isset( $sub_path ) )
			{
				if ( $root_only === TRUE )
				{
					if ( class_exists( 'O2System', FALSE ) )
					{
						foreach ( [ SYSTEMPATH, APPSPATH ] as $package_path )
						{
							if ( $namespace = array_search( $package_path, $this->_packages_paths ) )
							{
								$package_paths[ $namespace ] = $package_path . $sub_path . DIRECTORY_SEPARATOR;
							}
						}

						if ( isset( \O2System::$active ) AND \O2System::$active->offsetExists( 'module' ) )
						{
							$parents = \O2System::$registry->find_parents( \O2System::$active[ 'module' ] );

							if ( ! empty( $parents ) )
							{
								foreach ( $parents as $parent )
								{
									$package_paths[ $parent->namespace ] = ROOTPATH . $parent->realpath . $sub_path . DIRECTORY_SEPARATOR;
								}
							}

							$package_paths[ \O2System::$active[ 'module' ]->namespace ] = ROOTPATH . \O2System::$active[ 'module' ]->realpath . $sub_path . DIRECTORY_SEPARATOR;
						}
					}
				}
				else
				{
					foreach ( $this->_packages_paths as $namespace => $package_path )
					{
						$package_paths[ $namespace ] = $package_path . $sub_path . DIRECTORY_SEPARATOR;
					}
				}
			}
			else
			{
				if ( $root_only === TRUE )
				{
					if ( class_exists( 'O2System', FALSE ) )
					{
						foreach ( [ SYSTEMPATH, APPSPATH ] as $package_path )
						{
							if ( $namespace = array_search( $package_path, $this->_packages_paths ) )
							{
								$package_paths[ $namespace ] = $package_path;
							}
						}

						if ( isset( \O2System::$active ) AND \O2System::$active->offsetExists( 'module' ) )
						{
							$parents = \O2System::$registry->find_parents( \O2System::$active[ 'module' ] );

							if ( ! empty( $parents ) )
							{
								foreach ( $parents as $parent )
								{
									$package_paths[ $parent->namespace ] = ROOTPATH . $parent->realpath;
								}
							}

							$package_paths[ \O2System::$active[ 'module' ]->namespace ] = ROOTPATH . \O2System::$active[ 'module' ]->realpath;
						}
					}

					return $package_paths;
				}
				else
				{
					$package_paths = $this->_packages_paths;
				}
			}

			return $package_paths;
		}
	}

	/**
	 * Class Language
	 *
	 * @package O2System\Glob
	 */
	class Language extends ArrayObject
	{
		/**
		 * Active Language
		 *
		 * @type string
		 */
		public    $active = 'en';

		/**
		 * Array of Language Paths
		 *
		 * @type array
		 */
		protected $_paths = array();

		/**
		 * List of loaded language files
		 *
		 * @access  protected
		 *
		 * @var array
		 */
		protected $_is_loaded = array();

		/**
		 * Language Package Info
		 *
		 * @access  public
		 * @type    array
		 */
		protected $_info = array();

		// ------------------------------------------------------------------------

		/**
		 * Class Constructor
		 *
		 * @access  public
		 */
		public function __construct()
		{
			$this->addPath( __DIR__ );
		}

		// ------------------------------------------------------------------------

		/**
		 * Add Paths
		 *
		 * @param $paths
		 *
		 * @return $this
		 */
		public function addPaths( $paths )
		{
			foreach ( $paths as $path )
			{
				$this->addPath( $path );
			}

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Add Path
		 *
		 * @param $path
		 *
		 * @return $this
		 */
		public function addPath( $path )
		{
			$path = str_replace( '/', DIRECTORY_SEPARATOR, $path );

			if ( is_dir( rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'languages' ) )
			{
				$this->_paths[] = rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;
			}

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set Active
		 *
		 * @param   string $code
		 *
		 * @access  public
		 */
		public function setActive( $code )
		{
			$this->active = $code;
		}

		// ------------------------------------------------------------------------

		/**
		 * Load a language file
		 *
		 * @access    public
		 *
		 * @param    mixed     the name of the language file to be loaded. Can be an array
		 * @param    string    the language (english, etc.)
		 * @param    bool      return loaded array of translations
		 * @param    bool      add suffix to $langfile
		 * @param    string    alternative path to look for language file
		 *
		 * @return    mixed
		 */
		public function load( $file, $code = NULL )
		{
			$code = is_null( $code ) ? $this->active : $code;

			if ( is_file( $file ) )
			{
				$this->_loadFile( $file );
			}
			else
			{
				$file = strtolower( $file );

				foreach ( $this->_paths as $package_path )
				{
					$filepaths = array(
						$package_path . $code . DIRECTORY_SEPARATOR . $file . '.ini',
						$package_path . $file . '_' . $code . '.ini',
						$package_path . $file . '-' . $code . '.ini',
						$package_path . $file . '.ini',
					);

					foreach ( $filepaths as $filepath )
					{
						$this->_loadFile( $filepath );
					}
				}
			}

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Load File
		 *
		 * @param $filepath
		 */
		protected function _loadFile( $filepath )
		{
			if ( is_file( $filepath ) AND ! in_array( $filepath, $this->_is_loaded ) )
			{
				$lines = parse_ini_file( $filepath, TRUE, INI_SCANNER_RAW );

				if ( ! empty( $lines ) )
				{

					$this->_is_loaded[ pathinfo( $filepath, PATHINFO_FILENAME ) ] = $filepath;

					foreach ( $lines as $key => $line )
					{
						$this->offsetSet( $key, $line );
					}
				}
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Fetch a single line of text from the language array
		 *
		 * @access    public
		 *
		 * @param    string $line the language line
		 *
		 * @return    string
		 */
		public function line( $line = '' )
		{
			$line = strtoupper( $line );

			return ( $line == '' || ! $this->offsetExists( $line ) ) ? NULL : $this->offsetGet( $line );
		}
	}

	/**
	 * Class Exception
	 *
	 * @package O2System\Glob
	 */
	class Exception extends Interfaces\ExceptionInterface
	{
	}

	/**
	 * Class Exceptions
	 *
	 * @package O2System\Glob
	 */
	class Exceptions extends Exception\ExceptionHandler
	{
		protected $_debug_ips   = array();
		protected $_environment = 'development';

		// ------------------------------------------------------------------------

		/**
		 * Exceptions constructor.
		 */
		public function __construct()
		{
			parent::__construct();

			$this->addPath( __DIR__ );

			// Register Exception View and Language Path
			if ( class_exists( 'O2System', FALSE ) )
			{
				$this->addPaths( [ SYSTEMPATH, APPSPATH ] );
				\O2System::$language->addPath( SYSTEMPATH )->load( 'exception' );
			}
			else
			{
				\O2System\Glob::$language->load( 'exception' );
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Set Debug Ips
		 *
		 * @param      $ips
		 * @param null $user_ip_address
		 */
		public function setDebugIps( $ips, $user_ip_address = NULL )
		{
			if ( is_array( $ips ) )
			{
				$this->_debug_ips = $ips;
			}
			elseif ( is_string( $ips ) )
			{
				$this->_debug_ips = array_map( 'trim', explode( ',', $ips ) );
			}

			if ( class_exists( 'O2System', FALSE ) )
			{
				$this->setEnvironment( ENVIRONMENT );
			}

			if ( isset( $user_ip_address ) )
			{
				if ( in_array( $user_ip_address, $this->_debug_ips ) )
				{
					$this->setEnvironment( 'development' );
				}
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Set Environment
		 *
		 * @param $environment
		 */
		public function setEnvironment( $environment )
		{
			switch ( $environment )
			{
				default:
				case 'development':
					error_reporting( -1 );
					ini_set( 'display_errors', 1 );
					$this->_environment = 'development';
					break;
				case 'testing':
				case 'production':
					ini_set( 'display_errors', 0 );
					error_reporting( E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED );
					$this->_environment = $environment;
					break;
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Get Environment
		 *
		 * @return string
		 */
		public function getEnvironment()
		{
			return $this->_environment;
		}
	}

	/**
	 * Class Logger
	 *
	 * @package O2System\Glob
	 */
	class Logger extends \O2System\Gears\Logger
	{
		/**
		 * Logger constructor.
		 *
		 * @param array $config
		 */
		public function __construct( $config = array() )
		{
			parent::__construct( $config );
		}
	}

	/**
	 * Class HttpStatusCode
	 *
	 * @package O2System\Glob
	 */
	class HttpStatusCode
	{
		const SWITCHING_PROTOCOLS               = 101;
		const OK                                = 200;
		const CREATED                           = 201;
		const ACCEPTED                          = 202;
		const NON_AUTHORITATIVE_INFORMATION     = 203;
		const NO_CONTENT                        = 204;
		const RESET_CONTENT                     = 205;
		const PARTIAL_CONTENT                   = 206;
		const MULTIPLE_CHOICES                  = 300;
		const MOVED_PERMANENTLY                 = 301;
		const MOVED_TEMPORARILY                 = 302;
		const SEE_OTHER                         = 303;
		const NOT_MODIFIED                      = 304;
		const USE_PROXY                         = 305;
		const TEMPORARY_REDIRECT                = 307;
		const BAD_REQUEST                       = 400;
		const UNAUTHORIZED                      = 401;
		const PAYMENT_REQUIRED                  = 402;
		const FORBIDDEN                         = 403;
		const NOT_FOUND                         = 404;
		const METHOD_NOT_ALLOWED                = 405;
		const NOT_ACCEPTABLE                    = 406;
		const PROXY_AUTHENTICATION_REQUIRED     = 407;
		const REQUEST_TIMEOUT                   = 408;
		const CONFLICT                          = 409;
		const GONE                              = 410;
		const LENGTH_REQUIRED                   = 411;
		const PRECONDITION_FAILED               = 412;
		const REQUEST_ENTITY_TOO_LARGE          = 413;
		const REQUEST_URI_TOO_LONG              = 414;
		const UNSUPPORTED_MEDIA_TYPE            = 415;
		const REQUESTED_RANGE_NOT_SATISFIABLE   = 416;
		const EXPECTATION_FAILED                = 417;
		const IM_A_TEAPOT                       = 418; // RFC 2324
		const AUTHENTIFICATION_TIMEOUT          = 419; // not in RFC 2616
		const UNPROCESSABLE_ENTITY              = 422; // WebDAV; RFC 4918
		const LOCKED                            = 423; // WebDAV; RFC 4918
		const FAILED_DEPENDENCY                 = 424; // WebDAV
		const UNORDERED_COLLECTION              = 425; // Internet draft
		const UPGRADE_REQUIRED                  = 426; // RFC 2817
		const PRECONDITION_REQUIRED             = 428; // RFC 6585
		const TO_MANY_REQUEST                   = 429; // RFC 6585
		const REQUEST_HEADER_FIELDS_TOO_LARGE   = 430; // RFC 6585
		const NO_RESPONSE                       = 444; // Nginx
		const RETRY_WITH                        = 449; // Microsoft
		const BLOCKED_BY_PARENTAL_CONTROLS      = 450;
		const REDIRECT                          = 451;
		const REQUEST_HEADER_TOO_LARGE          = 494;
		const CERT_ERROR                        = 495; // Nginx
		const NO_CERT                           = 496; // Nginx
		const HTTP_TO_HTTPS                     = 497; // Nginx
		const CLIENT_CLOSED_REQUEST             = 499; // Nginx
		const INTERNAL_SERVER_ERROR             = 500;
		const NOT_IMPLEMENTED                   = 501;
		const BAD_GATEWAY                       = 502;
		const SERVICE_UNAVAILABLE               = 503;
		const GATEWAY_TIMEOUT                   = 504;
		const HTTP_VERSION_NOT_SUPPORTED        = 505;
		const VARIANT_ALSO_NEGOTIATES           = 506; // RFC 2295
		const INSUFFICIENT_STORAGE              = 507; // WebDAV; RFC 4918
		const LOOP_DETECTED                     = 508; // WebDAV; RFC 5842
		const BANDWIDTH_LIMIT_EXCEEDED          = 509;  // Apache bw/limited extension
		const NOT_EXTENDED                      = 510; // RFC 2774
		const NETWORK_AUTHENTIFICATION_REQUIRED = 511; // RFC 6585
		const NETWORK_READ_TIMEOUT_ERROR        = 598;
		const NETWORK_CONNECT_TIMEOUT_ERROR     = 599;
		const METHOD_REQUEST_NOT_FOUND          = 701; // O2System Framework
		const METHOD_INVALID_PARAMETER          = 702;
		const CONFIGURATION_MISSING             = 703;
		const CONFIGURATION_INVALID             = 704;
		const MISSING_LIBRARY                   = 705;

		// ------------------------------------------------------------------------

		/**
		 * Set Header
		 *
		 * @param      $code
		 * @param null $description
		 */
		public static function setHeader( $code, $description = NULL )
		{
			// There is no header at console
			if ( PHP_SAPI === 'cli' ) return;

			if ( class_exists( 'O2System', FALSE ) )
			{
				\O2System::$language->load( 'http_status' );
				$lang_description = \O2System::$language->line( 'HTTPSTATUS_' . $code );
			}
			else
			{
				\O2System\Glob::$language->load( 'http_status' );
				$lang_description = \O2System\Glob::$language->line( 'HTTPSTATUS_' . $code );
			}

			$lang_description = empty( $lang_description ) ? 'Internal Server Error' : $lang_description;
			$description = empty( $description ) ? $lang_description : $description;

			if ( strpos( PHP_SAPI, 'cgi' ) === 0 )
			{
				@header( 'Status: ' . $code . ' ' . $description, TRUE );
			}
			else
			{
				$server_protocol = isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.1';
				@header( $server_protocol . ' ' . $code . ' ' . $description, TRUE, $code );
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Get Header
		 *
		 * @param $code
		 *
		 * @return string
		 */
		public static function getHeader( $code )
		{
			if ( class_exists( 'O2System', FALSE ) )
			{
				\O2System::$language->load( 'http_status' );
				$lang_header = \O2System::$language->line( 'HTTPSTATUS_' . $code );
			}
			else
			{
				\O2System\Glob::$language->load( 'http_status' );
				$lang_header = \O2System\Glob::$language->line( 'HTTPSTATUS_' . $code );
			}

			$lang_header = empty( $lang_header ) ? 'Internal Server Error' : $lang_header;

			return $lang_header;
		}

		// ------------------------------------------------------------------------

		/**
		 * Get Description
		 *
		 * @param $code
		 *
		 * @return string
		 */
		public static function getDescription( $code )
		{
			$prefix = is_cli() ? 'HTTPSTATUSDESCRIPTION_CLI_' : 'HTTPSTATUS_';

			if ( class_exists( 'O2System', FALSE ) )
			{
				\O2System::$language->load( 'http_status' );
				$lang_description = \O2System::$language->line( $prefix . $code );
			}
			else
			{
				\O2System\Glob::$language->load( 'http_status' );
				$lang_description = \O2System\Glob::$language->line( $prefix . $code );
			}

			$lang_description = empty( $lang_description ) ? 'Internal Server Error' : $lang_description;

			return $lang_description;
		}
	}
}
