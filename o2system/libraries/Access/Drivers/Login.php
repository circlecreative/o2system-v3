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
use O2System\Model;

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
	public function user( $username, $password )
	{
		if ( \O2System::Session()->has_userdata( 'attempts' ) === FALSE )
		{
			$attempts = array(
				'count' => 0,
				'time'  => now(),
			);

			\O2System::Session()->set_userdata( 'attempts', $attempts );
		}
		else
		{
			$attempts = \O2System::Session()->userdata( 'attempts' );
		}

		if ( $attempts[ 'count' ] < $this->_config[ 'attempts' ] )
		{
			$username = trim( $username );
			$password = trim( $password );

			$user = $this->_library->model->get_account( $username );

			if ( $user instanceof \ArrayObject )
			{
				if ( $user->offsetExists( 'salt' ) )
				{
					$hash_password = $this->_library->hash_password( $password, $user->salt );
				}
				else
				{
					$hash_password = $this->_library->hash_password( $password );
				}

				if ( $user->password === $hash_password )
				{
					$this->_set_access( $user, $password );

					\O2System::Session()->unset_userdata( 'attempts' );

					return TRUE;
				}
			}
		}

		$attempts = \O2System::Session()->userdata( 'attempts' );
		$attempts[ 'count' ]++;
		$attempts[ 'time' ] = now();

		\O2System::Session()->set_userdata( 'attempts', $attempts );

		return FALSE;
	}

	// ------------------------------------------------------------------------

	protected function _set_remember()
	{
		$remember = ( \O2System::Input()->post( 'remember' ) == 1 ? TRUE : FALSE );

		if ( $remember === TRUE )
		{
			$id_user_account = $_SESSION[ 'account' ]->offsetExists( 'id_user_account' ) ? $_SESSION[ 'account' ]->id_user_account : $_SESSION[ 'account' ]->id;

			$token = array(
				'id_user_account' => $id_user_account,
				'token'           => $_SESSION[ 'account' ]->token,
				'ip_address'      => \O2System::Input()->ip_address(),
				'agent'           => \O2System::Input()->user_agent(),
			);

			$lifetime = time() + \O2System::$config[ 'cookie' ][ 'lifetime' ];
			\O2System::Cache()->save( 'remember-' . $_SESSION[ 'account' ]->token, $token, ( $lifetime - 60 ) );

			$token = serialize( $token );
			$token = $this->_library->encryption->encrypt( $token );

			$name = 'remember';

			if ( $cookie = \O2System::$config[ 'cookie' ] )
			{
				$name = $cookie[ 'prefix' ] . $name;
			}

			$domain = parse_domain()->domain;

			delete_cookie( $name );

			setcookie(
				$name,
				$token,
				$lifetime,
				'/',
				$domain,
				is_https(),
				TRUE // HttpOnly; Yes, this is intentional and not configurable for security reasons
			);
		}
	}


	/**
	 * Set Access
	 *
	 * Set access for login user
	 *
	 * @param object $user
	 * @param string $password
	 *
	 * @access  protected
	 */
	protected function _set_access( $user, $password )
	{
		$id_user_account = $user->offsetExists( 'id_user_account' ) ? $user->id_user_account : $user->id;

		if ( $user->offsetExists( 'salt' ) )
		{
			$salt = $this->_library->generate_salt();
			$hash_password = $this->_library->hash_password( $password, $salt );
			$this->_library->model->update_account( array(
				                                        'id'       => $id_user_account,
				                                        'password' => $hash_password,
				                                        'salt'     => $salt,
			                                        ) );

			$user->offsetUnset( 'salt' );
		}

		if ( $user->offsetExists( 'password' ) )
		{
			$user->offsetUnset( 'password' );
		}

		$ip_address = \O2System::Input()->ip_address();
		$useragent = \O2System::Input()->user_agent();
		$user->token = hash( "haval256,5", \O2System::$config[ 'encryption_key' ] . $id_user_account . $useragent . $ip_address . microtime() );

		\O2System::Session()->set_userdata( 'account', $user );

		if ( $this->_library->getConfig( 'sso', 'access' ) === TRUE AND $this->_library->user->get_sso_token() === FALSE )
		{
			if ( \O2System::$active[ 'domain' ] === $this->_library->getConfig( 'sso', 'server' ) )
			{
				$this->_set_sso_access( $user, \O2System::$active[ 'domain' ] );
			}
			else
			{
				$this->_set_sso_access( $user, $this->_library->getConfig( 'sso', 'server' ) );
			}
		}

		if ( $this->_library->user->get_remember_token() === FALSE )
		{
			$this->_set_remember();
		}
	}

	// ------------------------------------------------------------------------

	protected function _set_sso_access( $user, $domain = NULL )
	{
		$id_user_account = $user->offsetExists( 'id_user_account' ) ? $user->id_user_account : $user->id;

		$token = array(
			'id_user_account' => $id_user_account,
			'token'           => $user->token,
			'ip_address'      => \O2System::Input()->ip_address(),
			'agent'           => \O2System::Input()->user_agent(),
		);

		$lifetime = time() + $this->_library->getConfig( 'sso', 'lifetime' );
		\O2System::Cache()->save( 'sso-' . $user->token, $token, ( $lifetime - 60 ) );

		$token = serialize( $token );
		$token = $this->_library->encryption->encrypt( $token );

		$name = 'sso';

		if ( $cookie = \O2System::$config[ 'cookie' ] )
		{
			$name = $cookie[ 'prefix' ] . $name;
		}

		$domain = parse_domain( $domain )->domain;

		delete_cookie( $name );

		setcookie(
			$name,
			$token,
			$lifetime, '/',
			'.' . $domain,
			is_https(),
			TRUE // HttpOnly; Yes, this is intentional and not configurable for security reasons
		);
	}

	public function sso( $token )
	{
		if ( $sso = \O2System::Cache()->get( 'sso-' . $token ) )
		{
			if ( $this->_library->getConfig( 'login', 'match_ip' ) === TRUE )
			{
				if ( $sso[ 'ip_address' ] !== \O2System::Input()->ip_address() )
				{
					return FALSE;
				}
			}

			if ( $this->_library->getConfig( 'login', 'match_agent' ) === TRUE )
			{
				if ( $sso[ 'agent' ] !== \O2System::Input()->user_agent() )
				{
					return FALSE;
				}
			}

			if ( $this->_library->model instanceof Model )
			{
				if ( $user = $this->_library->model->get_account( $sso[ 'id_user_account' ] ) )
				{
					$id_user_account = $user->offsetExists( 'id_user_account' ) ? $user->id_user_account : $user->id;

					if ( $user->offsetExists( 'salt' ) )
					{
						$salt = $this->_library->generate_salt();
						$hash_password = $this->_library->hash_password( $user->password, $salt );
						$this->_library->model->update_account( array(
							                                        'id'       => $id_user_account,
							                                        'password' => $hash_password,
							                                        'salt'     => $salt,
						                                        ) );

						$user->offsetUnset( 'salt' );
					}

					if ( $user->offsetExists( 'password' ) )
					{
						$user->offsetUnset( 'password' );
					}

					\O2System::Session()->set_userdata( 'account', $user );

					$lifetime = time() + $this->_library->getConfig( 'sso', 'lifetime' );

					$token = serialize( $sso );
					$token = $this->_library->encryption->encrypt( $token );

					$name = 'sso';

					if ( $cookie = \O2System::$config[ 'cookie' ] )
					{
						$name = $cookie[ 'prefix' ] . $name;
					}

					$domain = isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ];

					setcookie(
						$name,
						$token,
						$lifetime, '/',
						'.' . $domain,
						is_https(),
						TRUE // HttpOnly; Yes, this is intentional and not configurable for security reasons
					);

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	public function remember( $token )
	{
		if ( $remember = \O2System::Cache()->get( 'remember-' . $token ) )
		{
			if ( $this->_library->getConfig( 'login', 'match_ip' ) === TRUE )
			{
				if ( $remember[ 'ip_address' ] !== \O2System::Input()->ip_address() )
				{
					return FALSE;
				}
			}

			if ( $this->_library->getConfig( 'login', 'match_agent' ) === TRUE )
			{
				if ( $remember[ 'agent' ] !== \O2System::Input()->user_agent() )
				{
					return FALSE;
				}
			}

			if ( $this->_library->model instanceof Model )
			{
				if ( $user = $this->_library->model->get_account( $remember[ 'id_user_account' ] ) )
				{
					// remove old cookie
					delete_cookie( 'remember' );

					$_POST[ 'remember' ] = 1;

					$this->_set_access( $user, $user->password );

					return TRUE;
				}
			}
		}

		return FALSE;
	}
}
