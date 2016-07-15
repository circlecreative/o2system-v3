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
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Libraries\Socialmedia\Drivers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Libraries\Socialmedia\Interfaces\DriverInterface;
use O2System\Libraries\Socialmedia\Metadata\Connection;
use O2System\Libraries\Socialmedia\Metadata\Name;
use O2System\Libraries\Socialmedia\Metadata\Token;

/**
 * Facebook SDK Driver
 *
 * @package       social
 * @subpackage    drivers
 * @category      driver class
 * @author        Steeven Andrian Salim
 */
class Google extends DriverInterface
{
	protected $_url = [
		'base'  => 'https://plus.google.com/',
		'api'   => 'https://www.googleapis.com/plus/',
		'share' => 'https://plus.google.com/share?url=%s',
	];

	public function getShareLink( $link = NULL )
	{
		$link = isset( $link ) ? $link : current_url();

		return sprintf( $this->_url[ 'share' ], urlencode( $link ) );
	}

	public function getAuthorizeLink( $callback )
	{
		$redirect_uri = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );

		$google = new \Google_Client();

		$google->setClientId( $this->_config[ 'client_id' ] );
		$google->setClientSecret( $this->_config[ 'client_secret' ] );
		$google->setRedirectUri( $redirect_uri );
		$google->setScopes( $this->_config[ 'client_scopes' ] );
		$google->addScope( \Google_Service_Plus::PLUS_LOGIN );

		\O2System::Session()->setUserdata(
			'socialmedia_google_request', [
			'redirect_uri' => $redirect_uri,
		] );

		return $google->createAuthUrl();
	}

	/**
	 * Get Authorize Token
	 *
	 * @access  public
	 * @return  array|bool
	 */
	public function getAuthorizeToken()
	{
		if ( $get = \O2System::Input()->get() )
		{
			if ( \O2System::Session()->hasUserdata( 'socialmedia_google_request' ) )
			{
				$request = \O2System::Session()->userdata( 'socialmedia_google_request' );

				$google = new \Google_Client();

				$google->setClientId( $this->_config[ 'client_id' ] );
				$google->setClientSecret( $this->_config[ 'client_secret' ] );
				$google->setRedirectUri( $request[ 'redirect_uri' ] );
				$google->setScopes( $this->_config[ 'client_scopes' ] );
				$google->addScope( \Google_Service_Plus::PLUS_LOGIN );

				$google->authenticate( $get->code );
				$access_token = $google->getAccessToken();

				if ( is_array( $access_token ) )
				{
					$access_token[ 'redirect_uri' ] = $request[ 'redirect_uri' ];

					return $access_token;
				}
				else
				{
					$this->_errors[] = 'User-Canceled: Canceled by User';
				}
			}
			else
			{
				$this->_errors[] = 'Session: Undefined Google+ Request';
			}
		}
		else
		{
			$this->_errors[] = 'GET: Undefined Google+ Feedback Code';
		}

		return FALSE;
	}

	public function setConnection( $connection )
	{
		if ( $connection instanceof Connection )
		{
			if ( isset( $connection->token[ 'access_token' ] ) )
			{
				$this->_connection = new Connection();

				if ( $connection->token instanceof Token )
				{
					$this->_connection->setToken( $connection->token->__toArray() );
				}
				else
				{
					$this->_connection->setToken( (array) $connection->token );
				}
			}
		}
		elseif ( is_array( $connection ) )
		{
			if ( isset( $connection[ 'access_token' ] ) )
			{
				$this->_connection = new Connection();
				$this->_connection->setToken( $connection );
			}
		}

		if ( $this->isConnected() )
		{
			return $this->__buildConnection();
		}

		return FALSE;
	}

	/**
	 * Connection Builder
	 *
	 * @return bool
	 */
	protected function __buildConnection()
	{
		if ( $person = $this->getUserProfile() )
		{
			$this->_connection->setUserProfile(
				[
					'id'          => $person->getId(),
					'username'    => $person->getNickname(),
					'name'        => new Name( explode( ' ', $person->getDisplayName() ) ),
					'description' => $person->getAboutMe(),
					'url'         => $person->getUrl(),
					'avatar'      => $person->getImage()->url,
					'cover'       => NULL,
				] );

			$this->_connection->setMetadata( (array) $person );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param        $path
	 * @param array  $params
	 * @param string $method
	 *
	 * @return bool|\Google_Service_Plus_Resource_People
	 */
	public function request( $path, $params = [ ], $method = 'GET' )
	{
		$google = new \Google_Client();

		$google->setClientId( $this->_config[ 'client_id' ] );
		$google->setClientSecret( $this->_config[ 'client_secret' ] );
		$google->setRedirectUri( $this->_connection->token[ 'redirect_uri' ] );
		$google->setScopes( $this->_config[ 'client_scopes' ] );

		$google->setAccessToken( $this->_connection->token->__toArray() );
		$google->addScope( \Google_Service_Plus::PLUS_ME );

		$plus = new \Google_Service_Plus( $google );

		if ( property_exists( $plus, $path ) )
		{
			return $plus->{$path};
		}

		return FALSE;
	}

	/**
	 * @param string $user_id
	 *
	 * @return bool|\Google_Service_Plus_Person
	 */
	public function getUserProfile( $user_id = 'me' )
	{
		if ( $request = $this->request( 'people' ) )
		{
			if ( $person = $request->get( $user_id ) )
			{
				return $person;
			}
		}

		return FALSE;
	}
}