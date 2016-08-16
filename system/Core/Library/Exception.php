<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 11:32 AM
 */

namespace O2System\Core\Library;


class Exception extends \O2System\Core\Exception
{
	public $library = [
		'name'        => 'O2System Core Library Base',
		'description' => 'O2System Base Library Class',
		'version'     => '4.0.0',
	];
}