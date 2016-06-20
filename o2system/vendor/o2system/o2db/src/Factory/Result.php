<?php
/**
 * O2DB
 *
 * Open Source PHP Data Object Wrapper for PHP 5.4.0 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * @package     O2ORM
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, .
 * @license     http://circle-creative.com/products/o2db/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\DB\Factory;

// ------------------------------------------------------------------------

use O2System\DB\Exception;
use SeekableIterator;
use Countable;
use Serializable;
use O2System\DB\Factory\Row;

class Result implements SeekableIterator, Countable, Serializable
{
	/**
	 * O2DB Driver
	 *
	 * @type \O2System\DB\Interfaces\Driver
	 */
	protected static $_driver    = NULL;

	/**
	 * PDO Statement
	 * 
	 * @type \PDOStatement
	 */
	protected static $_statement = NULL;
	protected        $_position  = 0;
	protected        $_num_rows  = 0;
	protected        $_rows      = array();

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param   object $driver_object
	 *
	 * @access  public
	 */
	public function __construct( \O2System\DB\Interfaces\Driver $driver )
	{
		static::$_driver = $driver;
		static::$_statement = $driver->pdo_statement;

		$class = isset( $driver->row_class_name ) ? $driver->row_class_name : NULL;
		$args = isset( $driver->row_class_args ) ? $driver->row_class_args : NULL;
		$this->_fetchRows( $class, $args );
	}

	// --------------------------------------------------------------------

	protected function _fetchRows($class, $args )
	{
		if ( ! class_exists( $class ) )
		{
			$class = '\O2System\DB\Factory\Row';
		}

		if ( isset( $args ) )
		{
			while ( $row = static::$_statement->fetchObject( $class, $args ) )
			{
				$this->_num_rows++;
				$this->_rows[] = $row;
			}
		}
		else
		{
			while ( $row = static::$_statement->fetchObject( $class ) )
			{
				$this->_num_rows++;
				$this->_rows[] = $row;
			}
		}
	}

	public function seek( $position )
	{
		$position = $position < 0 ? 0 : $position;

		if ( isset( $this->_rows[ $position ] ) )
		{
			$this->_position = $position;

			return $this->_rows[ $position ];
		}

		return NULL;
	}

	public function rewind()
	{
		$this->_position = 0;

		return $this->seek( $this->_position );
	}

	public function current()
	{
		return $this->seek( $this->_position );
	}

	public function key()
	{
		return $this->_position;
	}

	public function next()
	{
		++$this->_position;

		return $this->seek( $this->_position );
	}

	public function previous()
	{
		--$this->_position;

		return $this->seek( $this->_position );
	}

	public function first()
	{
		return $this->seek( 0 );
	}

	public function last()
	{
		return $this->seek( $this->_num_rows - 1 );
	}

	public function valid()
	{
		return isset( $this->_rows[ $this->_position ] );
	}

	public function count()
	{
		if ( is_int( $this->_num_rows ) )
		{
			return $this->_num_rows;
		}

		return $this->_num_rows = count( $this->_rows );
	}

	public function serialize()
	{
		return serialize( $this->_rows );
	}

	public function unserialize( $rows )
	{
		$this->_rows = unserialize( $rows );
	}

	public function __toString()
	{
		return json_encode( $this->_rows );
	}

	public function __toArray()
	{
		$results = array();

		if ( $this->numRows() > 0 )
		{
			foreach ( $this->_rows as $row )
			{
				$results[] = $row->__toArray();
			}
		}

		return $results;
	}

	public function json()
	{
		return $this->__toString();
	}

	/**
	 * Number of rows in the result set
	 *
	 * @return    int
	 */
	public function numRows($filtered_num_rows = FALSE )
	{
		if ( $filtered_num_rows === TRUE )
		{
			$statement = preg_replace( '!\s+!', ' ', static::$_statement->queryString );
			$statements = preg_split( '/LIMIT/', $statement );
			$statements = preg_split( '/FROM/', reset( $statements ) );

			$statement = "SELECT COUNT(*) AS num_rows FROM " . trim( end( $statements ) );
			$result = static::$_driver->query( $statement );

			return $result->first()->num_rows;
		}

		return $this->count();
	}

	// --------------------------------------------------------------------

	/**
	 * Query result. Acts as a wrapper function for the following functions.
	 *
	 * @param    string $type 'object', 'array' or a custom class name
	 *
	 * @return    array
	 */
	public function result()
	{
		return $this;
	}

	// --------------------------------------------------------------------
	/**
	 * Rows
	 *
	 * Alias for Result Method
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function rows()
	{
		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Row
	 *
	 * Return single row of query result, by default it's returning the first row.
	 *
	 * @param   int $index Row Index
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function row( $position = 0 )
	{
		return $this->seek( $position );
	}

	// ------------------------------------------------------------------------

	/**
	 * The following methods are normally overloaded by the identically named
	 * methods in the platform-specific driver -- except when query caching
	 * is used. When caching is enabled we do not load the other driver.
	 * These functions are primarily here to prevent undefined function errors
	 * when a cached result object is in use. They are not otherwise fully
	 * operational due to the unavailability of the database resource IDs with
	 * cached results.
	 */

	// --------------------------------------------------------------------

	/**
	 * Number of fields in the result set
	 *
	 * Overridden by driver result classes.
	 *
	 * @return    int
	 */
	public function numFields()
	{
		return static::$_statement->columnCount();
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Field Names
	 *
	 * Generates an array of column names.
	 *
	 * Overridden by driver result classes.
	 *
	 * @return    array
	 */
	public function fieldsList()
	{
		$field_names = array();
		for ($i = 0, $c = $this->numFields(); $i < $c; $i++ )
		{
			// Might trigger an E_WARNING due to not all subdrivers
			// supporting getColumnMeta()
			$field_names[ $i ] = @static::$_statement->getColumnMeta( $i );
			$field_names[ $i ] = $field_names[ $i ][ 'name' ];
		}

		return $field_names;
	}

	// --------------------------------------------------------------------

	/**
	 * Field data
	 *
	 * Generates an array of objects containing field meta-data.
	 *
	 * Overridden by driver result classes.
	 *
	 * @return    array
	 */
	public function fieldsMetadata()
	{
		try
		{
			$result = array();

			for ($i = 0, $c = $this->numFields(); $i < $c; $i++ )
			{
				$field = static::$_statement->getColumnMeta( $i );

				$result[ $i ] = new \stdClass();
				$result[ $i ]->name = $field[ 'name' ];
				$result[ $i ]->type = $field[ 'native_type' ];
				$result[ $i ]->max_length = ( $field[ 'len' ] > 0 ) ? $field[ 'len' ] : NULL;
				$result[ $i ]->primary_key = (int) ( ! empty( $field[ 'flags' ] ) && in_array( 'primary_key', $field[ 'flags' ], TRUE ) );
			}

			return $result;
		}
		catch ( \Exception $e )
		{
			throw new Exception( $e );
		}
	}

	// --------------------------------------------------------------------

	public function free()
	{
		$this->destroy();
	}

	/**
	 * Free the result
	 *
	 * Overridden by driver result classes.
	 *
	 * @return    void
	 */
	public function destroy()
	{
		if ( is_object( static::$_statement ) )
		{
			static::$_statement = FALSE;
		}
	}

	// --------------------------------------------------------------------
}