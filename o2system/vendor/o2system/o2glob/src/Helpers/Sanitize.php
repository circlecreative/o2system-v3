<?php
/**
 * O2Glob
 *
 * Global Common Class Libraries for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2glob/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\Glob\Helpers;


class Sanitize
{
	public static function email( $string )
	{
		$string = preg_replace( '((?:\n|\r|\t|%0A|%0D|%08|%09)+)i', '', $string );

		return (string) filter_var( $string, FILTER_SANITIZE_EMAIL );
	}

	// ------------------------------------------------------------------------

	public static function url( $string )
	{
		return (string) filter_var( $string, FILTER_SANITIZE_URL );
	}

	// ------------------------------------------------------------------------

	public static function numeric( $string )
	{
		return (int) filter_var( $string, FILTER_SANITIZE_NUMBER_INT );
	}

	// ------------------------------------------------------------------------

	protected function string( $string )
	{
		return (string) filter_var( $string, FILTER_SANITIZE_STRING );
	}
}