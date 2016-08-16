<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/11/2016
 * Time: 9:43 PM
 */

namespace O2System\Core\Traits;


trait Magic
{
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

		return NULL;
	}

	public static function __callStatic( $method, array $args = [ ] )
	{
		$class_name = get_called_class();
		$class_name = strtolower( $class_name );

		if ( \O2System::instance()->__isset( $class_name ) )
		{
			return call_user_func_array( [ 'O2System::' . $class_name . '()', '__call', $method ], $args );
			//return \O2System::{$class_name}()->__call( $method, $args );
		}
	}
}