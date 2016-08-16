<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12-Jul-16
 * Time: 1:50 PM
 */

namespace O2System\Libraries\OAuth;


class Server
{
	protected $_timestamp_threshold = 300; // in seconds, five minutes
	protected $_version             = '1.0';             // hi blaine
	protected $_signature_methods   = [ ];

	/**
	 * @var DataStore
	 */
	protected $_data_store;

	public function __construct( DataStore $data_store )
	{
		$this->_data_store = $data_store;
	}

	public function addSignatureMethod( $signature_method )
	{
		$this->_signature_methods[ $signature_method->getName() ] =
			$signature_method;
	}

	// high level functions

	/**
	 * process a request_token request
	 * returns the request token on success
	 */
	public function fetchRequestToken( Request &$request )
	{
		$this->__getVersion( $request );

		$consumer = $this->__getConsumer( $request );

		// no token required for the initial token request
		$token = NULL;

		$this->__checkSignature( $request, $consumer, $token );

		// Rev A change
		$callback  = $request->getParameter( 'oauth_callback' );
		$new_token = $this->_data_store->newRequestToken( $consumer, $callback );

		return $new_token;
	}

	/**
	 * process an access_token request
	 * returns the access token on success
	 */
	public function fetchAccessToken( Request &$request )
	{
		$this->__getVersion( $request );

		$consumer = $this->__getConsumer( $request );

		// requires authorized request token
		$token = $this->__getToken( $request, $consumer, "request" );

		$this->__checkSignature( $request, $consumer, $token );

		// Rev A change
		$verifier  = $request->getParameter( 'oauth_verifier' );
		$new_token = $this->_data_store->newAccessToken( $token, $consumer, $verifier );

		return $new_token;
	}

	/**
	 * verify an api call, checks all the parameters
	 */
	public function verifyRequest( Request &$request )
	{
		$this->__getVersion( $request );
		$consumer = $this->__getConsumer( $request );
		$token    = $this->__getToken( $request, $consumer, "access" );
		$this->__checkSignature( $request, $consumer, $token );

		return [ $consumer, $token ];
	}

	// Internals from here
	/**
	 * version 1
	 */
	private function __getVersion( Request &$request )
	{
		$version = $request->getParameter( "oauth_version" );
		if ( ! $version )
		{
			// Service Providers MUST assume the protocol version to be 1.0 if this parameter is not present.
			// Chapter 7.0 ("Accessing Protected Ressources")
			$version = '1.0';
		}
		if ( $version !== $this->_version )
		{
			throw new Exception( "OAuth version '$version' not supported" );
		}

		return $version;
	}

	/**
	 * figure out the signature with some defaults
	 */
	private function __getSignatureMethod( Request &$request )
	{
		$signature_method =
			@$request->getParameter( "oauth_signature_method" );

		if ( ! $signature_method )
		{
			// According to chapter 7 ("Accessing Protected Ressources") the signature-method
			// parameter is required, and we can't just fallback to PLAINTEXT
			throw new Exception( 'No signature method parameter. This parameter is required' );
		}

		if ( ! in_array(
			$signature_method,
			array_keys( $this->_signature_methods ) )
		)
		{
			throw new Exception(
				"Signature method '$signature_method' not supported " .
				"try one of the following: " .
				implode( ", ", array_keys( $this->_signature_methods ) )
			);
		}

		return $this->_signature_methods[ $signature_method ];
	}

	/**
	 * try to find the consumer for the provided request's consumer key
	 */
	private function __getConsumer( Request &$request )
	{
		$consumer_key = @$request->getParameter( "oauth_consumer_key" );
		if ( ! $consumer_key )
		{
			throw new Exception( "Invalid consumer key" );
		}

		$consumer = $this->_data_store->lookupConsumer( $consumer_key );
		if ( ! $consumer )
		{
			throw new Exception( "Invalid consumer" );
		}

		return $consumer;
	}

	/**
	 * try to find the token for the provided request's token key
	 */
	private function __getToken( Request &$request, Consumer $consumer, $token_type = "access" )
	{
		$token_field = @$request->getParameter( 'oauth_token' );
		$token       = $this->_data_store->lookupToken(
			$consumer, $token_type, $token_field
		);
		if ( ! $token )
		{
			throw new Exception( "Invalid $token_type token: $token_field" );
		}

		return $token;
	}

	/**
	 * all-in-one function to check the signature on a request
	 * should guess the signature method appropriately
	 */
	private function __checkSignature( Request &$request, Consumer $consumer, Token $token )
	{
		// this should probably be in a different method
		$timestamp = @$request->getParameter( 'oauth_timestamp' );
		$nonce     = @$request->getParameter( 'oauth_nonce' );

		$this->__checkTimestamp( $timestamp );
		$this->__checkNonce( $consumer, $token, $nonce, $timestamp );

		$signature_method = $this->__getSignatureMethod( $request );

		$signature       = $request->getParameter( 'oauth_signature' );
		$valid_signature = $signature_method->checkSignature(
			$request,
			$consumer,
			$token,
			$signature
		);

		if ( ! $valid_signature )
		{
			throw new Exception( "Invalid signature" );
		}
	}

	/**
	 * check that the timestamp is new enough
	 */
	private function __checkTimestamp( $timestamp )
	{
		if ( ! $timestamp )
		{
			throw new Exception(
				'Missing timestamp parameter. The parameter is required'
			);
		}

		// verify that timestamp is recentish
		$now = time();
		if ( abs( $now - $timestamp ) > $this->_timestamp_threshold )
		{
			throw new Exception(
				"Expired timestamp, yours $timestamp, ours $now"
			);
		}
	}

	/**
	 * check that the nonce is not repeated
	 */
	private function __checkNonce( Consumer $consumer, Token $token, $nonce, $timestamp )
	{
		if ( ! $nonce )
		{
			throw new Exception(
				'Missing nonce parameter. The parameter is required'
			);
		}

		// verify that the nonce is uniqueish
		$found = $this->_data_store->lookupNonce(
			$consumer,
			$token,
			$nonce,
			$timestamp
		);
		if ( $found )
		{
			throw new Exception( "Nonce already used: $nonce" );
		}
	}
}