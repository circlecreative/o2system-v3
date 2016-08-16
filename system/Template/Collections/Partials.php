<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/13/2016
 * Time: 3:06 PM
 */

namespace O2System\Template\Collections;

use O2System\Core\Interfaces\DriverInterface;
use O2System\Core\Library\Collections;

class Partials extends Collections
{
	/**
	 * Library Instance
	 *
	 * @access  protected
	 * @type    Template
	 */
	protected $library;

	public function __set( $index, $content )
	{
		$this->offsetSet( $index, $content );
	}

	public function __get( $offset )
	{
		return $this->offsetGet( $offset );
	}

	public function setPartials( array $partials )
	{
		$this->storage = $partials;
	}

	public function addPartials( array $partials )
	{
		foreach ( $partials as $partial => $content )
		{
			$this->addPartial( $partial, $content );
		}
	}

	public function addPartial( $index, $content )
	{
		$vars               = $this->library->getVars();
		$vars[ 'partials' ] = $this;

		$content = $this->library->view->load( $content, $vars, TRUE );

		$this->offsetSet( $index, $content );
	}
}