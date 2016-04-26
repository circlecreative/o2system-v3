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

namespace O2System\File\Factory;

// ------------------------------------------------------------------------

use O2System\File;

/**
 * Zip Compression Class
 *
 * This class is based on a library I found at Zend:
 * http://www.zend.com/codex.php?id=696&single=1
 *
 * The original library is a little rough around the edges so I
 * refactored it and added several additional methods -- Rick Ellis
 *
 * @package        O2System
 * @subpackage     Libraries
 * @category       Encryption
 * @author         EllisLab Dev Team & Circle Creative Dev Team
 * @link           http:/o2system.center/wiki/#zip
 */
class Zip
{
    public static function read( $file )
    {
        if( ! extension_loaded( 'zip' ) )
        {
            return FALSE;
        }

        if( is_file( $file ) )
        {
            $zip = zip_open( $file );

            if( $zip )
            {
                while( $zip_entry = zip_read( $zip ) )
                {
                    $contents[ ] = zip_entry_name( $zip_entry );
                }

                zip_close( $zip );
            }

            return $contents;
        }
    }

    // ------------------------------------------------------------------------

    public static function write( $file, $source )
    {
        if( ! extension_loaded( 'zip' ) )
        {
            return FALSE;
        }

        if( is_file( $file ) )
        {
            unlink( $file );
        }

        $zip = new \ZipArchive();

        if( ! $zip->open( $file, ZIPARCHIVE::CREATE ) )
        {
            return FALSE;
        }

        $source = str_replace( '\\', '/', realpath( $source ) );

        if( is_dir( $source ) === TRUE )
        {

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $source ),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $arr = explode( "/", $source );
            $maindir = $arr[ count( $arr ) - 1 ];

            $source = "";
            for( $i = 0; $i < count( $arr ) - 1; $i++ )
            {
                $source .= '/' . $arr[ $i ];
            }

            $source = substr( $source, 1 );

            $zip->addEmptyDir( $maindir );

            foreach( $files as $file )
            {
                $file = str_replace( '\\', '/', $file );

                // Ignore "." and ".." folders
                if( in_array( substr( $file, strrpos( $file, '/' ) + 1 ), array( '.', '..' ) ) )
                {
                    continue;
                }

                $file = realpath( $file );

                if( is_dir( $file ) === TRUE )
                {
                    $zip->addEmptyDir( str_replace( $source . '/', '', $file . '/' ) );
                }
                else if( is_file( $file ) === TRUE )
                {
                    $zip->addFromString( str_replace( $source . '/', '', $file ), file_get_contents( $file ) );
                }
            }
        }
        else if( is_file( $source ) === TRUE )
        {
            $zip->addFromString( basename( $source ), File::read( $source ) );
        }

        return $zip->close();
    }

    // ------------------------------------------------------------------------

    public static function extract( $file, $destination_path )
    {
        if( ! extension_loaded( 'zip' ) )
        {
            return FALSE;
        }

        if( is_file( $file ) )
        {
            $zip = new \ZipArchive;
            $contents = $zip->open( $file );

            if( $contents === TRUE )
            {
                if( is_dir( $destination_path ) )
                {
                    $zip->extractTo( $destination_path );
                    $zip->close();
                }
            }
        }
    }
}