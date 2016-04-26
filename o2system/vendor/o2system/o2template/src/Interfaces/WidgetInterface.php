<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/10/2015
 * Time: 3:21 AM
 */

namespace O2System\Template\Interfaces;

use O2System\Template\Metadata\Widget;

interface WidgetInterface
{
	public function setMetadata( Widget $widget );

	public function render();

	public function __toString();
}