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

namespace O2System\Cache\Interfaces;

// ------------------------------------------------------------------------
use O2System\Glob\Interfaces\DriverInterface;

/**
 * Driver Interface Class
 *
 * @package         o2cache
 * @subpackage      Interfaces
 * @author          O2System Developer Team
 */
abstract class Driver extends DriverInterface
{
    /**
     * Driver Name
     *
     * @access    public
     * @var       string
     */
    public $driver;

    /**
     * Driver Resource Handler
     *
     * @access    public
     * @var       object
     */
    protected $_handle;

    /**
     * Driver Class Constructor
     *
     * @param    array $config
     *
     * @access   public
     */
    public function __construct( $config = array() )
    {
        foreach( $config as $key => $value )
        {
            $this->{$key} = $value;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Initialize Cache Driver
     *
     * @access  public
     * @return  bool
     * @throws  Exception
     */
    abstract public function initialize();

    // ------------------------------------------------------------------------

    /**
     * Get
     *
     * Look for a value in the cache. If it exists, return the data
     * if not, return FALSE
     *
     * @param    string $id
     *
     * @return    mixed    value matching $id or FALSE on failure
     */
    abstract public function get( $id );

    // ------------------------------------------------------------------------

    /**
     * Cache Save
     *
     * @param    string $id   Cache ID
     * @param    mixed  $data Data to store
     * @param    bool   $raw  Whether to store the raw value
     * @param    int    $ttl  Cache TTL (in seconds)
     *
     * @return    bool    TRUE on success, FALSE on failure
     */
    abstract public function save( $id, $data, $raw = FALSE, $ttl = NULL );

    // ------------------------------------------------------------------------

    /**
     * Delete from Cache
     *
     * @param    string $id Cache ID
     *
     * @return    bool    TRUE on success, FALSE on failure
     */
    abstract public function delete( $id );

    // ------------------------------------------------------------------------

    /**
     * Increment a raw value
     *
     * @param    string $id     Cache ID
     * @param    int    $offset Step/value to add
     *
     * @return    mixed    New value on success or FALSE on failure
     */
    abstract public function increment( $id, $offset = 1 );

    // ------------------------------------------------------------------------

    /**
     * Decrement a raw value
     *
     * @param    string $id     Cache ID
     * @param    int    $offset Step/value to reduce by
     *
     * @return    mixed    New value on success or FALSE on failure
     */
    abstract public function decrement( $id, $offset = 1 );

    // ------------------------------------------------------------------------

    /**
     * Destroy the cache
     *
     * @return    bool    TRUE on success, FALSE on failure
     */
    abstract public function destroy();

    // ------------------------------------------------------------------------

    /**
     * Cache Info
     *
     * @param    string $type = 'user'    user/filehits
     *
     * @return    mixed    array containing cache info on success OR FALSE on failure
     */
    abstract public function info( $type = 'user' );

    // ------------------------------------------------------------------------

    /**
     * Get Cache Metadata
     *
     * @param    string $id key to get cache metadata on
     *
     * @return    mixed    cache item metadata
     */
    abstract public function metadata( $id );

    // ------------------------------------------------------------------------

    /**
     * is_supported()
     *
     * Check to see if the driver is supported.
     *
     * @return  bool
     * @throws  Exception
     */
    abstract public function is_supported();
}