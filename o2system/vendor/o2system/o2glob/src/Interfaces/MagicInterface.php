<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/11/2016
 * Time: 9:43 PM
 */

namespace O2System\Glob\Interfaces;


trait MagicInterface
{
	public function __call( $method, $args = array() )
	{
		$method = strtolower( $method );
		$args = is_string( $args ) ? [ $args ] : $args;

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

	public static function __callStatic( $method, array $args = array() )
	{
		$class_name = get_called_class();
		$class_name = strtolower( $class_name );

		if( class_exists( 'O2System' ) )
		{
			if( \O2System::instance()->__isset( $class_name ) )
			{
				return \O2System::{$class_name}()->__call( $method, $args );
			}
		}
	}
}