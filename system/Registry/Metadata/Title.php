<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 5:08 AM
 */

namespace O2System\Registry\Metadata;

use O2System\Core\SPL\ArrayObject;

class Title extends ArrayObject
{
	public function __construct()
	{
		parent::__construct(
			[
				'page'    => new Title\Page(),
				'browser' => new Title\Browser(),
			] );
	}

	public function setSeparator( $separator )
	{
		$this->page->setSeparator( $separator );
		$this->browser->setSeparator( $separator );
	}

	public function setTitle( $title )
	{
		$this->page->clearStorage();
		$this->browser->clearStorage();

		$this->page[]    = $title;
		$this->browser[] = $title;
	}

	public function setPageTitle( $title )
	{
		$this->page->clearStorage();
		$this->page[] = $title;
	}

	public function setBrowserTitle( $title )
	{
		$this->browser->clearStorage();
		$this->browser[] = $title;
	}
}