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

use O2System\Controller;
use O2System\Glob\ArrayObject;
use O2System\Glob\HttpHeader;
use O2System\Glob\HttpHeaderResponse;
use O2System\Glob\HttpHeaderStatus;

abstract class Restful extends Controller
{
	protected $_http_response_codes = [
		200 => 'OK',
		201 => 'Created',
		400 => 'Bad Request',
		404 => 'Not Found',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		405 => 'Method Not Allowed',
		409 => 'Conflict',
		500 => 'Internal Server Error',
	];

	protected $_allowed_request_methods = [
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'OPTIONS',
	];

	protected $_allowed_request_headers = [
		'API-Token',
		'Authorization-Token',
		'Accept',
		'Authorization',
		'Content-Type',
		'Content-Range',
		'Content-Disposition',
		'Content-Description',
		'X-Request-With',
		'X-Powered-By',
		'Cache-Control',
	];

	protected $_authorize_route_methods = [ ];

	protected $_allow_origin      = '*';
	protected $_allow_credentials = TRUE;

	protected $_max_age = 86400;

	public function __construct()
	{
		// Load Applications Language
		\O2System::$language->load( DIR_APPLICATIONS );

		if ( $module = \O2System::$active[ 'modules' ]->current() )
		{
			\O2System::$language->load( $module->parameter );

			// Load Module Model
			if ( ! $this->__get( $module_model_object_name = strtolower( $module->type ) . '_model' ) )
			{
				$model_classes = [
					rtrim( \O2System::$active[ 'namespaces' ]->current(), '\\' ) . '\\' . 'Core\\Model',
					rtrim( \O2System::$active[ 'namespaces' ]->current(), '\\' ) . '\\' . 'Models\\' . ucfirst( \O2System::$active[ 'controller' ]->parameter ),
				];

				foreach ( $model_classes as $model_class )
				{
					if ( class_exists( $model_class ) )
					{
						$this->{$module_model_object_name} = new $model_class();
						break;
					}
				}
			}
		}

		// Load Controller Language
		\O2System::$language->load( \O2System::$active[ 'controller' ]->parameter );

		// Load Controller Model
		if ( empty( $this->controller_model ) )
		{
			$model_classes = [
				rtrim( \O2System::$active[ 'namespaces' ]->current(), '\\' ) . '\\' . 'Models\\' . prepare_class_name( \O2System::$active[ 'controller' ]->parameter ),
				rtrim( \O2System::$active[ 'namespaces' ]->current(), '\\' ) . '\\' . 'Core\\Model',
			];

			foreach ( $model_classes as $model_class )
			{
				if ( class_exists( $model_class ) )
				{
					$this->controller_model = new $model_class();
					break;
				}
			}
		}
	}

	abstract protected function _isAuthorize();

	abstract protected function _isAllowedOrigin();

	protected function _route( $method, $args = [ ] )
	{
		// Clear Previous Set Headers
		HttpHeader::clearPreviousHeader( TRUE );

		// Set New Headers
		HttpHeaderResponse::setHeader( 'Access-Control-Allow-Credentials', $this->_allow_credentials );
		HttpHeaderResponse::setHeader( 'Access-Control-Allow-Methods', $this->_allowed_request_methods );
		HttpHeaderResponse::setHeader( 'Access-Control-Allow-Headers', $this->_allowed_request_headers );

		HttpHeader::remove( 'Access-Control-Allow-Origin' );
		HttpHeaderResponse::setHeader( 'Access-Control-Allow-Origin', '*' );

		if ( $this->_isAllowedOrigin() === TRUE )
		{
			HttpHeader::remove( 'Access-Control-Allow-Origin' );
			HttpHeaderResponse::setHeader( 'Access-Control-Allow-Origin', $this->_allow_origin );

			if ( in_array( ltrim( $method, '_' ), $this->_authorize_route_methods ) )
			{
				return $this->__routeCall( $method, $args );
			}
			elseif ( $this->_isAuthorize() === TRUE )
			{
				return $this->__routeCall( $method, $args );
			}
		}

		if ( $this->input->server( 'REQUEST_METHOD' ) === 'OPTIONS' )
		{
			$this->_send(
				[
					'status'  => new ArrayObject(
						[
							'success'     => TRUE,
							'code'        => 200,
							'description' => HttpHeaderStatus::getDescription( 200 ),
						] ),
					'methods' => $this->_allowed_request_methods,
				] );
		}
		else
		{
			$this->_send(
				[
					'status' => new ArrayObject(
						[
							'success'     => FALSE,
							'code'        => 401,
							'description' => HttpHeaderStatus::getDescription( 401 ),
						] ),
				] );
		}
	}

	private function __routeCall( $method, $args = [ ] )
	{
		if ( in_array( $this->input->server( 'REQUEST_METHOD' ), $this->_allowed_request_methods ) )
		{
			if ( $this->input->server( 'REQUEST_METHOD' ) === 'OPTIONS' )
			{
				$this->_send(
					[
						'status'  => new ArrayObject(
							[
								'success'     => TRUE,
								'code'        => 200,
								'description' => HttpHeaderStatus::getDescription( 200 ),
							] ),
						'methods' => $this->_allowed_request_methods,
					] );
			}
			elseif ( method_exists( $this, $method ) )
			{
				return call_user_func_array( [ $this, $method ], $args );
			}
			else
			{
				$this->_send(
					[
						'status' => new ArrayObject(
							[
								'success'     => FALSE,
								'code'        => 405,
								'description' => HttpHeaderStatus::getDescription( 405 ),
							] ),
					] );
			}
		}
		else
		{
			$this->_send(
				[
					'status' => new ArrayObject(
						[
							'success'     => FALSE,
							'code'        => 405,
							'description' => HttpHeaderStatus::getDescription( 405 ),
							'message'     => 'Forbidden Request Method',
						] ),
				] );
		}
	}

	protected function _send( array $response )
	{
		if ( isset( $response[ 'status' ][ 'code' ] ) AND array_key_exists( $response[ 'status' ][ 'code' ], $this->_http_response_codes ) )
		{
			HttpHeaderResponse::setHeader( 'Access-Control-Max-Age', $this->_max_age );
			HttpHeaderStatus::setHeader( $response[ 'status' ][ 'code' ] );

			$content_type = $this->input->server( 'CONTENT_RESULT' );

			switch ( $content_type )
			{
				default:
				case 'application/json':
					$response = $this->_toJson( $response );

					$this->view->output
						->setContentType( 'application/json' )
						->setContent( $response );
					break;

				case 'application/xml':
					$response = $this->_toXml( $response, new \SimpleXMLElement( '<response/>' ) );

					$this->view->output
						->setContentType( 'application/xml' )
						->setContent( $response->asXML() );
					break;
			}
		}
		else
		{
			$this->_send(
				[
					'status' => new ArrayObject(
						[
							'success'     => FALSE,
							'code'        => 500,
							'description' => HttpHeaderStatus::getDescription( 500 ),
						] ),
				] );
		}
	}

	protected function _toJson( array $response )
	{
		return json_encode( $response, JSON_PRETTY_PRINT );
	}

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
}