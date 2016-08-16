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

use O2System\Session\Interfaces\HandlerInterface;

/**
 * Class Memcached
 *
 * @package O2System\Session\Handlers
 */
class Memcached extends HandlerInterface implements \SessionHandlerInterface
{
	/**
	 * Platform Name
	 *
	 * @access  protected
	 * @var string
	 */
	protected $platform = 'memcached';

	/**
	 * Memcached Object
	 *
	 * @var \Memcache|\Memcached
	 */
	protected $memcached;

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
		if ( class_exists( 'Memcached', FALSE ) )
		{
			$this->memcached = new \Memcached();
			$this->memcached->setOption( \Memcached::OPT_BINARY_PROTOCOL, TRUE ); // required for touch() usage
		}
		elseif ( class_exists( 'Memcache', FALSE ) )
		{
			$this->memcached = new \Memcache();
		}
		else
		{
			\O2System::$log->error( 'Session: Failed to create Memcache(d) object; extension not loaded?' );

			return FALSE;
		}

		if ( isset( $this->config[ 'servers' ] ) )
		{
			foreach ( $this->config[ 'servers' ] as $server => $setup )
			{
				isset( $setup[ 'port' ] ) OR $setup[ 'port' ] = 11211;
				isset( $setup[ 'weight' ] ) OR $setup[ 'weight' ] = 1;

				if ( $this->memcached instanceof \Memcache )
				{
					// Third parameter is persistance and defaults to TRUE.
					$this->memcached->addServer(
						$setup[ 'host' ],
						$setup[ 'port' ],
						TRUE,
						$setup[ 'weight' ]
					);
				}
				elseif ( $this->memcached instanceof \Memcached )
				{
					$this->memcached->addServer(
						$setup[ 'host' ],
						$setup[ 'port' ],
						$setup[ 'weight' ]
					);
				}
			}
		}
		else
		{
			if ( $this->memcached instanceof \Memcache )
			{
				// Third parameter is persistance and defaults to TRUE.
				$this->memcached->addServer(
					$this->config[ 'host' ],
					$this->config[ 'port' ],
					TRUE,
					$this->config[ 'weight' ]
				);
			}
			elseif ( $this->memcached instanceof \Memcached )
			{
				$this->memcached->addServer(
					$this->config[ 'host' ],
					$this->config[ 'port' ],
					$this->config[ 'weight' ]
				);
			}
		}

		if ( $this->memcached->getVersion() === FALSE )
		{
			\O2System::Log()->error( 'Session: Memcached connection refused' );

			return FALSE;
		}

		return TRUE;
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
		if ( isset( $this->memcached ) )
		{
			isset( $this->lockKey ) AND $this->memcached->delete( $this->lockKey );

			if ( ! $this->memcached->quit() )
			{
				return FALSE;
			}

			$this->memcached = NULL;

			return TRUE;
		}

		return FALSE;
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
		if ( isset( $this->memcached, $this->lockKey ) )
		{
			$this->memcached->delete( $this->prefixKey . $session_id );

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
		// Not necessary, Memcached takes care of that.
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
		if ( isset( $this->memcached ) AND $this->_lockSession( $session_id ) )
		{
			// Needed by write() to detect session_regenerate_id() calls
			$this->sessionId = $session_id;

			$session_data      = (string) $this->memcached->get( $this->prefixKey . $session_id );
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
		if ( ! isset( $this->memcached ) )
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
			$this->memcached->replace( $this->lockKey, time(), 300 );

			if ( $this->fingerprint !== ( $fingerprint = md5( $session_data ) ) )
			{
				if ( $this->memcached->set( $this->prefixKey . $session_id, $session_data, $this->config[ 'lifetime' ] ) )
				{
					$this->fingerprint = $fingerprint;

					return TRUE;
				}

				return FALSE;
			}

			return $this->memcached->touch( $this->prefixKey . $session_id, $this->config[ 'lifetime' ] );
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
			return $this->memcached->replace( $this->lockKey, time(), 300 );
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->prefixKey . $session_id . ':lock';
		$attempt  = 0;

		do
		{
			if ( $this->memcached->get( $lock_key ) )
			{
				sleep( 1 );
				continue;
			}

			if ( ! $this->memcached->set( $lock_key, time(), 300 ) )
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
		if ( isset( $this->memcached, $this->lockKey ) AND $this->isLocked )
		{
			if ( ! $this->memcached->delete( $this->lockKey ) AND
				$this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND
			)
			{
				\O2System::$log->error( 'Session: Error while trying to free lock for ' . $this->lockKey );

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
		return (bool) ( extension_loaded( 'memcached' ) OR extension_loaded( 'memcache' ) );
	}

	//--------------------------------------------------------------------
}