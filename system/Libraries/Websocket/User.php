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

namespace O2System\Libraries\Websocket;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * Websocket Client Class
 *
 * @author  Sann-Remy Chea
 *
 * @package O2System\Libraries\Websocket
 */
class User
{
	protected $_id;
	protected $_socket;

	/**
	 * Handshake
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $_is_handshake = FALSE;
	protected $_pid;
	protected $_is_connected;

	public function __construct( $id, $socket )
	{
		$this->_id           = $id;
		$this->_socket       = $socket;
		$this->_is_handshake = FALSE;
		$this->_pid          = NULL;
		$this->_is_connected = TRUE;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function getSocket()
	{
		return $this->_socket;
	}

	public function isHandshake()
	{
		return $this->_is_handshake;
	}

	public function getPid()
	{
		return $this->_pid;
	}

	public function isConnected()
	{
		return $this->_is_connected;
	}

	public function setId( $id )
	{
		$this->_id = $id;
	}

	public function setSocket( $socket )
	{
		$this->_socket = $socket;
	}

	public function doHandshake()
	{
		$this->_is_handshake = TRUE;
	}

	public function setPid( $pid )
	{
		$this->_pid = $pid;
	}

	public function setIsConnected( $is_connected )
	{
		$this->_is_connected = $is_connected;
	}

	public function __toString()
	{
		return "( User: " . $this->_id . " )";
	}

}