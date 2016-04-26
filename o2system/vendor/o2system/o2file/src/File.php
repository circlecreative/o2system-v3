<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
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
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System;

use O2System\Glob\ArrayObject;

/**
 * Data Class Library
 *
 * @package        O2System
 * @subpackage     Libraries/Data
 * @category       System Libraries
 * @author         Steeven Andrian Salim
 * @link
 */
class File
{
	/**
	 * Read File
	 *
	 * Opens the file specified in the path and returns it as a string.
	 *
	 * @param    string $file Path to file
	 *
	 * @return    string    File contents
	 */
	public static function read( $file )
	{
		if ( is_file( $file ) )
		{
			return @file_get_contents( $file );
		}

		return NULL;
	}

	// ------------------------------------------------------------------------

	/**
	 * Write File
	 *
	 * Writes data to the file specified in the path.
	 * Creates a new file if non-existent.
	 *
	 * @param    string $path File path
	 * @param    string $data Data to write
	 * @param    string $mode fopen() mode (default: 'wb')
	 *
	 * @return    bool
	 */
	public static function write( $path, $data, $mode = 'wb' )
	{
		if ( ! $fp = @fopen( $path, $mode ) )
		{
			return FALSE;
		}

		flock( $fp, LOCK_EX );

		for ( $result = $written = 0, $length = strlen( $data ); $written < $length; $written += $result )
		{
			if ( ( $result = fwrite( $fp, substr( $data, $written ) ) ) === FALSE )
			{
				break;
			}
		}

		flock( $fp, LOCK_UN );
		fclose( $fp );

		return is_int( $result );
	}

	// ------------------------------------------------------------------------

	/**
	 * Show File
	 *
	 * @param   string $filename Filename with path
	 *
	 * @return  mixed
	 */
	public static function show( $filename )
	{
		if ( $mime = self::mime( $filename ) )
		{
			$mime = is_array( $mime ) ? $mime[ 0 ] : $mime;
		}
		elseif ( is_file( $filename ) )
		{
			$mime = 'application/octet-stream';
		}

		$size = filesize( $filename );
		$name = pathinfo( $filename, PATHINFO_BASENAME );

		// Common headers
		$expires = 604800; // (60*60*24*7)
		header( 'Expires:' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );

		header( 'Accept-Ranges: bytes', TRUE );
		header( "Cache-control: private", TRUE );
		header( 'Pragma: private', TRUE );

		header( 'Content-Type: ' . $mime );
		header( 'Content-Disposition: inline; filename="' . $name . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );

		$ETag = '"' . md5( $name ) . '"';

		if ( ! empty( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] )
			&& $_SERVER[ 'HTTP_IF_NONE_MATCH' ] == $ETag
		)
		{
			header( 'HTTP/1.1 304 Not Modified' );
			header( 'Content-Length: ' . $size );
			exit;
		}

		$expires = 604800; // (60*60*24*7)
		header( 'ETag: ' . $ETag );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', time() ) . ' GMT' );
		header( 'Expires:' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );

		// Open file
		// @readfile($file_path);
		$file = @fopen( $filename, "rb" );
		if ( $file )
		{
			while ( ! feof( $file ) )
			{
				print( fread( $file, 1024 * 8 ) );
				flush();
				if ( connection_status() != 0 )
				{
					@fclose( $file );
					die();
				}
			}
			@fclose( $file );
		}
	}

	/**
	 * Tests for file writability
	 *
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute. is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @link    https://bugs.php.net/bug.php?id=54709
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_really_writable( $file )
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if ( DIRECTORY_SEPARATOR === '/' AND
			( strpos( phpversion(), '5.4' ) !== FALSE OR ! ini_get( 'safe_mode' ) )
		)
		{
			return is_writable( $file );
		}

		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if ( is_dir( $file ) )
		{
			$file = rtrim( $file, '/' ) . '/' . md5( mt_rand() );
			if ( ( $fp = @fopen( $file, 'ab' ) ) === FALSE )
			{
				return FALSE;
			}

			fclose( $fp );
			@chmod( $file, 0777 );
			@unlink( $file );

			return TRUE;
		}
		elseif ( ! is_file( $file ) OR ( $fp = @fopen( $file, 'ab' ) ) === FALSE )
		{
			return FALSE;
		}

		fclose( $fp );

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get File Info
	 *
	 * Given a file and path, returns the name, path, size, date modified
	 * Second parameter allows you to explicitly declare what information you want returned
	 * Options are: name, server_path, size, date, readable, writable, executable, fileperms
	 * Returns FALSE if the file cannot be found.
	 *
	 * @param    string    path to file
	 * @param    mixed     array or comma separated string of information returned
	 *
	 * @return    array
	 */
	public static function info( $filename )
	{
		if ( file_exists( $filename ) )
		{
			$fileinfo = new ArrayObject( pathinfo( $filename ) );
			$fileinfo->mime = self::mime( $fileinfo );
			$fileinfo->realpath = realpath( $filename );
			$fileinfo->filesize = @filesize( $filename );
			$fileinfo->owner = @fileowner( $filename );
			$fileinfo->group = @filegroup( $filename );
			$fileinfo->last_modified = @date( 'r', filemtime( $filename ) );
			$fileinfo->last_access = @date( 'r', fileatime( $filename ) );
			$fileinfo->chmod = @substr( sprintf( '%o', fileperms( $filename ) ), -4 );
			$fileinfo->permissions = @fileperms( $filename );
			$fileinfo->readable = is_readable( $filename );
			$fileinfo->writable = self::is_really_writable( $filename );
			$fileinfo->executable = is_executable( $filename );

			return $fileinfo;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Force Download
	 *
	 * Generates headers that force a download to happen
	 *
	 * @param    string    Filename tobe downloaded
	 * @param    mixed     Data tobe downloaded
	 * @param    array     Options Array
	 *
	 * @return  mixed
	 */
	public static function download( $filename = '', $data = FALSE, $options = [
		'partial'     => TRUE,
		'speed.limit' => FALSE,
		'set.mime'    => FALSE,
	] )
	{
		if ( $filename === '' OR $data === '' )
		{
			return FALSE;
		}
		elseif ( $data === NULL )
		{
			if ( @is_file( $filename ) && ( $filesize = @filesize( $filename ) ) !== FALSE )
			{
				$filepath = $filename;
				$filename = explode( '/', str_replace( DIRECTORY_SEPARATOR, '/', $filename ) );
				$filename = end( $filename );
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			$filesize = strlen( $data );
		}

		// Set the default MIME type to send
		$mime = 'application/octet-stream';

		$x = explode( '.', $filename );
		$extension = end( $x );

		if ( $options[ 'set.mime' ] === TRUE )
		{
			if ( count( $x ) === 1 OR $extension === '' )
			{
				/* If we're going to detect the MIME type,
				 * we'll need a file extension.
				 */
				return FALSE;
			}

			// Load the mime types
			$mimes =& self::mimes();

			// Only change the default MIME if we can find one
			if ( isset( $mimes[ $extension ] ) )
			{
				$mime = is_array( $mimes[ $extension ] ) ? $mimes[ $extension ][ 0 ] : $mimes[ $extension ];
			}
		}

		/* It was reported that browsers on Android 2.1 (and possibly older as well)
		 * need to have the filename extension upper-cased in order to be able to
		 * download it.
		 *
		 * Reference: http://digiblog.de/2011/04/19/android-and-the-download-file-headers/
		 */
		if ( count( $x ) !== 1 && isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) && preg_match( '/Android\s(1|2\.[01])/', $_SERVER[ 'HTTP_USER_AGENT' ] ) )
		{
			$x[ count( $x ) - 1 ] = strtoupper( $extension );
			$filename = implode( '.', $x );
		}

		if ( $data === NULL && ( $fp = @fopen( $filepath, 'rb' ) ) === FALSE )
		{
			return FALSE;
		}

		// Clean output buffer
		if ( ob_get_level() !== 0 && @ob_end_clean() === FALSE )
		{
			@ob_clean();
		}

		// Check for partial download
		if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) && $options[ 'partial' ] === TRUE )
		{
			list ( $a, $range ) = explode( "=", $_SERVER[ 'HTTP_RANGE' ] );
			list ( $fbyte, $lbyte ) = explode( "-", $range );

			if ( ! $lbyte )
			{
				$lbyte = $filesize - 1;
			}

			$new_length = $lbyte - $fbyte;

			header( "HTTP/1.1 206 Partial Content", TRUE );
			header( "Content-Length: $new_length", TRUE );
			header( "Content-Range: bytes $fbyte-$lbyte/$filesize", TRUE );
		}
		else
		{
			header( "Content-Length: " . $filesize );
		}

		// Common headers
		header( 'Content-Type: ' . $mime, TRUE );
		header( 'Content-Disposition: attachment; filename="' . pathinfo( $filename, PATHINFO_BASENAME ) . '"', TRUE );

		$expires = 604800; // (60*60*24*7)
		header( 'Expires:' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );

		header( 'Accept-Ranges: bytes', TRUE );
		header( "Cache-control: private", TRUE );
		header( 'Pragma: private', TRUE );

		// Open file
		if ( $data === FALSE )
		{
			$file = fopen( $filename, 'r' );
			if ( ! $file )
			{
				return FALSE;
			}
		}

		// Cut data for partial download
		if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) && $options[ 'partial' ] === TRUE )
		{
			if ( $data === FALSE )
			{
				fseek( $file, $range );
			}
			else
			{
				$data = substr( $data, $range );
			}
		}

		// Disable script time limit
		@set_time_limit( 0 );

		// Check for speed limit or file optimize
		if ( $options[ 'speed.limit' ] > 0 OR $data === FALSE )
		{
			if ( $data === FALSE )
			{
				$chunk_size = $options[ 'speed.limit' ] > 0 ? $options[ 'speed.limit' ] * 1024 : 512 * 1024;
				while ( ! feof( $file ) and ( connection_status() == 0 ) )
				{
					$buffer = fread( $file, $chunk_size );
					echo $buffer;
					flush();
					if ( $options[ 'speed.limit' ] > 0 )
					{
						sleep( 1 );
					}
				}
				fclose( $file );
			}
			else
			{
				$index = 0;
				$options[ 'speed.limit' ] *= 1024; //convert to kb
				while ( $index < $filesize and ( connection_status() == 0 ) )
				{
					$left = $filesize - $index;
					$buffer_size = min( $left, $options[ 'speed.limit' ] );
					$buffer = substr( $data, $index, $buffer_size );
					$index += $buffer_size;
					echo $buffer;
					flush();
					sleep( 1 );
				}
			}
		}
		else
		{
			echo $data;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns the MIME types array from config/mimes.php
	 *
	 * @return    array
	 */
	public static function mimes()
	{
		static $_mimes;

		if ( empty( $_mimes ) )
		{
			if ( file_exists( __DIR__ . '/Config/Mimes.php' ) )
			{
				$_mimes = require( __DIR__ . '/Config/Mimes.php' );
			}
		}

		return $_mimes;
	}

	/**
	 * Returns the MIME types array from config/mimes.php
	 *
	 * @return  string|bool
	 */
	public static function mime( $filename )
	{
		$mimes = self::mimes();

		$ext = $filename;

		if ( is_file( $filename ) )
		{
			$ext = pathinfo( $filename, PATHINFO_EXTENSION );
		}

		$ext = strtolower( $ext );

		if ( isset( $mimes[ $ext ] ) )
		{
			return $mimes[ $ext ];
		}

		return FALSE;
	}

	/**
	 * File Permissions
	 *
	 * @param        $filename
	 * @param string $type
	 *
	 * @return bool|string
	 */
	public static function permissions( $filename, $type = 'octal' )
	{
		if ( is_file( $filename ) )
		{
			$perms = fileperms( $filename );

			switch ( $type )
			{
				default:
				case 'octal':
					return substr( sprintf( '%o', $perms ), -3 );
					break;

				case 'symbolic':
					if ( ( $perms & 0xC000 ) === 0xC000 )
					{
						$symbolic = 's'; // Socket
					}
					elseif ( ( $perms & 0xA000 ) === 0xA000 )
					{
						$symbolic = 'l'; // Symbolic Link
					}
					elseif ( ( $perms & 0x8000 ) === 0x8000 )
					{
						$symbolic = '-'; // Regular
					}
					elseif ( ( $perms & 0x6000 ) === 0x6000 )
					{
						$symbolic = 'b'; // Block special
					}
					elseif ( ( $perms & 0x4000 ) === 0x4000 )
					{
						$symbolic = 'd'; // Directory
					}
					elseif ( ( $perms & 0x2000 ) === 0x2000 )
					{
						$symbolic = 'c'; // Character special
					}
					elseif ( ( $perms & 0x1000 ) === 0x1000 )
					{
						$symbolic = 'p'; // FIFO pipe
					}
					else
					{
						$symbolic = 'u'; // Unknown
					}

					// Owner
					$symbolic .= ( ( $perms & 0x0100 ) ? 'r' : '-' )
						. ( ( $perms & 0x0080 ) ? 'w' : '-' )
						. ( ( $perms & 0x0040 ) ? ( ( $perms & 0x0800 ) ? 's' : 'x' ) : ( ( $perms & 0x0800 ) ? 'S' : '-' ) );

					// Group
					$symbolic .= ( ( $perms & 0x0020 ) ? 'r' : '-' )
						. ( ( $perms & 0x0010 ) ? 'w' : '-' )
						. ( ( $perms & 0x0008 ) ? ( ( $perms & 0x0400 ) ? 's' : 'x' ) : ( ( $perms & 0x0400 ) ? 'S' : '-' ) );

					// World
					$symbolic .= ( ( $perms & 0x0004 ) ? 'r' : '-' )
						. ( ( $perms & 0x0002 ) ? 'w' : '-' )
						. ( ( $perms & 0x0001 ) ? ( ( $perms & 0x0200 ) ? 't' : 'x' ) : ( ( $perms & 0x0200 ) ? 'T' : '-' ) );

					return $symbolic;
					break;
			}
		}

		return FALSE;
	}
}