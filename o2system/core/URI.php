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

use O2System\Metadata\Controller;
use O2System\Metadata\Page;

defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

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
	protected $_config = [ ];

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

		\O2System::$active[ 'URI' ] = new \O2System\Metadata\URI();

		// Parse Request
		$this->_parseRequest();

		\O2System::Log( 'info', 'URI Class Initialized' );
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
	public function setRequest( $segments )
	{
		\O2System::$active[ 'URI' ]->setSegments( $segments, 'rsegments' );
		$this->_validateSegments( \O2System::$active[ 'URI' ]->rsegments );
	}

	// ------------------------------------------------------------------------

	protected function _parseRequest()
	{
		// If it's a CLI request, ignore the configuration
		if ( is_cli() OR
			( $protocol = strtoupper( $this->_config[ 'protocol' ] ) === 'CLI' )
		)
		{
			$uri_string = $this->_parseArgv();
		}
		elseif ( $protocol = strtoupper( $this->_config[ 'protocol' ] ) )
		{
			empty( $protocol ) && $protocol = 'REQUEST_URI';

			switch ( $protocol )
			{
				case 'AUTO': // For BC purposes only
				case 'REQUEST_URI':
					$uri_string = $this->_parseRequestURI();
					break;
				case 'QUERY_STRING':
					$uri_string = $this->_parseQueryString();
					break;
				case 'PATH_INFO':
				default:
					$uri_string = isset( $_SERVER[ $protocol ] )
						? $_SERVER[ $protocol ]
						: $this->_parseRequestURI();
					break;
			}
		}

		// Filter out control characters and trim slashes
		$uri_string = trim( remove_invisible_characters( $uri_string, FALSE ), '/' );

		// If the URI contains only a slash we'll kill it
		$uri_string = \O2System::$active[ 'URI' ]->string === '/' ? NULL : $uri_string;

		// Set Active URI String
		\O2System::$active[ 'URI' ]->setString( $uri_string );

		// Split segments into array with indexing
		$segments = explode( '/', \O2System::$active[ 'URI' ]->string );

		// Populate the segments array
		foreach ( $segments as $key => $segment )
		{
			// Filter segments for security
			if ( $segment = trim( $this->_filterSegment( $segment ) ) )
			{
				if ( \O2System::$registry->offsetExists( 'languages' ) )
				{
					if ( isset( \O2System::$registry->languages[ $segment ] ) )
					{
						\O2System::$active[ 'language' ] = \O2System::$registry->languages[ $segment ];

						continue;
					}
				}

				\O2System::$active[ 'URI' ]->segments[ $key ] = $segment;
			}
		}

		// Re-Index URI Segments
		\O2System::$active[ 'URI' ]->reindexSegments();

		// Re-Define URI String
		\O2System::$active[ 'URI' ]->setString( \O2System::$active[ 'URI' ]->segments );

		// Validate Segments
		$this->_validateSegments();
	}

	/**
	 * Parse CLI arguments
	 *
	 * Take each command line argument and assume it is a URI segment.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _parseArgv()
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
	protected function _parseRequestURI()
	{
		if ( ! isset( $_SERVER[ 'REQUEST_URI' ], $_SERVER[ 'SCRIPT_NAME' ] ) )
		{
			return '';
		}

		$uri   = parse_url( $_SERVER[ 'REQUEST_URI' ] );
		$query = isset( $uri[ 'query' ] ) ? $uri[ 'query' ] : '';
		$uri   = isset( $uri[ 'path' ] ) ? $uri[ 'path' ] : '';

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
			$query                     = explode( '?', $query, 2 );
			$uri                       = $query[ 0 ];
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
		return $this->_removeRelativeDirectory( $uri );
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
	protected function _parseQueryString()
	{
		$uri = isset( $_SERVER[ 'QUERY_STRING' ] ) ? $_SERVER[ 'QUERY_STRING' ] : @getenv( 'QUERY_STRING' );

		if ( trim( $uri, '/' ) === '' )
		{
			return '';
		}
		elseif ( strncmp( $uri, '/', 1 ) === 0 )
		{
			$uri                       = explode( '?', $uri, 2 );
			$_SERVER[ 'QUERY_STRING' ] = isset( $uri[ 1 ] ) ? $uri[ 1 ] : '';
			$uri                       = rawurldecode( $uri[ 0 ] );
		}

		parse_str( $_SERVER[ 'QUERY_STRING' ], $_GET );

		return $this->_removeRelativeDirectory( $uri );
	}

	// ------------------------------------------------------------------------

	/**
	 * Remove relative directory (../) and multi slashes (///)
	 *
	 * Do some final cleaning of the URI and return it, currently only used in self::_parseRequestURI()
	 *
	 * @param   string $uri URI String
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _removeRelativeDirectory( $uri )
	{
		$segments = [ ];
		$segment  = strtok( $uri, '/' );

		$base_dirs = explode( '/', str_replace( '\\', '/', ROOTPATH ) );

		while ( $segment !== FALSE )
		{
			if ( ( ! empty( $segment ) || $segment === '0' ) AND
				$segment !== '..' AND
				! in_array(
					$segment, $base_dirs
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
	protected function _filterSegment( $string )
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
		return str_replace(
			[ '$', '(', ')', '%28', '%29', $this->_config[ 'suffix' ], '.html' ],    // Bad
			[ '&#36;', '&#40;', '&#41;', '&#40;', '&#41;', '' ],    // Good
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
	protected function _validateSegments( $segments = [ ] )
	{
		$segments = empty( $segments ) ? \O2System::$active[ 'URI' ]->segments : $segments;

		\O2System::$active[ 'URI' ]->setSegments( $segments, 'rsegments' );
		\O2System::$active[ 'URI' ]->setSegments( \O2System::$active[ 'URI' ]->rsegments, 'isegments' );

		$num_rsegments = \O2System::$active[ 'URI' ]->totalSegments( 'rsegments' );

		if ( $num_rsegments > 0 )
		{
			for ( $i = 0; $i < $num_rsegments; $i++ )
			{
				$slice_rsegments = array_slice( \O2System::$active[ 'URI' ]->rsegments, 0, ( $num_rsegments - $i ) );

				// Find module registry
				if ( $module = \O2System::$registry->find( implode( '/', $slice_rsegments ), 'modules' ) )
				{
					$parents = \O2System::$registry->getParents( $module );

					if ( ! empty( $parents ) )
					{
						foreach ( $parents as $parent )
						{
							if ( ! in_array( $parent->namespace, \O2System::$active[ 'namespaces' ]->getArrayCopy() ) )
							{
								\O2System::$active[ 'namespaces' ][]  = $parent->namespace;
								\O2System::$active[ 'directories' ][] = ROOTPATH . $parent->realpath;
								\O2System::$active[ 'modules' ][]     = $parent;
							}

						}
					}

					if ( ! in_array( $module->namespace, \O2System::$active[ 'namespaces' ]->getArrayCopy() ) )
					{
						\O2System::$active[ 'namespaces' ][] = $module->namespace;
						\O2System::$active[ 'namespaces' ]->setCurrent( \O2System::$active[ 'namespaces' ]->count() - 1 );

						\O2System::$active[ 'directories' ][] = ROOTPATH . $module->realpath;
						\O2System::$active[ 'directories' ]->setCurrent( \O2System::$active[ 'directories' ]->count() - 1 );

						\O2System::$active[ 'modules' ][] = $module;
						\O2System::$active[ 'modules' ]->setCurrent( \O2System::$active[ 'modules' ]->count() - 1 );
					}

					// Reload Module Config
					\O2System::$config->load( 'config' );
					\O2System::$active[ 'URI' ]->setSegments( array_diff( \O2System::$active[ 'URI' ]->rsegments, $slice_rsegments ), 'isegments' );
					break;
				}
			}
		}

		if ( count( \O2System::$active[ 'URI' ]->isegments ) > 0 )
		{
			$page_directories = [ ];

			foreach ( \O2System::$active[ 'directories' ] as $directory )
			{
				if ( is_dir( $page_directory = $directory . 'pages' . DIRECTORY_SEPARATOR ) )
				{
					$page_directories[] = $page_directory;

					// Is the pages has language directory?
					if ( \O2System::$active->offsetExists( 'language' ) )
					{
						if ( is_dir( $page_directory . \O2System::$active[ 'language' ]->parameter . DIRECTORY_SEPARATOR ) )
						{
							$page_directories[] = $page_directory . \O2System::$active[ 'language' ]->parameter . DIRECTORY_SEPARATOR;
						}
					}
				}
			}

			if ( $module = \O2System::$active[ 'modules' ]->current() )
			{
				if ( is_dir( $page_directory = ROOTPATH . $module->realpath . 'pages' . DIRECTORY_SEPARATOR ) )
				{
					$page_directories[] = $page_directory;

					// Is the pages has language directory?
					if ( \O2System::$active->offsetExists( 'language' ) )
					{
						if ( is_dir( $page_directory . \O2System::$active[ 'language' ]->parameter . DIRECTORY_SEPARATOR ) )
						{
							$page_directories[] = $page_directory . \O2System::$active[ 'language' ]->parameter . DIRECTORY_SEPARATOR;
						}
					}
				}
			}

			foreach ( array_reverse( $page_directories ) as $page_directory )
			{
				if ( is_file( $page_filepath = $page_directory . implode( DIRECTORY_SEPARATOR, \O2System::$active[ 'URI' ]->isegments ) . '.phtml' ) )
				{
					\O2System::$active[ 'page' ] = new Page( $page_filepath );

					// Find Modular Pages Controller
					if ( isset( $module ) AND is_file( $controller_filepath = ROOTPATH . $module->realpath . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' ) )
					{
						\O2System::$active[ 'controller' ]     = new Controller( $controller_filepath );
						\O2System::$active[ 'URI' ]->rsegments = [ $module->parameter, 'pages', 'index' ];
					}
					elseif ( is_file( $controller_filepath = APPSPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' ) )
					{
						\O2System::$active[ 'controller' ]     = new Controller( $controller_filepath );
						\O2System::$active[ 'URI' ]->rsegments = [ 'pages', 'index' ];
					}
					elseif ( is_file( $controller_filepath = SYSTEMPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Pages.php' ) )
					{
						\O2System::$active[ 'controller' ]     = new Controller( $controller_filepath );
						\O2System::$active[ 'URI' ]->rsegments = [ 'pages', 'index' ];
					}

					\O2System::$active[ 'controller' ]->params = [
						\O2System::$active[ 'page' ]->realpath,
						\O2System::$active[ 'page' ]->vars,
					];

					// We didn't have to find the module because is definitely a page
					// The router will be set the active controller into pages controller
					break;
				}
			}
		}
	}

	// --------------------------------------------------------------------
}