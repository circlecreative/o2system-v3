<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12-Jul-16
 * Time: 1:35 PM
 */

namespace O2System\Libraries\OAuth;


class Token
{
	// access tokens and request tokens
	public $key;
	public $secret;

	/**
	 * key = the token
	 * secret = the token secret
	 */
	function __construct( $key = NULL, $secret = NULL )
	{
		$this->key    = $key;
		$this->secret = $secret;
	}

	/**
	 * generates the basic string serialization of a token that a server
	 * would respond to request_token and access_token calls with
	 */
	function __toString()
	{
		return "oauth_token=" .
		Utility::rfc3986UrlEncode( $this->key ) .
		"&oauth_token_secret=" .
		Utility::rfc3986UrlEncode( $this->secret );
	}
}