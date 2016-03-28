<?php

namespace O2System\Template\Drivers;

use IteratorAggregate;
use Countable;
use ArrayAccess;

use O2System\Template\Collections\Widgets as WidgetsCollections;
use O2System\Bootstrap\Factory\Link;
use O2System\Bootstrap\Factory\Nav;
use O2System\Glob\Interfaces\DriverInterface;
use Traversable;

class Widgets extends DriverInterface implements IteratorAggregate, Countable, ArrayAccess
{
	private $storage = array();

	public function __get( $property )
	{
		$property = str_replace( '-', '_', $property );

		if ( $this->offsetExists( $property ) )
		{
			return $this->offsetGet( $property );
		}

		return parent::__get( $property );
	}

	public function __set( $offset, $value )
	{
		$this->offsetSet( $offset, $value );
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 *        <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator( $this->storage );
	}

	/**
	 * Whether a offset exists
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists( $offset )
	{
		return (bool) isset( $this->storage[ $offset ] );
	}

	/**
	 * Offset to retrieve
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 *
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet( $offset )
	{
		return $this->offsetExists( $offset ) ? $this->storage[ $offset ] : NULL;
	}

	/**
	 * Offset to set
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet( $offset, $value )
	{
		$offset = str_replace( '-', '_', $offset );

		$this->storage[ $offset ] = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset( $offset )
	{
		unset( $this->storage[ $offset ] );
	}

	/**
	 * Count elements of an object
	 *
	 * @link  http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 *        </p>
	 *        <p>
	 *        The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count()
	{
		return count( $this->storage );
	}

	public function set_positions( $widgets, $default_position = 'sidebar' )
	{
		foreach ( $widgets as $key => $widget )
		{
			if ( is_numeric( $key ) )
			{
				$this->set_position( $widget, $default_position );
			}
			else
			{
				$this->set_position( $key, $widget );
			}
		}
	}

	public function set_position( $widget, $position = 'sidebar' )
	{
		if ( $this->offsetExists( $widget ) )
		{
			$widget = $this->offsetGet( $widget );

			if(empty($this->storage[$position]))
			{
				$this->storage[$position] = new WidgetsCollections();
			}
			
			$this->storage[ $position ][ $widget->parameter ] = $widget;
		}

		return $this;
	}
}