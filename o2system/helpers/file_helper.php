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
 * File Helpers
 *
 * @package        O2System
 * @subpackage     helpers
 * @category       Helpers
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/helpers/file.html
 */
// ------------------------------------------------------------------------

if ( ! function_exists( 'file_read' ) )
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
	function file_read( $file )
	{
		return \O2System\File::read( $file );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'file_write' ) )
{
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
	function file_write( $path, $data, $mode = 'wb' )
	{
		return \O2System\File::write( $path, $data, $mode );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'file_show' ) )
{
	/**
	 * Show File
	 *
	 * @param   string $filename Filename with path
	 *
	 * @return  void
	 */
	function file_show( $filename )
	{
		\O2System\File::show( $filename );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'file_info' ) )
{
	/**
	 * Info File
	 *
	 * @param   string $filename Filename with path
	 *
	 * @return  object
	 */
	function file_info( $filename )
	{
		return \O2System\File::info( $filename );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'file_mime' ) )
{
	/**
	 * Info File
	 *
	 * @param   string $filename Filename with path
	 *
	 * @return  object
	 */
	function file_mime( $filename )
	{
		return \O2System\File::mime( $filename );
	}
}