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

namespace O2System\DB\Interfaces;

// ------------------------------------------------------------------------

use O2System\DB\Collections\Queries;
use O2System\DB\Collections\Registries;
use O2System\DB\Metadata\ErrorInfo;
use O2System\DB\Metadata\Result;
use O2System\DB\Metadata\Result\Write;
use O2System\DB\QueryException;

/**
 * Class ConnectionInterface
 *
 * @package O2System\DB\Interfaces
 */
abstract class ConnectionInterface extends QueryInterface
{
	/**
	 * Platform Name
	 *
	 * @type string
	 */
	protected $_platform = 'PDO';

	/**
	 * Time Start
	 *
	 * Microtime when connection was made
	 *
	 * @var float
	 */
	protected $_time_start;

	/**
	 * Time Duration
	 *
	 * How long it took to establish connection.
	 *
	 * @var float
	 */
	protected $_time_duration;

	/**
	 * Active transaction counter
	 *
	 * @type int
	 */
	protected $_count_transactions = 0;

	/**
	 * Array of query objects that have executed
	 * on this connection.
	 *
	 * @var Queries
	 */
	protected static $queries;

	/**
	 * Connection Handler
	 *
	 * @var \PDO|mixed
	 */
	protected $_handler = NULL;

	/**
	 * Connection Statement
	 *
	 * @var \PDOStatement|mixed
	 */
	protected $_statement = NULL;

	/**
	 * Connection Registries
	 *
	 * @type Registries
	 */
	protected $_registries = NULL;

	/**
	 * Error
	 *
	 * Last error on query execution
	 *
	 * @type bool|string
	 */
	protected $_error = FALSE;

	// ------------------------------------------------------------------------

	/**
	 * Initialize
	 *
	 * Initializes the database connection/settings.
	 *
	 * @return void
	 */
	public function initialize()
	{
		if ( empty( static::$config ) )
		{
			return;
		}

		// Setup Config
		$this->_is_persistent = (bool) static::$config[ 'persistent' ];
		$this->_is_debug_mode = (bool) static::$config[ 'debug_enabled' ];
		$this->_database      = static::$config[ 'database' ];
		$this->_table_prefix  = static::$config[ 'table_prefix' ];
		$this->_registries    = new Registries();

		// Set connection time start
		$this->_time_start = microtime( TRUE );

		// Connect to the database and set the connection ID
		$this->connect( $this->_is_persistent );

		// Create connection queries
		static::$queries = new Queries();

		// Set connection time duration
		$this->_time_duration = microtime( TRUE ) - $this->_time_start;
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic method __call
	 *
	 * @param string $method Method name
	 * @param array  $args   Method arguments
	 *
	 * @return mixed
	 */
	public function __call( $method, array $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( [ $this, $method ], $args );
		}
		elseif ( method_exists( $this->_handler, $method ) )
		{
			return call_user_func_array( [ $this->_handler, $method ], $args );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic method __get
	 *
	 * @param string $property Property Name
	 *
	 * @return mixed
	 */
	public function __get( $property )
	{
		if ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}
		elseif ( @property_exists( $this->_handler, $property ) )
		{
			return $this->_handler->{$property};
		}
		elseif ( static::$config->offsetExists( $property ) )
		{
			return static::$config->offsetGet( $property );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Connect
	 *
	 * Connect to the database.
	 *
	 * @param bool $persistent
	 *
	 * @return resource
	 */
	abstract public function connect( $persistent = TRUE );

	// ------------------------------------------------------------------------

	/**
	 * Persistent Connect
	 *
	 * Create a persistent database connection.
	 *
	 * @return resource
	 */
	public function persistentConnect()
	{
		return $this->connect( TRUE );
	}

	//--------------------------------------------------------------------

	/**
	 * Reconnect
	 *
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	abstract public function reconnect();

	//--------------------------------------------------------------------

	/**
	 * Disconnect
	 *
	 * Close database connection.
	 *
	 * @return void
	 */
	public function disconnect()
	{
		if ( isset( $this->_statement ) )
		{
			$this->_statement->closeCursor();
		}

		$this->_handler   = NULL;
		$this->_statement = NULL;
	}

	//--------------------------------------------------------------------

	/**
	 * Is Connected
	 *
	 * Determine if the connection is connected
	 *
	 * @return bool
	 */
	public function isConnected()
	{
		return (bool) ( empty( $this->_handler ) ? FALSE : TRUE );
	}

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $database
	 *
	 * @return mixed
	 */
	abstract public function setDatabase( $database );

	//--------------------------------------------------------------------

	/**
	 * Get Database
	 *
	 * Returns the name of the current database being used.
	 *
	 * @return string
	 */
	public function getDatabase()
	{
		return empty( $this->database ) ? '' : $this->database;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Error
	 *
	 * Returns the last error encountered by this connection.
	 *
	 * @return mixed
	 */
	public function getError()
	{
		return $this->_error;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Error Code
	 *
	 * Returns the last error code encountered by this connection.
	 *
	 * @param bool $is_statement
	 *
	 * @return mixed
	 */
	public function getErrorCode( $is_statement = FALSE )
	{
		return ( $is_statement ? $this->_statement->errorCode() : $this->_handler->errorCode() );
	}

	//--------------------------------------------------------------------

	/**
	 * Get Error Info
	 *
	 * Returns the last error info encountered by this connection.
	 *
	 * @param bool $is_statement
	 *
	 * @return ErrorInfo
	 */
	public function getErrorInfo( $is_statement = FALSE )
	{
		return ( $is_statement ? new ErrorInfo( $this->_statement->errorInfo() ) : new ErrorInfo( $this->_handler->errorInfo() ) );
	}

	//--------------------------------------------------------------------

	/**
	 * Get Platform
	 *
	 * The name of the platform in use (MySQLi, mssql, etc)
	 *
	 * @return mixed
	 */
	public function getPlatform()
	{
		return $this->_platform;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Version
	 *
	 * Returns a string containing the version of the database being used.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		if ( $this->_registries->offsetExists( 'version' ) === FALSE )
		{
			$version = $this->_handler->query( 'select version()' )->fetchColumn();

			$this->_registries->version = mb_substr( $version, 0, 6 );
		}

		return $this->_registries->version;
	}

	//--------------------------------------------------------------------

	/**
	 * Query
	 *
	 * Execute Query
	 *
	 * @param \O2System\DB\Interfaces\QueryInterface $query
	 *
	 * @return DB|DB\Write
	 * @throws \O2System\DB\QueryException
	 */
	public function query( QueryInterface $query )
	{
		$this->_handler->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

		$result       = new Result( $this );
		$result_query = new Result\Query();
		$result_query->setTable( $query->_table );
		$result_query->setString( $query->getSQLString( TRUE ) );
		$result_query->setTimeStart();

		try
		{
			$this->_statement = $this->_handler->query( $query->getSQLString( TRUE ) );
			$result_query->setTimeEnd();

			if ( $result_query->isWriteType() === TRUE )
			{
				$result = new Write(
					[
						'rowCount'     => $this->_statement->rowCount(),
						'lastInsertId' => @$this->_handler->lastInsertId(),
					] );
			}
			else
			{
				$result->fetchRowsInto();
			}
		}
		catch ( \PDOException $e )
		{
			//  releasing the database resources associated with the PDOStatement object
			if ( isset( $this->_statement ) )
			{
				$this->_statement->closeCursor();

				try
				{
					$this->_statement = $this->_handler->query( $query->getSQLString( TRUE ) );
				}
				catch ( \PDOException $e )
				{
					if ( $this->_is_debug_mode )
					{
						throw new QueryException( $e->getMessage(), $result_query->getString() );
					}
					else
					{
						$this->_error = $e->getMessage();
						$result_query->setError( $this->getErrorInfo() );
					}
				}
			}
			else
			{
				if ( $this->_is_debug_mode )
				{
					throw new QueryException( $this->getErrorInfo()->__toString(), $result_query->getString() );
				}
				else
				{
					$this->_error = $this->getErrorInfo()->__toString();
					$result_query->setError( $this->getErrorInfo() );
				}
			}
		}

		$result->setQuery( $result_query );

		static::$queries[] = $result_query;

		return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Statement
	 *
	 * Get PDOStatement Instance
	 *
	 * @return bool|mixed|\PDOStatement
	 */
	public function getStatement()
	{
		if ( isset( $this->_statement ) )
		{
			return $this->_statement;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Close Statement
	 *
	 * Closing current PDOStatement Instance
	 */
	public function closeStatement()
	{
		if ( isset( $this->_statement ) )
		{
			$this->_statement->closeCursor();
		}

		$this->_statement = NULL;
	}

	// ------------------------------------------------------------------------

	/**
	 * Execute
	 *
	 * Execute Query Statement
	 *
	 * @see http://php.net/manual/en/pdo.query.php
	 *
	 * @return DB|DB\Write
	 * @throws \O2System\DB\QueryException
	 */
	public function execute()
	{
		$args = func_get_args();

		$this->_handler->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

		$result       = new Result( $this );
		$result_query = new Result\Query();
		$result_query->setString( $args[ 0 ] );
		$result_query->setTimeStart();

		try
		{
			$this->_statement = call_user_func_array( [ $this->_handler, 'query' ], $args );
		}
		catch ( \PDOException $e )
		{
			//  releasing the database resources associated with the PDOStatement object
			if ( isset( $this->_statement ) )
			{
				$this->_statement->closeCursor();

				try
				{
					$this->_statement = $this->_handler->query( $result_query->getString() );
				}
				catch ( \PDOException $e )
				{
					if ( $this->_is_debug_mode )
					{
						throw new QueryException( $e->getMessage(), $result_query->getString() );
					}
					else
					{
						$this->_error = $e->getMessage();
						$result_query->setError( $this->getErrorInfo() );
					}
				}
			}
			else
			{
				if ( $this->_is_debug_mode )
				{
					throw new QueryException( $this->getErrorInfo()->__toString(), $result_query->getString() );
				}
				else
				{
					$this->_error = $this->getErrorInfo()->__toString();
					$result_query->setError( $this->getErrorInfo() );
				}
			}
		}

		$result_query->setTimeEnd();

		static::$queries[] = $result_query;

		if ( isset( $this->_statement ) )
		{
			if ( $result_query->isWriteType() === FALSE )
			{
				$result->fetchRowsInto();
			}
			else
			{
				return new Write(
					[
						'rowCount'     => $this->_statement->rowCount(),
						'lastInsertId' => @$this->_handler->lastInsertId(),
					] );
			}
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Exec
	 *
	 * PDO::exec() executes an SQL statement in a single function call,
	 * returning the number of rows affected by the statement.
	 *
	 * @see http://php.net/manual/en/pdo.exec.php
	 *
	 * @param string $statement SQL Query String
	 *
	 * @return int
	 * @throws \O2System\DB\QueryException
	 */
	public function exec( $statement )
	{
		$this->_handler->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

		try
		{
			return (int) $this->_handler->exec( $statement );
		}
		catch ( \PDOException $e )
		{
			if ( $this->_is_debug_mode )
			{
				throw new QueryException( $e->getMessage(), $statement );
			}
			else
			{
				$this->_error = $e->getMessage();
			}

			return (int) 0;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Trans Begin
	 *
	 * If transaction is begin
	 *
	 * @return bool
	 */
	public function transBegin()
	{
		if ( ! $this->_count_transactions++ )
		{
			return (bool) $this->_handler->beginTransaction();
		}

		return $this->_count_transactions >= 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Trans Commit
	 *
	 * Commit all transactions
	 *
	 * @return bool
	 */
	public function transCommit()
	{
		if ( ! --$this->_count_transactions )
		{
			return (bool) $this->_handler->commit();
		}

		return $this->_count_transactions >= 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Trans Status
	 *
	 * Determine if in transaction
	 *
	 * @return bool
	 */
	public function transStatus()
	{
		return (bool) $this->_handler->inTransaction();
	}

	/**
	 * Trans Rollback
	 *
	 * Rollback transactions
	 *
	 * @return bool
	 */
	public function transRollback()
	{
		if ( $this->_count_transactions >= 0 )
		{
			$this->_count_transactions = 0;

			return (bool) $this->_handler->rollBack();
		}

		$this->_count_transactions = 0;

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Statement Debug Dump Params
	 *
	 * @return bool
	 */
	public function getStatementDebugDumpParams()
	{
		return $this->_statement->debugDumpParams();
	}

	//--------------------------------------------------------------------

	/**
	 * Get Queries
	 *
	 * Returns Queries Collections
	 *
	 * @return Queries
	 */
	public function getQueries()
	{
		return static::$queries;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Total Queries
	 *
	 * Returns the total number of queries that have been performed
	 * on this connection.
	 *
	 * @return int
	 */
	public function getTotalQueries()
	{
		return (int) static::$queries->count();
	}

	//--------------------------------------------------------------------

	/**
	 * Get Last Query
	 *
	 * Returns the last query's statement object.
	 *
	 * @return Result\Query
	 */
	public function getLastQuery()
	{
		return static::$queries->last();
	}

	//--------------------------------------------------------------------

	/**
	 * Get Last Insert ID
	 *
	 * @param null $name
	 *
	 * @return string
	 */
	public function getLastInsertId( $name = NULL )
	{
		return $this->_handler->lastInsertId( $name );
	}

	/**
	 * Get Affected Rows
	 *
	 * Get num of affected rows by INSERT|UPDATE|REPLACE|DELETE execution
	 *
	 * @return int
	 */
	public function getAffectedRows()
	{
		return $this->_statement->rowCount();
	}

	/**
	 * Get Connection Time Start
	 *
	 * Returns the time we started to connect to this database in
	 * seconds with microseconds.
	 *
	 * Used by the Debug Toolbar's timeline.
	 *
	 * @return float
	 */
	public function getConnectionTimeStart()
	{
		return $this->_time_start;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Connection Duration
	 *
	 * Returns the number of seconds with microseconds that it took
	 * to connect to the database.
	 *
	 * Used by the Debug Toolbar's timeline.
	 *
	 * @param int $decimals
	 *
	 * @return mixed
	 */
	public function getConnectionDuration( $decimals = 6 )
	{
		return number_format( $this->_time_duration, $decimals );
	}

	//--------------------------------------------------------------------

	/**
	 * Protect Identifiers
	 *
	 * This function is used extensively by the Query Builder class, and by
	 * a couple functions in this class.
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it. Some logic is necessary in order to deal with
	 * column names that include the path. Consider a query like this:
	 *
	 * SELECT hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @param    string|array
	 * @param    bool
	 * @param    mixed
	 * @param    bool
	 *
	 * @return    string
	 */
	public function protectIdentifiers( $item, $is_single_prefix = FALSE, $is_protect_identifiers = NULL, $is_field_exists = TRUE )
	{
		if ( ! is_bool( $is_protect_identifiers ) )
		{
			$is_protect_identifiers = static::$isProtectIdentifiers;
		}

		if ( is_array( $item ) )
		{
			$escaped_items = [ ];
			foreach ( $item as $key => $value )
			{
				$escaped_items[ $this->protectIdentifiers( $key ) ] = $this->protectIdentifiers(
					$value, $is_single_prefix,
					$is_protect_identifiers, $is_field_exists );
			}

			return $escaped_items;
		}
		else
		{
			// Preventing multiple escape character
			$item = str_replace( static::$escapeCharacter, '', $item );
		}

		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix. There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		//
		// Added exception for single quotes as well, we don't want to alter
		// literal strings. -- Narf
		if ( strcspn( $item, "()'" ) !== strlen( $item ) )
		{
			return $item;
		}

		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace( '/\s+/', ' ', trim( $item ) );

		// If the item has an alias declaration we remove it and set it aside.
		// Note: strripos() is used in order to support spaces in table names
		if ( $offset = strripos( $item, ' AS ' ) )
		{
			$alias = ( $is_protect_identifiers )
				? substr( $item, $offset, 4 ) . $this->escapeIdentifiers( substr( $item, $offset + 4 ) )
				: substr( $item, $offset );
			$item  = substr( $item, 0, $offset );
		}
		elseif ( $offset = strrpos( $item, ' ' ) )
		{
			$alias = ( $is_protect_identifiers )
				? ' ' . $this->escapeIdentifiers( substr( $item, $offset + 1 ) )
				: substr( $item, $offset );
			$item  = substr( $item, 0, $offset );
		}
		else
		{
			$alias = '';
		}

		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if ( strpos( $item, '.' ) !== FALSE )
		{
			$parts = explode( '.', $item );

			// Does the first segment of the exploded item match
			// one of the aliases previously identified? If so,
			// we have nothing more to do other than escape the item
			//
			// NOTE: The ! empty() condition prevents this method
			//       from breaking when QB isn't enabled.
			if ( ! empty( $this->_table_aliases ) && in_array( $parts[ 0 ], $this->_table_aliases ) )
			{
				if ( $is_protect_identifiers === TRUE )
				{
					foreach ( $parts as $key => $val )
					{
						if ( ! in_array( $val, static::$reservedIdentifiers ) )
						{
							$parts[ $key ] = $this->escapeIdentifiers( $val );
						}
					}

					$item = implode( '.', $parts );
				}

				return $item . $alias;
			}

			// Is there a table prefix defined in the config file? If not, no need to do anything
			if ( $this->_table_prefix !== '' )
			{
				// We now add the table prefix based on some logic.
				// Do we have 4 segments (hostname.database.table.column)?
				// If so, we add the table prefix to the column name in the 3rd segment.
				if ( isset( $parts[ 3 ] ) )
				{
					$i = 2;
				}
				// Do we have 3 segments (database.table.column)?
				// If so, we add the table prefix to the column name in 2nd position
				elseif ( isset( $parts[ 2 ] ) )
				{
					$i = 1;
				}
				// Do we have 2 segments (table.column)?
				// If so, we add the table prefix to the column name in 1st segment
				else
				{
					$i = 0;
				}

				// This flag is set when the supplied $item does not contain a field name.
				// This can happen when this function is being called from a JOIN.
				if ( $is_field_exists === FALSE )
				{
					$i++;
				}

				// Verify table prefix and replace if necessary
				if ( $this->_table_swap_prefix !== '' && strpos( $parts[ $i ], $this->_table_swap_prefix ) === 0 )
				{
					$parts[ $i ] = preg_replace( '/^' . $this->_table_swap_prefix . '(\S+?)/', $this->_table_prefix . '\\1', $parts[ $i ] );
				}
				// We only add the table prefix if it does not already exist
				elseif ( strpos( $parts[ $i ], $this->_table_prefix ) !== 0 )
				{
					$parts[ $i ] = $this->_table_prefix . $parts[ $i ];
				}

				// Put the parts back together
				$item = implode( '.', $parts );
			}

			if ( $is_protect_identifiers === TRUE )
			{
				$item = $this->escapeIdentifiers( $item );
			}

			return $item . $alias;
		}

		// In some cases, especially 'from', we end up running through
		// protect_identifiers twice. This algorithm won't work when
		// it contains the escapeChar so strip it out.
		$item = trim( $item, static::$escapeCharacter );

		// Is there a table prefix? If not, no need to insert it
		if ( $this->_table_prefix !== '' )
		{
			// Verify table prefix and replace if necessary
			if ( $this->_table_swap_prefix !== '' && strpos( $item, $this->_table_swap_prefix ) === 0 )
			{
				$item = preg_replace( '/^' . $this->_table_swap_prefix . '(\S+?)/', $this->_table_prefix . '\\1', $item );
			}
			// Do we prefix an item with no segments?
			elseif ( $is_single_prefix === TRUE && strpos( $item, $this->_table_prefix ) !== 0 )
			{
				$item = $this->_table_prefix . $item;
			}
		}

		if ( $is_protect_identifiers === TRUE && ! in_array( $item, static::$reservedIdentifiers ) )
		{
			$item = $this->escapeIdentifiers( $item );
		}

		return $item . $alias;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape Identifiers
	 *
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param    mixed
	 *
	 * @return    mixed
	 */
	public function escapeIdentifiers( $item )
	{
		if ( static::$escapeCharacter === '' OR empty( $item ) OR in_array( $item, static::$reservedIdentifiers ) )
		{
			return $item;
		}
		elseif ( is_array( $item ) )
		{
			foreach ( $item as $key => $value )
			{
				$item[ $key ] = $this->escapeIdentifiers( $value );
			}

			return $item;
		}
		// Avoid breaking functions and literal values inside queries
		elseif ( ctype_digit( $item ) OR $item[ 0 ] === "'" OR ( static::$escapeCharacter !== '"' && $item[ 0 ] === '"' ) OR
			strpos( $item, '(' ) !== FALSE
		)
		{
			return $item;
		}

		static $preg_escaped_string = [ ];

		if ( empty( $preg_escaped_string ) )
		{
			if ( is_array( static::$escapeCharacter ) )
			{
				$preg_escaped_string = [
					preg_quote( static::$escapeCharacter[ 0 ], '/' ),
					preg_quote( static::$escapeCharacter[ 1 ], '/' ),
					static::$escapeCharacter[ 0 ],
					static::$escapeCharacter[ 1 ],
				];
			}
			else
			{
				$preg_escaped_string[ 0 ] = $preg_escaped_string[ 1 ] = preg_quote( static::$escapeCharacter, '/' );
				$preg_escaped_string[ 2 ] = $preg_escaped_string[ 3 ] = static::$escapeCharacter;
			}
		}

		foreach ( static::$reservedIdentifiers as $id )
		{
			if ( strpos( $item, '.' . $id ) !== FALSE )
			{
				return preg_replace(
					'/' . $preg_escaped_string[ 0 ] . '?([^' . $preg_escaped_string[ 1 ] . '\.]+)' . $preg_escaped_string[ 1 ] . '?\./i',
					$preg_escaped_string[ 2 ] . '$1' . $preg_escaped_string[ 3 ] . '.', $item );
			}
		}

		return preg_replace(
			'/' . $preg_escaped_string[ 0 ] . '?([^' . $preg_escaped_string[ 1 ] . '\.]+)' . $preg_escaped_string[ 1 ] . '?(\.)?/i',
			$preg_escaped_string[ 2 ] . '$1' . $preg_escaped_string[ 3 ] . '$2', $item );
	}

	//--------------------------------------------------------------------

	/**
	 * Prefix Table
	 *
	 * Prepends a database prefix if one exists in configuration
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	public function prefixTable( $table )
	{
		return $this->_table_prefix . $table;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape
	 *
	 * Escape string
	 *
	 * @param $string
	 *
	 * @return int|string
	 */
	public function escape( $string )
	{
		if ( is_array( $string ) )
		{
			$string = array_map( [ &$this, 'escape' ], $string );

			return $string;
		}
		else if ( is_string( $string ) OR ( is_object( $string ) && method_exists( $string, '__toString' ) ) )
		{
			return "'" . $this->escapeString( $string ) . "'";
		}
		else if ( is_bool( $string ) )
		{
			return ( $string === FALSE ) ? 0 : 1;
		}
		else if ( $string === NULL )
		{
			return 'NULL';
		}

		return $string;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape String
	 *
	 * @param string|\string[] $string
	 * @param bool             $like
	 *
	 * @return array|string|\string[]
	 */
	public function escapeString( $string, $like = FALSE )
	{
		if (is_array($string))
		{
			foreach ($string as $key => $val)
			{
				$string[$key] = $this->escapeString($val, $like);
			}

			return $string;
		}
		
		$string = $this->_driverEscapeString($string);

		// escape LIKE condition wildcards
		if ($like === true)
		{
			return str_replace(
				[ static::$escapeLikeCharacter, '%', '_' ],
				[ static::$escapeLikeCharacter . static::$escapeLikeCharacter, static::$escapeLikeCharacter . '%', static::$escapeLikeCharacter . '_' ],
				$string
			);
		}

		return $string;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape Like String
	 *
	 * @param $string
	 *
	 * @return array|string|\string[]
	 */
	public function escapeLikeString( $string )
	{
		return $this->escapeString( $string, TRUE );
	}

	//--------------------------------------------------------------------


	/**
	 * Driver Escape String
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	protected function _driverEscapeString( $string )
	{
		return str_replace( "'", "\\'", remove_invisible_characters( $string ) );
	}

	//--------------------------------------------------------------------

	/**
	 * Is literal
	 *
	 * Determines if a string represents a literal value or a field name
	 *
	 * @param    string $str
	 *
	 * @return    bool
	 */
	public function isLiteral( $str )
	{
		$str = trim( $str );

		if ( empty( $str ) || ctype_digit( $str ) || (string) (float) $str === $str ||
			in_array( strtoupper( $str ), [ 'TRUE', 'FALSE' ], TRUE )
		)
		{
			return TRUE;
		}

		static $_str;

		if ( empty( $_str ) )
		{
			$_str = ( '`' !== '"' )
				? [ '"', "'" ] : [ "'" ];
		}

		return in_array( $str[ 0 ], $_str, TRUE );
	}

	//--------------------------------------------------------------------

	/**
	 * Trims a entire array recursively.
	 *
	 * @author      Jonas John
	 * @version     0.2
	 * @link        http://www.jonasjohn.de/snippets/php/trim-array.htm
	 *
	 * @param       array $array Input array
	 */
	function trim( $array )
	{
		if ( ! is_array( $array ) )
		{
			return trim( $array );
		}

		return array_map( [ &$this, 'trim' ], $array );
	}

	/**
	 * Get Databases
	 *
	 * Returns list of Databases
	 *
	 * @return mixed
	 * @throws \O2System\DB\QueryException
	 */
	public function getDatabases()
	{
		if ( isset( static::$sqlUtiliyStatements[ 'SHOW_DATABASES' ] ) )
		{
			if ( empty( $this->_registries->databases ) )
			{
				$sql_statement = static::$sqlUtiliyStatements[ 'SHOW_DATABASES' ];

				$result = $this->execute( $sql_statement );

				if ( $result->count() > 0 )
				{
					foreach ( $result as $row )
					{
						if ( ! isset( $key ) )
						{
							if ( isset( $row[ 'database' ] ) )
							{
								$key = 'database';
							}
							elseif ( isset( $row[ 'Database' ] ) )
							{
								$key = 'Database';
							}
							else
							{
								/* We have no other choice but to just get the first element's key.
								 * Due to array_shift() accepting its argument by reference, if
								 * E_STRICT is on, this would trigger a warning. So we'll have to
								 * assign it first.
								 */
								$key = $row->keys();
								$key = array_shift( $key );
							}
						}

						$this->_registries->addDatabase( $row->{$key} );
					}
				}
			}
		}

		return (array) $this->_registries->databases;
	}

	//--------------------------------------------------------------------

	/**
	 * Is Database Exists
	 *
	 * Determine if a particular database exists
	 *
	 * @param  string $database Database Name
	 *
	 * @return bool
	 */
	public function isDatabaseExists( $database )
	{
		if ( $databases = $this->getDatabases() )
		{
			return (bool) in_array( $database, $databases );
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Tables
	 *
	 * Get current database tables
	 *
	 * @param  string|null $database Database name
	 *
	 * @return array
	 * @throws \O2System\DB\QueryException
	 */
	public function getTables( $database = NULL )
	{
		$database = isset( $database ) ? $database : $this->_database;

		if ( isset( static::$sqlUtiliyStatements[ 'SHOW_TABLES' ] ) )
		{
			if ( empty( $this->_registries->tables ) )
			{
				$sql_statement = sprintf( static::$sqlUtiliyStatements[ 'SHOW_TABLES' ], $database );

				$result = $this->execute( $sql_statement );

				if ( $result->count() > 0 )
				{
					foreach ( $result as $row )
					{
						if ( ! isset( $key ) )
						{
							if ( isset( $row[ 'table_name' ] ) )
							{
								$key = 'table_name';
							}
							elseif ( isset( $row[ 'TABLE_NAME' ] ) )
							{
								$key = 'TABLE_NAME';
							}
							else
							{
								/* We have no other choice but to just get the first element's key.
								 * Due to array_shift() accepting its argument by reference, if
								 * E_STRICT is on, this would trigger a warning. So we'll have to
								 * assign it first.
								 */
								$key = $row->keys();
								$key = array_shift( $key );
							}
						}

						$this->_registries->addTable( $row->{$key} );
					}
				}
			}
		}

		return (array) $this->_registries->tables;
	}

	//--------------------------------------------------------------------

	/**
	 * Is Table Exists
	 *
	 * Determine if particular table exists
	 *
	 * @param $table
	 *
	 * @return bool
	 */
	public function isTableExists( $table )
	{
		return (bool) in_array( $this->protectIdentifiers( $table, TRUE, FALSE, FALSE ), $this->getTables() );
	}

	//--------------------------------------------------------------------

	/**
	 * Get Table Fields
	 *
	 * Get current database table fields
	 *
	 * @param string $table Table name
	 *
	 * @return array
	 * @throws \O2System\DB\QueryException
	 */
	public function getTableFields( $table )
	{
		if ( isset( static::$sqlUtiliyStatements[ 'SHOW_COLUMNS' ] ) )
		{
			if ( empty( $this->_registries->tables_fields[ $table ] ) )
			{
				$sql_statement = sprintf( static::$sqlUtiliyStatements[ 'SHOW_COLUMNS' ], $this->protectIdentifiers( $table ) );

				$result = $this->execute( $sql_statement );

				if ( $result->count() > 0 )
				{
					foreach ( $result as $row )
					{
						// Do we know from where to get the column's name?
						if ( ! isset( $key ) )
						{
							if ( isset( $row[ 'column_name' ] ) )
							{
								$key = 'column_name';
							}
							elseif ( isset( $row[ 'COLUMN_NAME' ] ) )
							{
								$key = 'COLUMN_NAME';
							}
							elseif ( isset( $row[ 'Field' ] ) )
							{
								$key = 'Field';
							}
							else
							{
								// We have no other choice but to just get the first element's key.
								$key = key( $row );
							}
						}

						$this->_registries->addTableFields( $table, new Result\Field( $row->__toArray() ) );
					}
				}
			}
		}

		return (array) $this->_registries->tables_fields[ $table ];
	}

	//--------------------------------------------------------------------

	/**
	 * Is Field Exists
	 *
	 * Determine if particular field exists
	 *
	 * @param string $field Field name
	 * @param string $table Table name
	 *
	 * @return bool
	 */
	public function isFieldExists( $field, $table )
	{
		return (bool) array_key_exists( $field, $this->getTableFields( $table ) );
	}
}