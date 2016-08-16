<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 27-Jul-16
 * Time: 6:52 PM
 */

namespace O2System\Core\Request\Handlers;

use O2System\Exception;
use O2System\Registry\Metadata\Page;

class URI
{
	/**
	 * URI Class Configuration
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $config = [ ];

	public $segments  = [ ];
	public $string;
	public $rsegments = [ ];
	public $rstring;
	public $isegments = [ ];
	public $istring;

	// --------------------------------------------------------------------

	public function __construct()
	{
		// Set URI Configuration
		$this->config = \O2System::$config[ 'URI' ];

		\O2System::$log->debug( 'LOG_DEBUG_REQUEST_URI_CLASS_INITIALIZED' );
	}

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
		$this->setSegments( $segments, 'rsegments' );
		$this->_validateSegments( $this->rsegments );
	}

	// ------------------------------------------------------------------------

	/**
	 * Initialize
	 *
	 * This method will try to get the requested URI string from
	 * multiple types of protocols (AUTO, REQUEST_URI, QUERY_STRING)
	 * and parse it into multiple types array's of URI segments (segments, rsegments, isegments)
	 *
	 * @throws \O2System\Exception
	 */
	public function initialize()
	{
		$uri_string = '';

		// If it's a CLI request, ignore the configuration
		if ( is_cli() OR
			( $protocol = strtoupper( $this->config[ 'protocol' ] ) === 'CLI' )
		)
		{
			$uri_string = $this->_parseArgv();
		}
		elseif ( $protocol = strtoupper( $this->config[ 'protocol' ] ) )
		{
			empty( $protocol ) && $protocol = 'REQUEST_URI';

			switch ( $protocol )
			{
				case 'AUTO':
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
		$uri_string = $this->string === '/' ? NULL : $uri_string;

		// Set Active URI String
		$this->setString( $uri_string );

		// Split segments into array with indexing
		$segments = explode( '/', $this->string );

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
						\O2System::$request->language = \O2System::$registry->languages[ $segment ];

						continue;
					}
				}

				$this->segments[ $key ] = $segment;
			}
		}

		// Re-Index URI Segments
		$this->reindexSegments();

		// Re-Define URI String
		$this->setString( $this->segments );

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
	 * @param string $string URI String
	 *
	 * @return mixed
	 * @throws \O2System\Exception
	 */
	protected function _filterSegment( $string )
	{
		if ( ! empty( $string ) AND
			! empty( $this->config[ 'permitted_chars' ] ) AND
			! preg_match( '/^[' . $this->config[ 'permitted_chars' ] . ']+$/i', $string ) AND
			! is_cli()
		)
		{
			throw new Exception\RequestURIException( 'E_URI_HAS_DISALLOWED_CHARACTERS', 105 );
		}

		// Convert programatic characters to entities and return
		return str_replace(
			[ '$', '(', ')', '%28', '%29', $this->config[ 'suffix' ], '.html' ],    // Bad
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
		$segments = empty( $segments ) ? $this->segments : $segments;

		$this->setSegments( $segments, 'rsegments' );
		$this->setSegments( $this->rsegments, 'isegments' );

		$totalRSegments = $this->getTotalSegments( 'rsegments' );

		if ( $totalRSegments > 0 )
		{
			for ( $i = 0; $i <= $totalRSegments; $i++ )
			{
				$sliceRSegments = array_slice( $this->rsegments, 0, ( $totalRSegments - $i ) );

				// Find module registry
				if ( $module = \O2System::$registry->find( implode( '/', $sliceRSegments ), 'modules' ) )
				{
					$parents = \O2System::$registry->getParents( $module );

					if ( ! empty( $parents ) )
					{
						foreach ( $parents as $parent )
						{
							if ( ! in_array( $parent->namespace, \O2System::$request->namespaces->getArrayCopy() ) )
							{
								\O2System::$request->namespaces[]  = $parent->namespace;
								\O2System::$request->directories[] = $parent->getDirectory();
								\O2System::$request->modules[]     = $parent;
							}
						}
					}

					if ( \O2System::$request->namespaces->inStorage( $module->namespace ) === FALSE )
					{
						\O2System::$request->namespaces[] = $module->namespace;
						\O2System::$request->namespaces->search( $module->namespace, TRUE );

						\O2System::$request->directories[] = $module->getDirectory();
						\O2System::$request->directories->search( $module->getDirectory(), TRUE );

						\O2System::$request->modules[] = $module;
						\O2System::$request->modules->search( $module, TRUE );
					}

					// Reload Module Config
					\O2System::$config->load( 'config' );

					$this->setSegments( array_diff( $this->rsegments, $sliceRSegments ), 'isegments' );
					break;
				}
			}
		}

		if ( count( $this->isegments ) > 0 )
		{
			$findPagesDirectories = [ ];
			$pagesDirectories     = \O2System::$load->getPagesDirectories( TRUE );

			if ( $module = \O2System::$request->modules->current() )
			{
				if ( array_key_exists( $module->namespace, $pagesDirectories ) )
				{
					$findPagesDirectories[] = $modulePagesDirectory = $pagesDirectories[ $module->namespace ];

					// Is the pages has language directory?
					if ( isset( \O2System::$request->language ) )
					{
						if ( is_dir( $modulePagesDirectory . \O2System::$request->language->parameter . DIRECTORY_SEPARATOR ) )
						{
							$findPagesDirectories[] = $modulePagesDirectory . \O2System::$request->language->parameter . DIRECTORY_SEPARATOR;
						}
					}
				}
			}
			else
			{
				foreach ( $pagesDirectories as $pageDirectory )
				{
					$findPagesDirectories[] = $pageDirectory;

					// Is the pages has language directory?
					if ( isset( \O2System::$request->language ) )
					{
						if ( is_dir( $pageDirectory . \O2System::$request->language->parameter . DIRECTORY_SEPARATOR ) )
						{
							$findPagesDirectories[] = $pageDirectory . \O2System::$request->language->parameter . DIRECTORY_SEPARATOR;
						}
					}
				}
			}

			foreach ( $findPagesDirectories as $findPagesDirectory )
			{
				if ( is_file( $pageFilepath = $findPagesDirectory . implode( DIRECTORY_SEPARATOR, $this->isegments ) . '.phtml' ) )
				{
					\O2System::$request->page = new Page( $pageFilepath );

					// Find Modular Pages Controller
					if ( isset( $module ) )
					{
						if ( FALSE !== ( $controllerFilepath = $module->hasController( 'pages', TRUE, TRUE ) ) )
						{
							if ( $controller = \O2System::$request->routing->loadController( $controllerFilepath ) )
							{
								\O2System::$request->routing->setupController( $controller );
							}
						}
					}

					if ( empty( \O2System::$request->controller ) )
					{
						$controllerDirectories = \O2System::$load->getDirectories( 'Controllers', TRUE, TRUE );

						foreach ( $controllerDirectories as $controllerDirectory )
						{
							if ( is_file( $controllerFilepath = $controllerDirectory . 'Pages.php' ) )
							{
								if ( $controller = \O2System::$request->routing->loadController( $controllerFilepath ) )
								{
									\O2System::$request->routing->setupController( $controller );
									break;
								}
							}
						}
					}

					if ( isset( \O2System::$request->controller ) )
					{
						\O2System::$request->controller->params = [
							\O2System::$request->page->realpath,
							\O2System::$request->page->vars,
						];
					}

					// We didn't have to find the module because is definitely a page
					// The router will be set the active controller into pages controller
					break;
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set String
	 *
	 * @param        $string
	 * @param string $which
	 */
	public function setString( $string, $which = 'string' )
	{
		if ( is_array( $string ) )
		{
			$this->{$which} = implode( '/', $string );
		}
		else
		{
			$this->{$which} = $string;
		}
	}

	public function setSegments( $segments, $which = 'segments' )
	{
		if ( is_string( $segments ) )
		{
			$$this->{$which} = explode( '/', $segments );
		}
		else
		{
			$this->{$which} = $segments;
		}

		$this->reindexSegments( $which );

		switch ( $which )
		{
			case 'segments':
				$this->setString( $segments, 'string' );
				break;

			case 'rsegments':
				$this->setString( $segments, 'rstring' );
				break;

			case 'isegments':
				$this->setString( $segments, 'istring' );
				break;
		}
	}

	public function pushSegment( $segment, $which = 'segments' )
	{
		if ( is_string( $segment ) )
		{
			array_push( $this->{$which}, $segment );

			switch ( $which )
			{
				case 'segments':
					$this->setString( $this->{$which}, 'string' );
					break;

				case 'rsegments':
					$this->setString( $this->{$which}, 'rstring' );
					break;

				case 'isegments':
					$this->setString( $this->{$which}, 'istring' );
					break;
			}
		}
	}

	/**
	 * Fetch URI Segment
	 *
	 * @param   int   $n         Index
	 * @param   mixed $no_result What to return if the segment index is not found
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function getSegment( $n, $no_result = NULL, $which = 'segments' )
	{
		$no_result = in_array( $no_result, [ 'segments', 'rsegments', [ 'isegments' ] ] ) ? NULL : $no_result;

		return isset( $this->{$which}[ $n ] ) ? $this->{$which}[ $n ] : $no_result;
	}

	// ------------------------------------------------------------------------

	public function hasSegment( $segment, $which = 'segments' )
	{
		return (bool) in_array( $segment, $this->{$which} );
	}

	public function reindexSegments( $which = 'segments' )
	{
		$segments = array_values( $this->{$which} );

		if ( is_array( $segments ) )
		{
			if ( key( $segments ) == 0 )
			{
				array_unshift( $segments, NULL );
				unset( $segments[ 0 ] );
			}

			$this->{$which} = $segments;
		}
	}

	/**
	 * Total number of segments
	 *
	 * @access  public
	 * @return  int
	 */
	public function getTotalSegments( $which = 'segments' )
	{
		return (int) count( $this->{$which} );
	}

	// --------------------------------------------------------------------

	/**
	 * Slash segment
	 *
	 * Fetches an URI segment with a slash.
	 *
	 * @param   int    $key   Index
	 * @param   string $where Where to add the slash ('trailing' or 'leading')
	 *
	 * @access  public
	 * @return  string
	 */
	public function getSlashSegment( $key, $where = 'trailing', $which = 'segments' )
	{
		if ( in_array( $where, [ 'segments', 'rsegments', 'isegments' ] ) )
		{
			$which = $where;
			$where = 'trailing';
		}

		$leading = $trailing = '/';

		if ( $where === 'trailing' )
		{
			$leading = '';
		}
		elseif ( $where === 'leading' )
		{
			$trailing = '';
		}

		return $leading . $this->getSegment( $key, $which ) . $trailing;
	}

	public function hasExtensionSegment( $extension = NULL )
	{
		$segment_extension = pathinfo( $this->string, PATHINFO_EXTENSION );

		if ( ! empty( $segment_extension ) )
		{
			return TRUE;
		}
		elseif ( isset( $extension ) )
		{
			if ( $extension === $segment_extension )
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	public function findSegmentKey( $segment, $which = 'segments' )
	{
		$segments = $this->{$which};

		if ( $key = array_search( $segment, $segments ) )
		{
			return $key;
		}

		return FALSE;
	}
}