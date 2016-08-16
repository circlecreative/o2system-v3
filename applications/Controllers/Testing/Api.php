<?php
namespace Applications\Controllers\Testing;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

use O2System\Controllers\Restful;
use O2System\Core\HTTP\Header;
use O2System\Core\SPL\ArrayObject;

class Api extends Restful
{
	protected function _isAuthorize()
	{
		// TODO: Implement _isAuthorize() method.
		return TRUE;
	}

	protected function _isAllowedOrigin()
	{
		$server = $this->request->getServer( 'HTTP_HOST' );
		$origin = empty( $this->request->getHeader( 'ORIGIN' ) ) ? $this->request->getHeader( 'HOST' ) : $this->request->getHeader( 'ORIGIN' );

		if ( gethostbyname( $origin ) === gethostbyname( $server ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	protected function _index()
	{
		$this->_sendPayload(
			[
				'status' => new ArrayObject(
					[
						'success' => FALSE,
						'code'    => 200,
					] ),
			] );
	}
}