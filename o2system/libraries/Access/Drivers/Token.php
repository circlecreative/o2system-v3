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

namespace O2System\Libraries\Access\Drivers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\Interfaces\DriverInterface;
use O2System\Libraries\Access\Metadata\Credentials;
use O2System\Model;
use O2System\Glob\ArrayObject;

/**
 * Token Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/access/login.html
 */
class Token extends DriverInterface
{
	private $storage = [
		'signature'   => NULL,
		'credentials' => [ ],
	];

	/**
	 * Set Access
	 *
	 * Set access for login user
	 *
	 * @param object $user
	 * @param string $password
	 *
	 * @access  public
	 */
	public function setAccess( $user, $password, $remember = FALSE )
	{
		$id_user_account = $user->offsetExists( 'id_user_account' ) ? $user->id_user_account : $user->id;

		if ( $user->offsetExists( 'salt' ) )
		{
			$salt          = $this->_library->saltPassword();
			$hash_password = $this->_library->hashPassword( $password, $salt );
			$this->_library->model->updateAccount(
				[
					'id'       => $id_user_account,
					'password' => $hash_password,
					'salt'     => $salt,
				] );

			$user->offsetUnset( 'salt' );
		}

		\O2System::Session()->unsetUserdata( '__loginAttempts' );
		\O2System::Session()->unsetTempdata( '__rememberAttempts' );
		\O2System::Session()->unsetTempdata( '__ssoAttempts' );

		if ( $user->offsetExists( 'password' ) )
		{
			$user->offsetUnset( 'password' );
		}

		\O2System::$active[ 'account' ] = $user;

		$ip_address = \O2System::Input()->ipAddress();
		$user_agent = \O2System::Input()->userAgent();

		\O2System::Session()->setUserdata( 'account', $user );

		$this->storage[ 'signature' ]   = hash( "haval256,5", \O2System::$config[ 'encryption_key' ] . $id_user_account . $user_agent . $ip_address . microtime() );
		$this->storage[ 'credentials' ] = [
			'id_user_account' => ( $user->offsetExists( 'id_user_account' ) ? $user->id_user_account : $user->id ),
			'ip_address'      => \O2System::Input()->ipAddress(),
			'user_agent'      => \O2System::Input()->userAgent(),
		];

		if ( $this->_library->getConfig( 'token', 'access' ) === TRUE )
		{
			$this->__setJson();
		}

		if ( $this->_library->getConfig( 'remember', 'access' ) === TRUE AND $remember === TRUE AND $this->_library->cookie->getRemember() === FALSE )
		{
			$this->__setRemember();
		}

		if ( $this->_library->getConfig( 'sso', 'access' ) === TRUE AND $this->_library->cookie->getSso() === FALSE )
		{
			$this->__setSso();
		}
	}

	private function __setJson()
	{
		$lifetime = time() + $this->_library->getConfig( 'token', 'lifetime' );
		\O2System::Cache()->save( 'token-' . $this->storage[ 'signature' ], $this->storage, ( $lifetime - 60 ) );
	}

	private function __setRemember()
	{
		$lifetime = time() + $this->_library->getConfig( 'remember', 'lifetime' );
		\O2System::Cache()->save( 'remember-' . $this->storage[ 'signature' ], $this->storage, ( $lifetime - 60 ) );

		$credentials = serialize( $this->storage );
		$credentials = $this->_library->encryption->encrypt( $credentials );

		$this->_library->cookie->setRemember( $credentials );
	}

	private function __setSso()
	{
		$lifetime = time() + $this->_library->getConfig( 'sso', 'lifetime' );
		\O2System::Cache()->save( 'sso-' . $this->storage[ 'signature' ], $this->storage, ( $lifetime - 60 ) );

		$credentials = serialize( $this->storage );
		$credentials = $this->_library->encryption->encrypt( $credentials );

		$this->_library->cookie->setSso( $credentials );
	}

	public function getSignature()
	{
		return $this->storage[ 'signature' ];
	}

	public function getCredentials( $signature )
	{
		if ( $cache = \O2System::Cache()->get( 'token-' . $signature ) )
		{
			$this->storage[ 'signature' ]          = $cache[ 'signature' ];
			$cache[ 'credentials' ][ 'signature' ] = $cache[ 'signature' ];

			return $this->storage[ 'credentials' ] = new Credentials( $cache[ 'credentials' ] );
		}

		return FALSE;
	}
}