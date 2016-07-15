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

	protected $_attributes = [
		'class' => [ 'nav' ],
	];

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
					$this->setType( $type );
				}
			}
			elseif ( is_array( $type ) )
			{
				$attr = $type;
			}
		}

		if ( isset( $attr ) )
		{
			$this->addAttributes( $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function setType( $type )
	{
		$types = [
			self::NAV_TABS  => 'nav-tabs',
			self::NAV_PILLS => 'nav-pills',
		];

		if ( array_key_exists( $type, $types ) )
		{
			$this->addClass( $types[ $type ] );
		}

		return $this;
	}

	public function addItem( $item, $describe = NULL, $attr = [ ], $key = NULL )
	{
		if ( $item instanceof Link )
		{
			$attr[ 'role' ] = 'presentation';

			if ( class_exists( 'O2System', FALSE ) )
			{
				if ( $item->getAttribute( 'href' ) === current_url() )
				{
					$describe = Lists::ITEM_ACTIVE;
				}
			}

			parent::addItem( $item, $describe, $attr, $key );
		}
		elseif ( $item instanceof Dropdown )
		{
			$dropdown = clone $item;
			$dropdown->setTag( 'li' );

			if ( isset( $dropdown->button ) )
			{
				$dropdown->button->setTag( 'a' )->setClass( 'dropdown-toggle' )->addAtribute( 'href', '#' );
			}

			$dropdown->addAttribute( 'role', 'presentation' );

			parent::addItem( $dropdown, $describe, $attr, $key );
		}
		elseif ( is_string( $item ) )
		{
			$item = new Link( $item, '#' );

			parent::addItem( $item, $describe, $attr, $key );
		}

		return $this;
	}

	/**
	 * stacked
	 *
	 * @return object
	 */
	public function isStacked()
	{
		$this->addClass( 'nav-stacked' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * justified
	 *
	 * @return object
	 */
	public function isJustified()
	{
		$this->addClass( 'nav-justified' );

		return $this;
	}

	// ------------------------------------------------------------------------


}