<?php
/**
 * O2CURL
 *
 * Lightweight HTTP request Libraries for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        o2curl
 * @author         O2System Developer Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2curl/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2curl.html
 */
// ------------------------------------------------------------------------

namespace O2System
{

	use O2System\CURL\Exception;
	use O2System\CURL\Factory\Requests;
	use O2System\CURL\Factory\Response as Response;
	use O2System\CURL\Metadata\Error;
	use O2System\CURL\Metadata\Info;

	/**
	 * CURL Library
	 *
	 * @package          o2curl
	 * @subpackage
	 * @category         bootstrap
	 * @version          1.0
	 * @author           O2System Developer Team
	 * @copyright        Copyright (c) 2005 - 2014
	 * @license          http://circle-creative.com/products/o2curl/license.html
	 * @link             http://circle-creative.com
	 */
	class CURL
	{
		use Core\Interfaces\SingletonInterface;

		protected $_http_version = CURL_HTTP_VERSION_1_1;

		/**
		 * CURL Timeout
		 *
		 * @access  protected
		 * @type    int
		 */
		protected $_timeout = 5;

		/**
		 * CURL Verify Peer
		 *
		 * @access  protected
		 * @type    bool
		 */
		protected $_verifypeer = FALSE;

		/**
		 * CURL Verify Host
		 *
		 * @access  protected
		 * @type    int
		 */
		protected $_verifyhost = 0;

		/**
		 * CURL Certificate Info
		 *
		 * @access  protected
		 * @type    string
		 */
		protected $_cainfo = NULL;

		/**
		 * CURL Response Encoding Type
		 *
		 * @access  protected
		 * @type    string
		 */
		protected $_encoding = 'gzip';

		/**
		 * CURL Response Encoding Type
		 *
		 * @access  protected
		 * @type    string
		 */
		protected $_max_redirects = 10;

		/**
		 * CURL Auth
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_auth = [
			'user'   => NULL,
			'pass'   => NULL,
			'method' => CURLAUTH_BASIC,
		];

		/**
		 * CURL Proxy
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_proxy = [
			'port'    => FALSE,
			'tunnel'  => FALSE,
			'address' => FALSE,
			'type'    => CURLPROXY_HTTP,
			'auth'    => [
				'user'   => NULL,
				'pass'   => NULL,
				'method' => CURLAUTH_BASIC,
			],
		];

		protected $_cookie = NULL;

		/**
		 * CURL User Agent
		 *
		 * @access  protected
		 * @type    string
		 */
		protected $_useragent = 'O2CURL/1.0';

		/**
		 * CURL Headers
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_headers = [ ];

		/**
		 * CURL Options
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_options = [ ];

		/**
		 * CURL Handle
		 *
		 * @access  protected
		 * @type    Resource
		 */
		protected $_handle = NULL;

		/**
		 * CURL Response
		 *
		 * @access  protected
		 * @type    Response
		 */
		protected $_response;

		// ------------------------------------------------------------------------

		public function __reconstruct()
		{
			if ( ! function_exists( 'curl_init' ) )
			{
				throw new Exception( 'CURL_MODULENOTFOUND' );
			}
		}

		/**
		 * Set Timeout
		 *
		 * @param $timeout
		 */
		public function setTimeout( $timeout )
		{
			$this->_timeout = (int) $timeout;

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set Max Redirects
		 *
		 * @param $max_redirects
		 */
		public function setMaxRedirects( $max_redirects )
		{
			$this->_max_redirects = (int) $max_redirects;

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set Verify CURL SSL
		 *
		 * @param bool $peer
		 * @param int  $host
		 * @param null $cainfo
		 *
		 * @return $this
		 */
		public function setVerify( $peer = TRUE, $host = 2, $cainfo = NULL )
		{
			$this->_verifypeer = $peer;
			$this->_verifyhost = is_int( $host ) ? $host : 2;

			if ( isset( $cainfo ) )
			{
				$this->_cainfo = $cainfo;
			}

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set CURL Response Encoding
		 *
		 * @param string $encoding
		 *
		 * @return \O2System\CURL
		 */
		public function setEncoding( $encoding )
		{
			$this->_encoding = $encoding;

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set authentication method to use
		 *
		 * @param string     $username Authentication Username
		 * @param string     $password Authentication Password
		 * @param int|string $method   Authentication Method
		 *
		 * @return \O2System\CURL
		 */
		public function setAuth( $username = '', $password = '', $method = CURLAUTH_BASIC )
		{
			$this->_auth[ 'user' ]   = $username;
			$this->_auth[ 'pass' ]   = $password;
			$this->_auth[ 'method' ] = $method;

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set proxy to use
		 *
		 * @param string      $address Proxy Address
		 * @param int|string  $port    Proxy Port
		 * @param int|string  $type    Proxy Type (Available options for this are CURLPROXY_HTTP, CURLPROXY_HTTP_1_0
		 *                             CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A and
		 *                             CURLPROXY_SOCKS5_HOSTNAME)
		 * @param bool|string $tunnel  Enable/Disable Tunneling
		 *
		 * @return Curl
		 */
		public function setProxy( $address, $port = 1080, $type = CURLPROXY_HTTP, $tunnel = FALSE )
		{
			$this->_proxy[ 'type' ]    = $type;
			$this->_proxy[ 'port' ]    = $port;
			$this->_proxy[ 'tunnel' ]  = $tunnel;
			$this->_proxy[ 'address' ] = $address;

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set default headers to send on every request
		 *
		 * @param array $headers headers array
		 *
		 * @return  \O2System\CURL
		 */
		public function setHeaders( array $headers = [ ] )
		{
			$this->_headers = array_merge( $this->_headers, $headers );

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set a new default header to send on every request
		 *
		 * @param string $key   header name
		 * @param string $value header value
		 *
		 * @return \O2System\CURL
		 */
		public function setHeader( $key, $value )
		{
			$this->_headers[ $key ] = $value;

			return $this;
		}

		// ------------------------------------------------------------------------

		public function setUseragent( $useragent )
		{
			$this->_useragent = $useragent;

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set COOKIE request to a URL
		 *
		 * @param string $url     URL to send the TRACE request to
		 * @param string $path
		 * @param array  $params
		 * @param array  $headers additional headers to send
		 *
		 * @return Response
		 * @throws \Exception
		 */
		public function setCookie( array $cookie )
		{
			foreach ( $cookie as $key => $value )
			{
				$cookies[] = $key . '=' . $value;
			}

			$this->_cookie = implode( '; ', $cookies );

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Reset Headers
		 *
		 * @access  public
		 * @return \O2System\CURL
		 */
		public function resetHeaders()
		{
			$this->_headers = [ ];

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Set CURL Options
		 *
		 * @param   array $options
		 *
		 * @access  public
		 * @return \O2System\CURL
		 */
		public function setOptions( array $options )
		{
			$this->_options = $options;

			return $this;
		}

		/**
		 * Set CURL Option
		 *
		 * @param   int   $index
		 * @param   mixed $value
		 *
		 * @access  public
		 * @return \O2System\CURL
		 */
		public function setOption( $index, $value )
		{
			$this->_options[ $index ] = $value;

			return $this;
		}

		/**
		 * Set File
		 *
		 * @param   string $filename
		 * @param   string $mimetype
		 * @param   string $postname
		 *
		 * @return \CURLFile|string
		 */
		public function setFile( $filename, $mimetype = '', $postname = '' )
		{
			if ( function_exists( 'curl_file_create' ) )
			{
				return curl_file_create( $filename, $mimetype = '', $postname = '' );
			}
			else
			{
				return sprintf( '@%s;filename=%s;type=%s', $filename, $postname ? : basename( $filename ), $mimetype );
			}
		}

		// ------------------------------------------------------------------------

		public function __call( $method, $args = [ ] )
		{
			if ( strpos( $method, 'set' ) !== FALSE )
			{
				return call_user_func_array( [ $this, $method ], $args );
			}
			elseif ( in_array( strtoupper( $method ), [ 'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD', 'TRACE', 'CONNECT' ] ) )
			{
				@list( $url, $params, $init_only ) = $this->__parseArgs( $args );

				$params    = empty( $params ) ? [ ] : $params;
				$init_only = empty( $init_only ) ? FALSE : (bool) $init_only;

				return $this->request( $url, $params, strtoupper( $method ), $init_only );
			}
		}

		private function __parseArgs( array $args )
		{
			@list( $url, $path, $params, $headers, $init_only ) = $args;

			if ( empty( $url ) )
			{
				throw new Exception( 'CURL_URLNOTSET' );
			}
			elseif ( is_string( $path ) )
			{
				$url = $this->__parseUrl( $url, $path );
			}
			elseif ( is_array( $path ) )
			{
				if ( is_numeric( key( $path ) ) )
				{
					$url  = $this->__parseUrl( $url, implode( '/', $path ) );
					$path = NULL;
				}
				else
				{
					$url    = $this->__parseUrl( $url );
					$params = $path;
					$path   = NULL;
				}
			}

			if ( isset( $headers ) AND is_bool( $headers ) )
			{
				$init_only = $headers;
				$headers   = $params;
			}
			elseif ( isset( $params ) AND is_bool( $params ) )
			{
				$init_only = $params;
				$params    = [ ];
			}

			if ( is_array( $headers ) )
			{
				$this->setHeaders( $headers );
			}

			return [ $url, $params, $init_only ];
		}

		private function __parseUrl( $url, $path = NULL )
		{
			$parse_url = parse_url( $url );

			if ( isset( $parse_url[ 'query' ] ) )
			{
				parse_str( $parse_url[ 'query' ], $parse_url[ 'query' ] );
			}
			else
			{
				$parse_url[ 'query' ] = [ ];
			}

			if ( isset( $path ) )
			{
				$parse_path = parse_url( $path );

				if ( isset( $parse_path[ 'query' ] ) )
				{
					parse_str( $parse_path[ 'query' ], $parse_path[ 'query' ] );
					$parse_url[ 'query' ] = array_merge( $parse_url[ 'query' ], $parse_path[ 'query' ] );
				}

				$parse_url[ 'path' ] = rtrim( $parse_url[ 'path' ], '/' ) . '/' . $parse_path[ 'path' ];
			}

			$parse_url[ 'query' ] = empty( $parse_url[ 'query' ] ) ? NULL : '?' . http_build_query( $parse_url[ 'query' ], NULL, '&', PHP_QUERY_RFC3986 );

			return $parse_url[ 'scheme' ] . '://' . $parse_url[ 'host' ] . $parse_url[ 'path' ] . $parse_url[ 'query' ];
		}

		/**
		 * Make an HTTP request
		 *
		 * @param   string $url
		 * @param   array  $params
		 * @param   string $method
		 * @param   bool   $init_only
		 *
		 * @access  public
		 * @throws  Exception
		 * @return  Response
		 */
		public function request( $url, array $params = [ ], $method, $init_only = FALSE )
		{
			if ( empty( $url ) )
			{
				throw new Exception( 'CURL_URLNOTSET' );
			}

			//$this->_options[ CURLOPT_VERBOSE ]        = TRUE;
			$this->_options[ CURLOPT_URL ]            = $url;
			$this->_options[ CURLOPT_HTTP_VERSION ]   = $this->_http_version;
			$this->_options[ CURLOPT_TIMEOUT ]        = $this->_timeout;
			$this->_options[ CURLOPT_CONNECTTIMEOUT ] = $this->_timeout;
			$this->_options[ CURLOPT_MAXREDIRS ]      = $this->_max_redirects;
			$this->_options[ CURLOPT_RETURNTRANSFER ] = TRUE;
			$this->_options[ CURLOPT_SSL_VERIFYPEER ] = $this->_verifypeer;
			$this->_options[ CURLOPT_SSL_VERIFYHOST ] = $this->_verifyhost;
			$this->_options[ CURLOPT_USERAGENT ]      = $this->_useragent;
			$this->_options[ CURLOPT_ENCODING ]       = $this->_encoding;

			if ( isset( $this->_cainfo ) )
			{
				$this->_options[ CURLOPT_CAINFO ] = $this->_cainfo;
			}

			if ( count( $this->_headers ) > 0 )
			{
				$this->_options[ CURLOPT_HEADER ]     = TRUE;
				$this->_options[ CURLOPT_HTTPHEADER ] = $this->__buildHeaders();
			}

			if ( isset( $this->_auth[ 'user' ] ) )
			{
				$this->_options[ CURLOPT_HTTPAUTH ] = $this->_auth[ 'method' ];
				$this->_options[ CURLOPT_USERPWD ]  = $this->_auth[ 'user' ] . ':' . $this->_auth[ 'pass' ];
			}

			if ( $this->_proxy[ 'address' ] !== FALSE )
			{
				$this->_options[ CURLOPT_PROXYTYPE ]       = $this->_proxy[ 'type' ];
				$this->_options[ CURLOPT_PROXY ]           = $this->_proxy[ 'address' ];
				$this->_options[ CURLOPT_PROXYPORT ]       = $this->_proxy[ 'port' ];
				$this->_options[ CURLOPT_HTTPPROXYTUNNEL ] = $this->_proxy[ 'tunnel' ];
				$this->_options[ CURLOPT_PROXYAUTH ]       = $this->_proxy[ 'auth' ][ 'method' ];
				$this->_options[ CURLOPT_PROXYUSERPWD ]    = $this->_proxy[ 'auth' ][ 'user' ] . ':' . $this->_proxy[ 'auth' ][ 'pass' ];
			}

			if ( isset( $this->_cookie ) )
			{
				$this->_options[ CURLOPT_COOKIE ] = $this->_cookie;
			}

			switch ( $method )
			{
				case 'GET':
					break;

				case 'POST':
					$this->_options[ CURLOPT_POST ]       = TRUE;
					$this->_options[ CURLOPT_POSTFIELDS ] = $this->__buildQuery( $params );
					break;

				case 'DELETE':
					$this->_options[ CURLOPT_CUSTOMREQUEST ] = 'DELETE';

					if ( ! isset( $this->_options[ CURLOPT_USERPWD ] ) )
					{
						$this->_options[ CURLOPT_USERPWD ] = 'anonymous:user';
					}
					break;

				case 'PUT':
					if ( ! is_file( $params[ 'filename' ] ) )
					{
						throw new Exception( 'CURL_FILENOTFOUND' );
					}

					$this->_options[ CURLOPT_CUSTOMREQUEST ] = 'PUT';
					$this->_options[ CURLOPT_PUT ]           = TRUE;
					$this->_options[ CURLOPT_INFILESIZE ]    = filesize( $params[ 'filename' ] );
					$this->_options[ CURLOPT_INFILE ]        = fopen( $params[ 'filename' ], 'r' );

					if ( ! isset( $this->_options[ CURLOPT_USERPWD ] ) )
					{
						$this->_options[ CURLOPT_USERPWD ] = 'anonymous:user';
					}
					break;

				case 'HEAD':
					$this->_options[ CURLOPT_HTTPGET ] = TRUE;
					$this->_options[ CURLOPT_HEADER ]  = TRUE;
					$this->_options[ CURLOPT_NOBODY ]  = TRUE;
					break;

				case 'TRACE':
					$this->_options[ CURLOPT_CUSTOMREQUEST ] = 'TRACE';
					break;

				case 'OPTIONS':
					$this->_options[ CURLOPT_CUSTOMREQUEST ] = 'OPTIONS';
					break;

				case 'DOWNLOAD':
					$this->_options[ CURLOPT_CUSTOMREQUEST ]  = 'DOWNLOAD';
					$this->_options[ CURLOPT_BINARYTRANSFER ] = TRUE;
					$this->_options[ CURLOPT_RETURNTRANSFER ] = FALSE;
					break;

				case 'PATCH':
					$this->_options[ CURLOPT_CUSTOMREQUEST ] = 'PATCH';
					break;

				case 'CONNECT':
					$this->_options[ CURLOPT_CUSTOMREQUEST ] = 'CONNECT';
					break;
			}

			if ( in_array( $method, [ 'GET', 'PUT', 'DELETE' ] ) AND ! empty( $params ) )
			{
				$this->_options[ CURLOPT_URL ] .= '?' . $this->__buildQuery( $params );
			}

			//print_out($this);

			$this->_handle = curl_init();
			curl_setopt_array( $this->_handle, $this->_options );

			if ( $init_only === FALSE )
			{
				$response = curl_exec( $this->_handle );
				$error    = curl_error( $this->_handle );
				$info     = curl_getinfo( $this->_handle );

				if ( $response === FALSE )
				{
					$this->_response = json_encode( [ 'status' => [ 'code' => 403, 'description' => 'Bad request' ] ] );
				}

				curl_close( $this->_handle );

				$this->_response = new Response();
				$this->_response->setMetadata( $info );
				$this->_response->setBody( $response );

				if ( $error )
				{
					$this->_response->setError( 500, $error );
				}
				elseif ( $info[ 'http_code' ] !== 200 )
				{
					$error = HttpHeaderStatus::getDescription( $info[ 'http_code' ] );
					$this->_response->setError( $info[ 'http_code' ], $error );
				}

				return $this->_response;
			}

			return $this;
		}

		// ------------------------------------------------------------------------


		public function multiRequest( Requests $requests )
		{
			$this->_handle = curl_multi_init();
			$handles       = [ ];

			foreach ( $requests as $curl )
			{
				curl_multi_add_handle( $this->_handle, $handles[] = $curl->getHandle() );
			}

			//execute the handles
			$active = NULL;
			do
			{
				$multi_exec = curl_multi_exec( $this->_handle, $active );
			}
			while ( $multi_exec == CURLM_CALL_MULTI_PERFORM );

			while ( $active AND $multi_exec == CURLM_OK )
			{
				while ( curl_multi_exec( $this->_handle, $active ) === CURLM_CALL_MULTI_PERFORM ) ;
			}

			foreach ( $handles as $handle )
			{
				$content = curl_multi_getcontent( $handle );

				if ( $content )
				{
					$response = new Response();
					$response->setMetadata( curl_getinfo( $handle ) );
					$response->setBody( $content );
					$response->setError( 500, curl_error( $handle ) );

					$this->_response[] = $response;
				}

				curl_multi_remove_handle( $this->_handle, $handle );
			}

			curl_multi_close( $this->_handle );

			return $this->_response;
		}

		/**
		 * Get Info
		 *
		 * @param bool|FALSE $options
		 *
		 * @return mixed
		 */
		public function getHandle()
		{
			return $this->_handle;
		}

		/**
		 * Get Info
		 *
		 * @param bool|FALSE $options
		 *
		 * @return mixed
		 */
		public function getInfo()
		{
			if ( isset( $this->_response->info ) )
			{
				return $this->_response->info;
			}

			return new Info();
		}

		// ------------------------------------------------------------------------

		public function getError()
		{
			if ( isset( $this->_response->error ) )
			{
				return $this->_response->error;
			}

			return new Error(
				[
					'code'    => 444,
					'message' => HttpHeaderStatus::getDescription( 444 ),
				] );
		}

		/**
		 * Parse URL
		 *
		 * @param        $url
		 * @param string $path
		 * @param array  $params
		 *
		 * @return string
		 */
		public function prepareUrl( $url, $path = '', array $params = [ ] )
		{
			if ( $path )
			{
				if ( isset( $path[ 0 ] ) AND $path[ 0 ] === '/' )
				{
					$path = substr( $path, 1 );
				}

				$url .= $path;
			}

			if ( ! empty( $params ) )
			{
				// does it exist a query string?
				$queryString = parse_url( $url, PHP_URL_QUERY );
				if ( empty( $queryString ) )
				{
					$url .= '?';
				}
				else
				{
					$url .= '&';
				}

				// it needs to be PHP_QUERY_RFC3986. We want to have %20 between scopes
				$url .= $this->__buildQuery( $params );
			}

			return $url;
		}

		// ------------------------------------------------------------------------

		/**
		 * Build Query
		 *
		 * @param array $params
		 *
		 * @return string
		 */
		private function __buildQuery( array $params = [ ] )
		{
			return http_build_query( $params, NULL, '&', PHP_QUERY_RFC3986 );
		}

		// ------------------------------------------------------------------------

		/**
		 * Build Headers
		 *
		 * @return array
		 */
		private function __buildHeaders()
		{
			$formatted_headers = [ ];

			foreach ( $this->_headers as $key => $value )
			{
				$key = trim( $key );

				if ( strpos( $key, '-' ) !== FALSE )
				{
					$x_key = explode( '-', $key );
					$key   = implode( '-', array_map( 'ucfirst', $x_key ) );
				}
				else
				{
					$x_key = explode( ' ', $key );
					$key   = implode( ' ', array_map( 'ucfirst', $x_key ) );
				}

				$formatted_headers[] = $key . ': ' . trim( $value );
			}

			return $formatted_headers;
		}
	}
}

namespace O2System\CURL
{
	/**
	 * Class Exception
	 *
	 * @package O2System\Cache
	 */
	class Exception extends \O2System\Core\Exception
	{
		public $view_exception = 'curl_exception.php';

		public $library = [
			'name'        => 'O2System CURL (O2CURL)',
			'description' => 'Open Source PHP CURL Wrapper Class',
			'version'     => '1.0',
		];
	}

	// ------------------------------------------------------------------------
}