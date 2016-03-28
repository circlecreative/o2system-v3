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

namespace O2System\Libraries\Social;
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Libraries\Social\Factory\Driver;
use O2System\Libraries\Social\Factory\Session;

/**
 * Facebook SDK Driver
 *
 * @package       social
 * @subpackage    drivers
 * @category      driver class
 * @author        Steeven Andrian Salim
 */
class Google extends Driver
{
	protected $_api_url = 'https://www.googleapis.com/plus';
	protected $_client  = NULL;

	/**
	 * Driver Constructor
	 *
	 * @access  public
	 */
	public function initialize()
	{
		$controller =& get_instance();
		$load->helper( 'url' );

		$this->_session_name = 'social_' . strtolower( get_namespace_class( get_called_class() ) );

		if( ! isset( static::$_session ) )
		{
			static::$_session = new Session( $this->_session_name );
		}

		$this->_client = new \Google_Client();
	}

	public function get_authorize_url( $callback )
	{
		$this->_config[ 'app_callback_url' ] = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );

		$this->_client->setClientId( $this->_config[ 'app_id' ] );
		$this->_client->setClientSecret( $this->_config[ 'app_secret' ] );
		$this->_client->setRedirectUri( $this->_config[ 'app_callback_url' ] );
		$this->_client->setScopes( $this->_config[ 'app_permissions' ] );

		$controller =& get_instance();
		$session->set_userdata( $this->_session_name, [ 'app_callback_url' => $this->_config[ 'app_callback_url' ] ] );

		return $this->_client->createAuthUrl();
	}

	public function set_connection()
	{
		$controller =& get_instance();
		$request_token = $session->userdata( $this->_session_name );

		$this->_client->setClientId( $this->_config[ 'app_id' ] );
		$this->_client->setClientSecret( $this->_config[ 'app_secret' ] );
		$this->_client->setRedirectUri( $request_token[ 'app_callback_url' ] );
		$this->_client->setScopes( $this->_config[ 'app_permissions' ] );

		if( $controller->input->get( 'code' ) )
		{
			$this->_client->authenticate( $controller->input->get( 'code' ) );

			$access_token = $this->_client->getAccessToken();

			if( ! empty( $access_token ) )
			{
				// Set Session Access Data
				static::$_session[ 'access' ] = new \stdClass();
				static::$_session[ 'access' ]->oauth_token = $access_token;
				static::$_session[ 'access' ]->oauth_callback = $request_token[ 'app_callback_url' ];

				$profile = $this->get_profile();

				// Set Session User Data
				static::$_session[ 'user' ] = new \stdClass();
				static::$_session[ 'user' ]->id_user = $profile->id;
				static::$_session[ 'user' ]->username = $profile->email;


				static::$_session[ 'user' ]->name_full = $profile->name;
				static::$_session[ 'user' ]->description = NULL;
				static::$_session[ 'user' ]->avatar = $profile->picture;
				static::$_session[ 'user' ]->cover = NULL;

				$session->set_userdata( $this->_session_name, static::$_session );

				return TRUE;
			}
		}

		return FALSE;
	}

	public function request_api( $path = NULL, $params = array(), $method = 'get' )
	{
		if( $this->is_connected() )
		{
			$this->_client->setClientId( $this->_config[ 'app_id' ] );
			$this->_client->setClientSecret( $this->_config[ 'app_secret' ] );
			$this->_client->setRedirectUri( static::$_session[ 'access' ]->oauth_callback );
			$this->_client->setScopes( $this->_config[ 'app_permissions' ] );
			$this->_client->setAccessToken( static::$_session[ 'access' ]->oauth_token );

			return TRUE;
		}

		return FALSE;
	}

	public function get_profile()
	{
		if( $this->request_api() )
		{
			$plus = new \Google_Service_Oauth2( $this->_client );

			return $plus->userinfo->get();
		}

		return FALSE;
	}

	public function get_feeds( $page = 1, $count = 10, array $params = array() )
	{
		if( $this->request_api() )
		{
			$plus = new \Google_Service_Plus( $this->_client );
			$activities = $plus->activities->listActivities( static::$_session[ 'user' ]->id_user, 'public' );

			return $activities->getItems();
		}

		return FALSE;
	}

	public function post_feed( $feed, array $params = array() )
	{

	}

	public function post_link( $status, $link )
	{

	}

	public function post_media( $status, $media )
	{

	}
}