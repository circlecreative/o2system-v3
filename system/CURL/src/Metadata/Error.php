<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 28-Jun-16
 * Time: 9:04 PM
 */

namespace O2System\CURL\Metadata;


use O2System\Core\SPL\ArrayObject;


class Error extends ArrayObject
{
	public function __construct()
	{
		parent::__construct(
			[
				'code'    => 444,
				'message' => HttpHeaderStatus::getDescription( 444 ),
			] );
	}

	public function setCode( $code )
	{
		$this->__set( 'code', $code );
	}

	public function setMessage( $message )
	{
		$this->__set( 'message', $message );
	}
}