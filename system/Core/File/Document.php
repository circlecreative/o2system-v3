<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 09-Aug-16
 * Time: 1:42 AM
 */

namespace O2System\Core\File;


use O2System\Core\File\Interfaces\MIME;

class Document implements MIME
{
	protected $mimeType;
	
	public function setMimeType( $mimeType )
	{
		$this->mimeType = $mimeType;

		return $this;
	}

	public function getMimeType()
	{
		return $this->mimeType;
	}
}