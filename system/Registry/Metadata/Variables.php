<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 5:01 AM
 */

namespace O2System\Registry\Metadata;


use O2System\Core\SPL\ArrayObject;

class Variables extends ArrayObject
{
	public function set( array $sets )
	{
		$this->clearStorage();

		foreach ( $sets as $key => $value )
		{
			$this->add( $key, $value );
		}
	}

	public function add( $key, $value )
	{
		$this->offsetSet( underscore( $key ), $value );
	}

	public function append( $vars )
	{
		if ( count( $vars ) > 0 )
		{
			foreach ( $vars as $key => $value )
			{
				$this->add( $key, $value );
			}
		}
	}
}