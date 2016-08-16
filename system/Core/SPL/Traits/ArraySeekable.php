<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 15/08/2016
 * Time: 11:48
 */

namespace O2System\Core\SPL\Traits;


trait ArraySeekable
{
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
	 * Rewind
	 *
	 * Rewind array storage position
	 *
	 * @return mixed|null
	 */
	public function rewind()
	{
		$value = reset( $this->storage );

		$this->position = key( $this->storage );

		return $value;
	}

	/**
	 * Seek
	 *
	 * Seek storage position
	 *
	 * @param int $position
	 *
	 * @return mixed|null
	 */
	public function seek( $position )
	{
		if ( isset( $this->storage[ $position ] ) )
		{
			foreach ( $this->storage as $key => $value )
			{
				if ( is_numeric( $key ) AND $key == $position )
				{
					$value          = next( $this->storage );
					$this->position = $position;

					return $value;
					break;
				}
				elseif ( is_string( $key ) AND $key === $position )
				{
					$value          = prev( $this->storage );
					$this->position = key( $this->storage );

					return $value;
					break;
				}
			}
		}

		return NULL;
	}

	/**
	 * Current
	 *
	 * Get storage current value
	 *
	 * @return mixed|null
	 */
	public function current()
	{
		if ( empty( $this->position ) )
		{
			$value = current( $this->storage );

			if ( $value === FALSE )
			{
				return $this->first();
			}

			$this->position = key( $this->storage );

			return $value;
		}
		else
		{
			return $this->storage[ $this->position ];
		}
	}

	/**
	 * Key
	 *
	 * Get storage current position key
	 *
	 * @return int
	 */
	public function key()
	{
		return key( $this->storage );
	}

	/**
	 * Next
	 *
	 * Go to next storage position
	 *
	 * @return mixed|null
	 */
	public function next()
	{
		$value = next( $this->storage );

		$this->position = key( $this->storage );

		return $value;
	}

	/**
	 * Previous
	 *
	 * Get storage previous value
	 *
	 * @return mixed|null
	 */
	public function previous()
	{
		$value = prev( $this->storage );

		$this->position = key( $this->storage );

		return $value;
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
		$value = reset( $this->storage );

		$this->position = key( $this->storage );

		return $value;
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
		$value = end( $this->storage );

		$this->position = key( $this->storage );

		return $value;
	}

	/**
	 * Valid
	 *
	 * Is current position exists
	 *
	 * @return bool
	 */
	public function valid()
	{
		return (bool) $this->offsetExists( $this->position );
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