<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 15/08/2016
 * Time: 17:13
 */

namespace O2System\Core\SPL\Traits;


use O2System\Core\SPL\ArrayObject;

trait ArrayFunctions
{
	public function getArrayCombine( array $keys )
	{
		$arrayCombine = array_combine( $keys, $this->getArrayCopy() );

		return new ArrayObject( $arrayCombine );
	}

	public function getArrayKeys( $searchValue = NULL, $strict = FALSE )
	{
		return array_keys( $this->getArrayCopy(), $searchValue, $strict );
	}

	public function getArrayLimit( $limit = 0 )
	{
		if ( $limit > 0 AND $this->count() > 0 )
		{
			$i = 0;
			foreach ( $this->getArrayCopy() as $key => $value )
			{
				if ( $i < $limit )
				{
					$arrayCopy[ $key ] = $value;
				}
				$i++;
			}

			return $arrayCopy;
		}

		return $this->getArrayCopy();
	}

	public function getArraySlice( $offset = 0, $limit, $preserve_keys = FALSE )
	{
		return array_slice( $this->getArrayCopy(), $offset, $limit, $preserve_keys );
	}

	public function getArraySlices( array $slices, $preserve_keys = FALSE )
	{
		$arrayCopy = $this->getArrayCopy();

		foreach ( $slices as $key => $limit )
		{
			$arraySlices[ $key ] = array_slice( $arrayCopy, 0, $limit, $preserve_keys );
		}

		return $arraySlices;
	}

	public function getArrayChunk( $size, $preserve_keys = FALSE )
	{
		return array_chunk( $this->getArrayCopy(), $size, $preserve_keys );
	}

	public function getArrayChunks( array $chunks, $preserve_keys = FALSE )
	{
		$arrayCopy = $this->getArrayCopy();

		$offset = 0;
		foreach ( $chunks as $key => $limit )
		{
			$arrayChunks[ $key ] = array_slice( $arrayCopy, $offset, $limit, $preserve_keys );
			$offset              = $limit;
		}

		return $arrayChunks;
	}

	public function getArrayShuffle( $limit = 0 )
	{
		$arrayCopy = $this->getArrayCopy( $limit );
		shuffle( $arrayCopy );

		return $arrayCopy;
	}

	public function getArrayReverse()
	{
		$arrayCopy = $this->getArrayCopy();

		return array_reverse( $arrayCopy );
	}

	/**
	 * Get Array Column
	 *
	 * Return the values from a single column in the storage
	 *
	 * @param $column
	 *
	 * @return array
	 */
	public function getArrayColumn( $column )
	{
		return array_column( $this->getArrayCopy(), $column );
	}

	/**
	 * Get Array Unique
	 *
	 * Removes duplicate values from storage
	 *
	 * @param int $sortFlags
	 *
	 * @return array
	 */
	public function getArrayUnique( $sortFlags = SORT_STRING )
	{
		return array_unique( $this->getArrayCopy(), $sortFlags );
	}

	/**
	 * Get Array Flip
	 *
	 * Returns the flipped array
	 *
	 * @return array
	 */
	public function getArrayFlip()
	{
		return array_flip( $this->getArrayCopy() );
	}

	/**
	 * Get Array Sum
	 *
	 * Calculate the sum of values in storage
	 *
	 * @return number
	 */
	public function getArraySum()
	{
		return array_sum( $this->getArrayCopy() );
	}
}