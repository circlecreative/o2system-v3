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
			call_user_func_array( [ $this, '__reconstruct' ], $params );
		}
	}

	// ------------------------------------------------------------------------

	public static function &instance()
	{
		if ( is_null( static::$_instance ) )
		{
			static::$_instance = new static;
		}

		return static::$_instance;
	}

	// ------------------------------------------------------------------------

	public function __call( $method, $args = [ ] )
	{
		if ( method_exists( $this, camelcase( $method ) ) )
		{
			return call_user_func_array( [ $this, camelcase( $method ) ], $args );
		}
		elseif ( method_exists( $this, 'get' . studlycapcase( $method ) ) )
		{
			return call_user_func_array( [ $this, 'get' . studlycapcase( $method ) ], $args );
		}
		elseif ( $this->__isset( underscore( $method ) ) )
		{
			if ( empty( $args ) )
			{
				return $this->__get( underscore( $method ) );
			}
			elseif ( method_exists( $this->{underscore( $method )}, '__call' ) )
			{
				return call_user_func_array( [ $this->{underscore( $method )}, '__call' ], $args );
			}
		}
	}

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