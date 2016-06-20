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
use O2System\Bootstrap\Interfaces\ContentInterface;

/**
 *
 * @package Jumbotron
 */
class Jumbotron extends FactoryInterface
{
	protected $_tag        = 'div';
	protected $_attributes = array(
		'class' => [ 'jumbotron' ],
	);

	protected $_is_full_width = FALSE;

	public $header      = NULL;
	public $description = NULL;
	public $link        = NULL;
	public $buttons     = array();

	// ------------------------------------------------------------------------

	/**
	 * build
	 *
	 * @return object
	 */
	public function build()
	{
		@list( $attr ) = func_get_args();

		if ( is_array( $attr ) )
		{
			$this->addAttributes( $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function __clone()
	{
		foreach ( [ 'header', 'link' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		foreach ( $this->buttons as $key => $button )
		{
			$this->buttons[ $key ] = clone $button;
		}

		return $this;
	}

	public function setImage($image )
	{
		if ( is_file( $image ) )
		{
			$image = path_to_url( $image );
		}

		$style = array(
			"background-image: url('$image')",
			'background-position: center',
			'background-size: cover',
		);

		$this->addAttribute( 'style', implode( ';', $style ) );

		return $this;
	}
	
	/**
	 * Alert Title
	 *
	 * @param string $title
	 * @param string $tag
	 *
	 * @return object
	 */
	public function setHeader($title, $tag = 'h1', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
		}
		
		if ( $title instanceof FactoryInterface )
		{
			$this->header = clone $title;
			$this->header->setTag( $tag );
		}
		else
		{
			$this->header = new Tag( $tag, ucwords( $title ), $attr );
		}

		$this->header->addClass( 'jumbotron-header' );
		
		return $this;
	}
	
	// ------------------------------------------------------------------------

	public function setDescription($description, $tag = 'p', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
			$tag = 'p';
		}

		if ( $description instanceof Tag )
		{
			$this->description = $description;
		}
		else
		{
			$this->description = new Tag( $tag, $description, $attr );
		}

		$this->description->addClass( 'jumbotron-description' );

		return $this;
	}

	/**
	 * link
	 *
	 * @param string $link
	 * @param string $href
	 * @param string $attributes
	 *
	 * @return object
	 */
	public function setLink($link, $attr = array() )
	{
		if ( $link instanceof Link )
		{
			$this->link = clone $link;
		}
		else
		{
			$this->link = new Link( '', $link, $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function addButton($label, $attr = array() )
	{
		if ( $label instanceof Button )
		{
			$this->buttons[] = $label;
		}
		elseif ( $label instanceof Link )
		{
			$this->buttons[] = $label;
		}
		else
		{
			$this->buttons = new Button( $label, $attr );
		}

		return $this;
	}

	/**
	 * Render
	 *
	 * @return
	 */
	public function render()
	{
		if ( ! empty( $this->description ) )
		{
			if ( isset( $this->header ) )
			{
				if ( isset( $this->link ) )
				{
					$header = clone $this->link;

					if ( method_exists( $header, 'appendContent' ) )
					{
						$header->appendContent( $this->header );
					}
					elseif ( method_exists( $header, 'appendLabel' ) )
					{
						$header->appendLabel( $this->header );
					}
				}
				else
				{
					$header = $this->header;
				}

				$output[] = $header;
			}

			$output[] = $this->description;

			if ( ! empty( $this->buttons ) )
			{
				$output[] = new Tag( 'p', implode( PHP_EOL, $this->buttons ), [ 'class' => 'jumbotron-buttons' ] );
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $output ), $this->_attributes ) )->render();
		}

		return '';
	}
}