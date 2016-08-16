<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 7:01 PM
 */

namespace O2System\Core\HTML\Elements;


class Frameset
{
	protected $invalidElements = [
		'article',
		'aside',
		'audio',
		'bdi',
		'canvas',
		'datalist',
		'details',
		'dialog',
		'embed',
		'figcaption',
		'figure',
		'footer',
		'header',
		'keygen',
		'main',
		'mark',
		'menuitem',
		'meter',
		'nav',
		'output',
		'rp', 'rt',
		'ruby',
		'section',
		'soource',
		'summary',
		'time',
		'track',
		'video',
		'wbr',
	];
}