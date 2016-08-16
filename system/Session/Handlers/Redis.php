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

namespace O2System\Session\Handlers;

// ------------------------------------------------------------------------

use O2System\Session\HandlerException;
use O2System\Session\Interfaces\HandlerInterface;

/**
 * Class Redis
 *
 * @package O2System\Session\Handlers
 */
class Redis extends HandlerInterface implements \SessionHandlerInterface
{
	/**
	 * Platform Name
	 *
	 * @access  protected
	 * @var string
	 */
	protected $platform = 'redis';

	/**
	 * Redis Object
	 *
	 * @var \Redis
	 */
	protected $redis;

	/**
	 * Initialize session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.open.php
	 *
	 * @param string $save_path The path where to store/retrieve the session.
	 * @param string $name      The session name.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function open( $save_path, $name )
	{
		if ( class_exists( 'Redis', FALSE ) )
		{
			$this->redis = new \Redis();
		}
		else
		{
			\O2System::$log->error( 'Session: Failed to create Redis object; extension not loaded?' );

			return FALSE;
		}

		try
		{
			if ( ! $this->redis->connect(
				$this->config[ 'host' ], ( $this->config[ 'host' ][ 0 ] === '/' ? 0
				: $this->config[ 'port' ] ), $this->config[ 'timeout' ] )
			)
			{
				\O2System::$log->error( 'Session: Redis connection failed. Check your configuration.' );

				return FALSE;
			}

			if ( isset( $this->config[ 'password' ] ) AND ! $this->redis->auth( $this->config[ 'password' ] ) )
			{
				\O2System::$log->error( 'Session: Redis authentication failed.' );

				return FALSE;
			}

			return TRUE;
		}
		catch ( \RedisException $e )
		{
			\O2System::$log->error( 'Session: Redis connection refused (' . $e->getMessage() . ')' );

			return FALSE;
		}
	}

	/**
	 * Close the session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.close.php
	 * @return bool <p>
	 *        The return value (usually TRUE on success, FALSE on failure).
	 *        Note this value is returned internally to PHP for processing.
	 *        </p>
	 * @since 5.4.0
	 */
	public function close()
	{
		if ( isset( $this->redis ) )
		{
			try
			{
				if ( $this->redis->ping() === '+PONG' )
				{
					isset( $this->lockKey ) AND $this->redis->delete( $this->lockKey );

					if ( ! $this->redis->close() )
					{
						return FALSE;
					}
				}
			}
			catch ( \RedisException $e )
			{
				\O2System::$log->error( 'Session: Got RedisException on close(): ' . $e->getMessage() );
			}

			$this->redis = NULL;

			return TRUE;
		}

		return TRUE;
	}

	/**
	 * Destroy a session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.destroy.php
	 *
	 * @param string $session_id The session ID being destroyed.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function destroy( $session_id )
	{
		if ( isset( $this->redis, $this->isLocked ) )
		{
			if ( ( $result = $this->redis->delete( $this->prefixKey . $session_id ) ) !== 1 )
			{
				\O2System::$log->debug( 'Session: Redis::delete() expected to return 1, got ' . var_export( $result, TRUE ) . ' instead.' );
			}

			return $this->_destroyCookie();
		}

		return FALSE;
	}

	/**
	 * Cleanup old sessions
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.gc.php
	 *
	 * @param int $maxlifetime <p>
	 *                         Sessions that have not updated for
	 *                         the last maxlifetime seconds will be removed.
	 *                         </p>
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function gc( $maxlifetime )
	{
		// Not necessary, Redis takes care of that.
		return TRUE;
	}

	/**
	 * Read session data
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.read.php
	 *
	 * @param string $session_id The session id to read data for.
	 *
	 * @return string <p>
	 * Returns an encoded string of the read data.
	 * If nothing was read, it must return an empty string.
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function read( $session_id )
	{
		if ( isset( $this->redis ) AND $this->_lockSession( $session_id ) )
		{
			// Needed by write() to detect session_regenerate_id() calls
			$this->sessionId = $session_id;

			$session_data      = (string) $this->redis->get( $this->prefixKey . $session_id );
			$this->fingerprint = md5( $session_data );

			return $session_data;
		}

		return FALSE;
	}

	/**
	 * Write session data
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.write.php
	 *
	 * @param string $session_id   The session id.
	 * @param string $session_data <p>
	 *                             The encoded session data. This data is the
	 *                             result of the PHP internally encoding
	 *                             the $_SESSION superglobal to a serialized
	 *                             string and passing it as this parameter.
	 *                             Please note sessions use an alternative serialization method.
	 *                             </p>
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function write( $session_id, $session_data )
	{
		if ( ! isset( $this->redis ) )
		{
			return FALSE;
		}
		// Was the ID regenerated?
		elseif ( $session_id !== $this->sessionId )
		{
			if ( ! $this->_lockRelease() OR ! $this->_lockSession( $session_id ) )
			{
				return FALSE;
			}

			$this->fingerprint = md5( '' );
			$this->sessionId   = $session_id;
		}

		if ( isset( $this->lockKey ) )
		{
			$this->redis->setTimeout( $this->lockKey, 300 );

			if ( $this->fingerprint !== ( $fingerprint = md5( $session_data ) ) )
			{
				if ( $this->redis->set( $this->prefixKey . $session_id, $session_data, $this->config[ 'lifetime' ] ) )
				{
					$this->fingerprint = $fingerprint;

					return TRUE;
				}

				return FALSE;
			}

			return $this->redis->setTimeout( $this->prefixKey . $session_id, $this->config[ 'lifetime' ] );
		}

		return FALSE;
	}

	/**
	 * Get lock
	 *
	 * Acquires an (emulated) lock.
	 *
	 * @param    string $session_id Session ID
	 *
	 * @return    bool
	 */
	protected function _lockSession( $session_id )
	{
		if ( isset( $this->lockKey ) )
		{
			return $this->redis->setTimeout( $this->lockKey, 300 );
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->prefixKey . $session_id . ':lock';
		$attempt  = 0;

		do
		{
			if ( ( $ttl = $this->redis->ttl( $lock_key ) ) > 0 )
			{
				sleep( 1 );
				continue;
			}

			if ( ! $this->redis->setex( $lock_key, 300, time() ) )
			{
				\O2System::$log->error( 'Session: Error while trying to obtain lock for ' . $this->prefixKey . $session_id );

				return FALSE;
			}

			$this->lockKey = $lock_key;
			break;
		}
		while ( ++$attempt < 30 );

		if ( $attempt === 30 )
		{
			\O2System::$log->error( 'Session: Unable to obtain lock for ' . $this->prefixKey . $session_id . ' after 30 attempts, aborting.' );

			return FALSE;
		}
		elseif ( $ttl === -1 )
		{
			\O2System::$log->debug( 'Session: Lock for ' . $this->prefixKey . $session_id . ' had no TTL, overriding.' );
		}

		$this->isLocked = TRUE;

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Release lock
	 *
	 * Releases a previously acquired lock
	 *
	 * @return    bool
	 */
	protected function _lockRelease()
	{
		if ( isset( $this->redis, $this->lockKey ) && $this->isLocked )
		{
			if ( ! $this->redis->delete( $this->lockKey ) )
			{
				throw new HandlerException( 'Session: Error while trying to free lock for ' . $this->lockKey );

				return FALSE;
			}

			$this->lockKey  = NULL;
			$this->isLocked = FALSE;
		}

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported()
	{
		return (bool) extension_loaded( 'redis' );
	}

	//--------------------------------------------------------------------
}