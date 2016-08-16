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

namespace O2System\Core\Request\Handlers;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Registry\Metadata\Controller;

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
class Routing
{
	/**
	 * Router Class Configuration
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $config = [ ];

	/**
	 * Validate Methods
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $validateMethods = [ ];

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
			$this->config = $config;
		}

		// Build the reflection to determine validation methods
		$reflection = new \ReflectionClass( get_called_class() );

		foreach ( $reflection->getMethods() as $method )
		{
			if ( strpos( $method->name, '_isValid' ) !== FALSE )
			{
				$this->validateMethods[] = $method->name;
			}
		}

		if ( \O2System::$request->uri->getTotalSegments( 'segments' ) > 0 )
		{
			$this->_parseRoutes();
		}

		if ( \O2System::$request->uri->getTotalSegments( 'isegments' ) == 0 )
		{
			$this->_setDefaultController();
		}

		if ( $this->_validateRequest() === FALSE )
		{
			$this->showError( 404 );
		}

		\O2System::$log->debug( 'LOG_DEBUG_REQUEST_ROUTING_CLASS_INITIALIZED' );
	}

	// ------------------------------------------------------------------------

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
		$string = \O2System::$request->uri->string;

		// Get HTTP verb
		$http_verb = isset( $_SERVER[ 'REQUEST_METHOD' ] ) ? strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) : 'cli';

		// Is there a literal match?  If so we're done
		if ( isset( $this->config[ $string ] ) )
		{
			// Check default routes format
			if ( is_string( $this->config[ $string ] ) )
			{
				\O2System::$request->uri->setSegments( $this->config[ $string ], 'rsegments' );
				\O2System::$request->uri->setSegments( $this->config[ $string ], 'isegments' );

				return;
			}
			// Is there a matching http verb?
			elseif ( is_array( $this->config[ $string ] ) AND isset( $this->config[ $string ][ $http_verb ] ) )
			{
				\O2System::$request->uri->setSegments( $this->config[ $string ][ $http_verb ], 'rsegments' );
				\O2System::$request->uri->setSegments( $this->config[ $string ][ $http_verb ], 'isegments' );

				return;
			}
		}

		$route = $this->config;
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

					\O2System::$request->uri->setSegments( $value, 'rsegments' );
					\O2System::$request->uri->setSegments( $value, 'isegments' );

					return;
					break;
				}
			}
		}

		// If we got this far it means we didn't encounter a
		// matching route so we'll set to original parsed segments
		return;
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
	 * @return  bool
	 */
	protected function _setDefaultController()
	{
		$controllerSegments = [ ];

		if ( isset( $this->config[ 'default_controller' ] ) )
		{
			sscanf( $this->config[ 'default_controller' ], '%[^/]/%s', $class, $method );

			$controllerSegments[] = $class;

			$method = empty( $method ) ? 'index' : $method;
		}

		if ( $module = \O2System::$request->modules->current() )
		{
			$controllerSegments[] = $module->parameter;
		}

		foreach ( $controllerSegments as $controllerSegment )
		{
			$controllerFilepath = NULL;

			if ( $module = \O2System::$request->modules->current() )
			{
				if ( FALSE !== ( $moduleControllerFilepath = $module->hasController( $controllerSegment, TRUE, TRUE ) ) )
				{
					$controllerFilepath = $moduleControllerFilepath;
				}
			}
			else
			{
				$controllerFilepath = \O2System::$request->directories->getCurrent( 'Controllers' ) . prepare_filename( $controllerSegment ) . '.php';
			}

			if ( $controller = $this->loadController( $controllerFilepath ) )
			{
				\O2System::$log->debug( 'LOG_DEBUG_REQUEST_ROUTING_SET_DEFAULT_CONTROLLER' );

				$invalidSegments = \O2System::$request->uri->isegments;
				array_unshift( $invalidSegments, $controllerSegment );

				$this->setupController( $controller, array_values( $invalidSegments ) );

				return TRUE;

				break;
			}
		}

		$this->showError( 404 );
	}

	// --------------------------------------------------------------------

	public function loadController( $filepath )
	{
		if ( is_file( $filepath ) )
		{
			$controller = new Controller( $filepath );

			return $controller;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function setupController( Controller $controller, array $invalidSegments = [ ] )
	{
		if ( ! empty( $invalidSegments ) )
		{
			\O2System::$request->uri->setSegments( $invalidSegments, 'isegments' );
		}

		// Add requested namespace
		\O2System::$request->namespaces[] = $controller->namespace;
		\O2System::$request->namespaces->setCurrent( \O2System::$request->namespaces->count() - 1 );

		$invalidSegments = \O2System::$request->uri->isegments;
		$invalidSegments = array_diff( $invalidSegments, [ $controller->parameter ] );
		$methodSegment   = 'index';

		if ( count( $invalidSegments ) > 0 )
		{
			if ( filter_var( reset( $invalidSegments ), FILTER_VALIDATE_INT ) === FALSE )
			{
				$methodSegment = camelcase( reset( $invalidSegments ) );

				if ( FALSE !== ( in_array( $methodSegment, [ 'add', 'addNew', 'edit', 'update' ] ) ) )
				{
					$methodSegment = 'form';
				}
				elseif ( $methodSegment === 'form' )
				{
					return $this->showError( 403 );
				}

				array_shift( $invalidSegments );
			}
		}

		if ( isset( $controller->method ) )
		{
			// _route() method exists
			if ( $controller->method === '_route' )
			{
				if ( $controller->isPublicMethod( $methodSegment ) )
				{
					// Route to public controller method()
					$controller->params[ 0 ] = $methodSegment;
					$controller->params[ 1 ] = $invalidSegments;
				}
				elseif ( $controller->isProtectedMethod( $methodSegment ) )
				{
					// Route to protected controller _method()
					$controller->params[ 0 ] = '_' . $methodSegment;
					$controller->params[ 1 ] = $invalidSegments;
				}
				else
				{
					if ( $controller->isPublicMethod( 'index' ) )
					{
						// Route to public controller index()
						$controller->params[ 0 ] = 'index';
						$controller->params[ 1 ] = $methodSegment;

						$controller->params = array_merge( $controller->params, $invalidSegments );
					}
					elseif ( $controller->isProtectedMethod( 'index' ) )
					{
						// Route to protected controller _index()
						$controller->params[ 0 ] = '_index';
						$controller->params[ 1 ] = $methodSegment;

						$controller->params = array_merge( $controller->params, $invalidSegments );
					}
					else
					{
						$controller->params[ 0 ] = $methodSegment;

						$controller->params = array_merge( $controller->params, $invalidSegments );
					}
				}
			}
			// index() method exists
			elseif ( $controller->method === 'index' )
			{
				if ( $methodSegment === 'index' )
				{
					$controller->params = $invalidSegments;
				}
				else
				{
					$controller->params[ 0 ] = $methodSegment;

					$controller->params = array_merge( $controller->params, $invalidSegments );
				}
			}
		}
		else
		{
			if ( is_ajax() )
			{
				if ( $controller->isProtectedMethod( $methodSegment ) )
				{
					// Route to public controller method()
					$controller->method = '_' . $methodSegment;
					$controller->params = $invalidSegments;
				}
				else
				{
					return $this->showError( 403 );
				}
			}
			elseif ( $controller->isProtectedMethod( $methodSegment ) )
			{
				return $this->showError( 403 );
			}
			elseif ( $controller->isPublicMethod( $methodSegment ) )
			{
				// Route to public controller method()
				$controller->method = $methodSegment;
				$controller->params = $invalidSegments;
			}
			else
			{
				return $this->showError( 404 );
			}
		}

		if ( isset( $controller->method ) )
		{
			\O2System::$request->uri->pushSegment( $controller->method, 'rsegments' );
		}

		\O2System::$request->controller = $controller;
	}

	/**
	 * Route Error
	 *
	 * @access  public
	 * @return  bool
	 */
	public function showError( $code )
	{
		if ( ! empty( $this->config[ $code . '_override' ] ) )
		{
			if ( sscanf( $this->config[ $code . '_override' ], '%[^/]/%s', $class, $method ) !== 2 )
			{
				$method = 'index';
			}
		}

		if ( empty( $class ) AND empty( $method ) )
		{
			$class  = 'error';
			$method = 'index';
		}

		\O2System::$request->controller = NULL;

		if ( $module = \O2System::$request->modules->current() )
		{
			if ( $directory = $module->hasController( $class, TRUE, 'directory' ) )
			{
				$directory = rtrim( $directory, DIRECTORY_SEPARATOR );

				if ( $controller = $this->loadController( $directory . DIRECTORY_SEPARATOR . prepare_filename( $class ) . '.php' ) )
				{
					$this->setupController( $controller, [ $class, $method, $code ] );

					return TRUE;
				}
			}
		}

		if ( isset( \O2System::$request->controller ) )
		{
			if ( $controller = $this->loadController( APPSPATH . 'Controllers' . DIRECTORY_SEPARATOR . prepare_filename( $class ) . '.php' ) )
			{
				$this->setupController( $controller, [ $class, $method, $code ] );

				return TRUE;
			}
		}

		if ( isset( \O2System::$request->controller ) )
		{
			if ( $controller = $this->loadController( SYSTEMPATH . 'Controllers' . DIRECTORY_SEPARATOR . prepare_filename( $class ) . '.php' ) )
			{
				$this->setupController( $controller, [ $class, $method, $code ] );

				return TRUE;
			}
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
		foreach ( $this->validateMethods as $method )
		{
			if ( $this->{$method}( $segment ) === TRUE )
			{
				return TRUE;
			}
		}

		return FALSE;
	}

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
		if ( empty( \O2System::$request->controller ) )
		{
			$validateSegments = \O2System::$request->uri->isegments;

			$numValidateSegments = count( $validateSegments );

			// Get Controller Directories
			$controllerDirectories = \O2System::$request->directories->getSubDirectories( 'Controllers', TRUE );

			for ( $i = 0; $i <= $numValidateSegments; $i++ )
			{
				// Define Routed Segments
				$rSegments = array_slice( $validateSegments, 0, ( $numValidateSegments - $i ) );

				// Define Controller Filename
				$controllerFilename = implode( DIRECTORY_SEPARATOR, $rSegments );
				$controllerFilename = prepare_filename( $controllerFilename ) . '.php';

				// Search through directories
				foreach ( $controllerDirectories as $controllerDirectory )
				{
					if ( $controller = $this->loadController( $controllerDirectory . $controllerFilename ) )
					{
						// Define Invalid Segments
						$invalidSegments = array_diff( $validateSegments, $rSegments );

						// Reset Routed Segments
						\O2System::$request->uri->setSegments( $rSegments, 'rsegments' );
						\O2System::$request->uri->reindexSegments( 'rsegments' );

						// Reset Invalid Segments
						\O2System::$request->uri->setSegments( array_filter( $invalidSegments ), 'isegments' );
						\O2System::$request->uri->reindexSegments( 'isegments' );

						// Add PSR4 Namespace
						\O2System::$load->addNamespace( $controller->namespace, $controller->directory );

						// Setup Controller
						$this->setupController( $controller );
						break;
					}
				}

				// If controller has been set
				if ( $controller instanceof Controller )
				{
					break;
				}
			}

			if ( isset( \O2System::$request->controller ) === FALSE )
			{
				return $this->_setDefaultController();
			}

			return TRUE;
		}

		return (bool) isset( \O2System::$request->controller );
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