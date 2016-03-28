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
 * @package     O2System
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2014, .
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\Template\Factory;

// ------------------------------------------------------------------------

/**
 * Wiki Generator Class
 *
 * @package     O2System
 * @subpackage  Libraries
 * @category    Library
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2015
 * @link        http://o2system.center/wiki
 */
class Wiki
{
    protected $_lang;
    protected $_folder;
    protected $_pages;
    protected $_files;
    protected $_base_url;

    /**
     * class constructor
     */
    public function __construct()
    {
        $controller =& get_instance();
        $this->_lang = $active['lang'];
    }

    // ------------------------------------------------------------------------
    /**
     * @param $folder
     *
     * @return $this
     */
    public function set_folder( $folder )
    {
        $this->_folder = $folder;

        return $this;
    }

    // ------------------------------------------------------------------------
    /**
     * @param $url
     *
     * @return $this
     */
    public function set_base_url( $url )
    {
        $this->_base_url = $url;

        return $this;
    }

    // ------------------------------------------------------------------------
    /**
     * load
     */
    public function load()
    {
        if( is_dir( $this->_folder . $this->_lang ) )
        {
            $this->_folder = $this->_folder . $this->_lang;
        }

        $directory = new \RecursiveDirectoryIterator( $this->_folder );
        $iterator = new \RecursiveIteratorIterator( $directory );
        $results = new \RegexIterator( $iterator, '/^.+\.md/i', \RecursiveRegexIterator::GET_MATCH );

        $this->_pages = array();
        foreach( $results as $file )
        {
            foreach( $results as $files )
            {
                foreach( $files as $file )
                {
                    $wiki = new \stdClass();
                    $wiki->realpath = realpath( $file );

                    $wiki->filepath = str_replace( realpath( $this->_folder ), '', $wiki->realpath );
                    $wiki->filepath = str_replace( DS, '/', substr( $wiki->filepath, 1 ) );

                    $filename = pathinfo( $wiki->realpath, PATHINFO_FILENAME );
                    $x_filename = explode( '.', $filename );

                    $wiki->num = '';
                    foreach( $x_filename as $_filename )
                    {
                        if( is_numeric( $_filename ) )
                        {
                            $wiki->num .= empty( $wiki->num ) ? $_filename : '.' . $_filename;
                        }
                        elseif( $_filename !== 'md' )
                        {
                            $wiki->title = $_filename;
                        }
                    }

                    $wiki->title = str_replace( '_', '-', $wiki->title );
                    $x_title = explode( '-', $wiki->title );
                    $x_title = array_map( 'ucfirst', $x_title );
                    $wiki->title = implode( ' ', $x_title );
                    $wiki->num_title = $wiki->num . '. ' . $wiki->title;

                    // add to files
                    $this->_files[ $wiki->num_title ] = $wiki->realpath;
                }
            }
        }
    }

    // ------------------------------------------------------------------------
    /**
     * @return null|string
     */
    public function tocify()
    {
        if( ! empty( $this->_files ) )
        {
            ksort( $this->_files, SORT_STRING );

            foreach( $this->_files as $file )
            {
                $output = file_get_contents( $file );
                $markdown = new \Parsedown();
                $contents[ ] = $markdown->text( $output );
            }

            return implode( PHP_EOL, $contents );
        }

        return NULL;
    }
}