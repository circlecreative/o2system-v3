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

/**
 * Class Bootstrap Alert Builder
 *
 * @package O2Boostrap\Factory
 */
class Group extends FactoryInterface
{
	use AlignmentInterface;
	use ItemsInterface;
	use SizeInterface;
	use ResponsiveInterface;
	use PrintableInterface;

	const BUTTON_GROUP          = 'BUTTON_GROUP';
	const INPUT_GROUP           = 'INPUT_GROUP';
	const FORM_GROUP            = 'FORM_GROUP';
	const FORM_GROUP_VERTICAL   = 'FORM_GROUP_VERTICAL';
	const FORM_GROUP_HORIZONTAL = 'FORM_GROUP_HORIZONTAL';
	const FORM_GROUP_INLINE     = 'FORM_GROUP_INLINE';
	const LIST_GROUP            = 'LIST_GROUP';
	const LIST_LINK_GROUP       = 'LIST_LINK_GROUP';
	const LIST_BUTTON_GROUP     = 'LIST_BUTTON_GROUP';
	const LIST_CUSTOM_GROUP     = 'LIST_CUSTOM_GROUP';
	const PROGRESS_BAR_GROUP    = 'PROGRESS_BAR_GROUP';
	const THUMBNAIL_GROUP       = 'THUMBNAIL_GROUP';
	const MEDIA_GROUP           = 'MEDIA_GROUP';
	const PANEL_GROUP           = 'PANEL_GROUP';

	protected $_type         = NULL;
	protected $_tag          = 'div';
	protected $_is_justified = FALSE;
	protected $_is_vertical  = FALSE;

	/**
	 * Group
	 *
	 * @param string | array $button
	 * @param type           $position
	 *
	 * @return type
	 */
	public function build()
	{
		@list( $type, $attr ) = func_get_args();

		if ( isset( $attr ) )
		{
			if ( isset( $attr[ 'class' ] ) )
			{
				if ( is_string( $attr[ 'class' ] ) )
				{
					$class = explode( ' ', $attr[ 'class' ] );
					$attr[ 'class' ] = array_map( 'trim', $class );
				}
			}
			else
			{
				$attr[ 'class' ] = array();
			}

			$this->_attributes = $attr;
		}

		switch ( $type )
		{
			case self::BUTTON_GROUP:
				$this->_type = self::BUTTON_GROUP;
				$this->_tag = 'div';

				// Set SizeInterface class prefix
				$this->set_size_class_prefix( 'btn-group' );
				$this->add_class( 'btn-group' );
				$this->add_attribute( 'role', 'group' );
				break;

			case self::INPUT_GROUP:
				$this->_type = self::INPUT_GROUP;
				$this->_tag = 'div';

				// Set SizeInterface class prefix
				$this->set_size_class_prefix( 'input-group' );
				$this->add_class( 'input-group' );
				break;

			case self::FORM_GROUP:
				$this->_type = self::FORM_GROUP;
				$this->_tag = 'div';
				$this->add_class( 'form-group' );
				break;

			case self::FORM_GROUP_INLINE:
				$this->_type = self::FORM_GROUP_INLINE;
				$this->_tag = 'div';
				$this->add_class( 'form-inline' );
				break;

			case self::FORM_GROUP_VERTICAL:
				$this->_type = self::FORM_GROUP_VERTICAL;
				$this->_tag = 'div';
				$this->add_class( 'form-vertical' );
				break;

			case self::FORM_GROUP_HORIZONTAL:
				$this->_type = self::FORM_GROUP_HORIZONTAL;
				$this->_tag = 'div';
				$this->add_class( 'form-horizontal' );
				break;

			case self::LIST_GROUP:
				$this->_type = self::LIST_GROUP;
				$this->_tag = 'ul';
				$this->add_class( 'list-group' );
				break;

			case self::LIST_BUTTON_GROUP:
			case self::LIST_LINK_GROUP:
			case self::LIST_CUSTOM_GROUP:
				$this->_type = self::LIST_GROUP;
				$this->_tag = 'div';
				$this->add_class( 'list-group' );
				break;

			case self::PROGRESS_BAR_GROUP:
				$this->_type = self::PROGRESS_BAR_GROUP;
				$this->_tag = 'div';
				$this->add_class( 'progress' );
				break;

			case self::THUMBNAIL_GROUP:
				$this->_type = self::THUMBNAIL_GROUP;
				$this->_tag = 'div';
				$this->add_classes( [ 'row', 'thumbnails' ] );
				break;

			case self::MEDIA_GROUP:
				$this->_type = self::MEDIA_GROUP;
				$this->_tag = 'div';
				$this->add_class( 'media-list' );
				break;
			case self::PANEL_GROUP:
				$this->_type = self::PANEL_GROUP;
				$this->_tag = 'div';
				$this->add_class( 'panel-group' );
				break;
		}

		return $this;
	}

	public function is_vertical()
	{
		if ( $this->_type === self::BUTTON_GROUP )
		{
			$this->_is_vertical = TRUE;
			$this->remove_class( 'btn-group' );
			$this->add_class( 'btn-group-vertical' );

			foreach ( $this->_items as $key => $item )
			{
				if ( $item instanceof Dropdown )
				{
					$item->button->add_class( 'dropdown-toggle' );
				}
			}
		}

		return $this;
	}

	public function is_justified()
	{
		if ( $this->_type === self::BUTTON_GROUP )
		{
			$this->_is_justified = TRUE;
			$this->add_class( 'btn-group-justified' );

			foreach ( $this->_items as $key => $item )
			{
				$link = clone $item;
				//$link->set_tag( 'a' );
				$this->_items[ $key ] = $link;
			}
		}

		return $this;
	}


	// ------------------------------------------------------------------------

	public function add_item( $item )
	{
		if ( $item instanceof Dropdown )
		{
			if ( $this->_type === self::BUTTON_GROUP OR
					$this->_is_justified === TRUE OR
					$this->_is_vertical === TRUE
			)
			{
				$item->remove_class( 'dropdown' )->add_class( 'btn-group' )->add_attribute( 'role', 'group' );
			}

			$this->_items[] = $item;
		}
		elseif ( $item instanceof Progress )
		{
			if ( $this->_type === self::PROGRESS_BAR_GROUP )
			{
				$progress_bar = explode( PHP_EOL, $item->render() );
				array_shift( $progress_bar );
				array_pop( $progress_bar );

				$this->_items[] = implode( PHP_EOL, $progress_bar );
			}
		}
		elseif ( $item instanceof Button )
		{
			if ( $this->_type === self::BUTTON_GROUP AND
					$this->_is_justified === TRUE
			)
			{
				$button = clone $item;
				$button->set_tag( 'a' );

				$this->_items[] = $button;
			}
			else
			{
				$this->_items[] = $item;
			}
		}
		else
		{
			$this->_items[] = $item;
		}

		return $this;
	}

	public function render()
	{
		if ( ! empty( $this->_items ) )
		{
			if ( $this->_type === self::THUMBNAIL_GROUP )
			{
				$col_xs = round( 12 / ( $this->_num_per_rows - 2 ) );
				$col_sm = round( 12 / ( $this->_num_per_rows - 1 ) );
				$col_md = round( 12 / ( $this->_num_per_rows ) );
				$col_lg = round( 12 / ( $this->_num_per_rows ) );

				foreach ( $this->_items as $key => $item )
				{
					$container = new Tag( 'div', $item, [ 'class' => 'thumbnail-item' ] );
					$container->add_class( 'col-xs-' . $col_xs );
					$container->add_class( 'col-sm-' . $col_sm );
					$container->add_class( 'col-md-' . $col_md );
					$container->add_class( 'col-lg-' . $col_lg );

					$this->_items[ $key ] = $container;
				}
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $this->_items ), $this->_attributes ) )->render();
		}

		return '';
	}
}