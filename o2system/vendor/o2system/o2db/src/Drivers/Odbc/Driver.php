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

namespace O2System\DB\Drivers\Odbc;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Driver as DriverInterface;

/**
 * PDO ODBC Driver Adapter Class
 *
 * Based on CodeIgniter PDO ODBC Driver Adapter Class
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
	public $platform = 'ODBC';

	/**
	 * Database schema
	 *
	 * @type    string
	 */
	public $schema = 'public';

	// --------------------------------------------------------------------

	/**
	 * Identifier escape character
	 *
	 * Must be empty for ODBC.
	 *
	 * @type    string
	 */
	protected $_escape_character = '';

	/**
	 * ESCAPE statement string
	 *
	 * @type    string
	 */
	protected $_like_escape_string = " {escape '%s'} ";

	/**
	 * ORDER BY random keyword
	 *
	 * @type    array
	 */
	protected $_random_keywords = array( 'RND()', 'RND(%d)' );

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
			$this->dsn = 'odbc:';

			// Pre-defined DSN
			if ( empty( $this->hostname ) && empty( $this->HOSTNAME ) && empty( $this->port ) && empty( $this->PORT ) )
			{
				if ( isset( $this->DSN ) )
				{
					$this->dsn .= 'DSN=' . $this->DSN;
				}
				elseif ( ! empty( $this->database ) )
				{
					$this->dsn .= 'DSN=' . $this->database;
				}

				return;
			}

			// If the DSN is not pre-configured - try to build an IBM DB2 connection string
			$this->dsn .= 'DRIVER=' . ( isset( $this->DRIVER ) ? '{' . $this->DRIVER . '}' : '{IBM DB2 ODBC DRIVER}' ) . ';';

			if ( isset( $this->DATABASE ) )
			{
				$this->dsn .= 'DATABASE=' . $this->DATABASE . ';';
			}
			elseif ( ! empty( $this->database ) )
			{
				$this->dsn .= 'DATABASE=' . $this->database . ';';
			}

			if ( isset( $this->HOSTNAME ) )
			{
				$this->dsn .= 'HOSTNAME=' . $this->HOSTNAME . ';';
			}
			else
			{
				$this->dsn .= 'HOSTNAME=' . ( empty( $this->hostname ) ? '127.0.0.1;' : $this->hostname . ';' );
			}

			if ( isset( $this->PORT ) )
			{
				$this->dsn .= 'PORT=' . $this->port . ';';
			}
			elseif ( ! empty( $this->port ) )
			{
				$this->dsn .= ';PORT=' . $this->port . ';';
			}

			$this->dsn .= 'PROTOCOL=' . ( isset( $this->PROTOCOL ) ? $this->PROTOCOL . ';' : 'TCPIP;' );
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
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '" . $this->schema . "'";

		if ( $prefix_limit !== FALSE && $this->table_prefix !== '' )
		{
			return $sql . " AND table_name LIKE '" . $this->escape_like_string( $this->table_prefix ) . "%' "
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
		return 'SELECT column_name FROM information_schema.columns WHERE table_name = ' . $this->escape( $table );
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
		return 'DELETE FROM ' . $table;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param    string    the table name
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
		return preg_replace( '/(^\SELECT (DISTINCT)?)/i', '\\1 TOP ' . $this->qb_limit . ' ', $sql );
	}
}