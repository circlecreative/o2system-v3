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

use O2System\Core\Gears\Logger;
use O2System\Session\Interfaces\Driver;
use O2System\Session\Interfaces\Handler;

/**
 * Session Redis Driver
 *
 * Based on CodeIgniter Session Redis Driver by Andrey Andreev
 *
 * @package       o2session
 * @subpackage    Libraries
 * @category      Sessions
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2session/user-guide/drivers/redis
 */
class Redis extends Driver implements \SessionHandlerInterface
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
	 * @param    array $params Configuration parameters
	 *
	 * @access  public
	 * @throws  \InvalidArgumentException
	 */
	public function __construct( &$params )
	{
		if ( empty( $params[ 'storage' ][ 'save_path' ] ) )
		{
			throw new \InvalidArgumentException( 'Session: No Redis save path configured.' );
		}
		elseif ( is_string( $params[ 'storage' ][ 'save_path' ] ) AND strpos( $params[ 'storage' ][ 'save_path' ], '://' ) !== FALSE )
		{
			/**
			 * Parse the URL from the DSN string
			 * parameters or as a data source name in the first
			 * parameter. DSNs must have this prototype:
			 * $dsn = 'tcp://password@hostname:port/?timeout=5';
			 */
			if ( ( $dsn = @parse_url( $params[ 'storage' ][ 'save_path' ] ) ) === FALSE )
			{
				throw new \InvalidArgumentException( 'Session: Invalid Redis save path format: ' . $params[ 'storage' ][ 'save_path' ] );
			}

			$params[ 'storage' ][ 'save_path' ] = array(
				'socket'   => isset( $dsn[ 'scheme' ] ) ? rawurldecode( $dsn[ 'scheme' ] ) : '',
				'password' => isset( $dsn[ 'username' ] ) ? rawurldecode( $dsn[ 'username' ] ) : '',
				'host'     => isset( $dsn[ 'hostname' ] ) ? rawurldecode( $dsn[ 'hostname' ] ) : '',
				'port'     => isset( $dsn[ 'port' ] ) ? rawurldecode( $dsn[ 'port' ] ) : '',
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
			[ 1 ];
		}

		if ( ! isset( $params[ 'storage' ][ 'save_path' ][ 'host' ] ) )
		{
			throw new \InvalidArgumentException( 'Session: Invalid Redis save path format: ' . $params[ 'storage' ][ 'save_path' ] );
		}

		parent::__construct( $params );

		if ( $this->_config[ 'storage' ][ 'match_ip' ] === TRUE )
		{
			$this->_key_prefix .= $_SERVER[ 'REMOTE_ADDR' ] . ':';
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Sanitizes save_path and initializes connection.
	 *
	 * @param   string $save_path Server path
	 * @param   string $name      Session cookie name, unused
	 *
	 * @access  public
	 * @return  bool
	 * @throws  \RuntimeException
	 */
	public function open( $save_path, $name )
	{
		if ( empty( $this->_config[ 'storage' ][ 'save_path' ] ) )
		{
			return FALSE;
		}

		$redis = new \Redis();

		if ( ! $redis->connect( $this->_config[ 'storage' ][ 'save_path' ][ 'host' ], $this->_config[ 'storage' ][ 'save_path' ][ 'port' ], $this->_config[ 'storage' ][ 'save_path' ][ 'timeout' ] ) )
		{
			throw new \RuntimeException( 'Session: Unable to connect to Redis with the configured settings.' );
		}
		elseif ( isset( $this->_config[ 'storage' ][ 'save_path' ][ 'password' ] ) && ! $redis->auth( $this->_config[ 'storage' ][ 'save_path' ][ 'password' ] ) )
		{
			throw new \RuntimeException( 'Session: Unable to authenticate to Redis instance.' );
		}
		elseif ( isset( $this->_config[ 'storage' ][ 'save_path' ][ 'database' ] ) && ! $redis->select( $this->_config[ 'storage' ][ 'save_path' ][ 'database' ] ) )
		{
			throw new \RuntimeException( 'Session: Unable to select Redis database with index ' . $this->_config[ 'storage' ][ 'save_path' ][ 'database' ] );
		}
		else
		{
			$this->_handle = $redis;

			return TRUE;
		}

		return FALSE;
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
			$this->_handle->setTimeout( $this->_lock_key, 300 );
			if ( $this->_fingerprint !== ( $fingerprint = md5( $session_data ) ) )
			{
				if ( $this->_handle->set( $this->_key_prefix . $session_id, $session_data, $this->_config[ 'storage' ][ 'lifetime' ] ) )
				{
					$this->_fingerprint = $fingerprint;

					return TRUE;
				}

				return FALSE;
			}

			return $this->_handle->setTimeout( $this->_key_prefix . $session_id, $this->_config[ 'storage' ][ 'lifetime' ] );
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
	 * @throws  \RuntimeException
	 */
	public function close()
	{
		if ( isset( $this->_handle ) )
		{
			try
			{
				if ( $this->_handle->ping() === '+PONG' )
				{
					isset( $this->_lock_key ) && $this->_handle->delete( $this->_lock_key );
					if ( ! $this->_handle->close() )
					{
						return FALSE;
					}
				}
			}
			catch ( \RedisException $e )
			{
				throw new \RuntimeException( 'Session: Got RedisException on close(): ' . $e->getMessage() );
			}

			$this->_handle = NULL;

			return TRUE;
		}

		return TRUE;
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
		// Not necessary, Redis takes care of that.
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
	 * @access  protected
	 * @return  bool
	 */
	protected function _get_lock( $session_id )
	{
		if ( isset( $this->_lock_key ) )
		{
			return $this->_handle->setTimeout( $this->_lock_key, 300 );
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->_key_prefix . $session_id . ':lock';
		$attempt = 0;
		do
		{
			if ( ( $ttl = $this->_handle->ttl( $lock_key ) ) > 0 )
			{
				sleep( 1 );
				continue;
			}

			if ( ! $this->_handle->setex( $lock_key, 300, time() ) )
			{
				return FALSE;
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
	 * @access  protected
	 * @return  bool
	 */
	protected function _release_lock()
	{
		if ( isset( $this->_handle, $this->_lock_key ) && $this->_lock )
		{
			if ( ! $this->_handle->delete( $this->_lock_key ) )
			{
				return FALSE;
			}

			$this->_lock_key = NULL;
			$this->_lock = FALSE;
		}

		return TRUE;
	}

}
