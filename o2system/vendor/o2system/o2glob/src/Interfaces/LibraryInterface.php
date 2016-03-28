<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/9/2016
 * Time: 4:06 AM
 */

namespace O2System\Glob\Interfaces;

use O2System\Glob;

abstract class LibraryInterface extends Glob
{
	/**
	 * Class Configuration
	 *
	 * @access protected
	 */
	protected $_config = array();

	/**
	 * List of library valid drivers
	 *
	 * @access  protected
	 *
	 * @type    array   driver classes list
	 */
	protected $_valid_drivers = array();

	/**
	 * List of library errors
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_errors = array();

	/**
	 * Debug Mode Flag
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $_debug_mode = FALSE;

	// ------------------------------------------------------------------------

	/**
	 * Libraries Class Constructor
	 *
	 * @access  public
	 */
	public function __reconstruct( array $config = array() )
	{
		$this->_config = array_merge( $this->_config, $config );

		// Define valid drivers
		$drivers_path = $this->__getDriversPath();

		foreach ( glob( $drivers_path . '*.php' ) as $filepath )
		{
			if ( is_file( $filepath ) )
			{
				$this->_valid_drivers[ strtolower( pathinfo( $filepath, PATHINFO_FILENAME ) ) ] = $filepath;
			}
		}

		// Register Exception View and Language Path
		if ( class_exists( 'O2System', FALSE ) )
		{
			\O2System::Exceptions()->addPath( dirname( $drivers_path ) );
			\O2System::$language->addPath( dirname( $drivers_path ) )->load( get_class_name( $this ) );
		}
		else
		{
			\O2System\Glob::Exceptions()->addPath( dirname( $drivers_path ) );
			\O2System\Glob::$language->addPath( dirname( $drivers_path ) )->load( get_class_name( $this ) );
		}

		// Create self Instance
		static::$_instance =& $this;

		// Reconstruct Class
		if ( method_exists( $this, 'initialize' ) )
		{
			call_user_func( array( $this, 'initialize' ) );
		}
	}

	// ------------------------------------------------------------------------

	final private function __getDriversPath()
	{
		$parent_class = get_parent_class( $this );
		$parent_class = $parent_class === 'O2System\Glob\Interfaces\LibraryInterface' ? get_class( $this ) : $parent_class;

		$class_realpath = ( new \ReflectionClass( $parent_class ) )->getFileName();

		$driver_paths = array(
			dirname( $class_realpath ) . DIRECTORY_SEPARATOR . 'Drivers' . DIRECTORY_SEPARATOR,
			dirname( $class_realpath ) . DIRECTORY_SEPARATOR . strtolower( pathinfo( $class_realpath, PATHINFO_FILENAME ) ) . DIRECTORY_SEPARATOR,
		);

		foreach ( $driver_paths as $driver_path )
		{
			if ( is_dir( $driver_path ) )
			{
				return $driver_path;
				break;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Get
	 * Magic method used as called class property getter
	 *
	 * @access      public
	 * @static      static class method
	 *
	 * @param   string $property property name
	 *
	 * @return mixed
	 */
	public function __get( $property )
	{
		if ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}
		elseif ( $this->__isset( $property ) )
		{
			return parent::__get( $property );
		}
		elseif ( array_key_exists( $property, $this->_valid_drivers ) )
		{
			// Try to load the driver
			return $this->_loadDriver( $property );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Load driver
	 *
	 * Separate load_driver call to support explicit driver load by library or user
	 *
	 * @param   string $driver driver class name (lowercase)
	 *
	 * @return    object    Driver class
	 */
	protected function _loadDriver( $driver )
	{
		if ( is_file( $filepath = $this->_valid_drivers[ $driver ] ) )
		{
			require_once( $filepath );

			$parent_class = get_parent_class( $this );
			$parent_class = $parent_class === 'O2System\Glob\Interfaces\LibraryInterface' ? get_class( $this ) : $parent_class;

			$class_name = $parent_class . '\\Drivers\\' . ucfirst( $driver );

			if ( class_exists( $class_name, FALSE ) )
			{
				$object = new $class_name();
				$object->setLibrary( $this );

				if ( isset( $this->_config[ $driver ] ) )
				{
					$object->setConfig( $this->_config[ $driver ] );
				}

				$this->__set( $driver, $object );

				return $this->__get( $driver );
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Library Config
	 *
	 * @access   public
	 * @final    this method can't be overwritten
	 *
	 * @param array $config
	 *
	 * @return $this
	 */
	final public function setConfig( $config, $value = NULL )
	{
		if ( is_array( $config ) )
		{
			$this->_config = $config;
		}
		elseif ( isset( $this->_config[ $config ] ) )
		{
			$this->_config[ $config ] = $value;
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Library Config Item
	 *
	 * @access  public
	 * @final   this method can't be overwritten
	 *
	 * @param string|null $item Config item index name
	 *
	 * @return array|null
	 */
	final public function getConfig( $item = NULL, $index = NULL )
	{
		if ( isset( $item ) )
		{
			if ( isset( $this->_config[ $item ] ) )
			{
				if ( isset( $index ) )
				{
					return isset( $this->_config[ $item ][ $index ] ) ? $this->_config[ $item ][ $index ] : NULL;
				}

				return $this->_config[ $item ];
			}
		}

		return $this->_config;
	}

	// ------------------------------------------------------------------------

	/**
	 * Throw Error
	 *
	 * @param   string $error Error Message
	 * @param   int    $code  Error Code
	 *
	 * @access  public
	 * @return  bool
	 */
	final public function setError( $error, $code = 0, $vars = array(), $class_name = NULL )
	{
		if ( isset( $class_name ) )
		{
			$error = '<strong>[ ' . $class_name . ' ] </strong>' . $error;
		}

		$code = (int) $code;

		if ( $code > 0 )
		{
			$this->_errors[ $code ] = $error;
		}
		else
		{
			$this->_errors[] = $error;
		}

		if ( $this->_debug_mode === FALSE )
		{
			$parent_class = get_parent_class( $this );
			$parent_class = $parent_class === 'O2System\Glob\Interfaces\Libraries' ? get_class( $this ) : $parent_class;

			$exception_class = $parent_class . '\\Exception';

			if ( class_exists( $exception_class, FALSE ) )
			{
				throw new $exception_class( $error, $code );
			}
			else
			{
				throw new \ErrorException( $error, $code );
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Error
	 *
	 * @access  public
	 * @return  array
	 */
	final public function getErrors()
	{
		return $this->_errors;
	}
}