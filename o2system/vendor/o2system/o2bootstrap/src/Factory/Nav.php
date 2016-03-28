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

/**
 *
 * @package Nav
 */
class Nav extends Lists
{
	use IconInterface;

	const NAV_TABS  = 'NAV_TABS';
	const NAV_PILLS = 'NAV_PILLS';

	protected $_attributes = array(
		'class' => array( 'nav' ),
	);

	/**
	 * build
	 */
	public function build()
	{
		@list( $type, $attr ) = func_get_args();

		if ( isset( $type ) )
		{
			if ( is_string( $type ) )
			{
				if ( in_array( $type, [ self::NAV_TABS, self::NAV_PILLS ] ) )
				{
					$this->set_type( $type );
				}
			}
			elseif ( is_array( $type ) )
			{
				$attr = $type;
			}
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function set_type( $type )
	{
		$types = array(
			self::NAV_TABS  => 'nav-tabs',
			self::NAV_PILLS => 'nav-pills',
		);

		if ( array_key_exists( $type, $types ) )
		{
			$this->add_class( $types[ $type ] );
		}

		return $this;
	}

	public function add_item( $item, $describe = NULL, $attr = array() )
	{
		if ( $item instanceof Link )
		{
			$attr[ 'role' ] = 'presentation';

			if ( class_exists( 'O2System', FALSE ) )
			{
				if ( $item->get_attribute( 'href' ) === current_url() )
				{
					$describe = Lists::ITEM_ACTIVE;
				}
			}

			parent::add_item( $item, $describe, $attr );
		}
		elseif ( $item instanceof Dropdown )
		{
			$dropdown = clone $item;
			$dropdown->set_tag( 'li' );

			if ( isset( $dropdown->button ) )
			{
				$dropdown->button->set_tag( 'a' )->set_class( 'dropdown-toggle' )->add_attribute( 'href', '#' );
			}

			$dropdown->add_attribute( 'role', 'presentation' );
			$this->_items[] = $dropdown;
		}
		elseif ( is_string( $item ) )
		{
			$item = new Link( $item, '#' );

			parent::add_item( $item, $describe, $attr );
		}

		return $this;
	}

	/**
	 * stacked
	 *
	 * @return object
	 */
	public function is_stacked()
	{
		$this->add_class( 'nav-stacked' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * justified
	 *
	 * @return object
	 */
	public function is_justified()
	{
		$this->add_class( 'nav-justified' );

		return $this;
	}

	// ------------------------------------------------------------------------


}