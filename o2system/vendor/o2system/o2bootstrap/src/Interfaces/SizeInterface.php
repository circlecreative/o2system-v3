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

trait SizeInterface
{
	protected $_sizes = array(
		'tiny',
		'small',
		'medium',
		'large',
	);

	protected $_size_class_prefix = NULL;

	public function set_size_class_prefix( $prefix )
	{
		$this->_size_class_prefix = $prefix;
	}

	/**
	 * tiny
	 *
	 * @return object
	 */
	public function is_tiny()
	{
		$this->add_class( $this->_size_class_prefix . '-' . 'xs' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * small
	 *
	 * @return object
	 */
	public function is_small()
	{
		$this->add_class( $this->_size_class_prefix . '-' . 'sm' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * medium
	 *
	 * @return object
	 */
	public function is_medium()
	{
		$this->add_class( $this->_size_class_prefix . '-' . 'md' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * large
	 *
	 * @return object
	 */
	public function is_large()
	{
		$this->add_class( $this->_size_class_prefix . '-' . 'lg' );

		return $this;
	}

	// ------------------------------------------------------------------------
}