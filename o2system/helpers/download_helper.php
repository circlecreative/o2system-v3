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
 * Download Helpers
 *
 * @package        O2System
 * @subpackage     helpers
 * @category       Helpers
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/helpers/download.html
 */
// ------------------------------------------------------------------------

if( ! function_exists( 'force_download' ) )
{
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
    function force_download( $filename = '', $data = FALSE, $options = [
        'partial'     => TRUE,
        'speed.limit' => FALSE,
        'set.mime'    => FALSE
    ] )
    {
        if( $filename === '' OR $data === '' )
        {
            return FALSE;
        }
        elseif( $data === NULL )
        {
            if( @is_file( $filename ) && ( $filesize = @filesize( $filename ) ) !== FALSE )
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

        if( $options[ 'set.mime' ] === TRUE )
        {
            if( count( $x ) === 1 OR $extension === '' )
            {
                /* If we're going to detect the MIME type,
                 * we'll need a file extension.
                 */
                return FALSE;
            }

            // Load the mime types
            $mimes =& get_mimes();

            // Only change the default MIME if we can find one
            if( isset( $mimes[ $extension ] ) )
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
        if( count( $x ) !== 1 && isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) && preg_match( '/Android\s(1|2\.[01])/', $_SERVER[ 'HTTP_USER_AGENT' ] ) )
        {
            $x[ count( $x ) - 1 ] = strtoupper( $extension );
            $filename = implode( '.', $x );
        }

        if( $data === NULL && ( $fp = @fopen( $filepath, 'rb' ) ) === FALSE )
        {
            return FALSE;
        }

        // Clean output buffer
        if( ob_get_level() !== 0 && @ob_end_clean() === FALSE )
        {
            @ob_clean();
        }

        // Check for partial download
        if( isset( $_SERVER[ 'HTTP_RANGE' ] ) && $options[ 'partial' ] === TRUE )
        {
            list ( $a, $range ) = explode( "=", $_SERVER[ 'HTTP_RANGE' ] );
            list ( $fbyte, $lbyte ) = explode( "-", $range );

            if( ! $lbyte )
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
        if( $data === FALSE )
        {
            $file = fopen( $filename, 'r' );
            if( ! $file )
            {
                return FALSE;
            }
        }

        // Cut data for partial download
        if( isset( $_SERVER[ 'HTTP_RANGE' ] ) && $options[ 'partial' ] === TRUE )
        {
            if( $data === FALSE )
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
        if( $options[ 'speed.limit' ] > 0 OR $data === FALSE )
        {
            if( $data === FALSE )
            {
                $chunk_size = $options[ 'speed.limit' ] > 0 ? $options[ 'speed.limit' ] * 1024 : 512 * 1024;
                while( ! feof( $file ) and ( connection_status() == 0 ) )
                {
                    $buffer = fread( $file, $chunk_size );
                    echo $buffer;
                    flush();
                    if( $options[ 'speed.limit' ] > 0 )
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
                while( $index < $filesize and ( connection_status() == 0 ) )
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
}