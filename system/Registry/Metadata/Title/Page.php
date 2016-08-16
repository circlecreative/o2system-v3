<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 5:14 AM
 */

namespace O2System\Registry\Metadata\Title;


use O2System\Core\SPL\ArrayIterator;

class Page extends ArrayIterator
{
	private $separator = '-';

	public function setSeparator( $separator )
	{
		$this->separator = $separator;

		return $this;
	}

	public function __toString()
	{
		return implode( ' ' . $this->separator . ' ', $this->getArrayCopy() );
	}
}