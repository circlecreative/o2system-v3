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

namespace O2System\DB\Drivers\Pgsql;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Driver as DriverInterface;

/**
 * PDO PostgreSQL Driver Adapter Class
 *
 * Based on CodeIgniter PDO PostgreSQL Driver Adapter Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Driver extends DriverInterface
{
	/**
	 * Platform
	 *
	 * @type    string
	 */
	public $platform = 'pgsql';

	/**
	 * Database schema
	 *
	 * @type    string
	 */
	public $schema = 'public';

	// --------------------------------------------------------------------

	/**
	 * ORDER BY random keyword
	 *
	 * @type    array
	 */
	protected $_random_keywords = array( 'RANDOM()', 'RANDOM()' );

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * Builds the DSN if not already set.
	 *
	 * @param    array $params
	 *
	 * @return    void
	 */
	public function __construct( $params )
	{
		parent::__construct( $params );

		if ( empty( $this->dsn ) )
		{
			$this->dsn = 'pgsql:host=' . ( empty( $this->hostname ) ? '127.0.0.1' : $this->hostname );

			empty( $this->port ) OR $this->dsn .= ';port=' . $this->port;
			empty( $this->database ) OR $this->dsn .= ';dbname=' . $this->database;

			if ( ! empty( $this->username ) )
			{
				$this->dsn .= ';username=' . $this->username;
				empty( $this->password ) OR $this->dsn .= ';password=' . $this->password;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Database connection
	 *
	 * @param    bool $persistent
	 *
	 * @return    object
	 */
	public function connect( $persistent = FALSE )
	{
		parent::connect( $persistent );

		if ( is_object( $this->pdo_conn ) && ! empty( $this->schema ) )
		{
			$this->simple_query( 'SET search_path TO ' . $this->schema . ',public' );
		}

		return $this->pdo_conn;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @param    string $name
	 *
	 * @return    int
	 */
	public function insert_id( $name = NULL )
	{
		if ( $name === NULL && version_compare( $this->version(), '8.1', '>=' ) )
		{
			$query = $this->query( 'SELECT LASTVAL() AS ins_id' );
			$query = $query->row();

			return $query->ins_id;
		}

		return $this->pdo_conn->lastInsertId( $name );
	}

	// --------------------------------------------------------------------

	/**
	 * Determines if a query is a "write" type.
	 *
	 * @param    string    An SQL query string
	 *
	 * @return    bool
	 */
	public function is_write_type( $sql )
	{
		return (bool) preg_match( '/^\s*"?(SET|INSERT(?![^\)]+\)\s+RETURNING)|UPDATE(?!.*\sRETURNING)|DELETE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i', str_replace( array( "\r\n", "\r", "\n" ), ' ', $sql ) );
	}

	// --------------------------------------------------------------------

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 *
	 * @param    string $str
	 *
	 * @return    mixed
	 */
	public function escape( $str )
	{
		if ( is_bool( $str ) )
		{
			return ( $str ) ? 'TRUE' : 'FALSE';
		}

		return parent::escape( $str );
	}

	// --------------------------------------------------------------------

	/**
	 * ORDER BY
	 *
	 * @param    string $orderby
	 * @param    string $direction ASC, DESC or RANDOM
	 * @param    bool   $escape
	 *
	 * @return    object
	 */
	public function order_by( $orderby, $direction = '', $escape = NULL )
	{
		$direction = strtoupper( trim( $direction ) );
		if ( $direction === 'RANDOM' )
		{
			if ( ! is_float( $orderby ) && ctype_digit( (string) $orderby ) )
			{
				$orderby = ( $orderby > 1 )
					? (float) '0.' . $orderby
					: (float) $orderby;
			}

			if ( is_float( $orderby ) )
			{
				$this->simple_query( 'SET SEED ' . $orderby );
			}

			$orderby = $this->_random_keywords[ 0 ];
			$direction = '';
			$escape = FALSE;
		}

		return parent::order_by( $orderby, $direction, $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * Show table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @param    bool $prefix_limit
	 *
	 * @return    string
	 */
	protected function _list_tables_statement( $prefix_limit = FALSE )
	{
		$sql = 'SELECT "table_name" FROM "information_schema"."tables" WHERE "table_schema" = \'' . $this->schema . "'";

		if ( $prefix_limit === TRUE && $this->table_prefix !== '' )
		{
			return $sql . ' AND "table_name" LIKE \''
			. $this->escape_like_string( $this->table_prefix ) . "%' "
			. sprintf( $this->_like_escape_string, $this->_like_escape_character );
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * List column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _list_columns_statement( $table = '' )
	{
		return 'SELECT "column_name"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = ' . $this->escape( strtolower( $table ) );
	}

	// --------------------------------------------------------------------

	/**
	 * Returns an object with field data
	 *
	 * @param    string $table
	 *
	 * @return    array
	 */
	public function field_data( $table )
	{
		$sql = 'SELECT "column_name", "data_type", "character_maximum_length", "numeric_precision", "column_default"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = ' . $this->escape( strtolower( $table ) );

		if ( ( $query = $this->query( $sql ) ) === FALSE )
		{
			return FALSE;
		}
		$query = $query->result_object();

		$result = array();
		for ( $i = 0, $c = count( $query ); $i < $c; $i++ )
		{
			$result[ $i ] = new \stdClass();
			$result[ $i ]->name = $query[ $i ]->column_name;
			$result[ $i ]->type = $query[ $i ]->data_type;
			$result[ $i ]->max_length = ( $query[ $i ]->character_maximum_length > 0 ) ? $query[ $i ]->character_maximum_length : $query[ $i ]->numeric_precision;
			$result[ $i ]->default = $query[ $i ]->column_default;
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param    string $table
	 * @param    array  $values
	 *
	 * @return    string
	 */
	protected function _update_statement( $table, $values )
	{
		$this->qb_limit = FALSE;
		$this->qb_orderby = array();

		return parent::_update_statement( $table, $values );
	}

	// --------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @param    string $table  Table name
	 * @param    array  $values Update data
	 * @param    string $index  WHERE key
	 *
	 * @return    string
	 */
	protected function _update_batch_statement( $table, $values, $index )
	{
		$ids = array();
		foreach ( $values as $key => $value )
		{
			$ids[] = $value[ $index ];

			foreach ( array_keys( $value ) as $field )
			{
				if ( $field !== $index )
				{
					$final[ $field ][] = 'WHEN ' . $value[ $index ] . ' THEN ' . $value[ $field ];
				}
			}
		}

		$cases = '';
		foreach ( $final as $k => $v )
		{
			$cases .= $k . ' = (CASE ' . $index . "\n"
				. implode( "\n", $v ) . "\n"
				. 'ELSE ' . $k . ' END), ';
		}

		$this->where( $index . ' IN(' . implode( ',', $ids ) . ')', NULL, FALSE );

		return 'UPDATE ' . $table . ' SET ' . substr( $cases, 0, -2 ) . $this->_compile_where_having( 'qb_where' );
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _delete( $table )
	{
		$this->qb_limit = FALSE;

		return parent::_delete( $table );
	}

	// --------------------------------------------------------------------

	/**
	 * LIMIT
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @param    string $sql SQL Query
	 *
	 * @return    string
	 */
	protected function _limit( $sql )
	{
		return $sql . ' LIMIT ' . $this->qb_limit . ( $this->qb_offset ? ' OFFSET ' . $this->qb_offset : '' );
	}
}