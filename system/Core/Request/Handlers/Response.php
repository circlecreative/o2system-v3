<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 9:23 AM
 */

namespace O2System\Core\Request\Handlers;


use O2System\Core\File\Interfaces\MIME;
use O2System\Core\Request\Response\Document;
use O2System\Core\Request\Response\Header;

class Response extends \O2System\Core\HTTP\Response
{
	public function __construct()
	{
		if ( empty( $this->header ) )
		{
			$this->header = new Header();
			$this->header->setProtocolVersion( \O2System::$registry->server->get( 'SERVER_PROTOCOL' ) );
			$this->header->setStatusCode( Header::STATUS_OK );
			$this->header->setLine( Header::RESPONSE_CONTENT_TYPE, MIME::TEXT_HTML_UTF8 );
			$this->header->setLine( Header::RESPONSE_CONTENT_ENCODING, 'UTF-8' );
		}

		if ( empty( $this->body ) )
		{
			$this->body = new Document();
			$this->body->setDocType( Document::HTML5 );
			$this->body->setMimeType( MIME::TEXT_HTML );
			$this->body->setEncoding( 'UTF-8' );
		}

		\O2System::$log->debug( 'LOG_DEBUG_REQUEST_RESPONSE_CLASS_INITIALIZED' );
	}

	public function setByStatusCode( $code )
	{
		$this->header->setStatusCode( $code );
	}
}