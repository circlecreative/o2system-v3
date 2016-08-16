<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Controllers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\Controller;
use O2System\Core\File\Interfaces\MIME;
use O2System\Core\SPL\ArrayObject;
use O2System\Core\Request\Response\File;
use O2System\Core\Request\Response\Header;

abstract class Restful extends Controller
{
	/**
	 * List of whitelist request URI string segments
	 *
	 * All listed URI string segments that specified will be automatically allowed
	 * without validation.
	 *
	 * @type array
	 */
	protected $accessControlWhitelistSegments = [ ];

	/**
	 * Push Access-Control-Allow-Origin flag
	 *
	 * Used for push 'Access-Control-Allow-Origin: *' to header
	 * If you're set this flag into FALSE you must push it via server configuration
	 *
	 * APACHE via .htaccess
	 * IIS via .webconfig
	 * Nginx via config
	 *
	 * @type string
	 */
	protected $isPushAccessControlAllowOrigin = TRUE;

	/**
	 * Access-Control-Allow-Origin
	 *
	 * Used for indicates whether a resource can be shared based by
	 * returning the value of the Origin request header, "*", or "null" in the response.
	 *
	 * @type string
	 */
	protected $accessControlAllowOrigin = '*';

	/**
	 * Access-Control-Allow-Credentials
	 *
	 * Used for indicates whether the response to request can be exposed when the omit credentials flag is unset.
	 * When part of the response to a preflight request it indicates that the actual request can include user
	 * credentials.
	 *
	 * @type bool
	 */
	protected $accessControlAllowCredentials = TRUE;

	/**
	 * Access-Control-Allow-Methods
	 *
	 * Used for indicates, as part of the response to a preflight request,
	 * which methods can be used during the actual request.
	 *
	 * @type array
	 */
	protected $accessControlAllowMethods = [
		'GET', // common request
		'POST', // used for create, update request
		'PUT', // used for upload files request
		'DELETE', // used for delete request
		'OPTIONS', // used for preflight request
	];

	/**
	 * Access-Control-Allow-Headers
	 *
	 * Used for indicates, as part of the response to a preflight request,
	 * which header field names can be used during the actual request.
	 *
	 * @type int
	 */
	protected $accessControlAllowHeaders = [
		'Origin',
		'Access-Control-Request-Method',
		'Access-Control-Request-Headers',
		'API-Authenticate', // API-Authenticate: api_key="xxx", api_secret="xxx", api_signature="xxx"
		'X-Api-Token',
		'X-Web-Token', // X-Web-Token: xxx (json-web-token)
		'X-Csrf-Token',
		'X-Xss-Token',
		'X-Request-ID',
		'X-Requested-With',
		'X-Requested-Result',
	];

	/**
	 * Access-Control-Allow-Headers
	 *
	 * Used for indicates, as part of the response to a preflight request,
	 * which header field names can be used during the actual request.
	 *
	 * @type int
	 */
	protected $accessControlAllowContentTypes = [
		'application/json',
		'application/xml',
	];

	/**
	 * Access-Control-Max-Age
	 *
	 * Used for indicates how long the results of a preflight request can be cached in a preflight result cache
	 *
	 * @type int
	 */
	protected $accessControlMaxAge = 86400;

	/**
	 * Access-Control-Last-Polling-Call-Timestamp
	 *
	 * Used for indicates last long polling call timestamp
	 *
	 * @type int Time
	 */
	protected $accessControlLastPollingCallTimestamp;

	/**
	 * Access-Control-Last-Polling-Changed-Timestamp
	 *
	 * Used for indicates last long polling changed timestamp
	 *
	 * @type int Time
	 */
	protected $accessControlLastPollingChangedTimestamp;

	// ------------------------------------------------------------------------

	/**
	 * Restful constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		// Setup Response Header
		$this->request->response->setHeader(
			( new Header() )
				->setProtocolVersion( \O2System::$registry->server->get( 'SERVER_PROTOCOL' ) )
		);

		// Setup Response Body
		$this->request->response->setBody( new File() );
	}

	// ------------------------------------------------------------------------

	/**
	 * _route Method
	 *
	 * Overriding Request Routing Handlers
	 *
	 * @param       $method
	 * @param array $args
	 */
	protected function _route( $method, array $args = [ ] )
	{
		if ( $this->isPushAccessControlAllowOrigin === TRUE )
		{
			/**
			 * Prepare for preflight modern browser request
			 *
			 * Since some server cannot use 'Access-Control-Allow-Origin: *'
			 * the Access-Control-Allow-Origin will be defined based on requested origin
			 */
			if ( $this->accessControlAllowOrigin === '*' )
			{
				$origin = empty( $this->request->getHeader( 'ORIGIN' ) ) ? $this->request->getHeader( 'HOST' ) : $this->request->getHeader( 'ORIGIN' );

				if ( strpos( $origin, 'http' ) !== FALSE )
				{
					$origin = parse_domain( $origin );
					$origin = $origin->origin;
				}

				$this->accessControlAllowOrigin = ( is_https() ? 'https://' : 'http://' ) . $origin;
			}

			$this->request->response->header->setLine( Header::RESPONSE_ACCESS_CONTROL_ALLOW_CREDENTIALS, $this->accessControlAllowCredentials );
		}

		$this->request->response->header->setLine( Header::RESPONSE_ACCESS_CONTROL_ALLOW_METHODS, $this->accessControlAllowMethods );
		$this->request->response->header->setLine( Header::RESPONSE_ACCESS_CONTROL_ALLOW_HEADERS, $this->accessControlAllowHeaders );
		$this->request->response->header->setLine( Header::RESPONSE_ACCESS_CONTROL_ALLOW_CONTENT_TYPES, $this->accessControlAllowContentTypes );
		$this->request->response->header->setLine( Header::RESPONSE_ACCESS_CONTROL_MAX_AGE, $this->accessControlMaxAge );
		$this->request->response->header->setLine( 'X-Api-Protection', '1; mode=block;' );
		$this->request->response->header->setLine( 'X-Web-Protection', '1; mode=block;' );

		if ( $this->request->getMethod( TRUE ) === 'OPTIONS' )
		{
			$this->_sendPayload(
				[
					'status'  => new ArrayObject(
						[
							'success' => TRUE,
							'code'    => 200,
						] ),
					'methods' => [
						'GET'     => 'Common Request',
						'POST'    => 'Used for Create, Update Request',
						'PUT'     => 'Used for Upload File Request',
						'DELETE'  => 'Delete Request',
						'OPTIONS' => 'Preflight Request',
					],
				] );

			return;
		}
		elseif ( in_array( $this->request->uri->string, $this->accessControlWhitelistSegments ) )
		{
			$this->__routeCall( $method, $args );

			return;
		}

		if ( $this->_isAllowedOrigin() === FALSE AND $this->_isAuthorize() === FALSE )
		{
			$origin = empty( $this->request->getHeader( 'ORIGIN' ) ) ? $this->request->getHeader( 'HOST' ) : $this->request->getHeader( 'ORIGIN' );

			$this->_sendPayload(
				[
					'status' => new ArrayObject(
						[
							'success' => FALSE,
							'code'    => 403,
							'message' => 'Unauthorized access from your origin: ' . $origin,
						] ),
				] );

			return;
		}
		elseif ( $this->_isAuthorize() === FALSE )
		{
			$this->_sendPayload(
				[
					'status' => new ArrayObject(
						[
							'success' => FALSE,
							'code'    => 403,
							'message' => 'Unauthorized access',
						] ),
				] );

			return;
		}

		$this->__routeCall( $method, $args );
	}

	// ------------------------------------------------------------------------

	protected function _sendPayload( array $response )
	{
		if ( isset( $response[ 'status' ][ 'code' ] ) )
		{
			$response[ 'status' ][ 'description' ] = trim( str_replace( 'Status', '', Header::getConstant( $response[ 'status' ][ 'code' ], TRUE ) ) );

			$this->request->response->header->setStatusCode( $response[ 'status' ][ 'code' ] );
			$this->request->response->header->setLine( Header::REQUEST_ACCEPT, [ MIME::APPLICATION_JSON, MIME::APPLICATION_XML ] );

			$this->request->response->header->setLine( Header::REQUEST_ACCEPT_CHARSET, 'UTF-8' );
			$this->request->response->header->setLine( Header::RESPONSE_ALLOW, $this->accessControlAllowMethods );

			$content_type = $this->request->getHeader( 'X_REQUESTED_RESULT' );

			switch ( $content_type )
			{
				default:
				case 'application/json':

					$this->request->response->body->setMimeType( MIME::APPLICATION_JSON );
					$response = $this->_toJson( $response );
					$this->view->output->setContent( $response );

					break;

				case 'application/xml':

					$this->request->response->body->setMimeType( MIME::APPLICATION_XML );
					$response = $this->_toXml( $response, new \SimpleXMLElement( '<response/>' ) );
					$this->view->output->setContent( $response->asXML() );

					break;
			}
		}
		else
		{
			$this->_sendPayload(
				[
					'status' => new ArrayObject(
						[
							'success' => FALSE,
							'code'    => 500,
							'message' => 'Undefined response status code',
						] ),
				] );
		}
	}

	// ------------------------------------------------------------------------

	protected function _toJson( array $response )
	{
		return json_encode( $response, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT );
	}

	// ------------------------------------------------------------------------

	protected function _toXml( array $response, \SimpleXMLElement $xml )
	{
		foreach ( $response as $key => $value )
		{
			$key = is_numeric( $key ) ? 'data' : $key;

			if ( empty( $value ) )
			{
				continue;
			}

			if ( is_object( $value ) )
			{
				if ( $value instanceof ArrayObject )
				{
					$this->_toXml( $value->__toArray(), $xml->addChild( $key ) );
				}
				else
				{
					$this->_toXml( get_object_vars( $value ), $xml->addChild( $key ) );
				}
			}
			elseif ( is_array( $value ) )
			{
				$this->_toXml( $value, $xml->addChild( $key ) );
			}
			elseif ( is_string( $value ) OR is_numeric( $value ) )
			{
				$xml->addChild( $key, $value );
			}
		}

		return $xml;
	}

	// ------------------------------------------------------------------------

	/**
	 * _route Call Method
	 *
	 * Internal requested controller method caller
	 *
	 * @param       $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	private function __routeCall( $method, array $args = [ ] )
	{
		if ( in_array( $this->request->getMethod( TRUE ), $this->accessControlAllowMethods ) )
		{
			if ( $this->request->getMethod( TRUE ) === 'OPTIONS' )
			{
				$this->_sendPayload(
					[
						'status'  => new ArrayObject(
							[
								'success' => TRUE,
								'code'    => 200,
							] ),
						'methods' => [
							'GET'     => 'Common Request',
							'POST'    => 'Used for Create, Update Request',
							'PUT'     => 'Used for Upload File Request',
							'DELETE'  => 'Delete Request',
							'OPTIONS' => 'Preflight Request',
						],
					] );
			}
			elseif ( method_exists( $this, $method ) )
			{
				return call_user_func_array( [ $this, $method ], $args );
			}
			else
			{
				$this->_sendPayload(
					[
						'status' => new ArrayObject(
							[
								'success'    => FALSE,
								'code'       => 400,
								'message'    => 'Method not exists',
								'controller' => $this->request->controller,
							] ),
					] );
			}
		}
		else
		{
			$this->_sendPayload(
				[
					'status' => new ArrayObject(
						[
							'success' => FALSE,
							'code'    => 405,
							'message' => 'Forbidden request Method',
						] ),
				] );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Allowed Origin
	 *
	 * Determine if the requested ORIGIN is allowed by requested controller
	 *
	 * @return bool
	 */
	abstract protected function _isAllowedOrigin();

	// ------------------------------------------------------------------------

	/**
	 * Is Authorize
	 *
	 * Determine if this request is allowed by requested controller
	 *
	 * @return bool
	 */
	abstract protected function _isAuthorize();

	// ------------------------------------------------------------------------

	/**
	 * Server-side file.
	 * This file is an infinitive loop. Seriously.
	 * It gets the file data.txt's last-changed timestamp, checks if this is larger than the timestamp of the
	 * AJAX-submitted timestamp (time of last ajax request), and if so, it sends back a JSON with the data from
	 * data.txt (and a timestamp). If not, it waits for one seconds and then start the next while step.
	 *
	 * Note: This returns a JSON, containing the content of data.txt and the timestamp of the last data.txt change.
	 * This timestamp is used by the client's JavaScript for the next request, so THIS server-side script here only
	 * serves new content after the last file change. Sounds weird, but try it out, you'll get into it really fast!
	 */
	protected function _longPolling()
	{
		if ( method_exists( $this, '_getLastPollingChangedTimestamp' ) AND method_exists( $this, '_getLastPollingData' ) )
		{
			// set php runtime to unlimited
			set_time_limit( 0 );

			// main loop
			while ( TRUE )
			{
				// if ajax request has send a timestamp, then $last_ajax_call = timestamp, else $last_ajax_call = null
				$this->accessControlLastPollingCallTimestamp = (int) $this->input->getPost( 'last_call_timestamp' );

				// PHP caches file data, like requesting the size of a file, by default. clearstatcache() clears that cache
				clearstatcache();

				// get timestamp of when file has been changed the last time
				$this->accessControlLastPollingChangedTimestamp = (int) $this->_getLastPollingChangedTimestamp();

				// if no timestamp delivered or last polling changed timestamp has been changed SINCE last call timestamp
				if ( $this->accessControlLastPollingCallTimestamp == 0 OR $this->accessControlLastPollingChangedTimestamp > $this->accessControlLastPollingCallTimestamp )
				{
					// get last polling changed data
					$data = $this->_getLastPollingData();

					$this->_sendPayload(
						[
							'status' => new ArrayObject(
								[
									'success' => TRUE,
									'code'    => 200,
								] ),
							'result' => new ArrayObject(
								[
									'metadata' => new ArrayObject(
										[
											'timestamp' => new ArrayObject(
												[
													'last_call'    => $this->accessControlLastPollingCallTimestamp,
													'last_changed' => $this->accessControlLastPollingChangedTimestamp,
												] ),
										] ),
									'data'     => $data,
								] ),
						] );

					// leave this loop step
					break;

				}
				else
				{
					// wait for 1 sec (not very sexy as this blocks the PHP/Apache process, but that's how it goes)
					sleep( 1 );
					continue;
				}
			}
		}
		else
		{
			$this->_sendPayload(
				[
					'status' => new ArrayObject(
						[
							'success' => FALSE,
							'code'    => 501,
						] ),
				] );
		}
	}
}