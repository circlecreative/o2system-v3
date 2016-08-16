<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 5:14 AM
 */

namespace O2System\Core\View\Metadata\Title;


use O2System\Core\SPL\ArrayIterator;

class Page extends ArrayIterator
{
	private $separator = '&#8212;';

	public function set($title, $value = NULL)
	{
		$this->setTitle( $title );
	}

	public function setTitle($title)
	{
		$this->clearStorage();

		if ( is_array( $title ) )
		{
			foreach ( $title as $string )
			{
				$key = $this->count() > 0 ? $this->count() + 1 : 0;
				$this->offsetSet( $key, $string );
			}
		}
		else
		{
			$key = $this->count() > 0 ? $this->count() + 1 : 0;
			$this->offsetSet( $key, $title );
		}
	}

	public function add( $title )
	{
		$this->addTitle( $title );
	}

	public function addTitle( $title )
	{
		$key = $this->count() > 0 ? $this->count() + 1 : 0;
		$this->offsetSet( $key, $title );
	}

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