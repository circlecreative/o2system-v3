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

namespace O2System\DB\Drivers\Cubrid;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Driver as DriverInterface;

/**
 * PDO Cubrid Driver Adapter Class
 *
 * Based on CodeIgniter PDO Cubrid Driver Adapter Class
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
	public $platform = 'Cubrid';

	/**
	 * Identifier escape character
	 *
	 * @type    string
	 */
	protected $_escape_character = '`';

	/**
	 * ORDER BY random keyword
	 *
	 * @type array
	 */
	protected $_random_keywords = array( 'RANDOM()', 'RANDOM(%d)' );

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
			$this->dsn = 'cubrid:host=' . ( empty( $this->hostname ) ? '127.0.0.1' : $this->hostname );

			empty( $this->port ) OR $this->dsn .= ';port=' . $this->port;
			empty( $this->database ) OR $this->dsn .= ';dbname=' . $this->database;
			empty( $this->charset ) OR $this->dsn .= ';charset=' . $this->charset;
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
		$sql = 'SHOW TABLES';

		if ( $prefix_limit === TRUE && $this->table_prefix !== '' )
		{
			return $sql . " LIKE '" . $this->escape_like_string( $this->table_prefix ) . "%'";
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
		return 'SHOW COLUMNS FROM ' . $this->protect_identifiers( $table, TRUE, NULL, FALSE );
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
		if ( ( $query = $this->query( 'SHOW COLUMNS FROM ' . $this->protect_identifiers( $table, TRUE, NULL, FALSE ) ) ) === FALSE )
		{
			return FALSE;
		}
		$query = $query->result_object();

		$result = array();
		for ( $i = 0, $c = count( $query ); $i < $c; $i++ )
		{
			$result[ $i ] = new \stdClass();
			$result[ $i ]->name = $query[ $i ]->Field;

			sscanf( $query[ $i ]->Type, '%[a-z](%d)',
			        $result[ $i ]->type,
			        $result[ $i ]->max_length
			);

			$result[ $i ]->default = $query[ $i ]->Default;
			$result[ $i ]->primary_key = (int) ( $query[ $i ]->Key === 'PRI' );
		}

		return $result;
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
					$final[ $field ][] = 'WHEN ' . $index . ' = ' . $value[ $index ] . ' THEN ' . $value[ $field ];
				}
			}
		}

		$cases = '';
		foreach ( $final as $k => $v )
		{
			$cases .= $k . " = CASE \n"
				. implode( "\n", $v ) . "\n"
				. 'ELSE ' . $k . ' END), ';
		}

		$this->where( $index . ' IN(' . implode( ',', $ids ) . ')', NULL, FALSE );

		return 'UPDATE ' . $table . ' SET ' . substr( $cases, 0, -2 ) . $this->_compile_where_having( 'qb_where' );
	}

	// --------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 *
	 * If the database does not support the TRUNCATE statement,
	 * then this method maps to 'DELETE FROM table'
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _truncate_statement( $table )
	{
		return 'TRUNCATE ' . $table;
	}

	// --------------------------------------------------------------------

	/**
	 * FROM tables
	 *
	 * Groups tables in FROM clauses if needed, so there is no confusion
	 * about operator precedence.
	 *
	 * @return    string
	 */
	protected function _from_tables()
	{
		if ( ! empty( $this->qb_join ) && count( $this->qb_from ) > 1 )
		{
			return '(' . implode( ', ', $this->qb_from ) . ')';
		}

		return implode( ', ', $this->qb_from );
	}
}