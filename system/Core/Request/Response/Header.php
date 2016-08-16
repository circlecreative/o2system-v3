<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 5:00 PM
 */

namespace O2System\Core\Request\Response;


use O2System\Libraries\Encryption;

class Header extends \O2System\Core\HTTP\Header
{
	public function __construct()
	{
		parent::__construct();

		// Create ETag
		$ETag_vars = [
			'URI'          => \O2System::$request->uri->string,
			'REQUEST_TIME' => \O2System::$request->getTime(),
		];

		$encrypt = new Encryption();
		$ETag    = $encrypt->encrypt( json_encode( $ETag_vars ) );

		$this->setLine( Header::RESPONSE_ETAG, $ETag );
	}

	public function send()
	{
		if ( isset( $this->protocolVersion ) AND isset( $this->statusCode ) AND isset( $this->statusDescription ) )
		{
			$this->statusDescription = readable( str_replace( 'STATUS_', '', $this->statusDescription ), TRUE );

			@header( $this->protocolVersion . ' ' . $this->statusCode . ' ' . $this->statusDescription, TRUE, $this->statusCode );
		}

		if ( isset( $this->statusCode ) AND isset( $this->statusDescription ) )
		{
			@header( Header::RESPONSE_STATUS . ': ' . $this->statusCode . ' ' . $this->statusDescription, TRUE );
		}

		@header( Header::RESPONSE_X_GENERATED_BY . ': ' . SYSTEM_NAME . ' ' . SYSTEM_VERSION );

		if ( \O2System::$security->protection->xss->isEnabled() )
		{
			@header( Header::RESPONSE_X_XSS_PROTECTION . ': ' . '1; mode=block;' );
		}

		if ( \O2System::$security->protection->csrf->isEnabled() )
		{
			@header( Header::RESPONSE_X_CSRF_PROTECTION . ': ' . '1; mode=block;' );
		}

		@header( Header::RESPONSE_DATE . ': ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT', TRUE );

		if ( isset( $this->expires ) )
		{
			$max_age = $this->expires - $_SERVER[ 'REQUEST_TIME' ];

			@header( Header::RESPONSE_PRAGMA . ': public', TRUE );
			@header( Header::RESPONSE_EXPIRES . ': ' . gmdate( 'D, d M Y H:i:s', $this->expires ) . ' GMT', TRUE );
			@header( Header::RESPONSE_CACHE_CONTROL . ': max-age=' . (int) $max_age . ', public', TRUE );
		}

		if ( isset( $this->lastModified ) )
		{
			@header( Header::RESPONSE_LAST_MODIFIED . ': ' . gmdate( 'D, d M Y H:i:s', $this->lastModified ) . ' GMT' );
		}

		foreach ( $this->getArrayCopy() as $line => $value )
		{
			@header( $line . ': ' . $value, TRUE );
		}
	}
}