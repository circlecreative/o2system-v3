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

namespace O2System\Bootstrap\Factory;

use O2System\Bootstrap\Interfaces\IconInterface;
use O2System\Bootstrap\Interfaces\AlignmentInterface;
use O2System\Bootstrap\Interfaces\ResponsiveInterface;
use O2System\Bootstrap\Interfaces\TypographyInterface;

/**
 *
 * @package Link
 */
class Link extends Button
{
	use AlignmentInterface;
	use TypographyInterface;
	use ResponsiveInterface;

	protected $_tag        = 'a';
	protected $_attributes = array();

	/**
	 * build
	 *
	 * @return object | string
	 */
	public function build()
	{
		$this->set_contextual_class_prefix( 'btn' );
		$this->set_size_class_prefix( 'btn' );

		@list( $label, $href, $type, $attr ) = func_get_args();

		if ( is_array( $label ) )
		{
			$attr = $label;
		}
		else
		{
			$this->_label[] = $label;
		}

		if ( is_array( $href ) )
		{
			$this->add_attributes( $href );
		}
		else
		{
			$this->add_attribute( 'href', $href );
		}

		if ( isset( $type ) )
		{
			if ( is_array( $type ) )
			{
				$this->add_attributes( $type );
			}
			elseif ( is_string( $type ) )
			{
				if ( in_array( $type, $this->_contextual_classes ) )
				{
					$this->add_class( 'btn' );

					$this->{'is_' . $type}();
				}
			}
		}

		if ( isset( $attr ) )
		{
			if ( is_array( $attr ) )
			{
				$this->add_attributes( $attr );
			}
			elseif ( is_string( $attr ) )
			{
				if ( in_array( $attr, $this->_contextual_classes ) )
				{
					$this->add_class( 'btn' );

					$this->{'is_' . $attr}();
				}
			}
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Render
	 *
	 * @return null|string
	 */
	public function render()
	{
		if ( isset( $this->_label ) )
		{
			if ( isset( $this->icon ) )
			{
				$this->prepend_label( $this->icon );
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $this->_label ), $this->_attributes ) )->render();
		}

		return '';
	}
}
