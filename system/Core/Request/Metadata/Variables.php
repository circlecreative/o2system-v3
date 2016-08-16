<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 02-Aug-16
 * Time: 10:35 PM
 */

namespace O2System\Core\Request\Metadata;


use O2System\Core\SPL\ArrayObject;

class Variables extends ArrayObject
{
	public function __construct( $vars )
	{
		$vars = is_object( $vars ) ? get_object_vars( $vars ) : $vars;

		parent::__construct( $vars );
	}
}