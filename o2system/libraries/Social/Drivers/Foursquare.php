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

namespace O2System\Libraries\Social\Drivers;
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\Interfaces\DriverInterface;

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
	protected $_api_url = 'https://api.foursquare.com/';

	public function getAuthorizeUrl($callback )
	{
		$this->_config[ 'app_callback_url' ] = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );

		$params = array(
			'response_type' => 'code',
			'client_id'     => $this->_config[ 'app_id' ],
			'redirect_uri'  => $this->_config[ 'app_callback_url' ]
		);

		$controller =& get_instance();
		$session->setUserdata( $this->_session_name, [ 'app_callback_url' => $this->_config[ 'app_callback_url' ] ] );

		return CURL::generate_url( 'https://foursquare.com/', 'oauth2/authenticate', $params );
	}

	public function setConnection()
	{
		$controller =& get_instance();
		$request_token = $session->userdata( $this->_session_name );

		if( $controller->input->get( 'code' ) )
		{
			$params = array(
				'grant_type'    => 'authorization_code',
				'code'          => $controller->input->get( 'code' ),
				'redirect_uri'  => $request_token[ 'app_callback_url' ],
				'client_id'     => $this->_config[ 'app_id' ],
				'client_secret' => $this->_config[ 'app_secret' ]
			);

			$request = CURL::get( 'https://foursquare.com/', 'oauth2/access_token', $params );

			if( $request->meta->http_code === 200 )
			{
				// Set Session Access Data
				static::$_session[ 'access' ] = new \stdClass();
				static::$_session[ 'access' ]->oauth_token = $request->body->access_token;

				$profile = $this->getProfile();

				// Set Session User Data
				static::$_session[ 'user' ] = new \stdClass();
				static::$_session[ 'user' ]->id_user = $profile->id;
				static::$_session[ 'user' ]->username = $profile->contact->email;
				static::$_session[ 'user' ]->name_full = trim( $profile->firstName . ' ' . $profile->lastName );
				static::$_session[ 'user' ]->description = $profile->bio;
				static::$_session[ 'user' ]->avatar = $profile->photo->prefix . str_replace( '/', '', $profile->photo->suffix );
				static::$_session[ 'user' ]->cover = NULL;

				$session->setUserdata( $this->_session_name, static::$_session );

				return TRUE;
			}
			elseif( $request->meta->http_code === 400 )
			{
				//throw new \Exception($request->headers->error);
				$this->_parent->set_error( $request->headers->error, FALSE );
			}
		}

		return FALSE;
	}

	public function requestApi($path = NULL, $params = array(), $method = 'get' )
	{
		if( $this->is_connected() )
		{
			$path = strpos( $path, 'http' ) !== FALSE ? $path : $this->_api_url . $this->_config[ 'api_version' ] . '/' . $path;

			$params[ 'oauth_token' ] = static::$_session[ 'access' ]->oauth_token;
			$params[ 'v' ] = date( 'Ymd' );
			$params[ 'm' ] = 'foursquare';

			//CURL::set_header('Host', 'api.linkedin.com');
			//CURL::set_header('Connection', 'Keep-Alive');
			//CURL::set_header('Authorization', 'Bearer '.static::$_session['access']->oauth_token . "\r\n" . "x-li-format: json\r\n");
			$request = CURL::get( $path, array(), $params );

			if( $request->meta->http_code === 200 )
			{
				return $request->body->response;
			}
			else
			{
				if( isset( $request->headers->meta->errorDetail ) )
				{
					$this->_parent->set_error( $request->headers->meta->errorDetail, FALSE );
				}
				elseif( isset( $request->body->message ) )
				{
					$this->_parent->set_error( $request->body->message, FALSE );
				}
			}
		}

		return FALSE;
	}

	public function getProfile()
	{
		$result = $this->requestApi( 'users/self' );

		if( isset( $result->user ) )
		{
			return $result->user;
		}

		return FALSE;
	}

	public function getFeeds($page = 1, $count = 10, array $params = array() )
	{
		$params[ 'offset' ] = $page === 1 ? 0 : $page * $count;
		$params[ 'limit' ] = $params;

		$result = $this->requestApi( 'users/self/checkins', $params );

		if( isset( $result->checkins ) )
		{
			return $result->checkins->items;
		}

		return FALSE;
	}

	public function postFeed($feed, array $params = array() )
	{
		return FALSE;
	}

	public function postLink($status, $link )
	{
		return FALSE;
	}

	public function postMedia($status, $media )
	{
		return FALSE;
	}
}