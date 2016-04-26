<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 2/24/2016
 * Time: 4:40 PM
 */

namespace O2System\Controllers;

use O2System\Controller;
use O2System\Glob\ArrayObject;
use O2System\Glob\HttpStatusCode;

abstract class Restful extends Controller
{
	protected $_http_response_codes = array(
		200 => 'OK',
		201 => 'Created',
		400 => 'Bad Request',
		404 => 'Not Found',
		401 => 'Unauthorized',
		405 => 'Method Not Allowed',
		409 => 'Conflict',
		500 => 'Internal Server Error',
	);

	public function __construct()
	{
		// Load Applications Language
		\O2System::$language->load( DIR_APPLICATIONS );

		if ( \O2System::$active->offsetExists( 'module' ) )
		{
			\O2System::$language->load( \O2System::$active[ 'module' ]->parameter );

			// Load Module Model
			if ( ! $this->__get( $module_model_object_name = strtolower( \O2System::$active[ 'module' ]->type ) . '_model' ) )
			{
				$model_classes = array(
					rtrim( \O2System::$active[ 'namespace' ], '\\' ) . '\\' . 'Core\\Model',
					rtrim( \O2System::$active[ 'namespace' ], '\\' ) . '\\' . 'Models\\' . ucfirst( \O2System::$active[ 'controller' ]->parameter ),
				);

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
			$model_classes = array(
				rtrim( \O2System::$active[ 'namespace' ], '\\' ) . '\\' . 'Models\\' . prepare_class_name( \O2System::$active[ 'controller' ]->parameter ),
				rtrim( \O2System::$active[ 'namespace' ], '\\' ) . '\\' . 'Core\\Model',
			);

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

	protected function _route( $method, $args = array() )
	{
		if ( method_exists( $this, '_authorize' ) )
		{
			if ( $this->_authorize() === TRUE )
			{
				$this->__route_call( $method, $args );
			}
			else
			{
				$this->_send( array(
					              'status' => new ArrayObject( array(
						                                           'success'     => FALSE,
						                                           'code'        => 401,
						                                           'description' => HttpStatusCode::getDescription( 401 ),
					                                           ) ),
				              ) );
			}
		}
		else
		{
			$this->__route_call( $method, $args );
		}
	}

	private function __route_call( $method, $args = array() )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		else
		{
			$this->_send( array(
				              'status' => new ArrayObject( array(
					                                           'success'     => FALSE,
					                                           'code'        => 405,
					                                           'description' => HttpStatusCode::getDescription( 405 ),
				                                           ) ),
			              ) );
		}
	}

	protected function _send( array $response )
	{
		//print_out($this->_toJson((array)$response['results']));
		if ( isset( $response[ 'status' ][ 'code' ] ) AND array_key_exists( $response[ 'status' ][ 'code' ], $this->_http_response_codes ) )
		{
			$this->view->output->set_header( 'Access-Control-Allow-Origin', '*' );
			$this->view->output->set_header( 'Access-Control-Allow-Credentials', 'true' );
			$this->view->output->set_header( 'Access-Control-Expose-Headers', 'Access-Control-Allow-Origin' );
			$this->view->output->set_header( 'Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE' );
			$this->view->output->set_header_status( $response[ 'status' ][ 'code' ] );

			$content_type = $this->input->server( 'CONTENT_RESULT' );

			switch ( $content_type )
			{
				default:
				case 'application/json':
					$response = $this->_toJson( $response );

					$this->view->output
						->set_content_type( 'application/json' )
						->set_content( $response );
					break;

				case 'application/xml':
					$response = $this->_toXml( $response, new \SimpleXMLElement( '<response/>' ) );

					$this->view->output
						->set_content_type( 'application/xml' )
						->set_content( $response->asXML() );
					break;
			}
		}
		else
		{
			$this->_send( array(
				              'status' => new ArrayObject( array(
					                                           'success'     => FALSE,
					                                           'code'        => 500,
					                                           'description' => HttpStatusCode::getDescription( 500 ),
				                                           ) ),
			              ) );
		}
	}

	protected function _toJson( array $response )
	{
		//print_out($response);
		//print_out(json_encode( $response,JSON_FORCE_OBJECT));
		return json_encode( $response, JSON_PRETTY_PRINT );
	}

	protected function _toXml( array $response, \SimpleXMLElement $xml )
	{
		foreach ( $response as $key => $value )
		{
			$key = is_numeric( $key ) ? 'data' : $key;

			if ( empty( $value ) ) continue;

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