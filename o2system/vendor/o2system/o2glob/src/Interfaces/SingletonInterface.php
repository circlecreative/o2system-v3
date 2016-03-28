<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/11/2016
 * Time: 9:43 PM
 */

namespace O2System\Glob\Interfaces;


trait SingletonInterface
{
	/**
	 * Instance
	 *
	 * @access  protected
	 * @type    Singleton   The reference to *Singleton* instance of this class
	 */
	protected static $_instance;

	// ------------------------------------------------------------------------

	final public function __construct()
	{
		$params = func_get_args();

		static::$_instance =& $this;

		// Reconstruct Class
		if ( method_exists( $this, '__reconstruct' ) )
		{
			call_user_func_array( array( $this, '__reconstruct' ), $params );
		}
	}

	// ------------------------------------------------------------------------

	public static function instance()
	{
		if ( is_null( static::$_instance ) )
		{
			static::$_instance = new static;
		}

		return static::$_instance;
	}

	// ------------------------------------------------------------------------

	public function __call( $method, $args = array() )
	{
		$method = strtolower( $method );

		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		elseif ( method_exists( $this, 'get_' . $method ) )
		{
			return call_user_func_array( array( $this, 'get_' . $method ), $args );
		}
		elseif ( $this->__isset( $method ) )
		{
			if ( empty( $args ) )
			{
				return $this->__get( $method );
			}
			elseif ( method_exists( $this->{$method}, '__call' ) )
			{
				return call_user_func_array( array( $this->{$method}, '__call' ), $args );
			}
		}
	}

	public static function __callStatic( $method, $args = array() )
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