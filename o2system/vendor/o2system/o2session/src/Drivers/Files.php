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
 * Session Files Driver
 *
 * Based on CodeIgniter Session Files Driver by Andrey Andreev
 *
 * @package       o2session
 * @subpackage    Drivers
 * @category      Sessions
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2session/user-guide/drivers/files
 */
class Files extends Driver implements \SessionHandlerInterface
{
    /**
     * Save path
     *
     * @access  protected
     * @type    string
     */
    protected $_save_path;

    /**
     * File name
     *
     * @access  protected
     * @type    resource
     */
    protected $_file_path;

    /**
     * File new flag
     *
     * @access  protected
     * @type    bool
     */
    protected $_file_new;

    // ------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param   array   $params Configuration parameters
     *
     * @access  public
     */
    public function __construct( &$params )
    {
        parent::__construct( $params );

        if( isset( $this->_config[ 'storage' ][ 'save_path' ] ) )
        {
            $this->_config[ 'storage' ][ 'save_path' ] = rtrim( $this->_config[ 'storage' ][ 'save_path' ], '/\\' );
            ini_set( 'session.save_path', $this->_config[ 'storage' ][ 'save_path' ] );
        }
        else
        {
            $this->_config[ 'storage' ][ 'save_path' ] = rtrim( ini_get( 'session.save_path' ), '/\\' );
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Open
     *
     * Sanitizes the save_path directory.
     *
     * @param   string  $save_path  Path to session files' directory
     * @param   string  $name       Session cookie name
     *
     * @return  bool
     * @throws  \RuntimeException
     */
    public function open( $save_path, $name )
    {
        if( ! is_dir( $save_path ) )
        {
            if( ! mkdir( $save_path, 0775, TRUE ) )
            {
                throw new \RuntimeException( "Session: Configured save path '" . $save_path . "' is not a directory, doesn't exist or cannot be created." );
            }
        }
        elseif( ! is_writable( $save_path ) )
        {
            throw new \RuntimeException( "Session: Configured save path '" . $save_path . "' is not writable by the PHP process." );
        }

        $this->_config[ 'storage' ][ 'save_path' ] = $save_path;
        $this->_file_path = $this->_config[ 'storage' ][ 'save_path' ] . DIRECTORY_SEPARATOR
                            . $name // we'll use the session cookie name as a prefix to avoid collisions
                            . ( $this->_config[ 'storage' ][ 'match_ip' ] ? md5( $_SERVER[ 'REMOTE_ADDR' ] ) : '' );

        $this->_file_path = str_replace( '/', DIRECTORY_SEPARATOR, $this->_file_path );

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Read
     *
     * Reads session data and acquires a lock
     *
     * @param   string  $session_id Session ID
     *
     * @access  public
     * @return  string  Serialized session data
     */
    public function read( $session_id )
    {
        // If there is no session_id then just return FALSE
        // for avoiding apache crashing
        if( empty( $session_id ) ) return FALSE;

        // This might seem weird, but PHP 5.6 introduces session_reset(),
        // which re-reads session data
        if( $this->_handle === NULL )
        {
            // Just using fopen() with 'c+b' mode would be perfect, but it is only
            // available since PHP 5.2.6 and we have to set permissions for new files,
            // so we'd have to hack around this ...
            if( ( $this->_file_new = ! is_file( $this->_file_path . $session_id ) ) === TRUE )
            {
                if( ( $this->_handle = fopen( $this->_file_path . $session_id, 'w+b' ) ) === FALSE )
                {
                    return FALSE;
                }
            }
            elseif( ( $this->_handle = fopen( $this->_file_path . $session_id, 'r+b' ) ) === FALSE )
            {
                return FALSE;
            }

            if( flock( $this->_handle, LOCK_EX ) === FALSE )
            {
                fclose( $this->_handle );
                $this->_handle = NULL;

                return FALSE;
            }

            // Needed by write() to detect session_regenerate_id() calls
            $this->_session_id = $session_id;

            if( $this->_file_new )
            {
                chmod( $this->_file_path . $session_id, 0644 );
                $this->_fingerprint = md5( '' );

                return '';
            }
        }
        else
        {
            rewind( $this->_handle );
        }

        $session_data = '';
        for( $read = 0, $length = filesize( $this->_file_path . $session_id ); $read < $length; $read += strlen( $buffer ) )
        {
            if( ( $buffer = fread( $this->_handle, $length - $read ) ) === FALSE )
            {
                break;
            }

            $session_data .= $buffer;
        }

        $this->_fingerprint = md5( $session_data );

        return $session_data;
    }

    // ------------------------------------------------------------------------

    /**
     * Write
     *
     * Writes (create / update) session data
     *
     * @param   string  $session_id     Session ID
     * @param   string  $session_data   Serialized session data
     *
     * @return    bool
     */
    public function write( $session_id, $session_data )
    {
        // If the two IDs don't match, we have a session_regenerate_id() call
        // and we need to close the old handle and open a new one
        if( $session_id !== $this->_session_id && ( ! $this->close() OR $this->read( $session_id ) === FALSE ) )
        {
            return FALSE;
        }

        if( ! is_resource( $this->_handle ) )
        {
            return FALSE;
        }
        elseif( $this->_fingerprint === md5( $session_data ) )
        {
            return ( $this->_file_new )
                ? TRUE
                : touch( $this->_file_path . $session_id );
        }

        if( ! $this->_file_new )
        {
            ftruncate( $this->_handle, 0 );
            rewind( $this->_handle );
        }

        if( ( $length = strlen( $session_data ) ) > 0 )
        {
            for( $written = 0; $written < $length; $written += $result )
            {
                if( ( $result = fwrite( $this->_handle, substr( $session_data, $written ) ) ) === FALSE )
                {
                    break;
                }
            }

            if( ! is_int( $result ) )
            {
                $this->_fingerprint = md5( substr( $session_data, 0, $written ) );

                return FALSE;
            }
        }

        $this->_fingerprint = md5( $session_data );

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Close
     *
     * Releases locks and closes file descriptor.
     *
     * @access  public
     * @return  bool
     */
    public function close()
    {
        if( is_resource( $this->_handle ) )
        {
            flock( $this->_handle, LOCK_UN );
            fclose( $this->_handle );

            $this->_handle = $this->_file_new = $this->_session_id = NULL;

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
     * @param   string  $session_id Session ID
     *
     * @access  public
     * @return  bool
     */
    public function destroy( $session_id )
    {
        if( $this->close() )
        {
            return is_file( $this->_file_path . $session_id )
                ? ( unlink( $this->_file_path . $session_id ) && $this->_cookie_destroy() )
                : TRUE;
        }
        elseif( $this->_file_path !== NULL )
        {
            clearstatcache();

            return is_file( $this->_file_path . $session_id )
                ? ( unlink( $this->_file_path . $session_id ) && $this->_cookie_destroy() )
                : TRUE;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Garbage Collector
     *
     * Deletes expired sessions
     *
     * @param   int $maxlifetime    Maximum lifetime of sessions
     *
     * @access  public
     * @return  bool
     */
    public function gc( $maxlifetime )
    {
        if( ! is_dir( $this->_config[ 'storage' ][ 'save_path' ] ) OR ( $directory = opendir( $this->_config[ 'storage' ][ 'save_path' ] ) ) === FALSE )
        {
            return FALSE;
        }

        $ts = time() - $maxlifetime;

        $pattern = sprintf(
            '/^%s[0-9a-f]{%d}$/',
            preg_quote( $this->_config[ 'cookie' ][ 'name' ], '/' ),
            ( $this->_config[ 'storage' ][ 'match_ip' ] === TRUE ? 72 : 40 )
        );

        while( ( $file = readdir( $directory ) ) !== FALSE )
        {
            // If the filename doesn't match this pattern, it's either not a session file or is not ours
            if( ! preg_match( $pattern, $file )
                OR ! is_file( $this->_config[ 'storage' ][ 'save_path' ] . DIRECTORY_SEPARATOR . $file )
                OR ( $mtime = filemtime( $this->_config[ 'storage' ][ 'save_path' ] . DIRECTORY_SEPARATOR . $file ) ) === FALSE
                OR $mtime > $ts
            )
            {
                continue;
            }

            unlink( $this->_config[ 'storage' ][ 'save_path' ] . DIRECTORY_SEPARATOR . $file );
        }

        closedir( $directory );

        return TRUE;
    }
}
