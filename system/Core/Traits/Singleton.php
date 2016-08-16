<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/11/2016
 * Time: 9:43 PM
 */

namespace O2System\Core\Traits;


trait Singleton
{
	/**
	 * Instance
	 *
	 * @access  protected
	 * @type    Singleton   The reference to *Singleton* instance of this class
	 */
	protected static $instance;

	// ------------------------------------------------------------------------

	public function __construct()
	{
		$params = func_get_args();

		static::$instance =& $this;

		// Reconstruct Class
		if ( method_exists( $this, '__reconstruct' ) )
		{
			call_user_func_array( [ $this, '__reconstruct' ], $params );
		}
	}

	// ------------------------------------------------------------------------

	public static function &instance()
	{
		if ( is_null( static::$instance ) )
		{
			static::$instance = new static;
		}

		return static::$instance;
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
		elseif ( property_exists( $this, underscore( $method ) ) )
		{
			$method = underscore( $method );

			if ( isset( $this->{$method} ) )
			{
				$object =& $this->{$method};
			}
			elseif ( isset( static::${$method} ) )
			{
				$object =& static::${$method};
			}

			if ( isset( $object ) )
			{
				if ( empty( $args ) )
				{
					return $object;
				}
				elseif ( method_exists( $object, '__call' ) )
				{
					$method = $args[ 0 ];
					$args   = array_slice( $args, 1 );

					return call_user_func_array( [ $object, '__call' ], [ $method, $args ] );
				}
				elseif ( method_exists( $object, $method ) )
				{
					return call_user_func_array( [ $object, $method ], $args );
				}
			}
		}

		return NULL;
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