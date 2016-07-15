<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System;

use O2System\Glob\HttpHeader;

defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * System Router
 *
 * Parses URIs and determines routing
 *
 * @package       O2System
 * @subpackage    system/core
 * @category      Core Class
 * @author        O2System Developer Team
 * @link          http://o2system.center/framework/user-guide/core/router.html
 */
class Router
{
	/**
	 * Router Class Configuration
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_config = [ ];

	/**
	 * Validate Methods
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_validate_methods = [ ];

	// ------------------------------------------------------------------------


	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		// Load the router config
		if ( $config = \O2System::$config->load( 'router', TRUE ) )
		{
			$this->_config = $config;
		}

		// Build the reflection to determine validation methods
		$reflection = new \ReflectionClass( get_called_class() );

		foreach ( $reflection->getMethods() as $method )
		{
			if ( strpos( $method->name, '_isValid' ) !== FALSE )
			{
				$this->_validate_methods[] = $method->name;
			}
		}

		if ( \O2System::$active[ 'URI' ]->totalSegments( 'segments' ) > 0 )
		{
			$this->_parseRoutes();
		}

		if ( \O2System::$active[ 'URI' ]->totalSegments( 'isegments' ) == 0 )
		{
			$this->_setDefaultController();
		}

		if ( $this->_validateRequest() === FALSE )
		{
			$this->showError( 404 );
		}

		\O2System::Log( 'info', 'Router Class Initialized' );
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Default Controller
	 *
	 * Default controller setter replacement
	 *
	 * @used-by Router::_setRouting()
	 *
	 * @access  protected
	 * @throws \Exception
	 * @return  array
	 */
	protected function _setDefaultController()
	{
		if ( \O2System::$active[ 'URI' ]->totalSegments( 'rsegments' ) > 0 )
		{
			foreach ( \O2System::$active[ 'URI' ]->rsegments as $key => $rsegment )
			{
				$controller = \O2System::$active[ 'directories' ]->current() . 'controllers' . prepare_filename( $rsegment ) . '.php';

				if ( $controller = $this->__loadController( $controller ) )
				{
					\O2System::Log( 'debug', 'Default controller set.' );

					$this->__setupController( $controller, \O2System::$active[ 'URI' ]->isegments );

					return TRUE;

					break;
				}
			}
		}
		elseif ( isset( $this->_config[ 'default_controller' ] ) )
		{
			sscanf( $this->_config[ 'default_controller' ], '%[^/]/%s', $class, $method );

			$method = empty( $method ) ? 'index' : $method;

			$rsegments = [ $class, $method ];

			\O2System::Log( 'debug', 'Default controller set.' );

			if ( $module = \O2System::$active[ 'modules' ]->current() )
			{
				$rsegments = explode( '/', $module->segments );

				\O2System::$active[ 'URI' ]->setSegments( [ $class, $method ], 'isegments' );

				if ( in_array( $class, $rsegments ) )
				{
					$rsegments = array_merge( $rsegments, [ $method ] );
				}
				else
				{
					$rsegments = array_merge( $rsegments, [ $class, $method ] );
				}
			}

			\O2System::$active[ 'URI' ]->setSegments( $rsegments, 'rsegments' );
		}
		else
		{
			$this->showError( 404 );
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Routes
	 *
	 * Matches any routes that may exist in the config/routes.php file
	 * against the URI to determine if the class/method need to be remapped.
	 *
	 * @return    void
	 */
	protected function _parseRoutes()
	{
		$string = \O2System::$active[ 'URI' ]->string;

		// Get HTTP verb
		$http_verb = isset( $_SERVER[ 'REQUEST_METHOD' ] ) ? strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) : 'cli';

		// Is there a literal match?  If so we're done
		if ( isset( $this->_config[ $string ] ) )
		{
			// Check default routes format
			if ( is_string( $this->_config[ $string ] ) )
			{
				\O2System::URI()->setRequest( $this->_config[ $string ], 'rsegments' );

				return;
			}
			// Is there a matching http verb?
			elseif ( is_array( $this->_config[ $string ] ) AND isset( $this->_config[ $string ][ $http_verb ] ) )
			{
				\O2System::URI()->setRequest( $this->_config[ $string ][ $http_verb ], 'rsegments' );

				return;
			}
		}

		$route = $this->_config;
		unset( $route[ 'default_controller' ], $route[ '404_override' ] );

		if ( ! empty( $route ) )
		{
			// Loop through the route array looking for wildcards
			foreach ( $route as $key => $value )
			{
				// Check if route format is using http verb
				if ( is_array( $value ) )
				{
					if ( isset( $value[ $http_verb ] ) )
					{
						$value = $value[ $http_verb ];
					}
					else
					{
						continue;
					}
				}

				// Convert wildcards to RegEx
				$key = str_replace( [ ':any', ':num' ], [ '[^/]+', '[0-9]+' ], $key );

				// Does the RegEx match?
				if ( preg_match( '#^' . $key . '$#', $string, $matches ) )
				{
					// Are we using callbacks to process back-references?
					if ( ! is_string( $value ) && is_callable( $value ) )
					{
						// Remove the original string from the matches array.
						array_shift( $matches );

						// Execute the callback using the values in matches as its parameters.
						$value = call_user_func_array( $value, $matches );
					}
					// Are we using the default routing method for back-references?
					elseif ( strpos( $value, '$' ) !== FALSE && strpos( $key, '(' ) !== FALSE )
					{
						$value = preg_replace( '#^' . $key . '$#', $value, $string );
					}

					\O2System::URI()->setRequest( $value, 'rsegments' );

					return;
				}
			}
		}

		// If we got this far it means we didn't encounter a
		// matching route so we'll set to original parsed segments
		return;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate request
	 *
	 * Attempts validate the URI request and determine the controller path.
	 *
	 * @used-by    Router:__construct()
	 *
	 * @param    array $segments URI segments
	 *
	 * @return    mixed    URI segments
	 */
	final protected function _validateRequest()
	{
		if ( \O2System::$active->offsetExists( 'controller' ) === FALSE )
		{
			$validate_segments = \O2System::$active[ 'URI' ]->isegments;

			// Find controller first
			$directories = \O2System::Load()->getPackagePaths( 'controllers', TRUE );

			$sub_directory        = '';
			$is_found             = FALSE;
			$directories_segments = [ ];
			foreach ( \O2System::$active[ 'URI' ]->isegments as $key => $segment )
			{
				$sub_directory .= str_replace( '-', '_', $segment ) . DIRECTORY_SEPARATOR;

				foreach ( $directories as $namespace => $directory )
				{
					if ( is_dir( $directory . $sub_directory ) )
					{
						if ( is_file( $directory . $sub_directory . prepare_filename( next( \O2System::$active[ 'URI' ]->isegments ) ) . '.php' ) )
						{
							$directories_segments[] = $segment;
						}

						$class_namespace = $namespace . prepare_class_name( $sub_directory );
						\O2System::Load()->addNamespace( $class_namespace, $directory . $sub_directory );
						$directories[ $class_namespace ] = $directory . $sub_directory;

						$is_found = TRUE;
					}
				}

				if ( $is_found === TRUE )
				{
					$validate_segments = array_diff(
						$validate_segments,
						$directories_segments
					);

					if ( empty( $validate_segments ) )
					{
						$validate_segments = explode( DIRECTORY_SEPARATOR, $sub_directory );
					}
				}
			}

			$validate_segments = array_filter( $validate_segments );
			$validate_segments = array_values( $validate_segments );
			array_unshift( $validate_segments, NULL );
			unset( $validate_segments[ 0 ] );

			$validate_segments = empty( $validate_segments ) ? \O2System::$active[ 'URI' ]->rsegments : $validate_segments;

			$directories = array_reverse( $directories );

			foreach ( $directories as $namespace => $directory )
			{
				foreach ( $validate_segments as $segment )
				{
					if ( $controller = $this->__loadController( $directory . prepare_filename( $segment ) . '.php' ) )
					{
						$this->__setupController( $controller, $validate_segments );

						break;
					}
				}

				if ( \O2System::$active->offsetExists( 'controller' ) )
				{
					break;
				}
			}

			if ( \O2System::$active->offsetExists( 'controller' ) === FALSE )
			{
				return $this->_setDefaultController();
			}
			else
			{
				return TRUE;
			}
		}
		elseif ( \O2System::$active->offsetExists( 'controller' ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	protected function _isControllerSegment( $segment )
	{

	}

	protected function _isControllerMethodSegment( $segment )
	{

	}

	private function __loadController( $filepath )
	{
		if ( file_exists( $filepath ) )
		{
			$controller = new \O2System\Metadata\Controller( $filepath );

			return $controller;
		}

		return FALSE;
	}

	private function __setupController( $controller, $validate_segments )
	{
		if ( \O2System::$active[ 'modules' ]->isEmpty() === FALSE )
		{
			foreach ( \O2System::$active[ 'modules' ] as $key => $module )
			{
				if ( $controller->namespace === $module->namespace . 'Controllers\\' )
				{
					\O2System::$active[ 'modules' ]->setCurrent( $key );
					break;
				}
			}
		}

		// Set Current Module Namespace, Directories and Routed Segments
		$rsegments = [ ];
		if ( $current_module = \O2System::$active[ 'modules' ]->current() )
		{
			foreach ( \O2System::$active[ 'modules' ] as $module_key => $module )
			{
				if ( $module->namespace === $current_module->namespace )
				{
					\O2System::$active[ 'modules' ]->setCurrent( $module_key );
					break;
				}
			}

			$search_namespace = explode( 'Controllers\\', $controller->namespace );
			$search_namespace = reset( $search_namespace );

			if ( $namespace_key = array_search( $search_namespace, \O2System::$active[ 'namespaces' ]->getArrayCopy() ) )
			{
				\O2System::$active[ 'namespaces' ]->setCurrent( $namespace_key );
			}

			$search_directory = explode( 'controllers' . DIRECTORY_SEPARATOR, $controller->realpath );
			$search_directory = reset( $search_directory );

			if ( $directory_key = array_search( $search_directory, \O2System::$active[ 'directories' ]->getArrayCopy() ) )
			{
				\O2System::$active[ 'directories' ]->setCurrent( $directory_key );
			}

			// Now we need to update the URI Routed Segments
			$rsegments = explode( '/', $module->segments );
		}

		// Setup Routed Segments
		if ( ! in_array( $controller->parameter, $rsegments ) )
		{
			$x_realpath = explode( 'Controllers\\', $controller->class );
			$x_segments = array_map( 'strtolower', explode( '\\', end( $x_realpath ) ) );

			$rsegments = array_merge( $rsegments, $x_segments );
		}

		if ( $validate_segment_key = array_search( $controller->parameter, $validate_segments ) )
		{
			$validate_segments = array_slice( $validate_segments, $validate_segment_key );
			$validate_segments = array_values( $validate_segments );
		}

		$method_segment = 'index';

		if ( isset( $validate_segments[ 0 ] ) )
		{
			if ( ! filter_var( $validate_segments[ 0 ], FILTER_VALIDATE_INT ) )
			{
				$method_segment = str_replace( '-', '_', $validate_segments[ 0 ] );
				$method_segment = camelcase( $method_segment );

				if ( in_array( $method_segment, [ 'add_new', 'edit' ] ) )
				{
					$method_segment = 'form';
				}

				array_shift( $validate_segments );
			}
		}

		if ( isset( $controller->method ) )
		{
			// _route() method exists
			if ( $controller->method === '_route' )
			{
				if ( $controller->isPublicMethod( $method_segment ) )
				{
					// Route to public controller method()
					$controller->params[ 0 ] = $method_segment;
				}
				elseif ( $controller->isProtectedMethod( $method_segment ) )
				{
					// Route to protected controller _method()
					$controller->params[ 0 ] = '_' . $method_segment;
				}
				else
				{
					if ( $controller->isPublicMethod( 'index' ) )
					{
						// Route to public controller index()
						$controller->params[ 0 ] = 'index';
						$controller->params[ 1 ] = $method_segment;
					}
					elseif ( $controller->isProtectedMethod( 'index' ) )
					{
						// Route to protected controller _index()
						$controller->params[ 0 ] = '_index';
						$controller->params[ 1 ] = $method_segment;
					}
					else
					{
						// Assign params[0] as method
						$controller->params[ 0 ] = $method_segment;
					}
				}
			}
			// index() method exists
			elseif ( $controller->method === 'index' )
			{
				if ( $method_segment !== 'index' )
				{
					if ( is_ajax() )
					{
						if ( $controller->isProtectedMethod( $method_segment ) )
						{
							// Route to public controller method()
							$controller->method = '_' . $method_segment;
						}
						else
						{
							$this->showError( 403 );
						}
					}
					elseif ( $controller->isProtectedMethod( $method_segment ) )
					{
						$this->showError( 403 );
					}
					elseif ( $controller->isPublicMethod( $method_segment ) )
					{
						// Route to public controller method()
						$controller->method = $method_segment;
					}
					else
					{
						$controller->params[] = $method_segment;
					}
				}
			}
		}
		else
		{
			if ( is_ajax() )
			{
				if ( $controller->isProtectedMethod( $method_segment ) )
				{
					// Route to public controller method()
					$controller->method = '_' . $method_segment;
				}
				else
				{
					$this->showError( 403 );
				}
			}
			elseif ( $controller->isProtectedMethod( $method_segment ) )
			{
				$this->showError( 403 );
			}
			elseif ( $controller->isPublicMethod( $method_segment ) )
			{
				// Route to public controller method()
				$controller->method = $method_segment;
			}
			else
			{
				$this->showError( 404 );
			}
		}

		if ( isset( $controller->method ) )
		{
			// We need to update again the URI Routed segments
			array_push( $rsegments, $controller->method );

			\O2System::$active[ 'URI' ]->setSegments( $rsegments, 'rsegments' );

			if ( $controller->method === '_route' )
			{
				if ( $controller->params[ 0 ] === $controller->parameter )
				{
					$controller->params[ 0 ] = 'index';
				}
			}

			// @todo validate other segments
			foreach ( $validate_segments as $segment )
			{
				if ( $this->_validateSegment( $segment ) === FALSE )
				{
					if ( $controller->method === '_route' )
					{
						$controller->params[ 1 ][] = $segment;
					}
					else
					{
						$controller->params[] = $segment;
					}
				}
			}
		}

		unset( \O2System::$active[ 'URI' ]->isegments, \O2System::$active[ 'URI' ]->istring );

		\O2System::$active[ 'controller' ] = $controller;
	}

	/**
	 * Validate Segment
	 *
	 * Segment validation replacement
	 *
	 * @param   string $segment URI Segment String
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function _validateSegment( $segment )
	{
		foreach ( $this->_validate_methods as $method )
		{
			if ( $this->{$method}( $segment ) === TRUE )
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------


	/**
	 * Route Error 403
	 *
	 * @access  public
	 * @return  bool
	 */
	public function showError( $code )
	{
		if ( ! empty( $this->_config[ $code . '_override' ] ) )
		{
			if ( sscanf( $this->_config[ $code . '_override' ], '%[^/]/%s', $class, $method ) !== 2 )
			{
				$method = 'index';
			}
		}

		if ( empty( $class ) AND empty( $method ) )
		{
			$class  = 'Error';
			$method = 'index';
		}

		unset( \O2System::$active[ 'controller' ] );

		$rsegments = [ ];

		if ( $module = \O2System::$active[ 'modules' ]->current() )
		{
			$directory = ROOTPATH . $module->realpath . 'controllers' . DIRECTORY_SEPARATOR;

			if ( $controller = $this->__loadController( $directory . prepare_filename( $class ) . '.php' ) )
			{
				\O2System::$active[ 'controller' ] = $controller;

				if ( \O2System::$active[ 'modules' ]->isEmpty() === FALSE )
				{
					foreach ( \O2System::$active[ 'modules' ] as $key => $module )
					{
						if ( $controller->namespace === $module->namespace . 'Controllers\\' )
						{
							\O2System::$active[ 'modules' ]->setCurrent( $key );
							break;
						}
					}
				}

				if ( $module = \O2System::$active[ 'modules' ]->current() )
				{
					$search_namespace = explode( 'Controllers\\', $controller->namespace );
					$search_namespace = reset( $search_namespace );

					if ( $namespace_key = array_search( $search_namespace, \O2System::$active[ 'namespaces' ]->getArrayCopy() ) )
					{
						\O2System::$active[ 'namespaces' ]->setCurrent( $namespace_key );
					}

					$search_directory = explode( 'controllers/', $directory );
					$search_directory = reset( $search_directory );

					if ( $directory_key = array_search( $search_directory, \O2System::$active[ 'directories' ]->getArrayCopy() ) )
					{
						\O2System::$active[ 'directories' ]->setCurrent( $directory_key );
					}

					// Now we need to update the URI Routed Segments
					$rsegments = explode( '/', $module->segments );
				}
			}
		}

		if ( \O2System::$active->offsetExists( 'controller' ) === FALSE )
		{
			if ( is_file( APPSPATH . 'controllers' . DIRECTORY_SEPARATOR . prepare_filename( $class ) . '.php' ) )
			{
				$controller = new \O2System\Metadata\Controller( APPSPATH . 'controllers' . DIRECTORY_SEPARATOR . prepare_filename( $class ) . '.php' );

				if ( $controller->isPublicMethod( $method ) )
				{
					\O2System::$active[ 'controller' ] = $controller;
				}
			}
		}

		if ( \O2System::$active->offsetExists( 'controller' ) === FALSE )
		{
			if ( is_file( SYSTEMPATH . 'controllers' . DIRECTORY_SEPARATOR . prepare_filename( $class ) . '.php' ) )
			{
				$controller = new \O2System\Metadata\Controller( SYSTEMPATH . 'controllers' . DIRECTORY_SEPARATOR . prepare_filename( $class ) . '.php' );

				if ( $controller->isPublicMethod( $method ) )
				{
					\O2System::$active[ 'controller' ] = $controller;
				}
			}
		}

		if ( \O2System::$active->offsetExists( 'controller' ) )
		{
			if ( \O2System::$active[ 'controller' ]->method === 'index' )
			{
				// We need to update again the URI Routed segments
				array_push( $rsegments, \O2System::$active[ 'controller' ]->parameter );
				array_push( $rsegments, \O2System::$active[ 'controller' ]->method );

				\O2System::$active[ 'URI' ]->setSegments( $rsegments, 'rsegments' );
				\O2System::$active[ 'controller' ]->params = [ $code ];
			}
			elseif ( \O2System::$active[ 'controller' ]->method === '_route' )
			{
				// We need to update again the URI Routed segments
				array_push( $rsegments, \O2System::$active[ 'controller' ]->parameter );
				array_push( $rsegments, \O2System::$active[ 'controller' ]->method );

				\O2System::$active[ 'URI' ]->setSegments( $rsegments, 'rsegments' );

				\O2System::$active[ 'controller' ]->params = [
					'index',
					[ $code ],
				];
			}
			else
			{
				throw new Exception( 'Undefined Error Method', 412 );
			}

			return TRUE;
		}

		return FALSE;
	}

	public function routeUrl( $uri = NULL, $suffix = NULL, $protocol = NULL )
	{
		if ( \O2System::$config->offsetExists( 'route_url' ) )
		{
			$route_url = \O2System::$config->offsetGet( 'route_url' );
			$route_url = is_array( $route_url ) ? implode( '/', $route_url ) : $route_url;

			if ( strpos( $route_url, 'http' ) !== FALSE )
			{
				if ( isset( $uri ) )
				{
					$uri       = is_array( $uri ) ? implode( '/', $uri ) : $uri;
					$route_url = $route_url . trim( $uri, '/' );
				}

				if ( isset( $suffix ) )
				{
					if ( is_bool( $suffix ) )
					{
						$URI    = \O2System::$config->offsetGet( 'URI' );
						$suffix = ( empty( $URI[ 'suffix' ] ) ) ? '' : $URI[ 'suffix' ];

						$route_url = rtrim( $route_url, $suffix );
					}
					elseif ( is_array( $suffix ) )
					{
						$http_query = (array) $_GET;
						$http_query = array_diff( $suffix, $http_query );

						$suffix = array_merge( $suffix, $http_query );
						$suffix = array_unique( $suffix );

						$suffix = '/?' . http_build_query( $suffix );
					}
				}

				if ( ! empty( $route_url ) )
				{
					$extension = pathinfo( $route_url, PATHINFO_EXTENSION );

					if ( empty( $extension ) )
					{
						$route_url = $route_url . $suffix;
					}
				}

				if ( isset( $protocol ) )
				{
					$route_url = $protocol . substr( $route_url, strpos( $route_url, '://' ) );
				}

				return $route_url;
			}
			else
			{
				$uri = is_array( $uri ) ? array_unshift( $uri, $route_url ) : trim( $route_url, '/' ) . '/' . $uri;

				return \O2System::$config->baseUrl( $uri, $suffix, $protocol );
			}
		}

		return \O2System::$config->baseUrl( $uri, $suffix, $protocol );
	}
}