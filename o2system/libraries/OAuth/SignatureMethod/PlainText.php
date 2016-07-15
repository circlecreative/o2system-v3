<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12-Jul-16
 * Time: 1:41 PM
 */

namespace o2system\libraries\OAuth\SignatureMethod;

use O2System\Libraries\OAuth\Consumer;
use O2System\Libraries\OAuth\Interfaces\SignatureMethod;
use O2System\Libraries\OAuth\Request;
use O2System\Libraries\OAuth\Token;
use O2System\Libraries\OAuth\Utility;

class PlainText extends SignatureMethod
{
	public function getName()
	{
		return "PLAINTEXT";
	}

	/**
	 * oauth_signature is set to the concatenated encoded values of the Consumer Secret and
	 * Token Secret, separated by a '&' character (ASCII code 38), even if either secret is
	 * empty. The result MUST be encoded again.
	 *   - Chapter 9.4.1 ("Generating Signatures")
	 *
	 * Please note that the second encoding MUST NOT happen in the SignatureMethod, as
	 * OAuthRequest handles this!
	 */
	public function buildSignature( Request $request, Consumer $consumer, Token $token )
	{
		$key_parts = [
			$consumer->secret,
			( $token ) ? $token->secret : "",
		];

		$key_parts            = Utility::rfc3986UrlEncode( $key_parts );
		$key                  = implode( '&', $key_parts );
		$request->base_string = $key;

		return $key;
	}
}