<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 02-Aug-16
 * Time: 1:34 PM
 */

namespace O2System\Security\Handlers;

use O2System\Core;

class Protection extends Core\Library
{
	use Core\Library\Traits\Handlers;

	public function __construct()
	{
		parent::__construct();

		// Initialize Handlers Classes Lazy Init
		$this->__locateHandlers();

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	public function initialize( Core\SPL\ArrayObject $config )
	{
		if ( $config[ 'xss' ] === TRUE )
		{
			$this->xss->initialize();
		}

		if ( $config[ 'csrf' ] === TRUE )
		{
			$this->csrf->initialize();
		}
	}
}