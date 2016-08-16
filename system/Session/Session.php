<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 28-Jul-16
 * Time: 2:15 AM
 */

namespace O2System
{

	use ArrayAccess;
	use Countable;
	use IteratorAggregate;
	use O2System\Core\SPL\ArrayIterator;
	use O2System\Session\Config;
	use O2System\Session\Interfaces\HandlerInterface;
	use Traversable;

	class Session implements IteratorAggregate, ArrayAccess, Countable
	{
		/**
		 * Valid Platform Handlers
		 *
		 * @var array
		 */
		protected static $validHandlers = [
			'apc'       => 'Apc',
			'files'     => 'File',
			'file'      => 'File',
			'database'  => 'Database',
			'memcached' => 'Memcached',
			'redis'     => 'Redis',
			'wincache'  => 'Wincache',
			'xcache'    => 'Xcache',
		];

		protected $config;

		/**
		 * Instance of the handler to use.
		 *
		 * @var HandlerInterface
		 */
		protected $handler;

		/**
		 * Userdata array.
		 *
		 * Just a reference to $_SESSION, for BC purposes.
		 */
		protected $storage;

		/**
		 * Session constructor.
		 *
		 * @param array $config
		 */
		public function __construct( array $config = [ ] )
		{
			if ( empty( $config ) )
			{
				$config = \O2System::$config->load( 'session', TRUE )->getArrayCopy();
			}

			if ( isset( $config[ 'handler' ] ) )
			{
				if ( isset( static::$validHandlers[ $config[ 'handler' ] ] ) )
				{
					if ( isset( static::$validHandlers[ $config[ 'handler' ] ] ) )
					{
						$class_name = '\O2System\Session\Handlers\\' . static::$validHandlers[ $config[ 'handler' ] ];

						if ( class_exists( $class_name ) )
						{
							// Initialize Cache Handler
							$this->handler = new $class_name( $this->config = new Config( $config ) );
						}
					}
				}
			}

			\O2System::$log->debug( 'LOG_DEBUG_SESSION_CLASS_INITIALIZED' );
		}

		/**
		 * Initialize the session container and starts up the session.
		 */
		public function start()
		{
			if ( is_cli() )
			{
				\O2System::$log->debug( 'Session: Initialization under CLI aborted.' );

				return;
			}
			elseif ( (bool) ini_get( 'session.auto_start' ) )
			{
				\O2System::$log->error( 'Session: session.auto_start is enabled in php.ini. Aborting.' );

				return;
			}

			if ( ! $this->handler instanceof \SessionHandlerInterface )
			{
				\O2System::$log->error(
					"Session: Handler '" . $this->handler->getPlatform() .
					"' doesn't implement SessionHandlerInterface. Aborting." );
			}

			$this->_configure();

			session_set_save_handler( $this->handler, TRUE );

			// Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
			if ( isset( $_COOKIE[ $this->config[ 'name' ] ] ) && (
					! is_string( $_COOKIE[ $this->config[ 'name' ] ] ) || ! preg_match( '/^[0-9a-f]{40}$/', $_COOKIE[ $this->config[ 'name' ] ] )
				)
			)
			{
				unset( $_COOKIE[ $this->config[ 'name' ] ] );
			}

			if ( empty( $this->config[ 'cookie' ]->domain ) )
			{
				$this->config[ 'cookie' ]->domain = ( isset( $_SERVER[ 'HTTP_HOST' ] ) ? '.' . $_SERVER[ 'HTTP_HOST' ] : ( isset( $_SERVER[ 'SERVER_NAME' ] ) ? '.' . $_SERVER[ 'SERVER_NAME' ] : NULL ) );
			}

			@session_start();

			// Is session ID auto-regeneration configured? (ignoring ajax requests)
			if ( ( empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) ||
					strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) !== 'xmlhttprequest' ) && ( $regenerate_time = $this->config[ 'regenerate' ]->lifetime ) > 0
			)
			{
				if ( ! isset( $_SESSION[ '__o2sessionLastRegenerate' ] ) )
				{
					$_SESSION[ '__o2sessionLastRegenerate' ] = time();
				}
				elseif ( $_SESSION[ '__o2sessionLastRegenerate' ] < ( time() - $regenerate_time ) )
				{
					$this->regenerate( (bool) $this->config[ 'regenerate' ]->destroy );
				}
			}
			// Another work-around ... PHP doesn't seem to send the session cookie
			// unless it is being currently created or regenerated
			elseif ( isset( $_COOKIE[ $this->config[ 'name' ] ] ) && $_COOKIE[ $this->config[ 'name' ] ] === session_id() )
			{
				setcookie(
					$this->config[ 'name' ],
					session_id(),
					( empty( $this->config[ 'lifetime' ] ) ? 0 : time() + $this->config[ 'lifetime' ] ),
					$this->config[ 'cookie' ]->path,
					$this->config[ 'cookie' ]->domain,
					$this->config[ 'cookie' ]->secure,
					TRUE
				);
			}

			$this->_initVars();

			\O2System::$log->info( "Session: Class initialized using '" . $this->handler->getPlatform() . "' driver." );
		}

		//--------------------------------------------------------------------

		/**
		 * Does a full stop of the session:
		 *
		 * - destroys the session
		 * - unsets the session id
		 * - destroys the session cookie
		 */
		public function stop()
		{
			setcookie(
				$this->config[ 'name' ],
				session_id(),
				1,
				$this->config[ 'cookie' ]->path,
				$this->config[ 'cookie' ]->domain,
				$this->config[ 'cookie' ]->secure,
				TRUE
			);

			session_regenerate_id( TRUE );
		}

		//--------------------------------------------------------------------


		/**
		 * Configuration.
		 *
		 * Handle input binds and configuration defaults.
		 */
		protected function _configure()
		{
			if ( empty( $this->config[ 'name' ] ) )
			{
				$this->config[ 'name' ] = ini_get( 'session.name' );
			}
			else
			{
				ini_set( 'session.name', $this->config[ 'name' ] );
			}

			session_set_cookie_params(
				$this->config[ 'lifetime' ],
				$this->config[ 'cookie' ]->path,
				$this->config[ 'cookie' ]->domain,
				$this->config[ 'cookie' ]->secure,
				TRUE // HTTP only; Yes, this is intentional and not configurable for security reasons.
			);

			if ( empty( $this->config[ 'lifetime' ] ) )
			{
				$this->config[ 'lifetime' ] = (int) ini_get( 'session.gc_maxlifetime' );
			}
			else
			{
				ini_set( 'session.gc_maxlifetime', (int) $this->config[ 'lifetime' ] );
			}

			// Security is king
			ini_set( 'session.use_trans_sid', 0 );
			ini_set( 'session.use_strict_mode', 1 );
			ini_set( 'session.use_cookies', 1 );
			ini_set( 'session.use_only_cookies', 1 );
			ini_set( 'session.hash_function', 1 );
			ini_set( 'session.hash_bits_per_character', 4 );
		}

		//--------------------------------------------------------------------

		/**
		 * Handle temporary variables
		 *
		 * Clears old "flash" data, marks the new one for deletion and handles
		 * "temp" data deletion.
		 */
		protected function _initVars()
		{
			if ( ! empty( $_SESSION[ '__o2session_vars' ] ) )
			{
				$current_time = time();

				foreach ( $_SESSION[ '__o2session_vars' ] as $key => &$value )
				{
					if ( $value === 'new' )
					{
						$_SESSION[ '__o2session_vars' ][ $key ] = 'old';
					}
					// Hacky, but 'old' will (implicitly) always be less than time() ;)
					// DO NOT move this above the 'new' check!
					elseif ( $value < $current_time )
					{
						unset( $_SESSION[ $key ], $_SESSION[ '__o2session_vars' ][ $key ] );
					}
				}

				if ( empty( $_SESSION[ '__o2session_vars' ] ) )
				{
					unset( $_SESSION[ '__o2session_vars' ] );
				}
			}

			$this->storage = &$_SESSION;
		}

		//--------------------------------------------------------------------
		//--------------------------------------------------------------------
		// Session Utility Methods
		//--------------------------------------------------------------------

		/**
		 * Regenerates the session ID.
		 *
		 * @param bool $destroy Should old session data be destroyed?
		 */
		public function regenerate( $destroy = FALSE )
		{
			$_SESSION[ '__o2sessionLastRegenerate' ] = time();
			session_regenerate_id( $destroy );
		}

		//--------------------------------------------------------------------

		/**
		 * Destroys the current session.
		 */
		public function destroy()
		{
			session_destroy();
		}

		//--------------------------------------------------------------------
		//--------------------------------------------------------------------
		// Basic Setters and Getters
		//--------------------------------------------------------------------

		/**
		 * Sets user data into the session.
		 *
		 * If $data is a string, then it is interpreted as a session property
		 * key, and  $value is expected to be non-null.
		 *
		 * If $data is an array, it is expected to be an array of key/value pairs
		 * to be set as session properties.
		 *
		 * @param string $data  Property name or assoo2systemative array of properties
		 * @param null   $value Property value if single key provided
		 */
		public function set( $data, $value = NULL )
		{
			if ( is_array( $data ) )
			{
				foreach ( $data as $key => &$value )
				{
					$_SESSION[ $key ] = $value;
				}

				return;
			}

			$_SESSION[ $data ] = $value;
		}

		//--------------------------------------------------------------------

		/**
		 * Get user data that has been set in the session.
		 *
		 * If the property exists as "normal", returns it.
		 * Otherwise, returns an array of any temp or flash data values with the
		 * property key.
		 *
		 * Replaces the legacy method $session->userdata();
		 *
		 * @param  $key    Identifier of the session property to retrieve
		 *
		 * @return array|null    The property value(s)
		 */
		public function get( $key = NULL )
		{
			$userdata = [ ];

			if ( isset( $key ) )
			{
				return isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : NULL;
			}
			elseif ( empty( $_SESSION ) )
			{
				return $userdata;
			}

			$_exclude = array_merge(
				[ '__o2session_vars' ], $this->getFlashKeys(), $this->getTempKeys()
			);

			foreach ( array_keys( $_SESSION ) as $key )
			{
				if ( ! in_array( $key, $_exclude, TRUE ) )
				{
					$userdata[ $key ] = $_SESSION[ $key ];
				}
			}

			return $userdata;
		}

		//--------------------------------------------------------------------

		/**
		 * Returns whether an index exists in the session array.
		 *
		 * @param string $key Identifier of the session property we are interested in.
		 *
		 * @return bool
		 */
		public function has( $key )
		{
			return (bool) isset( $_SESSION[ $key ] );
		}

		//--------------------------------------------------------------------

		/**
		 * Remove one or more session properties.
		 *
		 * If $key is an array, it is interpreted as an array of string property
		 * identifiers to remove. Otherwise, it is interpreted as the identifier
		 * of a speo2systemfic session property to remove.
		 *
		 * @param  $key Identifier of the session property or properties to remove.
		 */
		public function remove( $key )
		{
			if ( is_array( $key ) )
			{
				foreach ( $key as $k )
				{
					unset( $_SESSION[ $k ] );
				}

				return;
			}

			unset( $_SESSION[ $key ] );
		}

		//--------------------------------------------------------------------

		/**
		 * Magic method to set variables in the session by simply calling
		 *  $session->foo = bar;
		 *
		 * @param  $key Identifier of the session property to set.
		 * @param  $value
		 */
		public function __set( $key, $value )
		{
			$_SESSION[ $key ] = $value;
		}

		//--------------------------------------------------------------------

		/**
		 * Magic method to get session variables by simply calling
		 *  $foo = $session->foo;
		 *
		 * @param  $key Identifier of the session property to remove.
		 *
		 * @return null|string
		 */
		public function __get( $key )
		{
			// Note: Keep this order the same, just in case somebody wants to
			//       use 'session_id' as a session data key, for whatever reason
			if ( isset( $_SESSION[ $key ] ) )
			{
				return $_SESSION[ $key ];
			}
			elseif ( $key === 'session_id' )
			{
				return session_id();
			}

			return NULL;
		}

		//--------------------------------------------------------------------

		/**
		 * Magic method to check if session variable is has been set or not by simply calling
		 *  isset($session->foo)
		 *
		 * @param $key
		 *
		 * @return bool
		 */
		public function __isset( $key )
		{
			return (bool) isset( $_SESSION[ $key ] );
		}

		/**
		 * Magic method to unset session variable by simply calling
		 *  unset($session->foo)
		 *
		 * @param $key
		 *
		 * @return void
		 */
		public function __unset( $key )
		{
			unset( $_SESSION[ $key ] );
		}

		/**
		 * Get Data
		 *
		 * Get all session data
		 *
		 * @return mixed
		 */
		public function &getData()
		{
			return $_SESSION;
		}

		//--------------------------------------------------------------------
		// Flash Data Methods
		//--------------------------------------------------------------------

		/**
		 * Sets data into the session that will only last for a single request.
		 * Perfect for use with single-use status update messages.
		 *
		 * If $data is an array, it is interpreted as an assoo2systemative array of
		 * key/value pairs for flashdata properties.
		 * Otherwise, it is interpreted as the identifier of a speo2systemfic
		 * flashdata property, with $value containing the property value.
		 *
		 * @param      $data    Property identifier or assoo2systemative array of properties
		 * @param null $value   Property value if $data is a scalar
		 */
		public function setFlashData( $data, $value = NULL )
		{
			$this->set( $data, $value );
			$this->markAsFlashData( is_array( $data ) ? array_keys( $data ) : $data );
		}

		//--------------------------------------------------------------------

		/**
		 * Retrieve one or more items of flash data from the session.
		 *
		 * If the item key is null, return all flashdata.
		 *
		 * @param string $key Property identifier
		 *
		 * @return array|null    The requested property value, or an assoo2systemative array  of them
		 */
		public function getFlashData( $key = NULL )
		{
			if ( isset( $key ) )
			{
				return ( isset( $_SESSION[ '__o2session_vars' ], $_SESSION[ '__o2session_vars' ][ $key ], $_SESSION[ $key ] ) &&
					! is_int( $_SESSION[ '__o2session_vars' ][ $key ] ) ) ? $_SESSION[ $key ] : NULL;
			}

			$flashdata = [ ];

			if ( ! empty( $_SESSION[ '__o2session_vars' ] ) )
			{
				foreach ( $_SESSION[ '__o2session_vars' ] as $key => &$value )
				{
					is_int( $value ) OR $flashdata[ $key ] = $_SESSION[ $key ];
				}
			}

			return $flashdata;
		}

		//--------------------------------------------------------------------

		/**
		 * Keeps a single piece of flash data alive for one more request.
		 *
		 * @param string $key Property identifier or array of them
		 */
		public function keepFlashData( $key )
		{
			$this->markAsFlashData( $key );
		}

		//--------------------------------------------------------------------

		/**
		 * Mark a session property or properties as flashdata.
		 *
		 * @param $key    Property identifier or array of them
		 *
		 * @return False if any of the properties are not already set
		 */
		public function markAsFlashData( $key )
		{
			if ( is_array( $key ) )
			{
				for ( $i = 0, $c = count( $key ); $i < $c; $i++ )
				{
					if ( ! isset( $_SESSION[ $key[ $i ] ] ) )
					{
						return FALSE;
					}
				}

				$new = array_fill_keys( $key, 'new' );

				$_SESSION[ '__o2session_vars' ] = isset( $_SESSION[ '__o2session_vars' ] ) ? array_merge( $_SESSION[ '__o2session_vars' ], $new ) : $new;

				return TRUE;
			}

			if ( ! isset( $_SESSION[ $key ] ) )
			{
				return FALSE;
			}

			$_SESSION[ '__o2session_vars' ][ $key ] = 'new';

			return TRUE;
		}

		//--------------------------------------------------------------------

		/**
		 * Unmark data in the session as flashdata.
		 *
		 * @param mixed $key Property identifier or array of them
		 */
		public function unmarkFlashData( $key )
		{
			if ( empty( $_SESSION[ '__o2session_vars' ] ) )
			{
				return;
			}

			is_array( $key ) OR $key = [ $key ];

			foreach ( $key as $k )
			{
				if ( isset( $_SESSION[ '__o2session_vars' ][ $k ] ) && ! is_int( $_SESSION[ '__o2session_vars' ][ $k ] ) )
				{
					unset( $_SESSION[ '__o2session_vars' ][ $k ] );
				}
			}

			if ( empty( $_SESSION[ '__o2session_vars' ] ) )
			{
				unset( $_SESSION[ '__o2session_vars' ] );
			}
		}

		//--------------------------------------------------------------------

		/**
		 * Retrieve all of the keys for session data marked as flashdata.
		 *
		 * @return array    The property names of all flashdata
		 */
		public function getFlashKeys()
		{
			if ( ! isset( $_SESSION[ '__o2session_vars' ] ) )
			{
				return [ ];
			}

			$keys = [ ];
			foreach ( array_keys( $_SESSION[ '__o2session_vars' ] ) as $key )
			{
				is_int( $_SESSION[ '__o2session_vars' ][ $key ] ) OR $keys[] = $key;
			}

			return $keys;
		}

		//--------------------------------------------------------------------
		//--------------------------------------------------------------------
		// Temp Data Methods
		//--------------------------------------------------------------------

		/**
		 * Sets new data into the session, and marks it as temporary data
		 * with a set lifespan.
		 *
		 * @param      $data    Session data key or assoo2systemative array of items
		 * @param null $value   Value to store
		 * @param int  $ttl     Time-to-live in seconds
		 */
		public function setTempData( $data, $value = NULL, $ttl = 300 )
		{
			$this->set( $data, $value );
			$this->markAsTempData( is_array( $data ) ? array_keys( $data ) : $data, $ttl );
		}

		//--------------------------------------------------------------------

		/**
		 * Returns either a single piece of tempdata, or all temp data currently
		 * in the session.
		 *
		 * @param  $key   Session data key
		 *
		 * @return mixed        Session data value or null if not found.
		 */
		public function getTempData( $key = NULL )
		{
			if ( isset( $key ) )
			{
				return ( isset( $_SESSION[ '__o2session_vars' ], $_SESSION[ '__o2session_vars' ][ $key ], $_SESSION[ $key ] ) &&
					is_int( $_SESSION[ '__o2session_vars' ][ $key ] ) ) ? $_SESSION[ $key ] : NULL;
			}

			$tempdata = [ ];

			if ( ! empty( $_SESSION[ '__o2session_vars' ] ) )
			{
				foreach ( $_SESSION[ '__o2session_vars' ] as $key => &$value )
				{
					is_int( $value ) && $tempdata[ $key ] = $_SESSION[ $key ];
				}
			}

			return $tempdata;
		}

		//--------------------------------------------------------------------

		/**
		 * Removes a single piece of temporary data from the session.
		 *
		 * @param string $key Session data key
		 */
		public function removeTempData( $key )
		{
			$this->unmarkTempData( $key );
			unset( $_SESSION[ $key ] );
		}

		//--------------------------------------------------------------------

		/**
		 * Mark one of more pieces of data as being temporary, meaning that
		 * it has a set lifespan within the session.
		 *
		 * @param     $key    Property identifier or array of them
		 * @param int $ttl    Time to live, in seconds
		 *
		 * @return bool    False if any of the properties were not set
		 */
		public function markAsTempData( $key, $ttl = 300 )
		{
			$ttl += time();

			if ( is_array( $key ) )
			{
				$temp = [ ];

				foreach ( $key as $k => $v )
				{
					// Do we have a key => ttl pair, or just a key?
					if ( is_int( $k ) )
					{
						$k = $v;
						$v = $ttl;
					}
					else
					{
						$v += time();
					}

					if ( ! isset( $_SESSION[ $k ] ) )
					{
						return FALSE;
					}

					$temp[ $k ] = $v;
				}

				$_SESSION[ '__o2session_vars' ] = isset( $_SESSION[ '__o2session_vars' ] ) ? array_merge( $_SESSION[ '__o2session_vars' ], $temp ) : $temp;

				return TRUE;
			}

			if ( ! isset( $_SESSION[ $key ] ) )
			{
				return FALSE;
			}

			$_SESSION[ '__o2session_vars' ][ $key ] = $ttl;

			return TRUE;
		}

		//--------------------------------------------------------------------

		/**
		 * Unmarks temporary data in the session, effectively removing its
		 * lifespan and allowing it to live as long as the session does.
		 *
		 * @param $key    Property identifier or array of them
		 */
		public function unmarkTempData( $key )
		{
			if ( empty( $_SESSION[ '__o2session_vars' ] ) )
			{
				return;
			}

			is_array( $key ) OR $key = [ $key ];

			foreach ( $key as $k )
			{
				if ( isset( $_SESSION[ '__o2session_vars' ][ $k ] ) && is_int( $_SESSION[ '__o2session_vars' ][ $k ] ) )
				{
					unset( $_SESSION[ '__o2session_vars' ][ $k ] );
				}
			}

			if ( empty( $_SESSION[ '__o2session_vars' ] ) )
			{
				unset( $_SESSION[ '__o2session_vars' ] );
			}
		}

		//--------------------------------------------------------------------

		/**
		 * Retrieve the keys of all session data that have been marked as temporary data.
		 *
		 * @return array
		 */
		public function getTempKeys()
		{
			if ( ! isset( $_SESSION[ '__o2session_vars' ] ) )
			{
				return [ ];
			}

			$keys = [ ];
			foreach ( array_keys( $_SESSION[ '__o2session_vars' ] ) as $key )
			{
				is_int( $_SESSION[ '__o2session_vars' ][ $key ] ) && $keys[] = $key;
			}

			return $keys;
		}

		/**
		 * Started
		 *
		 * Check if the PHP Session is has been started.
		 *
		 * @access  public
		 * @return  bool
		 */
		public function isStarted()
		{
			if ( php_sapi_name() !== 'cli' )
			{
				if ( version_compare( phpversion(), '5.4.0', '>=' ) )
				{
					return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
				}
				else
				{
					return session_id() === '' ? FALSE : TRUE;
				}
			}

			return FALSE;
		}

		/**
		 * Retrieve an external iterator
		 *
		 * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
		 * @return Traversable An instance of an object implementing <b>Iterator</b> or
		 *        <b>Traversable</b>
		 * @since 5.0.0
		 */
		public function getIterator()
		{
			return new ArrayIterator( $_SESSION );
		}

		/**
		 * Whether a offset exists
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
		 *
		 * @param mixed $offset <p>
		 *                      An offset to check for.
		 *                      </p>
		 *
		 * @return boolean true on success or false on failure.
		 * </p>
		 * <p>
		 * The return value will be casted to boolean if non-boolean was returned.
		 * @since 5.0.0
		 */
		public function offsetExists( $offset )
		{
			return $this->has( $offset );
		}

		/**
		 * Offset to retrieve
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to retrieve.
		 *                      </p>
		 *
		 * @return mixed Can return all value types.
		 * @since 5.0.0
		 */
		public function offsetGet( $offset )
		{
			return $this->get( $offset );
		}

		/**
		 * Offset to set
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to assign the value to.
		 *                      </p>
		 * @param mixed $value  <p>
		 *                      The value to set.
		 *                      </p>
		 *
		 * @return void
		 * @since 5.0.0
		 */
		public function offsetSet( $offset, $value )
		{
			$this->set( $offset, $value );
		}

		/**
		 * Offset to unset
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to unset.
		 *                      </p>
		 *
		 * @return void
		 * @since 5.0.0
		 */
		public function offsetUnset( $offset )
		{
			$this->delete( $offset );
		}

		/**
		 * Count elements of an object
		 *
		 * @link  http://php.net/manual/en/countable.count.php
		 * @return int The custom count as an integer.
		 *        </p>
		 *        <p>
		 *        The return value is cast to an integer.
		 * @since 5.1.0
		 */
		public function count()
		{
			return (int) count( $_SESSION );
		}
	}
}

namespace O2System\Session
{

	use O2System\Core\SPL\ArrayObject;

	// ------------------------------------------------------------------------

	/**
	 * Class Config
	 *
	 * @package O2System\Cache
	 */
	class Config extends ArrayObject
	{
		/**
		 * Config constructor.
		 *
		 * @param   array $config
		 *
		 * @access  public
		 */
		public function __construct( array $config = [ ] )
		{
			/**
			 * Defaults configurations
			 */
			$defaults = [
				'apc'       => [
					'handler' => 'apc',
				],
				'file'      => [
					'handler' => 'file',
					'path'    => APPSPATH . 'cache' . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR,
				],
				'memcached' => [
					'handler' => 'memcached',
					'host'    => '127.0.0.1',
					'port'    => 11211,
					'weight'  => 1,
				],
				'redis'     => [
					'handler'  => 'redis',
					'host'     => '127.0.0.1',
					'password' => NULL,
					'port'     => 6379,
					'timeout'  => 0,
				],
				'wincache'  => [
					'handler' => 'wincache',
				],
				'xcache'    => [
					'handler' => 'xcache',
				],
			];

			if ( isset( $config[ 'handler' ] ) )
			{
				$config[ 'handler' ] = $config[ 'handler' ] === 'files' ? 'file' : $config[ 'handler' ];

				$default = $defaults[ $config[ 'handler' ] ];

				$config = array_merge( $default, $config );
			}

			foreach ( [ 'match', 'regenerate', 'cookie' ] as $index )
			{
				if ( isset( $config[ $index ] ) )
				{
					if ( is_array( $config[ $index ] ) )
					{
						$config[ $index ] = new ArrayObject( $config[ $index ] );
					}
				}
			}


			parent::__construct( $config );
		}

		/**
		 * Check if server is support platform
		 *
		 * @access public
		 * @return bool
		 */
		public static function isSupported( $platform )
		{
			$platform = strtolower( $platform );

			if ( isset( static::$_valid_handlers[ $platform ] ) )
			{
				$class_name = '\O2System\Session\Handlers\\' . static::$_valid_handlers[ $platform ];

				if ( class_exists( $class_name ) )
				{
					// Initialize Cache Handler
					return (bool) ( new $class_name() )->isSupported();
				}
			}

			return FALSE;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Class Exception
	 *
	 * @package O2System\Cache
	 */
	class Exception extends \O2System\Core\Exception
	{
		/**
		 * Library Description
		 *
		 * @var array
		 */
		public $library = [
			'name'        => 'O2System Session (O2Session)',
			'description' => 'Open Source PHP Session Management',
			'version'     => '1.0',
		];

		/**
		 * Custom view exception filename
		 *
		 * @var string
		 */
		public $view_exception = 'session_exception.php';
	}

	// ------------------------------------------------------------------------

	/**
	 * Class StorageException
	 *
	 * @package O2System\Cache
	 */
	class StorageException extends Exception
	{

	}

	// ------------------------------------------------------------------------

	/**
	 * Class UndefinedHandlerException
	 *
	 * @package O2System\Cache
	 */
	class UndefinedHandlerException extends Exception
	{
	}

	// ------------------------------------------------------------------------

	class UnsupportedHandlerException extends Exception
	{
		/**
		 * UnsupportedHandlerException constructor.
		 *
		 * @param string $message
		 * @param int    $code
		 * @param array  $args
		 *
		 * @access  public
		 */
		public function __construct( $message, $code = 0, $args = [ ] )
		{
			$this->args = $args;
			parent::__construct( $message, $code );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Class BadMethodCallException
	 *
	 * @package O2System\Cache
	 */
	class BadMethodCallException extends Exception
	{
		/**
		 * BadMethodCallException constructor.
		 *
		 * @param string $message
		 * @param int    $code
		 * @param array  $args
		 *
		 * @access  public
		 */
		public function __construct( $message, $code, $args = [ ] )
		{
			$this->args = $args;
			parent::__construct( $message, $code );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Class HandlerException
	 *
	 * @package O2System\Cache
	 */
	class HandlerException extends Exception
	{
	}
}