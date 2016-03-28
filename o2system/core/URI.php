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

use O2System\Metadata\Controller;

/**
 * System URI
 *
 * Parses URIs and determines routing
 *
 * @package        O2System
 * @subpackage     core
 * @category       Core Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/uri.html
 */
final class URI
{
	/**
	 * URI Class Configuration
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_config = array();

	/**
	 * Current URI Segments
	 *
	 * @access  public
	 * @type    array
	 */
	public $segments = array();

	/**
	 * Routed URI Segments
	 *
	 * @access  public
	 * @type    array
	 */
	public $rsegments = array();

	/**
	 * Current URI String
	 *
	 * @access  public
	 * @type    string
	 */
	public $string = NULL;

	/**
	 * Routed URI String
	 *
	 * @access  public
	 * @type    string
	 */
	public $rstring = NULL;

	// --------------------------------------------------------------------

	/**
	 * URI Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		// Set URI Configuration
		$this->_config = \O2System::$config[ 'URI' ];

		// Set all active controllers
		if ( is_file( SYSTEMPATH . 'core' . DIRECTORY_SEPARATOR . 'Controller.php' ) )
		{
			$controller = new \stdClass();
			$controller->parameter = 'o2system';
			$controller->class = '\O2System\Controller';
			$controller->realpath = SYSTEMPATH . 'core' . DIRECTORY_SEPARATOR . 'Controller.php';

			\O2System::$active[ 'controllers' ][ 'o2system' ] = $controller;
		}

		if ( is_file( APPSPATH . 'core' . DIRECTORY_SEPARATOR . 'Controller.php' ) )
		{
			$controller = new \stdClass();
			$controller->parameter = 'applications';
			$controller->class = \O2System::$config[ 'namespace' ] . 'Core\Controller';
			$controller->realpath = APPSPATH . 'core' . DIRECTORY_SEPARATOR . 'Controller.php';

			\O2System::$active[ 'controllers' ][ 'applications' ] = $controller;
		}

		// Set Request
		$this->set_request();

		\O2System::Log( 'info', 'URI Class Initialized' );
	}
	// --------------------------------------------------------------------

	/**
	 * Fetch the URI String
	 *
	 * @param   string $request URI String Request
	 *
	 * @access  public
	 */
	final public function set_request( $uri_string = NULL )
	{
		if ( isset( $uri_string ) )
		{
			if ( is_array( $uri_string ) )
			{
				$uri_string = implode( '/', $uri_string );
			}

			// Filter out control characters and trim slashes
			$uri_string = trim( remove_invisible_characters( $uri_string, FALSE ), '/' );

			// If the URI contains only a slash we'll kill it
			$uri_string === '/' ? NULL : $uri_string;

			// Split segments into array with indexing
			$segments = explode( '/', 'indexing/ ' . $uri_string );
			unset( $segments[ 0 ] );

			// Populate the segments array
			foreach ( $segments as $key => $segment )
			{
				// Filter segments for security
				if ( $segment = trim( $this->_filter_segment( $segment ) ) )
				{
					$this->rsegments[ $key ] = $segment;
				}
			}
		}
		else
		{
			// If it's a CLI request, ignore the configuration
			if ( is_cli() OR
				( $protocol = strtoupper( $this->_config[ 'protocol' ] ) === 'CLI' )
			)
			{
				$uri_string = $this->_parse_argv();
			}
			elseif ( $protocol = strtoupper( $this->_config[ 'protocol' ] ) )
			{
				empty( $protocol ) && $protocol = 'REQUEST_URI';

				switch ( $protocol )
				{
					case 'AUTO': // For BC purposes only
					case 'REQUEST_URI':
						$uri_string = $this->_parse_request_uri();
						break;
					case 'QUERY_STRING':
						$uri_string = $this->_parse_query_string();
						break;
					case 'PATH_INFO':
					default:
						$uri_string = isset( $_SERVER[ $protocol ] )
							? $_SERVER[ $protocol ]
							: $this->_parse_request_uri();
						break;
				}
			}

			// Filter out control characters and trim slashes
			$uri_string = trim( remove_invisible_characters( $uri_string, FALSE ), '/' );

			// If the URI contains only a slash we'll kill it
			$uri_string === '/' ? NULL : $uri_string;

			$this->string = $uri_string;

			// Split segments into array with indexing
			$segments = explode( '/', 'indexing/ ' . $this->string );
			unset( $segments[ 0 ] );

			// Populate the segments array
			foreach ( $segments as $key => $segment )
			{
				// Filter segments for security
				if ( $segment = trim( $this->_filter_segment( $segment ) ) )
				{
					if ( \O2System::$registry->offsetExists( 'languages' ) )
					{
						if ( isset( \O2System::$registry->languages[ $segment ] ) )
						{
							\O2System::$active[ 'language' ] = \O2System::$registry->languages[ $segment ];

							continue;
						}
					}

					if ( \O2System::$active->offsetExists( 'language' ) )
					{
						$key = $key - 1;
					}

					$this->segments[ $key ] = $segment;
					$this->rsegments[ $key ] = $segment;
				}
			}
		}

		$this->rstring = implode( '/', $this->rsegments );

		// Validate the segments array
		$this->_validate_segments();
	}

	// --------------------------------------------------------------------

	/**
	 * Parse CLI arguments
	 *
	 * Take each command line argument and assume it is a URI segment.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _parse_argv()
	{
		$args = array_slice( $_SERVER[ 'argv' ], 1 );

		return $args ? implode( '/', $args ) : '';
	}

	// --------------------------------------------------------------------

	/**
	 * Parse REQUEST_URI
	 *
	 * Will parse REQUEST_URI and automatically detect the URI from it,
	 * while fixing the query string if necessary.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _parse_request_uri()
	{
		if ( ! isset( $_SERVER[ 'REQUEST_URI' ], $_SERVER[ 'SCRIPT_NAME' ] ) )
		{
			return '';
		}

		$uri = parse_url( $_SERVER[ 'REQUEST_URI' ] );
		$query = isset( $uri[ 'query' ] ) ? $uri[ 'query' ] : '';
		$uri = isset( $uri[ 'path' ] ) ? $uri[ 'path' ] : '';

		if ( isset( $_SERVER[ 'SCRIPT_NAME' ][ 0 ] ) )
		{
			if ( strpos( $uri, $_SERVER[ 'SCRIPT_NAME' ] ) === 0 )
			{
				$uri = (string) substr( $uri, strlen( $_SERVER[ 'SCRIPT_NAME' ] ) );
			}
			elseif ( strpos( $uri, dirname( $_SERVER[ 'SCRIPT_NAME' ] ) ) === 0 )
			{
				$uri = (string) substr( $uri, strlen( dirname( $_SERVER[ 'SCRIPT_NAME' ] ) ) );
			}
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if ( trim( $uri, '/' ) === '' AND strncmp( $query, '/', 1 ) === 0 )
		{
			$query = explode( '?', $query, 2 );
			$uri = $query[ 0 ];
			$_SERVER[ 'QUERY_STRING' ] = isset( $query[ 1 ] ) ? $query[ 1 ] : '';
		}
		else
		{
			$_SERVER[ 'QUERY_STRING' ] = $query;
		}

		parse_str( $_SERVER[ 'QUERY_STRING' ], $_GET );

		if ( $uri === '/' || $uri === '' )
		{
			return '/';
		}

		// Do some final cleaning of the URI and return it
		return $this->_remove_relative_directory( $uri );
	}

	// --------------------------------------------------------------------

	/**
	 * Parse QUERY_STRING
	 *
	 * Will parse QUERY_STRING and automatically detect the URI from it.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _parse_query_string()
	{
		$uri = isset( $_SERVER[ 'QUERY_STRING' ] ) ? $_SERVER[ 'QUERY_STRING' ] : @getenv( 'QUERY_STRING' );

		if ( trim( $uri, '/' ) === '' )
		{
			return '';
		}
		elseif ( strncmp( $uri, '/', 1 ) === 0 )
		{
			$uri = explode( '?', $uri, 2 );
			$_SERVER[ 'QUERY_STRING' ] = isset( $uri[ 1 ] ) ? $uri[ 1 ] : '';
			$uri = rawurldecode( $uri[ 0 ] );
		}

		parse_str( $_SERVER[ 'QUERY_STRING' ], $_GET );

		return $this->_remove_relative_directory( $uri );
	}

	// ------------------------------------------------------------------------

	/**
	 * Remove relative directory (../) and multi slashes (///)
	 *
	 * Do some final cleaning of the URI and return it, currently only used in self::_parse_request_uri()
	 *
	 * @param   string $uri URI String
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _remove_relative_directory( $uri )
	{
		$segments = array();
		$segment = strtok( $uri, '/' );

		$base_dirs = explode( '/', str_replace( '\\', '/', ROOTPATH ) );

		while ( $segment !== FALSE )
		{
			if ( ( ! empty( $segment ) || $segment === '0' ) AND
				$segment !== '..' AND
				! in_array( $segment, $base_dirs
				)
			)
			{
				$segments[] = $segment;
			}
			$segment = strtok( '/' );
		}

		return implode( '/', $segments );
	}

	// ------------------------------------------------------------------------

	/**
	 * Filter Segment
	 *
	 * Filters segments for malicious characters.
	 *
	 * @param   string $string URI String
	 *
	 * @access  protected
	 * @return  string
	 * @throws  \HttpRequestException
	 */
	protected function _filter_segment( $string )
	{
		if ( ! empty( $string ) AND
			! empty( $this->_config[ 'permitted_chars' ] ) AND
			! preg_match( '/^[' . $this->_config[ 'permitted_chars' ] . ']+$/i', $string ) AND
			! is_cli()
		)
		{
			throw new Exception( 'The URI you submitted has disallowed characters.' );
		}

		// Convert programatic characters to entities and return
		return str_replace( array( '$', '(', ')', '%28', '%29', $this->_config[ 'suffix' ], '.html' ),    // Bad
		                    array( '&#36;', '&#40;', '&#41;', '&#40;', '&#41;', '' ),    // Good
		                    $string );
	}

	/**
	 * Set URI Segments
	 *
	 * @param bool $routed
	 *
	 * @access  protected
	 * @throws \HttpRequestException
	 */
	protected function _validate_segments()
	{
		if ( empty( $this->rsegments ) ) return;

		// Validate Pages Segments
		if ( is_dir( $page_dir = APPSPATH . 'pages' . DIRECTORY_SEPARATOR ) )
		{
			if ( \O2System::$active->offsetExists( 'language' ) === FALSE )
			{
				if ( \O2System::$registry->offsetExists( 'languages' ) )
				{
					if ( isset( \O2System::$registry->languages[ \O2System::$config['language'] ] ) )
					{
						\O2System::$active[ 'language' ] = \O2System::$registry->languages[ \O2System::$config['language'] ];
					}
				}
			}

			$num_rsegments = count( $this->rsegments );

			for ( $i = 0; $i < $num_rsegments; $i++ )
			{
				$slice_rsegments = array_slice( $this->rsegments, 0, ( $num_rsegments - $i ) );
				$page_filename = implode( DIRECTORY_SEPARATOR, $slice_rsegments );
				$page_filename = str_replace( '-', '_', $page_filename );

				// Is the pages has language directory?
				if ( \O2System::$active->offsetExists( 'language' ) )
				{
					if ( is_dir( $page_dir . DIRECTORY_SEPARATOR . \O2System::$active[ 'language' ]->parameter . DIRECTORY_SEPARATOR ) )
					{
						$page_dir = $page_dir . DIRECTORY_SEPARATOR . \O2System::$active[ 'language' ]->parameter . DIRECTORY_SEPARATOR;
					}
				}

				if ( is_file( $page_filepath = $page_dir . $page_filename . '.phtml' ) )
				{
					\O2System::$active[ 'page' ] = $page_filepath;

					// We didn't have to find the module because is definitely a page
					// The router will be set the active controller into pages controller
					return;

					break;
				}
			}
		}

		// Validate Module Segments
		$num_rsegments = count( $this->rsegments );

		for ( $i = 0; $i < $num_rsegments; $i++ )
		{
			$slice_rsegments = array_slice( $this->rsegments, 0, ( $num_rsegments - $i ) );
			$offset = implode( '/', $slice_rsegments );

			// Find module registry
			if ( $module = \O2System::$registry->find( $offset, 'modules' ) )
			{
				\O2System::$active[ 'module' ] = $module;

				$this->rsegments = array_diff( $this->rsegments, $slice_rsegments );
				$page_filename = implode( DIRECTORY_SEPARATOR, $this->rsegments );

				// Re-load module config
				\O2System::$config->load( 'config' );

				// Find module core controller
				if ( is_file( ROOTPATH . $module->realpath . 'core' . DIRECTORY_SEPARATOR . 'Controller.php' ) )
				{
					$controller = new Controller();
					$controller->parameter = $module->parameter;
					$controller->class = $module->namespace . 'Core\Controller';
					$controller->realpath = ROOTPATH . $module->realpath . 'core' . DIRECTORY_SEPARATOR . 'Controller.php';

					\O2System::$active[ 'controllers' ][ $controller->parameter ] = $controller;
				}

				// Find module default controller
				if ( is_file( ROOTPATH . $module->realpath . 'controllers' . DIRECTORY_SEPARATOR . prepare_filename( $module->parameter ) . '.php' ) )
				{
					$controller = new Controller();
					$controller->parameter = $module->parameter;
					$controller->class = $module->namespace . 'Controllers\\' . prepare_class_name( $module->parameter );
					$controller->realpath = ROOTPATH . $module->realpath . 'controllers' . DIRECTORY_SEPARATOR . prepare_filename( $module->parameter ) . '.php';
					$controller->params = array();
					$controller->public_methods = array();

					if ( class_exists( $controller->class ) )
					{
						$reflection = new \ReflectionClass( $controller->class );

						foreach ( $reflection->getMethods( \ReflectionMethod::IS_PUBLIC ) as $method )
						{
							if ( $method->name === '_route' OR
									! preg_match_all( '(^_)', $method->name, $match )
							)
							{
								$controller->public_methods[ $method->name ] = $method;
							}
						}

						\O2System::$active[ 'controllers' ][ $controller->parameter ] = $controller;
						\O2System::$active[ 'controller' ] = $controller;
					}
				}

				// Re-index routed segments
				$this->reindex_rsegments();

				// Let's try to find a module pages
				if ( is_dir( $page_dir = ROOTPATH . $module->realpath . 'pages' . DIRECTORY_SEPARATOR ) )
				{
					// Is the pages has language directory?
					if ( \O2System::$active->offsetExists( 'language' ) )
					{
						if ( is_dir( $page_dir . DIRECTORY_SEPARATOR . \O2System::$active[ 'language' ]->parameter . DIRECTORY_SEPARATOR ) )
						{
							$page_dir = $page_dir . DIRECTORY_SEPARATOR . \O2System::$active[ 'language' ]->parameter . DIRECTORY_SEPARATOR;
						}
					}

					$page_filename = str_replace( [ 'pages' . DIRECTORY_SEPARATOR, 'page' . DIRECTORY_SEPARATOR ], '', $page_filename );

					if ( is_file( $page_filepath = $page_dir . $page_filename . '.phtml' ) )
					{
						\O2System::$active[ 'page' ] = $page_filepath;
					}
				}

				break;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URI Segment
	 *
	 * @param   int   $n         Index
	 * @param   mixed $no_result What to return if the segment index is not found
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function segment( $n, $no_result = NULL )
	{
		return isset( $this->segments[ $n ] ) ? $this->segments[ $n ] : $no_result;
	}

	// ------------------------------------------------------------------------

	public function reindex_segments()
	{
		$this->segments = array_merge( [ NULL ], $this->segments );
		unset( $this->segments[ 0 ] );
	}

	/**
	 * Total number of segments
	 *
	 * @access  public
	 * @return  int
	 */
	public function total_segments()
	{
		return count( $this->segments );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URI "routed" Segment
	 *
	 * Returns the re-routed URI segment (assuming routing rules are used)
	 * based on the index provided. If there is no routing, will return
	 * the same result as URI::segment().
	 *
	 * @param   int   $n         Index
	 * @param   mixed $no_result What to return if the segment index is not found
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function rsegment( $n, $no_result = NULL )
	{
		return isset( $this->rsegments[ $n ] ) ? $this->rsegments[ $n ] : $no_result;
	}

	// --------------------------------------------------------------------

	public function reindex_rsegments()
	{
		$this->rsegments = array_merge( [ NULL ], $this->rsegments );
		unset( $this->rsegments[ 0 ] );
	}

	/**
	 * Total number of routed segments
	 *
	 * @access  public
	 * @return  int
	 */
	public function total_rsegments()
	{
		return count( $this->rsegments );
	}

	// ------------------------------------------------------------------------

	/**
	 * Slash segment
	 *
	 * Fetches an URI segment with a slash.
	 *
	 * @param   int    $n     Index
	 * @param   string $where Where to add the slash ('trailing' or 'leading')
	 *
	 * @access  public
	 * @return  string
	 */
	public function slash_segment( $n, $where = 'trailing' )
	{
		return $this->_slash_segment( $n, $where, 'segment' );
	}

	// --------------------------------------------------------------------

	/**
	 * Internal Slash segment
	 *
	 * Fetches an URI Segment and adds a slash to it.
	 *
	 * @param   int    $n     Index
	 * @param   string $where Where to add the slash ('trailing' or 'leading')
	 * @param   string $which Array name ('segment' or 'rsegment')
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _slash_segment( $n, $where = 'trailing', $which = 'segment' )
	{
		$leading = $trailing = '/';

		if ( $where === 'trailing' )
		{
			$leading = '';
		}
		elseif ( $where === 'leading' )
		{
			$trailing = '';
		}

		return $leading . $this->$which( $n ) . $trailing;
	}

	// --------------------------------------------------------------------

	/**
	 * Slash routed segment
	 *
	 * Fetches an URI routed segment with a slash.
	 *
	 * @param    int    $n     Index
	 * @param    string $where Where to add the slash ('trailing' or 'leading')
	 *
	 * @access  public
	 * @return  string
	 */
	public function slash_rsegment( $n, $where = 'trailing' )
	{
		return $this->_slash_segment( $n, $where, 'rsegment' );
	}
}