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

namespace O2System\Core;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

require_once( SYSTEMPATH . 'Thirdparty/vendor/autoload.php' );

/**
 * System Loader
 *
 * This class contains functions that enable config files to be managed based on CodeIgniter Concept by EllisLab, Inc.
 *
 * @package        O2System
 * @subpackage     system/core
 * @category       Core Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/loader.html
 */
final class Loader
{
	/**
	 * List of Loaded Controllers
	 *
	 * @static
	 * @access  protected
	 * @type    array
	 */
	protected static $loadedControllers = [ ];
	protected        $config;
	/**
	 * Holds all the Prefix Class maps.
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $legacyMaps = [ ];

	/**
	 * Holds all the PSR-4 compliant namespaces maps.
	 * These namespaces should be loaded according to the PSR-4 standard.
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $psr4Maps = [ ];

	/**
	 * List of Packages Paths
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $assetsDirectories = [ ];

	/**
	 * List of Packages Paths
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $languagesDirectories = [ ];

	/**
	 * List of Packages Paths
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $configDirectories = [ ];

	/**
	 * List of Packages Paths
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $helpersDirectories = [ ];

	/**
	 * List of Packages Paths
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $viewsDirectories = [ ];

	/**
	 * List of Packages Paths
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $pagesDirectories = [ ];

	/**
	 * List of Packages Paths
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $themesDirectories = [ ];

	/**
	 * List of Loaded Helper Files
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $loadedHelpers = [ ];

	// ------------------------------------------------------------------------

	public function __construct()
	{
		$this->config = require( SYSTEMPATH . 'Config' . DIRECTORY_SEPARATOR . 'loader.php' );

		$this->addNamespace( 'O2System\\', SYSTEMPATH );
		$this->addNamespace( 'Applications\\', APPSPATH );

		// Add Assets Directories
		$this->addAssetsDirectory( 'O2System\\', SYSTEMPATH );

		// Add Themes Directories
		$this->addThemesDirectory( 'Applications\\', APPSPATH );

		$this->registerHandler();
	}

	public function addAssetsDirectory( $namespace, $path )
	{
		if ( $namespace === 'O2System\\' )
		{
			$assetsDirectory = str_replace( SYSTEMPATH, PUBLICPATH, $path ) . 'assets';
		}
		else
		{
			$assetsDirectory = str_replace( APPSPATH, PUBLICPATH, $path ) . 'assets';
		}

		if ( is_dir( $assetsDirectory ) )
		{
			$this->assetsDirectories[ $namespace ] = realpath( $assetsDirectory ) . DIRECTORY_SEPARATOR;
		}
		else
		{
			if ( ! is_dir( $assetsDirectory ) )
			{
				mkdir( $assetsDirectory, 0777, TRUE );
			}

			$this->assetsDirectories[ $namespace ] = realpath( $assetsDirectory ) . DIRECTORY_SEPARATOR;
		}
	}

	public function addThemesDirectory( $namespace, $path )
	{
		if ( $namespace === 'O2System\\' )
		{
			$themesDirectory = str_replace( SYSTEMPATH, PUBLICPATH, $path ) . 'themes';
		}
		else
		{
			$themesDirectory = str_replace( APPSPATH, PUBLICPATH, $path ) . 'themes';
		}

		if ( is_dir( $themesDirectory ) )
		{
			$this->themesDirectories[ $namespace ] = realpath( $themesDirectory ) . DIRECTORY_SEPARATOR;
		}
		else
		{
			if ( ! is_dir( $themesDirectory ) )
			{
				mkdir( $themesDirectory, 0777, TRUE );
			}

			$this->themesDirectories[ $namespace ] = realpath( $themesDirectory ) . DIRECTORY_SEPARATOR;
		}
	}

	public function getAssetsDirectories( $reverse = FALSE )
	{
		$assetsDirectories[] = PUBLICPATH . 'assets' . DIRECTORY_SEPARATOR;

		if ( $module = \O2System::$request->modules->current() )
		{
			if ( array_key_exists( $module->namespace, $this->themesDirectories ) )
			{
				$assetsDirectories[] = $this->assetsDirectories[ $module->namespace ];
			}
		}

		if ( \O2System::$view->theme->active !== FALSE )
		{
			$assetsDirectories[] = \O2System::$view->theme->active->realpath . 'assets' . DIRECTORY_SEPARATOR;
		}

		return $reverse === TRUE ? array_reverse( $assetsDirectories ) : $assetsDirectories;
	}

	public function getLanguagesDirectories( $reverse = FALSE )
	{
		return $reverse === TRUE ? array_reverse( $this->languagesDirectories ) : $this->languagesDirectories;
	}

	public function getConfigDirectories( $reverse = FALSE )
	{
		return $reverse === TRUE ? array_reverse( $this->configDirectories ) : $this->configDirectories;
	}

	public function getHelpersDirectories( $reverse = FALSE )
	{
		return $reverse === TRUE ? array_reverse( $this->helpersDirectories ) : $this->helpersDirectories;
	}

	public function getViewsDirectories( $reverse = FALSE )
	{
		// Default directories
		$viewsDirectories[] = $currentDirectory = \O2System::$request->directories->getCurrent( 'Views' );

		// Theme replacement directories
		if ( \O2System::$view->theme->active !== FALSE )
		{
			$viewsDirectories[] = \O2System::$view->theme->active->realpath . strtolower( str_replace( APPSPATH, '', dirname( $currentDirectory ) ) ) . DIRECTORY_SEPARATOR;
		}

		return $reverse === TRUE ? array_reverse( $viewsDirectories ) : $viewsDirectories;
	}

	public function getPagesDirectories( $reverse = FALSE )
	{
		return $reverse === TRUE ? array_reverse( $this->pagesDirectories ) : $this->pagesDirectories;
	}

	public function getThemesDirectories( $reverse = FALSE )
	{
		$themesDirectories[] = PUBLICPATH . 'themes' . DIRECTORY_SEPARATOR;

		if ( $module = \O2System::$request->modules->current() )
		{
			if ( array_key_exists( $module->namespace, $this->themesDirectories ) )
			{
				$themesDirectories[] = $this->themesDirectories[ $module->namespace ];
			}
		}

		return $reverse === TRUE ? array_reverse( $themesDirectories ) : $themesDirectories;
	}

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
		$path = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );

		if ( is_array( $namespaces ) )
		{
			foreach ( $namespaces as $namespace => $path )
			{
				$this->addNamespace( $namespace, $path );
			}
		}
		elseif ( is_dir( $path = realpath( $path ) . DIRECTORY_SEPARATOR ) )
		{
			$namespaces = rtrim( $namespaces, '\\' ) . '\\';

			if ( $path === DIRECTORY_SEPARATOR OR $namespaces === '\\' )
			{
				return;
			}

			$this->psr4Maps[ $namespaces ] = $path;

			// Get Sub Directories
			foreach ( [ 'Languages', 'Config', 'Helpers', 'Views', 'Pages' ] as $subDirectory )
			{
				if ( is_dir( $path . $subDirectory ) )
				{
					$this->{strtolower( $subDirectory ) . 'Directories'}[ $namespaces ] = $path . $subDirectory . DIRECTORY_SEPARATOR;
				}
			}

			// Autoload Composer
			if ( is_file( $path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' ) )
			{
				require( $path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' );
			}
		}
	}

	private function __addPsrNamespaceMaps( $namespace, $path )
	{
		if ( array_key_exists( $namespace, $this->psr4Maps ) )
		{
			if ( is_string( $this->psr4Maps[ $namespace ] ) )
			{
				if ( $this->psr4Maps[ $namespace ] !== $path )
				{
					$this->psr4Maps[ $namespace ] = [
						$this->psr4Maps[ $namespace ],
						$path,
					];
				}
			}
			elseif ( is_array( $this->psr4Maps[ $namespace ] ) )
			{
				if ( ! in_array( $path, $this->psr4Maps[ $namespace ] ) )
				{
					array_push( $this->psr4Maps[ $namespace ], $path );
				}
			}
		}
		else
		{
			$this->psr4Maps[ $namespace ] = $path;
		}
	}

	// ------------------------------------------------------------------------

	private function __addPackagesPaths( $namespace, $path )
	{
		if ( array_key_exists( $namespace, $this->psr4Directories ) )
		{
			if ( is_string( $this->psr4Directories[ $namespace ] ) )
			{
				if ( $this->psr4Directories[ $namespace ] !== $path )
				{
					$this->psr4Directories[ $namespace ] = [
						$this->psr4Directories[ $namespace ],
						$path,
					];
				}
			}
			elseif ( is_array( $this->psr4Directories[ $namespace ] ) )
			{
				if ( ! in_array( $path, $this->psr4Directories[ $namespace ] ) )
				{
					array_push( $this->psr4Directories[ $namespace ], $path );
				}
			}
		}
		else
		{
			$this->psr4Directories[ $namespace ] = $path;
		}
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
		spl_autoload_register( [ $this, 'findClass' ], $throw, $prepend );
	}

	// ------------------------------------------------------------------------

	public function setConfig( array $config )
	{
		$this->config = $config;
	}

	// ------------------------------------------------------------------------

	public function getNamespace( $path )
	{
		$path = realpath( $path );
		$path = rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		if ( FALSE !== ( $namespace = array_search( $path, $this->psr4Maps ) ) )
		{
			return $namespace;
		}

		return $this->_getNamespaceFromPath( $path );
	}

	protected function _getNamespaceFromPath( $path )
	{
		$namespace = str_replace( ROOTPATH, '', $path );

		return prepare_class_name( $namespace );
	}

	public function getNamespacePath( $namespace, $path = NULL )
	{
		if ( array_key_exists( $namespace, $this->psr4Maps ) )
		{
			if ( is_array( $this->psr4Maps[ $namespace ] ) )
			{
				if ( in_array( $path, $this->psr4Maps[ $namespace ] ) )
				{
					return $path;
				}
			}
			else
			{
				return $this->psr4Maps[ $namespace ];
			}
		}
		elseif ( isset( $path ) )
		{
			$map = $this->_getNamespaceFromPath( $path );

			if ( is_array( $map ) )
			{
				$sub_path = $namespace = str_replace( reset( $map ), '', $path );

				return reset( $map ) . $sub_path . DIRECTORY_SEPARATOR;
			}
		}

		return FALSE;
	}

	/**
	 * SPL Find Class Autoloader
	 *
	 * @access  public
	 *
	 * @param   string $class Class Name
	 */
	public function findClass( $class )
	{
		if ( class_exists( $class, FALSE ) )
		{
			return;
		}

		$namespace = get_namespace( $class );
		$class     = get_class_name( $class );

		if ( array_key_exists( $namespace, $this->psr4Maps ) )
		{
			$classDirectory = $this->psr4Maps[ $namespace ];
		}
		else
		{
			$xNamespace   = explode( '\\', $namespace );
			$xNamespace   = array_filter( $xNamespace );
			$numNamespace = count( $xNamespace );

			for ( $i = 0; $i < $numNamespace; $i++ )
			{
				$sliceNamespace = array_slice( $xNamespace, 0, ( $numNamespace - $i ) );
				$findNamespace  = implode( '\\', $sliceNamespace ) . '\\';

				if ( array_key_exists( $findNamespace, $this->psr4Maps ) )
				{
					// Define namespace directory
					$namespaceDirectory = $this->psr4Maps[ $findNamespace ];

					// Define namespace subDirectory
					$namespaceSubDirectory = array_diff( $xNamespace, $sliceNamespace );

					if ( is_dir( $namespaceFindDirectory = $namespaceDirectory . implode( DIRECTORY_SEPARATOR, $namespaceSubDirectory ) ) )
					{
						$classDirectory = $namespaceFindDirectory . DIRECTORY_SEPARATOR;
						break;
					}
				}
			}
		}

		if ( isset( $classDirectory ) )
		{
			$filePaths = [
				$classDirectory . $class . DIRECTORY_SEPARATOR . $class . '.php',
				$classDirectory . $class . '.php',
			];

			foreach ( $filePaths as $filePath )
			{
				if ( is_file( $filePath ) )
				{
					require_once $filePath;
					break;
				}
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Package Paths
	 *
	 * @param string $subPath Sub Directory Package Path
	 *
	 * @access  public
	 * @return  array
	 */
	public function getDirectories( $subDirectory = NULL, $rootOnly = FALSE, $reverse = FALSE )
	{
		if ( $rootOnly )
		{
			$directories = [ SYSTEMPATH, APPSPATH ];
		}
		else
		{
			$directories = array_values( $this->psr4Maps );
		}

		if ( isset( $subDirectory ) )
		{
			$subDirectory = prepare_class_name( $subDirectory );

			foreach ( $directories as $key => $directory )
			{
				$directories[ $key ] = $directory . str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $subDirectory ) . DIRECTORY_SEPARATOR;
			}
		}

		return $reverse === TRUE ? array_reverse( $directories ) : $directories;
	}

	// ------------------------------------------------------------------------

	/**
	 * Database Loader
	 *
	 * @param   string $connection Database Configuration Connection Key
	 *
	 * @access  public
	 * @return  object  O2System\Libraries\Database()
	 */
	public function database( $connection = 'default' )
	{
		return \O2System::$db->connect( $connection );
	}

	/**
	 * Load Helpers
	 *
	 * Load multiple helpers
	 *
	 * @uses    Loader::helper()
	 *
	 * @param   array $helpers Helper names
	 *
	 * @return  object
	 */
	public function helpers( $helpers = [ ] )
	{
		foreach ( $helpers as $helper )
		{
			$this->helper( $helper );
		}
	}
	// --------------------------------------------------------------------

	/**
	 * Load Helper
	 *
	 * @param   string $helper Helper name
	 *
	 * @return  void
	 */
	public function helper( $helper )
	{
		// Helper namespace
		$namespace = get_namespace( $helper );

		// Real helper filename
		$helper = get_class_name( $helper );

		if ( in_array( strtolower( $helper ), $this->loadedHelpers ) )
		{
			if ( method_exists( \O2System::$log, 'notice' ) )
			{
				\O2System::$log->notice( 'LOG_NOTICE_ALREADY_LOAD_HELPER', [ strtolower( $helper ) ] );
			}

			return;
		}

		if ( array_key_exists( $namespace, $this->helpersDirectories ) )
		{
			$directory = $this->helpersDirectories[ $namespace ];

			if ( is_file( $filePath = $directory . $helper . '.php' ) )
			{
				include_once( $filePath );
			}
		}
		else
		{
			foreach ( [ 'O2System\\', 'Applications\\' ] as $namespace )
			{
				$directory = $this->helpersDirectories[ $namespace ];

				if ( is_file( $filePath = $directory . $helper . '.php' ) )
				{
					include_once( $filePath );
				}
			}
		}

		if ( method_exists( \O2System::$log, 'debug' ) )
		{
			\O2System::$log->debug( 'LOG_DEBUG_PERFORMING_LOAD_HELPER', [ strtolower( $helper ) ] );
		}
	}

	/**
	 * View Loader
	 *
	 * Alias for O2System\View::Load()
	 *
	 * @param   string $view   View Filename
	 * @param   array  $vars   View Variables
	 * @param   bool   $return Return View as String
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function view( $view, $vars = [ ], $return = FALSE )
	{
		return \O2System::$view->load( $view, $vars, $return );
	}
	// ------------------------------------------------------------------------

	/**
	 * View Page Loader
	 *
	 * Alias for O2System\View::page()
	 *
	 * @param   string $page   View Filename
	 * @param   array  $vars   View Variables
	 * @param   bool   $return Return View as String
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function page( $page, $vars = [ ], $return = FALSE )
	{
		return \O2System::$view->page( $page, $vars, $return );
	}

	// -----------------------------------------------------------------------

	public function theme( $theme )
	{
		return \O2System::$view->theme->set( $theme );
	}

	// -----------------------------------------------------------------------

	public function layout( $layout )
	{
		return \O2System::$view->theme->setLayout( $layout );
	}
	// ------------------------------------------------------------------------

	/**
	 * Autoload
	 *
	 * Loader Autoload Files
	 *
	 * @access  public
	 */
	public function __autoload()
	{
		$system_autoload = require( SYSTEMPATH . 'Config' . DIRECTORY_SEPARATOR . 'autoload.php' );

		if ( is_file( APPSPATH . 'config' . DIRECTORY_SEPARATOR . strtolower( ENVIRONMENT ) . DIRECTORY_SEPARATOR . 'autoload.php' ) )
		{
			include( APPSPATH . 'config' . DIRECTORY_SEPARATOR . strtolower( ENVIRONMENT ) . DIRECTORY_SEPARATOR . 'autoload.php' );
		}
		elseif ( is_file( APPSPATH . 'config' . DIRECTORY_SEPARATOR . 'autoload.php' ) )
		{
			include( APPSPATH . 'config' . DIRECTORY_SEPARATOR . 'autoload.php' );
		}

		if ( isset( $autoload ) )
		{
			$autoload = array_merge_recursive( $autoload, $system_autoload );
		}
		else
		{
			$autoload = $system_autoload;
		}

		foreach ( $autoload as $type => $config )
		{
			$config = array_unique( $config );

			if ( empty( $config ) || count( $config ) == 0 )
			{
				continue;
			}

			switch ( $type )
			{
				case 'libraries':
					foreach ( $config as $library => $library_name )
					{
						if ( is_numeric( $library ) )
						{
							$this->library( $library_name );
						}
						else
						{
							$this->library( $library, $library_name );
						}
					}
					break;
				case 'helpers':
					foreach ( $config as $helper )
					{
						$this->helper( $helper );
					}
					break;
				case 'config':
					foreach ( $config as $item )
					{
						$this->config( $item );
					}
					break;
				case 'language':
					foreach ( $config as $language )
					{
						$this->language( $language );
					}
					break;
				case 'models':
					foreach ( $config as $model => $model_name )
					{
						if ( is_numeric( $model ) )
						{
							$this->model( $model_name );
						}
						else
						{
							$this->model( $model, $model_name );
						}
					}
					break;
			}
		}

		\O2System::$log->debug( 'LOG_DEBUG_PERFORMING_LOADER_AUTOLOAD' );
	}
	// ------------------------------------------------------------------------

	/**
	 * Library
	 *
	 * Library class loader
	 *
	 * @param   string $class       Class Name
	 * @param   array  $params      Class Constructor Parameters
	 * @param   string $object_name Class Object Name
	 *
	 * @access  public
	 * @return  object
	 */
	public function library( $class, $params = [ ], $object_name = NULL )
	{
		if ( class_exists( $class ) )
		{
			$class_name  = get_class_name( $class );
			$object_name = empty( $object_name ) ? strtolower( $class_name ) : $object_name;

			if ( \O2System::$libraries->offsetExists( $object_name ) === FALSE )
			{
				\O2System::$libraries->offsetSet( $object_name, self::_initClass( $class ) );
			}

			\O2System::$log->debug( 'LOG_DEBUG_PERFORMING_LOAD_LIBRARY', [ $class ] );

			return TRUE;
		}

		return FALSE;
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
	protected function _initClass( $class, array $params = [ ] )
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
	 * Config Loader
	 *
	 * Loads a config file (an alias for O2System\Core\Config::load()).
	 *
	 * @uses    \O2System\Config::load()
	 *
	 * @param   string $config Configuration file name
	 *
	 * @return  mixed
	 */
	public function config( $config )
	{
		return \O2System::$config->load( $config, TRUE );
	}

	/**
	 * Language Loader
	 *
	 * Loads language files. (an alias for O2System\Core\Lang::load()).
	 *
	 * @uses    O2System\Language::load()
	 *
	 * @param   string|array $files List of language file names to load
	 * @param   string       $ideom Language name
	 *
	 * @return  object
	 */
	public function language( $files, $ideom = NULL )
	{
		return \O2System::$language->load( $files, $ideom );
	}

	/**
	 * Model
	 *
	 * Model class loader
	 *
	 * @param   string $class       Model Class Name
	 * @param   string $data        Model Data
	 * @param   string $object_name Model Global Object Name
	 *
	 * @access  public
	 * @return  object
	 */
	public function model( $class, $object_name = NULL )
	{
		if ( class_exists( $class ) )
		{
			if ( strpos( $class, 'Core' ) )
			{
				$class_name = explode( '\\', $class );
				$class_name = $class_name[ 0 ];
			}
			else
			{
				$class_name = get_class_name( $class );
			}

			$object_name = empty( $object_name ) ? strtolower( $class_name ) : $object_name;

			if ( \O2System::$models->offsetExists( $object_name ) === FALSE )
			{
				\O2System::$models->offsetSet( $object_name, self::_initClass( $class ) );
			}

			\O2System::$log->debug( 'LOG_DEBUG_PERFORMING_LOAD_MODEL', [ $class ] );

			return TRUE;
		}
	}
	// ------------------------------------------------------------------------
}