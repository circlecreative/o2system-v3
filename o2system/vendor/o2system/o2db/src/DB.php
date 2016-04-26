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

namespace O2System
{

	use O2System\DB\UndefinedDriverException;
	use O2System\DB\UnsupportedDriverException;
	use O2System\Glob\Interfaces\MagicInterface;

	class DB
	{
		use MagicInterface;

		protected static $_instance;
		protected        $_driver;

		/**
		 * Class Constructor
		 *
		 * @param array $config
		 *
		 * @access  public
		 * @throws  DB\Exception
		 */
		public function __construct( $config )
		{
			// Register Exception View and Language Path
			if ( class_exists( 'O2System', FALSE ) )
			{
				\O2System::Exceptions()->addPath( __DIR__ );
				\O2System::$language->addPath( __DIR__ )->load( get_class_name( $this ) );
			}
			else
			{
				\O2System\Glob::Exceptions()->addPath( __DIR__ );
				\O2System\Glob::$language->addPath( __DIR__ )->load( get_class_name( $this ) );
			}

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
					throw new DB\Exception( 'DB_INVALIDCONNECTIONSTR' );
				}

				$config = array(
					'driver'   => $dsn[ 'scheme' ],
					'hostname' => isset( $dsn[ 'host' ] ) ? rawurldecode( $dsn[ 'host' ] ) : '',
					'port'     => isset( $dsn[ 'port' ] ) ? rawurldecode( $dsn[ 'port' ] ) : '',
					'username' => isset( $dsn[ 'user' ] ) ? rawurldecode( $dsn[ 'user' ] ) : '',
					'password' => isset( $dsn[ 'pass' ] ) ? rawurldecode( $dsn[ 'pass' ] ) : '',
					'database' => isset( $dsn[ 'path' ] ) ? rawurldecode( substr( $dsn[ 'path' ], 1 ) ) : '',
				);

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
						if ( is_string( $value ) AND in_array( strtoupper( $value ), array( 'TRUE', 'FALSE', 'NULL' ) ) )
						{
							$value = var_export( $value, TRUE );
						}

						$config[ $key ] = $value;
					}
				}
			}

			if ( empty( $config[ 'driver' ] ) )
			{
				throw new UndefinedDriverException( 'DB_UNDEFINEDDRIVER' );
			}

			if ( in_array( $config[ 'driver' ], array( 'mssql', 'sybase' ) ) )
			{
				$config[ 'driver' ] = 'dblib';
			}

			$_valid_drivers = array(
				'cubrid',
				'mysql',
				'mssql',
				'firebird',
				'ibm',
				'informix',
				'oci',
				'odbc',
				'pgsql',
				'sqlite',
			);

			if ( ! in_array( $config[ 'driver' ], $_valid_drivers ) )
			{
				throw new UnsupportedDriverException( 'DB_UNSUPPORTEDDRIVER' );
			}

			if ( is_dir( $driver_path = __DIR__ . '/Drivers/' . ucfirst( $config[ 'driver' ] . '/' ) ) )
			{
				// Create DB Connection
				$class_name = '\O2System\DB\Drivers\\' . ucfirst( $config[ 'driver' ] ) . '\\Driver';

				// Create Instance
				$this->_driver = new $class_name( $config );
				$this->_driver->connect();

				if ( $this->_driver->is_connected() )
				{
					if ( ! isset( static::$_instance ) )
					{
						static::$_instance = $this->_driver;
					}
				}
			}
		}

		// ------------------------------------------------------------------------

		public function load( $tool )
		{
			$driver = ucfirst( strtolower( $this->_driver->platform ) );

			switch ( $tool )
			{
				case 'forge':

					$forge_class_name = '\O2System\DB\Drivers\\' . $driver . '\\Forge';
					$this->forge = new $forge_class_name( $this->_driver );

					return $this->forge;
					break;
				case 'utility':

					$utility_class_name = '\O2System\DB\Drivers\\' . $driver . '\\Utility';
					$this->utility = new $utility_class_name( $this->_driver );

					return $this->utility;
					break;
			}

			return FALSE;
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

		public function __call( $method, $args = array() )
		{
			if ( method_exists( $this, $method ) )
			{
				return call_user_func_array( array( $this, $method ), $args );
			}

			return $this->_driver->__call( $method, $args );
		}

		public static function __callStatic( $method, $args = array() )
		{
			if ( isset( static::$_instance ) )
			{
				return static::$_instance->__call( $method, $args );
			}
		}
	}
}

namespace O2System\DB
{

	use O2System\Glob\Interfaces\ExceptionInterface;

	class Exception extends ExceptionInterface
	{
		public $library = array(
			'name'        => 'O2System DB (O2DB)',
			'description' => 'Open Source PHP Data Object (PDO) Wrapper',
			'version'     => '1.0',
		);

		public $view_exception = 'db_exception.php';

		public function __construct( $message = NULL, $code = 0, \PDOException $previous = NULL )
		{
			if ( ! is_null( $previous ) )
			{
				$this->previous = $previous;

				if ( strstr( $this->previous->getMessage(), 'SQLSTATE[' ) )
				{
					preg_match( '/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $this->previous->getMessage(), $matches );

					if ( ! empty( $matches ) )
					{
						$code = ( $matches[ 1 ] == 'HT000' || 'HY000' ? $matches[ 2 ] : $matches[ 1 ] );
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
		public function __construct( $message, $code = 0, $args = array() )
		{
			$this->_args = $args;
			parent::__construct( $message, $code );
		}
	}

	class BadMethodCallException extends Exception
	{
		public function __construct( $message, $code, $args = array() )
		{
			$this->_args = $args;
			parent::__construct( $message, $code );
		}
	}

	class QueryException extends Exception
	{
		protected $_statement;

		public function __construct( \PDOException $pdoException, $statement )
		{
			parent::__construct( NULL, 0, $pdoException );

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
}