<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 22-Jul-16
 * Time: 9:11 PM
 */

namespace O2System\DB\Drivers\SQLite;


use O2System\DB\Exception;
use O2System\DB\Interfaces\ConnectionInterface;

class Connection extends ConnectionInterface
{
	protected $_platform = 'SQLite';

	/**
	 * ORDER BY random keyword
	 *
	 * @var    array
	 */
	protected $_order_by_random_keywords = [ 'RAND()', 'RAND(%d)' ];

	/**
	 * Identifier escape character
	 *
	 * @var    string
	 */
	protected $_escape_character = '`';

	/**
	 * ESCAPE statement string
	 *
	 * @type    string
	 */
	protected $_escape_like_string = " ESCAPE '%s' ";

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
		$this->handler = NULL;

		$this->connect( $this->is_persistent );
	}

	public function connect( $persistent = TRUE )
	{
		$this->_config[ 'options' ][ \PDO::ATTR_PERSISTENT ] = is_bool( $persistent ) ? $persistent : $this->is_persistent;

		if ( $this->_config[ 'strict_on' ] === TRUE )
		{
			if ( empty( $this->_config[ 'options' ][ \PDO::MYSQL_ATTR_INIT_COMMAND ] ) )
			{
				$this->_config[ 'options' ][ \PDO::MYSQL_ATTR_INIT_COMMAND ] = 'SET SESSION sql_mode="STRICT_ALL_TABLES"';
			}
			else
			{
				$this->_config[ 'options' ][ \PDO::MYSQL_ATTR_INIT_COMMAND ] .= ', @@session.sql_mode = "STRICT_ALL_TABLES"';
			}
		}

		if ( $this->_config[ 'compress' ] === TRUE )
		{
			$this->_config[ 'options' ][ \PDO::MYSQL_ATTR_COMPRESS ] = TRUE;
		}

		try
		{
			if ( empty( $this->_config[ 'dsn' ] ) )
			{
				$this->_config[ 'dsn' ] = sprintf( 'mysql:host=%s;port=%s;dbname=%s', $this->_config[ 'hostname' ], $this->_config[ 'port' ], $this->database );
			}

			$this->handler = new \PDO( $this->_config[ 'dsn' ], $this->_config[ 'username' ], $this->_config[ 'password' ], $this->_config[ 'options' ] );
		}
		catch ( \PDOException $e )
		{
			// TODO: Log Error

			$this->_error = $e->getMessage();
		}

		if ( empty( $this->handler ) )
		{
			if ( isset( $this->_config[ 'failover' ] ) )
			{
				foreach ( $this->_config[ 'failover' ] as $failover )
				{
					try
					{
						if ( empty( $failover[ 'dsn' ] ) )
						{
							$database = isset( $failover[ 'database' ] ) ? $failover[ 'database' ] : $this->database;

							$failover[ 'dsn' ] = sprintf( 'mysql:host=%s;port=%s;dbname=%s', $failover[ 'hostname' ], $failover[ 'port' ], $database );
						}

						$this->handler = new \PDO( $this->_config[ 'dsn' ], $this->_config[ 'username' ], $this->_config[ 'password' ], $this->_config[ 'options' ] );

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

		if ( empty( $this->handler ) )
		{
			throw new Exception( 'SQLite: Unable to connect to the database' );
		}
		else
		{
			$this->builder = new Builder( $this );
		}
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

		if ( $this->handler->query( 'USE ' . $database ) )
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
		$sql = 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\'';

		if ( $prefix_limit === TRUE AND $this->table_prefix !== '' )
		{
			return $sql . ' AND "NAME" LIKE \'' . $this->escapeLikeString( $this->table_prefix ) . "%' "
			. sprintf( $this->_escape_like_string, $this->_escape_like_character );
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
	protected function _getFieldDataSQLStatement( $table = '' )
	{
		return $this->_getListColumnsSQLStatement( $table );
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
		return 'PRAGMA TABLE_INFO(' . $this->protectIdentifiers( $table, TRUE, NULL, FALSE ) . ')';
	}
}