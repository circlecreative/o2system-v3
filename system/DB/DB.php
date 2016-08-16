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

namespace O2System
{

	use O2System\DB\Collections\Config;
	use O2System\DB\Collections\Connections;
	use O2System\DB\ConnectionException;
	use O2System\DB\Interfaces\ConnectionInterface;
	use O2System\DB\UndefinedDriverException;
	use O2System\DB\UnsupportedDriverException;

	class DB
	{
		/**
		 * Database Config
		 *
		 * @type    Config
		 */
		protected $config;

		/**
		 * Database Connections
		 *
		 * @type    Connections
		 */
		protected $connections;

		public function __construct( array $config = [ ] )
		{
			$this->config      = new Config();
			$this->connections = new Connections();

			if ( empty( $config ) )
			{
				$config = \O2System::$config->load( 'database', TRUE );
			}

			if ( isset( $config[ 'username' ] ) AND isset( $config[ 'database' ] ) )
			{
				$this->connect( 'default', new DB\Config( $config ) );
			}
			elseif ( isset( $config[ 'default' ] ) )
			{
				foreach ( $config as $connection => $setup )
				{
					if ( ! empty( $setup[ 'username' ] ) AND ! empty( $setup[ 'database' ] ) )
					{
						$this->connect( $connection, new DB\Config( $setup ) );
					}
				}
			}

			\O2System::$log->debug( 'LOG_DEBUG_DB_CLASS_INITIALIZED' );
		}

		/**
		 * @param      $connection
		 * @param bool $new_instance
		 *
		 * @return bool|mixed
		 * @throws \O2System\DB\ConnectionException
		 * @throws \O2System\DB\UndefinedDriverException
		 * @throws \O2System\DB\UnsupportedDriverException
		 * @throws \O2System\Exception
		 */
		public function connect( $connection, $config = [ ], $new_instance = FALSE )
		{
			if ( is_string( $config ) AND strpos( $config, '://' ) !== FALSE )
			{
				/**
				 * Parse the URL from the DSN string
				 * Database settings can be passed as discreet
				 * parameters or as a data source name in the first
				 * parameter. DSNs must have this prototype:
				 * $dsn = 'driver://username:password@hostname/database';
				 */
				if ( ( $dsn = @parse_url( $config ) ) === FALSE )
				{
					throw new Exception( 'DB_INVALIDCONNECTIONSTR' );
				}

				$config = [
					'driver'   => $dsn[ 'scheme' ],
					'hostname' => isset( $dsn[ 'host' ] ) ? rawurldecode( $dsn[ 'host' ] ) : '',
					'port'     => isset( $dsn[ 'port' ] ) ? rawurldecode( $dsn[ 'port' ] ) : '',
					'username' => isset( $dsn[ 'user' ] ) ? rawurldecode( $dsn[ 'user' ] ) : '',
					'password' => isset( $dsn[ 'pass' ] ) ? rawurldecode( $dsn[ 'pass' ] ) : '',
					'database' => isset( $dsn[ 'path' ] ) ? rawurldecode( substr( $dsn[ 'path' ], 1 ) ) : '',
				];

				// Validate Connection
				$config[ 'username' ] = $config[ 'username' ] === 'username' ? NULL : $config[ 'username' ];
				$config[ 'password' ] = $config[ 'password' ] === 'password' ? NULL : $config[ 'password' ];
				$config[ 'hostname' ] = $config[ 'hostname' ] === 'hostname' ? NULL : $config[ 'hostname' ];

				// Were additional config items set?
				if ( isset( $dsn[ 'query' ] ) )
				{
					parse_str( $dsn[ 'query' ], $extra );

					foreach ( $extra as $key => $value )
					{
						if ( is_string( $value ) AND in_array( strtoupper( $value ), [ 'TRUE', 'FALSE', 'NULL' ] ) )
						{
							$value = var_export( $value, TRUE );
						}

						$config[ $key ] = $value;
					}
				}

				$this->config[ $connection ] = new DB\Config( $config );
			}
			elseif ( is_array( $config ) )
			{
				$this->config[ $connection ] = new DB\Config( $config );
			}
			elseif ( $config instanceof DB\Config )
			{
				$this->config[ $connection ] = $config;
			}

			if ( $this->config->offsetExists( $connection ) )
			{
				$config = $this->config->offsetGet( $connection );

				if ( empty( $config[ 'driver' ] ) )
				{
					throw new UndefinedDriverException( 'DB_UNDEFINEDDRIVER' );
				}

				if ( $this->connections->offsetExists( $connection ) AND $new_instance === FALSE )
				{
					return $this->connections[ $connection ];
				}
				elseif ( $this->connections->offsetExists( $connection ) AND $new_instance === TRUE )
				{
					return $this->_initializeConnection( $this->config[ $connection ] );
				}
				else
				{
					return $this->connections[ $connection ] = $this->_initializeConnection( $this->config[ $connection ] );
				}
			}

			return FALSE;
		}

		protected function _initializeConnection( DB\Config $config )
		{
			$_valid_drivers = [
				'mysql' => 'MySQL',
			];

			if ( isset( $_valid_drivers[ $config[ 'driver' ] ] ) )
			{
				$class_name = '\O2System\DB\Drivers\\' . $_valid_drivers[ $config[ 'driver' ] ] . '\\Connection';

				if ( class_exists( $class_name ) )
				{
					// Create DB Connection
					$connection_class = new $class_name();
					$connection_class->setConfig( $config );
					$connection_class->initialize();

					return $connection_class;
				}
			}

			throw new UnsupportedDriverException( 'Currently not support database driver:' . $config[ 'driver' ] );
		}

		/**
		 * Server supported drivers
		 *
		 * @access public
		 * @return array
		 */
		public static function getSupportedDrivers()
		{
			return \PDO::getAvailableDrivers();
		}

		public function load( $tool, $connection = 'default' )
		{
			if ( $this->connections->offsetExists( $connection ) )
			{
				if ( $namespace = $this->connections->getConnectionNamespace( $connection ) )
				{
					$class     = $namespace . prepare_class_name( $tool );
					$directory = $this->connections->getConnectionDriverDirectory( $connection );

					require_once( $directory . prepare_filename( $tool, '.php' ) );

					if ( class_exists( $class, FALSE ) )
					{
						$tool = new $class( $this->connections[ $connection ] );

						return $tool;
					}
				}
			}
		}

		/**
		 * Magic method __get
		 *
		 * @param $connection
		 *
		 * @return ConnectionInterface|mixed
		 * @throws \O2System\DB\ConnectionException
		 */
		public function __get( $connection )
		{
			if ( $this->connections->offsetExists( $connection ) )
			{
				return $this->connections->offsetGet( $connection );
			}

			throw new ConnectionException( 'DB_UNDEFINEDCONNECTION' );
		}

		// ------------------------------------------------------------------------

		/**
		 * Magic method __call
		 *
		 * @param       $method
		 * @param array $args
		 *
		 * @return mixed|ConnectionInterface
		 * @throws \O2System\DB\ConnectionException
		 */
		public function __call( $method, array $args = [ ] )
		{
			if ( method_exists( $this, $method ) )
			{
				return call_user_func_array( [ $this, $method ], $args );
			}
			elseif ( $this->connections->offsetExists( 'default' ) )
			{
				return call_user_func_array( [ $this->connections->default, $method ], $args );
			}

			throw new ConnectionException( 'Undefined default database connection' );
		}
	}
}

namespace O2System\DB
{

	use O2System\Core\SPL\ArrayObject;

	class Config extends ArrayObject
	{
		public function __construct( array $config )
		{
			parent::__construct(
				array_merge(
					[
						'driver'        => 'mysql',
						'dsn'           => '',
						'hostname'      => '127.0.0.1',
						'port'          => 3306,
						'username'      => '',
						'password'      => '',
						'database'      => '',
						'charset'       => 'utf8',
						'collate'       => 'utf8_general_ci',
						'table_prefix'  => '',
						'strict_on'     => FALSE,
						'encrypt'       => FALSE,
						'compress'      => FALSE,
						'buffered'      => FALSE,
						'persistent'    => TRUE,
						'trans_enabled' => FALSE,
						'debug_enabled' => FALSE,
						'options'       => [
							\PDO::ATTR_CASE         => \PDO::CASE_NATURAL,
							\PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
						],
					], $config ) );
		}
	}

	class Exception extends \O2System\Core\Exception
	{
		public $library = [
			'name'        => 'O2System DB (O2DB)',
			'description' => 'Open Source PHP Data Object (PDO) Wrapper',
			'version'     => '1.0',
		];

		public $view_exception = 'db_exception.php';

		public function __construct( $message = NULL, $code = 0, \PDOException $previous = NULL )
		{
			if ( ! is_null( $previous ) )
			{
				$this->previous = $previous;

				if ( strstr( @$this->previous->getMessage(), 'SQLSTATE[' ) )
				{
					preg_match( '/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', @$this->previous->getMessage(), $matches );

					if ( ! empty( $matches ) )
					{
						$code    = ( $matches[ 1 ] == 'HT000' || 'HY000' ? $matches[ 2 ] : $matches[ 1 ] );
						$message = $matches[ 3 ];
					}
					else
					{
						$message = $this->previous->getMessage();
					}
				}
			}

			parent::__construct( $message, $code );
		}
	}

	class ConnectionException extends Exception
	{

	}

	class UndefinedDriverException extends Exception
	{
	}

	class UnsupportedDriverException extends Exception
	{
		public function __construct( $message, $code = 0, $args = [ ] )
		{
			$this->args = $args;
			parent::__construct( $message, $code );
		}
	}

	class BadMethodCallException extends Exception
	{
		public function __construct( $message, $code, $args = [ ] )
		{
			$this->args = $args;
			parent::__construct( $message, $code );
		}
	}

	class QueryException extends Exception
	{
		protected $_statement;

		public function __construct( $message, $statement = NULL )
		{
			parent::__construct( $message );

			$this->setStatement( $statement );
		}

		public function setStatement( $statement )
		{
			$this->_statement = $statement;
		}

		public function getStatement()
		{
			return $this->_statement;
		}
	}

	class QueryBuilderException extends Exception
	{
	}
}