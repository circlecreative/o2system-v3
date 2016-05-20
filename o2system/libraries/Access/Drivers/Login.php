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
 * Login Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/access/login.html
 */
class Login extends DriverInterface
{
	/**
	 * Login User
	 *
	 *
	 * @param string $username User Username|Email|MSIDN
	 * @param string $password User Password
	 *
	 * @access  public
	 * @return  bool
	 */
	public function set( $username, $password, $remember = FALSE )
	{
		$remember = empty( $remember ) ? FALSE : TRUE;

		if ( \O2System::Session()->has_userdata( '__loginAttempts' ) === FALSE )
		{
			$attempts = array(
				'count' => 0,
				'time'  => now(),
			);

			\O2System::Session()->set_userdata( '__loginAttempts', $attempts );
		}
		else
		{
			$attempts = \O2System::Session()->userdata( '__loginAttempts' );
		}

		if ( $attempts[ 'count' ] < $this->_config[ 'attempts' ] )
		{
			$username = trim( $username );
			$password = trim( $password );

			$user = $this->_library->model->getAccount( $username );

			if ( $user instanceof \ArrayObject )
			{
				if ( $user->offsetExists( 'salt' ) )
				{
					$hash_password = $this->_library->hashPassword( $password, $user->salt );
				}
				else
				{
					$hash_password = $this->_library->hashPassword( $password );
				}

				if ( $user->password === $hash_password )
				{
					$this->_library->token->setAccess( $user, $password, $remember );

					return TRUE;
				}
			}
		}

		$attempts = \O2System::Session()->userdata( '__loginAttempts' );
		$attempts[ 'count' ]++;
		$attempts[ 'time' ] = now();

		\O2System::Session()->set_userdata( '__loginAttempts', $attempts );

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function getAttempts()
	{
		$attempts = array(
			'count' => 0,
			'time'  => now(),
		);

		if ( \O2System::Session()->has_userdata( 'attempts' ) )
		{
			$attempts = \O2System::Session()->userdata( 'attempts' );
		}

		return $attempts;
	}

	// ------------------------------------------------------------------------

	public function fromCredentials( Credentials $credentials )
	{
		if ( $this->_library->getConfig( 'login', 'match_ip' ) === TRUE )
		{
			if ( $credentials[ 'ip_address' ] !== \O2System::Input()->ipAddress() )
			{
				return FALSE;
			}
		}

		if ( $this->_library->getConfig( 'login', 'match_agent' ) === TRUE )
		{
			if ( $credentials[ 'user_agent' ] !== \O2System::Input()->userAgent() )
			{
				return FALSE;
			}
		}

		if ( $this->_library->model instanceof Model )
		{
			if ( $user = $this->_library->model->getAccount( $credentials[ 'id_user_account' ] ) )
			{
				if ( $user instanceof \ArrayObject === FALSE ) return FALSE;

				$this->_library->token->setAccess( $user, $user->password, (bool) empty( get_cookie( 'remember' ) ) );

				return TRUE;
			}
		}

		return FALSE;
	}

	public function destroy()
	{

	}
}
