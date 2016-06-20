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

namespace O2System\Libraries\Social\Drivers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use Api\Metadata\Member\Socialmedia\Account;
use O2System\CURL\Interfaces\Method;
use O2System\Exception;
use O2System\Glob\ArrayObject;
use O2System\Glob\Interfaces\DriverInterface;
use O2System\Libraries\Social\Metadata\Access;
use O2System\Libraries\Social\Metadata\Connection;
use O2System\Libraries\Social\Metadata\Profile;

/**
 * Facebook Driver
 *
 * @package       social
 * @subpackage    drivers
 * @category      driver class
 * @author        Steeven Andrian Salim
 */
class Facebook extends DriverInterface
{
	protected $_url = [
		'share' => 'https://www.facebook.com/sharer/sharer.php?u=%s',
		'api'   => 'https://graph.facebook.com/?id=%s',
	];

	/**
	 * @type Connection
	 */
	protected $_connection;

	public function __construct()
	{
		if ( ! class_exists( '\Facebook\Facebook' ) )
		{
			throw new Exception( 'Required Facebook PHP SDK' );
		}
	}

	public function getShareLink( $link = NULL )
	{
		$link = isset( $link ) ? $link : current_url();

		return sprintf( $this->_url[ 'share' ], urlencode( $link ) );
	}

	public function getAuthorizeLink( $callback_url )
	{
		$this->_config[ 'app_callback_url' ] = strpos( $callback_url, 'http' ) !== FALSE ? $callback_url : base_url( $callback_url );

		$sdk = new \Facebook\Facebook(
			[
				'app_id'                => $this->_config[ 'app_id' ],
				'app_secret'            => $this->_config[ 'app_secret' ],
				'default_graph_version' => $this->_config[ 'api_version' ],
			] );

		$helper = $sdk->getRedirectLoginHelper();

		return $helper->getLoginUrl( $this->_config[ 'app_callback_url' ], $this->_config[ 'app_permissions' ] );
	}

	public function connect()
	{
		$sdk = new \Facebook\Facebook(
			[
				'app_id'                => $this->_config[ 'app_id' ],
				'app_secret'            => $this->_config[ 'app_secret' ],
				'default_graph_version' => $this->_config[ 'api_version' ],
			] );

		$helper = $sdk->getRedirectLoginHelper();

		// Try to get FB Access Token
		try
		{
			$access_token = $helper->getAccessToken();

			if ( ! empty( $access_token ) )
			{
				$connection = new Connection(
					[
						'access'  => new Access(
							[
								'token' => (string) $access_token,
							] ),
						'profile' => new Profile(),
					] );

				$profile = $this->getProfile();

				// Set Session User Data
				$connection[ 'profile' ]->id_user_account = $profile->id;
				$connection[ 'profile' ]->username        = $profile->email;

				$connection[ 'profile' ]->name        = new ArrayObject( explode( ' ', $profile->name ) );
				$connection[ 'profile' ]->name->full  = $profile->name;
				$connection[ 'profile' ]->description = $profile->bio;
				$connection[ 'profile' ]->avatar      = $profile->picture[ 'data' ][ 'url' ];
				$connection[ 'profile' ]->cover       = $profile->cover[ 'source' ];

				\O2System::Session()->setUserdata( 'social_facebook_connection', $connection );

				return TRUE;
			}
		}
		catch ( \Facebook\Exceptions\FacebookResponseException $e )
		{
			throw new Exception( 'Social Facebook Graph returned an error: ' . $e->getMessage() );
		}
		catch ( \Facebook\Exceptions\FacebookSDKException $e )
		{
			throw new Exception( 'Social Facebook SDK returned an error: ' . $e->getMessage() );
		}

		return FALSE;
	}

	public function apiRequest( $path = NULL, $params = [ ], $method = Method::GET )
	{
		if ( $this->isConnected() )
		{
			$sdk = new \Facebook\Facebook(
				[
					'app_id'                => $this->_config[ 'app_id' ],
					'app_secret'            => $this->_config[ 'app_secret' ],
					'default_graph_version' => $this->_config[ 'api_version' ],
				] );

			$method = strtolower( $method );

			try
			{
				$result = $sdk->{$method}( $path, static::$_session[ 'access' ]->oauth_token );

				if ( $result->getDecodedBody() )
				{
					return $result->getDecodedBody();
				}
			}
			catch ( \Facebook\Exceptions\FacebookResponseException $e )
			{
				throw new \Exceptions( 'Graph returned an error: ' . $e->getMessage() );
			}
			catch ( Facebook\Exceptions\FacebookSDKException $e )
			{
				throw new \Exceptions( 'Facebook SDK returned an error: ' . $e->getMessage() );
			}
		}

		return FALSE;
	}

	protected function _build_field_options( $options = [ ] )
	{
		foreach ( $options as $key => $value )
		{
			$query[] = $key . '(' . $value . ')';
		}

		return implode( '.', $query );
	}

	protected function _build_fields( $fields = [ ] )
	{
		return implode( ',', $fields );
	}

	protected function _build_post( $data = [ ] )
	{
		foreach ( $data as $key => $value )
		{
			$fields[ $key ] = urlencode( $value );
		}

		$fields_string = '';

		foreach ( $fields as $key => $value )
		{
			$fields_string .= $key . '=' . $value . '&';
		}

		return rtrim( $fields_string, '&' );
	}

	public function get_profile()
	{
		return (object) $this->apiRequest( '/me?fields=id,gender,hometown,first_name,middle_name,last_name,name,email,birthday,cover,picture,bio,address' );
	}

	public function get_feeds( $page = 1, $count = 10, array $params = [ ] )
	{
		$controller =& get_instance();

		$result = $this->apiRequest( '/me/feed?limit=' . $count );

		if ( isset( $result[ 'data' ] ) )
		{
			parse_str( parse_url( $result[ 'paging' ][ 'previous' ], PHP_URL_QUERY ), $previous );
			parse_str( parse_url( $result[ 'paging' ][ 'next' ], PHP_URL_QUERY ), $next );

			static::$_session[ 'feeds_paging' ][ $page - 1 ] = (object) [
				'page_num'   => $page - 1,
				'page_token' => $previous[ '__paging_token' ],
				'page_since' => $previous[ 'since' ],
			];

			static::$_session[ 'feeds_paging' ][ $page ] = (object) [
				'page_num'   => $page,
				'page_token' => $previous[ '__paging_token' ],
				'page_since' => $previous[ 'since' ],
			];

			static::$_session[ 'feeds_paging' ][ $page + 1 ] = (object) [
				'page_num'   => $page + 1,
				'page_token' => $next[ '__paging_token' ],
				'page_until' => $next[ 'until' ],
			];

			$session->setUserdata( $this->_session_name, static::$_session );

			return $result[ 'data' ];
		}

		return FALSE;
	}

	public function pull_albums( $count = 5, array $options = [ ] )
	{
		if ( isset( $this->is_connected ) )
		{
			if ( ! empty( $options ) )
			{
				$parameters = array_change_key_case( $options, CASE_LOWER );
			}

			$uri                          = '/' . $this->_config[ 'api_version' ] . '/me/albums?';
			$parameters[ 'access_token' ] = $this->_session->access_token;

			$parameters[ 'limit' ]  = $count;
			$parameters[ 'format' ] = 'json';
			$parameters[ 'method' ] = 'get';

			$result = $this->apiRequest( $uri . http_build_query( $parameters ) );

			return $result->data;
		}

		return FALSE;
	}

	public function pull_album( $album_id, $count = 5, array $options = [ ] )
	{
		if ( isset( $this->is_connected ) )
		{
			if ( ! empty( $options ) )
			{
				$parameters = array_change_key_case( $options, CASE_LOWER );
			}

			$uri                          = '/' . $this->_config[ 'api_version' ] . '/' . $album_id . '/photos?';
			$parameters[ 'access_token' ] = $this->_session->access_token;

			$parameters[ 'limit' ]  = $count;
			$parameters[ 'format' ] = 'json';
			$parameters[ 'method' ] = 'get';

			$result = $this->apiRequest( $uri . http_build_query( $parameters ) );

			return $result->data;
		}

		return FALSE;
	}

	public function pull_photos( $count = 5, array $options = [ ] )
	{
		if ( isset( $this->is_connected ) )
		{
			if ( ! empty( $options ) )
			{
				$parameters = array_change_key_case( $options, CASE_LOWER );
			}

			$uri                          = '/' . $this->_config[ 'api_version' ] . '/me/photos?';
			$parameters[ 'access_token' ] = $this->_session->access_token;

			$parameters[ 'limit' ]  = $count;
			$parameters[ 'format' ] = 'json';
			$parameters[ 'method' ] = 'get';

			$result = $this->apiRequest( $uri . http_build_query( $parameters ) );

			return $result->data;
		}

		return FALSE;
	}


	public function pull_likes( $count = 5, array $options = [ ] )
	{

	}

	public function post_feed( $feed, array $params = [ ] )
	{
		if ( ! empty( $post ) )
		{
			if ( isset( $this->is_connected ) )
			{
				$uri                    = '/' . $this->_config[ 'api_version' ] . '/me/feed';
				$post[ 'access_token' ] = $this->_session->access_token;

				if ( isset( $post[ 'actions' ] ) )
				{
					if ( ! is_object( $post[ 'actions' ] ) )
					{
						show_error( 'Facebook Feed Actions Variable Type Must Be an Object' );
					}
				}

				if ( isset( $post[ 'privacy' ] ) )
				{
					if ( ! is_object( $post[ 'privacy' ] ) )
					{
						show_error( 'Facebook Feed Privacy Variable Type Must Be an Object' );
					}
				}

				if ( isset( $post[ 'targeting' ] ) )
				{
					if ( ! is_object( $post[ 'targeting' ] ) )
					{
						show_error( 'Facebook Feed Targeting Variable Type Must Be an Object' );
					}
				}

				$result = $this->apiRequest( $uri, $post );

				return $result;
			}
		}

		return FALSE;
	}

	public function post_message( $message )
	{
		if ( ! empty( $message ) )
		{
			if ( isset( $this->is_connected ) )
			{
				return $this->post_feed( [ 'message' => $message ] );
			}
		}

		return FALSE;
	}

	public function post_link( $status, $link )
	{
		if ( ! empty( $link ) )
		{
			if ( isset( $this->is_connected ) )
			{
				if ( ! isset( $link[ 'link' ] ) )
				{
					show_error( 'Facebook link post doesn\'t have valid Link URL' );
				}

				return $this->post_feed( $link );
			}
		}

		return FALSE;
	}

	public function post_media( $status, $media )
	{
		if ( ! empty( $photo ) )
		{
			if ( isset( $this->is_connected ) )
			{
				if ( empty( $album ) )
				{
					$uri = '/' . $this->_config[ 'api_version' ] . '/me/photos?';
				}
				else
				{
					$uri = '/' . $this->_config[ 'api_version' ] . '/me/' . $album_id . '/photos?';
				}

				if ( is_string( $photo ) )
				{
					$picture = $photo;
					unset( $photo );
					$photo[ 'picture' ] = $picture;
				}

				//print_out($photo);

				if ( ! isset( $photo[ 'picture' ] ) )
				{
					show_error( 'Facebook photo post doesn\'t have valid image file path' );
				}

				$picture = $photo[ 'picture' ];
				unset( $photo[ 'picture' ] );

				$picture = realpath( $picture );

				if ( file_exists( $picture ) )
				{
					$photo[ 'image' ] = $picture;
				}

				$parameters[ 'access_token' ] = $this->_session->access_token;

				$result = $this->apiRequest( $uri . http_build_query( $parameters ), $photo, TRUE );

				return $result;
			}
		}

		return FALSE;
	}

	public function post_video( $video )
	{
		if ( ! empty( $video ) )
		{
			if ( isset( $this->is_connected ) )
			{
				$uri = '/' . $this->_config[ 'api_version' ] . '/me/photos?';

				if ( ! isset( $video[ 'video' ] ) )
				{
					show_error( 'Facebook video post doesn\'t have valid video file path' );
				}

				$file = $video[ 'picture' ];
				unset( $video[ 'picture' ] );

				$file = realpath( $file );

				if ( file_exists( $file ) )
				{
					$video[ 'file' ] = '@' . $file;
				}

				$parameters[ 'access_token' ] = $this->_session->access_token;

				$result = $this->apiRequest( $uri . http_build_query( $parameters ), $video, TRUE );

				return $result;
			}
		}
	}

	public function delete_post( $post_id )
	{
		if ( ! empty( $post_id ) )
		{
			if ( isset( $this->is_connected ) )
			{
				$uri = '/' . $this->_config[ 'api_version' ] . '/' . $post_id;

				$post[ 'access_token' ] = $this->_session->access_token;
				$post[ 'method' ]       = 'delete';

				$result = $this->apiRequest( $uri, $post );

				return $result;
			}
		}

		return FALSE;
	}

	public function disconnect()
	{
		if ( ! empty( $post_id ) )
		{
			if ( isset( $this->is_connected ) )
			{
				$uri = '/' . $this->_config[ 'api_version' ] . '/permissions?';

				$post[ 'access_token' ] = $this->_session->access_token;
				$post[ 'method' ]       = 'delete';

				$result = $this->apiRequest( $uri, $post );

				return $result;
			}
		}

		return FALSE;
	}
}