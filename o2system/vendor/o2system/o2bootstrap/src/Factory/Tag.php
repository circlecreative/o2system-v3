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

use O2System\Bootstrap\Interfaces\FactoryInterface;
use O2System\Bootstrap\Interfaces\AlignmentInterface;
use O2System\Bootstrap\Interfaces\PrintableInterface;
use O2System\Bootstrap\Interfaces\ResponsiveInterface;
use O2System\Bootstrap\Interfaces\TypographyInterface;
use O2System\Bootstrap\Interfaces\ContentInterface;


class Tag extends FactoryInterface
{
	use AlignmentInterface;
	use TypographyInterface;
	use ResponsiveInterface;
	use PrintableInterface;
	use ContentInterface;

	protected $_tag;
	
	protected $_attributes = array();

	public function build()
	{
		@list( $tag, $content, $attr ) = func_get_args();

		if ( isset( $tag ) )
		{
			$this->_tag = $tag;
		}

		if ( isset( $content ) )
		{
			if ( is_array( $content ) )
			{
				$attr = $content;
			}
			else
			{
				$this->set_content( $content );
			}
		}

		if ( ! empty( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	public function set_tag( $tag )
	{
		$this->_tag = $tag;
	}

	public function open()
	{
		$attr = $this->_attributes;
		unset( $attr[ 'realpath' ] );

		return '<' . $this->_tag . $this->_stringify_attributes( $attr ) . '>';
	}

	public function close()
	{
		return '</' . $this->_tag . '>';
	}

	public function render()
	{
		$self_closing_tags = array(
			'area',
			'base',
			'br',
			'col',
			'command',
			'embed',
			'hr',
			'img',
			'input',
			'keygen',
			'link',
			'meta',
			'param',
			'source',
			'track',
			'wbr',
		);

		if ( in_array( $this->_tag, $self_closing_tags ) )
		{
			$attr = $this->_attributes;
			unset( $attr[ 'realpath' ] );

			return '<' . $this->_tag . $this->_stringify_attributes( $attr ) . ' />';
		}
		else
		{
			$output[] = $this->open();

			if ( empty( $this->_content ) )
			{
				$output[] = $this->close();

				return implode( '', $output );
			}
			else
			{
				$output[] = implode( PHP_EOL, $this->_content );
				$output[] = $this->close();

				return implode( PHP_EOL, $output );
			}
		}

		return '';
	}
}