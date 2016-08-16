<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 11:54 AM
 */

namespace O2System\Core\View\Metadata;


use O2System\Core\SPL\ArrayObject;

class Powered extends ArrayObject
{
	public function __construct()
	{
		parent::__construct(
			[
				'system_name'    => SYSTEM_NAME,
				'system_version' => SYSTEM_VERSION,
				'proudly'        => 'Proudly powered by ' . SYSTEM_NAME . ' ' . SYSTEM_VERSION . ' &#8212; Open Source PHP Framework',
				'line'           => SYSTEM_NAME . ' ' . SYSTEM_VERSION . ' &#8212; Open Source PHP Framework',
				'url'            => 'https://www.o2system.io',
			] );
	}
}