<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 9:39 AM
 */

namespace O2System\Core\Traits;


trait Constant
{
	public static function getConstant( $code, $readable = FALSE )
	{
		$description = @array_search( $code, static::getConstants() );

		if ( $readable )
		{
			return readable( strtolower( $description ), TRUE );
		}

		return $description;
	}

	public static function getConstants()
	{
		$reflection = new \ReflectionClass( __CLASS__ );

		return $reflection->getConstants();
	}
}