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

use O2System\Metadata\Module;

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
		// Build the reflection to determine validation methods
		$reflection = new \ReflectionClass( get_called_class() );

		foreach ( $reflection->getMethods() as $method )
		{
			if ( strpos( $method->name, '_is_' ) !== FALSE )
			{
				$this->_validate_methods[] = $method->name;
			}
		}

		// Route Mapping
		$this->_setRouting();
	}

	// ------------------------------------------------------------------------

	/**
	 * Set route mapping
	 *
	 * Determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @return    void
	 */
	protected function _setRouting()
	{
		// Load the router config
		if ( $config = \O2System::$config->load( 'router', TRUE ) )
		{
			$this->_config = $config;
		}

		// Set active directory and namespace
		if ( \O2System::$active->offsetExists( 'module' ) )
		{
			\O2System::$active[ 'directory' ] = ROOTPATH . \O2System::$active[ 'module' ]->realpath;
			\O2System::$active[ 'namespace' ] = \O2System::$active[ 'module' ]->namespace;
		}
		else
		{
			\O2System::$active[ 'directory' ] = APPSPATH;
			\O2System::$active[ 'namespace' ] = \O2System::$config[ 'namespace' ];
		}

		// Is there already has a pages request
		if ( \O2System::$active->offsetExists( 'page' ) )
		{
			if ( is_file( SYSTEMPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' ) )
			{
				if ( class_exists( 'O2System\Controllers\Pages' ) )
				{
					$controller = new \O2System\Metadata\Controller( SYSTEMPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' );
				}

				\O2System::$active[ 'controllers' ][ 'o2system_pages' ] = $controller;
				\O2System::$active[ 'controller' ] = $controller;
			}

			if ( is_file( APPSPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' ) )
			{
				$controller = new \O2System\Metadata\Controller( APPSPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' );
				\O2System::$active[ 'controllers' ][ 'applications_pages' ] = $controller;
				\O2System::$active[ 'controller' ] = $controller;
			}

			if ( \O2System::$active->offsetExists( 'module' ) )
			{
				if ( is_file( ROOTPATH . \O2System::$active[ 'module' ]->realpath . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' ) )
				{
					$controller = new \O2System\Metadata\Controller( \O2System::$active[ 'module' ]->realpath . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' );

					\O2System::$active[ 'controllers' ][ 'modules_pages' ] = $controller;
					\O2System::$active[ 'controller' ] = $controller;
				}
			}

			\O2System::$active[ 'controller' ]->method = 'index';
			\O2System::$active[ 'controller' ]->params = \O2System::URI()->rsegments;
		}
		else
		{
			// Is there anything to parse?
			if ( empty( \O2System::URI()->rsegments ) )
			{
				$this->_setDefaultController();
			}
			else
			{
				$this->_parseRoutes();
			}
		}
	}

	// --------------------------------------------------------------------

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
			$this->show_404();
		}
		else
		{
			if ( \O2System::$active->offsetExists( 'controller' ) === FALSE )
			{
				// Is the method being specified?
				if ( sscanf( $this->_config[ 'default_controller' ], '%[^/]/%s', $class, $method ) !== 2 )
				{
					$method = 'index';
				}

				$this->_setRequest( [ $class, $method ] );

				\O2System::Log( 'debug', 'Default controller set.' );
			}
			else
			{
				$this->_setRequest( [ 'index' ] );
			}
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
		$rstring = \O2System::URI()->rstring;

		// Get HTTP verb
		$http_verb = isset( $_SERVER[ 'REQUEST_METHOD' ] ) ? strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) : 'cli';

		// Is there a literal match?  If so we're done
		if ( isset( $this->_config[ $rstring ] ) )
		{
			// Check default routes format
			if ( is_string( $this->_config[ $rstring ] ) )
			{
				$this->_setRequest( explode( '/', $this->_config[ $rstring ] ) );

				return;
			}
			// Is there a matching http verb?
			elseif ( is_array( $this->_config[ $rstring ] ) AND isset( $this->_config[ $rstring ][ $http_verb ] ) )
			{
				$this->_setRequest( explode( '/', $this->_config[ $rstring ][ $http_verb ] ) );

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

					$this->_setRequest( explode( '/', $value ) );

					return;
				}
			}
		}

		// If we got this far it means we didn't encounter a
		// matching route so we'll set the site default route
		$this->_setRequest( \O2System::URI()->rsegments );
	}

	// --------------------------------------------------------------------

	/**
	 * Set request route
	 *
	 * Takes an array of URI segments as input and sets the class/method
	 * to be called.
	 *
	 * @used-by Router::_parseRoutes()
	 *
	 * @param   array $segments URI Segments
	 *
	 * @access  protected
	 */
	protected function _setRequest( $segments )
	{
		$this->_validateRequest( $segments );

		// If we don't have any controller set
		if ( \O2System::$active->offsetExists( 'controller' ) === FALSE )
		{
			$this->error404();
		}
		else
		{
			// If the controller method has been set let's update the URI Routed segments
			if ( empty( \O2System::$active[ 'controller' ]->method ) )
			{
				$this->error404();
			}
		}
	}
	// ------------------------------------------------------------------------

	/**
	 * Validate request
	 *
	 * Attempts validate the URI request and determine the controller path.
	 *
	 * @used-by    Router::_setRequest()
	 *
	 * @param    array $segments URI segments
	 *
	 * @return    mixed    URI segments
	 */
	final protected function _validateRequest( $segments )
	{
		// Find controller first
		$directories = array_reverse( \O2System::Load()->getPackagePaths( 'controllers', TRUE ) );

		$num_segments = count( $segments );
		$valid_segments = array();

		for ( $i = 0; $i <= $num_segments; $i++ )
		{
			$valid_segments = array_slice( $segments, 0, ( $num_segments - $i ) );

			if ( \O2System::$active->offsetExists( 'module' ) )
			{
				$parent_modules = \O2System::$registry->find_parents( \O2System::$active[ 'module' ] );
				array_unshift( $parent_modules, \O2System::$active[ 'module' ] );

				$module = FALSE;

				foreach ( $parent_modules as $parent_module )
				{
					if ( $module = \O2System::$registry->find( $parent_module->segments . '/' . implode( '/', $valid_segments ), 'modules' ) )
					{
						\O2System::$active[ 'module' ] = $module;
						\O2System::$active[ 'directory' ] = ROOTPATH . $module->realpath;
						\O2System::$active[ 'namespace' ] = ltrim( $module->namespace, '\\' ) . '\\';

						// Reset URI Routed Segments
						\O2System::URI()->rsegments = array_diff( $segments, $valid_segments );
						\O2System::URI()->rstring = implode( '/', \O2System::URI()->rsegments );
						\O2System::URI()->reindex_rsegments();

						// Reset Controller
						if ( \O2System::$active->offsetExists( 'controller' ) )
						{
							\O2System::$active->offsetUnset( 'controller' );
						}

						break;
					}
				}

				if ( $module instanceof Module )
				{
					$this->_setRouting();

					return;
					break;
				}
				elseif ( empty( $valid_segments ) )
				{
					$valid_segments = [ \O2System::$active[ 'module' ]->parameter ];
				}
			}

			$filename = end( $valid_segments );

			if ( $filename === 'index' )
			{
				array_pop( $valid_segments );
				$filename = end( $valid_segments );
			}

			$folder_segments = array_map( function ( $segment )
			{
				return str_replace( '-', '_', $segment );
			}, array_diff( $valid_segments, [ $filename ] ) );

			$filename = prepare_filename( $filename );

			$sub_directory = implode( DIRECTORY_SEPARATOR, $folder_segments ) . DIRECTORY_SEPARATOR;
			$sub_directory = ltrim( $sub_directory, DIRECTORY_SEPARATOR );

			foreach ( $directories as $namespace => $directory )
			{
				if ( \O2System::$active->offsetExists( 'controller' ) )
				{
					break;
				}

				$class_namespace = rtrim( $namespace, '\\' ) . '\\Controllers\\';

				if ( empty( $sub_directory ) )
				{
					$sub_directory = strtolower( $filename ) . DIRECTORY_SEPARATOR;

					if ( is_dir( $directory . $sub_directory ) )
					{
						$directory = $directory . $sub_directory;

						$class_namespace = $class_namespace . prepare_class_name( $sub_directory );
						\O2System::Load()->addNamespace( $class_namespace, $directory );
					}
				}
				elseif ( is_dir( $directory . $sub_directory ) )
				{
					$directory = $directory . $sub_directory;

					$class_namespace = $class_namespace . prepare_class_name( $sub_directory );
					\O2System::Load()->addNamespace( $class_namespace, $directory );

					$sub_directory = strtolower( $filename ) . DIRECTORY_SEPARATOR;

					if ( is_dir( $directory . $sub_directory ) )
					{
						$directory = $directory . $sub_directory;
						\O2System::Load()->addNamespace( $class_namespace, $directory );
					}
				}

				if ( is_file( $directory . $filename . '.php' ) )
				{
					$controller = new \O2System\Metadata\Controller( $directory . $filename . '.php' );

					if ( in_array( $controller->class, array( 'O2System\Controllers\Widgets' ) ) )
					{
						continue;
					}

					if ( $controller->reflection instanceof \ReflectionClass )
					{
						try
						{
							if ( $uri_strict = $controller->reflection->getStaticPropertyValue( 'uri_strict' ) )
							{
								if ( \O2System::URI()->rstring !== $uri_strict )
								{
									continue;
								}
							}
						}
						catch ( \ReflectionException $e )
						{
							\O2System::Log( 'debug', 'Non Controller URI Strict.' );
						}

						\O2System::$active[ 'controllers' ][ $controller->parameter ] = $controller;
						\O2System::$active[ 'controller' ] = $controller;

						// Validate namespace
						if ( isset( $parent_modules ) )
						{
							foreach ( $parent_modules as $parent_module )
							{
								if ( $parent_module->namespace === $namespace )
								{
									// We need to update active module, directory and namespace
									\O2System::$active[ 'module' ] = $parent_module;
									\O2System::$active[ 'directory' ] = ROOTPATH . $parent_module->realpath;
									\O2System::$active[ 'namespace' ] = rtrim( $parent_module->namespace, '\\' ) . '\\';

									// Now we need to update the URI Routed Segments
									\O2System::URI()->rsegments = explode( '/', $parent_module->segments );
									array_push( \O2System::URI()->rsegments, \O2System::$active[ 'controller' ]->parameter );

									\O2System::URI()->rsegments = array_unique( \O2System::URI()->rsegments );

									\O2System::URI()->reindex_rsegments();
									\O2System::URI()->rstring = implode( '/', \O2System::URI()->rsegments );

									break;
								}
							}
						}

						break;
					}
				}
			}
		}

		if ( \O2System::$active->offsetExists( 'controller' ) )
		{
			if ( $segment_key = array_search( \O2System::$active[ 'controller' ]->parameter, $segments ) )
			{
				$segments = array_slice( $segments, $segment_key );
			}

			$method_segment = 'index';

			if ( isset( $segments[ 0 ] ) )
			{
				if( ! filter_var($segments[0], FILTER_VALIDATE_INT ) )
				{
					$method_segment = str_replace( '-', '_', $segments[ 0 ] );

					if ( in_array( $method_segment, [ 'add_new', 'edit' ] ) )
					{
						$method_segment = 'form';
					}

					array_shift( $segments );
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
					if( $method_segment !== 'index' )
					{
						if ( \O2System::Input()->is_ajax_request() )
						{
							if ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
							{
								// Route to public controller method()
								\O2System::$active[ 'controller' ]->method = '_' . $method_segment;
							}
							else
							{
								$this->error403();
							}
						}
						elseif ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
						{
							$this->error403();
						}
						elseif ( \O2System::$active[ 'controller' ]->isPublicMethod( $method_segment ) )
						{
							// Route to public controller method()
							\O2System::$active[ 'controller' ]->method = $method_segment;
						}
					}
				}
			}
			else
			{
				if ( \O2System::Input()->is_ajax_request() )
				{
					if ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
					{
						// Route to public controller method()
						\O2System::$active[ 'controller' ]->method = '_' . $method_segment;
					}
					else
					{
						$this->error403();
					}
				}
				elseif ( \O2System::$active[ 'controller' ]->isProtectedMethod( $method_segment ) )
				{
					$this->error403();
				}
				elseif ( \O2System::$active[ 'controller' ]->isPublicMethod( $method_segment ) )
				{
					// Route to public controller method()
					\O2System::$active[ 'controller' ]->method = $method_segment;
				}
			}

			if ( isset( \O2System::$active[ 'controller' ]->method ) )
			{
				// We need to update again the URI Routed segments
				array_push( \O2System::URI()->rsegments, \O2System::$active[ 'controller' ]->method );

				\O2System::URI()->reindex_rsegments();
				\O2System::URI()->rstring = implode( '/', \O2System::URI()->rsegments );
			}

			// @todo validate other segments
			foreach ( $segments as $segment )
			{
				if ( $this->_validateSegment( $segment ) === FALSE )
				{
					\O2System::$active[ 'controller' ]->params[] = $segment;
				}
			}
		}

	}
	// ------------------------------------------------------------------------

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
	public function error403()
	{
		if ( ! empty( $this->_config[ '403_override' ] ) )
		{
			if ( sscanf( $this->_config[ '403_override' ], '%[^/]/%s', $class, $method ) !== 2 )
			{
				$method = 'index';
			}

			$this->_validateRequest( [ $class, $method ] );
		}
		else
		{
			$segments = [ 'error', 'index' ];

			if ( \O2System::$active->offsetExists( 'module' ) )
			{
				array_unshift( $segments, \O2System::$active[ 'module' ]->parameter );
			}

			array_push( $segments, 403 );

			$this->_validateRequest( $segments );
		}

		if ( empty( \O2System::$active[ 'controller' ]->method ) )
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Override 404
	 *
	 * @access  public
	 * @return  bool
	 */
	public function error404()
	{
		if ( ! empty( $this->_config[ '404_override' ] ) )
		{
			if ( sscanf( $this->_config[ '404_override' ], '%[^/]/%s', $class, $method ) !== 2 )
			{
				$method = 'index';
			}

			$this->_validateRequest( [ $class, $method ] );
		}
		else
		{
			$segments = [ 'error', 'index' ];

			if ( \O2System::$active->offsetExists( 'module' ) )
			{
				array_unshift( $segments, \O2System::$active[ 'module' ]->parameter );
			}

			array_push( $segments, 404 );

			$this->_validateRequest( $segments );
		}

		if ( empty( \O2System::$active[ 'controller' ]->method ) )
		{
			return FALSE;
		}

		return TRUE;
	}

	public function base_url( $uri = NULL, $suffix = NULL, $protocol = NULL )
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

				return \O2System::$config->base_url( $uri, $suffix, $protocol );
			}
		}

		return \O2System::$config->base_url( $uri, $suffix, $protocol );
	}
}