<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 02-Aug-16
 * Time: 1:15 PM
 */

namespace O2System\Security\Handlers;


class Validation extends \O2System\Core\Input\Validation
{
	public function __construct( array $source_vars = [ ], $language )
	{
		parent::__construct( $source_vars, \O2System::$active->language->parameter );

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}
}