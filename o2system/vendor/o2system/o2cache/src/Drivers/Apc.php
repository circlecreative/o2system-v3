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
 * APC Caching Class
 *
 * @package        o2cache
 * @subpackage     Drivers
 * @author         O2System Developer Team
 * @link
 */
class Apc extends Driver
{
	/**
	 * Driver Name
	 *
	 * @access    public
	 * @var       string
	 */
	public $driver = 'APC';


	// ------------------------------------------------------------------------

	/**
	 * Initialize Cache Driver
	 *
	 * @access    public
	 * @return    bool
	 */
	public function initialize()
	{
		return $this->isSupported();
	}

	// ------------------------------------------------------------------------

	/**
	 * Get
	 *
	 * Look for a value in the cache. If it exists, return the data
	 * if not, return FALSE
	 *
	 * @param    string
	 *
	 * @return    mixed    value that is stored/FALSE on failure
	 */
	public function get( $id )
	{
		$success = FALSE;
		$data    = apc_fetch( $id, $success );

		if ( $success === TRUE )
		{
			return is_array( $data )
				? unserialize( $data[ 0 ] )
				: $data;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Save
	 *
	 * @param    string $id   Cache ID
	 * @param    mixed  $data Data to store
	 * @param    int    $ttol Length of time (in seconds) to cache the data
	 * @param    bool   $raw  Whether to store the raw value
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function save( $id, $data, $ttl = 60, $raw = FALSE )
	{
		$ttl = (int) $ttl;

		return apc_store(
			$id,
			( $raw === TRUE ? $data : [ serialize( $data ), time(), $ttl ] ),
			$ttl
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete from Cache
	 *
	 * @param    mixed    unique identifier of the item in the cache
	 *
	 * @return    bool    true on success/false on failure
	 */
	public function delete( $id )
	{
		return apc_delete( $id );
	}

	// ------------------------------------------------------------------------

	/**
	 * Increment a raw value
	 *
	 * @param    string $id     Cache ID
	 * @param    int    $offset Step/value to add
	 *
	 * @return    mixed    New value on success or FALSE on failure
	 */
	public function increment( $id, $offset = 1 )
	{
		return apc_inc( $id, $offset );
	}

	// ------------------------------------------------------------------------

	/**
	 * Decrement a raw value
	 *
	 * @param    string $id     Cache ID
	 * @param    int    $offset Step/value to reduce by
	 *
	 * @return    mixed    New value on success or FALSE on failure
	 */
	public function decrement( $id, $offset = 1 )
	{
		return apc_dec( $id, $offset );
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean the cache
	 *
	 * @return    bool    false on failure/true on success
	 */
	public function destroy()
	{
		return apc_clear_cache( 'user' );
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Info
	 *
	 * @param    string    user/filehits
	 *
	 * @return    mixed    array on success, false on failure
	 */
	public function info( $type = NULL )
	{
		return apc_cache_info( $type );
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Cache Metadata
	 *
	 * @param    mixed    key to get cache metadata on
	 *
	 * @return    mixed    array on success/false on failure
	 */
	public function metadata( $id )
	{
		$success = FALSE;
		$stored  = apc_fetch( $id, $success );

		if ( $success === FALSE OR count( $stored ) !== 3 )
		{
			return FALSE;
		}

		list( $data, $time, $ttl ) = $stored;

		return [
			'expire' => $time + $ttl,
			'mtime'  => $time,
			'data'   => unserialize( $data ),
		];
	}

	// ------------------------------------------------------------------------

	/**
	 * is_supported()
	 *
	 * Check to see if APC is available on this system, bail if it isn't.
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function isSupported()
	{
		if ( ! extension_loaded( 'apc' ) OR ! ini_get( 'apc.enabled' ) )
		{
			throw new Exception( 'The APC PHP extension must be loaded to use APC Cache.' );
		}

		return TRUE;
	}
}
