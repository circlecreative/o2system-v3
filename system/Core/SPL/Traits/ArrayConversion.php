<?php
/**
 * Created by PhpStorm.
 * User: mihawk37
 * Date: 14/08/16
 * Time: 11.46 PM
 */

namespace O2System\Core\SPL\Traits;


use O2System\Core\SPL\ArrayObject;

trait ArrayConversion
{
	/**
	 * __toObject
	 *
	 * Convert storage array into ArrayObject
	 *
	 * @param int $depth
	 *
	 * @return ArrayObject
	 */
	public function __toObject( $depth = 0 )
	{
		return $this->___toObjectIterator( $this->getArrayCopy(), ( $depth == 0 ? 'ALL' : $depth ) );
	}

	/**
	 * __toObjectIterator
	 *
	 * Iterate storage array into object
	 *
	 * @param        $array
	 * @param string $depth
	 * @param int    $counter
	 *
	 * @return ArrayObject
	 */
	private function ___toObjectIterator( $array, $depth = 'ALL', $counter = 0 )
	{
		$object = new ArrayObject();

		if ( $this->count() > 0 )
		{
			foreach ( $array as $key => $value )
			{
				if ( strlen( $key ) )
				{
					if ( is_array( $value ) )
					{
						if ( $depth == 'ALL' )
						{
							$object->offsetSet( $key, $this->___toObjectIterator( $value, $depth ) );
						}
						elseif ( is_numeric( $depth ) )
						{
							if ( $counter != $depth )
							{
								$object->offsetSet( $key, $this->___toObjectIterator( $value, $depth, $counter ) );
							}
							else
							{
								$object->offsetSet( $key, $value );
							}
						}
						elseif ( is_string( $depth ) && $key == $depth )
						{
							$object->offsetSet( $key, $value );
						}
						elseif ( is_array( $depth ) && in_array( $key, $depth ) )
						{
							$object->offsetSet( $key, $value );
						}
						else
						{
							$object->offsetSet( $key, $this->___toObjectIterator( $value, $depth ) );
						}
					}
					else
					{
						$object->offsetSet( $key, $value );
					}
				}
			}
		}

		return $object;
	}

	/**
	 * __toString
	 *
	 * Returning JSON Encode array copy of storage ArrayObject
	 *
	 * @return string
	 */
	public function __toString()
	{
		if ( method_exists( $this, 'render' ) )
		{
			return $this->render();
		}

		return json_encode( $this->getArrayCopy() );
	}

	// ------------------------------------------------------------------------

	/**
	 * __toJSON
	 *
	 * @see http://php.net/manual/en/function.json-encode.php
	 *
	 * @param int $options JSON encode options, default JSON_PRETTY_PRINT
	 * @param int $depth   Maximum depth of JSON encode. Must be greater than zero.
	 *
	 * @return string
	 */
	public function __toJSON( $options = JSON_PRETTY_PRINT, $depth = 512 )
	{
		$depth = $depth == 0 ? 512 : $depth;

		return call_user_func_array( 'json_encode', [ $this->getArrayCopy(), $options, $depth ] );
	}

	/**
	 * __toSerialize
	 *
	 * Convert rows into PHP serialize array
	 *
	 * @see http://php.net/manual/en/function.serialize.php
	 *
	 * @param int $options JSON encode options, default JSON_PRETTY_PRINT
	 * @param int $depth   Maximum depth of JSON encode. Must be greater than zero.
	 *
	 * @return string
	 */
	public function __toSerialize()
	{
		return serialize( $this->__toArray() );
	}

	// --------------------------------------------------------------------

	/**
	 * __toArray
	 *
	 * Returning array copy of storage ArrayObject
	 *
	 * @return string
	 */
	public function __toArray()
	{
		return $this->getArrayCopy();
	}

	// --------------------------------------------------------------------

	/**
	 * Implode
	 *
	 * Flatten array with glue
	 *
	 * @param $glue
	 *
	 * @return string
	 */
	public function implode( $glue = '' )
	{
		return implode( $glue, $this->getArrayCopy() );
	}

	// --------------------------------------------------------------------

	/**
	 * Join
	 *
	 * Flatten array with glue
	 *
	 * @param $glue
	 *
	 * @return string
	 */
	public function join( $glue = '' )
	{
		return join( $glue, $this->getArrayCopy() );
	}
}