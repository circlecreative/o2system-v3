<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/15/2016
 * Time: 2:37 AM
 */

namespace O2System\Template\Collections;


use O2System\Glob\ArrayObject;

class Widgets extends ArrayObject
{
	public function render()
	{
		if ( $this->__isEmpty() === FALSE )
		{
			return implode( PHP_EOL, $this->getArrayCopy() );
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}
}