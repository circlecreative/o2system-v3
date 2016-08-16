<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/11/2016
 * Time: 9:43 PM
 */

namespace O2System\Core\Traits;


trait Storage
{
	private   $storage          = [ ];
	protected $storage_keys_map = [ ];

	// ------------------------------------------------------------------------

	public function __set( $key, $value )
	{
		$key = underscore( $key );

		if ( array_key_exists( $key, $this->storage_keys_map ) )
		{
			$key = $this->storage_keys_map[ $key ];
		}

		return $this->storage[ $key ] = $value;
	}

	// ------------------------------------------------------------------------

	public function __get( $key )
	{
		$key = underscore( $key );

		if ( property_exists( $this, $key ) )
		{
			if ( isset( static::${$key} ) )
			{
				return static::${$key};
			}

			return $this->{$key};
		}

		return $this->__isset( $key ) ? $this->storage[ $key ] : NULL;
	}

	// ------------------------------------------------------------------------

	public function __isset( $key )
	{
		$key = underscore( $key );

		return (bool) isset( $this->storage[ $key ] );
	}

	// ------------------------------------------------------------------------

	public function __unset( $key )
	{
		$key = underscore( $key );

		unset( $this->storage[ $key ] );
	}

	// ------------------------------------------------------------------------

	public function getStorage()
	{
		return new \ArrayIterator( $this->storage );
	}
}