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

use O2System\Bootstrap\Interfaces\ItemsInterface;

class Fieldset extends Tag
{
	use ItemsInterface;

	const PANEL_DEFAULT  = 'PANEL_DEFAULT';
	const PANEL_FIELDSET = 'PANEL_FIELDSET';

	protected $_tag           = 'fieldset';
	protected $_fieldset_type = 'PANEL_DEFAULT';
	protected $_group_type    = 'FORM_GROUP';

	public $legend = NULL;
	public $fields = NULL;

	public function build()
	{
		@list( $legend, $fieldset_type, $attr ) = func_get_args();

		if ( is_string( $legend ) )
		{
			if ( in_array( $legend, [ self::PANEL_DEFAULT, self::PANEL_FIELDSET ] ) )
			{
				$this->set_fieldset_type( $legend );
			}
			elseif ( in_array( $legend, [ Group::FORM_GROUP_INLINE, Group::FORM_GROUP_VERTICAL, Group::FORM_GROUP_HORIZONTAL ] ) )
			{
				$this->set_group_type( $legend );
			}
			else
			{
				$this->set_legend( $legend );
			}
		}
		elseif ( $legend instanceof Tag )
		{
			$this->set_legend( $legend );
		}

		if ( is_array( $fieldset_type ) )
		{
			$attr = $fieldset_type;
		}
		elseif ( is_string( $fieldset_type ) )
		{
			if ( in_array( $fieldset_type, [ self::PANEL_DEFAULT, self::PANEL_FIELDSET ] ) )
			{
				$this->set_fieldset_type( $fieldset_type );
			}
			elseif ( in_array( $fieldset_type, [ Group::FORM_GROUP_INLINE, Group::FORM_GROUP_VERTICAL, Group::FORM_GROUP_HORIZONTAL ] ) )
			{
				$this->set_group_type( $fieldset_type );
			}
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	public function __clone()
	{
		foreach ( [ 'legend', 'fields' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		return $this;
	}

	public function set_fieldset_type( $fieldset_type )
	{
		if ( in_array( $fieldset_type, [ self::PANEL_DEFAULT, self::PANEL_FIELDSET ] ) )
		{
			$this->_fieldset_type = $fieldset_type;
		}

		return $this;
	}

	public function set_group_type( $group_type )
	{
		if ( in_array( $group_type, [ Group::FORM_GROUP, Group::FORM_GROUP_INLINE, Group::FORM_GROUP_VERTICAL, Group::FORM_GROUP_HORIZONTAL ] ) )
		{
			$this->_group_type = $group_type;
			$this->fields = new Group( $group_type );
		}

		return $this;
	}

	public function set_legend( $legend, $attr = array() )
	{
		if ( is_string( $legend ) )
		{
			$this->legend = new Tag( 'legend', $legend, $attr );
		}
		elseif ( $legend instanceof Tag )
		{
			$this->legend = $legend;
		}

		return $this;
	}

	public function add_items( array $items )
	{
		foreach ( $items as $label => $item )
		{
			$this->add_item( $item );
		}

		return $this;
	}

	public function add_item( $item )
	{
		if ( $item instanceof Group )
		{
			$this->_items[] = clone $item;
		}
		elseif ( isset( $item[ 'input' ] ) )
		{
			$group = new Group( Group::FORM_GROUP );

			// Set Input Label
			if ( isset( $item[ 'label' ] ) )
			{
				if ( isset( $item[ 'label' ][ 'show' ] ) AND $item[ 'label' ][ 'show' ] === TRUE )
				{
					$attr = isset( $item[ 'label' ][ 'attr' ] ) ? $item[ 'label' ][ 'attr' ] : [ 'for' => $item[ 'input' ][ 'name' ] ];
					$label = new Tag( 'label', $item[ 'label' ][ 'text' ], $attr );
					$label->add_class( 'control-label' );

					if ( isset( $item[ 'label' ][ 'tag' ] ) )
					{
						$label->set_tag( $item[ 'label' ][ 'tag' ] );
					}

					$group->add_item( $label );
				}
			}

			// Set Input
			$input = new Input();
			$input->set_type( $item[ 'input' ][ 'type' ] );
			$input->set_attributes( $item[ 'input' ][ 'attr' ] );

			if ( isset( $item[ 'input' ][ 'options' ] ) )
			{
				$input->set_options( $item[ 'input' ][ 'options' ] );
			}

			$properties = $item[ 'input' ];
			unset( $properties[ 'type' ], $properties[ 'attr' ], $properties[ 'options' ] );

			$input->set_properties( $properties );

			// Set Value
			if ( ! empty( $item[ 'input' ][ 'value' ] ) )
			{
				$input->set_value( $item[ 'input' ][ 'value' ], TRUE );
			}

			if ( isset( $item[ 'container' ] ) )
			{
				$container = new Tag( 'div', $input, $item[ 'container' ][ 'attr' ] );

				if ( isset( $item[ 'container' ][ 'tag' ] ) )
				{
					$container->set_tag( $item[ 'container' ][ 'tag' ] );
				}

				$group->add_item( $container );
			}
			else
			{
				$group->add_item( $input );
			}

			// Set Help
			if ( ! empty( $item[ 'help' ] ) )
			{
				$group->add_item( new Tag( 'span', $item[ 'help' ], [ 'class' => 'help-block help-control' ] ) );
			}

			$this->_items[] = $group;
		}
	}

	public function render()
	{
		switch ( $this->_fieldset_type )
		{
			case self::PANEL_FIELDSET :

				$fieldset = new Panel( Panel::DEFAULT_PANEL, $this->_attributes );

				$this->legend->append_content( ( new Link( [ 'class' => 'panel-collapse pull-right' ] ) )
					                               ->set_icon( 'fa fa-chevron-up' ) );

				$fieldset->set_title( $this->legend );

				if ( $this->_group_type === Form::HORIZONTAL_FORM )
				{
					$fieldset->add_class( 'form-horizontal' );
				}
				elseif ( $this->_group_type === Form::VERTICAL_FORM )
				{
					$fieldset->add_class( 'form-vertical' );
				}
				elseif ( $this->_group_type === Form::INLINE_FORM )
				{
					$fieldset->add_class( 'form-inline' );
				}

				break;

			default:

				$fieldset = new Tag( $this->_tag, $this->_attributes );

				break;
		}

		$fieldset->set_body( implode( PHP_EOL, $this->_items ) );

		return $fieldset->render();
	}
}