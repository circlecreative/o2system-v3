<?php
/**
 * O2Bootstrap
 *
 * An open source bootstrap components factory for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, PT. Lingkar Kreasi (Circle Creative).
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     O2Bootstrap
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2bootstrap/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2parser.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Bootstrap\Interfaces;

trait ResponsiveInterface
{
	/*
	 * Is Extra Small Visible
	 *
	 * Extra small (less than 768px) visible
	 *
	 * @access public
	 * @return $this
	 */
	public function isXsmallVisible()
	{
		$this->addClass( 'visible-xs' );

		return $this;
	}

	/*
	 * Is Small Visible
	 *
	 * Small (up to 768 px) visible
	 *
	 * @access public
	 * @return $this
	 */
	public function isSmallVisible()
	{
		$this->addClass( 'visible-sm' );

		return $this;
	}

	/*
	 * Is Medium Visible
	 *
	 * Medium (768 px to 991 px) visible
	 *
	 * @access public
	 * @return $this
	 */
	public function isMediumVisible()
	{
		$this->addClass( 'visible-md' );

		return $this;
	}

	/*
	 * Is Large Visible
	 *
	 * Larger (992 px and above) visible
	 *
	 * @access public
	 * @return $this
	 */
	public function isLargeVisible()
	{
		$this->addClass( 'visible-lg' );

		return $this;
	}

	/*
	 * Is Extra Small Hidden
	 *
	 * Extra small (less than 768px) hidden
	 *
	 * @access public
	 * @return $this
	 */
	public function isXsmallHidden()
	{
		$this->addClass( 'hidden-xs' );

		return $this;
	}

	/*
	 * Is Small hidden
	 *
	 * Small (up to 768 px) hidden
	 *
	 * @access public
	 * @return $this
	 */
	public function isSmallHidden()
	{
		$this->addClass( 'hidden-sm' );

		return $this;
	}

	/*
	 * Is Medium Hidden
	 *
	 * Medium (768 px to 991 px) hidden
	 *
	 * @access public
	 * @return $this
	 */
	public function isMediumHidden()
	{
		$this->addClass( 'hidden-md' );

		return $this;
	}

	/*
	 * Is Large Hidden
	 *
	 * Larger (992 px and above) hidden
	 *
	 * @access public
	 * @return $this
	 */
	public function isLargeHidden()
	{
		$this->addClass( 'hidden-lg' );

		return $this;
	}
}