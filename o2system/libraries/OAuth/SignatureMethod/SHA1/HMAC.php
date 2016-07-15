<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12-Jul-16
 * Time: 1:39 PM
 */

namespace O2System\Libraries\OAuth\SignatureMethod\SHA1;

use O2System\Libraries\OAuth\Consumer;
use O2System\Libraries\OAuth\Interfaces\SignatureMethod;
use O2System\Libraries\OAuth\Request;
use O2System\Libraries\OAuth\Token;
use O2System\Libraries\OAuth\Utility;

class HMAC extends SignatureMethod
{
	protected $_encoded = 'RFC3986';

	public function setEncoded( $encoded )
	{
		$encoded = strtoupper( $encoded );

		if ( in_array( $encoded, [ 'RFC3986', 'RFC1738' ] ) )
		{
			$this->_encoded = $encoded;
		}
	}

	public function getName()
	{
		return "HMAC-SHA1";
	}

	public function buildSignature( Request $request, Consumer $consumer, Token $token )
	{
		$base_string          = $request->getSignatureBaseString();
		$request->base_string = $base_string;

		$key_parts = [
			$consumer->secret,
			$token->secret,
		];

		$key_parts = Utility::rfc3986UrlEncode( $key_parts );

		$key = implode( '&', $key_parts );

		return base64_encode( hash_hmac( 'sha1', $base_string, $key, TRUE ) );
	}
}