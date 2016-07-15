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


use O2System\Glob\ArrayObject;
use O2System\Libraries\Socialmedia\Interfaces\DriverInterface;
use O2System\CURL;
use O2System\Libraries\OAuth\Consumer;
use O2System\Libraries\OAuth\Interfaces\Method;
use O2System\Libraries\OAuth\Request;
use O2System\Libraries\OAuth\SignatureMethod\SHA1\HMAC;
use O2System\Libraries\OAuth\Token;
use O2System\Libraries\OAuth\Utility;
use O2System\Libraries\Socialmedia\Metadata\Connection;
use O2System\Libraries\Socialmedia\Metadata\Name;

class Tumblr extends DriverInterface
{
	/**
	 * Social Media Driver URL List
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_url = [
		'share' => 'http://tumblr.com/widgets/share/tool?canonicalUrl=%s',
		'api'   => 'http://api.tumblr.com/',
		'base'  => 'http://www.tumblr.com/',
	];

	/**
	 * Social Media Driver API Client
	 *
	 * @access  protected
	 * @type    \Tumblr\API\Client
	 */
	protected $_api;

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
		$link = isset( $link ) ? $link : current_url();

		return sprintf( $this->_url[ 'share' ], urlencode( $link ) );
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

		$consumer = new Consumer( $this->_config[ 'consumer_key' ], $this->_config[ 'consumer_secret' ], $redirect_uri );
		$request  = Request::fromConsumerAndToken( $consumer, new Token(), Method::GET, $this->_url[ 'base' ] . 'oauth/request_token', [ 'oauth_callback' => $redirect_uri ] );
		$request->signRequest( new HMAC(), $consumer, new Token() );

		$curl     = new CURL();
		$response = $curl->get( $request->getHttpUrl(), $request->getParameters() );

		if ( $response->info->http_code == 200 )
		{
			\O2System::Session()->setUserdata(
				'socialmedia_tumblr_request', [
				'request_token'        => $response->data[ 'oauth_token' ],
				'request_token_secret' => $response->data[ 'oauth_token_secret' ],
			] );

			return $this->_url[ 'base' ] . 'oauth/authorize?oauth_token=' . $response->data[ 'oauth_token' ];
		}

		return FALSE;
	}

	/**
	 * Get Authorize Token
	 *
	 * @access  public
	 * @return  string|bool
	 */
	public function getAuthorizeToken()
	{
		if ( $get = \O2System::Input()->get() )
		{
			if ( \O2System::Session()->hasUserdata( 'socialmedia_tumblr_request' ) )
			{
				$request_token = \O2System::Session()->userdata( 'socialmedia_tumblr_request' );
				$consumer      = new Consumer( $this->_config[ 'consumer_key' ], $this->_config[ 'consumer_secret' ] );
				$token         = new Token( $request_token[ 'request_token' ], $request_token[ 'request_token_secret' ] );

				$request = Request::fromConsumerAndToken( $consumer, $token, Method::GET, $this->_url[ 'base' ] . 'oauth/access_token', [ 'oauth_verifier' => $get[ 'oauth_verifier' ] ] );
				$request->signRequest( new HMAC(), $consumer, $token );

				$curl     = new CURL();
				$response = $curl->get( $request->getHttpUrl(), $request->getParameters() );

				if ( $response->info->http_code == 200 )
				{
					return $response->data->__toArray();
				}
			}
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
			if ( isset( $connection->token[ 'oauth_token' ] ) AND isset( $connection->token[ 'oauth_token_secret' ] ) )
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
			if ( isset( $connection[ 'oauth_token' ] ) AND isset( $connection[ 'oauth_token_secret' ] ) )
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

	protected function __buildConnection()
	{
		if ( $user_profile = $this->getUserProfile() )
		{
			$this->_connection->setUserProfile(
				[
					'id'          => NULL,
					'username'    => $user_profile->name,
					'name'        => new Name( explode( ' ', $user_profile->blogs[ 0 ][ 'title' ] ) ),
					'description' => $user_profile->blogs[ 0 ][ 'description' ],
					'url'         => $user_profile->blogs[ 0 ][ 'url' ],
					'avatar'      => $this->getBlogAvatar( $user_profile->blogs[ 0 ][ 'name' ], 128 ),
					'cover'       => NULL,
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
			$consumer  = new Consumer( $this->_config[ 'consumer_key' ], $this->_config[ 'consumer_secret' ] );
			$token     = new Token( $this->_connection->token[ 'oauth_token' ], $this->_connection->token[ 'oauth_token_secret' ] );
			$signature = new HMAC();

			$request = Request::fromConsumerAndToken( $consumer, $token, $method, $this->_url[ 'api' ] . 'v2/' . trim( $path, '/' ) . '/', $params );
			$request->signRequest( $signature, $consumer, $token );

			$request_header = $request->toHeader();
			$request_pieces = explode( ' ', $request_header, 2 );
			$request_string = $request_pieces[ 1 ];

			$curl = new CURL();
			$curl->setHeaders(
				[
					'Authorization' => $request_string,
				] );

			$response = $curl->request( $request->getHttpUrl(), $params, $method );

			if ( $response->info->http_code === 200 )
			{
				return $response->data[ 'response' ];
			}
			elseif ( $response->info->http_code === 301 )
			{
				return $response->data[ 'response' ];
			}
		}
		else
		{
			$this->_errors[] = 'Connection: Undefined Connection Token';
		}

		return FALSE;
	}

	public function getUserProfile()
	{
		if ( $request = $this->request( 'user/info' ) )
		{
			if ( isset( $request[ 'user' ] ) )
			{
				return new ArrayObject( $request[ 'user' ] );
			}
		}

		return FALSE;
	}

	public function getBlogAvatar( $blog_name, $size = 64 )
	{
		$path = 'blog/' . $blog_name . '.tumblr.com/avatar/' . $size;

		if ( $request = $this->request( $path ) )
		{
			if ( isset( $request[ 'avatar_url' ] ) )
			{
				return $request[ 'avatar_url' ];
			}
		}

		return FALSE;
	}
}