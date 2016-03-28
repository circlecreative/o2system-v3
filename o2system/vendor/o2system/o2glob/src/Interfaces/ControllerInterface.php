<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/9/2016
 * Time: 4:10 AM
 */

namespace O2System\Glob\Interfaces;


class ControllerInterface
{
	protected static $_instance;

	/**
	 * Class constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		static::$_instance =& $this;
	}

	// ------------------------------------------------------------------------

	final public static function instance()
	{
		if ( is_null( static::$_instance ) )
		{
			static::$_instance = new static;
		}

		return static::$_instance;
	}

	// ------------------------------------------------------------------------

	public function __get( $property )
	{
		if ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}
		elseif ( class_exists( 'O2System', FALSE ) )
		{
			return \O2System::instance()->__get( $property );
		}

		return \O2System\Glob::instance()->__get( $property );
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

	// ------------------------------------------------------------------------

	public function __call( $method, $args = array() )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		elseif ( class_exists( 'O2System', FALSE ) )
		{
			return \O2System::instance()->__call( $method, $args );
		}

		return \O2System\Glob::instance()->__call( $method, $args );

	}

	// ------------------------------------------------------------------------

	final public static function __callStatic( $method, $args = array() )
	{
		return static::instance()->__call( $method, $args );
	}
}