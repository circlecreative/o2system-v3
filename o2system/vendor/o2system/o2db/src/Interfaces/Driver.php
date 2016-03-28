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

namespace O2System\DB\Interfaces;

// ------------------------------------------------------------------------

use O2System\DB\BadMethodCallException;
use O2System\DB\ConnectionException;
use O2System\DB\Exception;
use O2System\DB\Factory\Result;
use O2System\DB\QueryException;

/**
 * Database Driver Interface Class
 *
 * This is the platform-independent base DB implementation class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * Based on CodeIgniter Database Driver Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
abstract class Driver extends Query
{
	/**
	 * Database driver
	 *
	 * @type    string
	 */
	public $platform;

	/**
	 * Data Source Name / Connect string
	 *
	 * @type    string
	 */
	public $dsn;
	/**
	 * Username
	 *
	 * @type    string
	 */
	public $username;
	/**
	 * Password
	 *
	 * @type    string
	 */
	public $password;
	/**
	 * Hostname
	 *
	 * @type    string
	 */
	public $hostname;
	/**
	 * Database name
	 *
	 * @type    string
	 */
	public $database;

	/**
	 * Character set
	 *
	 * @type    string
	 */
	public $charset = 'utf8';
	/**
	 * Collation
	 *
	 * @type    string
	 */
	public $collate = 'utf8_general_ci';

	/**
	 * Table prefix
	 *
	 * @type    string
	 */
	public $table_prefix = '';


	/**
	 * Encryption flag/data
	 *
	 * @type    mixed
	 */
	public $encrypt = FALSE;
	/**
	 * Swap Prefix
	 *
	 * @type    string
	 */
	public $swap_prefix = '';

	/**
	 * Persistent connection flag
	 *
	 * @type    bool
	 */
	public $persistent = FALSE;

	/**
	 * Connection ID
	 *
	 * @type    object|resource
	 */
	public $pdo_conn = FALSE;

	/**
	 * Result ID
	 *
	 * @type    object|resource
	 */
	public $pdo_statement = FALSE;

	/**
	 * Debug flag
	 *
	 * Whether to display error messages.
	 *
	 * @type    bool
	 */
	public $debug_enabled = FALSE;

	/**
	 * Benchmark time
	 *
	 * @type    int
	 */
	public $benchmark = 0;

	/**
	 * Executed queries count
	 *
	 * @type    int
	 */
	public $query_count = 0;

	/**
	 * Bind marker
	 *
	 * Character used to identify values in a prepared statement.
	 *
	 * @type    string
	 */
	public $bind_marker = '?';

	/**
	 * Save queries flag
	 *
	 * Whether to keep an in-memory history of queries for debugging purposes.
	 *
	 * @type    bool
	 */
	public $save_queries = TRUE;

	/**
	 * Queries list
	 *
	 * @access  public
	 * @type    string[]
	 */
	public $queries = array();

	/**
	 * Query times
	 *
	 * A list of times that queries took to execute.
	 *
	 * @type    array
	 */
	public $query_times = array();

	/**
	 * Data cache
	 *
	 * An internal generic value cache.
	 *
	 * @type    array
	 */
	public $data_cache = array();

	/**
	 * Transaction enabled flag
	 *
	 * @type    bool
	 */
	public $trans_enabled = TRUE;

	/**
	 * Strict transaction mode flag
	 *
	 * @type    bool
	 */
	public $trans_strict = TRUE;

	/**
	 * Transaction depth level
	 *
	 * @type    int
	 */
	protected $_trans_depth = 0;

	/**
	 * Transaction status flag
	 *
	 * Used with transactions to determine if a rollback should occur.
	 *
	 * @type    bool
	 */
	protected $_trans_status = TRUE;

	/**
	 * Transaction failure flag
	 *
	 * Used with transactions to determine if a transaction has failed.
	 *
	 * @type    bool
	 */
	protected $_trans_failure = FALSE;

	/**
	 * Database Cache Handler
	 *
	 * @type    \O2System\Cache
	 */
	protected $_cache_handler;

	/**
	 * Database Logger Handler
	 *
	 * @type    \O2System\Gears\Logger
	 */
	protected $_logger_handler;

	/**
	 * Protect identifiers flag
	 *
	 * @type    bool
	 */
	protected $_protect_identifiers = TRUE;
	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @type    string[]
	 */
	protected $_reserved_identifiers = array( '*' );
	/**
	 * Identifier escape character
	 *
	 * @type    string
	 */
	protected $_escape_character = '"';
	/**
	 * ESCAPE statement string
	 *
	 * @type    string
	 */
	protected $_like_escape_string = " ESCAPE '%s' ";
	/**
	 * ESCAPE character
	 *
	 * @type    string
	 */
	protected $_like_escape_character = '!';
	/**
	 * ORDER BY random keyword
	 *
	 * @type    array
	 */
	protected $_random_keywords = array( 'RAND()', 'RAND(%d)' );

	/**
	 * COUNT string
	 *
	 * @used-by    Driver::count_all()
	 * @used-by    Query::count_all_results()
	 *
	 * @access     protected
	 * @type    string
	 */
	protected $_count_string = 'SELECT COUNT(*) AS ';

	/**
	 * PDO Options
	 *
	 * @access  public
	 * @type    array
	 */
	public $options = array();

	public $row_class_name = NULL;
	public $row_class_args = NULL;
	// --------------------------------------------------------------------

	/**
	 * Database Driver Class Constructor
	 *
	 * @param    array $params
	 *
	 * @access  public
	 * @throws  Exception
	 */
	public function __construct( array $params )
	{
		if ( is_array( $params ) )
		{
			foreach ( $params as $key => $val )
			{
				$this->$key = $val;
			}
		}

		if ( preg_match( '/([^:]+):/', $this->dsn, $match ) && count( $match ) === 2 )
		{
			// If there is a minimum valid dsn string pattern found, we're done
			// This is for general PDO users, who tend to have a full DSN string.
			$this->platform = $match[ 1 ];

			return;
		}
		// Legacy support for DSN specified in the hostname field
		elseif ( preg_match( '/([^:]+):/', $this->hostname, $match ) && count( $match ) === 2 )
		{
			$this->dsn = $this->hostname;
			$this->hostname = NULL;
			$this->platform = $match[ 1 ];

			return;
		}
		elseif ( in_array( $this->platform, array( 'mssql', 'sybase' ), TRUE ) )
		{
			$this->platform = 'dblib';
		}
		elseif ( ! in_array( $this->platform, array( 'cubrid', 'dblib', 'firebird', 'ibm', 'informix', 'mysql', 'oci', 'odbc', 'pgsql', 'sqlite', 'sqlsrv' ), TRUE ) )
		{
			if ( $this->debug_enabled )
			{
				throw new Exception( 'Invalid or non-existent PDO subdriver' );
			}
		}
	}

	// --------------------------------------------------------------------

	public function __call( $method, $args = array() )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}

		throw new BadMethodCallException( 'DB_UNSUPPORTEDMETHODCALL', 0, [ $method ] );
	}

	/**
	 * Database connection
	 *
	 * @param    bool $persistent
	 *
	 * @return    object
	 */
	public function connect( $persistent = FALSE )
	{
		/* If an established connection is available, then there's
		 * no need to connect and select the database.
		 *
		 * Depending on the database driver, conn_id can be either
		 * boolean TRUE, a resource or an object.
		 */
		if ( $this->pdo_conn )
		{
			return TRUE;
		}
		// ----------------------------------------------------------------

		$this->persistent = $persistent === TRUE ? TRUE : $this->persistent;
		$this->options[ \PDO::ATTR_PERSISTENT ] = $this->persistent;

		try
		{
			// Connect to the database and set the connection ID
			$this->pdo_conn = new \PDO( $this->dsn, $this->username, $this->password, $this->options );
		}
		catch ( \PDOException $e )
		{
			throw new ConnectionException( NULL, 0, $e );
		}

		// No connection resource? Check if there is a failover else throw an error

		if ( ! $this->pdo_conn )
		{
			// Check if there is a failover set
			if ( ! empty( $this->failover ) && is_array( $this->failover ) )
			{
				// Go over all the failovers
				foreach ( $this->failover as $failover )
				{
					// Replace the current settings with those of the failover
					foreach ( $failover as $key => $val )
					{
						$this->$key = $val;
					}

					// Try to connect
					$this->pdo_conn = new \PDO( $this->dsn, $this->username, $this->password, $this->options );

					// If a connection is made break the foreach loop
					if ( $this->pdo_conn )
					{
						break;
					}
				}
			}

			// We still don't have a connection?
			if ( ! $this->pdo_conn )
			{
				throw new Exception( 'Unable to connect to the database' );
			}
		}
	}
	// --------------------------------------------------------------------

	/**
	 * Reconnect
	 *
	 * Keep / reestablish the db connection if no queries have been
	 * sent for a length of time exceeding the server's idle timeout.
	 *
	 * This is just a dummy method to allow drivers without such
	 * functionality to not declare it, while others will override it.
	 *
	 * @return      void
	 */
	public function reconnect()
	{
		if ( ! $this->pdo_conn )
		{
			$this->connect();
		}
	}

	// --------------------------------------------------------------------

	public function is_connected()
	{
		if ( $this->pdo_conn instanceof \PDO )
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * The name of the platform in use (mysql, mssql, etc...)
	 *
	 * @return    string
	 */
	public function platform()
	{
		return $this->platform;
	}
	// --------------------------------------------------------------------
	/**
	 * Database version number
	 *
	 * Returns a string containing the version of the database being used.
	 * Most drivers will override this method.
	 *
	 * @return    string
	 */
	public function version()
	{
		if ( isset( $this->data_cache[ 'version' ] ) )
		{
			return $this->data_cache[ 'version' ];
		}
		// Not all subdrivers support the getAttribute() method
		try
		{
			return $this->data_cache[ 'version' ] = $this->pdo_conn->getAttribute( \PDO::ATTR_SERVER_VERSION );
		}
		catch ( \PDOException $e )
		{
			$result = $this->query( 'SELECT VERSION() AS ver' )->row();

			return $this->data_cache[ 'version' ] = $result->ver;
		}
	}

	// --------------------------------------------------------------------

	public function set_row_class( $class_name, $args = NULL )
	{
		$this->row_class_name = $class_name;

		if ( isset( $args ) )
		{
			$this->row_class_args = $args;
		}
	}

	/**
	 * Execute the query
	 *
	 * Accepts an SQL string as input and returns a result object upon
	 * successful execution of a "read" type query. Returns boolean TRUE
	 * upon successful execution of a "write" type query. Returns boolean
	 * FALSE upon failure, and if the $db_debug variable is set to TRUE
	 * will raise an error.
	 *
	 * @param    string $sql
	 * @param    array  $binds         = FALSE        An array of binding data
	 * @param    bool   $return_object = NULL
	 *
	 * @return    mixed
	 */
	public function query( $sql, $binds = FALSE, $return_object = NULL )
	{
		if ( $sql === '' )
		{
			throw new Exception( 'Cannot run an empty query string' );
		}
		elseif ( ! is_bool( $return_object ) )
		{
			$return_object = ! $this->is_write_type( $sql );
		}

		// Verify table prefix and replace if necessary
		if ( $this->table_prefix !== '' AND
			$this->swap_prefix !== '' AND
			$this->table_prefix !== $this->swap_prefix
		)
		{
			$sql = preg_replace( '/(\W)' . $this->swap_prefix . '(\S+?)/', '\\1' . $this->table_prefix . '\\2', $sql );
		}

		// Compile binds if needed
		if ( $binds !== FALSE )
		{
			$sql = $this->compile_binds( $sql, $binds );
		}

		// Is query caching enabled? If the query is a "read type"
		// we will load the caching class and return the previously
		// cached query if it exists
		if ( isset( $this->_cache_handler ) AND
			$this->_cache_enabled === TRUE AND
			$return_object === TRUE
		)
		{
			if ( FALSE !== ( $cache = $this->_cache_handler->get( $sql ) ) )
			{
				return $cache;
			}
		}

		// Save the query for debugging
		if ( $this->save_queries === TRUE )
		{
			$this->queries[] = $sql;
		}

		// Start the Query Timer
		$time_start = microtime( TRUE );

		// Run the Query
		if ( FALSE === ( $this->pdo_statement = $this->execute( $sql ) ) )
		{
			if ( $this->save_queries === TRUE )
			{
				$this->query_times[] = 0;
			}

			// This will trigger a rollback if transactions are being used
			$this->_trans_status = FALSE;

			// Grab the error now, as we might run some additional queries before displaying the error
			$error = $this->error();

			throw new Exception( $error[ 'message' ], $error[ 'code' ], $sql );
		}

		// Stop and aggregate the query time results
		$time_end = microtime( TRUE );
		$this->benchmark += $time_end - $time_start;

		if ( $this->save_queries === TRUE )
		{
			$this->query_times[] = $time_end - $time_start;
		}

		// Increment the query counter
		$this->query_count++;

		$action = substr( $sql, 0, 6 );
		$action = trim( $action );

		if ( in_array( $action, [ 'INSERT', 'UPDATE', 'DELETE' ] ) )
		{
			return $this;
		}

		return new Result( $this );
	}
	// --------------------------------------------------------------------

	/**
	 * Simple Query
	 * This is a simplified version of the query() function. Internally
	 * we only use it when running transaction commands since they do
	 * not require all the features of the main query() function.
	 *
	 * @param    string    the sql query
	 *
	 * @return    mixed
	 */
	public function execute( $sql )
	{
		try
		{
			return $this->pdo_conn->query( $sql );
		}
		catch ( \PDOException $e )
		{
			throw  new QueryException( $e, $sql );
		}
	}
	// --------------------------------------------------------------------
	/**
	 * Disable Transactions
	 * This permits transactions to be disabled at run-time.
	 *
	 * @return    void
	 */
	public function trans_off()
	{
		$this->trans_enabled = FALSE;
	}
	// --------------------------------------------------------------------

	/**
	 * Enable/disable Transaction Strict Mode
	 * When strict mode is enabled, if you are running multiple groups of
	 * transactions, if one group fails all groups will be rolled back.
	 * If strict mode is disabled, each group is treated autonomously, meaning
	 * a failure of one group will not affect any others
	 *
	 * @param    bool $mode = TRUE
	 *
	 * @return    void
	 */
	public function trans_strict( $mode = TRUE )
	{
		$this->trans_strict = is_bool( $mode ) ? $mode : TRUE;
	}
	// --------------------------------------------------------------------

	/**
	 * Start Transaction
	 *
	 * @param    bool $test_mode = FALSE
	 *
	 * @return    void
	 */
	public function trans_start( $test_mode = FALSE )
	{
		if ( ! $this->trans_enabled )
		{
			return;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ( $this->_trans_depth > 0 )
		{
			$this->_trans_depth += 1;

			return;
		}

		$this->trans_begin( $test_mode );
		$this->_trans_depth += 1;
	}
	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @param    bool $test_mode
	 *
	 * @return    bool
	 */
	public function trans_begin( $test_mode = FALSE )
	{
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ( ! $this->trans_enabled OR $this->_trans_depth > 0 )
		{
			return TRUE;
		}

		// Reset the transaction failure flag.
		// If the $test_mode flag is set to TRUE transactions will be rolled back
		// even if the queries produce a successful result.
		$this->_trans_failure = ( $test_mode === TRUE );

		return $this->pdo_conn->beginTransaction();
	}

	// --------------------------------------------------------------------

	/**
	 * Complete Transaction
	 *
	 * @return    bool
	 */
	public function trans_complete()
	{
		if ( ! $this->trans_enabled )
		{
			return FALSE;
		}
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ( $this->_trans_depth > 1 )
		{
			$this->_trans_depth -= 1;

			return TRUE;
		}
		else
		{
			$this->_trans_depth = 0;
		}
		// The query() function will set this flag to FALSE in the event that a query failed
		if ( $this->_trans_status === FALSE OR $this->_trans_failure === TRUE )
		{
			$this->trans_rollback();

			// If we are NOT running in strict mode, we will reset
			// the _trans_status flag so that subsequent groups of transactions
			// will be permitted.
			if ( $this->trans_strict === FALSE )
			{
				$this->_trans_status = TRUE;
			}

			if ( isset( $this->_logger_handler ) )
			{
				$this->_logger_handler->debug( 'Database: Transaction Failure' );
			}

			return FALSE;
		}

		return $this->pdo_conn->commit();
	}
	// --------------------------------------------------------------------

	/**
	 * Lets you retrieve the transaction flag to determine if it has failed
	 *
	 * @return    bool
	 */
	public function trans_status()
	{
		return $this->_trans_status;
	}
	// --------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return    bool
	 */
	public function trans_commit()
	{
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ( ! $this->trans_enabled OR $this->_trans_depth > 0 )
		{
			return TRUE;
		}

		return $this->pdo_conn->commit();
	}

	// --------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return    bool
	 */
	public function trans_rollback()
	{
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ( ! $this->trans_enabled OR $this->_trans_depth > 0 )
		{
			return TRUE;
		}

		return $this->pdo_conn->rollBack();
	}

	// --------------------------------------------------------------------

	/**
	 * Compile Bindings
	 *
	 * @param    string    the sql statement
	 * @param    array     an array of bind data
	 *
	 * @return    string
	 */
	public function compile_binds( $sql, $binds )
	{
		if ( empty( $binds ) OR empty( $this->bind_marker ) OR strpos( $sql, $this->bind_marker ) === FALSE )
		{
			return $sql;
		}
		elseif ( ! is_array( $binds ) )
		{
			$binds = array( $binds );
			$bind_count = 1;
		}
		else
		{
			// Make sure we're using numeric keys
			$binds = array_values( $binds );
			$bind_count = count( $binds );
		}
		// We'll need the marker length later
		$ml = strlen( $this->bind_marker );
		// Make sure not to replace a chunk inside a string that happens to match the bind marker
		if ( $c = preg_match_all( "/'[^']*'/i", $sql, $matches ) )
		{
			$c = preg_match_all( '/' . preg_quote( $this->bind_marker, '/' ) . '/i',
			                     str_replace( $matches[ 0 ],
			                                  str_replace( $this->bind_marker, str_repeat( ' ', $ml ), $matches[ 0 ] ),
			                                  $sql, $c ),
			                     $matches, PREG_OFFSET_CAPTURE );
			// Bind values' count must match the count of markers in the query
			if ( $bind_count !== $c )
			{
				return $sql;
			}
		}
		elseif ( ( $c = preg_match_all( '/' . preg_quote( $this->bind_marker, '/' ) . '/i', $sql, $matches, PREG_OFFSET_CAPTURE ) ) !== $bind_count )
		{
			return $sql;
		}
		do
		{
			$c--;
			$escaped_value = $this->escape( $binds[ $c ] );
			if ( is_array( $escaped_value ) )
			{
				$escaped_value = '(' . implode( ',', $escaped_value ) . ')';
			}
			$sql = substr_replace( $sql, $escaped_value, $matches[ 0 ][ $c ][ 1 ], $ml );
		}
		while ( $c !== 0 );

		return $sql;
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
		return (bool) preg_match( '/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i', $sql );
	}
	// --------------------------------------------------------------------

	/**
	 * Calculate the aggregate query elapsed time
	 *
	 * @param    int    The number of decimal places
	 *
	 * @return    string
	 */
	public function elapsed_time( $decimals = 6 )
	{
		return number_format( $this->benchmark, $decimals );
	}
	// --------------------------------------------------------------------

	/**
	 * Returns the total number of queries
	 *
	 * @return    int
	 */
	public function total_queries()
	{
		return $this->query_count;
	}
	// --------------------------------------------------------------------

	/**
	 * Returns the last query that was executed
	 *
	 * @return    string
	 */
	public function last_query()
	{
		return end( $this->queries );
	}
	// --------------------------------------------------------------------
	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 * Sets boolean and null types
	 *
	 * @param    string
	 *
	 * @return    mixed
	 */
	public function escape( $string )
	{
		if ( is_array( $string ) )
		{
			$string = array_map( array( &$this, 'escape' ), $string );

			return $string;
		}
		elseif ( is_string( $string ) OR ( is_object( $string ) && method_exists( $string, '__toString' ) ) )
		{
			return "'" . $this->escape_string( $string ) . "'";
		}
		elseif ( is_bool( $string ) )
		{
			return ( $string === FALSE ) ? 0 : 1;
		}
		elseif ( $string === NULL )
		{
			return 'NULL';
		}

		return $string;
	}
	// --------------------------------------------------------------------

	/**
	 * Escape String
	 *
	 * @param    string|string[] $string Input string
	 * @param    bool            $like   Whether or not the string will be used in a LIKE condition
	 *
	 * @return    string
	 */
	public function escape_string( $string, $like = FALSE )
	{
		if ( is_array( $string ) )
		{
			foreach ( $string as $key => $val )
			{
				$string[ $key ] = $this->escape_string( $val, $like );
			}

			return $string;
		}
		$string = $this->_escape_string( $string );

		// escape LIKE condition wildcards
		if ( $like === TRUE )
		{
			return str_replace(
				array( $this->_like_escape_character, '%', '_' ),
				array( $this->_like_escape_character . $this->_like_escape_character, $this->_like_escape_character . '%', $this->_like_escape_character . '_' ),
				$string
			);
		}

		return $string;
	}
	// --------------------------------------------------------------------

	/**
	 * Escape LIKE String
	 *
	 * Calls the individual driver for platform
	 * specific escaping for LIKE conditions
	 *
	 * @param    string|string[]
	 *
	 * @return    mixed
	 */
	public function escape_like_string( $string )
	{
		return $this->escape_string( $string, TRUE );
	}
	// --------------------------------------------------------------------


	/**
	 * Platform-dependant string escape
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	protected function _escape_string( $string )
	{
		// Escape the string
		$string = $this->pdo_conn->quote( $string );

		// If there are duplicated quotes, trim them away
		return ( $string[ 0 ] === "'" )
			? substr( $string, 1, -1 )
			: $string;
	}
	// --------------------------------------------------------------------
	/**
	 * Primary
	 *
	 * Retrieves the primary key. It assumes that the row in the first
	 * position is the primary key
	 *
	 * @param    string $table Table name
	 *
	 * @return    string
	 */
	public function primary( $table )
	{
		$fields = $this->list_fields( $table );

		return is_array( $fields ) ? current( $fields ) : FALSE;
	}
	// --------------------------------------------------------------------
	/**
	 * "Count All" query
	 *
	 * Generates a platform-specific query string that counts all records in
	 * the specified database
	 *
	 * @param    string
	 *
	 * @return    int
	 */
	public function count_rows( $table = '' )
	{
		if ( $table === '' )
		{
			return 0;
		}
		$query = $this->query( $this->_count_string . $this->escape_identifiers( 'numrows' ) . ' FROM ' . $this->protect_identifiers( $table, TRUE, NULL, FALSE ) );

		if ( $query->num_rows() === 0 )
		{
			return 0;
		}
		$query = $query->row();

		$this->_reset_select();

		return (int) $query->numrows;
	}
	// --------------------------------------------------------------------
	/**
	 * Returns an array of table names
	 *
	 * @param    string $constrain_by_prefix = FALSE
	 *
	 * @return    array
	 */
	public function list_tables( $constrain_by_prefix = FALSE )
	{
		// Is there a cached result?
		if ( isset( $this->data_cache[ 'table_names' ] ) )
		{
			return $this->data_cache[ 'table_names' ];
		}

		if ( FALSE === ( $sql = $this->_list_tables_statement( $constrain_by_prefix ) ) )
		{
			return FALSE;
		}

		$this->data_cache[ 'table_names' ] = array();

		$results = $this->query( $sql );

		if ( $results->num_rows() > 0 )
		{
			foreach ( $results as $row )
			{
				// Do we know from which column to get the table name?
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
						$key = $row->fields_list();
						$key = reset( $key );
					}
				}

				$this->data_cache[ 'table_names' ][] = $row[ $key ];
			}
		}

		return $this->data_cache[ 'table_names' ];
	}
	// --------------------------------------------------------------------

	/**
	 * SQL Statement List Database Tables
	 *
	 * @param   bool $prefix_limit
	 *
	 * @access  protected
	 * @return    string
	 */
	abstract protected function _list_tables_statement( $prefix_limit = FALSE );

	/**
	 * Determine if a particular table exists
	 *
	 * @param    string $table_name
	 *
	 * @return    bool
	 */
	public function table_exists( $table_name )
	{
		return in_array( $this->protect_identifiers( $table_name, TRUE, FALSE, FALSE ), $this->list_tables() );
	}
	// --------------------------------------------------------------------
	/**
	 * Fetch Field Names
	 *
	 * @param    string    the table name
	 *
	 * @return    array
	 */
	public function list_fields( $table )
	{
		// Is there a cached result?
		if ( isset( $this->data_cache[ 'field_names' ][ $table ] ) )
		{
			return $this->data_cache[ 'field_names' ][ $table ];
		}

		if ( FALSE === ( $sql = $this->_list_columns_statement( $table ) ) )
		{
			return FALSE;
		}

		$query = $this->query( $sql );

		$this->data_cache[ 'field_names' ][ $table ] = array();

		foreach ( $query->result() as $row )
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
					// We have no other choice but to just get the first element's key.
					$key = 'Field';
				}
				else
				{
					$key = key( $row );
				}
			}
			$this->data_cache[ 'field_names' ][ $table ][] = $row[ $key ];
		}

		return $this->data_cache[ 'field_names' ][ $table ];
	}
	// --------------------------------------------------------------------

	/**
	 * SQL Statement List Database Table Columns
	 *
	 * @param   string $table Database Table Name
	 *
	 * @access  protected
	 * @return  string
	 */
	abstract protected function _list_columns_statement( $table = '' );

	/**
	 * Determine if a particular field exists
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    bool
	 */
	public function field_exists( $field_name, $table_name )
	{
		return in_array( $field_name, $this->list_fields( $table_name ) );
	}
	// --------------------------------------------------------------------

	/**
	 * Returns an object with field data
	 *
	 * @param    string $table the table name
	 *
	 * @return    array
	 */
	public function field_data( $table )
	{
		$query = $this->query( $this->_field_data_statement( $this->protect_identifiers( $table, TRUE, NULL, FALSE ) ) );

		return ( $query ) ? $query->field_data() : FALSE;
	}
	// --------------------------------------------------------------------

	/**
	 * Field data statement
	 *
	 * Generates a platform-specific query so that the column data can be retrieved
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _field_data_statement( $table )
	{
		return 'SELECT TOP 1 * FROM ' . $this->protect_identifiers( $table );
	}

	// --------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param    mixed
	 *
	 * @return    mixed
	 */
	public function escape_identifiers( $item )
	{
		if ( $this->_escape_character === '' OR empty( $item ) OR in_array( $item, $this->_reserved_identifiers ) )
		{
			return $item;
		}
		elseif ( is_array( $item ) )
		{
			foreach ( $item as $key => $value )
			{
				$item[ $key ] = $this->escape_identifiers( $value );
			}

			return $item;
		}
		// Avoid breaking functions and literal values inside queries
		elseif ( ctype_digit( $item ) OR $item[ 0 ] === "'" OR ( $this->_escape_character !== '"' && $item[ 0 ] === '"' ) OR strpos( $item, '(' ) !== FALSE )
		{
			return $item;
		}
		static $preg_ec = array();
		if ( empty( $preg_ec ) )
		{
			if ( is_array( $this->_escape_character ) )
			{
				$preg_ec = array(
					preg_quote( $this->_escape_character[ 0 ], '/' ),
					preg_quote( $this->_escape_character[ 1 ], '/' ),
					$this->_escape_character[ 0 ],
					$this->_escape_character[ 1 ],
				);
			}
			else
			{
				$preg_ec[ 0 ] = $preg_ec[ 1 ] = preg_quote( $this->_escape_character, '/' );
				$preg_ec[ 2 ] = $preg_ec[ 3 ] = $this->_escape_character;
			}
		}
		foreach ( $this->_reserved_identifiers as $id )
		{
			if ( strpos( $item, '.' . $id ) !== FALSE )
			{
				return preg_replace( '/' . $preg_ec[ 0 ] . '?([^' . $preg_ec[ 1 ] . '\.]+)' . $preg_ec[ 1 ] . '?\./i', $preg_ec[ 2 ] . '$1' . $preg_ec[ 3 ] . '.', $item );
			}
		}

		return preg_replace( '/' . $preg_ec[ 0 ] . '?([^' . $preg_ec[ 1 ] . '\.]+)' . $preg_ec[ 1 ] . '?(\.)?/i', $preg_ec[ 2 ] . '$1' . $preg_ec[ 3 ] . '$2', $item );
	}
	// --------------------------------------------------------------------
	/**
	 * Generate an insert string
	 *
	 * @param    string    the table upon which the query will be performed
	 * @param    array     an associative array data of key/values
	 *
	 * @return    string
	 */
	public function insert_string( $table, $data )
	{
		$fields = $values = array();
		foreach ( $data as $key => $value )
		{
			$fields[] = $this->escape_identifiers( $key );
			$values[] = $this->escape( $value );
		}

		return $this->_insert_statement( $this->protect_identifiers( $table, TRUE, NULL, FALSE ), $fields, $values );
	}
	// --------------------------------------------------------------------

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @param    string    the table name
	 * @param    array     the insert keys
	 * @param    array     the insert values
	 *
	 * @return    string
	 */
	protected function _insert_statement( $table, $keys, $values )
	{
		return 'INSERT INTO ' . $table . ' (' . implode( ', ', $keys ) . ') VALUES (' . implode( ', ', $values ) . ')';
	}
	// --------------------------------------------------------------------

	/**
	 * Generate an update string
	 *
	 * @param    string    the table upon which the query will be performed
	 * @param    array     an associative array data of key/values
	 * @param    mixed     the "where" statement
	 *
	 * @return    string
	 */
	public function update_string( $table, $data, $where )
	{
		if ( empty( $where ) )
		{
			return FALSE;
		}
		$this->where( $where );
		$fields = array();
		foreach ( $data as $key => $value )
		{
			$fields[ $this->protect_identifiers( $key ) ] = $this->escape( $value );
		}
		$sql = $this->_update_statement( $this->protect_identifiers( $table, TRUE, NULL, FALSE ), $fields );
		$this->_reset_write();

		return $sql;
	}
	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param    string    the table name
	 * @param    array     the update data
	 *
	 * @return    string
	 */
	protected function _update_statement( $table, $values )
	{
		foreach ( $values as $key => $val )
		{
			$string_value[] = $key . ' = ' . $val;
		}

		return 'UPDATE ' . $table . ' SET ' . implode( ', ', $string_value )
		. $this->_compile_where_having( 'qb_where' )
		. $this->_compile_order_by()
		. ( $this->qb_limit ? ' LIMIT ' . $this->qb_limit : '' );
	}
	// --------------------------------------------------------------------
	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	protected function _has_operator( $str )
	{
		return (bool) preg_match( '/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i', trim( $str ) );
	}
	// --------------------------------------------------------------------

	/**
	 * Returns the SQL string operator
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	protected function _get_operator( $str )
	{
		static $_operators;
		if ( empty( $_operators ) )
		{
			$_les = ( $this->_like_escape_string !== '' )
				? '\s+' . preg_quote( trim( sprintf( $this->_like_escape_string, $this->_like_escape_character ) ), '/' )
				: '';
			$_operators = array(
				'\s*(?:<|>|!)?=\s*',        // =, <=, >=, !=
				'\s*<>?\s*',            // <, <>
				'\s*>\s*',            // >
				'\s+IS NULL',            // IS NULL
				'\s+IS NOT NULL',        // IS NOT NULL
				'\s+EXISTS\s*\([^\)]+\)',    // EXISTS(sql)
				'\s+NOT EXISTS\s*\([^\)]+\)',    // NOT EXISTS(sql)
				'\s+BETWEEN\s+\S+\s+AND\s+\S+',    // BETWEEN value AND value
				'\s+IN\s*\([^\)]+\)',        // IN(list)
				'\s+NOT IN\s*\([^\)]+\)',    // NOT IN (list)
				'\s+LIKE\s+\S+' . $_les,        // LIKE 'expr'[ ESCAPE '%s']
				'\s+NOT LIKE\s+\S+' . $_les    // NOT LIKE 'expr'[ ESCAPE '%s']
			);
		}

		return preg_match( '/' . implode( '|', $_operators ) . '/i', $str, $match )
			? $match[ 0 ] : FALSE;
	}
	// --------------------------------------------------------------------

	/**
	 * Enables a native PHP function to be run, using a platform agnostic wrapper.
	 *
	 * @param    string $function Function name
	 *
	 * @return    mixed
	 */
	public function call_function( $function )
	{
		$driver = ( $this->platform === 'postgre' ) ? 'pg_' : $this->platform . '_';
		if ( FALSE === strpos( $driver, $function ) )
		{
			$function = $driver . $function;
		}
		if ( ! function_exists( $function ) )
		{
			return FALSE;
		}

		return ( func_num_args() > 1 )
			? call_user_func_array( $function, array_slice( func_get_args(), 1 ) )
			: call_user_func( $function );
	}
	// --------------------------------------------------------------------

	/**
	 * Close DB Connection
	 *
	 * @return    void
	 */
	public function disconnect()
	{
		if ( $this->pdo_conn instanceof \PDO )
		{
			$this->pdo_conn = FALSE;
		}
	}
	// --------------------------------------------------------------------

	/**
	 * Protect Identifiers
	 *
	 * This function is used extensively by the Query Builder class, and by
	 * a couple functions in this class.
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it. Some logic is necessary in order to deal with
	 * column names that include the path. Consider a query like this:
	 *
	 * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
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
	 * @param    string
	 * @param    bool
	 * @param    mixed
	 * @param    bool
	 *
	 * @return    string
	 */
	public function protect_identifiers( $item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE )
	{
		if ( ! is_bool( $protect_identifiers ) )
		{
			$protect_identifiers = $this->_protect_identifiers;
		}
		if ( is_array( $item ) )
		{
			$escaped_array = array();
			foreach ( $item as $k => $v )
			{
				$escaped_array[ $this->protect_identifiers( $k ) ] = $this->protect_identifiers( $v, $prefix_single, $protect_identifiers, $field_exists );
			}

			return $escaped_array;
		}
		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix. There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		//
		// Added exception for single quotes as well, we don't want to alter
		// literal strings. -- Narf
		if ( strpos( $item, '(' ) !== FALSE OR strpos( $item, "'" ) !== FALSE )
		{
			return $item;
		}
		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace( '/\s+/', ' ', $item );
		// If the item has an alias declaration we remove it and set it aside.
		// Note: strripos() is used in order to support spaces in table names
		if ( $offset = strripos( $item, ' AS ' ) )
		{
			$alias = ( $protect_identifiers )
				? substr( $item, $offset, 4 ) . $this->escape_identifiers( substr( $item, $offset + 4 ) )
				: substr( $item, $offset );
			$item = substr( $item, 0, $offset );
		}
		elseif ( $offset = strrpos( $item, ' ' ) )
		{
			$alias = ( $protect_identifiers )
				? ' ' . $this->escape_identifiers( substr( $item, $offset + 1 ) )
				: substr( $item, $offset );
			$item = substr( $item, 0, $offset );
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
			if ( in_array( $parts[ 0 ], $this->qb_aliased_tables ) )
			{
				if ( $protect_identifiers === TRUE )
				{
					foreach ( $parts as $key => $val )
					{
						if ( ! in_array( $val, $this->_reserved_identifiers ) )
						{
							$parts[ $key ] = $this->escape_identifiers( $val );
						}
					}
					$item = implode( '.', $parts );
				}

				return $item . $alias;
			}
			// Is there a table prefix defined in the config file? If not, no need to do anything
			if ( $this->table_prefix !== '' )
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
				if ( $field_exists === FALSE )
				{
					$i++;
				}
				// Verify table prefix and replace if necessary
				if ( $this->swap_prefix !== '' && strpos( $parts[ $i ], $this->swap_prefix ) === 0 )
				{
					$parts[ $i ] = preg_replace( '/^' . $this->swap_prefix . '(\S+?)/', $this->table_prefix . '\\1', $parts[ $i ] );
				}
				// We only add the table prefix if it does not already exist
				elseif ( strpos( $parts[ $i ], $this->table_prefix ) !== 0 )
				{
					$parts[ $i ] = $this->table_prefix . $parts[ $i ];
				}
				// Put the parts back together
				$item = implode( '.', $parts );
			}
			if ( $protect_identifiers === TRUE )
			{
				$item = $this->escape_identifiers( $item );
			}

			return $item . $alias;
		}
		// Is there a table prefix? If not, no need to insert it
		if ( $this->table_prefix !== '' )
		{
			// Verify table prefix and replace if necessary
			if ( $this->swap_prefix !== '' && strpos( $item, $this->swap_prefix ) === 0 )
			{
				$item = preg_replace( '/^' . $this->swap_prefix . '(\S+?)/', $this->table_prefix . '\\1', $item );
			}
			// Do we prefix an item with no segments?
			elseif ( $prefix_single === TRUE && strpos( $item, $this->table_prefix ) !== 0 )
			{
				$item = $this->table_prefix . $item;
			}
		}
		if ( $protect_identifiers === TRUE && ! in_array( $item, $this->_reserved_identifiers ) )
		{
			$item = $this->escape_identifiers( $item );
		}

		return $item . $alias;
	}
	// --------------------------------------------------------------------

	/**
	 * Affected Rows
	 *
	 * @return    int
	 */
	public function affected_rows()
	{
		return is_object( $this->pdo_statement ) ? $this->pdo_statement->rowCount() : 0;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @param    string $name
	 *
	 * @return    int
	 */
	public function last_insert_id( $name = NULL )
	{
		return $this->pdo_conn->lastInsertId( $name );
	}

	// --------------------------------------------------------------------


	/**
	 * Error
	 *
	 * Returns an array containing code and message of the last
	 * database error that has occured.
	 *
	 * @return    array
	 */
	public function error()
	{
		$error = array( 'code' => '00000', 'message' => '' );
		$pdo_error = $this->pdo_conn->errorInfo();

		if ( empty( $pdo_error[ 0 ] ) )
		{
			return $error;
		}

		$error[ 'code' ] = isset( $pdo_error[ 1 ] ) ? $pdo_error[ 0 ] . '/' . $pdo_error[ 1 ] : $pdo_error[ 0 ];
		if ( isset( $pdo_error[ 2 ] ) )
		{
			$error[ 'message' ] = $pdo_error[ 2 ];
		}

		return $error;
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
		foreach ( $final as $field_name => $field_value )
		{
			$cases .= $field_name . ' = CASE ' . "\n";

			foreach ( $field_value as $row )
			{
				$cases .= $row . "\n";
			}

			$cases .= 'ELSE ' . $field_name . ' END, ';
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
		return 'TRUNCATE TABLE ' . $table;
	}

}