<?php
/**
 * O2CURL
 *
 * Lightweight HTTP Request Libraries for PHP 5.4+
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
	use O2System\CURL\Interfaces\Method as Method;
	use O2System\CURL\Factory\Response as Response;

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
		use Glob\Interfaces\SingletonInterface;

		/**
		 * CURL Handle
		 *
		 * @access  protected
		 * @type    Resource
		 */
		protected $_handle = NULL;

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
		 * CURL Auth
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_auth = array(
			'user'   => '',
			'pass'   => '',
			'method' => CURLAUTH_BASIC,
		);

		/**
		 * CURL Proxy
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_proxy = array(
			'port'    => FALSE,
			'tunnel'  => FALSE,
			'address' => FALSE,
			'type'    => CURLPROXY_HTTP,
			'auth'    => array(
				'user'   => '',
				'pass'   => '',
				'method' => CURLAUTH_BASIC,
			),
		);

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
		protected $_headers = array();

		// ------------------------------------------------------------------------

		/**
		 * Set Timeout
		 *
		 * @param $timeout
		 */
		public function set_timeout( $timeout )
		{
			$this->_timeout = (int) $timeout;

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
		public function set_verify( $peer = TRUE, $host = 2, $cainfo = NULL )
		{
			$this->_verifypeer = $peer;
			$this->_verifyhost = $host;

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
		public function encoding( $encoding = 'gzip' )
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
		public function set_auth( $username = '', $password = '', $method = CURLAUTH_BASIC )
		{
			$this->_auth[ 'user' ] = $username;
			$this->_auth[ 'pass' ] = $password;
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
		public function set_proxy( $address, $port = 1080, $type = CURLPROXY_HTTP, $tunnel = FALSE )
		{
			$this->_proxy[ 'type' ] = $type;
			$this->_proxy[ 'port' ] = $port;
			$this->_proxy[ 'tunnel' ] = $tunnel;
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
		public function set_headers( array $headers = array() )
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
		public function set_header( $key, $value )
		{
			$this->_headers[ $key ] = $value;

			return $this;
		}

		// ------------------------------------------------------------------------

		public function set_useragent( $useragent )
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
		public function set_cookie( array $cookie )
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
		public function reset_headers()
		{
			$this->_headers = array();

			return $this;
		}

		// ------------------------------------------------------------------------

		/**
		 * Add File
		 *
		 * @param        $filename
		 * @param string $mimetype
		 * @param string $postname
		 *
		 * @return \CURLFile|string
		 */
		public function add_file( $filename, $mimetype = '', $postname = '' )
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

		/**
		 * Send a GET request to a URL
		 *
		 * @param string $url     URL to send the GET request to
		 * @param string $path
		 * @param array  $params
		 * @param array  $headers additional headers to send
		 *
		 * @return Response
		 * @throws \Exception
		 */
		public function get( $url, $path = '', array $params = array(), array $headers = array() )
		{
			$url = $this->parse_url( $url, $path, $params );

			if ( ! empty( $headers ) )
			{
				$this->set_headers( $headers );
			}

			return $this->_request( $url, Method::GET );
		}

		// ------------------------------------------------------------------------

		/**
		 * Send POST request to a URL
		 *
		 * @param string $url     URL to send the POST request to
		 * @param string $path
		 * @param array  $params
		 * @param array  $headers additional headers to send
		 *
		 * @return Response
		 * @throws \Exception
		 */
		public function post( $url, $path = '', array $params = array(), array $headers = array() )
		{
			$url = $this->parse_url( $url, $path );

			if ( ! empty( $headers ) )
			{
				$this->set_headers( $headers );
			}

			return $this->_request( $url, Method::POST, $params );
		}

		// ------------------------------------------------------------------------

		/**
		 * Send a HEAD request to a URL
		 *
		 * @param string $url     URL to send the HEAD request to
		 * @param string $path
		 * @param array  $params
		 * @param array  $headers additional headers to send
		 *
		 * @return Response
		 * @throws \Exception
		 */
		public function head( $url, $path = '', array $params = array(), array $headers = array() )
		{
			$url = $this->parse_url( $url, $path );

			if ( ! empty( $headers ) )
			{
				$this->set_headers( $headers );
			}

			return $this->_request( $url, Method::HEAD, $params );
		}

		// ------------------------------------------------------------------------

		/**
		 * Send a CONNECT request to a URL
		 *
		 * @param string $url     URL to send the CONNECT request to
		 * @param string $path
		 * @param array  $params
		 * @param array  $headers additional headers to send
		 *
		 * @return Response
		 * @throws \Exception
		 */
		public function connect( $url, $path = '', array $params = array(), array $headers = array() )
		{
			$url = $this->parse_url( $url, $path );

			if ( ! empty( $headers ) )
			{
				$this->set_headers( $headers );
			}

			return $this->_request( $url, Method::CONNECT, $params );
		}

		// ------------------------------------------------------------------------

		/**
		 * Send PUT request to a URL
		 *
		 * @param string $url     URL to send the PUT request to
		 * @param string $path
		 * @param array  $params
		 * @param array  $headers additional headers to send
		 *
		 * @return Response
		 * @throws \Exception
		 */
		public function put( $url, $path = '', array $params = array(), array $headers = array() )
		{
			$url = $this->parse_url( $url, $path );

			if ( ! empty( $headers ) )
			{
				$this->set_headers( $headers );
			}

			return $this->_request( $url, Method::PUT, $params );
		}

		// ------------------------------------------------------------------------

		/**
		 * Send PATCH request to a URL
		 *
		 * @param string $url     URL to send the PATCH request to
		 * @param string $path
		 * @param array  $params
		 * @param array  $headers additional headers to send
		 *
		 * @return Response
		 * @throws \Exception
		 */
		public function patch( $url, $path = '', array $params = array(), array $headers = array() )
		{
			$url = $this->parse_url( $url, $path );

			if ( ! empty( $headers ) )
			{
				$this->set_headers( $headers );
			}

			return $this->_request( $url, Method::PATCH, $params );
		}

		// ------------------------------------------------------------------------

		/**
		 * Send TRACE request to a URL
		 *
		 * @param string $url     URL to send the TRACE request to
		 * @param string $path
		 * @param array  $params
		 * @param array  $headers additional headers to send
		 *
		 * @return Response
		 * @throws \Exception
		 */
		public function trace( $url, $path = '', array $params = array(), array $headers = array() )
		{
			$url = $this->parse_url( $url, $path );

			if ( ! empty( $headers ) )
			{
				$this->set_headers( $headers );
			}

			return $this->_request( $url, Method::TRACE, $params );
		}

		// ------------------------------------------------------------------------

		/**
		 * Make an HTTP request
		 *
		 * @param string $url
		 * @param string $method
		 * @param array  $post_params
		 *
		 * @return string
		 * @throws \Exception
		 */
		protected function _request( $url, $method, array $post_params = array() )
		{
			/* Curl settings */
			$options = [
				// CURLOPT_VERBOSE => true,
				CURLOPT_CONNECTTIMEOUT => $this->_timeout,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_SSL_VERIFYPEER => $this->_verifypeer,
				CURLOPT_SSL_VERIFYHOST => $this->_verifyhost,
				CURLOPT_TIMEOUT        => $this->_timeout,
				CURLOPT_URL            => $url,
				CURLOPT_USERAGENT      => $this->_useragent,
				CURLOPT_ENCODING       => 'gzip',
			];

			if ( isset( $this->_cainfo ) )
			{
				$options[ CURLOPT_CAINFO ] = $this->_cainfo;
			}

			if ( ! empty( $this->_headers ) )
			{
				$options[ CURLOPT_HEADER ] = TRUE;
				$options[ CURLOPT_HTTPHEADER ] = $this->_build_headers( $this->_headers );
			}

			if ( ! empty( $this->_auth[ 'user' ] ) )
			{
				$options[ CURLOPT_HTTPAUTH ] = $this->_auth[ 'method' ];
				$options[ CURLOPT_HTTPAUTH ] = $this->_auth[ 'user' ] . ':' . $this->_auth[ 'pass' ];
			}

			if ( $this->_proxy[ 'address' ] !== FALSE )
			{
				$options[ CURLOPT_PROXYTYPE ] = $this->_proxy[ 'type' ];
				$options[ CURLOPT_PROXY ] = $this->_proxy[ 'address' ];
				$options[ CURLOPT_PROXYPORT ] = $this->_proxy[ 'port' ];
				$options[ CURLOPT_HTTPPROXYTUNNEL ] = $this->_proxy[ 'tunnel' ];
				$options[ CURLOPT_PROXYAUTH ] = $this->_proxy[ 'auth' ][ 'method' ];
				$options[ CURLOPT_PROXYUSERPWD ] = $this->_proxy[ 'auth' ][ 'user' ] . ':' . $this->_proxy[ 'auth' ][ 'pass' ];
			}

			if ( isset( $this->_cookie ) )
			{
				$options[ CURLOPT_COOKIE ] = $this->_cookie;
			}

			switch ( $method )
			{
				case 'GET':
					break;
				case 'POST':
					$options[ CURLOPT_POST ] = TRUE;
					$options[ CURLOPT_POSTFIELDS ] = $this->_build_query( $post_params );
					break;
				case 'DELETE':
					$options[ CURLOPT_CUSTOMREQUEST ] = 'DELETE';
					break;
				case 'PUT':
					$options[ CURLOPT_CUSTOMREQUEST ] = 'PUT';
					break;
			}

			if ( in_array( $method, [ 'GET', 'PUT', 'DELETE' ] ) AND ! empty( $post_params ) )
			{
				$options[ CURLOPT_URL ] .= '?' . $this->_build_query( $post_params );
			}

			$this->_handle = curl_init();
			curl_setopt_array( $this->_handle, $options );
			$response = curl_exec( $this->_handle );
			$error = curl_error( $this->_handle );
			$info = $this->info();

			if ( $error )
			{
				throw new \RuntimeException( $error );
			}

			return new Response( $response, $info );
		}

		// ------------------------------------------------------------------------

		/**
		 * Info
		 *
		 * @param bool|FALSE $options
		 *
		 * @return mixed
		 */
		public function info( $options = FALSE )
		{
			if ( $options )
			{
				$info = curl_getinfo( $this->_handle, $options );
			}
			else
			{
				$info = curl_getinfo( $this->_handle );
			}

			return $info;
		}

		// ------------------------------------------------------------------------

		/**
		 * Parse URL
		 *
		 * @param        $url
		 * @param string $path
		 * @param array  $params
		 *
		 * @return string
		 */
		public function parse_url( $url, $path = '', array $params = array() )
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
				$url .= $this->_build_query( $params );
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
		protected function _build_query( array $params = array() )
		{
			return http_build_query( $params, NULL, '&', PHP_QUERY_RFC3986 );
		}

		// ------------------------------------------------------------------------

		/**
		 * Build Headers
		 *
		 * @param array $headers
		 *
		 * @return array
		 */
		protected function _build_headers( array $headers = array() )
		{
			$formatted_headers = array();

			foreach ( $headers as $key => $value )
			{
				$key = trim( $key );
				$x_key = explode( ' ', $key );
				$key = implode( ' ', array_map( 'ucfirst', $x_key ) );

				$formatted_headers[] = $key . ': ' . trim( $value );
			}

			return $formatted_headers;
		}
	}
}