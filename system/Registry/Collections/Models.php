<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 27-Jul-16
 * Time: 8:33 PM
 */

namespace O2System\Registry\Collections;


use O2System\Core\SPL\ArrayObject;

class Models extends ArrayObject
{
	public function __construct()
	{
		parent::__construct();

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}
}