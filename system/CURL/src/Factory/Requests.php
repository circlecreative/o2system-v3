<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 28-Jun-16
 * Time: 6:01 AM
 */

namespace O2System\CURL\Factory;


use O2System\Core\SPL\ArrayIterator;
use O2System\CURL;

class Requests extends ArrayIterator
{
	public function offsetSet( $index, $value )
	{
		if ( $value instanceof CURL )
		{
			parent::offsetSet( $index, $value );
		}
	}
}