<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 7:54 PM
 */

namespace O2System\Core\HTTP;

use O2System\Core\File\Document;

class Response implements Header\Fields\Response
{
	protected $body;
	protected $header;

	public function setHeader( Header $header )
	{
		$this->header = $header;

		return $this;
	}

	public function setBody( Document $body )
	{
		$this->body = $body;

		return $this;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getHeader()
	{
		return $this->header;
	}

	public function __get( $name )
	{
		if ( property_exists( $this, $name ) )
		{
			if ( isset( $this->{$name} ) )
			{
				return $this->{$name};
			}
		}

		return NULL;
	}
}