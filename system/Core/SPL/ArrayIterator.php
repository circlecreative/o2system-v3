<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 6:58 PM
 */

namespace O2System\Core\SPL;

use O2System\Core\SPL\Traits\ArrayFunctions;
use O2System\Core\SPL\Traits\ArrayStorage;

class ArrayIterator extends \ArrayIterator
{
	use ArrayFunctions;
	use ArrayStorage;

	/**
	 * Set Current
	 *
	 * Set storage current position
	 *
	 * @param $position
	 *
	 * @return void
	 */
	public function setCurrent( $position )
	{
		$this->seek( $position );
	}

	/**
	 * Set Position
	 *
	 * Set storage position
	 *
	 * @param $position
	 *
	 * @return void
	 */
	public function setPosition( $position )
	{
		$this->seek( $position );
	}

	/**
	 * First
	 *
	 * Get storage first value
	 *
	 * @return mixed|null
	 */
	public function first()
	{
		return reset( $this->getArrayCopy() );
	}

	/**
	 * Last
	 *
	 * Get array last value
	 *
	 * @return mixed
	 */
	public function last()
	{
		return end( $this->getArrayCopy() );
	}

	public function search( $value, $setPosition = FALSE )
	{
		if ( FALSE !== ( $position = array_search( $value, $this->getArrayCopy() ) ) )
		{
			if ( $setPosition === TRUE )
			{
				$this->seek( $position );
			}

			return $position;
		}

		return FALSE;
	}
}