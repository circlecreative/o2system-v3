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

	protected $_tag          = 'fieldset';
	protected $_fieldsetType = 'PANEL_DEFAULT';
	protected $_group_type   = 'FORM_GROUP';

	public $legend = NULL;
	public $fields = NULL;

	public function build()
	{
		@list( $legend, $fieldsetType, $attr ) = func_get_args();

		if ( is_string( $legend ) )
		{
			if ( in_array( $legend, [ self::PANEL_DEFAULT, self::PANEL_FIELDSET ] ) )
			{
				$this->setFieldsetType( $legend );
			}
			elseif ( in_array( $legend, [ Group::FORM_GROUP_INLINE, Group::FORM_GROUP_VERTICAL, Group::FORM_GROUP_HORIZONTAL ] ) )
			{
				$this->setGroupType( $legend );
			}
			else
			{
				$this->setLegend( $legend );
			}
		}
		elseif ( $legend instanceof Tag )
		{
			$this->setLegend( $legend );
		}

		if ( is_array( $fieldsetType ) )
		{
			$attr = $fieldsetType;
		}
		elseif ( is_string( $fieldsetType ) )
		{
			if ( in_array( $fieldsetType, [ self::PANEL_DEFAULT, self::PANEL_FIELDSET ] ) )
			{
				$this->setFieldsetType( $fieldsetType );
			}
			elseif ( in_array( $fieldsetType, [ Group::FORM_GROUP_INLINE, Group::FORM_GROUP_VERTICAL, Group::FORM_GROUP_HORIZONTAL ] ) )
			{
				$this->setGroupType( $fieldsetType );
			}
		}

		if ( isset( $attr ) )
		{
			$this->addAttributes( $attr );
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

	public function setFieldsetType( $fieldsetType )
	{
		if ( in_array( $fieldsetType, [ self::PANEL_DEFAULT, self::PANEL_FIELDSET ] ) )
		{
			$this->_fieldsetType = $fieldsetType;
		}

		return $this;
	}

	public function setGroupType( $group_type )
	{
		if ( in_array( $group_type, [ Group::FORM_GROUP, Group::FORM_GROUP_INLINE, Group::FORM_GROUP_VERTICAL, Group::FORM_GROUP_HORIZONTAL ] ) )
		{
			$this->_group_type = $group_type;
			$this->fields      = new Group( $group_type );
		}

		return $this;
	}

	public function setLegend( $legend, $attr = [ ] )
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

	public function addItems( array $items )
	{
		foreach ( $items as $label => $item )
		{
			$this->addItem( $item );
		}

		return $this;
	}

	public function addItem( $item )
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
					$attr  = isset( $item[ 'label' ][ 'attr' ] ) ? $item[ 'label' ][ 'attr' ] : [ 'for' => $item[ 'input' ][ 'name' ] ];
					$label = new Tag( 'label', $item[ 'label' ][ 'text' ], $attr );
					$label->addClass( 'control-label' );

					if ( isset( $item[ 'label' ][ 'tag' ] ) )
					{
						$label->setTag( $item[ 'label' ][ 'tag' ] );
					}

					$group->addItem( $label );
				}
			}

			// Set Input
			$input = new Input();
			$input->setType( $item[ 'input' ][ 'type' ] );
			$input->setAttributes( $item[ 'input' ][ 'attr' ] );

			if ( isset( $item[ 'input' ][ 'options' ] ) )
			{
				$input->setOptions( $item[ 'input' ][ 'options' ] );
			}

			$properties = $item[ 'input' ];
			unset( $properties[ 'type' ], $properties[ 'attr' ], $properties[ 'options' ] );

			$input->setProperties( $properties );

			// Set Value
			if ( ! empty( $item[ 'input' ][ 'value' ] ) )
			{
				$input->setValue( $item[ 'input' ][ 'value' ], TRUE );
			}

			if ( isset( $item[ 'container' ] ) )
			{
				$container = new Tag( 'div', $input, $item[ 'container' ][ 'attr' ] );

				if ( isset( $item[ 'container' ][ 'tag' ] ) )
				{
					$container->setTag( $item[ 'container' ][ 'tag' ] );
				}

				$group->addItem( $container );
			}
			else
			{
				$group->addItem( $input );
			}

			// Set Help
			if ( ! empty( $item[ 'help' ] ) )
			{
				$group->addItem( new Tag( 'span', $item[ 'help' ], [ 'class' => 'help-block help-control' ] ) );
			}

			$this->_items[] = $group;
		}
	}

	public function render()
	{
		switch ( $this->_fieldsetType )
		{
			case self::PANEL_FIELDSET :

				$fieldset = new Panel( Panel::DEFAULT_PANEL, $this->_attributes );

				$this->legend->appendContent(
					( new Link( [ 'class' => 'panel-collapse pull-right' ] ) )
						->setIcon( 'fa fa-chevron-up' ) );

				$fieldset->setTitle( $this->legend );

				if ( $this->_group_type === Form::HORIZONTAL_FORM )
				{
					$fieldset->addClass( 'form-horizontal' );
				}
				elseif ( $this->_group_type === Form::VERTICAL_FORM )
				{
					$fieldset->addClass( 'form-vertical' );
				}
				elseif ( $this->_group_type === Form::INLINE_FORM )
				{
					$fieldset->addClass( 'form-inline' );
				}

				break;

			default:

				$fieldset = new Tag( $this->_tag, $this->_attributes );

				break;
		}

		$fieldset->setBody( implode( PHP_EOL, $this->_items ) );

		return $fieldset->render();
	}
}