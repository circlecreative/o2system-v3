<?php
/**
 * O2Session
 *
 * An open source Session Management Library for PHP 5.4 or newer
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
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://o2system.in/features/o2session/license
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://o2system.in
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System
{
	/**
	 * Session Class
	 *
	 * Based on CodeIgniter Session Library by Andrey Andreev
	 *
	 * @package     o2session
	 * @author      O2System Developer Team
	 * @link        http://o2system.in/features/o2session
	 */
	class Session
	{
		/**
		 * Valid Drivers List
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_valid_drivers = array(
			'database',
			'files',
			'memcached',
			'redis',
		);

		/**
		 * Session Driver
		 *
		 * @access  protected
		 * @type    string
		 */
		protected $_driver = 'files';

		/**
		 * Session Config
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_config;

		// ------------------------------------------------------------------------

		/**
		 * Class constructor
		 *
		 * @param   array $config Configuration parameters
		 *
		 * @access  public
		 */
		public function __construct( array $config = array() )
		{
			if ( PHP_SAPI === 'cli' OR // No sessions under CLI
				defined( 'STDIN' ) OR
				(bool) ini_get( 'session.auto_start' )
			)
			{
				return;
			}

			if ( ! empty( $config ) )
			{
				$this->_config = $config;
			}

			// Initialize Configuration
			$this->_configure();

			if ( ! empty( $this->_config[ 'storage' ][ 'driver' ] ) )
			{
				if ( in_array( $this->_config[ 'storage' ][ 'driver' ], $this->_valid_drivers ) )
				{
					$this->_driver = $this->_config[ 'storage' ][ 'driver' ];
				}
			}

			$class = 'O2System\Session\Drivers\\' . ucfirst( $this->_driver );
			$class = new $class( $this->_config );

			if ( $class instanceof \SessionHandlerInterface )
			{
				session_set_save_handler( $class, TRUE );

				// Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
				if ( isset( $_COOKIE[ $this->_config[ 'cookie' ][ 'name' ] ] )
					&& (
						! is_string( $_COOKIE[ $this->_config[ 'cookie' ][ 'name' ] ] )
						OR ! preg_match( '/^[0-9a-f]{40}$/', $_COOKIE[ $this->_config[ 'cookie' ][ 'name' ] ] )
					)
				)
				{
					print_out('t');
					unset( $_COOKIE[ $this->_config[ 'cookie' ][ 'name' ] ] );
				}

				session_start();

				// Is session ID auto-regeneration configured? (ignoring ajax requests)
				if ( ( empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) OR strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) !== 'xmlhttprequest' )
					&& ( $regenerate_time = $this->_config[ 'storage' ][ 'regenerate_time' ] ) > 0
				)
				{
					if ( ! isset( $_SESSION[ '__session_last_regenerate' ] ) )
					{
						$_SESSION[ '__session_last_regenerate' ] = time();
					}
					elseif ( $_SESSION[ '__session_last_regenerate' ] < ( time() - $regenerate_time ) )
					{
						$this->regenerate_id( (bool) $this->_config[ 'storage' ][ 'regenerate_id' ] );
					}
				}
				// Another work-around ... PHP doesn't seem to send the session cookie
				// unless it is being currently created or regenerated
				elseif ( isset( $_COOKIE[ $this->_config[ 'cookie' ][ 'name' ] ] ) && $_COOKIE[ $this->_config[ 'cookie' ][ 'name' ] ] === session_id() )
				{
					setcookie(
						$this->_config[ 'cookie' ][ 'name' ],
						session_id(),
						( empty( $this->_config[ 'cookie' ][ 'lifetime' ] ) ? 0 : time() + $this->_config[ 'cookie' ][ 'lifetime' ] ),
						$this->_config[ 'cookie' ][ 'path' ],
						$this->_config[ 'cookie' ][ 'domain' ],
						$this->_config[ 'cookie' ][ 'secure' ],
						TRUE
					);
				}

				$this->_init_vars();
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Configuration
		 *
		 * Handle input parameters and configuration defaults
		 *
		 * @access  protected
		 * @return  void
		 */
		protected function _configure()
		{
			// Configure Session
			if ( empty( $this->_config[ 'storage' ][ 'name' ] ) )
			{
				$this->_config[ 'storage' ][ 'name' ] = ini_get( 'session.name' );
			}
			else
			{
				ini_set( 'session.name', $this->_config[ 'storage' ][ 'name' ] );
			}

			if ( empty( $this->_config[ 'storage' ][ 'lifetime' ] ) )
			{
				$this->_config[ 'storage' ][ 'lifetime' ] = (int) ini_get( 'session.gc_maxlifetime' );
			}
			else
			{
				ini_set( 'session.gc_maxlifetime', $this->_config[ 'storage' ][ 'lifetime' ] );
			}

			// Configure Session Cookie
			if ( empty( $this->_config[ 'cookie' ][ 'lifetime' ] ) )
			{
				$this->_config[ 'cookie' ][ 'lifetime' ] = (int) $this->_config[ 'storage' ][ 'lifetime' ];
			}

			if ( empty( $this->_config[ 'cookie' ][ 'name' ] ) )
			{
				$this->_config[ 'cookie' ][ 'name' ] = $this->_config[ 'storage' ][ 'name' ];
			}

			session_set_cookie_params(
				$this->_config[ 'cookie' ][ 'lifetime' ],
				$this->_config[ 'cookie' ][ 'path' ],
				$this->_config[ 'cookie' ][ 'domain' ],
				(bool) $this->_config[ 'cookie' ][ 'secure' ],
				TRUE // HttpOnly; Yes, this is intentional and not configurable for security reasons
			);

			// Security is king
			ini_set( 'session.use_trans_sid', 0 );
			ini_set( 'session.use_strict_mode', 1 );
			ini_set( 'session.use_cookies', 1 );
			ini_set( 'session.use_only_cookies', 1 );
			ini_set( 'session.hash_function', 1 );
			ini_set( 'session.hash_bits_per_character', 4 );
		}

		// ------------------------------------------------------------------------

		/**
		 * Handle temporary variables
		 *
		 * Clears old "flash" data, marks the new one for deletion and handles
		 * "temp" data deletion.
		 *
		 * @access  protected
		 */
		protected function _init_vars()
		{
			if ( ! empty( $_SESSION[ '__session_vars' ] ) )
			{
				$current_time = time();

				foreach ( $_SESSION[ '__session_vars' ] as $key => &$value )
				{
					if ( $value === 'new' )
					{
						$_SESSION[ '__session_vars' ][ $key ] = 'old';
					}
					// Hacky, but 'old' will (implicitly) always be less than time() ;)
					// DO NOT move this above the 'new' check!
					elseif ( $value < $current_time )
					{
						unset( $_SESSION[ $key ], $_SESSION[ '__session_vars' ][ $key ] );
					}
				}

				if ( empty( $_SESSION[ '__session_vars' ] ) )
				{
					unset( $_SESSION[ '__session_vars' ] );
				}
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Mark as flash
		 *
		 * @param   mixed $key Session data key(s)
		 *
		 * @access  public
		 * @return  bool
		 */
		public function mark_flashdata( $key )
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

				$_SESSION[ '__session_vars' ] = isset( $_SESSION[ '__session_vars' ] )
					? array_merge( $_SESSION[ '__session_vars' ], $new )
					: $new;

				return TRUE;
			}

			if ( ! isset( $_SESSION[ $key ] ) )
			{
				return FALSE;
			}

			$_SESSION[ '__session_vars' ][ $key ] = 'new';

			return TRUE;
		}

		// ------------------------------------------------------------------------

		/**
		 * Get flash keys
		 *
		 * @access  public
		 * @return  array
		 */
		public function get_flashdata()
		{
			if ( ! isset( $_SESSION[ '__session_vars' ] ) )
			{
				return array();
			}

			$keys = array();
			foreach ( array_keys( $_SESSION[ '__session_vars' ] ) as $key )
			{
				is_int( $_SESSION[ '__session_vars' ][ $key ] ) OR $keys[] = $key;
			}

			return $keys;
		}

		// ------------------------------------------------------------------------

		/**
		 * Unmark flash
		 *
		 * @param   mixed $key Session data key(s)
		 *
		 * @access  public
		 * @return  void
		 */
		public function unset_flashdata( $key )
		{
			if ( empty( $_SESSION[ '__session_vars' ] ) )
			{
				return;
			}

			is_array( $key ) OR $key = array( $key );

			foreach ( $key as $k )
			{
				if ( isset( $_SESSION[ '__session_vars' ][ $k ] ) && ! is_int( $_SESSION[ '__session_vars' ][ $k ] ) )
				{
					unset( $_SESSION[ '__session_vars' ][ $k ] );
				}
			}

			if ( empty( $_SESSION[ '__session_vars' ] ) )
			{
				unset( $_SESSION[ '__session_vars' ] );
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Mark as temp
		 *
		 * @param   mixed $key Session data key(s)
		 * @param   int   $ttl Time-to-live in seconds
		 *
		 * @access  public
		 * @return  bool
		 */
		public function mark_tempdata( $key, $ttl = 300 )
		{
			$ttl += time();

			if ( is_array( $key ) )
			{
				$temp = array();

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

				$_SESSION[ '__session_vars' ] = isset( $_SESSION[ '__session_vars' ] )
					? array_merge( $_SESSION[ '__session_vars' ], $temp )
					: $temp;

				return TRUE;
			}

			if ( ! isset( $_SESSION[ $key ] ) )
			{
				return FALSE;
			}

			$_SESSION[ '__session_vars' ][ $key ] = $ttl;

			return TRUE;
		}

		// ------------------------------------------------------------------------

		/**
		 * Get temp keys
		 *
		 * @access  public
		 * @return  array
		 */
		public function get_tempdata()
		{
			if ( ! isset( $_SESSION[ '__session_vars' ] ) )
			{
				return array();
			}

			$keys = array();
			foreach ( array_keys( $_SESSION[ '__session_vars' ] ) as $key )
			{
				is_int( $_SESSION[ '__session_vars' ][ $key ] ) && $keys[] = $key;
			}

			return $keys;
		}

		// ------------------------------------------------------------------------

		/**
		 * __get()
		 *
		 * @param   string $key 'session_id' or a session data key
		 *
		 * @access  public
		 * @return  mixed
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

		// ------------------------------------------------------------------------

		/**
		 * __set()
		 *
		 * @param   string $key   Session data key
		 * @param   mixed  $value Session data value
		 *
		 * @access  public
		 */
		public function __set( $key, $value )
		{
			$_SESSION[ $key ] = $value;
		}

		// ------------------------------------------------------------------------

		/**
		 * Session destroy
		 *
		 * Legacy Session compatibility method
		 *
		 * @access  public
		 */
		public function destroy()
		{
			session_destroy();
		}

		// ------------------------------------------------------------------------

		/**
		 * Session regenerate
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   bool $destroy Destroy old session data flag
		 *
		 * @access  public
		 */
		public function regenerate_id( $destroy = FALSE )
		{
			$_SESSION[ '__session_last_regenerate' ] = time();
			session_regenerate_id( $destroy );
		}

		// ------------------------------------------------------------------------

		/**
		 * Get userdata reference
		 *
		 * Legacy Session compatibility method
		 *
		 * @access  public
		 * @return  array
		 */
		public function &get_userdata()
		{
			return $_SESSION;
		}

		// ------------------------------------------------------------------------

		/**
		 * Userdata (fetch)
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   string $key Session data key
		 *
		 * @access  public
		 * @return  mixed   Session data value or NULL if not found
		 */
		public function userdata( $key = NULL )
		{
			if ( isset( $key ) )
			{
				return isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : NULL;
			}
			elseif ( empty( $_SESSION ) )
			{
				return array();
			}

			$userdata = array();
			$_exclude = array_merge(
				array( '__session_vars' ),
				$this->get_flashdata(),
				$this->get_tempdata()
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

		// ------------------------------------------------------------------------

		/**
		 * Set userdata
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   mixed $data  Session data key or an associative array
		 * @param   mixed $value Value to store
		 *
		 * @access  public
		 */
		public function set_userdata( $data, $value = NULL )
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

		// ------------------------------------------------------------------------

		/**
		 * Unset userdata
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   mixed $key Session data key(s)
		 *
		 * @access  public
		 */
		public function unset_userdata( $key )
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

		// ------------------------------------------------------------------------

		/**
		 * All userdata (fetch)
		 *
		 * Legacy Session compatibility method
		 *
		 * @access  public
		 * @return  array $_SESSION, excluding flash data items
		 */
		public function all_userdata()
		{
			return $this->userdata();
		}

		// ------------------------------------------------------------------------

		/**
		 * Has userdata
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   string $key Session data key
		 *
		 * @access  public
		 * @return  bool
		 */
		public function has_userdata( $key )
		{
			return isset( $_SESSION[ $key ] );
		}

		// ------------------------------------------------------------------------

		/**
		 * Flashdata (fetch)
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   string $key Session data key
		 *
		 * @access  public
		 * @return  mixed   Session data value or NULL if not found
		 */
		public function flashdata( $key = NULL )
		{
			if ( isset( $key ) )
			{
				return ( isset( $_SESSION[ '__session_vars' ], $_SESSION[ '__session_vars' ][ $key ], $_SESSION[ $key ] ) && ! is_int( $_SESSION[ '__session_vars' ][ $key ] ) )
					? $_SESSION[ $key ]
					: NULL;
			}

			$flashdata = array();

			if ( ! empty( $_SESSION[ '__session_vars' ] ) )
			{
				foreach ( $_SESSION[ '__session_vars' ] as $key => &$value )
				{
					is_int( $value ) OR $flashdata[ $key ] = $_SESSION[ $key ];
				}
			}

			return $flashdata;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set flashdata
		 *
		 * Legacy Session compatibiliy method
		 *
		 * @param   mixed $data  Session data key or an associative array
		 * @param   mixed $value Value to store
		 *
		 * @access  public
		 */
		public function set_flashdata( $data, $value = NULL )
		{
			$this->set_userdata( $data, $value );
			$this->mark_flashdata( is_array( $data ) ? array_keys( $data ) : $data );
		}

		// ------------------------------------------------------------------------

		/**
		 * Keep flashdata
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   mixed $key Session data key(s)
		 *
		 * @access  public
		 */
		public function keep_flashdata( $key )
		{
			$this->mark_flashdata( $key );
		}

		// ------------------------------------------------------------------------

		/**
		 * Temp data (fetch)
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   string $key Session data key
		 *
		 * @access  public
		 * @return  mixed   Session data value or NULL if not found
		 */
		public function tempdata( $key = NULL )
		{
			if ( isset( $key ) )
			{
				return ( isset( $_SESSION[ '__session_vars' ], $_SESSION[ '__session_vars' ][ $key ], $_SESSION[ $key ] ) && is_int( $_SESSION[ '__session_vars' ][ $key ] ) )
					? $_SESSION[ $key ]
					: NULL;
			}

			$tempdata = array();

			if ( ! empty( $_SESSION[ '__session_vars' ] ) )
			{
				foreach ( $_SESSION[ '__session_vars' ] as $key => &$value )
				{
					is_int( $value ) && $tempdata[ $key ] = $_SESSION[ $key ];
				}
			}

			return $tempdata;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set tempdata
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   mixed $data  Session data key or an associative array of items
		 * @param   mixed $value Value to store
		 * @param   int   $ttl   Time-to-live in seconds
		 *
		 * @access  public
		 */
		public function set_tempdata( $data, $value = NULL, $ttl = 300 )
		{
			$this->set_userdata( $data, $value );
			$this->mark_tempdata( is_array( $data ) ? array_keys( $data ) : $data, $ttl );
		}

		// ------------------------------------------------------------------------

		/**
		 * Unset tempdata
		 *
		 * Legacy Session compatibility method
		 *
		 * @param   mixed $key Session data key(s)
		 *
		 * @access  public
		 */
		public function unset_tempdata( $key )
		{
			$this->unset_flashdata( $key );
		}

		/**
		 * Started
		 *
		 * Check if the PHP Session is has been started.
		 *
		 * @access  public
		 * @return  bool
		 */
		public function is_started()
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
	}
}

namespace O2System\Session
{

	use O2System\Glob\Interfaces\ExceptionInterface;

	/**
	 * Class Exception
	 *
	 * @package O2System\Session
	 */
	class Exception extends ExceptionInterface
	{
	}
}