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

trait AlignmentInterface
{
	protected $_pull_class_prefix = 'pull';

	public function setPullClassPrefix($prefix )
	{
		$this->_pull_class_prefix = $prefix;
	}

	/**
	 * left
	 *
	 * @return object
	 */
	public function pullLeft()
	{
		$this->addClass( $this->_pull_class_prefix . '-left' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * right
	 *
	 * @return object
	 */
	public function pullRight()
	{
		$this->addClass( $this->_pull_class_prefix . '-right' );

		return $this;
	}

	/**
	 * left
	 *
	 * @return object
	 */
	public function alignLeft()
	{
		$this->addClass( 'align-left' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * right
	 *
	 * @return object
	 */
	public function alignRight()
	{
		$this->addClass( 'align-right' );

		return $this;
	}

	public function textLeft()
	{
		$this->addClass( 'text-left' );

		return $this;
	}

	public function textRight()
	{
		$this->addClass( 'text-right' );

		return $this;
	}

	public function textJustify()
	{
		$this->addClass( 'text-justify' );

		return $this;
	}

	public function textCenter()
	{
		$this->addClass( 'text-center' );

		return $this;
	}
}