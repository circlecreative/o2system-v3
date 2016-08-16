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
 * Class File
 *
 * @package O2System\Session\Handlers
 */
class File extends HandlerInterface implements \SessionHandlerInterface
{
	/**
	 * Platform Name
	 *
	 * @access  protected
	 * @var string
	 */
	protected $platform = 'file';

	/**
	 * File Handle
	 *
	 * @var resource
	 */
	protected $_file;

	/**
	 * File write path
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * File Name
	 *
	 * @var resource
	 */
	protected $_filepath;

	/**
	 * Whether this is a new file.
	 *
	 * @var bool
	 */
	protected $_is_new_file;

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
		if ( isset( $this->config[ 'path' ] ) )
		{
			$this->config[ 'path' ] = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $this->config[ 'path' ] );

			if ( is_dir( $this->config[ 'path' ] ) )
			{
				$this->_path = $this->config[ 'path' ];
			}
			elseif ( defined( 'APPSPATH' ) )
			{
				$this->_path = APPSPATH . 'cache' . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR . $this->config[ 'path' ];
			}
		}
		else
		{
			$this->_path = APPSPATH . 'cache' . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR;
		}

		$this->_path = rtrim( $this->_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		$this->_filepath = $this->_path
			. $name . '-'// we'll use the session cookie name as a prefix to avoid collisions
			. ( $this->config[ 'match' ]->ip ? md5( $_SERVER[ 'REMOTE_ADDR' ] ) : '' );

		if ( $this->isSupported() === FALSE )
		{
			throw new HandlerException( 'Session: Cannot write into directory: ' . $this->_path );
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
		if ( is_resource( $this->_file ) )
		{
			flock( $this->_file, LOCK_UN );
			fclose( $this->_file );

			$this->_file = $this->_is_new_file = $this->sessionId = NULL;

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
		if ( $this->close() )
		{
			return file_exists( $this->_filepath . $session_id )
				? ( unlink( $this->_filepath . $session_id ) && $this->_destroyCookie() )
				: TRUE;
		}
		elseif ( $this->_filepath !== NULL )
		{
			clearstatcache();

			return file_exists( $this->_filepath . $session_id )
				? ( unlink( $this->_filepath . $session_id ) && $this->_destroyCookie() )
				: TRUE;
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
		if ( ! is_dir( $this->_path ) || ( $directory = opendir( $this->_path ) ) === FALSE )
		{
			throw new HandlerException( "Session: Garbage collector couldn't list files under directory '" . $this->_path . "'." );

			return FALSE;
		}

		$ts = time() - $maxlifetime;

		while ( ( $file = readdir( $directory ) ) !== FALSE )
		{
			// If the filename doesn't match this pattern, it's either not a session file or is not ours
			if ( ! preg_match( '[' . $this->config[ 'name' ] . '-]+[0-9-a-f]+', $file )
				|| ! is_file( $this->_path . '/' . $file )
				|| ( $mtime = filemtime( $this->_path . '/' . $file ) ) === FALSE
				|| $mtime > $ts
			)
			{
				continue;
			}

			unlink( $this->_path . '/' . $file );
		}

		closedir( $directory );

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
		// This might seem weird, but PHP 5.6 introduced session_reset(),
		// which re-reads session data
		if ( $this->_file === NULL )
		{

			if ( ( $this->_file = fopen( $this->_filepath . $session_id, 'c+b' ) ) === FALSE )
			{
				throw new HandlerException( "Session: Unable to open file '" . $this->_filepath . $session_id . "'." );

				return FALSE;
			}

			if ( flock( $this->_file, LOCK_EX ) === FALSE )
			{
				fclose( $this->_file );
				$this->_file = NULL;

				throw new HandlerException( "Session: Unable to obtain lock for file '" . $this->_filepath . $session_id . "'." );

				return FALSE;
			}

			// Needed by write() to detect session_regenerate_id() calls
			$this->sessionId = $session_id;

			if ( $this->_is_new_file )
			{
				chmod( $this->_filepath . $session_id, 0600 );
				$this->fingerprint = md5( '' );

				return '';
			}
		}
		else
		{
			rewind( $this->_file );
		}

		$session_data = '';
		for ( $read = 0, $length = filesize( $this->_filepath . $session_id ); $read < $length; $read += strlen( $buffer ) )
		{
			if ( ( $buffer = fread( $this->_file, $length - $read ) ) === FALSE )
			{
				break;
			}

			$session_data .= $buffer;
		}

		$this->fingerprint = md5( $session_data );

		return $session_data;
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
		// If the two IDs don't match, we have a session_regenerate_id() call
		// and we need to close the old handle and open a new one
		if ( $session_id !== $this->sessionId && ( ! $this->close() || $this->read( $session_id ) === FALSE ) )
		{
			return FALSE;
		}

		if ( ! is_resource( $this->_file ) )
		{
			return FALSE;
		}
		elseif ( $this->fingerprint === md5( $session_data ) )
		{
			return ( $this->_is_new_file )
				? TRUE
				: touch( $this->_filepath . $session_id );
		}

		if ( ! $this->_is_new_file )
		{
			ftruncate( $this->_file, 0 );
			rewind( $this->_file );
		}

		if ( ( $length = strlen( $session_data ) ) > 0 )
		{
			for ( $written = 0; $written < $length; $written += $result )
			{
				if ( ( $result = fwrite( $this->_file, substr( $session_data, $written ) ) ) === FALSE )
				{
					break;
				}
			}

			if ( ! is_int( $result ) )
			{
				$this->fingerprint = md5( substr( $session_data, 0, $written ) );

				throw new HandlerException( 'Session: Unable to write data.' );

				return FALSE;
			}
		}

		$this->fingerprint = md5( $session_data );

		return TRUE;
	}

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported()
	{
		return (bool) is_writable( $this->_path );
	}
}