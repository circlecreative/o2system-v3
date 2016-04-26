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

namespace O2System\Session\Drivers;

// ------------------------------------------------------------------------

use O2System\Session\Interfaces\Driver;

/**
 * Session Memcached Driver
 *
 * Based on CodeIgniter Session Memcached Driver by Andrey Andreev
 *
 * @package       o2session
 * @subpackage    Drivers
 * @category      Sessions
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2session/user-guide/drivers/memcached
 */
class Memcached extends Driver implements \SessionHandlerInterface
{
	/**
	 * Key prefix
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $_key_prefix = 'o2session:';

	/**
	 * Lock key
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $_lock_key;

	// ------------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param   array $params Configuration parameters
	 *
	 * @access  public
	 * @throws  \InvalidArgumentException
	 */
	public function __construct( &$params )
	{
		if ( empty( $params[ 'storage' ][ 'save_path' ] ) )
		{
			throw new \InvalidArgumentException( 'Session: No Memcached save path configured.' );
		}
		elseif ( is_string( $params[ 'storage' ][ 'save_path' ] ) AND strpos( $params[ 'storage' ][ 'save_path' ], '://' ) !== FALSE )
		{
			/**
			 * Parse the URL from the DSN string
			 * parameters or as a data source name in the first
			 * parameter. DSNs must have this prototype:
			 * $dsn = 'memcached://hostname:port/?weight=1';
			 */
			if ( ( $dsn = @parse_url( $params[ 'storage' ][ 'save_path' ] ) ) === FALSE )
			{
				throw new \InvalidArgumentException( 'Session: Invalid Memcached save path format: ' . $params[ 'storage' ][ 'save_path' ] );
			}

			$params[ 'storage' ][ 'save_path' ] = array(
				'host' => isset( $dsn[ 'username' ] ) ? rawurldecode( $dsn[ 'username' ] ) : '',
				'port' => isset( $dsn[ 'password' ] ) ? rawurldecode( $dsn[ 'password' ] ) : '',
			);

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

					$params[ 'storage' ][ 'save_path' ][ $key ] = $value;
				}
			}
		}

		if ( ! isset( $params[ 'storage' ][ 'save_path' ][ 'host' ] ) )
		{
			throw new \InvalidArgumentException( 'Session: Invalid Memcached save path format: ' . $params[ 'storage' ][ 'save_path' ] );
		}

		parent::__construct( $params );

		if ( empty( $this->_config[ 'storage' ][ 'save_path' ] ) )
		{
			throw new \InvalidArgumentException( 'Session: No Memcached save path configured.' );
		}

		if ( $this->_config[ 'storage' ][ 'match_ip' ] === TRUE )
		{
			$this->_key_prefix .= $_SERVER[ 'REMOTE_ADDR' ] . ':';
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Sanitizes save_path and initializes connections.
	 *
	 * @uses    \Memcached
	 *
	 * @param   string $save_path Server path(s)
	 * @param   string $name      Session cookie name, unused
	 *
	 * @access  public
	 * @return  bool
	 */
	public function open( $save_path, $name )
	{
		if ( class_exists( 'Memcached', FALSE ) )
		{
			$this->_handle = new \Memcached();
		}
		elseif ( class_exists( 'Memcache', FALSE ) )
		{
			$this->_handle = new \Memcache();
		}
		else
		{
			return FALSE;
		}

		if ( get_class( $this->_handle ) === 'Memcache' )
		{
			// Third parameter is persistance and defaults to TRUE.
			$this->_handle->addServer(
				$this->_config[ 'storage' ][ 'save_path' ][ 'host' ],
				$this->_config[ 'storage' ][ 'save_path' ][ 'port' ],
				TRUE,
				$this->_config[ 'storage' ][ 'save_path' ][ 'weight' ]
			);
		}
		else
		{
			$this->_handle->addServer(
				$this->_config[ 'storage' ][ 'save_path' ][ 'host' ],
				$this->_config[ 'storage' ][ 'save_path' ][ 'port' ],
				$this->_config[ 'storage' ][ 'save_path' ][ 'weight' ]
			);

			$this->_handle->setOption( \Memcached::OPT_BINARY_PROTOCOL, TRUE ); // required for touch() usage
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Read
	 *
	 * Reads session data and acquires a lock
	 *
	 * @param   string $session_id Session ID
	 *
	 * @access  public
	 * @return  string  Serialized session data
	 */
	public function read( $session_id )
	{
		if ( isset( $this->_handle ) && $this->_get_lock( $session_id ) )
		{
			// Needed by write() to detect session_regenerate_id() calls
			$this->_session_id = $session_id;

			$session_data = (string) $this->_handle->get( $this->_key_prefix . $session_id );
			$this->_fingerprint = md5( $session_data );

			return $session_data;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Write
	 *
	 * Writes (create / update) session data
	 *
	 * @param   string $session_id   Session ID
	 * @param   string $session_data Serialized session data
	 *
	 * @access  public
	 * @return  bool
	 */
	public function write( $session_id, $session_data )
	{
		if ( ! isset( $this->_handle ) )
		{
			return FALSE;
		}
		// Was the ID regenerated?
		elseif ( $session_id !== $this->_session_id )
		{
			if ( ! $this->_release_lock() OR ! $this->_get_lock( $session_id ) )
			{
				return FALSE;
			}

			$this->_fingerprint = md5( '' );
			$this->_session_id = $session_id;
		}

		if ( isset( $this->_lock_key ) )
		{
			if ( get_class( $this->_handle ) === 'Memcache' )
			{
				$this->_handle->replace( $this->_lock_key, time(), FALSE, 300 );
			}
			else
			{
				$this->_handle->replace( $this->_lock_key, time(), 300 );
			}

			if ( $this->_fingerprint !== ( $fingerprint = md5( $session_data ) ) )
			{
				if ( get_class( $this->_handle ) === 'Memcache' )
				{
					if ( $this->_handle->set( $this->_key_prefix . $session_id, $session_data, FALSE, $this->_config[ 'storage' ][ 'lifetime' ] ) )
					{
						$this->_fingerprint = $fingerprint;

						return TRUE;
					}
				}
				else
				{
					if ( $this->_handle->set( $this->_key_prefix . $session_id, $session_data, $this->_config[ 'storage' ][ 'lifetime' ] ) )
					{
						$this->_fingerprint = $fingerprint;

						return TRUE;
					}
				}

				return FALSE;
			}

			if ( get_class( $this->_handle ) === 'Memcache' )
			{
				// set new lifetime
			}
			else
			{
				return $this->_handle->touch( $this->_key_prefix . $session_id, $this->_config[ 'storage' ][ 'lifetime' ] );
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Close
	 *
	 * Releases locks and closes connection.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function close()
	{
		if ( isset( $this->_handle ) )
		{
			isset( $this->_lock_key ) && $this->_handle->delete( $this->_lock_key );

			if ( method_exists( $this->_handle, 'quit' ) )
			{
				if ( ! $this->_handle->quit() )
				{
					return FALSE;
				}
			}
			elseif ( method_exists( $this->_handle, 'close' ) )
			{
				if ( ! $this->_handle->close() )
				{
					return FALSE;
				}
			}

			$this->_handle = NULL;

			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Destroy
	 *
	 * Destroys the current session.
	 *
	 * @param   string $session_id Session ID
	 *
	 * @access  public
	 * @return  bool
	 */
	public function destroy( $session_id )
	{
		if ( isset( $this->_handle, $this->_lock_key ) )
		{
			$this->_handle->delete( $this->_key_prefix . $session_id );

			return $this->_cookie_destroy();
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Garbage Collector
	 *
	 * Deletes expired sessions
	 *
	 * @param   int $maxlifetime Maximum lifetime of sessions
	 *
	 * @access  public
	 * @return  bool
	 */
	public function gc( $maxlifetime )
	{
		// Not necessary, Memcached takes care of that.
		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get lock
	 *
	 * Acquires an (emulated) lock.
	 *
	 * @param   string $session_id Session ID
	 *
	 * @access  public
	 * @return  bool
	 */
	protected function _get_lock( $session_id )
	{
		if ( isset( $this->_lock_key ) )
		{
			return $this->_handle->replace( $this->_lock_key, time(), 300 );
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->_key_prefix . $session_id . ':lock';
		$attempt = 0;
		do
		{
			if ( $this->_handle->get( $lock_key ) )
			{
				sleep( 1 );
				continue;
			}

			if ( get_class( $this->_handle ) === 'Memcache' )
			{
				if ( ! $this->_handle->set( $lock_key, time(), FALSE, 300 ) )
				{
					return FALSE;
				}
			}
			else
			{
				if ( ! $this->_handle->set( $lock_key, time(), 300 ) )
				{
					return FALSE;
				}
			}

			$this->_lock_key = $lock_key;
			break;
		}
		while ( $attempt++ < 30 );

		if ( $attempt === 30 )
		{
			return FALSE;
		}

		$this->_lock = TRUE;

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Release lock
	 *
	 * Releases a previously acquired lock
	 *
	 * @uses    \Memcached
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function _release_lock()
	{
		if ( isset( $this->_handle, $this->_lock_key ) && $this->_lock )
		{
			if ( ! $this->_handle->delete( $this->_lock_key ) && $this->_handle->getResultCode() !== \Memcached::RES_NOTFOUND )
			{
				return FALSE;
			}

			$this->_lock_key = NULL;
			$this->_lock = FALSE;
		}

		return TRUE;
	}
}
