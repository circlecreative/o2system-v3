<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, PT. Lingkar Kreasi (Circle Creative).
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
 * @copyright   Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2system-codeigniter.html
 * @since       Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Libraries\Socialmedia\Interfaces;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Libraries\Socialmedia\Metadata\Connection;

abstract class DriverInterface extends \O2System\Glob\Interfaces\DriverInterface
{
	/**
	 * Social Media Driver URL List
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_url = [
		'base'  => NULL,
		'api'   => NULL,
		'share' => NULL,
	];

	/**
	 * Social Media Driver Connection
	 *
	 * @access  protected
	 * @type    Connection
	 */
	protected $_connection;

	/**
	 * Social Media Driver API Client
	 *
	 * @access  protected
	 * @type    Mixed
	 */
	protected $_api;

	/**
	 * Get Share Link
	 *
	 * @param string|null $link
	 *
	 * @access  public
	 * @return  string|null
	 */
	abstract public function getShareLink( $link = NULL );

	/**
	 * Get Authorize Link
	 *
	 * @param string $callback
	 *
	 * @access  public
	 * @return  string|bool
	 */
	abstract public function getAuthorizeLink( $callback );

	/**
	 * Get Authorize Token
	 *
	 * @access  public
	 * @return  array|bool
	 */
	abstract public function getAuthorizeToken();

	/**
	 * Set Connection
	 *
	 * @param Connection|array $connection
	 *
	 * @return bool
	 */
	abstract public function setConnection( $connection );

	public function isConnected()
	{
		if ( $this->_connection instanceof Connection )
		{
			return (bool) $this->_connection->offsetExists( 'token' );
		}

		return FALSE;
	}

	/**
	 * Get Connection
	 *
	 * @return Connection|bool
	 */
	public function getConnection()
	{
		if ( $this->isConnected() === FALSE )
		{
			$this->__buildConnection();
		}

		return $this->_connection;
	}

	/**
	 * Connection Builder
	 *
	 * @return bool
	 */
	abstract protected function __buildConnection();

	/**
	 * Magic Method API Client and Driver Caller
	 *
	 * @param       $method
	 * @param array $args
	 *
	 * @return bool|mixed
	 */
	public function __call( $method, array $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( [ $this, $method ], $args );
		}
		elseif ( isset( $this->_api ) )
		{
			if ( method_exists( $this->_api, $method ) )
			{
				return call_user_func_array( [ $this->_api, $method ], $args );
			}
		}

		return FALSE;
	}
}