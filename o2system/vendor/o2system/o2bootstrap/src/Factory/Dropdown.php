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


class Dropdown extends Lists
{
	const ITEM_HEADER = 'ITEM_HEADER';

	protected $_tag        = 'div';
	protected $_attributes = [
		'class' => [ 'dropdown' ],
	];

	protected $_is_dropup = FALSE;
	protected $_is_split  = FALSE;

	/**
	 * @type Button
	 */
	public $button;

	/**
	 * @type Button
	 */
	public $caret;

	public function build()
	{
		$this->setPullClassPrefix( 'dropdown-menu' );

		@list( $button, $attr ) = func_get_args();

		if ( isset( $button ) )
		{
			if ( is_array( $button ) )
			{
				$attr = $button;
			}
			else
			{
				$this->setButton( $button );
			}
		}

		if ( isset( $attr ) )
		{
			$this->addAttributes( $attr );
		}

		$this->setCaret( '' );

		return $this;
	}

	public function __clone()
	{
		foreach ( [ 'button', 'caret' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		return $this;
	}

	public function __call( $method, $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( [ $this, $method ], $args );
		}
		elseif ( method_exists( $this->button, $method ) )
		{
			return call_user_func_array( [ $this->button, $method ], $args );
		}
		elseif ( method_exists( $this->caret, $method ) )
		{
			return call_user_func_array( [ $this->caret, $method ], $args );
		}

		return $this;
	}

	public function addItem( $item, $describe = NULL, $attr = [ ], $key = NULL )
	{
		if ( $describe === self::ITEM_HEADER )
		{
			if ( isset( $attr[ 'class' ] ) )
			{
				if ( is_array( $attr[ 'class' ] ) )
				{
					array_push( $attr[ 'class' ], 'dropdown-header' );
				}
				else
				{
					$attr[ 'class' ] = 'dropdown-header ' . $attr[ 'class' ];
				}
			}
			else
			{
				$attr[ 'class' ] = 'dropdown-header';
			}
		}

		parent::addItem( $item, $describe, $attr, $key );

		return $this;
	}

	public function setButton( $button )
	{
		if ( is_string( $button ) )
		{
			$this->button = new Button( $button );
		}
		elseif ( $button instanceof Tag )
		{
			$this->button = $button;
		}

		$this->button->addClass( 'dropdown-toggle' );
		$this->button->addAttributes( [ 'data-toggle' => 'dropdown', 'aria-haspopup' => TRUE, 'aria-expanded' => TRUE ] );

		return $this;
	}

	public function setCaret( $caret = NULL )
	{
		if ( is_bool( $caret ) )
		{
			$this->caret = FALSE;
		}
		elseif ( is_string( $caret ) OR is_null( $caret ) )
		{
			$this->caret = implode(
				PHP_EOL, [
				new Tag( 'span', $caret, [ 'class' => 'caret' ] ),
				new Tag( 'span', 'Toggle Dropdown', [ 'class' => 'sr-only' ] ),
			] );
		}
		elseif ( $caret instanceof Tag )
		{
			$this->caret = $caret;
		}

		return $this;
	}

	public function isDropup()
	{
		$this->_is_dropup = TRUE;
		$this->removeClass( 'dropdown' );
		$this->addClass( 'dropup' );

		return $this;
	}

	public function isSplit()
	{
		$this->_is_split = TRUE;
		$this->setSizeClassPrefix( 'btn-group' );

		if ( empty( $this->caret ) )
		{
			$this->setCaret();
		}

		$this->caret = new Button( $this->caret, $this->button->getAttributes() );
		$this->button->setAttributes( [ 'class' => $this->button->getClasses() ] );
		$this->button->removeClass( 'dropdown-toggle' );

		return $this;
	}

	public function render()
	{
		if ( ! empty( $this->_items ) )
		{
			if ( $this->_is_split === TRUE )
			{
				$dropdown = new Group( Group::BUTTON_GROUP );
				$dropdown->setAttributes( $this->_attributes );
				$dropdown->addClass( 'btn-group' );

				$dropdown->addItem( $this->button );
				$dropdown->addItem( $this->caret );

				$lists = new Lists();
				$lists->setClass( 'dropdown-menu' );
				$lists->addItems( $this->_items );

				$dropdown->addItem( $lists );

				return $dropdown->render();
			}
			else
			{
				if ( $this->caret )
				{
					$this->button->appendLabel( $this->caret );
				}

				$items[] = $this->button;

				$lists = new Lists();
				$lists->setClass( 'dropdown-menu' );
				$lists->addItems( $this->_items );

				$items[] = $lists;

				return ( new Tag( $this->_tag, implode( PHP_EOL, $items ), $this->_attributes ) )->render();
			}
		}

		return NULL;
	}
}