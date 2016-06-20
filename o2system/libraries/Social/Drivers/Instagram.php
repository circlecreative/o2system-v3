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
use MetzWeb\Instagram\Instagram as SDK;

/**
 * Instagram Driver
 *
 * @package       social
 * @subpackage    drivers
 * @category      driver class
 * @author        Steeven Andrian Salim
 */
class Instagram extends Driver
{
	protected $_api_url = 'https://api.instagram.com/';

	public function getAuthorizeUrl($callback )
	{
		$this->_config[ 'app_callback_url' ] = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );

		$sdk = new SDK( array(
			                'apiKey'      => $this->_config[ 'app_key' ],
			                'apiSecret'   => $this->_config[ 'app_secret' ],
			                'apiCallback' => $this->_config[ 'app_callback_url' ]
		                ) );

		$controller =& get_instance();
		$session->setUserdata( $this->_session_name, [ 'app_callback_url' => $this->_config[ 'app_callback_url' ] ] );

		return $sdk->getLoginUrl();
	}

	public function setConnection()
	{
		$controller =& get_instance();
		$request_token = $session->userdata( $this->_session_name );

		$sdk = new SDK( array(
			                'apiKey'      => $this->_config[ 'app_key' ],
			                'apiSecret'   => $this->_config[ 'app_secret' ],
			                'apiCallback' => $request_token[ 'app_callback_url' ]
		                ) );

		if( $controller->input->get( 'code' ) )
		{
			$access_token = $sdk->getOAuthToken( $controller->input->get( 'code' ) );

			if( ! empty( $access_token ) )
			{
				// Set Session Access Data
				static::$_session[ 'access' ] = new \stdClass();
				static::$_session[ 'access' ]->oauth_token = $access_token->access_token;
				static::$_session[ 'access' ]->oauth_callback = $request_token[ 'app_callback_url' ];

				// Set Session User Data
				static::$_session[ 'user' ] = new \stdClass();
				static::$_session[ 'user' ]->id_user = $access_token->user->id;
				static::$_session[ 'user' ]->username = $access_token->user->username;

				static::$_session[ 'user' ]->name_full = $access_token->user->full_name;
				static::$_session[ 'user' ]->description = $access_token->user->bio;
				static::$_session[ 'user' ]->avatar = $access_token->user->profile_picture;
				static::$_session[ 'user' ]->cover = NULL;

				$session->setUserdata( $this->_session_name, static::$_session );

				return TRUE;
			}

		}

		return FALSE;
	}

	public function requestApi($path = NULL, $params = array(), $method = 'get' )
	{
		if( $this->is_connected() )
		{
			$sdk = new SDK( array(
				                'apiKey'      => $this->_config[ 'app_key' ],
				                'apiSecret'   => $this->_config[ 'app_secret' ],
				                'apiCallback' => static::$_session[ 'access' ]->oauth_callback
			                ) );

			$method = strtolower( $method ) . $path;

			$sdk->setAccessToken( static::$_session[ 'access' ]->oauth_token );

			$result = call_user_func_array( array( $sdk, $method ), $params );

			if( $result->meta->code === 200 )
			{
				return $result->data;
			}
		}

		return FALSE;
	}

	protected function _buildApiGuid()
	{
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		                mt_rand( 0, 65535 ),
		                mt_rand( 0, 65535 ),
		                mt_rand( 0, 65535 ),
		                mt_rand( 16384, 20479 ),
		                mt_rand( 32768, 49151 ),
		                mt_rand( 0, 65535 ),
		                mt_rand( 0, 65535 ),
		                mt_rand( 0, 65535 ) );
	}

	protected function _buildUserAgent()
	{
		$resolutions = array( '720x1280', '320x480', '480x800', '1024x768', '1280x720', '768x1024', '480x320' );
		$versions = array( 'GT-N7000', 'SM-N9000', 'GT-I9220', 'GT-I9100' );
		$dpis = array( '120', '160', '320', '240' );

		$ver = $versions[ array_rand( $versions ) ];
		$dpi = $dpis[ array_rand( $dpis ) ];
		$res = $resolutions[ array_rand( $resolutions ) ];

		return 'Instagram 4.' . mt_rand( 1, 2 ) . '.' . mt_rand( 0, 2 ) . ' Android (' . mt_rand( 10, 11 ) . '/' . mt_rand( 1, 3 ) . '.' . mt_rand( 3, 5 ) . '.' . mt_rand( 0, 5 ) . '; ' . $dpi . '; ' . $res . '; samsung; ' . $ver . '; ' . $ver . '; smdkc210; en_US)';
	}

	protected function _buildSignature($data )
	{
		return hash_hmac( 'sha256', $data, 'b4a23f5e39b5929e0666ac5de94c89d1618a2916' );
	}

	public function getProfile()
	{
		return $this->requestApi( 'User' );
	}

	public function getFeeds($page = 1, $count = 10, array $params = array() )
	{
		$count = $count + 1;

		return $this->requestApi( 'UserFeed', [ $count ] );
	}

	public function postFeed($feed, array $params = array() )
	{
		return FALSE;
	}

	public function postLink($status, $link )
	{
		return FALSE;
	}


	public function pullLikes($count = 5, array $options = array() )
	{
		if( isset( $this->_is_connected ) )
		{
			$uri = 'v1/users/self/media/liked';
			if( ! empty( $options ) )
			{
				$parameters = array_change_key_case( $options, CASE_UPPER );
			}

			$parameters[ 'access_token' ] = $this->_session->access_token;
			$parameters[ 'count' ] = $count;

			$result = $this->_api_request( $uri . http_build_query( $parameters ) );

			return $result->data;
		}

		return FALSE;
	}

	public function postMedia($media, $options = array() )
	{
		if( ! empty( $media ) )
		{
			if( ! isset( $options[ 'password' ] ) )
			{
				return $this->_parent->set_error( 'This method required a valid user password', 407 );
			}

			if( file_exists( $media ) )
			{
				$guid = $this->_buildApiGuid();

				$data = json_encode(
					array(
						'device_id'    => 'android-' . $guid,
						'guid'         => $guid,
						//'username'     => $this->_session->user->username,
						//'password'     => $options[ 'password' ],
						'access_token' => $this->_session->access_token,
						'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
					)
				);

				$signature = $this->_buildSignature( $data );
				$data = 'signed_body=' . $signature . '.' . urlencode( $data ) . '&ig_sig_key_version=4';

				$login = $this->_api_request( 'accounts/login/', 'post', $data );

				if( strpos( $login[ 1 ], 'error' ) )
				{
					return $this->_parent->set_error( 'Couldn\'t authorize with provided credentials. There\'s a chance that this proxy/ip is blocked', 401 );
				}
				else
				{
					if( empty( $login[ 1 ] ) )
					{
						return $this->_parent->set_error( 'Couldn\'t authorize with provided credentials', 401 );
					}
					else
					{
						$access = @json_decode( $login[ 1 ], TRUE );

						if( empty( $access ) )
						{
							return $this->_parent->set_error( 'Couldn\'t authorize with provided credentials', 401 );
						}
						else
						{
							$media = realpath( $media );

							$data = array(
								'device_timestamp' => time(),
								'photo'            => '@' . $media
							);

							$post = $this->_api_request( 'media/upload/', 'post', $data, TRUE );

							if( empty( $post[ 1 ] ) )
							{
								return $this->_parent->set_error( 'Failed to upload Instagram Media', 406 );
							}
							else
							{
								$upload = @json_decode( $post[ 1 ], TRUE );

								if( empty( $upload ) )
								{
									return $this->_parent->set_error( 'Couldn\'t decode Instagram upload response', 204 );
								}
								else
								{
									if( isset( $upload[ 'status' ] ) AND $upload[ 'status' ] === 'ok' )
									{
										if( isset( $options[ 'caption' ] ) )
										{
											$caption = preg_replace( "/\r|\n/", "", $options[ 'caption' ] );

											$data = array(
												'device_id'        => 'android-' . $guid,
												'guid'             => $guid,
												'media_id'         => $access[ 'media_id' ],
												'caption'          => $caption,
												'device_timestamp' => time(),
												'source_type'      => 5,
												'filter_type'      => 0,
												'extra'            => '{}',
												'Content-Type'     => 'application/x-www-form-urlencoded; charset=UTF-8'
											);

											$signature = $this->_buildSignature( $data );
											$post_data = 'signed_body=' . $signature . '.' . urlencode( $data ) . '&ig_sig_key_version=4';

											$configure = $this->_api_request( 'media/configure/', 'post', $post_data, TRUE );

											if( empty( $configure[ 1 ] ) )
											{
												return $this->_parent->set_error( 'Couldn\'t decode Instagram media configure response', 204 );
											}
											else
											{
												if( strpos( $configure[ 1 ], "login_required" ) )
												{
													return $this->_parent->set_error( 'Couldn\'t authorize with provided credentials. There\'s a chance that this proxy/ip is blocked', 401 );
												}
												else
												{
													$login = @json_decode( $configure[ 1 ], TRUE );

													if( isset( $login[ 'status' ] ) AND $login[ 'status' ] != 'fail' )
													{
														return TRUE;
													}
												}
											}
										}
									}
									else
									{
										return $this->_parent->set_error( 'Couldn\'t decode Instagram upload response', 204 );
									}
								}
							}
						}
					}
				}
			}
		}

		return FALSE;
	}

}