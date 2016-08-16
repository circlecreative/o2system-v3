<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 26-Jul-16
 * Time: 7:54 AM
 */

namespace O2System\DB\Metadata\Result\Row;

use O2System\Core\SPL\ArrayObject;

class SimpleSerializeField extends ArrayObject
{
	public function __construct( $data = [ ] )
	{
		parent::__construct( [ ] );

		if ( ! empty( $data ) )
		{
			foreach ( $data as $key => $value )
			{
				$this->__set( $key, $value );
			}
		}
	}

	public function __set( $index, $value )
	{
		$this->offsetSet( $index, $value );
	}

	public function __toArray()
	{
		return $this->getArrayCopy();
	}
}