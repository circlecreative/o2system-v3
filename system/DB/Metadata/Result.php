<?php
/**
 * O2DB
 *
 * Open Source PHP Data Abstraction Layers
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative)
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
 * @package     O2DB
 * @author      PT. Lingkar Kreasi (Circle Creative)
 * @copyright   Copyright (c) 2005 - 2016, PT. Lingkar Kreasi (Circle Creative)
 * @license     http://circle-creative.com/products/o2db/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\DB\Metadata;

// ------------------------------------------------------------------------

use Countable;
use O2System\DB\Interfaces\ConnectionInterface;
use O2System\DB\Metadata\Result\Query;
use O2System\DB\Metadata\Result\Row;
use SeekableIterator;

/**
 * Class Result
 *
 * @package O2System\DB\Metadata
 */
class Result implements SeekableIterator, Countable
{
	/**
	 * Connection Instance
	 *
	 * @access  protected
	 * @type    ConnectionInterface
	 */
	protected static $db;

	/**
	 * Result Query Metadata
	 *
	 * @access  protected
	 * @type    Query
	 */
	protected $_query;

	/**
	 * SeekableIterator Offset
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $_offset = 0;

	/**
	 * Num of result rows
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $_num_rows = 0;

	/**
	 * List of Result Rows
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_rows = [ ];

	//--------------------------------------------------------------------

	/**
	 * Results constructor.
	 *
	 * @param \O2System\DB\Interfaces\ConnectionInterface $db
	 */
	public function __construct( ConnectionInterface $db )
	{
		static::$db = $db;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Query
	 *
	 * Set Query Metadata
	 *
	 * @param DB\Query $query
	 */
	public function setQuery( Query $query )
	{
		$this->_query = $query;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Rows Into
	 *
	 * @param string $class Class Name
	 * @param array  $args  Class Constructor Arguments
	 */
	public function fetchRowsInto( $class = 'O2System\DB\Metadata\Result\Row', array $args = [ ] )
	{
		if ( $statement = static::$db->getStatement() )
		{
			while ( $row = call_user_func_array( [ $statement, 'fetchObject' ], [ $class, $args ] ) )
			{
				$this->_num_rows++;
				$this->_rows[] = $row;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Seek
	 *
	 * SeekableIterator by rows offset
	 *
	 * @param int $offset
	 *
	 * @return Row|NULL
	 */
	public function seek( $offset )
	{
		$offset = $offset < 0 ? 0 : $offset;

		if ( isset( $this->_rows[ $offset ] ) )
		{
			$this->_offset = $offset;

			return $this->_rows[ $offset ];
		}

		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Rewind
	 *
	 * SeekableIterator rewind rows offset to 0
	 *
	 * @return Row|NULL
	 */
	public function rewind()
	{
		$this->_offset = 0;

		return $this->seek( $this->_offset );
	}

	// --------------------------------------------------------------------

	/**
	 * Current
	 *
	 * SeekableIterator current rows offset
	 *
	 * @return Row|NULL
	 */
	public function current()
	{
		return $this->seek( $this->_offset );
	}

	// --------------------------------------------------------------------

	/**
	 * Key
	 *
	 * SeekableIterator rows offset key
	 *
	 * @return int
	 */
	public function key()
	{
		return (int) $this->_offset;
	}

	// --------------------------------------------------------------------

	/**
	 * Next
	 *
	 * SeekableIterator next rows offset
	 *
	 * @return Row|NULL
	 */
	public function next()
	{
		++$this->_offset;

		return $this->seek( $this->_offset );
	}

	// --------------------------------------------------------------------

	/**
	 * Previous
	 *
	 * SeekableIterator previous offset
	 *
	 * @return Row|NULL
	 */
	public function previous()
	{
		--$this->_offset;

		return $this->seek( $this->_offset );
	}

	// --------------------------------------------------------------------

	/**
	 * First
	 *
	 * SeekableIterator first rows offset
	 *
	 * @return Row|NULL
	 */
	public function first()
	{
		return $this->seek( 0 );
	}

	// --------------------------------------------------------------------

	/**
	 * Last
	 *
	 * SeekableIterator last rows offset
	 *
	 * @return Row|NULL
	 */
	public function last()
	{
		return $this->seek( $this->_num_rows - 1 );
	}

	// --------------------------------------------------------------------

	/**
	 * Valid
	 *
	 * SeekableIterator valid rows offset
	 *
	 * @return Row|NULL
	 */
	public function valid()
	{
		return isset( $this->_rows[ $this->_offset ] );
	}

	// --------------------------------------------------------------------

	/**
	 * Count
	 *
	 * Num of result rows
	 *
	 * @return Row|NULL
	 */
	public function count()
	{
		if ( is_int( $this->_num_rows ) )
		{
			return $this->_num_rows;
		}

		return $this->_num_rows = count( $this->_rows );
	}

	// --------------------------------------------------------------------

	/**
	 * Count All
	 *
	 * Num of result table rows
	 *
	 * @return int
	 */
	public function countAll()
	{
		if ( empty( $this->_query ) )
		{
			print_out( 'tet' );
		}
		return (int) static::$db->countAll( $this->_query->getTable() );
	}

	// --------------------------------------------------------------------

	/**
	 * Num Rows
	 *
	 * Num of result rows ( Result::count() alias )
	 *
	 * @return    int
	 */
	public function numRows()
	{
		return $this->count();
	}

	// --------------------------------------------------------------------

	/**
	 * Row
	 *
	 * Return a single row by offset
	 *
	 * @param int $offset Rows offset, default 0
	 *
	 * @return NULL|DB\Row
	 */
	public function row( $offset = 0 )
	{
		return $this->seek( $offset );
	}

	// ------------------------------------------------------------------------

	/**
	 * Result Rows
	 *
	 * @param string $type Array|Object|Class Name
	 *
	 * @return array|Results|mixed
	 */
	public function rows( $type = 'array' )
	{
		if ( class_exists( $type ) )
		{
			return $this->fetchRowsInto( $type );
		}
		elseif ( $type === 'array' )
		{
			return $this->__toArray();
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Num Fields
	 *
	 * Return num of row fields
	 *
	 * @return int
	 */
	public function numFields()
	{
		return static::$db->getStatement()->columnCount();
	}

	// --------------------------------------------------------------------

	/**
	 * Fields
	 *
	 * List of result table fields
	 *
	 * @return mixed
	 */
	public function fields()
	{
		return array_keys( $this->first()->keys() );
	}

	// --------------------------------------------------------------------

	/**
	 * Free Result
	 *
	 * Free result statement
	 *
	 * @return void
	 */
	public function free()
	{
		static::$db->closeStatement();
	}

	// --------------------------------------------------------------------

	/**
	 * Get Query Metadata
	 *
	 * @return DB\Query
	 */
	public function getQuery()
	{
		return static::$query;
	}

	// --------------------------------------------------------------------

	/**
	 * __toArray
	 *
	 * Convert each rows into array
	 *
	 * @return array
	 */
	public function __toArray()
	{
		$results = [ ];

		if ( $this->numRows() > 0 )
		{
			foreach ( $this->_rows as $row )
			{
				$results[] = $row->__toArray();
			}
		}

		return $results;
	}

	// --------------------------------------------------------------------

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

		return call_user_func_array( 'json_encode', [ $this->_rows, $options, $depth ] );
	}

	// --------------------------------------------------------------------

	/**
	 * __toString
	 *
	 * Convert result rows into JSON String
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) json_encode( $this->_rows );
	}
}