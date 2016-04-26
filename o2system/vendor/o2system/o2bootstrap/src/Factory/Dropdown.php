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
	protected $_attributes = array(
		'class' => [ 'dropdown' ],
	);

	protected $_is_dropup = FALSE;
	protected $_is_split  = FALSE;
	public    $button     = NULL;
	public    $caret      = NULL;

	public function build()
	{
		$this->set_pull_class_prefix( 'dropdown-menu' );

		@list( $button, $attr ) = func_get_args();

		if ( isset( $button ) )
		{
			if ( is_array( $button ) )
			{
				$attr = $button;
			}
			else
			{
				$this->set_button( $button );
			}
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		$this->set_caret( '' );

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

	public function __call( $method, $args = array() )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		elseif ( method_exists( $this->button, $method ) )
		{
			return call_user_func_array( array( $this->button, $method ), $args );
		}
		elseif ( method_exists( $this->caret, $method ) )
		{
			return call_user_func_array( array( $this->caret, $method ), $args );
		}

		return $this;
	}

	public function add_item( $item, $describe = NULL, $attr = array() )
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

			parent::add_item( $item, $attr );
		}
		else
		{
			parent::add_item( $item, $describe, $attr );
		}
	}

	public function set_button( $button )
	{
		if ( is_string( $button ) )
		{
			$this->button = new Button( $button );
		}
		elseif ( $button instanceof Tag )
		{
			$this->button = $button;
		}

		$this->button->add_class( 'dropdown-toggle' );
		$this->button->add_attributes( [ 'data-toggle' => 'dropdown', 'aria-haspopup' => TRUE, 'aria-expanded' => TRUE ] );

		return $this;
	}

	public function set_caret( $caret )
	{
		if ( is_bool( $caret ) )
		{
			$this->caret = FALSE;
		}
		elseif ( is_string( $caret ) )
		{
			$this->caret = new Tag( 'span', $caret, [ 'class' => 'caret' ] );
		}
		elseif ( $caret instanceof Tag )
		{
			$this->caret = $caret;
		}

		return $this;
	}

	public function is_dropup()
	{
		$this->_is_dropup = TRUE;
		$this->remove_class( 'dropdown' );
		$this->add_class( 'dropup' );

		return $this;
	}

	public function is_split()
	{
		$this->_is_split = TRUE;
		$this->set_size_class_prefix( 'btn-group' );

		$this->caret = new Button( $this->caret, $this->button->get_attributes() );
		$this->button->set_attributes( [ 'class' => $this->button->get_classes() ] );
		$this->button->remove_class( 'dropdown-toggle' );

		return $this;
	}

	public function render()
	{
		if ( ! empty( $this->_items ) )
		{
			if ( $this->_is_split === TRUE )
			{
				$dropdown = new Group( Group::BUTTON_GROUP );
				$dropdown->set_attributes( $this->_attributes );
				$dropdown->add_class( 'btn-group' );

				$dropdown->add_item( $this->button );
				$dropdown->add_item( $this->caret );

				$lists = new Lists();
				$lists->set_class( 'dropdown-menu' );
				$lists->add_items( $this->_items );

				$dropdown->add_item( $lists );

				return $dropdown->render();
			}
			else
			{
				if ( $this->caret )
				{
					$this->button->append_label( $this->caret );
				}

				$items[] = $this->button;

				$lists = new Lists();
				$lists->set_class( 'dropdown-menu' );
				$lists->add_items( $this->_items );

				$items[] = $lists;

				return ( new Tag( $this->_tag, implode( PHP_EOL, $items ), $this->_attributes ) )->render();
			}
		}

		return NULL;
	}
}