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

namespace O2System\Core\Request\Collections;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\SPL\ArrayAccess;

/**
 * Class Cookies
 *
 * @package O2System\Core\Request\Collections
 */
class Cookies extends ArrayAccess
{
	/**
	 * GET constructor.
	 */
	public function __construct()
	{
		$this->storage =& $_COOKIE;
	}

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @param    mixed $index     Index for item to be fetched from $_COOKIE
	 * @param    bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function get( $offset = NULL, $filter = NULL )
	{
		if ( $cookie = \O2System::$config[ 'cookie' ] )
		{
			$offset = $cookie[ 'prefix' ] . ltrim( $offset, $cookie[ 'prefix' ] );
		}

		if ( isset( $filter ) )
		{
			return $this->offsetFilter($offset, $filter);
		}

		return $this->offsetGet( $offset );
	}

	// --------------------------------------------------------------------

	/**
	 * Set cookie
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param    string|mixed[] $name     Cookie name or an array containing parameters
	 * @param    string         $value    Cookie value
	 * @param    int            $expire   Cookie expiration time in seconds
	 * @param    string         $domain   Cookie domain (e.g.: '.yourdomain.com')
	 * @param    string         $path     Cookie path (default: '/')
	 * @param    string         $prefix   Cookie name prefix
	 * @param    bool           $secure   Whether to only transfer cookies via SSL
	 * @param    bool           $httponly Whether to only makes the cookie accessible via HTTP (no javascript)
	 *
	 * @access  public
	 * @return  void
	 */
	public function set( $name, $value = '', $expire = 0, $domain = '', $path = '/', $prefix = '', $secure = FALSE, $httponly = FALSE )
	{
		$cookie = is_array( $name ) ? $name : func_get_args();

		foreach ( [ 'name', 'value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly' ] as $key => $item )
		{
			if ( isset( $cookie[ $key ] ) )
			{
				if ( empty( $cookie[ $key ] ) AND isset( \O2System::$config[ 'cookie' ][ $item ] ) )
				{
					$cookie[ $item ] = \O2System::$config[ 'cookie' ][ $item ];
				}
				else
				{
					$cookie[ $item ] = $cookie[ $key ];
				}
			}
			elseif ( isset( $cookie[ $item ] ) AND isset( \O2System::$config[ 'cookie' ][ $item ] ) )
			{
				$cookie[ $item ] = \O2System::$config[ 'cookie' ][ $item ];
			}

			unset( $cookie[ $key ] );
		}

		$cookie[ 'name' ]  = $cookie[ 'prefix' ] . ltrim( $cookie[ 'name' ], $cookie[ 'prefix' ] );
		$cookie[ 'value' ] = empty( $cookie[ 'value' ] ) ? NULL : $cookie[ 'value' ];

		if ( $cookie[ 'expire' ] > 0 )
		{
			$cookie[ 'expire' ] = empty( $cookie[ 'expire' ] ) ? time() + \O2System::$config[ 'cookie' ][ 'lifetime' ] : (int) $cookie[ 'expire' ];
		}

		$cookie[ 'domain' ] = '.' . ( empty( $cookie[ 'domain' ] ) ? parse_domain()->domain : ltrim( $cookie[ 'domain' ], '.' ) );

		setcookie(
			$cookie[ 'name' ],
			$cookie[ 'value' ],
			$cookie[ 'expire' ],
			$cookie[ 'path' ],
			$cookie[ 'domain' ],
			$cookie[ 'secure' ],
			$cookie[ 'httponly' ]
		);
	}

	// ------------------------------------------------------------------------
}