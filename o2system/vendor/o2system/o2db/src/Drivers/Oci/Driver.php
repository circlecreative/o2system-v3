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

namespace O2System\DB\Drivers\Oci;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Driver as DriverInterface;

/**
 * PDO Oracle Driver Adapter Class
 *
 * Based on CodeIgniter PDO Oracle Driver Adapter Class
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
	public $platform = 'Oracle';

	// --------------------------------------------------------------------

	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @type    string[]
	 */
	protected $_reserved_identifiers = array( '*', 'rownum' );

	/**
	 * ORDER BY random keyword
	 *
	 * @type    array
	 */
	protected $_random_keywords = array( 'ASC', 'ASC' ); // Currently not supported

	/**
	 * COUNT string
	 *
	 * @used-by    O2DB_DB_driver::count_all()
	 * @used-by    O2DB_DB_query_builder::count_all_results()
	 *
	 * @type    string
	 */
	protected $_count_string = 'SELECT COUNT(1) AS ';

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
			$this->dsn = 'oci:dbname=';

			// Oracle has a slightly different PDO DSN format (Easy Connect),
			// which also supports pre-defined DSNs.
			if ( empty( $this->hostname ) && empty( $this->port ) )
			{
				$this->dsn .= $this->database;
			}
			else
			{
				$this->dsn .= '//' . ( empty( $this->hostname ) ? '127.0.0.1' : $this->hostname )
					. ( empty( $this->port ) ? '' : ':' . $this->port ) . '/';

				empty( $this->database ) OR $this->dsn .= $this->database;
			}

			empty( $this->charset ) OR $this->dsn .= ';charset=' . $this->charset;
		}
		elseif ( ! empty( $this->charset ) && strpos( $this->dsn, 'charset=', 4 ) === FALSE )
		{
			$this->dsn .= ';charset=' . $this->charset;
		}
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
		$sql = 'SELECT "TABLE_NAME" FROM "ALL_TABLES"';

		if ( $prefix_limit === TRUE && $this->table_prefix !== '' )
		{
			return $sql . ' WHERE "TABLE_NAME" LIKE \'' . $this->escape_like_string( $this->table_prefix ) . "%' "
			. sprintf( $this->_like_escape_string, $this->_like_escape_character );
		}

		return $sql;
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
		if ( strpos( $table, '.' ) !== FALSE )
		{
			sscanf( $table, '%[^.].%s', $owner, $table );
		}
		else
		{
			$owner = $this->username;
		}

		return 'SELECT COLUMN_NAME FROM ALL_TAB_COLUMNS
			WHERE UPPER(OWNER) = ' . $this->escape( strtoupper( $owner ) ) . '
				AND UPPER(TABLE_NAME) = ' . $this->escape( strtoupper( $table ) );
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
		if ( strpos( $table, '.' ) !== FALSE )
		{
			sscanf( $table, '%[^.].%s', $owner, $table );
		}
		else
		{
			$owner = $this->username;
		}

		$sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHAR_LENGTH, DATA_PRECISION, DATA_LENGTH, DATA_DEFAULT, NULLABLE
			FROM ALL_TAB_COLUMNS
			WHERE UPPER(OWNER) = ' . $this->escape( strtoupper( $owner ) ) . '
				AND UPPER(TABLE_NAME) = ' . $this->escape( strtoupper( $table ) );

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

			$length = ( $query[ $i ]->CHAR_LENGTH > 0 )
				? $query[ $i ]->CHAR_LENGTH : $query[ $i ]->DATA_PRECISION;
			if ( $length === NULL )
			{
				$length = $query[ $i ]->DATA_LENGTH;
			}
			$result[ $i ]->max_length = $length;

			$default = $query[ $i ]->DATA_DEFAULT;
			if ( $default === NULL && $query[ $i ]->NULLABLE === 'N' )
			{
				$default = '';
			}
			$result[ $i ]->default = $query[ $i ]->COLUMN_DEFAULT;
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert batch statement
	 *
	 * @param    string $table  Table name
	 * @param    array  $keys   INSERT keys
	 * @param    array  $values INSERT values
	 *
	 * @return    string
	 */
	protected function _insert_batch( $table, $keys, $values )
	{
		$keys = implode( ', ', $keys );
		$sql = "INSERT ALL\n";

		for ( $i = 0, $c = count( $values ); $i < $c; $i++ )
		{
			$sql .= '	INTO ' . $table . ' (' . $keys . ') VALUES ' . $values[ $i ] . "\n";
		}

		return $sql . 'SELECT * FROM dual';
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
			$this->where( 'rownum <= ', $this->qb_limit, FALSE );
			$this->qb_limit = FALSE;
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
		return 'SELECT * FROM (SELECT inner_query.*, rownum rnum FROM (' . $sql . ') inner_query WHERE rownum < ' . ( $this->qb_offset + $this->qb_limit + 1 ) . ')'
		. ( $this->qb_offset ? ' WHERE rnum >= ' . ( $this->qb_offset + 1 ) : '' );
	}
}