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
	protected $_config = array();

	/**
	 * Validate Methods
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_validate_methods = array();

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
			if ( strpos( $method->name, '_is' ) !== FALSE )
			{
				$this->_validate_methods[] = $method->name;
			}
		}

		if ( \O2System::$active[ 'URI' ]->totalSegments( 'rsegments' ) == 0 )
		{
			$this->_setDefaultController();
		}
		else
		{
			$this->_parseRoutes();
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
		if ( empty( $this->_config[ 'default_controller' ] ) )
		{
			$this->showError( 404 );
		}
		else
		{
			// Is the method being specified?
			if ( sscanf( $this->_config[ 'default_controller' ], '%[^/]/%s', $class, $method ) !== 2 )
			{
				$method = 'index';
			}

			\O2System::Log( 'debug', 'Default controller set.' );

			if ( \O2System::$active->offsetExists( 'module' ) )
			{
				$segments = explode( '/', \O2System::$active[ 'module' ]->segments );

				return array_merge( $segments, [ $class, $method ] );
			}

			\O2System::URI()->setRequest( [ $class, $method ] );
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
		$rstring = \O2System::$active[ 'URI' ]->rstring;

		// Get HTTP verb
		$http_verb = isset( $_SERVER[ 'REQUEST_METHOD' ] ) ? strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) : 'cli';

		// Is there a literal match?  If so we're done
		if ( isset( $this->_config[ $rstring ] ) )
		{
			// Check default routes format
			if ( is_string( $this->_config[ $rstring ] ) )
			{
				\O2System::$active[ 'URI' ]->setSegments( $this->_config[ $rstring ], 'rsegments' );

				return;
			}
			// Is there a matching http verb?
			elseif ( is_array( $this->_config[ $rstring ] ) AND isset( $this->_config[ $rstring ][ $http_verb ] ) )
			{
				\O2System::$active[ 'URI' ]->setSegments( $this->_config[ $rstring ][ $http_verb ], 'rsegments' );

				return;
			}
		}

		$route = $this->_config;
		unset( $route[ 'default_controller' ], $route[ '404_override' ], $route[ 'translate_uri_dashes' ] );

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
				$key = str_replace( array( ':any', ':num' ), array( '[^/]+', '[0-9]+' ), $key );

				// Does the RegEx match?
				if ( preg_match( '#^' . $key . '$#', $rstring, $matches ) )
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
						$value = preg_replace( '#^' . $key . '$#', $value, $rstring );
					}

					\O2System::$active[ 'URI' ]->setSegments( $value, 'rsegments' );

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

			//print_out(\O2System::$active[ 'URI' ]);

			$sub_directory = '';
			$is_found = FALSE;
			$directories_segments = array();
			foreach ( \O2System::$active[ 'URI' ]->isegments as $key => $segment )
			{
				$sub_directory .= str_replace( '-', '_', $segment ) . DIRECTORY_SEPARATOR;

				foreach ( $directories as $namespace => $directory )
				{
					if ( is_dir( $directory . $sub_directory ) )
					{
						$directories_segments[] = $segment;
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

					//break;
				}
			}

			$validate_segments = array_filter( $validate_segments );
			$validate_segments = array_values( $validate_segments );
			array_unshift( $validate_segments, NULL );
			unset( $validate_segments[ 0 ] );

			$directories = array_reverse( $directories );

			$rsegments = array();
			foreach ( $directories as $namespace => $directory )
			{
				foreach ( $validate_segments as $segment )
				{
					if ( $controller = $this->_isValidController( $directory . prepare_filename( $segment ) . '.php' ) )
					{
						\O2System::$active[ 'controller' ] = $controller;

						if ( \O2System::$active[ 'modules' ]->isEmpty() === FALSE )
						{
							foreach ( \O2System::$active[ 'modules' ] as $key => $module )
							{
								if ( $controller->namespace === $module->namespace . 'Controllers\\' )
								{
									\O2System::$active[ 'module' ] = \O2System::$active[ 'modules' ]->seek( $key );
									break;
								}
							}
						}

						if ( \O2System::$active->offsetExists( 'module' ) )
						{
							$search_namespace = explode( 'Controllers\\', $namespace );
							$search_namespace = reset( $search_namespace );

							if ( $namespace_key = array_search( $search_namespace, \O2System::$active[ 'namespaces' ]->getArrayCopy() ) )
							{
								\O2System::$active[ 'directory' ] = \O2System::$active[ 'namespaces' ]->seek( $namespace_key );
							}

							$search_directory = explode( 'controllers/', $directory );
							$search_directory = reset( $search_directory );

							if ( $directory_key = array_search( $search_directory, \O2System::$active[ 'directories' ]->getArrayCopy() ) )
							{
								\O2System::$active[ 'directory' ] = \O2System::$active[ 'directories' ]->seek( $directory_key );
							}

							// Now we need to update the URI Routed Segments
							$rsegments = explode( '/', \O2System::$active[ 'module' ]->segments );
						}

						break;
					}
				}

				if ( \O2System::$active->offsetExists( 'controller' ) )
				{
					$x_realpath = explode( 'Controllers\\', \O2System::$active[ 'controller' ]->class );
					$x_segments = array_map( 'strtolower', explode( '\\', end( $x_realpath ) ) );
					$rsegments = array_merge( $rsegments, $x_segments );

					if ( $validate_segment_key = array_search( \O2System::$active[ 'controller' ]->parameter, $validate_segments ) )
					{
						$validate_segments = array_slice( $validate_segments, $validate_segment_key );
						$validate_segments = array_values( $validate_segments );
					}

					break;
				}
			}

			if ( \O2System::$active->offsetExists( 'controller' ) )
			{
				$method_segment = 'index';

				if ( isset( $validate_segments[ 0 ] ) )
				{
					if ( ! filter_var( $validate_segments[ 0 ], FILTER_VALIDATE_INT ) )
					{
						$method_segment = str_replace( '-', '_', $validate_segments[ 0 ] );

						if ( in_array( $method_segment, [ 'add_new', 'edit' ] ) )
						{
							$method_segment = 'form';
						}

						array_shift( $validate_segments );
					}
				}

				if ( isset( \O2System::$active[ 'controller' ]->method ) )
				{
					// _route() method exists
					if ( \O2System::$active[ 'controller' ]->method === '_route' )
					{
						if ( \O2System::$active[ 'controller' ]->isPublicMethod( $method_segment ) )
						{
							// Route to public controller method()
							\O2System::$active[ 'controller' ]->params[ 0 ] = $method_segment;
						}
						elseif ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
						{
							// Route to protected controller _method()
							\O2System::$active[ 'controller' ]->params[ 0 ] = '_' . $method_segment;
						}
						else
						{
							if ( \O2System::$active[ 'controller' ]->isPublicMethod( 'index' ) )
							{
								// Route to public controller index()
								\O2System::$active[ 'controller' ]->params[ 0 ] = 'index';
								\O2System::$active[ 'controller' ]->params[ 1 ] = $method_segment;
							}
							elseif ( \O2System::$active[ 'controller' ]->isProtectedMethod( 'index' ) )
							{
								// Route to protected controller _index()
								\O2System::$active[ 'controller' ]->params[ 0 ] = '_index';
								\O2System::$active[ 'controller' ]->params[ 1 ] = $method_segment;
							}
							else
							{
								// Assign params[0] as method
								\O2System::$active[ 'controller' ]->params[ 0 ] = $method_segment;
							}
						}
					}
					// index() method exists
					elseif ( \O2System::$active[ 'controller' ]->method === 'index' )
					{
						if ( $method_segment !== 'index' )
						{
							if ( is_ajax() )
							{
								if ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
								{
									// Route to public controller method()
									\O2System::$active[ 'controller' ]->method = '_' . $method_segment;
								}
								else
								{
									$this->showError( 403 );
								}
							}
							elseif ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
							{
								$this->showError( 403 );
							}
							elseif ( \O2System::$active[ 'controller' ]->isPublicMethod( $method_segment ) )
							{
								// Route to public controller method()
								\O2System::$active[ 'controller' ]->method = $method_segment;
							}
							else
							{
								\O2System::$active[ 'controller' ]->params[] = $method_segment;
							}
						}
					}
				}
				elseif ( \O2System::$active[ 'controller' ] instanceof \O2System\Metadata\Controller )
				{
					if ( is_ajax() )
					{
						if ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
						{
							// Route to public controller method()
							\O2System::$active[ 'controller' ]->method = '_' . $method_segment;
						}
						else
						{
							$this->showError( 403 );
						}
					}
					elseif ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
					{
						$this->showError( 403 );
					}
					elseif ( \O2System::$active[ 'controller' ]->isPublicMethod( $method_segment ) )
					{
						// Route to public controller method()
						\O2System::$active[ 'controller' ]->method = $method_segment;
					}
					else
					{
						$this->showError( 404 );
					}
				}

				if ( isset( \O2System::$active[ 'controller' ]->method ) )
				{
					// We need to update again the URI Routed segments
					array_push( $rsegments, \O2System::$active[ 'controller' ]->method );

					\O2System::$active[ 'URI' ]->setSegments( $rsegments, 'rsegments' );

					if ( \O2System::$active[ 'controller' ]->method === '_route' )
					{
						if ( \O2System::$active[ 'controller' ]->params[ 0 ] === \O2System::$active[ 'controller' ]->parameter )
						{
							\O2System::$active[ 'controller' ]->params[ 0 ] = 'index';
						}
					}

					// @todo validate other segments
					foreach ( $validate_segments as $segment )
					{
						if ( $this->_validateSegment( $segment ) === FALSE )
						{
							if ( \O2System::$active[ 'controller' ]->method === '_route' )
							{
								\O2System::$active[ 'controller' ]->params[ 1 ][] = $segment;
							}
							else
							{
								\O2System::$active[ 'controller' ]->params[] = $segment;
							}
						}
					}
				}

				unset( \O2System::$active[ 'URI' ]->isegments, \O2System::$active[ 'URI' ]->istring );

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

	protected function _isValidController( $filepath )
	{
		if ( file_exists( $filepath ) )
		{
			$controller = new \O2System\Metadata\Controller( $filepath );

			return $controller;
		}

		return FALSE;
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
			$class = 'Error';
			$method = 'index';
		}

		unset( \O2System::$active[ 'controller' ] );

		$rsegments = array();

		if ( \O2System::$active->offsetExists( 'module' ) )
		{
			$directory = ROOTPATH . \O2System::$active[ 'module' ]->realpath . 'controllers' . DIRECTORY_SEPARATOR;

			if ( $controller = $this->_isValidController( $directory . prepare_filename( $class ) . '.php' ) )
			{
				\O2System::$active[ 'controller' ] = $controller;

				if ( \O2System::$active[ 'modules' ]->isEmpty() === FALSE )
				{
					foreach ( \O2System::$active[ 'modules' ] as $key => $module )
					{
						if ( $controller->namespace === $module->namespace . 'Controllers\\' )
						{
							\O2System::$active[ 'module' ] = \O2System::$active[ 'modules' ]->seek( $key );
							break;
						}
					}
				}

				if ( \O2System::$active->offsetExists( 'module' ) )
				{
					$search_namespace = explode( 'Controllers\\', $controller->namespace );
					$search_namespace = reset( $search_namespace );

					if ( $namespace_key = array_search( $search_namespace, \O2System::$active[ 'namespaces' ]->getArrayCopy() ) )
					{
						\O2System::$active[ 'directory' ] = \O2System::$active[ 'namespaces' ]->seek( $namespace_key );
					}

					$search_directory = explode( 'controllers/', $directory );
					$search_directory = reset( $search_directory );

					if ( $directory_key = array_search( $search_directory, \O2System::$active[ 'directories' ]->getArrayCopy() ) )
					{
						\O2System::$active[ 'directory' ] = \O2System::$active[ 'directories' ]->seek( $directory_key );
					}

					// Now we need to update the URI Routed Segments
					$rsegments = explode( '/', \O2System::$active[ 'module' ]->segments );
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
				\O2System::$active[ 'controller' ]->params = array( $code );
			}
			elseif ( \O2System::$active[ 'controller' ]->method === '_route' )
			{
				// We need to update again the URI Routed segments
				array_push( $rsegments, \O2System::$active[ 'controller' ]->parameter );
				array_push( $rsegments, \O2System::$active[ 'controller' ]->method );

				\O2System::$active[ 'URI' ]->setSegments( $rsegments, 'rsegments' );

				\O2System::$active[ 'controller' ]->params = array(
					'index',
					array( $code ),
				);
			}
			else
			{
				throw new Exception( 'Undefined Error Method', 412 );
			}

			return TRUE;
		}

		return FALSE;
	}

	public function baseURL( $uri = NULL, $suffix = NULL, $protocol = NULL )
	{
		if ( \O2System::$config->offsetExists( 'route_url' ) )
		{
			$route_url = \O2System::$config->offsetGet( 'route_url' );
			$route_url = is_array( $route_url ) ? implode( '/', $route_url ) : $route_url;

			if ( strpos( $route_url, 'http' ) !== FALSE )
			{
				if ( isset( $uri ) )
				{
					$uri = is_array( $uri ) ? implode( '/', $uri ) : $uri;
					$route_url = $route_url . trim( $uri, '/' );
				}

				if ( isset( $suffix ) )
				{
					if ( is_bool( $suffix ) )
					{
						$URI = \O2System::$config->offsetGet( 'URI' );
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

				return \O2System::$config->baseURL( $uri, $suffix, $protocol );
			}
		}

		return \O2System::$config->baseURL( $uri, $suffix, $protocol );
	}
}