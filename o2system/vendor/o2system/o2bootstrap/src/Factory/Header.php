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

class Header extends FactoryInterface
{
	protected $_tag = 'div';
	protected $_attributes = array(
		'class' => [ 'page-header' ]
	);
	protected $_is_page_header = TRUE;
	public $title = NULL;
	public $subtext = NULL;

	public function build()
	{
		@list($title, $attr) = func_get_args();

		if( is_array( $title ) )
		{
			$attr = $title;
		}
		else
		{
			$this->set_title( $title );
		}

		if( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	public function __clone()
	{
		foreach ( [ 'title', 'subtext' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		return $this;
	}

	public function set_title( $title, $tag = 'h1', $attr = array() )
	{
		if( is_array( $tag ) )
		{
			$attr = $tag;
			$tag = $this->_is_page_header === TRUE ? 'h1' : 'h2';
		}

		if( $title instanceof Tag )
		{
			$this->title = clone $title;
			$this->title->set_tag( $tag );
		}
		else
		{
			$this->title = new Tag( $tag, $title, $attr );
		}

		return $this;
	}

	public function set_subtext( $subtext, $tag = 'small', $attr = array() )
	{
		if( is_array( $tag ) )
		{
			$attr = $tag;
			$tag = 'small';
		}

		if( $subtext instanceof Tag )
		{
			$this->subtext = clone $subtext;
			$this->subtext->set_tag( $tag );
		}
		else
		{
			$this->subtext = new Tag( $tag, $subtext, $attr );
		}

		return $this;
	}

	public function is_page_header( $is_page_header )
	{
		$this->_is_page_header = (bool) $is_page_header;
	}

	public function render()
	{
		if( isset( $this->title ) )
		{
			if( isset( $this->subtext ) )
			{
				$this->title->append_content( $this->subtext );
			}

			if( $this->is_page_header === TRUE )
			{
				return ( new Tag( $this->_tag, $this->title, $this->_attributes) )->render();
			}
			else
			{
				return $this->title->render();
			}
		}

		return '';
	}
}