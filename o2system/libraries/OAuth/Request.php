<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12-Jul-16
 * Time: 1:50 PM
 */

namespace O2System\Libraries\OAuth;


use O2System\Libraries\OAuth\Interfaces\SignatureMethod;

class Request
{
	private $__parameters;
	private $__http_method;
	private $__http_url;
	// for debug purposes
	public        $base_string;
	public static $version    = '1.0';
	public static $POST_INPUT = 'php://input';

	public function __construct( $http_url, $http_method, array $parameters = [ ] )
	{
		$parameters          = array_merge( Utility::parseParameters( parse_url( $http_url, PHP_URL_QUERY ) ), $parameters );
		$this->__parameters  = $parameters;
		$this->__http_method = $http_method;
		$this->__http_url    = $http_url;
	}


	/**
	 * attempt to build up a request from what was passed to the server
	 */
	public static function fromRequest( $http_method = NULL, $http_url = NULL, $parameters = NULL )
	{
		$scheme = ( ! isset( $_SERVER[ 'HTTPS' ] ) || $_SERVER[ 'HTTPS' ] != "on" )
			? 'http'
			: 'https';
		@$http_url or $http_url = $scheme .
			'://' . $_SERVER[ 'HTTP_HOST' ] .
			':' .
			$_SERVER[ 'SERVER_PORT' ] .
			$_SERVER[ 'REQUEST_URI' ];
		@$http_method or $http_method = $_SERVER[ 'REQUEST_METHOD' ];

		// We weren't handed any parameters, so let's find the ones relevant to
		// this request.
		// If you run XML-RPC or similar you should use this to provide your own
		// parsed parameter-list
		if ( ! $parameters )
		{
			// Find request headers
			$request_headers = Utility::getHeaders();

			// Parse the query-string to find GET parameters
			$parameters = Utility::parseParameters( $_SERVER[ 'QUERY_STRING' ] );

			// It's a POST request of the proper content-type, so parse POST
			// parameters and add those overriding any duplicates from GET
			if ( $http_method == "POST"
				&& @strstr(
					$request_headers[ "Content-Type" ],
					"application/x-www-form-urlencoded" )
			)
			{
				$post_data  = Utility::parseParameters(
					file_get_contents( self::$POST_INPUT )
				);
				$parameters = array_merge( $parameters, $post_data );
			}

			// We have a Authorization-header with OAuth data. Parse the header
			// and add those overriding any duplicates from GET or POST
			if ( @substr( $request_headers[ 'Authorization' ], 0, 6 ) == "OAuth " )
			{
				$header_parameters = Utility::splitHeader(
					$request_headers[ 'Authorization' ]
				);
				$parameters        = array_merge( $parameters, $header_parameters );
			}

		}

		return new Request( $http_url, $http_method, $parameters );
	}

	/**
	 * pretty much a helper function to set up the request
	 */
	public static function fromConsumerAndToken( Consumer $consumer, Token $token, $http_method, $http_url, array $parameters = [ ] )
	{
		$defaults = [
			"oauth_version"      => static::$version,
			"oauth_nonce"        => static::__generateNonce(),
			"oauth_timestamp"    => static::__generateTimestamp(),
			"oauth_consumer_key" => $consumer->key,
		];

		if ( isset( $token->key ) )
		{
			$defaults[ 'oauth_token' ] = $token->key;
		}

		$parameters = array_merge( $defaults, $parameters );

		return new Request( $http_url, $http_method, $parameters );
	}

	public function setParameters( array $parameters, $allow_duplicates = TRUE )
	{
		foreach ( $parameters as $name => $value )
		{
			$this->setParameter( $name, $value, $allow_duplicates );
		}
	}

	public function setParameter( $name, $value, $allow_duplicates = TRUE )
	{
		if ( $allow_duplicates && isset( $this->__parameters[ $name ] ) )
		{
			// We have already added parameter(s) with this name, so add to the list
			if ( is_scalar( $this->__parameters[ $name ] ) )
			{
				// This is the first duplicate, so transform scalar (string)
				// into an array so we can add the duplicates
				$this->__parameters[ $name ] = [ $this->__parameters[ $name ] ];
			}

			$this->__parameters[ $name ][] = $value;
		}
		else
		{
			$this->__parameters[ $name ] = $value;
		}
	}

	public function getParameter( $name )
	{
		return isset( $this->__parameters[ $name ] ) ? $this->__parameters[ $name ] : NULL;
	}

	public function getParameters()
	{
		return $this->__parameters;
	}

	public function getHttpUrl()
	{
		return $this->__http_url;
	}

	public function getHttpMethod()
	{
		return $this->__http_method;
	}

	public function unsetParameter( $name )
	{
		unset( $this->__parameters[ $name ] );
	}

	/**
	 * The request parameters, sorted and concatenated into a normalized string.
	 *
	 * @return string
	 */
	public function getSignableParameters()
	{
		// Grab all parameters
		$params = $this->__parameters;

		// Remove oauth_signature if present
		// Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
		if ( isset( $params[ 'oauth_signature' ] ) )
		{
			unset( $params[ 'oauth_signature' ] );
		}

		return Utility::buildHttpQuery( $params );
	}

	/**
	 * Returns the base string of this request
	 *
	 * The base string defined as the method, the url
	 * and the parameters (normalized), each urlencoded
	 * and the concated with &.
	 */
	public function getSignatureBaseString()
	{
		$parts = [
			$this->getNormalizedHttpMethod(),
			$this->getNormalizedHttpUrl(),
			$this->getSignableParameters(),
		];

		$parts = Utility::rfc3986UrlEncode( $parts );

		return implode( '&', $parts );
	}

	/**
	 * just uppercases the http method
	 */
	public function getNormalizedHttpMethod()
	{
		return strtoupper( $this->__http_method );
	}

	/**
	 * parses the url and rebuilds it to be
	 * scheme://host/path
	 */
	public function getNormalizedHttpUrl()
	{
		$parts = parse_url( $this->__http_url );

		$port   = @$parts[ 'port' ];
		$scheme = $parts[ 'scheme' ];
		$host   = $parts[ 'host' ];
		$path   = @$parts[ 'path' ];

		$port or $port = ( $scheme == 'https' ) ? '443' : '80';

		if ( ( $scheme == 'https' && $port != '443' )
			|| ( $scheme == 'http' && $port != '80' )
		)
		{
			$host = "$host:$port";
		}

		return "$scheme://$host$path";
	}

	/**
	 * builds a url usable for a GET request
	 */
	public function toUrl()
	{
		$post_data = $this->toPostdata();
		$out       = $this->getNormalizedHttpUrl();
		if ( $post_data )
		{
			$out .= '?' . $post_data;
		}

		return $out;
	}

	/**
	 * builds the data one would send in a POST request
	 */
	public function toPostdata()
	{
		return Utility::buildHttpQuery( $this->__parameters );
	}

	/**
	 * builds the Authorization: header
	 */
	public function toHeader( $realm = NULL )
	{
		$first = TRUE;
		if ( $realm )
		{
			$out   = 'Authorization: OAuth realm="' . Utility::rfc3986UrlEncode( $realm ) . '"';
			$first = FALSE;
		}
		else
		{
			$out = 'Authorization: OAuth';
		}

		$total = [ ];
		foreach ( $this->__parameters as $key => $value )
		{
			if ( substr( $key, 0, 5 ) != "oauth" )
			{
				continue;
			}
			if ( is_array( $value ) )
			{
				throw new Exception( 'Arrays not supported in headers' );
			}
			$out .= ( $first ) ? ' ' : ',';
			$out .= Utility::rfc3986UrlEncode( $key ) .
				'="' .
				Utility::rfc3986UrlEncode( $value ) .
				'"';
			$first = FALSE;
		}

		return $out;
	}

	public function __toString()
	{
		return $this->toUrl();
	}


	public function signRequest( SignatureMethod $signature_method, Consumer $consumer, Token $token )
	{
		$this->setParameter(
			"oauth_signature_method",
			$signature_method->getName(),
			FALSE
		);
		$signature = $this->buildSignature( $signature_method, $consumer, $token );
		$this->setParameter( "oauth_signature", $signature, FALSE );
	}

	public function buildSignature( SignatureMethod $signature_method, Consumer $consumer, Token $token )
	{
		$signature = $signature_method->buildSignature( $this, $consumer, $token );

		return $signature;
	}

	/**
	 * util function: current timestamp
	 */
	private static function __generateTimestamp()
	{
		return time();
	}

	/**
	 * util function: current nonce
	 */
	private static function __generateNonce()
	{
		$mt   = microtime();
		$rand = mt_rand();

		return md5( $mt . $rand ); // md5s look nicer than numbers
	}
}