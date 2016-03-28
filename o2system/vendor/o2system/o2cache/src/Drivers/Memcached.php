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
 * Memcached Caching Class
 *
 * @package        o2cache
 * @subpackage     Drivers
 * @author         O2System Developer Team
 * @link
 */
class Memcached extends Driver
{
	protected $_config = array(
		'driver' => 'memcached',
		'host'   => '127.0.0.1',
		'port'   => 11211,
		'weight' => 1,
	);

	/**
	 * Driver Name
	 *
	 * @access    public
	 * @var       string
	 */
	public $driver = 'Memcached';

	/**
	 * Holds the memcached object
	 *
	 * @var object
	 */
	protected $_handle;

	// ------------------------------------------------------------------------

	/**
	 * Initialize Cache Driver
	 *
	 * @access  public
	 * @return  bool
	 * @throws  Exception
	 */
	public function initialize()
	{
		if ( $this->is_supported() )
		{
			if ( class_exists( 'Memcached', FALSE ) )
			{
				$this->_handle = new \Memcached();
			}
			elseif ( class_exists( 'Memcache', FALSE ) )
			{
				$this->_handle = new \Memcache();
			}
			else
			{
				return FALSE;
			}

			if ( get_class( $this->_handle ) === 'Memcache' )
			{
				// Third parameter is persistance and defaults to TRUE.
				$this->_handle->addServer(
					$this->_config[ 'host' ],
					$this->_config[ 'port' ],
					TRUE,
					$this->_config[ 'weight' ]
				);
			}
			else
			{
				$this->_handle->addServer(
					$this->_config[ 'host' ],
					$this->_config[ 'port' ],
					$this->_config[ 'weight' ]
				);
			}

			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch from cache
	 *
	 * @param    string $id Cache ID
	 *
	 * @return    mixed    Data on success, FALSE on failure
	 */
	public function get( $id )
	{
		$data = $this->_handle->get( $id );

		return is_array( $data ) ? $data[ 0 ] : $data;
	}

	// ------------------------------------------------------------------------

	/**
	 * Save
	 *
	 * @param    string $id   Cache ID
	 * @param    mixed  $data Data being cached
	 * @param    int    $ttl  Time to live
	 * @param    bool   $raw  Whether to store the raw value
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function save( $id, $data, $ttl = 60, $raw = FALSE )
	{
		if ( $raw !== TRUE )
		{
			$data = array( $data, time(), $ttl );
		}

		if ( get_class( $this->_handle ) === 'Memcached' )
		{
			return $this->_handle->set( $id, $data, $ttl );
		}
		elseif ( get_class( $this->_handle ) === 'Memcache' )
		{
			return $this->_handle->set( $id, $data, 0, $ttl );
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete from Cache
	 *
	 * @param    mixed    key to be deleted.
	 *
	 * @return    bool    true on success, false on failure
	 */
	public function delete( $id )
	{
		return $this->_handle->delete( $id );
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
		return $this->_handle->increment( $id, $offset );
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
		return $this->_handle->decrement( $id, $offset );
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean the Cache
	 *
	 * @return    bool    false on failure/true on success
	 */
	public function destroy()
	{
		return $this->_handle->flush();
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
		return $this->_handle->getStats();
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Cache Metadata
	 *
	 * @param    mixed    key to get cache metadata on
	 *
	 * @return    mixed    FALSE on failure, array on success.
	 */
	public function metadata( $id )
	{
		$stored = $this->_handle->get( $id );

		if ( count( $stored ) !== 3 )
		{
			return FALSE;
		}

		list( $data, $time, $ttl ) = $stored;

		return array(
			'expire' => $time + $ttl,
			'mtime'  => $time,
			'data'   => $data,
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * Is supported
	 *
	 * Returns FALSE if memcached is not supported on the system.
	 * If it is, we setup the memcached object & return TRUE
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function is_supported()
	{
		if ( ! extension_loaded( 'memcached' ) AND ! extension_loaded( 'memcache' ) )
		{
			throw new Exception( 'The Memcached Extension must be loaded to use Memcached Cache.', 103 );
		}

		return TRUE;
	}

}
