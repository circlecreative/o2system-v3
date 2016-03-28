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

namespace O2System\DB\Drivers\Dblib;

// ------------------------------------------------------------------------

use O2System\DB\Exception;
use O2System\DB\Interfaces\Driver as DriverInterface;

/**
 * PDO DBLIB Driver Adapter Class
 *
 * Based on CodeIgniter PDO DBLIB Driver Adapter Class
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
	public $platform = 'DBLIB';

	// --------------------------------------------------------------------

	/**
	 * ORDER BY random keyword
	 *
	 * @type    array
	 */
	protected $_random_keywords = array( 'NEWID()', 'RAND(%d)' );

	/**
	 * Quoted identifier flag
	 *
	 * Whether to use SQL-92 standard quoted identifier
	 * (double quotes) or brackets for identifier escaping.
	 *
	 * @type    bool
	 */
	protected $_quoted_identifier;

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
			$this->dsn = $params[ 'subdriver' ] . ':host=' . ( empty( $this->hostname ) ? '127.0.0.1' : $this->hostname );

			if ( ! empty( $this->port ) )
			{
				$this->dsn .= ( DIRECTORY_SEPARATOR === '\\' ? ',' : ':' ) . $this->port;
			}

			empty( $this->database ) OR $this->dsn .= ';dbname=' . $this->database;
			empty( $this->charset ) OR $this->dsn .= ';charset=' . $this->charset;
			empty( $this->appname ) OR $this->dsn .= ';appname=' . $this->appname;
		}
		else
		{
			if ( ! empty( $this->charset ) && strpos( $this->dsn, 'charset=', 6 ) === FALSE )
			{
				$this->dsn .= ';charset=' . $this->charset;
			}

			$this->subdriver = 'dblib';
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

		if ( ! is_object( $this->pdo_conn ) )
		{
			return $this->pdo_conn;
		}

		// Determine how identifiers are escaped
		$query = $this->query( 'SELECT CASE WHEN (@@OPTIONS | 256) = @@OPTIONS THEN 1 ELSE 0 END AS qi' );
		$query = $query->row_array();
		$this->_quoted_identifier = empty( $query ) ? FALSE : (bool) $query[ 'qi' ];
		$this->_escape_character = ( $this->_quoted_identifier ) ? '"' : array( '[', ']' );

		return $this->pdo_conn;
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
		$sql = 'SELECT ' . $this->escape_identifiers( 'name' )
			. ' FROM ' . $this->escape_identifiers( 'sysobjects' )
			. ' WHERE ' . $this->escape_identifiers( 'type' ) . " = 'U'";

		if ( $prefix_limit === TRUE && $this->table_prefix !== '' )
		{
			$sql .= ' AND ' . $this->escape_identifiers( 'name' ) . " LIKE '" . $this->escape_like_string( $this->table_prefix ) . "%' "
				. sprintf( $this->_like_escape_string, $this->_like_escape_character );
		}

		return $sql . ' ORDER BY ' . $this->escape_identifiers( 'name' );
	}

	// --------------------------------------------------------------------

	/**
	 * Show column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _list_columns_statement( $table = '' )
	{
		return 'SELECT COLUMN_NAME
			FROM INFORMATION_SCHEMA.Columns
			WHERE UPPER(TABLE_NAME) = ' . $this->escape( strtoupper( $table ) );
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
		$sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, COLUMN_DEFAULT
			FROM INFORMATION_SCHEMA.Columns
			WHERE UPPER(TABLE_NAME) = ' . $this->escape( strtoupper( $table ) );

		if ( ( $query = $this->query( $sql ) ) === FALSE )
		{
			return FALSE;
		}
		$query = $query->result_object();

		$result = array();
		for ( $i = 0, $c = count( $query ); $i < $c; $i++ )
		{
			$result[ $i ] = new \stdClass();
			$result[ $i ]->name = $query[ $i ]->COLUMN_NAME;
			$result[ $i ]->type = $query[ $i ]->DATA_TYPE;
			$result[ $i ]->max_length = ( $query[ $i ]->CHARACTER_MAXIMUM_LENGTH > 0 ) ? $query[ $i ]->CHARACTER_MAXIMUM_LENGTH : $query[ $i ]->NUMERIC_PRECISION;
			$result[ $i ]->default = $query[ $i ]->COLUMN_DEFAULT;
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
		if ( $this->qb_limit )
		{
			return 'WITH ci_delete AS (SELECT TOP ' . $this->qb_limit . ' * FROM ' . $table . $this->_compile_where_having( 'qb_where' ) . ') DELETE FROM ci_delete';
		}

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
		$limit = $this->qb_offset + $this->qb_limit;

		// As of SQL Server 2005 (9.0.*) ROW_NUMBER() is supported,
		// however an ORDER BY clause is required for it to work
		if ( version_compare( $this->version(), '9', '>=' ) && $this->qb_offset && ! empty( $this->qb_orderby ) )
		{
			$orderby = $this->_compile_order_by();

			// We have to strip the ORDER BY clause
			$sql = trim( substr( $sql, 0, strrpos( $sql, $orderby ) ) );

			// Get the fields to select from our subquery, so that we can avoid
			// O2DB_rownum appearing in the actual results
			if ( count( $this->qb_select ) === 0 )
			{
				$select = '*'; // Inevitable
			}
			else
			{
				// Use only field names and their aliases, everything else is out of our scope.
				$select = array();
				$field_regexp = ( $this->_quoted_identifier )
					? '("[^\"]+")' : '(\[[^\]]+\])';
				for ( $i = 0, $c = count( $this->qb_select ); $i < $c; $i++ )
				{
					$select[] = preg_match( '/(?:\s|\.)' . $field_regexp . '$/i', $this->qb_select[ $i ], $m )
						? $m[ 1 ] : $this->qb_select[ $i ];
				}
				$select = implode( ', ', $select );
			}

			return 'SELECT ' . $select . " FROM (\n\n"
			. preg_replace( '/^(SELECT( DISTINCT)?)/i', '\\1 ROW_NUMBER() OVER(' . trim( $orderby ) . ') AS ' . $this->escape_identifiers( 'O2DB_rownum' ) . ', ', $sql )
			. "\n\n) " . $this->escape_identifiers( 'O2DB_subquery' )
			. "\nWHERE " . $this->escape_identifiers( 'O2DB_rownum' ) . ' BETWEEN ' . ( $this->qb_offset + 1 ) . ' AND ' . $limit;
		}

		return preg_replace( '/(^\SELECT (DISTINCT)?)/i', '\\1 TOP ' . $limit . ' ', $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Insert batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data.
	 *
	 * @param    string $table  Table name
	 * @param    array  $keys   INSERT keys
	 * @param    array  $values INSERT values
	 *
	 * @return    string|bool
	 */
	protected function _insert_batch( $table, $keys, $values )
	{
		// Multiple-value inserts are only supported as of SQL Server 2008
		if ( version_compare( $this->version(), '10', '>=' ) )
		{
			return parent::_insert_batch( $table, $keys, $values );
		}

		throw new Exception('Unsupported feature of the database platform you are using.');
	}
}