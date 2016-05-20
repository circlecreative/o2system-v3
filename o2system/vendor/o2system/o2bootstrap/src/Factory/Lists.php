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
use O2System\Bootstrap\Interfaces\ItemsInterface;
use O2System\Bootstrap\Interfaces\PrintableInterface;
use O2System\Bootstrap\Interfaces\ResponsiveInterface;
use O2System\Bootstrap\Interfaces\SizeInterface;

class Lists extends FactoryInterface
{
	use ItemsInterface;
	use SizeInterface;
	use AlignmentInterface;
	use ResponsiveInterface;
	use PrintableInterface;

	const LIST_DEFINITION = 'LIST_DEFINITION';
	const LIST_NUMBERED   = 'LIST_NUMBERED';
	const LIST_INLINE     = 'LIST_INLINE';
	const LIST_GROUP      = 'LIST_GROUP';
	const LIST_UNSTYLED   = 'LIST_UNSTYLED';
	const ITEM_SEPARATOR  = 'ITEM_SEPARATOR';
	const ITEM_DISABLED   = 'ITEM_DISABLED';
	const ITEM_ACTIVE     = 'ITEM_ACTIVE';

	protected $_tag         = 'ul';
	protected $_item_active = NULL;

	protected $_types = array(
		'group',
		'inline',
		'unstyled',
	);

	protected $_nested_items;


	public function build()
	{
		@list( $type, $attr ) = func_get_args();

		if ( is_array( $type ) )
		{
			$this->add_attributes( $type );
		}
		elseif ( in_array( strtolower( str_replace( 'LIST_', '', $type ) ), $this->_types ) )
		{
			$this->add_class( 'list-' . strtolower( str_replace( 'LIST_', '', $type ) ) );
		}
		elseif ( $type === self::LIST_NUMBERED )
		{
			$this->_tag = 'ol';
		}
		elseif ( $type === self::LIST_DEFINITION )
		{
			$this->_tag = 'dl';
		}

		if ( isset( $attr ) )
		{
			if ( is_array( $attr ) )
			{
				$this->add_attributes( $attr );
			}
		}

		return $this;
	}

	public function add_items( array $items )
	{
		foreach ( $items as $key => $item )
		{
			if ( $item instanceof \ArrayObject AND $item->offsetExists( 'id_parent' ) )
			{
				$this->_nested_items[ $item[ 'id_parent' ] ][] = $item;
			}
			else
			{
				$this->add_item( $item, $key );
			}
		}

		//print_out($this->_nested_items);

		if ( ! empty( $this->_nested_items ) )
		{
			$this->_items = $this->_buildNestedItems();

			//print_out( $this->_items );
		}

		return $this;
	}

	protected function _buildNestedItems( $id_parent = 0 )
	{
		if ( isset( $this->_nested_items[ $id_parent ] ) )
		{
			foreach ( $this->_nested_items[ $id_parent ] as $key => $nested_items )
			{
				//print_out($nested_items);
				// Has Child
				if ( isset( $this->_nested_items[ $nested_items->id ] ) )
				{
					$items[ $key ] = new Tag( 'li', $nested_items[ 'item' ] );
					$lists = new Lists();

					$lists->add_items( $this->_buildNestedItems( $nested_items->id ) );

					$items[ $key ]->append_content( $lists );
				}
				else
				{
					$items[] = new Tag( 'li', $nested_items[ 'item' ] );
				}
			}

			return $items;
		}
	}

	protected function _hasNestedChildItems( $id_parent )
	{
		return (bool) ( isset( $this->_nested_items[ $id_parent ] ) );
	}

	public function add_item( $item, $describe = NULL, $attr = array(), $key = NULL )
	{
		if ( $this->_tag === 'dl' )
		{
			$item = new Tag( 'dt', new Tag( 'dd', $describe ), $attr );

			if ( isset( $describe ) )
			{
				$key = $describe;
			}
		}
		else
		{
			if ( is_array( $describe ) )
			{
				$attr = $describe;
			}
			elseif ( $describe === self::ITEM_DISABLED )
			{
				if ( isset( $attr[ 'class' ] ) )
				{
					if ( is_array( $attr[ 'class' ] ) )
					{
						array_push( $attr[ 'class' ], 'disabled' );
					}
					else
					{
						$attr[ 'class' ] = $attr[ 'class' ] . ' disabled';
					}
				}
				else
				{
					$attr[ 'class' ] = 'disabled';
				}
			}
			elseif ( $describe === self::ITEM_ACTIVE )
			{
				if ( isset( $attr[ 'class' ] ) )
				{
					if ( is_array( $attr[ 'class' ] ) )
					{
						array_push( $attr[ 'class' ], 'active' );
					}
					else
					{
						$attr[ 'class' ] = $attr[ 'class' ] . ' active';
					}
				}
				else
				{
					$attr[ 'class' ] = 'active';
				}
			}
			elseif ( $describe instanceof Badge )
			{
				if ( is_string( $item ) )
				{
					$badge[] = $item;
					$badge[] = $describe;

					$item = implode( PHP_EOL, $badge );
				}
				elseif ( $item instanceof FactoryInterface )
				{
					if ( $this->has_class( 'list-group' ) )
					{
						$badge[] = $item;
						$badge[] = $describe;

						$item = implode( PHP_EOL, $badge );
					}
					else
					{
						$badge = clone $item;

						if ( method_exists( $badge, 'append_label' ) )
						{
							$badge->append_label( $describe );
						}
						elseif ( method_exists( $badge, 'append_content' ) )
						{
							$badge->append_content( $describe );
						}

						$item = $badge;
					}
				}
			}
			elseif ( is_string( $describe ) )
			{
				$key = $describe;
			}

			if ( $item === self::ITEM_SEPARATOR )
			{
				$item = new Tag( 'li', [ 'role' => 'separator', 'class' => 'divider' ] );
			}
			elseif ( ( $item instanceof FactoryInterface AND $item->get_tag() !== 'li' ) OR is_string( $item ) OR is_numeric( $item ) )
			{
				$item = new Tag( 'li', $item, $attr );
			}
		}

		if ( isset( $key ) )
		{
			$this->_items[ $key ] = $item;
		}
		else
		{
			$this->_items[] = $item;
		}

		return $this;
	}

	public function set_active( $index )
	{
		$this->_item_active = (int) $index;

		return $this;
	}

	public function render()
	{
		if ( ! empty( $this->_items ) )
		{
			$lists = array();

			foreach ( $this->_items as $key => $item )
			{
				if ( isset( $this->_item_active ) )
				{
					if ( $key == $this->_item_active )
					{
						$item->add_class( 'active' );
					}
				}

				if ( $this->has_class( 'list-group' ) )
				{
					$item->add_class( 'list-group-item' );
				}

				if ( method_exists( $item, 'render' ) )
				{
					$lists[] = $item->render();
				}
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $lists ), $this->_attributes ) )->render();
		}

		return '';
	}

	public function __call( $method, $args = array() )
	{
		$method = str_replace( 'is_', '', $method );

		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		else
		{
			if ( in_array( $method, $this->_types ) )
			{
				@list( $attr ) = $args;

				$method = 'LIST_' . strtoupper( $method );

				if ( isset( $attr ) )
				{
					return $this->build( $method, $attr );
				}
				else
				{
					return $this->build( $method );
				}
			}
		}
	}
}