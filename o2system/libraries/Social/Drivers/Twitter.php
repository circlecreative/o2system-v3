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

namespace O2System\Libraries\Social;
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Libraries\Social\Factory\Driver;
use Abraham\TwitterOAuth\TwitterOAuth as SDK;

/**
 * Twitter Driver
 *
 * @package       social
 * @subpackage    drivers
 * @category      driver class
 * @author        Steeven Andrian Salim
 */
class Twitter extends Driver
{
	protected $_api_url = 'https://api.twitter.com/';

	public function get_authorize_url( $callback )
	{
		$this->_config[ 'app_callback_url' ] = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );

		$sdk = new SDK(
			$this->_config[ 'app_key' ],
			$this->_config[ 'app_secret' ]
		);

		$request_token = $sdk->oauth( 'oauth/request_token', array(
			'oauth_callback' => $this->_config[ 'app_callback_url' ]
		) );

		$controller =& get_instance();

		$session->set_userdata( $this->_session_name, $request_token );

		return $sdk->url( 'oauth/authorize', array(
			'oauth_token' => $request_token[ 'oauth_token' ]
		) );
	}

	public function set_connection()
	{
		$controller =& get_instance();

		$request_token = $session->userdata( $this->_session_name );

		if( $request_token[ 'oauth_token' ] !== $controller->input->get( 'oauth_token' ) )
		{
			throw new \Exception( 'Invalid Twitter OAuth Token' );
		}

		$sdk = new SDK(
			$this->_config[ 'app_key' ],
			$this->_config[ 'app_secret' ],
			$request_token[ 'oauth_token' ],
			$request_token[ 'oauth_token_secret' ]
		);

		$access_token = $sdk->oauth( "oauth/access_token", [ "oauth_verifier" => $controller->input->get( 'oauth_verifier' ) ] );

		if( ! empty( $access_token ) )
		{
			// Set Session Access Data
			static::$_session[ 'access' ] = new \stdClass();
			static::$_session[ 'access' ]->oauth_token = $access_token[ 'oauth_token' ];
			static::$_session[ 'access' ]->oauth_token_secret = $access_token[ 'oauth_token_secret' ];

			// Set Session User Data
			static::$_session[ 'user' ] = new \stdClass();
			static::$_session[ 'user' ]->id_user = $access_token[ 'user_id' ];
			static::$_session[ 'user' ]->username = $access_token[ 'screen_name' ];

			$profile = $this->get_profile();
			static::$_session[ 'user' ]->name_full = $profile->name;
			static::$_session[ 'user' ]->description = $profile->description;
			static::$_session[ 'user' ]->avatar = $profile->profile_image_url;
			static::$_session[ 'user' ]->cover = $profile->profile_banner_url . '/1500x500';

			$session->set_userdata( $this->_session_name, static::$_session );

			return TRUE;
		}

		return FALSE;
	}

	public function request_api( $path = NULL, $params = array(), $method = 'get' )
	{
		if( $this->is_connected() )
		{
			$method = strtolower( $method );

			$sdk = new SDK(
				$this->_config[ 'app_key' ],
				$this->_config[ 'app_secret' ],
				static::$_session[ 'access' ]->oauth_token,
				static::$_session[ 'access' ]->oauth_token_secret
			);

			// format parameters
			$params = array_change_key_case( $params, CASE_LOWER );

			return $sdk->{$method}( $path, $params );
		}

		return FALSE;
	}

	public function get_profile()
	{
		return $this->request_api( 'account/verify_credentials' );
	}

	public function get_feeds( $page = 1, $count = 10, array $params = array() )
	{
		$path = isset( $params[ 'path' ] ) ? $params[ 'path' ] : 'user_timeline';

		if( isset( $params[ 'since_id' ] ) )
		{
			if( ! is_numeric( $params[ 'since_id' ] ) )
			{
				$params[ 'since_id' ] = strtotime( $params[ 'since_id' ] );
			}
		}

		$params[ 'count' ] = $count;

		return $this->request_api( 'statuses/' . $path, $params );
	}

	public function post_feed( $feed, array $params = array() )
	{
		if( strlen( $feed ) > 136 )
		{
			$feed = substr( $feed, 0, 136 );
			$feed = substr( $feed, 0, strrpos( $feed, ' ' ) ) . ' ...';
		}

		$params[ 'status' ] = $feed;

		return $this->request_api( 'statuses/update', $params, 'post' );
	}

	public function post_link( $status, $link )
	{
		if( ! empty( $link ) )
		{
			if( filter_var( $link, FILTER_VALIDATE_URL ) === FALSE )
			{
				show_error( 'Twitter link post doesn\'t have valid Link URL' );
			}

			$message = substr( $status, 0, 100 );
			$status = $message . ' — ' . $link;

			return $this->post_feed( $status );
		}

		return FALSE;
	}

	public function post_media( $status, $media )
	{
		if( $this->is_connected() )
		{
			$method = strtolower( $method );

			$sdk = new SDK(
				$this->_config[ 'app_key' ],
				$this->_config[ 'app_secret' ],
				static::$_session[ 'access' ]->oauth_token,
				static::$_session[ 'access' ]->oauth_token_secret
			);

			if( is_array( $media ) )
			{
				foreach( $media as $file )
				{
					if( file_exists( $file ) )
					{
						$upload = $sdk->upload( 'media/upload', [ 'media' => $file ] );
						$uploads[ ] = $upload->media_id_string;
					}
				}
			}
			elseif( file_exists( $media ) )
			{
				$upload = $sdk->upload( 'media/upload', [ 'media' => $media ] );
				$uploads[ ] = $upload->media_id_string;
			}

			if( ! empty( $uploads ) )
			{
				$params[ 'media_ids' ] = implode( ',', $uploads );

				return $this->post_feed( $status, $params );
			}
		}

		return FALSE;
	}
}