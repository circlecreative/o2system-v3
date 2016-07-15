<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12-Jul-16
 * Time: 1:36 PM
 */

namespace O2System\Libraries\OAuth\Interfaces;

use O2System\Libraries\OAuth\Consumer;
use O2System\Libraries\OAuth\Token;
use O2System\Libraries\OAuth\Request;

/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class SignatureMethod
{
	/**
	 * Needs to return the name of the Signature Method (ie HMAC-SHA1)
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Build up the signature
	 * NOTE: The output of this function MUST NOT be urlencoded.
	 * the encoding is handled in OAuthRequest when the final
	 * request is serialized
	 *
	 * @param OAuthRequest  $request
	 * @param OAuthConsumer $consumer
	 * @param OAuthToken    $token
	 *
	 * @return string
	 */
	abstract public function buildSignature( Request $request, Consumer $consumer, Token $token );

	/**
	 * Verifies that a given signature is correct
	 *
	 * @param Request  $request
	 * @param Consumer $consumer
	 * @param Token    $token
	 * @param string   $signature
	 *
	 * @return bool
	 */
	public function checkSignature( Request $request, Consumer $consumer, Token $token, $signature )
	{
		$built = $this->buildSignature( $request, $consumer, $token );

		return $built == $signature;
	}
}