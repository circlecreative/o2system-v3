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

namespace O2System\Libraries\Socialmedia\Drivers;

use O2System\CURL;
use O2System\Glob\ArrayObject;
use O2System\Libraries\Socialmedia\Interfaces\DriverInterface;
use O2System\Libraries\Socialmedia\Metadata\Connection;
use O2System\Libraries\Socialmedia\Metadata\Name;
use O2System\Libraries\Socialmedia\Metadata\Token;

defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * Instagram Driver
 *
 * @package       social
 * @subpackage    drivers
 * @category      driver class
 * @author        Steeven Andrian Salim
 */
class Instagram extends DriverInterface
{
	protected $_url = [
		'base'  => 'https://instagram.com/',
		'share' => 'https://instagram.com/pin/create/button/?url=%s',
		'api'   => 'https://api.instagram.com/v1/',
	];

	/**
	 * Get Share Link
	 *
	 * @param string|null $link
	 *
	 * @access  public
	 * @return  string|null
	 */
	public function getShareLink( $link = NULL )
	{
		// TODO: Implement getShareLink() method.
	}

	/**
	 * Get Authorize Link
	 *
	 * @param string $callback
	 *
	 * @access  public
	 * @return  string|bool
	 */
	public function getAuthorizeLink( $callback )
	{
		$redirect_uri = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );

		$instagram = new \MetzWeb\Instagram\Instagram(
			[
				'apiKey'      => $this->_config[ 'client_id' ],
				'apiSecret'   => $this->_config[ 'client_secret' ],
				'apiCallback' => $redirect_uri,
			] );

		\O2System::Session()->setUserdata(
			'socialmedia_instagram_request', [
			'api_callback' => $redirect_uri,
		] );

		return $instagram->getLoginUrl();
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
			if ( \O2System::Session()->hasUserdata( 'socialmedia_instagram_request' ) )
			{
				$request = \O2System::Session()->userdata( 'socialmedia_instagram_request' );

				$instagram = new \MetzWeb\Instagram\Instagram(
					[
						'apiKey'      => $this->_config[ 'client_id' ],
						'apiSecret'   => $this->_config[ 'client_secret' ],
						'apiCallback' => $request[ 'api_callback' ],
					] );

				$token = $instagram->getOAuthToken( $get->code );

				if ( isset( $token->access_token ) )
				{
					return [
						'access_token'    => $token->access_token,
						'access_id'       => $token->user->id,
						'access_username' => $token->user->username,
						'access_callback' => $request[ 'api_callback' ],
					];
				}
				elseif ( isset( $token->error_type ) )
				{
					$this->_errors[ $token->code ] = $token->error_type . ': ' . $token->error_message;
				}
				else
				{
					$this->_errors[] = 'User-Canceled: Canceled by User';
				}
			}
			else
			{
				$this->_errors[] = 'Session: Undefined Instagram Request';
			}
		}
		else
		{
			$this->_errors[] = 'GET: Undefined Instagram Feedback Code';
		}

		return FALSE;
	}

	/**
	 * Set Connection
	 *
	 * @param Connection|array $connection
	 *
	 * @return bool
	 */
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
		if ( $user_profile = $this->getUserProfile() )
		{
			$this->_connection->setUserProfile(
				[
					'id'          => $user_profile->id,
					'username'    => $user_profile->username,
					'name'        => new Name( explode( ' ', $user_profile->full_name ) ),
					'description' => $user_profile->bio,
					'url'         => $this->_url[ 'base' ] . $user_profile->username,
					'avatar'      => $user_profile->profile_picture,
					'cover'       => NULL,
				] );

			$this->_connection->setMetadata( $user_profile->__toArray() );

			return TRUE;
		}

		return FALSE;
	}

	public function request( $path = NULL, $params = [ ], $method = 'GET' )
	{
		$curl = new CURL();

		// Setup Default Parameters
		$params[ 'access_token' ] = $this->_connection->token[ 'access_token' ];

		if ( $method === 'POST' )
		{
			$response = $curl->post( $this->_url[ 'api' ], $path, $params );
		}
		else
		{
			$response = $curl->get( $this->_url[ 'api' ], $path, $params );
		}

		if ( $response->info->http_code === 200 )
		{
			if ( $response->data->meta[ 'code' ] === 200 )
			{
				return new ArrayObject( $response->data[ 'data' ] );
			}
		}

		return FALSE;
	}

	public function getUserProfile()
	{
		if ( $profile = $this->request( 'users/self' ) )
		{
			return $profile;
		}

		return FALSE;
	}
}