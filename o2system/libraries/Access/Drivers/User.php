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
 * User Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/access/login.html
 */
class User extends DriverInterface
{
	/**
	 * User Account
	 *
	 * @param string $return
	 *
	 * @return array|null|object
	 */
	public function account( $return = NULL )
	{
		if ( $this->is_login() )
		{
			if ( $account = \O2System::Session()->userdata( 'account' ) )
			{
				if ( $account instanceof \ArrayObject )
				{
					if ( isset( $return ) )
					{
						if ( $account->offsetExists( $return ) )
						{
							return $account->offsetGet( $return );
						}
					}

					return $account;
				}
			}
		}

		\O2System::Session()->unset_userdata( 'account' );

		return NULL;
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Login
	 *
	 * Check if user is already login
	 *
	 * @access  public
	 * @return  bool
	 */
	public function is_login()
	{
		if ( \O2System::Session()->has_userdata( 'account' ) )
		{
			\O2System::$active[ 'account' ] = \O2System::Session()->userdata( 'account' );

			return TRUE;
		}
		elseif ( \O2System::Session()->has_userdata( '__rememberAttempts' ) === FALSE )
		{
			\O2System::Session()->set_userdata( '__rememberAttempts', TRUE );
			\O2System::Session()->mark_tempdata( '__rememberAttempts', 300 );

			//echo 'remember:' . ( isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ] );
			if ( $token = $this->get_remember_token() )
			{
				return $this->_library->login->remember( $token );
			}
		}
		elseif ( $this->_library->getConfig( 'sso', 'access' ) === TRUE AND \O2System::Session()->has_userdata( '__ssoAttempts' ) === FALSE )
		{
			\O2System::Session()->set_userdata( '__ssoAttempts', TRUE );
			\O2System::Session()->mark_tempdata( '__ssoAttempts', 300 );

			if ( $token = $this->get_sso_token() )
			{
				return $this->_library->login->sso( $token );
			}
			else
			{
				$origin = isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ];
				$origin = parse_domain( $origin );

				$server = parse_domain( $this->_library->getConfig( 'sso', 'server' ) );

				if ( $origin->host !== $server->host )
				{
					$redirect = $server->origin . '/sso/request' . '?' . http_build_query( [ 'origin' => $origin->origin ] );
					$redirect = is_https() ? 'https://' . $redirect : 'http://' . $redirect;

					//print_out( $redirect );

					redirect( $redirect, 'refresh' );
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function get_sso_token()
	{
		$sso = \O2System::Input()->cookie( 'sso' );

		if ( ! empty( $sso ) )
		{
			$sso = $this->_library->encryption->decrypt( $sso );
			$sso = is_serialized( $sso ) ? unserialize( $sso ) : $sso;

			if ( isset( $sso[ 'token' ] ) )
			{
				if ( $cache = \O2System::Cache()->get( 'sso-' . $sso[ 'token' ] ) )
				{
					if ( $cache[ 'token' ] === $sso[ 'token' ] )
					{
						return $sso[ 'token' ];
					}
				}
			}
		}

		delete_cookie( 'sso' );

		return FALSE;
	}

	public function get_remember_token()
	{
		$remember = \O2System::Input()->cookie( 'remember' );

		if ( ! empty( $remember ) )
		{
			$remember = $this->_library->encryption->decrypt( $remember );
			$remember = is_serialized( $remember ) ? unserialize( $remember ) : $remember;

			if ( isset( $remember[ 'token' ] ) )
			{
				if ( $cache = \O2System::Cache()->get( 'remember-' . $remember[ 'token' ] ) )
				{
					if ( $cache[ 'token' ] === $remember[ 'token' ] )
					{
						return $remember[ 'token' ];
					}
				}
			}
		}

		delete_cookie( 'remember' );

		return FALSE;
	}

	public function destroy_cookies()
	{
		// Try to get remember cookie
		$remember = \O2System::Input()->cookie( 'remember' );

		if ( ! empty( $remember ) )
		{
			$remember = $this->_library->encryption->decrypt( $remember );
			$remember = is_serialized( $remember ) ? unserialize( $remember ) : $remember;

			if ( isset( $remember[ 'token' ] ) )
			{
				\O2System::Cache()->delete( 'remember-' . $remember[ 'token' ] );
				delete_cookie( 'remember' );
			}
		}

		// Try to get sso cookie
		$sso = \O2System::Input()->cookie( 'sso' );

		if ( ! empty( $sso ) )
		{
			$sso = $this->_library->encryption->decrypt( $sso );
			$sso = is_serialized( $sso ) ? unserialize( $sso ) : $sso;

			if ( isset( $sso[ 'token' ] ) )
			{
				\O2System::Cache()->delete( 'sso-' . $sso[ 'token' ] );
				delete_cookie( 'sso' );
			}
		}
	}

	/**
	 * Logout
	 *
	 * Destroy session, cookie, sso for active user
	 *
	 * @access  public
	 */
	public function logout()
	{
		$this->destroy_cookies();

		if ( \O2System::Session()->is_started() )
		{
			\O2System::Session()->destroy();
		}

		if ( $api_url = $this->_library->getConfig( 'sso', 'api' ) )
		{
			$origin = isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ];

			if ( strpos( $api_url, $origin ) === FALSE )
			{
				$api_url = is_https() ? 'https://' . $api_url : 'http://' . $api_url . '/logout';
				
				redirect( $api_url . '?' . http_build_query( [ 'origin' => $origin ] ) );
			}
		}
	}
}
