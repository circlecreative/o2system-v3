<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/15/2016
 * Time: 2:37 AM
 */

namespace O2System\Template\Collections;


use O2System\Core\Library\Collections;

class Widgets extends Collections
{
	/**
	 * Library Instance
	 *
	 * @access  protected
	 * @type    Template
	 */
	protected $library;
	
	public function render()
	{
		if ( $this->isEmpty() === FALSE )
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