<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/13/2016
 * Time: 3:06 PM
 */

namespace O2System\Template\Drivers;

use Countable;
use ArrayAccess;
use IteratorAggregate;
use O2System\Glob\Interfaces\DriverInterface;
use Sunra\PhpSimple\HtmlDomParser;
use Traversable;

class Partials extends DriverInterface implements IteratorAggregate, Countable, ArrayAccess
{
	private $storage = array();

	public function __set( $index, $content )
	{
		$this->offsetSet( $index, $content );
	}

	public function __get( $offset )
	{
		return $this->offsetGet( $offset );
	}

	public function setPartials(array $partials )
	{
		$this->storage = $partials;
	}

	public function addPartials(array $partials )
	{
		foreach ( $partials as $partial => $content )
		{
			$this->addPartial( $partial, $content );
		}
	}

	public function addPartial($index, $content )
	{
		$vars = $this->_library->_cached_vars;
		$vars[ 'partials' ] = $this;

		$content = $this->_library->view->load( $content, $vars, TRUE );

		$this->offsetSet( $index, $content );
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
		if ( $this->offsetExists( $offset ) )
		{
			return $this->storage[ $offset ];
		}

		return '';
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
}