<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 28-Jul-16
 * Time: 12:30 AM
 */

namespace O2System\Core\Request\Handlers;


use O2System\Core\Library\Handler;

class Negotiation extends Handler
{
	protected $_supported = [
		'charset'  => [ ],
		'encoding' => [ ],
		'language' => [ ],
		'media'    => [ ],
		'methods'  => [ ],
	];
}