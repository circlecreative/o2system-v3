<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 2/24/2016
 * Time: 4:40 PM
 */

namespace O2System\Controllers;

use O2System\Controller;

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
		$this->view->output->set_header_status( 200 );
		$this->view->output->set_header( 'Access-Control-Allow-Origin', '*' );
		$this->view->output->set_header( 'Access-Control-Allow-Credentials', 'true' );
		$this->view->output->set_header( 'Access-Control-Expose-Headers', 'Access-Control-Allow-Origin' );
		$this->view->output->set_header( 'Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE' );
		$this->view->output->set_header( 'Access-Control-Allow-Headers', 'Content-Type, *' );

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

	public function _route( $method, $args = array() )
	{
		$method = '_' . $method;

		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		else
		{
			redirect( 'error/404' );
		}
	}

	public function send( array $response, $output = 'json' )
	{
		switch ( $output )
		{
			case 'json':
				$response = $this->_toJson( $response );

				$this->view->output
					->set_content_type( 'application/json' )
					->set_content( $response );
				break;

			case 'xml':
				$response = $this->_toXml( $response );

				$this->view->output
					->set_content_type( 'application/xml' )
					->set_content( $response );
				break;
		}
	}

	protected function _toJson( array $response )
	{
		return json_encode( $response, JSON_PRETTY_PRINT );
	}

	protected function _toXml( array $response )
	{

	}
}