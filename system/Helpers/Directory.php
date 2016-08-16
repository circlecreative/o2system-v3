<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
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
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * Directory Helpers
 *
 * @package        O2System
 * @subpackage     helpers
 * @category       Helpers
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/helpers/directory.html
 */
// ------------------------------------------------------------------------

if ( ! function_exists( 'directory_size' ) )
{
	/**
	 * Directory Size
	 *
	 * @param      $path
	 * @param bool $raw_size
	 *
	 * @return string
	 */
	function directory_size( $path, $raw_size = TRUE )
	{
		$path = rtrim( $path, DIRECTORY_SEPARATOR );

		if ( is_file( $path ) )
		{
			return filesize( $path );
		}

		$size = 0;

		foreach ( glob( $path . "/*" ) as $filename )
		{
			$size += directory_size( $filename, TRUE );
		}

		if ( $raw_size === TRUE )
		{
			return $size;
		}
		else
		{
			$size_units = [ 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

			if ( ! isset( $size_string ) )
			{
				$size_string = '%01.2f %s';
			}

			foreach ( $size_units as $size_unit )
			{
				if ( $size < 1024 )
				{
					break;
				}
				if ( $size_unit != end( $size_units ) )
				{
					$size /= 1024;
				}
			}

			if ( $size_unit == $size_units[ 0 ] )
			{
				$size_string = '%01d %s';
			} // Bytes aren't normally fractional

			return sprintf( $size_string, $size, $size_unit );
		}
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'directory_scan' ) )
{
	/**
	 * Directori Scanner
	 *
	 * @param        $dir
	 * @param string $ext
	 * @param bool   $recursive
	 *
	 * @return array
	 */
	function directory_scan( $dir, $ext = '*', $recursive = TRUE )
	{
		$result = [ ];

		$scan_directory = scandir( $dir );

		foreach ( $scan_directory as $key => $value )
		{
			if ( ! in_array( $value, [ ".", ".." ] ) )
			{
				if ( is_file( $dir . DIRECTORY_SEPARATOR . $value ) )
				{
					if ( $ext == '*' )
					{
						$result[] = str_replace( '//', '/', $dir . DIRECTORY_SEPARATOR . $value );
					}
					else
					{
						if ( preg_match( '/(' . $ext . ')$/', $value ) )
						{
							$result[] = str_replace( '//', '/', $dir . DIRECTORY_SEPARATOR . $value );
						}
					}
				}
				elseif ( is_dir( $dir . DIRECTORY_SEPARATOR . $value ) )
				{
					if ( $recursive === TRUE )
					{
						$result[ $value ] = directory_scan( $dir . DIRECTORY_SEPARATOR . $value, $ext, $recursive );
					}
					else
					{
						$result = array_merge( $result, directory_scan( $dir . DIRECTORY_SEPARATOR . $value, $ext, $recursive ) );
					}
				}

			}
		}

		return $result;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'directory_files' ) )
{
	/**
	 * File Directory
	 *
	 * @param        $dir
	 * @param string $ext
	 *
	 * @return array
	 */
	function directory_files( $dir, $ext = '*' )
	{
		$files  = directory_map( $dir );
		$result = [ ];

		if ( ! empty( $files ) )
		{
			foreach ( $files as $key => $file )
			{
				if ( is_string( $file ) )
				{
					if ( $ext == '*' )
					{
						$_file = str_replace( '//', '/', $dir . DIRECTORY_SEPARATOR . $file );
					}
					else
					{
						if ( preg_match( '/(' . $ext . ')$/', $file ) )
						{
							$_file = str_replace( '//', '/', $dir . DIRECTORY_SEPARATOR . $file );
						}
					}

					$result[] = file_info( $_file );
				}
			}
		}

		return $result;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'directory_tree' ) )
{
	/**
	 * Directory tree
	 *
	 * @param      $dir
	 * @param int  $dir_depth
	 * @param bool $hidden
	 *
	 * @return array|bool
	 */
	function directory_tree( $dir, $dir_depth = 0, $hidden = FALSE )
	{
		if ( $fp = @opendir( $dir ) )
		{
			$dir_tree  = [ ];
			$new_depth = $dir_depth - 1;
			$dir       = rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

			while ( FALSE !== ( $dir = readdir( $fp ) ) )
			{
				// Remove '.', '..', and hidden files [optional]
				if ( ! trim( $dir, '.' ) OR ( $hidden == FALSE && $dir[ 0 ] == '.' ) )
				{
					continue;
				}

				if ( ( $dir_depth < 1 OR $new_depth > 0 ) && @is_dir( $dir . $dir ) )
				{
					$info         = directory_info( $dir . $dir );
					$info->branch = directory_tree( $dir . $dir . DIRECTORY_SEPARATOR, $new_depth, $hidden );

					$dir_tree[] = $info;
				}
			}

			closedir( $fp );

			return $dir_tree;
		}

		return FALSE;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'directory_info' ) )
{
	/**
	 * Directory Info
	 *
	 * @param $dir
	 *
	 * @return stdClass
	 */
	function directory_info( $dir )
	{
		$iterator            = new DirectoryIterator( $dir );
		$info                = new \O2System\Core\SPL\ArrayObject();
		$info->name          = pathinfo( $dir, PATHINFO_BASENAME );
		$info->path          = str_replace( [ '/' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR ], '/', $iterator->getPath() );
		$info->realpath      = realpath( $info->path );
		$info->parent_path   = str_replace(
			[
				'/' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR,
			], '/', pathinfo( $dir, PATHINFO_DIRNAME ) );
		$info->parent_name   = pathinfo( $info->parent_path, PATHINFO_BASENAME );
		$info->raw_size      = directory_size( $info->path, TRUE );
		$info->size          = byte_format( $info->raw_size );
		$info->owner         = $iterator->getOwner();
		$info->group         = $iterator->getGroup();
		$info->chmod         = substr( sprintf( '%o', $iterator->getPerms() ), -4 );
		$info->permissions   = $iterator->getPerms();
		$info->writable      = $iterator->isWritable();
		$info->last_modified = date( 'r', $iterator->getMTime() );
		$info->last_access   = date( 'r', $iterator->getATime() );

		return $info;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'directory_remove' ) )
{
	/**
	 * Directory Remove
	 *
	 * @param $dir
	 *
	 * @return bool
	 */
	function directory_remove( $dir )
	{
		$dir = realpath( $dir );

		if ( is_dir( $dir ) )
		{
			$iterator = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
			$files    = new RecursiveIteratorIterator( $iterator, RecursiveIteratorIterator::CHILD_FIRST );
			foreach ( $files as $file )
			{
				if ( $file->isDir() )
				{
					directory_remove( $file->getRealPath() );
				}
				elseif ( $file->isFile() )
				{
					unlink( $file->getRealPath() );
				}
			}

			rmdir( $dir );

			return TRUE;
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'directory_empty' ) )
{
	/**
	 * Delete Files
	 *
	 * Deletes all files contained in the supplied directory path.
	 * Files must be writable or owned by the system in order to be deleted.
	 * If the second parameter is set to TRUE, any directories contained
	 * within the supplied base directory will be nuked as well.
	 *
	 * @param    string $path    File path
	 * @param    bool   $del_dir Whether to delete any directories found in the path
	 * @param    bool   $htdocs  Whether to skip deleting .htaccess and index page files
	 * @param    int    $_level  Current directory depth level (default: 0; internal use only)
	 *
	 * @return    bool
	 */
	function directory_empty( $path, $del_dir = FALSE, $htdocs = FALSE, $_level = 0 )
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
					directory_empty( $path . DIRECTORY_SEPARATOR . $filename, $del_dir, $htdocs, $_level + 1 );
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
}

// ------------------------------------------------------------------------


if ( ! function_exists( 'get_dir_file_info' ) )
{
	/**
	 * Get Directory File Information
	 *
	 * Reads the specified directory and builds an array containing the filenames,
	 * filesize, dates, and permissions
	 *
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @param    string    path to source
	 * @param    bool      Look only at the top level directory specified?
	 * @param    bool      internal variable to determine recursion status - do not use in calls
	 *
	 * @return    array
	 */
	function get_dir_file_info( $source_dir, $top_level_only = TRUE, $_recursion = FALSE )
	{
		static $_filedata = [ ];
		$relative_path = $source_dir;

		if ( $fp = @opendir( $source_dir ) )
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ( $_recursion === FALSE )
			{
				$_filedata  = [ ];
				$source_dir = rtrim( realpath( $source_dir ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
			}

			// Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
			while ( FALSE !== ( $file = readdir( $fp ) ) )
			{
				if ( is_dir( $source_dir . $file ) && $file[ 0 ] !== '.' && $top_level_only === FALSE )
				{
					get_dir_file_info( $source_dir . $file . DIRECTORY_SEPARATOR, $top_level_only, TRUE );
				}
				elseif ( $file[ 0 ] !== '.' )
				{
					$_filedata[ $file ]                    = get_file_info( $source_dir . $file );
					$_filedata[ $file ][ 'relative_path' ] = $relative_path;
				}
			}

			closedir( $fp );

			return $_filedata;
		}

		return FALSE;
	}
}

// --------------------------------------------------------------------

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license      http://opensource.org/licenses/MIT MIT License
 * @link         http://codeigniter.com
 * @since        Version 1.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Directory Helpers
 *
 * @package        CodeIgniter
 * @subpackage     Helpers
 * @category       Helpers
 * @author         EllisLab Dev Team
 * @link           http://codeigniter.com/user_guide/helpers/directory_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists( 'directory_map' ) )
{
	/**
	 * Create a Directory Map
	 *
	 * Reads the specified directory and builds an array
	 * representation of it. Sub-folders contained with the
	 * directory will be mapped as well.
	 *
	 * @param    string $source_dir      Path to source
	 * @param    int    $directory_depth Depth of directories to traverse
	 *                                   (0 = fully recursive, 1 = current dir, etc)
	 * @param    bool   $hidden          Whether to show hidden files
	 *
	 * @return    array
	 */
	function directory_map( $source_dir, $directory_depth = 0, $hidden = FALSE )
	{
		if ( $fp = @opendir( $source_dir ) )
		{
			$filedata   = [ ];
			$new_depth  = $directory_depth - 1;
			$source_dir = rtrim( $source_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

			while ( FALSE !== ( $file = readdir( $fp ) ) )
			{
				// Remove '.', '..', and hidden files [optional]
				if ( $file === '.' OR $file === '..' OR ( $hidden === FALSE && $file[ 0 ] === '.' ) )
				{
					continue;
				}

				is_dir( $source_dir . $file ) && $file .= DIRECTORY_SEPARATOR;

				if ( ( $directory_depth < 1 OR $new_depth > 0 ) && is_dir( $source_dir . $file ) )
				{
					$filedata[ rtrim( $file, DIRECTORY_SEPARATOR ) ] = directory_map( $source_dir . $file, $new_depth, $hidden );
				}
				else
				{
					$filedata[] = $file;
				}
			}

			closedir( $fp );

			return $filedata;
		}

		return FALSE;
	}
}