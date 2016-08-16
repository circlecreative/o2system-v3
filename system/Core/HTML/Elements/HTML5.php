<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 9:44 AM
 */

namespace O2System\Core\HTML\Elements;


use O2System\Core\HTML\Interfaces\Element;

class HTML5 extends Element
{
	protected $invalidElements = [
		'acronym',
		'applet',
		'basefont',
		'big',
		'center',
		'dir',
		'font',
		'frame',
		'frameset',
		'noframes',
		'strike',
		'tt',
	];

	public function purify( $html, $method = 'REPLACE' )
	{
		if ( method_exists( $this, $function = '_purify' . studlycapcase( $method ) ) )
		{
			return call_user_func_array( [ $this, $function ], [ $html ] );
		}

		return $html;
	}

	protected function _purifyReplace( $html )
	{
		foreach ( $this->invalidElements as $element )
		{
			$html = preg_replace( '/<' . $element . '\s(.+?)>(.+?)<\/' . $element . '>/is', "<div $1>$2</div>", $html );
		}

		return $html;
	}
}