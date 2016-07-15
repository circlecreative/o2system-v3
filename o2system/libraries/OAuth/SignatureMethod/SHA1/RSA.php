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

abstract class RSA extends SignatureMethod
{
	public function getName()
	{
		return "RSA-SHA1";
	}

	// Up to the SP to implement this lookup of keys. Possible ideas are:
	// (1) do a lookup in a table of trusted certs keyed off of consumer
	// (2) fetch via http using a url provided by the requester
	// (3) some sort of specific discovery code based on request
	//
	// Either way should return a string representation of the certificate
	protected abstract function fetchPublicCertificate( Request &$request );

	// Up to the SP to implement this lookup of keys. Possible ideas are:
	// (1) do a lookup in a table of trusted certs keyed off of consumer
	//
	// Either way should return a string representation of the certificate
	protected abstract function fetchPrivateCertificate( Request &$request );

	public function buildSignature( Request $request, Consumer $consumer, Token $token )
	{
		$base_string          = $request->getSignatureBaseString();
		$request->base_string = $base_string;

		// Fetch the private key cert based on the request
		$cert = $this->fetchPrivateCertificate( $request );

		// Pull the private key ID from the certificate
		$privatekeyid = openssl_get_privatekey( $cert );

		// Sign using the key
		$ok = openssl_sign( $base_string, $signature, $privatekeyid );

		// Release the key resource
		openssl_free_key( $privatekeyid );

		return base64_encode( $signature );
	}

	public function checkSignature( Request $request, Consumer $consumer, Token $token, $signature )
	{
		$decoded_sig = base64_decode( $signature );

		$base_string = $request->getSignatureBaseString();

		// Fetch the public key cert based on the request
		$cert = $this->fetchPublicCertificate( $request );

		// Pull the public key ID from the certificate
		$publickeyid = openssl_get_publickey( $cert );

		// Check the computed signature against the one passed in the query
		$ok = openssl_verify( $base_string, $decoded_sig, $publickeyid );

		// Release the key resource
		openssl_free_key( $publickeyid );

		return $ok == 1;
	}
}