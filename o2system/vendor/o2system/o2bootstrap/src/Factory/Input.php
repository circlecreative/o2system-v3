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

/**
 *
 * @package Input
 */
class Input extends FactoryInterface
{
	protected $_tag = 'input';

	protected $_attributes = array(
		'class' => [ 'form-control' ],
	);

	protected $_properties = array();

	protected $_type    = NULL;
	protected $_value   = NULL;
	protected $_options = NULL;

	public $label = NULL;
	public $help  = NULL;


	// ------------------------------------------------------------------------

	/**
	 * build
	 *
	 * @return type
	 */
	public function build()
	{
		@list( $type, $attr ) = func_get_args();

		if ( is_string( $type ) )
		{
			$this->set_type( $type );
		}
		elseif ( is_array( $type ) )
		{
			$attr = $type;
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	public function __clone()
	{
		return $this;
	}

	public function set_label( $label, $attr = array() )
	{
		if ( $label instanceof Tag )
		{
			$this->label = $label;
		}
		else
		{
			$this->label = new Label( $label, $attr );
			$this->label->set_tag( 'label' );
		}

		return $this;
	}

	public function set_type( $type )
	{
		$this->_type = $type;

		return $this;
	}

	public function set_value( $value, $post_get = TRUE )
	{
		if ( empty( $value ) )
		{
			if ( $post_get === TRUE )
			{
				if ( isset( $_POST[ $this->_attributes[ 'name' ] ] ) )
				{
					$value = $_POST[ $this->_attributes[ 'name' ] ];
				}
				elseif ( isset( $_GET[ $this->_attributes[ 'name' ] ] ) )
				{
					$value = $_GET[ $this->_attributes[ 'name' ] ];
				}
			}
		}

		$this->_value = $value;

		return $this;
	}

	public function set_options( array $options )
	{
		$this->_options = $options;

		return $this;
	}

	public function set_help( $help, $attr = array() )
	{
		if ( $help instanceof Tag )
		{
			$this->help = $help;
		}
		else
		{
			$this->help = new Tag( 'span', $help, $attr );
		}

		$this->help->add_classes( [ 'help-block', 'help-control' ] );

		return $this;
	}

	public function set_properties( array $properties )
	{
		$this->_properties = $properties;

		return $this;
	}

	public function is_readonly()
	{
		$this->_attributes[ 'readonly' ] = 'readonly';

		return $this;
	}

	public function render()
	{
		switch ( $this->_type )
		{
			case 'text':

				$this->_attributes[ 'type' ] = 'text';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'password':

				$this->_attributes[ 'type' ] = 'password';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'radio':

				$this->_attributes[ 'type' ] = 'radio';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'checkbox':

				$this->_attributes[ 'type' ] = 'checkbox';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'number':

				$this->_attributes[ 'type' ] = 'number';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'date':

				$this->_attributes[ 'type' ] = 'date';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'color':

				$this->_attributes[ 'type' ] = 'color';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'month':

				$this->_attributes[ 'type' ] = 'month';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'week':

				$this->_attributes[ 'type' ] = 'week';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'time':

				$this->_attributes[ 'type' ] = 'time';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'datetime':

				$this->_attributes[ 'type' ] = 'datetime';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'datetime-local':

				$this->_attributes[ 'type' ] = 'datetime-local';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'email':

				$this->_attributes[ 'type' ] = 'email';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'search':

				$this->_attributes[ 'type' ] = 'search';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'tel':

				$this->_attributes[ 'type' ] = 'tel';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'url':

				$this->_attributes[ 'type' ] = 'url';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'select':

				$field = new Tag( 'select', $this->_attributes );

				if ( ! empty( $this->_options ) )
				{
					foreach ( $this->_options as $group => $option )
					{
						if ( is_array( $option ) )
						{
							$group = new Tag( 'optgroup', [ 'label' => $group ] );

							foreach ( $option as $value => $label )
							{
								$attr = [ 'value' => $value ];

								if ( isset( $this->_value ) )
								{
									if ( $this->_value == $value )
									{
										$attr[ 'selected' ] = 'selected';
									}
								}

								$group->append_content( new Tag( 'option', $label, $attr ) );
							}

							$field->append_content( $group );
						}
						elseif ( is_string( $option ) OR is_numeric( $option ) )
						{
							$attr = [ 'value' => $group ];

							if ( isset( $this->_value ) )
							{
								if ( $this->_value == $group )
								{
									$attr[ 'selected' ] = 'selected';
								}
							}

							$field->append_content( new Tag( 'option', $option, $attr ) );
						}
					}
				}

				break;

			case 'textarea':

				$this->_attributes[ 'type' ] = 'textarea';
				$field = new Tag( 'textarea', $this->_attributes );

				break;

			case 'file':

				$this->_attributes[ 'type' ] = 'file';
				$field = new Tag( 'input', $this->_attributes );

				break;

			case 'featured-image':

				$field = new Tag( 'div', [ 'data-role' => 'featured-image' ] );

				$properties[ 'size' ] = isset( $this->_properties[ 'size' ] ) ? $this->_properties[ 'size' ] : [ 'width' => 270, 'height' => 270 ];
				$properties['text'] = isset($this->_properties['text']) ? $this->_properties['text'] : 'Featured Image';

				$image_holder = new Tag( 'img', array(
					'src'          => 'holder.js/' . implode( 'x', $properties[ 'size' ] ) . '?text=' . $this->_properties[ 'text' ],
					'data-pattern' => 'image-preview',
				) );

				if ( ! empty( $this->_value ) )
				{
					$image_holder->add_class( 'hidden' );

					$image_preview = new Tag( 'img', array(
						'src'       => $this->_value,
						'data-role' => 'image-preview',
					) );

					$field->append_content( $image_preview );
				}

				$field->append_content( $image_holder );

				$this->_attributes[ 'type' ] = 'file';
				$this->_attributes[ 'data-role' ] = 'image-input';
				$this->_attributes[ 'accept' ] = 'image/*';

				$input_group = new Group( Group::INPUT_GROUP );
				$input_group->add_item( new Tag(
					                        'span',
					                        new Tag( 'span', '<i class="fa fa-camera"></i>' . new Tag( 'input', $this->_attributes ), array(
							                        'class' => 'btn btn-file',
							                        'data-role' => 'image-browse',
							                        'title' => 'Browse',
							                        'data-toggle' => 'tooltip'
					                        ) ),
					                        [ 'class' => 'input-group-btn' ] ) );

				$field->append_content( $input_group );

				break;

			case 'textarea':

				$this->_attributes[ 'type' ] = 'textarea';
				$field = new Tag( 'textarea', $this->_attributes );

				break;

			default:

				$field = new Tag( $this->_tag, $this->_attributes );

				break;
		}

		$field->add_class( 'form-control' );

		if( isset( $this->label ) )
		{
			$group = new Group( Group::FORM_GROUP );
			$group->add_item( $this->label );

			$group->add_item( $field );

			if( isset ($this->help ) )
			{
				$group->add_item( $this->help );
			}

			return $group->render();
		}

		return $field->render();
	}
}
