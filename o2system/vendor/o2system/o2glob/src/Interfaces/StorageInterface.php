<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/11/2016
 * Time: 9:43 PM
 */

namespace O2System\Glob\Interfaces;


trait StorageInterface
{
	private   $storage      = array();
	protected $_object_maps = array();

	// ------------------------------------------------------------------------

	public function __set( $index, $value )
	{
		return $this->storage[ $index ] = $value;
	}

	// ------------------------------------------------------------------------

	public function __get( $index )
	{
		if ( property_exists( $this, $index ) )
		{
			if ( isset( static::${$index} ) )
			{
				return static::${$index};
			}

			return $this->{$index};
		}

		return $this->__isset( $index ) ? $this->storage[ $index ] : NULL;
	}

	// ------------------------------------------------------------------------

	public function __isset( $index )
	{
		return (bool) isset( $this->storage[ $index ] );
	}

	// ------------------------------------------------------------------------

	public function __unset( $index )
	{
		unset( $this->storage[ $index ] );
	}

	// ------------------------------------------------------------------------

	public function getStorage()
	{
		return new \ArrayIterator( $this->storage );
	}
}