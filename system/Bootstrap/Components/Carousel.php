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
namespace O2System\Bootstrap\Components;


use O2System\Bootstrap\Interfaces\FactoryInterface;
use O2System\Bootstrap\Interfaces\ItemsInterface;

class Carousel extends FactoryInterface
{
	use ItemsInterface;

	protected $_tag        = 'div';
	protected $_attributes = [
		'class'     => [ 'carousel', 'slide' ],
		'data-ride' => 'carousel',
	];

	public function build()
	{
		@list( $id, $attr ) = func_get_args();

		if ( isset( $id ) )
		{
			if ( is_string( $id ) )
			{
				$this->setId( $id );
			}
			elseif ( is_array( $id ) )
			{
				$attr = $id;
				$this->setId( uniqid( 'carousel-' ) );
			}
		}

		if ( isset( $attr ) )
		{
			$this->addAttribute( $attr );
		}

		return $this;
	}

	public function addItem( $item )
	{
		if ( $item instanceof Image )
		{
			$this->_items[] = $item;
		}
		elseif ( $item instanceof Slide )
		{
			$this->_items[] = $item;
		}
		elseif ( $item instanceof Jumbotron )
		{
			$this->_items[] = $item;
		}
		elseif ( is_string( $item ) )
		{
			$image = new Image();
			$image->setSource( $item );

			$this->_items[] = $image;
		}

		return $this;
	}

	public function render()
	{
		if ( ! empty( $this->_items ) )
		{
			$indicators = new Lists( Lists::LIST_UNSTYLED );
			$indicators->setTag( 'ol' );
			$indicators->addClass( 'carousel-indicators' );

			foreach ( $this->_items as $key => $item )
			{
				if ( $key == 0 )
				{
					$indicators->addItem(
						'', Lists::ITEM_ACTIVE, [
						'data-target'   => '#' . $this->_attributes[ 'id' ],
						'data-slide-to' => $key,
					] );

					$slides[ $key ] = new Tag( 'div', $item, [ 'class' => 'item active' ] );
				}
				else
				{
					$indicators->addItem(
						'', [
						'data-target'   => '#' . $this->_attributes[ 'id' ],
						'data-slide-to' => $key,
					] );

					$slides[ $key ] = new Tag( 'div', $item, [ 'class' => 'item' ] );
				}
			}

			$slide = new Tag( 'div', implode( PHP_EOL, $slides ), [ 'class' => 'carousel-inner', 'role' => 'listbox' ] );

			$controls[ 'left' ] = new Link(
				implode(
					PHP_EOL, [
					new Tag( 'span', [ 'class' => 'glyphicon glyphicon-chevron-left', 'aria-hidden' => TRUE ] ),
					new Tag( 'span', 'Previous', [ 'class' => 'sr-only' ] ),
				] ), [ 'class' => 'left carousel-control', 'href' => '#' . $this->_attributes[ 'id' ], 'role' => 'button', 'data-slide' => 'prev' ] );

			$controls[ 'right' ] = new Link(
				implode(
					PHP_EOL, [
					new Tag( 'span', [ 'class' => 'glyphicon glyphicon-chevron-right', 'aria-hidden' => TRUE ] ),
					new Tag( 'span', 'Next', [ 'class' => 'sr-only' ] ),
				] ), [ 'class' => 'right carousel-control', 'href' => '#' . $this->_attributes[ 'id' ], 'role' => 'button', 'data-slide' => 'next' ] );

			return ( new Tag(
				$this->_tag, implode(
				PHP_EOL, [
				//$indicators, $slide, $controls[ 'left' ], $controls[ 'right' ],
				$indicators, $slide,
			] ), $this->_attributes ) )->render();
		}

		return '';
	}
}