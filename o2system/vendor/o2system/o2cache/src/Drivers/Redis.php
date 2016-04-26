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
 * Redis Caching Class
 *
 * @package        o2cache
 * @subpackage     Drivers
 * @author         Anton Lindqvist <anton@qvister.se>
 * @link
 */
class Redis extends Driver
{
	protected $_config = array(
		'driver'   => 'redis',
		'socket'   => NULL,
		'host'     => '127.0.0.1',
		'port'     => 6379,
		'password' => NULL,
		'timeout'  => 5,
	);

	/**
	 * An internal cache for storing keys of serialized values.
	 *
	 * @var    array
	 */
	protected $_serialized = array();

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
			$handle = new \Redis();

			try
			{
				if ( $this->socket_type === 'unix' )
				{
					$success = $handle->connect( $this->_config[ 'socket' ] );
				}
				else // tcp socket
				{
					$success = $handle->connect( $this->_config[ 'host' ], $this->_config[ 'port' ], $this->_config[ 'timeout' ] );
				}

				if ( $success )
				{
					$this->_handle = $handle;

					if ( isset( $this->_config[ 'password' ] ) )
					{
						$this->_handle->auth( $this->_config[ 'password' ] );
					}

					// Initialize the index of serialized values.
					$serialized = $this->_handle->sMembers( '_o2cache_serialized_indexes' );

					if ( ! empty( $serialized ) )
					{
						$this->_serialized = array_flip( $serialized );
					}

					return TRUE;
				}
			}
			catch ( \RedisException $e )
			{
				throw new Exception( 'Redis connection refused (' . $e->getMessage() . ')' );

				return FALSE;
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get cache
	 *
	 * @param    string    Cache ID
	 *
	 * @return    mixed
	 */
	public function get( $id )
	{
		$value = $this->_handle->get( $id );

		if ( $value !== FALSE && isset( $this->_serialized[ $id ] ) )
		{
			return unserialize( $value );
		}

		return $value;
	}

	// ------------------------------------------------------------------------

	/**
	 * Save cache
	 *
	 * @param    string $id   Cache ID
	 * @param    mixed  $data Data to save
	 * @param    int    $ttl  Time to live in seconds
	 * @param    bool   $raw  Whether to store the raw value (unused)
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function save( $id, $data, $ttl = 60, $raw = FALSE )
	{
		if ( is_array( $data ) OR is_object( $data ) )
		{
			if ( ! $this->_handle->sAdd( '_o2cache_serialized_indexes', $id ) )
			{
				return FALSE;
			}

			isset( $this->_serialized[ $id ] ) OR $this->_serialized[ $id ] = TRUE;
			$data = serialize( $data );
		}
		elseif ( isset( $this->_serialized[ $id ] ) )
		{
			$this->_serialized[ $id ] = NULL;
			$this->_handle->sRemove( '_o2cache_serialized_indexes', $id );
		}

		if ( $ttl === FALSE )
		{
			$this->_handle->set( $id, $data );

			return $this->_handle->persist( $id );
		}
		else
		{
			return $this->_handle->setex( $id, $ttl, $data );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete from cache
	 *
	 * @param    string    Cache key
	 *
	 * @return    bool
	 */
	public function delete( $key )
	{
		if ( $this->_handle->delete( $key ) !== 1 )
		{
			return FALSE;
		}

		if ( isset( $this->_serialized[ $key ] ) )
		{
			$this->_serialized[ $key ] = NULL;
			$this->_handle->sRemove( '_o2cache_serialized_indexes', $key );
		}

		return TRUE;
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
		return $this->_handle->incr( $id, $offset );
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
		return $this->_handle->decr( $id, $offset );
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean cache
	 *
	 * @return    bool
	 * @see        Redis::flushDB()
	 */
	public function destroy()
	{
		return $this->_handle->flushDB();
	}

	// ------------------------------------------------------------------------

	/**
	 * Get cache driver info
	 *
	 * @param    string    Not supported in Redis.
	 *                     Only included in order to offer a
	 *                     consistent cache API.
	 *
	 * @return    array
	 * @see        Redis::info()
	 */
	public function info( $type = NULL )
	{
		return $this->_handle->info();
	}

	// ------------------------------------------------------------------------

	/**
	 * Get cache metadata
	 *
	 * @param    string    Cache key
	 *
	 * @return    array
	 */
	public function metadata( $key )
	{
		$value = $this->get( $key );

		if ( $value )
		{
			return array(
				'expire' => time() + $this->_handle->ttl( $key ),
				'data'   => $value,
			);
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Check if Redis driver is supported
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function is_supported()
	{
		if ( ! extension_loaded( 'redis' ) )
		{
			throw new Exception( 'The Redis extension must be loaded to use Redis cache.', 103 );
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Class destructor
	 *
	 * Closes the connection to Redis if present.
	 *
	 * @return    void
	 */
	public function __destruct()
	{
		if ( $this->_handle instanceof \Redis )
		{
			$this->_handle->close();
		}
	}

}
