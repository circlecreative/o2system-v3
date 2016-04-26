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
 * Ini Class
 *
 * Ini file creator from array
 *
 * @package        O2System
 * @subpackage     Libraries/File
 * @category       System Libraries
 * @author         Circle Creative Dev Team
 * @link           http://o2system.center/wiki/libraries/data/drivers/ini.html
 */
class Ini
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
    public static function read( $filename, $return = 'array' )
    {
        if( is_file( $filename ) )
        {
            $result = parse_ini_file( $filename, TRUE );

            if( ! empty( $result ) )
            {
                if( $return === 'array' )
                {
                    return $result;
                }
                elseif( $return === 'object' )
                {
                    return (object)$result;
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
    public static function write( $filename, array $data, $has_sections = FALSE )
    {
        if( count( $data ) > 0 )
        {
            $content = self::_factory( $data, $has_sections );

            File::write( $filename, $content );
        }
    }

    // ------------------------------------------------------------------------

    /**
     * File writer factory
     *
     * @access  protected
     *
     * @param   array  $data         List of data
     * @param   bool   $has_sections Ini file has section
     * @param   string $content      Content file
     *
     * @return string
     */
    protected static function _factory( $data, $has_sections, $content = NULL )
    {
        foreach( $data as $key => $value )
        {
            if( is_array( $value ) )
            {
                if( $has_sections )
                {
                    $content .= "[$key]\n";
                    static::_factory( $value, FALSE, $content );
                }
                else
                {
                    foreach( $value as $value_k => $value_v )
                    {
                        if( is_int( $value_k ) )
                        {
                            $content .= $key . "[] = $value_v\n";
                        }
                        else
                        {
                            $content .= $key . "[$value_k] = $value_v\n";
                        }
                    }
                }
            }
            else
            {
                $content .= "$key = $value\n";
            }
        }

        return $content;
    }
}