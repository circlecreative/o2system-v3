<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 15/08/2016
 * Time: 06:51
 */

namespace Applications\Controllers\Testing;

use O2System\Core\Controller;
use O2System\Core\SPL\ArrayIterator;

class ArrayObjects extends Controller
{
	public function index()
	{
		$cachingIterator = new \SplObjectStorage();
		$cachingIterator->attach($this->registry);
		print_out($cachingIterator);
		$fruits = [
			'apple', 'orange', 'plum'
		];
		$object = new ArrayIterator( $fruits );
		//$object->coba['test'] = 'cobalo';

		$object->next();
		//$object->setCurrent(0 );
		print_out( $object );
	}
}