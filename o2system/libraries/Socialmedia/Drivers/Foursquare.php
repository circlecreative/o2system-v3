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

use O2System\CURL;
use O2System\Glob\ArrayObject;
use O2System\Libraries\OAuth\Utility;
use O2System\Libraries\Socialmedia\Interfaces\DriverInterface;
use O2System\Libraries\Socialmedia\Metadata\Connection;
use O2System\Libraries\Socialmedia\Metadata\Name;
use O2System\Libraries\Socialmedia\Metadata\Token;

/**
 * Foursquare Driver
 *
 * @package       social
 * @subpackage    drivers
 * @category      driver class
 * @author        Steeven Andrian Salim
 */
class Foursquare extends DriverInterface
{
	protected $_url = [
		'base'  => 'https://foursquare.com/',
		'share' => 'https://plus.google.com/share?url=%s',
		'api'   => 'https://api.foursquare.com/',
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

	public function getAuthorizeLink( $callback )
	{
		$redirect_uri = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );

		\O2System::Session()->setUserdata(
			'socialmedia_foursquare_request',
			[
				'redirect_uri' => $redirect_uri,
			] );

		return $this->_url[ 'base' ] . 'oauth2/authenticate?' . http_build_query(
			[
				'client_id'     => $this->_config[ 'client_id' ],
				'response_type' => 'code',
				'redirect_uri'  => $redirect_uri,
			], PHP_QUERY_RFC3986 );
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
			if ( \O2System::Session()->hasUserdata( 'socialmedia_foursquare_request' ) )
			{
				$request = \O2System::Session()->userdata( 'socialmedia_foursquare_request' );

				if ( $get->offsetExists( 'code' ) )
				{
					$curl = new CURL();

					$response = $curl->get(
						$this->_url[ 'base' ] . 'oauth2/access_token', [
						'grant_type'    => 'authorization_code',
						'code'          => $get->code,
						'redirect_uri'  => $request[ 'redirect_uri' ],
						'client_id'     => $this->_config[ 'client_id' ],
						'client_secret' => $this->_config[ 'client_secret' ],
					] );

					if ( $response->info->http_code === 200 )
					{
						return $response->data->__toArray();
					}
				}
				elseif ( $get->offsetExists( 'error' ) )
				{
					$this->_errors[] = $get->error . ': ' . $get->error_description;
				}
				else
				{
					$this->_errors[] = 'User-Canceled: Canceled by User';
				}
			}
			else
			{
				$this->_errors[] = 'Session: Undefined Foursquare Request';
			}
		}
		else
		{
			$this->_errors[] = 'GET: Undefined Foursquare Feedback Code';
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
		if ( $user_profile = $this->getUserProfile() )
		{
			// Set Username
			$x_canonicalUrl             = explode( '/', $user_profile[ 'canonicalUrl' ] );
			$user_profile[ 'username' ] = end( $x_canonicalUrl );

			$this->_connection->setUserProfile(
				[
					'id'          => $user_profile[ 'id' ],
					'username'    => $user_profile[ 'username' ],
					'name'        => new Name(
						[
							'first' => $user_profile[ 'firstName' ],
							'last'  => $user_profile[ 'lastName' ],
						] ),
					'description' => $user_profile[ 'bio' ],
					'url'         => $user_profile[ 'canonicalUrl' ],
					'avatar'      => $user_profile[ 'photo' ][ 'prefix' ] . '500x500' . $user_profile[ 'photo' ][ 'suffix' ],
					'cover'       => NULL,
				] );

			$this->_connection->setMetadata( (array) $user_profile );

			return TRUE;
		}

		return FALSE;
	}


	public function request( $path = NULL, $params = [ ], $method = 'GET' )
	{
		if ( $this->isConnected() )
		{
			$curl = new CURL();

			// Setup Default Parameters
			$params[ 'oauth_token' ] = $this->_connection->token[ 'access_token' ];
			$params[ 'v' ]           = date( 'Ymd' );

			if ( $method === 'POST' )
			{
				$response = $curl->post( $this->_url[ 'api' ] . 'v2/', $path, $params );
			}
			else
			{
				$response = $curl->get( $this->_url[ 'api' ] . 'v2/', $path, $params );
			}

			if ( $response->info->http_code === 200 )
			{
				if ( $response->data->meta[ 'code' ] === 200 )
				{
					return new ArrayObject( $response->data );
				}
				else
				{
					$this->_errors[ $response->data->meta[ 'code' ] ] = $response->data->meta[ 'errorType' ] . ': ' . $response->data->meta[ 'errorDetail' ];
				}
			}
		}
		else
		{
			$this->_errors[] = 'Connection: Undefined Connection Token';
		}

		return FALSE;
	}

	public function getUserProfile( $id = 'self' )
	{
		if ( $request = $this->request( 'users/' . $id ) )
		{
			if ( isset( $request[ 'response' ][ 'user' ] ) )
			{
				return $request[ 'response' ][ 'user' ];
			}
		}

		return FALSE;
	}

	public function getUserVenueHistory( $id = 'self' )
	{
		if ( $request = $this->request( 'users/' . $id . '/venuehistory' ) )
		{
			if ( isset( $request[ 'response' ][ 'venues' ] ) )
			{
				return $request[ 'response' ][ 'venues' ];
			}
		}

		return FALSE;
	}

	public function getUserSearch( array $params )
	{
		if ( $request = $this->request( 'users/search', $params ) )
		{
			if ( isset( $request[ 'response' ][ 'results' ] ) )
			{
				return $request[ 'response' ][ 'results' ];
			}
		}
	}

	public function getUserRequest()
	{
		if ( $request = $this->request( 'users/requests' ) )
		{
			if ( isset( $request[ 'response' ][ 'requests' ] ) )
			{
				return $request[ 'response' ][ 'requests' ];
			}
		}

		return FALSE;
	}
}