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

use O2System\Glob\ArrayObject;

/**
 * Input Class
 *
 * Pre-processes global input data for security
 *
 * @package        O2System
 * @subpackage     system/core
 * @category       Core Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/input.html
 */
final class Input
{
	/**
	 * Class Config
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_config = [ ];

	/**
	 * IP address of the current user
	 *
	 * @access  public
	 * @type    bool
	 */
	public $ip_address = FALSE;

	/**
	 * List of all HTTP request headers
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $headers = [ ];

	/**
	 * Input stream data
	 *
	 * Parsed from php://input at runtime
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $_input_stream = NULL;

	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		$this->_config = \O2System::$config[ 'input' ];

		// Sanitize global arrays
		$this->_sanitizeGlobals();

		\O2System::Log( 'info', 'Input Class Initialized' );
	}

	// --------------------------------------------------------------------

	/**
	 * Sanitize Globals
	 *
	 * Internal method serving for the following purposes:
	 *
	 *    - Unsets $_GET data, if query strings are not enabled
	 *    - Cleans POST, COOKIE and SERVER data
	 *    - Standardizes newline characters to PHP_EOL
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _sanitizeGlobals()
	{
		// Is $_GET data allowed? If not we'll set the $_GET to an empty array
		if ( $this->_config[ 'allowed_get' ] === FALSE )
		{
			$_GET = [ ];
		}
		// Clean $_GET Data
		elseif ( is_array( $_GET ) AND count( $_GET ) > 0 )
		{
			foreach ( $_GET as $key => $value )
			{
				$_GET[ $this->_cleanInputKeys( $key ) ] = $this->_cleanInputData( $value );
			}
		}

		// Clean $_POST Data
		if ( is_array( $_POST ) AND count( $_POST ) > 0 )
		{
			foreach ( $_POST as $key => $value )
			{
				$_POST[ $this->_cleanInputKeys( $key ) ] = $this->_cleanInputData( $value );
			}
		}

		// Clean $_COOKIE Data
		if ( is_array( $_COOKIE ) AND count( $_COOKIE ) > 0 )
		{
			// Also get rid of specially treated cookies that might be set by a server
			// or silly application, that are of no use to a CI application anyway
			// but that when present will trip our 'Disallowed Key Characters' alarm
			// http://www.ietf.org/rfc/rfc2109.txt
			// note that the key names below are single quoted strings, and are not PHP variables
			unset(
				$_COOKIE[ '$Version' ],
				$_COOKIE[ '$Path' ],
				$_COOKIE[ '$Domain' ]
			);

			foreach ( $_COOKIE as $key => $value )
			{
				if ( ( $cookie_key = $this->_cleanInputKeys( $key ) ) !== FALSE )
				{
					$_COOKIE[ $cookie_key ] = $this->_cleanInputData( $value );
				}
				else
				{
					unset( $_COOKIE[ $key ] );
				}
			}
		}

		// Sanitize PHP_SELF
		$_SERVER[ 'PHP_SELF' ] = strip_tags( $_SERVER[ 'PHP_SELF' ] );

		// CSRF Protection check
		if ( \O2System::$config[ 'csrf' ][ 'protection' ] === TRUE AND ! is_cli() )
		{
			\O2System::Security()->verifyCSRF();
		}

		\O2System::Log( 'debug', 'Input: Global POST, GET and COOKIE data sanitized' );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch from array
	 *
	 * Internal method used to retrieve values from global arrays.
	 *
	 * @param    array &$array    $_GET, $_POST, $_COOKIE, $_SERVER, etc.
	 * @param    mixed $index     Index for item to be fetched from $array
	 * @param    bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function _fetchFromArray( &$array, $index = NULL, $xss_clean = NULL )
	{
		is_bool( $xss_clean ) || $xss_clean = $this->_config[ 'xss_filtering' ];

		// If $index is NULL, it means that the whole $array is requested
		isset( $index ) || $index = array_keys( $array );

		// allow fetching multiple keys at once
		if ( is_array( $index ) )
		{
			$output = [ ];
			foreach ( $index as $key )
			{
				$output[ $key ] = $this->_fetchFromArray( $array, $key, $xss_clean );
			}

			return $output;
		}

		if ( isset( $array[ $index ] ) )
		{
			$value = $array[ $index ];
		}
		elseif ( ( $count = preg_match_all( '/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches ) ) > 1 ) // Does the index contain array notation
		{
			$value = $array;
			for ( $i = 0; $i < $count; $i++ )
			{
				$key = trim( $matches[ 0 ][ $i ], '[]' );
				if ( $key === '' ) // Empty notation will return the value as array
				{
					break;
				}

				if ( isset( $value[ $key ] ) )
				{
					$value = $value[ $key ];
				}
				else
				{
					return NULL;
				}
			}
		}
		else
		{
			return NULL;
		}

		return ( $xss_clean === TRUE )
			? \O2System::Security()->cleanXSS( $value )
			: $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from POST data with fallback to GET
	 *
	 * @param   string $index     Index for item to be fetched from $_POST or $_GET
	 * @param   bool   $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function postGet( $index, $xss_clean = NULL )
	{
		return isset( $_POST[ $index ] )
			? $this->post( $index, $xss_clean )
			: $this->get( $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the POST array
	 *
	 * @param    mixed $index     Index for item to be fetched from $_POST
	 * @param    bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function post( $index = NULL, $xss_clean = NULL )
	{
		if ( is_null( $index ) )
		{
			if ( ! empty( $_POST ) )
			{
				return new ArrayObject( $this->_fetchFromArray( $_POST, $index, $xss_clean ) );
			}
		}

		return $this->_fetchFromArray( $_POST, $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the GET array
	 *
	 * @param   mixed $index     Index for item to be fetched from $_GET
	 * @param   bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function get( $index = NULL, $xss_clean = NULL )
	{
		if ( is_null( $index ) )
		{
			if ( ! empty( $_GET ) )
			{
				return new ArrayObject( $this->_fetchFromArray( $_GET, $index, $xss_clean ) );
			}
		}

		return $this->_fetchFromArray( $_GET, $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from GET data with fallback to POST
	 *
	 * @param   string $index     Index for item to be fetched from $_GET or $_POST
	 * @param   bool   $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function getPost( $index, $xss_clean = NULL )
	{
		return isset( $_GET[ $index ] )
			? $this->get( $index, $xss_clean )
			: $this->post( $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the GET array
	 *
	 * @param   mixed $index     Index for item to be fetched from $_GET
	 * @param   bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function files( $index = NULL )
	{
		if ( is_null( $index ) )
		{
			if ( ! empty( $_FILES ) )
			{
				return new ArrayObject( $this->_fetchFromArray( $_FILES, $index ) );
			}
		}

		return $this->_fetchFromArray( $_FILES, $index );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the php://input stream
	 *
	 * Useful when you need to access PUT, DELETE or PATCH request data.
	 *
	 * @param    string $index     Index for item to be fetched
	 * @param    bool   $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function stream( $index = NULL, $xss_clean = NULL )
	{
		// Prior to PHP 5.6, the input stream can only be read once,
		// so we'll need to check if we have already done that first.
		if ( ! is_array( $this->_input_stream ) )
		{
			parse_str( file_get_contents( 'php://input' ), $this->_input_stream );
			is_array( $this->_input_stream ) || $this->_input_stream = [ ];
		}

		return $this->_fetchFromArray( $this->_input_stream, $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @param    mixed $index     Index for item to be fetched from $_COOKIE
	 * @param    bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function cookie( $index = NULL, $xss_clean = NULL )
	{
		if ( $cookie = \O2System::$config[ 'cookie' ] )
		{
			$index = $cookie[ 'prefix' ] . ltrim( $index, $cookie[ 'prefix' ] );
		}

		return $this->_fetchFromArray( $_COOKIE, $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Set cookie
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param    string|mixed[] $name     Cookie name or an array containing parameters
	 * @param    string         $value    Cookie value
	 * @param    int            $expire   Cookie expiration time in seconds
	 * @param    string         $domain   Cookie domain (e.g.: '.yourdomain.com')
	 * @param    string         $path     Cookie path (default: '/')
	 * @param    string         $prefix   Cookie name prefix
	 * @param    bool           $secure   Whether to only transfer cookies via SSL
	 * @param    bool           $httponly Whether to only makes the cookie accessible via HTTP (no javascript)
	 *
	 * @access  public
	 * @return  void
	 */
	public function setCookie( $name, $value = '', $expire = 0, $domain = '', $path = '/', $prefix = '', $secure = FALSE, $httponly = FALSE )
	{
		$cookie = is_array( $name ) ? $name : func_get_args();

		foreach ( [ 'name', 'value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly' ] as $key => $item )
		{
			if ( isset( $cookie[ $key ] ) )
			{
				if ( empty( $cookie[ $key ] ) AND isset( \O2System::$config[ 'cookie' ][ $item ] ) )
				{
					$cookie[ $item ] = \O2System::$config[ 'cookie' ][ $item ];
				}
				else
				{
					$cookie[ $item ] = $cookie[ $key ];
				}
			}
			elseif ( isset( $cookie[ $item ] ) AND isset( \O2System::$config[ 'cookie' ][ $item ] ) )
			{
				$cookie[ $item ] = \O2System::$config[ 'cookie' ][ $item ];
			}

			unset( $cookie[ $key ] );
		}

		$cookie[ 'name' ]  = $cookie[ 'prefix' ] . ltrim( $cookie[ 'name' ], $cookie[ 'prefix' ] );
		$cookie[ 'value' ] = empty( $cookie[ 'value' ] ) ? NULL : $cookie[ 'value' ];

		if ( $cookie[ 'expire' ] > 0 )
		{
			$cookie[ 'expire' ] = empty( $cookie[ 'expire' ] ) ? time() + \O2System::$config[ 'cookie' ][ 'lifetime' ] : (int) $cookie[ 'expire' ];
		}

		$cookie[ 'domain' ] = '.' . ( empty( $cookie[ 'domain' ] ) ? parse_domain()->domain : ltrim( $cookie[ 'domain' ], '.' ) );

		setcookie(
			$cookie[ 'name' ],
			$cookie[ 'value' ],
			$cookie[ 'expire' ],
			$cookie[ 'path' ],
			$cookie[ 'domain' ],
			$cookie[ 'secure' ],
			$cookie[ 'httponly' ]
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch the IP Address
	 *
	 * Determines and validates the visitor's IP address.
	 *
	 * @access  public
	 * @return  string  IP Address
	 */
	public function ipAddress()
	{
		if ( $this->ip_address !== FALSE )
		{
			return $this->ip_address;
		}

		$proxy_ips = \O2System::$config[ 'proxy_ips' ];

		if ( ! empty( $proxy_ips ) && ! is_array( $proxy_ips ) )
		{
			$proxy_ips = array_map( 'trim', explode( ',', $proxy_ips ) );
		}

		$this->ip_address = $this->server( 'REMOTE_ADDR' );

		if ( $proxy_ips )
		{
			foreach ( [
				          'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_FORWARDED', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR',
			          ] as $header )
			{
				$spoof = $this->server( $header );
				$spoof = empty( $spoof ) ? getenv( $header ) : $spoof;

				if ( $spoof !== NULL )
				{
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf( $spoof, '%[^,]', $spoof );

					if ( ! $this->isValidIp( $spoof ) )
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
						if ( $proxy_ips[ $i ] === $this->ip_address )
						{
							$this->ip_address = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
					isset( $separator ) || $separator = $this->isValidIp( $this->ip_address, 'ipv6' ) ? ':' : '.';

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
									str_repeat( ':', 9 - substr_count( $this->ip_address, ':' ) ),
									$this->ip_address
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
							$ip      = explode( '.', $this->ip_address );
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
						$this->ip_address = $spoof;
						break;
					}
				}
			}
		}

		if ( ! $this->isValidIp( $this->ip_address ) )
		{
			return $this->ip_address = '0.0.0.0';
		}

		return $this->ip_address;
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch an item from the GLOBALS array
	 *
	 * @param    mixed $index     Index for item to be fetched from $_GLOBALS
	 * @param    bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function globals( $index = NULL, $xss_clean = NULL )
	{
		return $this->_fetchFromArray( $_GLOBALS, $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @param    mixed $index     Index for item to be fetched from $_SERVER
	 * @param    bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function server( $index = NULL, $xss_clean = NULL )
	{
		return $this->_fetchFromArray( $_SERVER, $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the REQUEST array
	 *
	 * @param    mixed $index     Index for item to be fetched from $_REQUEST
	 * @param    bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function request( $index, $xss_clean = NULL )
	{
		return $this->_fetchFromArray( $_REQUEST, $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the ENV array
	 *
	 * @param    mixed $index     Index for item to be fetched from $_ENV
	 * @param    bool  $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function env( $index, $xss_clean = NULL )
	{
		return $this->_fetchFromArray( $_ENV, $index, $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Validate IP Address
	 *
	 * @param    string $ip    IP address
	 * @param    string $which IP protocol: 'ipv4' or 'ipv6'
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isValidIp( $ip, $which = '' )
	{
		switch ( strtolower( $which ) )
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = NULL;
				break;
		}

		return (bool) filter_var( $ip, FILTER_VALIDATE_IP, $which );
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch User Agent string
	 *
	 * @access  public
	 * @return  string|null    User Agent string or NULL if it doesn't exist
	 */
	public function userAgent( $xss_clean = NULL )
	{
		return $this->_fetchFromArray( $_SERVER, 'HTTP_USER_AGENT', $xss_clean );
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 *
	 * @param    string $index     Header name
	 * @param    bool   $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  string|null    The requested header on success or NULL on failure
	 */
	public function header( $index, $xss_clean = FALSE )
	{
		if ( empty( $this->headers ) )
		{
			$this->headers( $xss_clean );
		}

		if ( ! isset( $this->headers[ $index ] ) )
		{
			return NULL;
		}

		return ( $xss_clean === TRUE )
			? \O2System::Security()->xssClean( $this->headers[ $index ] )
			: $this->headers[ $index ];
	}

	// --------------------------------------------------------------------

	/**
	 * Request Headers
	 *
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 *
	 * @access  public
	 * @return  array
	 */
	public function headers( $xss_clean = FALSE )
	{
		// If header is already defined, return it immediately
		if ( ! empty( $this->headers ) )
		{
			return $this->headers;
		}

		// In Apache, you can simply call apache_request_headers()
		if ( function_exists( 'apache_request_headers' ) )
		{
			return $this->headers = apache_request_headers();
		}

		$this->headers[ 'Content-Type' ] = isset( $_SERVER[ 'CONTENT_TYPE' ] ) ? $_SERVER[ 'CONTENT_TYPE' ] : @getenv( 'CONTENT_TYPE' );

		foreach ( $_SERVER as $key => $val )
		{
			if ( sscanf( $key, 'HTTP_%s', $header ) === 1 )
			{
				// take SOME_HEADER and turn it into Some-Header
				$header = str_replace( '_', ' ', strtolower( $header ) );
				$header = str_replace( ' ', '-', ucwords( $header ) );

				$this->headers[ $header ] = $this->_fetchFromArray( $_SERVER, $key, $xss_clean );
			}
		}

		return $this->headers;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Method
	 *
	 * Return the request method
	 *
	 * @param    bool $upper Whether to return in upper or lower case
	 *                       (default: FALSE)
	 *
	 * @return    string
	 */
	public function method( $upper = FALSE )
	{
		return ( $upper )
			? strtoupper( $this->server( 'REQUEST_METHOD' ) )
			: strtolower( $this->server( 'REQUEST_METHOD' ) );
	}

	// --------------------------------------------------------------------

	/**
	 * Clean Keys
	 *
	 * Internal method that helps to prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @param    string $string Input string
	 * @param    string $fatal  Whether to terminate script exection
	 *                          or to return FALSE if an invalid
	 *                          key is encountered
	 *
	 * @access  public
	 * @return  string|bool
	 */
	protected function _cleanInputKeys( $string, $fatal = TRUE )
	{
		if ( ! preg_match( '/^[a-z0-9:_\/|-]+$/i', $string ) )
		{
			if ( $fatal === TRUE )
			{
				return FALSE;
			}
			else
			{
				\O2System::Exception()->setStatusHeader( 503 );
				echo 'Disallowed Key Characters.';
				exit( 7 ); // EXIT_USER_INPUT
			}
		}

		// Clean UTF-8 if supported
		if ( UTF8_ENABLED === TRUE )
		{
			return \O2System::UTF8()->cleanString( $string );
		}

		return $string;
	}

	// --------------------------------------------------------------------

	/**
	 * Clean Input Data
	 *
	 * Internal method that aids in escaping data and
	 * standardizing newline characters to PHP_EOL.
	 *
	 * @param    string|string[] $string Input string(s)
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _cleanInputData( $string )
	{
		if ( is_array( $string ) )
		{
			$new_array = [ ];
			foreach ( array_keys( $string ) as $key )
			{
				$new_array[ $this->_cleanInputKeys( $key ) ] = $this->_cleanInputData( $string[ $key ] );
			}

			return $new_array;
		}

		// Clean UTF-8 if supported
		if ( UTF8_ENABLED === TRUE )
		{
			$string = \O2System::UTF8()->cleanString( $string );
		}

		// Remove control characters
		$string = remove_invisible_characters( $string, FALSE );

		// Standardize newlines if needed
		if ( $this->_config[ 'standardize_newlines' ] === TRUE )
		{
			return preg_replace( '/(?:\r\n|[\r\n])/', PHP_EOL, $string );
		}

		return $string;
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

	// ------------------------------------------------------------------------

	/**
	 * Is Post Cancel
	 *
	 * Is a cancel post form request
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function isPostCancel()
	{
		if ( isset( $_POST[ 'cancel' ] ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Post Redirect
	 *
	 * Is a post form request has redirect request
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function isPostRedirect()
	{
		if ( isset( $_POST[ 'redirect' ] ) )
		{
			return TRUE;
		}

		return FALSE;
	}
}