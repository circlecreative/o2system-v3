<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 27-Jul-16
 * Time: 6:44 PM
 */

namespace O2System\Core;

use O2System\Core;

class Request extends Core\Library
{
	use Core\Library\Traits\Handlers;
	use Core\Library\Traits\Collections;

	/**
	 * Request constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		// Initialize Handlers Classes Lazy Init
		$this->__locateHandlers();

		// Initialize Collections Classes Lazy Init
		$this->__locateCollections();

		// Define Active Namespaces
		$this->namespaces = new Request\Environments\Namespaces( [ 'O2System\\', 'Applications\\' ] );
		$this->namespaces->setCurrent( 1 );

		// Define Active Directories
		$this->directories = new Request\Environments\Directories( [ SYSTEMPATH, APPSPATH ] );
		$this->directories->setCurrent( 1 );

		$this->modules = new Request\Environments\Modules();

		// Define Active Language
		if ( \O2System::$registry->offsetExists( 'languages' ) )
		{
			if ( isset( \O2System::$registry->languages[ \O2System::$config[ 'language' ] ] ) )
			{
				$this->language = \O2System::$registry->languages[ \O2System::$config[ 'language' ] ];
			}
		}

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	/**
	 * IP Address
	 *
	 * Get user's ip-address
	 *
	 * @access  public
	 * @return string
	 */
	public function getIpAddress()
	{
		$proxy_ips = \O2System::$config[ 'proxy_ips' ];

		if ( ! empty( $proxy_ips ) && ! is_array( $proxy_ips ) )
		{
			$proxy_ips = array_map( 'trim', explode( ',', $proxy_ips ) );
		}

		$this->ipAddress = \O2System::$registry->server[ 'REMOTE_ADDR' ];

		if ( $proxy_ips )
		{
			foreach ( [
				          'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_FORWARDED', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR',
			          ] as $offset )
			{
				$spoof = \O2System::$registry->server[ $offset ];
				$spoof = empty( $spoof ) ? getenv( $offset ) : $spoof;

				if ( $spoof !== NULL )
				{
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf( $spoof, '%[^,]', $spoof );

					if ( ! Core\Input\Validation::isValidIp( $spoof ) )
					{
						$spoof = NULL;
					}
					else
					{
						break;
					}
				}
			}

			if ( $spoof )
			{
				for ( $i = 0, $c = count( $proxy_ips ); $i < $c; $i++ )
				{
					// Check if we have an IP address or a subnet
					if ( strpos( $proxy_ips[ $i ], '/' ) === FALSE )
					{
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ( $proxy_ips[ $i ] === $this->ipAddress )
						{
							$this->ipAddress = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
					isset( $separator ) || $separator = Core\Input\Validation::isValidIp( $this->ipAddress, 'ipv6' ) ? ':' : '.';

					// If the proxy entry doesn't match the IP protocol - skip it
					if ( strpos( $proxy_ips[ $i ], $separator ) === FALSE )
					{
						continue;
					}

					// Convert the REMOTE_ADDR IP address to binary, if needed
					if ( ! isset( $ip, $sprintf ) )
					{
						if ( $separator === ':' )
						{
							// Make sure we're have the "full" IPv6 format
							$ip = explode(
								':',
								str_replace(
									'::',
									str_repeat( ':', 9 - substr_count( $this->ipAddress, ':' ) ),
									$this->ipAddress
								)
							);

							for ( $i = 0; $i < 8; $i++ )
							{
								$ip[ $i ] = intval( $ip[ $i ], 16 );
							}

							$sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
						}
						else
						{
							$ip      = explode( '.', $this->ipAddress );
							$sprintf = '%08b%08b%08b%08b';
						}

						$ip = vsprintf( $sprintf, $ip );
					}

					// Split the netmask length off the network address
					sscanf( $proxy_ips[ $i ], '%[^/]/%d', $netaddr, $masklen );

					// Again, an IPv6 address is most likely in a compressed form
					if ( $separator === ':' )
					{
						$netaddr = explode( ':', str_replace( '::', str_repeat( ':', 9 - substr_count( $netaddr, ':' ) ), $netaddr ) );
						for ( $i = 0; $i < 8; $i++ )
						{
							$netaddr[ $i ] = intval( $netaddr[ $i ], 16 );
						}
					}
					else
					{
						$netaddr = explode( '.', $netaddr );
					}

					// Convert to binary and finally compare
					if ( strncmp( $ip, vsprintf( $sprintf, $netaddr ), $masklen ) === 0 )
					{
						$this->ipAddress = $spoof;
						break;
					}
				}
			}
		}

		if ( ! Core\Input\Validation::isValidIp( $this->ipAddress ) )
		{
			return $this->ipAddress = '0.0.0.0';
		}

		return $this->ipAddress;
	}

	// ------------------------------------------------------------------------

	/**
	 * Determines if this request was made from the command line (CLI).
	 *
	 * @return bool
	 */
	public function isCli()
	{
		return ( PHP_SAPI === 'cli' || defined( 'STDIN' ) );
	}

	// ------------------------------------------------------------------------

	/**
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
	 *
	 * @return bool
	 */
	public function isAJAX()
	{
		return ( ! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) &&
			strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) === 'xmlhttprequest' );
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to detect if the current connection is secure through
	 * a few different methods.
	 *
	 * @return bool
	 */
	public function isSecure()
	{
		if ( ! empty( $_SERVER[ 'HTTPS' ] ) && strtolower( $_SERVER[ 'HTTPS' ] ) !== 'off' )
		{
			return TRUE;
		}
		elseif ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] === 'https' )
		{
			return TRUE;
		}
		elseif ( ! empty( $_SERVER[ 'HTTP_FRONT_END_HTTPS' ] ) && strtolower( $_SERVER[ 'HTTP_FRONT_END_HTTPS' ] ) !== 'off' )
		{
			return TRUE;
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_REQUEST object. This is the simplest way
	 * to grab data from the request object and can be used in lieu of the
	 * other get* methods in most cases.
	 *
	 * @param null $index
	 * @param null $filter
	 *
	 * @return mixed
	 */
	public function getVar( $index = NULL, $filter = NULL )
	{
		return $this->fetchGlobal( INPUT_REQUEST, $index, $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetches one or more items from a global, like cookies, get, post, etc.
	 * Can optionally filter the input when you retrieve it by passing in
	 * a filter.
	 *
	 * If $type is an array, it must conform to the input allowed by the
	 * filter_input_array method.
	 *
	 * http://php.net/manual/en/filter.filters.sanitize.php
	 *
	 * @param      $type
	 * @param null $index
	 * @param null $filter
	 *
	 * @return mixed
	 */
	protected function fetchGlobal( $type, $index = NULL, $filter = NULL )
	{
		// Null filters cause null values to return.
		if ( is_null( $filter ) )
		{
			$filter = FILTER_DEFAULT;
		}

		// If $index is null, it means that the whole input type array is requested
		if ( is_null( $index ) )
		{
			$loopThrough = [ ];
			switch ( $type )
			{
				case INPUT_GET    :
					$loopThrough = $_GET;
					break;
				case INPUT_POST   :
					$loopThrough = $_POST;
					break;
				case INPUT_COOKIE :
					$loopThrough = $_COOKIE;
					break;
				case INPUT_SERVER :
					$loopThrough = $_SERVER;
					break;
				case INPUT_ENV    :
					$loopThrough = $_ENV;
					break;
			}

			$values = [ ];
			foreach ( $loopThrough as $key => $value )
			{
				if ( is_array( $value ) AND is_array( $filter ) )
				{
					$values[ $key ] = filter_var_array( $value, $filter );
				}
				elseif ( is_array( $value ) )
				{
					foreach ( $value as $val )
					{
						$values[ $key ][] = filter_var( $val, $filter );
					}
				}
				else
				{
					$values[ $key ] = filter_var( $value, $filter );
				}
			}

			if ( empty( $values ) )
			{
				return FALSE;
			}

			return new Request\Metadata\Variables( $values );
		}

		// allow fetching multiple keys at once
		if ( is_array( $index ) )
		{
			$output = [ ];

			foreach ( $index as $key )
			{
				$output[ $key ] = $this->fetchGlobal( $type, $key, $filter );
			}

			return $output;
		}

		// Due to issues with FastCGI and testing,
		// we need to do these all manually instead
		// of the simpler filter_input();
		switch ( $type )
		{
			case INPUT_GET:
				$value = isset( $_GET[ $index ] ) ? $_GET[ $index ] : NULL;
				break;
			case INPUT_POST:
				$value = isset( $_POST[ $index ] ) ? $_POST[ $index ] : NULL;
				break;
			case INPUT_SERVER:
				$value = isset( $_SERVER[ $index ] ) ? $_SERVER[ $index ] : NULL;
				break;
			case INPUT_ENV:
				$value = isset( $_ENV[ $index ] ) ? $_ENV[ $index ] : NULL;
				break;
			case INPUT_COOKIE:
				$value = isset( $_COOKIE[ $index ] ) ? $_COOKIE[ $index ] : NULL;
				break;
			case INPUT_REQUEST:
				$value = isset( $_REQUEST[ $index ] ) ? $_REQUEST[ $index ] : NULL;
				break;
			case INPUT_SESSION:
				$value = isset( $_SESSION[ $index ] ) ? $_SESSION[ $index ] : NULL;
				break;
			default:
				$value = '';
		}

		if ( is_array( $value ) )
		{
			if ( is_string( key( $value ) ) )
			{
				return new Request\Metadata\Variables( $value );
			}
			else
			{
				return $value;
			}
		}
		elseif ( is_object( $value ) )
		{
			return $value;
		}

		return filter_var( $value, $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method that grabs the raw input stream and decodes
	 * the JSON into an array.
	 *
	 * If $assoc == true, then all objects in the response will be converted
	 * to associative arrays.
	 *
	 * @param bool $assoc   Whether to return objects as associative arrays
	 * @param int  $depth   How many levels deep to decode
	 * @param int  $options Bitmask of options
	 *
	 * @see http://php.net/manual/en/function.json-decode.php
	 *
	 * @return mixed
	 */
	public function getJSON( $assoc = FALSE, $depth = 512, $options = 0 )
	{
		return json_decode( $this->body, $assoc, $depth, $options );
	}

	//--------------------------------------------------------------------

	/**
	 * Get the request method.
	 *
	 * @param bool|false $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 */
	public function getMethod( $upper = FALSE )
	{
		return ( $upper )
			? strtoupper( $this->getServer( 'REQUEST_METHOD' ) )
			: strtolower( $this->getServer( 'REQUEST_METHOD' ) );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_SERVER array.
	 *
	 * @param null $index  Index for item to be fetched from $_SERVER
	 * @param null $filter A filter name to be applied
	 *
	 * @return mixed
	 */
	public function getServer( $index = NULL, $filter = NULL )
	{
		return $this->fetchGlobal( INPUT_SERVER, $index, $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * Get Request Time
	 *
	 * @param string|null $format date(format, REQUEST_TIME)
	 *
	 * @return mixed
	 */
	public function getTime( $format = NULL )
	{
		return isset( $format )
			? date( $format, $this->getServer( 'REQUEST_TIME' ) )
			: $this->getServer( 'REQUEST_TIME' );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_SERVER array.
	 *
	 * @param null $index  Index for item to be fetched from $_SERVER
	 * @param null $filter A filter name to be applied
	 *
	 * @return mixed
	 */
	public function getHeader( $index = NULL, $filter = NULL )
	{
		return $this->headers->offsetFilter($index, $filter);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from POST data with fallback to GET.
	 *
	 * @param null $index  Index for item to fetch from $_POST or $_GET
	 * @param null $filter A filter name to apply
	 *
	 * @return mixed
	 */
	public function getPostGet( $index = NULL, $filter = NULL )
	{
		// Use $_POST directly here, since filter_has_var only
		// checks the initial POST data, not anything that might
		// have been added since.
		return isset( $_POST[ $index ] )
			? $this->getPost( $index, $filter )
			: $this->getGet( $index, $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from POST.
	 *
	 * @param null $index  Index for item to fetch from $_POST.
	 * @param null $filter A filter name to apply
	 *
	 * @return mixed
	 */
	public function getPost( $index = NULL, $filter = NULL )
	{
		return $this->fetchGlobal( INPUT_POST, $index, $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from GET data.
	 *
	 * @param null $index  Index for item to fetch from $_GET.
	 * @param null $filter A filter name to apply.
	 *
	 * @return mixed
	 */
	public function getGet( $index = NULL, $filter = NULL )
	{
		return $this->fetchGlobal( INPUT_GET, $index, $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from GET data with fallback to POST.
	 *
	 * @param null $index  Index for item to be fetched from $_GET or $_POST
	 * @param null $filter A filter name to apply
	 *
	 * @return mixed
	 */
	public function getGetPost( $index = NULL, $filter = NULL )
	{
		// Use $_GET directly here, since filter_has_var only
		// checks the initial GET data, not anything that might
		// have been added since.
		return isset( $_GET[ $index ] )
			? $this->getGet( $index, $filter )
			: $this->getPost( $index, $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the COOKIE array.
	 *
	 * @param null $index  Index for item to be fetched from $_COOKIE
	 * @param null $filter A filter name to be applied
	 *
	 * @return mixed
	 */
	public function getCookie( $index = NULL, $filter = NULL )
	{
		return $this->fetchGlobal( INPUT_COOKIE, $index, $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch the user agent string
	 *
	 * @param null $filter
	 *
	 * @return mixed
	 */
	public function getUserAgent( $filter = NULL )
	{
		return $this->fetchGlobal( INPUT_SERVER, 'HTTP_USER_AGENT', $filter );
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all files that have been uploaded with this
	 * request. Each file is represented by an UploadedFile instance.
	 *
	 * @return array
	 */
	public function getFiles()
	{
		if ( is_null( $this->files ) )
		{
			// @todo modify to use Services, at the very least.
			$this->files = new Files();
		}

		return $this->files->all();
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves a single file by the name of the input field used
	 * to upload it.
	 *
	 * @param string $fileID
	 *
	 * @return UploadedFile|null
	 */
	public function getFile( $fileID )
	{
		if ( is_null( $this->files ) )
		{
			// @todo modify to use Services, at the very least.
			$this->files = new Files();
		}

		return $this->files->getFile( $fileID );
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_ENV array.
	 *
	 * @param null $index  Index for item to be fetched from $_ENV
	 * @param null $filter A filter name to be applied
	 *
	 * @return mixed
	 */
	public function getEnv( $index = NULL, $filter = NULL )
	{
		return $this->fetchGlobal( INPUT_ENV, $index, $filter );
	}

	//--------------------------------------------------------------------

	public function triggerResponse( $code )
	{

	}

	// ------------------------------------------------------------------------

	/**
	 * Is Post Insert
	 *
	 * Is a insert post form request
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function isPostInsert()
	{
		if ( empty( $_POST[ 'id' ] ) AND
			(
				isset( $_POST[ 'add' ] ) OR
				isset( $_POST[ 'add_new' ] ) OR
				isset( $_POST[ 'add_as_new' ] ) OR
				isset( $_POST[ 'save' ] )
			)
		)
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Post Update
	 *
	 * Is a update post form request
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function isPostUpdate()
	{
		if ( ! empty( $_POST[ 'id' ] ) AND
			(
				isset( $_POST[ 'update' ] ) OR
				isset( $_POST[ 'save' ] )
			)
		)
		{
			return TRUE;
		}

		return FALSE;
	}

}
