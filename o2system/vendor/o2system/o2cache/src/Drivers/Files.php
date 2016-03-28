<?php
/**
 * O2Cache
 *
 * An open source PHP Cache Management for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * @license        http://circle-creative.com/products/o2cache/license.html
 * @license        http://opensource.org/licenses/MIT   MIT License
 * @link           http://circle-creative.com/products/o2cache.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Cache\Drivers;

// ------------------------------------------------------------------------

use O2System\Cache\Exception;
use O2System\Cache\Interfaces\Driver;

/**
 * File Caching Class
 *
 * @package        o2cache
 * @subpackage     Drivers
 * @author         O2System Developer Team
 * @link
 */
class Files extends Driver
{
	/**
	 * Driver Name
	 *
	 * @access    public
	 * @var       string
	 */
	public $driver = 'Files';

	/**
	 * Driver Cache Path
	 *
	 * @access    public
	 * @var       string
	 */
	public $path = NULL;

	// ------------------------------------------------------------------------

	/**
	 * Initialize Cache Driver
	 *
	 * @access  public
	 * @return  bool
	 * @throws  Exception
	 */
	public function initialize()
	{
		if ( $this->is_supported() === FALSE )
		{
			throw new Exception( 'The File cache path is not writeable.', 103 );
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch from cache
	 *
	 * @param    string $id Cache ID
	 *
	 * @return    mixed    Data on success, FALSE on failure
	 */
	public function get( $id )
	{
		$data = $this->_get( $id );

		return is_array( $data ) ? $data[ 'data' ] : FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Save into cache
	 *
	 * @param    string $id   Cache ID
	 * @param    mixed  $data Data to store
	 * @param    int    $ttl  Time to live in seconds
	 * @param    bool   $raw  Whether to store the raw value (unused)
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function save( $id, $data, $ttl = 60, $raw = FALSE )
	{
		$ttl = isset( $this->_config[ 'ttl' ] ) ? $this->_config[ 'ttl' ] : $ttl;

		$contents = array(
			'time' => time(),
			'data' => $data,
		);

		if ( is_int( $ttl ) AND $ttl > 0 )
		{
			$contents[ 'ttl' ] = $ttl;
		}

		$contents = serialize( $contents );

		if ( ! $fp = @fopen( $this->path . $id, 'wb' ) )
		{
			return FALSE;
		}

		flock( $fp, LOCK_EX );

		for ( $result = $written = 0, $length = strlen( $contents ); $written < $length; $written += $result )
		{
			if ( ( $result = fwrite( $fp, substr( $contents, $written ) ) ) === FALSE )
			{
				break;
			}
		}

		flock( $fp, LOCK_UN );
		fclose( $fp );

		$result = is_int( $result );

		if ( $result )
		{
			chmod( $this->path . $id, 0640 );

			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete from Cache
	 *
	 * @param    mixed    unique identifier of item in cache
	 *
	 * @return    bool    true on success/false on failure
	 */
	public function delete( $id )
	{
		return is_file( $this->path . $id ) ? unlink( $this->path . $id ) : FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Increment a raw value
	 *
	 * @param    string $id     Cache ID
	 * @param    int    $offset Step/value to add
	 *
	 * @return    New value on success, FALSE on failure
	 */
	public function increment( $id, $offset = 1 )
	{
		$data = $this->_get( $id );

		if ( $data === FALSE )
		{
			$data = array( 'data' => 0, 'ttl' => 60 );
		}
		elseif ( ! is_int( $data[ 'data' ] ) )
		{
			return FALSE;
		}

		$new_value = $data[ 'data' ] + $offset;

		return $this->save( $id, $new_value, $data[ 'ttl' ] )
			? $new_value
			: FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Decrement a raw value
	 *
	 * @param    string $id     Cache ID
	 * @param    int    $offset Step/value to reduce by
	 *
	 * @return    New value on success, FALSE on failure
	 */
	public function decrement( $id, $offset = 1 )
	{
		$data = $this->_get( $id );

		if ( $data === FALSE )
		{
			$data = array( 'data' => 0, 'ttl' => 60 );
		}
		elseif ( ! is_int( $data[ 'data' ] ) )
		{
			return FALSE;
		}

		$new_value = $data[ 'data' ] - $offset;

		return $this->save( $id, $new_value, $data[ 'ttl' ] )
			? $new_value
			: FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean the Cache
	 *
	 * @return    bool    false on failure/true on success
	 */
	public function destroy()
	{
		return $this->_clean_files( $this->path, FALSE, TRUE );
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean the Cache files
	 *
	 * @param   string $path    File path
	 * @param   bool   $del_dir Whether to delete any directories found in the path
	 * @param   bool   $htdocs  Whether to skip deleting .htaccess and index page files
	 * @param   int    $_level  Current directory depth level (default: 0; internal use only)
	 *
	 * @access    protected
	 * @return    bool    false on failure/true on success
	 */
	protected function _clean_files( $path, $del_dir = FALSE, $htdocs = FALSE, $_level = 0 )
	{
		// Trim the trailing slash
		$path = rtrim( $path, '/\\' );

		if ( ! $current_dir = @opendir( $path ) )
		{
			return FALSE;
		}

		while ( FALSE !== ( $filename = @readdir( $current_dir ) ) )
		{
			if ( $filename !== '.' && $filename !== '..' )
			{
				if ( is_dir( $path . DIRECTORY_SEPARATOR . $filename ) && $filename[ 0 ] !== '.' )
				{
					$this->_clean_files( $path . DIRECTORY_SEPARATOR . $filename, $del_dir, $htdocs, $_level + 1 );
				}
				elseif ( $htdocs !== TRUE OR ! preg_match( '/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename ) )
				{
					@unlink( $path . DIRECTORY_SEPARATOR . $filename );
				}
			}
		}

		closedir( $current_dir );

		return ( $del_dir === TRUE && $_level > 0 )
			? @rmdir( $path )
			: TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Info
	 *
	 * Not supported by file-based caching
	 *
	 * @param    string    user/filehits
	 *
	 * @return    mixed    FALSE
	 */
	public function info( $type = NULL )
	{
		return $this->_dir_file_info( $this->path );
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Directory File Information
	 *
	 * Reads the specified directory and builds an array containing the filenames,
	 * filesize, dates, and permissions
	 *
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @param   string  path to source
	 * @param   bool    Look only at the top level directory specified?
	 * @param   bool    internal variable to determine recursion status - do not use in calls
	 *
	 * @return  array
	 */
	protected function _dir_file_info( $source_dir, $top_level_only = TRUE, $_recursion = FALSE )
	{
		static $_filedata = array();
		$relative_path = $source_dir;

		if ( $fp = @opendir( $source_dir ) )
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ( $_recursion === FALSE )
			{
				$_filedata = array();
				$source_dir = rtrim( realpath( $source_dir ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
			}

			// Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
			while ( FALSE !== ( $file = readdir( $fp ) ) )
			{
				if ( is_dir( $source_dir . $file ) && $file[ 0 ] !== '.' && $top_level_only === FALSE )
				{
					$this->_dir_file_info( $source_dir . $file . DIRECTORY_SEPARATOR, $top_level_only, TRUE );
				}
				elseif ( $file[ 0 ] !== '.' )
				{
					$_filedata[ $file ] = $this->_dir_file_info( $source_dir . $file );
					$_filedata[ $file ][ 'relative_path' ] = $relative_path;
				}
			}

			closedir( $fp );

			return $_filedata;
		}

		return FALSE;
	}

	/**
	 * Get Cache Metadata
	 *
	 * @param    mixed    key to get cache metadata on
	 *
	 * @return    mixed    FALSE on failure, array on success.
	 */
	public function metadata( $id )
	{
		if ( ! is_file( $this->path . $id ) )
		{
			return FALSE;
		}

		$data = unserialize( file_get_contents( $this->path . $id ) );

		if ( is_array( $data ) )
		{
			$mtime = filemtime( $this->path . $id );

			if ( ! isset( $data[ 'ttl' ] ) )
			{
				return FALSE;
			}

			return array(
				'expire' => $mtime + $data[ 'ttl' ],
				'mtime'  => $mtime,
			);
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Is supported
	 *
	 * In the file driver, check to see that the cache directory is indeed writable
	 *
	 * @return    bool
	 */
	public function is_supported()
	{
		// Try to create path
		if ( ! is_dir( $this->_config[ 'path' ] ) )
		{
			if ( ! mkdir( $this->_config[ 'path' ], 0775, TRUE ) )
			{
				$this->setError( 'CACHE_FILEPATHUNWRITEABLE', 2201, [ $this->_config[ 'path' ] ] );

				return FALSE;
			}
		}

		// Check if the path is writable
		if ( ! is_writable( $this->_config[ 'path' ] ) )
		{
			$this->setError( 'CACHE_FILEPATHUNWRITEABLE', 2201, [ $this->_config[ 'path' ] ] );

			return FALSE;
		}

		$this->path = $this->_config[ 'path' ];

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get all data
	 *
	 * Internal method to get all the relevant data about a cache item
	 *
	 * @param    string $id Cache ID
	 *
	 * @return    mixed    Data array on success or FALSE on failure
	 */
	protected function _get( $id )
	{
		if ( ! is_file( $this->path . $id ) )
		{
			return FALSE;
		}

		$data = unserialize( file_get_contents( $this->path . $id ) );

		if ( array_key_exists( 'ttl', $data ) )
		{
			if ( $data[ 'ttl' ] > 0 && time() > $data[ 'time' ] + $data[ 'ttl' ] )
			{
				unlink( $this->path . $id );

				return FALSE;
			}
		}

		return $data;
	}

}
