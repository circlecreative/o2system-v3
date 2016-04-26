<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Template\Drivers;

// ------------------------------------------------------------------------

use O2System\Bootstrap\Factory\Tag;
use O2System\Glob\Interfaces\DriverInterface;

/**
 * Template Themes Driver
 *
 * @package          Template
 * @subpackage       Library
 * @category         Driver
 * @version          1.0 Build 11.09.2012
 * @author           Circle Creative Developer Team
 * @copyright        Copyright (c) 2005 - 2014
 * @license          http://www.circle-creative.com/products/o2system/license.html
 * @link             http://www.circle-creative.com
 */
// ------------------------------------------------------------------------

use IteratorAggregate;
use Countable;
use ArrayAccess;
use Traversable;

class Metadata extends DriverInterface implements IteratorAggregate, Countable, ArrayAccess
{
	/**
	 * Metadata Variables
	 *
	 * @access protected
	 *
	 * @type    array
	 */
	private $storage = array();

	/**
	 * Set Metadata Variables
	 *
	 * @access public
	 *
	 * @param   array $tags List of Metadata Tags Variables
	 *
	 * @return $this
	 */
	public function set_meta( array $meta )
	{
		$this->storage = array();
		$this->add_meta( $meta );

		return $this;
	}

	// ------------------------------------------------------------------------

	public function add_meta( $meta, $content = NULL )
	{
		if ( is_array( $meta ) )
		{
			foreach ( $meta as $name => $content )
			{
				$this->add_meta( $name, $content );
			}
		}
		elseif ( is_string( $meta ) )
		{
			if ( $meta === 'http-equiv' )
			{
				$value = key( $content );

				$this->storage[ 'http_equiv_' . $value ] = new Tag( 'meta', array(
					'http-equiv' => $value,
					'content'    => $content[ $value ],
				) );
			}
			else
			{
				$this->storage[ $meta ] = new Tag( 'meta', array(
					'name'    => $meta,
					'content' => ( is_array( $content ) ? implode( ', ', $content ) : $content ),
				) );
			}
		}
	}

	public function set_charset( $charset )
	{
		$this->_config[ 'charset' ] = $charset;
	}

	/**
	 * Whether a offset exists
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean true on success or false on failure.
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
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet( $offset, $value )
	{
		$this->add_meta( $offset, $value );
	}

	/**
	 * Offset to unset
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset( $offset )
	{
		unset( $this->storage[ $offset ] );
	}

	/**
	 * Count Num of Meta
	 *
	 * @link  http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * @since 5.1.0
	 */
	public function count()
	{
		return count( $this->storage );
	}

	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Render Metadata
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function render()
	{
		if ( ! empty( $this->storage ) )
		{
			return implode( PHP_EOL, $this->storage );
		}

		return '';
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