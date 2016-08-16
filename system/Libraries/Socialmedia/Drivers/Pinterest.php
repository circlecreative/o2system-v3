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
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\SPL\ArrayObject;
use O2System\CURL;
use O2System\Libraries\Socialmedia\Interfaces\DriverInterface;
use O2System\Libraries\Socialmedia\Metadata\Connection;
use O2System\Libraries\Socialmedia\Metadata\Name;
use O2System\Libraries\Socialmedia\Metadata\Token;

class Pinterest extends DriverInterface
{
	protected $_url = [
		'base'  => 'https://pinterest.com',
		'share' => 'https://pinterest.com/pin/create/button/?url=%s',
		'api'   => 'https://api.pinterest.com/',
	];

	public function getShareLink( $link = NULL )
	{
		$link = isset( $link ) ? $link : current_url();

		return sprintf( $this->_url[ 'share' ], urlencode( $link ) );
	}

	public function getAuthorizeLink( $callback )
	{
		$redirect_uri = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );

		$parameters = [
			"response_type" => 'code',
			"redirect_uri"  => $redirect_uri,
			"client_id"     => $this->_config[ 'app_id' ],
			"client_secret" => $this->_config[ 'app_secret' ],
			"scope"         => implode( ',', $this->_config[ 'app_scopes' ] ),
			"state"         => substr( md5( rand() ), 0, 7 ),
		];

		return $this->_url[ 'api' ] . 'oauth/?' . http_build_query( $parameters );
	}

	/**
	 * Get Authorize Token
	 *
	 * @access  public
	 * @return  array|bool
	 */
	public function getAuthorizeToken()
	{
		if ( $get = \O2System::$request->getGet() )
		{
			if ( $get->offsetExists( 'code' ) )
			{
				$curl = new CURL();

				$response = $curl->post(
					$this->_url[ 'api' ] . 'v1/oauth/token', [
					'grant_type'    => 'authorization_code',
					'client_id'     => $this->_config[ 'app_id' ],
					'client_secret' => $this->_config[ 'app_secret' ],
					'code'          => $get->code,
				] );

				if ( $response->info->http_code === 200 )
				{
					return $response->data->__toArray();
				}
				elseif ( isset( $response->data->error ) )
				{
					$this->_errors[] = $response->data->error;
				}
			}
			else
			{
				$this->_errors[] = 'User-Canceled: Canceled by User';
			}
		}
		else
		{
			$this->_errors[] = 'GET: Undefined Pinterest Feedback Code';
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
					'name'        => new Name(
						[
							'first' => $user_profile->first_name,
							'last'  => $user_profile->last_name,
						] ),
					'description' => $user_profile->bio,
					'url'         => $this->_url[ 'base' ] . $user_profile->username,
					'avatar'      => $user_profile->image[ 'small' ][ 'url' ],
					'cover'       => $user_profile->image[ 'large' ][ 'url' ],
				] );

			$this->_connection->setMetadata( $user_profile->__toArray() );

			return TRUE;
		}

		return FALSE;
	}

	public function request( $path = NULL, $params = [ ], $method = 'GET' )
	{
		if ( $this->isConnected() )
		{
			$curl = new CURL();
			$curl->setHeaders(
				[
					'Authorization' => 'Bearer ' . $this->_connection->token[ 'access_token' ],
					'Content-Type'  => 'multipart/form-data',
				] );

			$response = $curl->request( $this->_url[ 'api' ] . 'v1/' . trim( $path, '/' ) . '/', $params, $method );

			if ( $response->info->http_code === 200 )
			{
				return $response->data[ 'data' ];
			}
		}
		else
		{
			$this->_errors[] = 'Connection: Undefined Connection Token';
		}

		return FALSE;
	}

	public function getUserProfile( array $fields = [ 'username', 'first_name', 'last_name', 'bio', 'created_at', 'counts', 'image[small,large]' ] )
	{

		if ( $profile = $this->request( 'me', [ 'fields' => implode( ',', $fields ) ] ) )
		{
			return new ArrayObject( $profile );
		}

		return FALSE;
	}
}