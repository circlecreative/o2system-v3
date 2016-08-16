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

namespace O2System\DB\Drivers\MySQL;


use O2System\DB\Exception;
use O2System\DB\Interfaces\ConnectionInterface;

class Connection extends ConnectionInterface
{
	protected $_platform = 'MySQL';

	/**
	 * Identifier escape character
	 *
	 * @var    string
	 */
	protected static $escapeCharacter = '`';

	public function connect( $persistent = TRUE )
	{
		static::$config[ 'options' ][ \PDO::ATTR_PERSISTENT ] = is_bool( $persistent ) ? $persistent : $this->_is_persistent;

		if ( static::$config[ 'strict_on' ] === TRUE )
		{
			if ( empty( static::$config[ 'options' ][ \PDO::MYSQL_ATTR_INIT_COMMAND ] ) )
			{
				static::$config[ 'options' ][ \PDO::MYSQL_ATTR_INIT_COMMAND ] = 'SET SESSION sql_mode="STRICT_ALL_TABLES"';
			}
			else
			{
				static::$config[ 'options' ][ \PDO::MYSQL_ATTR_INIT_COMMAND ] .= ', @@session.sql_mode = "STRICT_ALL_TABLES"';
			}
		}

		if ( static::$config[ 'compress' ] === TRUE )
		{
			static::$config[ 'options' ][ \PDO::MYSQL_ATTR_COMPRESS ] = TRUE;
		}

		try
		{
			if ( empty( static::$config[ 'dsn' ] ) OR static::$config[ 'dsn' ] === '' )
			{
				static::$config[ 'dsn' ] = sprintf( 'mysql:host=%s;port=%s;dbname=%s', static::$config[ 'hostname' ], static::$config[ 'port' ], $this->database );
			}

			$this->_handler = new \PDO( static::$config[ 'dsn' ], static::$config[ 'username' ], static::$config[ 'password' ], static::$config[ 'options' ] );
		}
		catch ( \PDOException $e )
		{
			// TODO: Log Error
			//print_out( static::$config );

			$this->_error = $e->getMessage();
		}

		if ( empty( $this->_handler ) )
		{
			if ( isset( static::$config[ 'failover' ] ) )
			{
				foreach ( static::$config[ 'failover' ] as $failover )
				{
					try
					{
						if ( empty( $failover[ 'dsn' ] ) )
						{
							$database = isset( $failover[ 'database' ] ) ? $failover[ 'database' ] : $this->database;

							$failover[ 'dsn' ] = sprintf( 'mysql:host=%s;port=%s;dbname=%s', $failover[ 'hostname' ], $failover[ 'port' ], $database );
						}

						$this->_handler = new \PDO( static::$config[ 'dsn' ], static::$config[ 'username' ], static::$config[ 'password' ], static::$config[ 'options' ] );

						break;
					}
					catch ( \PDOException $e )
					{
						// TODO: Log Error

						$this->_error = $e->getMessage();
					}
				}
			}
		}

		if ( empty( $this->_handler ) )
		{
			throw new Exception( 'MySQL: Unable to connect to the database' );
		}
	}

	/**
	 * Reconnect
	 *
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	public function reconnect()
	{
		$this->_handler = NULL;

		$this->connect( $this->_is_persistent );
	}

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $database
	 *
	 * @return mixed
	 */
	public function setDatabase( $database )
	{
		if ( $database === '' )
		{
			$database = $this->database;
		}

		if ( $this->_handler->query( 'USE ' . $database ) )
		{
			$this->database = $database;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 *
	 * @param bool $prefix_limit
	 *
	 * @return string
	 */
	protected function _getTablesSQLStatement( $prefix_limit = FALSE )
	{
		$sql = 'SHOW TABLES FROM ' . $this->escapeIdentifiers( $this->database );

		if ( $prefix_limit !== FALSE && $this->table_prefix !== '' )
		{
			return $sql . " LIKE '" . $this->escapeLikeString( $this->table_prefix ) . "%'";
		}

		return $sql;
	}

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function _getListColumnsSQLStatement( $table = '' )
	{
		return 'SHOW COLUMNS FROM ' . $this->protectIdentifiers( $table, TRUE, NULL, FALSE );
	}

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function _getFieldDataSQLStatement( $table = '' )
	{
		return $this->_getListColumnsSQLStatement( $table );
	}
}