<?php
/**
 * O2Parser
 *
 * An open source template engines driver for PHP 5.4+
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
 * @package     O2System
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2parser/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2parser.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Parser\Drivers;

// ------------------------------------------------------------------------

use O2System\Parser\Interfaces\Driver;


class Shortcode extends Driver
{
    private $_codes = array();

    /**
     * Setup Engine
     *
     * @param   $settings   Template Config
     *
     * @access  public
     * @return  Parser Engine Adapter Object
     */
    public function setup( $settings = array() )
    {
        return $this;
    }

    /**
     * Retrieve the shortcode regular expression for searching.
     *
     * The regular expression combines the shortcode tags in the regular expression
     * in a regex class.
     *
     * The regular expression contains 6 different sub matches to help with parsing.
     *
     * 1 - An extra [ to allow for escaping shortcodes with double [[]]
     * 2 - The shortcode name
     * 3 - The shortcode argument list
     * 4 - The self closing /
     * 5 - The content of a shortcode when it wraps some content.
     * 6 - An extra ] to allow for escaping shortcodes with double [[]]
     *
     * @since 2.5.0
     *
     * @uses  $shortcode_tags
     *
     * @return string The shortcode search regular expression
     */
    protected function _set_regex()
    {
        $tag_codes = array_keys( $this->_codes );
        $tag_regex = join( '|', array_map( 'preg_quote', $tag_codes ) );

        // WARNING! Do not change this regex
        return '\\['                                // Opening bracket
               . '(\\[?)'                                  // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
               . "(shortcode:([a-zA-Z0-9-_\.]+))"          // 2: Shortcode name
               . '(?![\\w-])'                              //    Not followed by word character or hyphen
               . '('                                       // 3: Unroll the loop: Inside the opening shortcode tag
               . '[^\\]\\/]*'                              //    Not a closing bracket or forward slash
               . '(?:' . '\\/(?!\\])'                      //    A forward slash not followed by a closing bracket
               . '[^\\]\\/]*'                              //    Not a closing bracket or forward slash
               . ')*?' . ')' . '(?:' . '(\\/)'             // 4: Self closing tag ...
               . '\\]'                                     //    ... and closing bracket
               . '|' . '\\]'                               //    Closing bracket
               . '(?:' . '('                               // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
               . '[^\\[]*+'                                //    Not an opening bracket
               . '(?:' . '\\[(?!\\/\\2\\])'                //    An opening bracket not followed by the closing shortcode tag
               . '[^\\[]*+'                                //    Not an opening bracket
               . ')*+' . ')' . '\\[\\/\\2\\]'              //    Closing shortcode tag
               . ')?' . ')' . '(\\]?)';                    // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }


    /**
     * Parse String
     *
     * @param   string   String Source Code
     * @param   array    Array of variables data to be parsed
     *
     * @access  public
     * @return  string  Parse Output Result
     */
    public function parse_string( $string, $vars = array() )
    {
        if( FALSE === strpos( $string, '[shortcode:' ) )
        {
            return FALSE;
        }

        preg_match_all( '/' . $this->_set_regex() . '/s', $string, $matches, PREG_SET_ORDER );
        if( empty( $matches ) )
        {
            return FALSE;
        }

        foreach( $matches as $shortcode )
        {
            $this->_calls[ $shortcode[ 0 ] ] = array(
                'tag' => trim( $shortcode[ 3 ] ), 'params' => $this->_parse_params( $shortcode[ 4 ] )
            );
        }

        return TRUE;
    }

    /**
     * Register Plugin
     *
     * Registers a plugin for use in a Twig template.
     *
     * @access  public
     */
    public function register_plugin()
    {
        list( $tag, $function ) = func_get_args();

        if( is_callable( $function ) )
        {
            $this->_codes[ $tag ] = $function;
        }
    }
}