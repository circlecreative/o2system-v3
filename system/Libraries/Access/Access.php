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

namespace O2System\Libraries;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core;

/**
 * Access Control Driver Based Library
 *
 * This class is used to control and manage user access
 *
 * @package        O2System
 * @subpackage     system/core
 * @category       Core Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/access.html
 */
class Access extends Core\Library
{
	use Core\Library\Traits\Handlers;

	/**
	 * Encryption
	 *
	 * @type Encryption
	 */
	public $encryption;

	/**
	 * User Model
	 *
	 * @access  public
	 *
	 * @type    UsersModel
	 */
	public $model = NULL;

	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __construct( array $config = [ ] )
	{
		\O2System::$load->helpers( [ 'string', 'cookie' ] );

		$config = \O2System::$config->load( 'access', TRUE );

		if ( $config[ 'password' ][ 'salt' ] === FALSE )
		{
			$config[ 'password' ][ 'rehash' ] = FALSE;
		}

		$this->encryption = new Encryption();

		$this->__locateHandlers();

		parent::__construct( $config );
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Model Handler
	 *
	 * @param \O2System\Model $model
	 *
	 * @access  public
	 */
	public function setUserModel( Core\Model $model )
	{
		$this->model = $model;
	}

	// ------------------------------------------------------------------------

	/**
	 * Hash Password
	 *
	 * Create a hash of given password based on configuration
	 *
	 * @param string      $password
	 * @param string|null $salt
	 *
	 * @access  public
	 * @return string
	 */
	public function hashPassword( $password, $salt = NULL )
	{
		switch ( strtolower( @$this->config[ 'password' ][ 'hash' ] ) )
		{
			default:
			case 'crypt':
				return crypt( $password, $salt );
				break;

			case 'md5':
				return md5( $password . $salt );
				break;

			case 'sha1':
				return sha1( $password . $salt );
				break;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Create Salt
	 *
	 * Generate salt for hashing password by microtime
	 *
	 * @access  public
	 * @return  string|null
	 */
	public function saltPassword()
	{
		if ( $this->config[ 'hash_salt' ] === TRUE )
		{
			return substr( md5( microtime() ), 1, 8 );
		}

		return NULL;
	}

	public function getSsoKey()
	{
		$sso_key = substr( md5( microtime() ), 1, 10 );

		if ( \O2System::$cache->get( $sso_key ) )
		{
			return $this->getSsoKey();
		}

		return $sso_key;
	}

	public function restrict( $uri = 'login' )
	{
		if ( $this->user->isLogin() === FALSE )
		{
			redirect( $uri );
		}
	}
}