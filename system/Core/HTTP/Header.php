<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 7:03 PM
 */

namespace O2System\Core\HTTP;

use O2System\Core\SPL\ArrayObject;
use O2System\Core\Traits\Constant;

class Header extends ArrayObject implements Header\Protocol, Header\Status, Header\Fields\Request, Header\Fields\Response
{
	use Constant;

	protected $protocolVersion;
	protected $statusCode;
	protected $statusDescription;
	protected $expires;
	protected $lastModified;

	public function __construct()
	{
		if ( empty( $this->protocolVersion ) )
		{
			$this->setProtocolVersion( Header::HTTP_VERSION_11 );
		}

		if ( empty( $this->statusCode ) )
		{
			$this->setStatusCode( Header::STATUS_OK );
		}

		parent::__construct();
	}

	public function setEtag( $ETag )
	{
		$this->setLine( Header::RESPONSE_ETAG, $ETag );
	}

	public function setLine( $line, $value )
	{
		$value = is_array( $value ) ? implode( ', ', $value ) : $value;
		$this->offsetSet( $line, $value );

		return $this;
	}

	public function removeLine( $line )
	{
		$this->offsetUnset( $line );
	}

	public function getLine( $line )
	{
		$this->offsetGet( $line );
	}

	public function getProtocolVersion()
	{
		return $this->protocolVersion;
	}

	public function setProtocolVersion( $protocolVersion )
	{
		if ( in_array( $protocolVersion, [ 'HTTP/1.0', 'HTTP/1.1', 'HTTP/2.0' ] ) )
		{
			$this->protocolVersion = $protocolVersion;
		}

		return $this;
	}

	public function setLastModified( $lastModified )
	{
		if ( is_string( $lastModified ) )
		{
			$lastModified = strtotime( $lastModified );
		}

		$this->lastModified = $lastModified;

		return $this;
	}

	public function setExpires( $expires )
	{
		if ( is_string( $expires ) )
		{
			$expires = strtotime( $expires );
		}
		elseif ( is_numeric( $expires ) )
		{
			$expires = time() + $expires;
		}

		$this->expires = $expires;

		return $this;
	}

	public function getStatusCode()
	{
		return $this->statusCode;
	}

	public function setStatusCode( $statusCode )
	{
		if ( in_array( $statusCode, static::getConstants() ) )
		{
			$this->statusCode        = $statusCode;
			$this->statusDescription = static::getConstant( $statusCode );
		}

		return $this;
	}

	public function getProperties()
	{
		return new ArrayObject(
			[
				'protocolVersion' => $this->protocolVersion,
				'statusCode'      => $this->statusCode,
				'expires'         => $this->expires,
				'lastModified'    => $this->lastModified,
				'lines'           => $this->getArrayCopy(),
			] );
	}

	public function getStatusHeader()
	{
		return \O2System::$language->line( 'HTTP_HEADER_' . $this->statusDescription );
	}

	public function getStatusDescription()
	{
		return \O2System::$language->line( 'HTTP_HEADER_DESCRIPTION_' . $this->statusDescription );
	}

	public function setFromProperties( ArrayObject $properties )
	{
		$this->protocolVersion = $properties->protocolVersion;
		$this->setStatusCode( $properties->statusCode );

		foreach ( $properties->lines as $line => $value )
		{
			$this->setLine( $line, $value );
		}

		return $this;
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

		@header( Header::RESPONSE_DATE . ': ' . gmdate( 'D, d M Y H:i:s', time() ) . ' GMT', TRUE );

		if ( isset( $this->expires ) )
		{
			$maxAge = $this->expires - $_SERVER[ 'REQUEST_TIME' ];

			@header( Header::RESPONSE_PRAGMA . ': public', TRUE );
			@header( Header::RESPONSE_EXPIRES . ': ' . gmdate( 'D, d M Y H:i:s', $this->expires ) . ' GMT', TRUE );
			@header( Header::RESPONSE_CACHE_CONTROL . ': max-age=' . (int) $maxAge . ', public', TRUE );
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