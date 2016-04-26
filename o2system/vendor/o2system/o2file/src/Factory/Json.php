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
 * @license        http://opensource.org/licenses/MIT   MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\File\Factory;

// ------------------------------------------------------------------------

use O2System\File;

/**
 * JSON Class
 *
 * JSON file creator from array
 *
 * @package        O2System
 * @subpackage     Libraries/Data
 * @category       System Libraries
 * @author         Steeven Andrian Salim
 * @link
 */
class Json
{
    /**
     * Read File
     *
     * @access public
     *
     * @param string $filename Filename with realpath
     * @param string $return   Type of return array or object
     *
     * @return mixed
     */
    public function read( $filename, $return = 'array' )
    {
        if( is_file( $filename ) )
        {
            $content = File::read( $filename );

            if( ! empty( $content ) )
            {
                $result = json_decode( $content );
            }

            if( json_last_error() === JSON_ERROR_NONE )
            {
                if( $return === 'array' )
                {
                    return (array)$result;
                }
                elseif( $return === 'object' )
                {
                    if( is_array( $result ) )
                    {
                        return (object)$result;
                    }
                    elseif( is_object( $result ) )
                    {
                        return $result;
                    }
                }
            }
        }

        return NULL;
    }

    // ------------------------------------------------------------------------

    /**
     * Write File
     *
     * @access  public
     *
     * @param string $filename Filename
     * @param array  $data     List of data
     */
    public function write( $filename, $data )
    {
        if( ! empty( $data ) )
        {
            if( is_object( $data ) OR is_array( $data ) )
            {
                $content = self::_factory( json_encode( $data ) );

                File::write( $filename, $content );
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * File writer factory
     *
     * @access  protected
     *
     * @param string $json Json encode string
     *
     * @return string
     */
    protected static function _factory( $json )
    {
        $result = '';
        $level = 0;
        $in_quotes = FALSE;
        $in_escape = FALSE;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ )
        {
            $char = $json[ $i ];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL )
            {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if( $in_escape )
            {
                $in_escape = FALSE;
            }
            else if( $char === '"' )
            {
                $in_quotes = ! $in_quotes;
            }
            else if( ! $in_quotes )
            {
                switch( $char )
                {
                    case '}':
                    case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;

                    case '{':
                    case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ":
                    case "\t":
                    case "\n":
                    case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            }
            else if( $char === '\\' )
            {
                $in_escape = TRUE;
            }
            if( $new_line_level !== NULL )
            {
                $result .= "\n" . str_repeat( "\t", $new_line_level );
            }
            $result .= $char . $post;
        }

        return $result;
    }
}