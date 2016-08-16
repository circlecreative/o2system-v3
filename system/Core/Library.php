<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 3:20 AM
 */

namespace O2System\Core;

abstract class Library
{
	use Collectors\Config;

	/**
	 * @type Library
	 */
	protected static $instance;

	// ------------------------------------------------------------------------

	/**
	 * Libraries Class Constructor
	 *
	 * @access  public
	 */
	public function __construct( $config = [ ] )
	{
		// Create self Instance
		static::$instance =& $this;

		$this->setConfig( $config );

		// Register Exception View and Language Path
		\O2System::$environment->registerLibrary( get_called_class() );
	}

	// ------------------------------------------------------------------------

	public function __isset( $property )
	{
		return (bool) isset( $this->{$property} );
	}

	public function __set( $property, $value )
	{
		$this->{$property} = $value;
	}

	/**
	 * Get
	 * Magic method used as called class property getter
	 *
	 * @access      public
	 * @static      static class method
	 *
	 * @param   string $key property name
	 *
	 * @return mixed
	 */
	public function __get( $key )
	{
		$key = underscore( $key );

		// Try to get driver
		if ( method_exists( $this, 'isValidDriver' ) )
		{
			if ( array_key_exists( $key, $this->loadedDrivers ) )
			{
				// Try to load the driver
				return $this->loadedDrivers[ $key ];
			}
			elseif ( $this->isValidDriver( $key ) )
			{
				// Try to load the driver
				return $this->_loadDriver( $key );
			}
		}

		// Try to get Handler
		if ( method_exists( $this, 'isValidHandler' ) )
		{
			if ( array_key_exists( $key, $this->loadedHandlers ) )
			{
				// Try to load the handler
				return $this->loadedHandlers[ $key ];
			}
			elseif ( $this->isValidHandler( $key ) )
			{
				// Try to load the handler
				return $this->_loadHandler( $key );
			}
		}

		// Try to get Collection
		if ( method_exists( $this, 'isValidCollection' ) )
		{
			if ( array_key_exists( $key, $this->loadedCollections ) )
			{
				// Try to load the collection
				return $this->loadedCollections[ $key ];
			}
			elseif ( $this->isValidCollection( $key ) )
			{
				// Try to load the collection
				return $this->_loadCollection( $key );
			}
		}

		// Try to get properties
		if ( property_exists( $this, $key ) )
		{
			if ( isset( static::${$key} ) )
			{
				return static::${$key};
			}
			elseif ( isset( $this->{$key} ) )
			{
				return $this->{$key};
			}
		}

		return NULL;
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

	public static function __callStatic( $method, $args = [ ] )
	{
		return static::instance()->__call( $method, $args );
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

	// ------------------------------------------------------------------------

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