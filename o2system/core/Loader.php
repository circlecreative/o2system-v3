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

namespace O2System;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\DB;

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
final class Loader extends Glob\Loader
{
	protected $_vars = array();

	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_config = require( SYSTEMPATH . 'config/loader.php' );

		$this->addNamespace( 'O2System', SYSTEMPATH );
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
	public function &library( $class, $params = array(), $object_name = NULL )
	{
		if ( $class === 'database' || $class === 'db' ) return static::database();

		$is_found = FALSE;

		if ( is_string( $params ) )
		{
			$object_name = $params;
			$params = array();
		}

		$class = prepare_class_name( $class );
		$class_name = get_class_name( $class );

		$object_name = empty( $object_name ) ? strtolower( $class_name ) : $object_name;

		if ( array_key_exists( $object_name, $this->_config[ 'object_maps' ] ) )
		{
			$object_name = $this->_config[ 'object_maps' ][ $object_name ];
		}

		if ( isset( static::$_libraries_classes[ $object_name ] ) )
		{
			\O2System::Log( 'notice', 'Loader: Already loaded library class ' . $class_name );

			return static::$_libraries_classes[ $object_name ];
		}

		$namespace = get_namespace( $class );
		$namespace = str_replace( 'O2system', 'O2System', $namespace );

		if ( isset( $this->_psr_namespace_maps[ $namespace . 'Libraries\\' ] ) )
		{
			$called_class = $namespace . 'Libraries\\' . $class_name;

			if ( class_exists( $called_class ) )
			{
				$is_found = TRUE;
			}
		}

		if ( $is_found === TRUE )
		{
			static::$_libraries_classes[ $object_name ] = self::_initClass( $called_class, $params );

			if ( \O2System::instance()->__get( $object_name ) == '' )
			{
				\O2System::instance()->__set( $object_name, static::$_libraries_classes[ $object_name ] );
			}

			\O2System::Log( 'debug', 'Loader: Load library class ' . $called_class );
		}

		return static::$_libraries_classes[ $object_name ];
	}
	// --------------------------------------------------------------------

	/**
	 * Driver
	 *
	 * Driver library class loader
	 *
	 * @param   string $class       Class Name
	 * @param   array  $params      Class Constructor Parameters
	 * @param   string $object_name Class Object Name
	 *
	 * @access  public
	 * @return  object
	 */
	public function &driver( $class, array $params = array(), $object_name = NULL )
	{
		return $this->library( $class, $params, $object_name );
	}
	// --------------------------------------------------------------------

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
	public function &model( $class, $data = array(), $object_name = NULL )
	{
		$is_found = FALSE;

		if ( is_string( $data ) )
		{
			$object_name = $data;
			$data = array();
		}

		$class = str_replace( '_model', '', $class );
		$class = prepare_class_name( $class );
		$class_name = get_class_name( $class );

		$object_name = empty( $object_name ) ? strtolower( $class_name ) : $object_name;
		$object_name = str_replace( '_model', '', $object_name ) . '_model';


		if ( isset( static::$_models_classes[ $object_name ] ) )
		{
			\O2System::Log( 'notice', 'Loader: Already loaded model class ' . $class_name );

			return static::$_models_classes[ $object_name ];
		}

		$namespace = get_namespace( $class );
		$namespace = str_replace( 'O2system', 'O2System', $namespace );

		if ( isset( $this->_psr_namespace_maps[ $namespace . 'Models\\' ] ) )
		{
			$called_class = $namespace . 'Models\\' . $class_name;

			if ( class_exists( $called_class ) )
			{
				$is_found = TRUE;
			}
		}

		if ( $is_found === FALSE )
		{
			if ( isset( $this->_psr_namespace_maps[ $namespace . 'Core\\' ] ) )
			{
				$called_class = $namespace . 'Core\Model';

				if ( class_exists( $called_class ) )
				{
					$is_found = TRUE;
				}
			}
		}

		if ( $is_found === TRUE )
		{
			static::$_models_classes[ $object_name ] = self::_initClass( $called_class, $data );

			if ( empty( \O2System::instance()->{$object_name} ) )
			{
				\O2System::instance()->__set( $object_name, static::$_models_classes[ $object_name ] );
			}

			\O2System::Log( 'debug', 'Loader: Load model class ' . $called_class );
		}

		return static::$_models_classes[ $object_name ];
	}

	/**
	 * Controller
	 *
	 * Controller class loader
	 *
	 * @param   string $class       Class Name
	 * @param   string $object_name Class Object Name
	 *
	 * @access  public
	 * @return  object
	 */
	public function &controller( $class )
	{
		$is_found = FALSE;

		$class = str_replace( '_controller', '', $class );
		$class = prepare_class_name( $class );
		$class_name = get_class_name( $class );

		$object_name = empty( $object_name ) ? strtolower( $class_name ) : $object_name;
		$object_name = str_replace( '_controller', '', $object_name ) . '_controller';

		if ( isset( static::$_controllers_classes[ $object_name ] ) )
		{
			\O2System::Log( 'notice', 'Loader: Already loaded controller class ' . $class_name );

			return static::$_controllers_classes[ $object_name ];
		}

		$namespace = get_namespace( $class );
		$namespace = str_replace( 'O2system', 'O2System', $namespace );

		if ( isset( $this->_psr_namespace_maps[ $namespace . 'Controllers\\' ] ) )
		{
			$called_class = $namespace . 'Controllers\\' . $class_name;

			if ( class_exists( $called_class ) )
			{
				$is_found = TRUE;
			}
		}

		if ( $is_found === TRUE )
		{
			$controller = new \ReflectionClass( $called_class );
			static::$_controllers_classes[ $object_name ] = $controller->newInstanceWithoutConstructor();

			\O2System::Log( 'debug', 'Loader: Load controller class ' . $called_class );
		}

		return static::$_controllers_classes[ $object_name ];
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
	public function database( $connection = 'default', $return = FALSE )
	{
		$config = \O2System::$config->load( 'database', TRUE );

		if ( isset( $config[ $connection ] ) )
		{
			if ( $config[ $connection ][ 'dsn' ] !== '' OR $config[ $connection ][ 'username' ] !== '' )
			{
				if ( $return === FALSE )
				{
					if ( ! \O2System::instance()->__isset( 'db' ) )
					{
						\O2System::instance()->__set( 'db', new DB( $config[ $connection ] ) );
					}

					return \O2System::instance()->__get( 'db' );
				}
				elseif ( $return === TRUE )
				{
					return new DB( $config[ $connection ] );
				}
			}
		}

		return FALSE;
	}
	// ------------------------------------------------------------------------

	/**
	 * Load Helper
	 *
	 * @param   string $helper Helper name
	 *
	 * @return  object
	 */
	public function helper( $helper )
	{
		if ( in_array( strtolower( $helper ), $this->_helpers ) )
		{
			\O2System::Log( 'notice', 'Loader: Already loaded helper ' . strtolower( $helper ) );

			return;
		}
		else
		{
			$is_found = FALSE;

			if ( strpos( $helper, '/' ) !== FALSE )
			{
				$namespace = get_namespace( $helper );

				$helper = strtolower( $helper );

				if ( isset( $this->_packages_paths[ $namespace ] ) )
				{
					if ( is_file( $this->_packages_paths[ $namespace ] . 'helpers' . DIRECTORY_SEPARATOR . $helper . '_helper.php' ) )
					{
						$is_found = TRUE;
						include_once( $this->_packages_paths[ $namespace ] . 'helpers' . DIRECTORY_SEPARATOR . $helper . '_helper.php' );
					}
				}
			}
			else
			{
				foreach ( $this->_packages_paths as $namespace => $path )
				{
					if ( is_file( $path . 'helpers' . DIRECTORY_SEPARATOR . $helper . '_helper.php' ) )
					{
						$is_found = TRUE;
						include_once( $path . 'helpers' . DIRECTORY_SEPARATOR . $helper . '_helper.php' );
					}
				}
			}

			if ( $is_found === TRUE )
			{
				array_push( $this->_helpers, strtolower( $helper ) );
				\O2System::Log( 'debug', 'Loader: Load helper ' . strtolower( $helper ) );
			}
		}
	}
	// -----------------------------------------------------------------------

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
	public function helpers( $helpers = array() )
	{
		foreach ( $helpers as $helper )
		{
			$this->helper( $helper );
		}
	}
	// -----------------------------------------------------------------------

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
	// ------------------------------------------------------------------------

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
	// ------------------------------------------------------------------------

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
	public function view( $view, $vars = array(), $return = FALSE )
	{
		return \O2System::View()->load( $view, $vars, $return );
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
	public function page( $page, $vars = array(), $return = FALSE )
	{
		return \O2System::View()->page( $page, $vars, $return );
	}

	// ------------------------------------------------------------------------

	public function theme( $theme )
	{
		return \O2System::View()->theme( $theme );
	}

	public function layout( $layout )
	{
		return \O2System::View()->layout( $layout );
	}

	/**
	 * Autoload
	 *
	 * Loader Autoload Files
	 *
	 * @access  public
	 */
	public function __autoload()
	{
		$system_autoload = require( SYSTEMPATH . 'config' . DIRECTORY_SEPARATOR . 'autoload.php' );

		if ( is_file( APPSPATH . 'config' . DIRECTORY_SEPARATOR . ENVIRONMENT . DIRECTORY_SEPARATOR . 'autoload.php' ) )
		{
			include( APPSPATH . 'config' . DIRECTORY_SEPARATOR . ENVIRONMENT . DIRECTORY_SEPARATOR . 'autoload.php' );
		}
		elseif ( is_file( APPSPATH . 'config' . DIRECTORY_SEPARATOR . 'autoload.php' ) )
		{
			include( APPSPATH . 'config' . DIRECTORY_SEPARATOR . 'autoload.php' );
		}

		if ( isset( $autoload ) )
		{
			$autoload = array_merge_recursive( $autoload, $system_autoload );

			foreach ( $autoload as $type => $config )
			{
				$config = array_unique( $config );

				if ( empty( $config ) || count( $config ) == 0 ) continue;

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

					case 'drivers':
						foreach ( $config as $driver => $driver_name )
						{
							if ( is_numeric( $driver ) )
							{
								$this->driver( $driver_name );
							}
							else
							{
								$this->driver( $driver, $driver_name );
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
							$this->lang( $language );
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
		}
	}
	// ------------------------------------------------------------------------
}