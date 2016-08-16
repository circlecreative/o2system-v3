<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 02-Aug-16
 * Time: 1:33 PM
 */

namespace O2System\Security\Handlers;


class Policy
{
	public function __construct()
	{
		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}
}