<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license      http://opensource.org/licenses/MIT	MIT License
 * @link         http://codeigniter.com
 * @since        Version 1.0.0
 * @filesource
 */
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

/**
 * CodeIgniter Form Helpers
 *
 * @package        CodeIgniter
 * @subpackage     Helpers
 * @category       Helpers
 * @author         EllisLab Dev Team
 * @link           http://codeigniter.com/user_guide/helpers/form_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_open' ) )
{
	/**
	 * Form Declaration
	 *
	 * Creates the opening portion of the form.
	 *
	 * @param    string    the URI segments of the form destination
	 * @param    array     a key/value pair of attributes
	 * @param    array     a key/value pair hidden data
	 *
	 * @return    string
	 */
	function form_open( $action = '', $attributes = [ ], $hidden = [ ] )
	{
		// If no action is provided then set to the current url
		if ( ! $action )
		{
			$action = \O2System::$config->baseURL( \O2System::$active[ 'URI' ]->string );
		}
		// If an action is not a full URL then turn it into one
		elseif ( strpos( $action, '://' ) === FALSE )
		{
			$action = \O2System::$config->baseURL( $action );
		}

		$attributes = _attributes_to_string( $attributes );

		if ( stripos( $attributes, 'method=' ) === FALSE )
		{
			$attributes .= ' method="post"';
		}

		if ( stripos( $attributes, 'accept-charset=' ) === FALSE )
		{
			$attributes .= ' accept-charset="' . strtolower( \O2System::$config->charset ) . '"';
		}

		$form = '<form action="' . $action . '"' . $attributes . ">\n";

		// Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
		if ( \O2System::$config[ 'csrf_protection' ] === TRUE && strpos( $action, \O2System::$config->baseURL() ) !== FALSE && ! stripos( $form, 'method="get"' ) )
		{
			$hidden[ \O2System::Security()->get_csrf_token_name() ] = \O2System::Security()->get_csrf_hash();
		}

		if ( is_array( $hidden ) )
		{
			foreach ( $hidden as $name => $value )
			{
				$form .= '<input type="hidden" name="' . $name . '" value="' . html_escape( $value ) . '" style="display:none;" />' . "\n";
			}
		}

		return $form;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_open_multipart' ) )
{
	/**
	 * Form Declaration - Multipart type
	 *
	 * Creates the opening portion of the form, but with "multipart/form-data".
	 *
	 * @param    string    the URI segments of the form destination
	 * @param    array     a key/value pair of attributes
	 * @param    array     a key/value pair hidden data
	 *
	 * @return    string
	 */
	function form_open_multipart( $action = '', $attributes = [ ], $hidden = [ ] )
	{
		if ( is_string( $attributes ) )
		{
			$attributes .= ' enctype="multipart/form-data"';
		}
		else
		{
			$attributes[ 'enctype' ] = 'multipart/form-data';
		}

		return form_open( $action, $attributes, $hidden );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_hidden' ) )
{
	/**
	 * Hidden Input Field
	 *
	 * Generates hidden fields. You can pass a simple key/value string or
	 * an associative array with multiple values.
	 *
	 * @param    mixed  $name  Field name
	 * @param    string $value Field value
	 * @param    bool   $recursing
	 *
	 * @return    string
	 */
	function form_hidden( $name, $value = '', $recursing = FALSE )
	{
		static $form;

		if ( $recursing === FALSE )
		{
			$form = "\n";
		}

		if ( is_array( $name ) )
		{
			foreach ( $name as $key => $val )
			{
				form_hidden( $key, $val, TRUE );
			}

			return $form;
		}

		if ( ! is_array( $value ) )
		{
			$form .= '<input type="hidden" name="' . $name . '" value="' . html_escape( $value ) . "\" />\n";
		}
		else
		{
			foreach ( $value as $k => $v )
			{
				$k = is_int( $k ) ? '' : $k;
				form_hidden( $name . '[' . $k . ']', $v, TRUE );
			}
		}

		return $form;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_input' ) )
{
	/**
	 * Text Input Field
	 *
	 * @param    mixed
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function form_input( $name = '', $value = '', array $attr = [ ] )
	{
		if ( $value instanceof \O2System\Glob\ArrayObject )
		{
			if ( $value->offsetExists( $name ) )
			{
				$value = $value->offsetGet( $name );
			}
			else
			{
				$value = NULL;
			}
		}

		if ( is_array( $value ) )
		{
			$attr = array_merge( $attr, $value );

			if ( empty( $attr[ 'value' ] ) )
			{
				$attr[ 'value' ] = NULL;
			}
		}
		else
		{
			$attr[ 'value' ] = $value;
		}

		if ( empty( $attr[ 'type' ] ) )
		{
			$attr[ 'type' ] = 'text';
		}

		$attr[ 'name' ] = $name;

		return new \O2System\Bootstrap\Factory\Tag( 'input', $attr );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_multiple_input' ) )
{
	/**
	 * Text Input Field
	 *
	 * @param    mixed
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function form_multiple_input( $name = '', $value = '', $attr = [ ] )
	{
		$inputs         = [ ];
		$attr[ 'type' ] = 'text';

		foreach ( $value as $key => $val )
		{
			$attr[ 'name' ]  = $name . '[' . $key . ']';
			$attr[ 'value' ] = $val;

			$inputs[] = new \O2System\Bootstrap\Factory\Tag( 'input', $attr );
		}

		return implode( PHP_EOL, $inputs );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_password' ) )
{
	/**
	 * Password Field
	 *
	 * Identical to the input function but adds the "password" type
	 *
	 * @param    mixed
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function form_password( $name = '', $value = '', $attr = '' )
	{
		is_array( $name ) OR $name = [ 'name' => $name ];
		$name[ 'type' ] = 'password';

		return form_input( $name, $value, $attr );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_upload' ) )
{
	/**
	 * Upload Field
	 *
	 * Identical to the input function but adds the "file" type
	 *
	 * @param    mixed
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function form_upload( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'file', 'name' => '' ];
		is_array( $name ) OR $name = [ 'name' => $name ];
		$name[ 'type' ] = 'file';

		return '<input ' . _parse_form_attributes( $name, $defaults ) . $attr . " />\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_textarea' ) )
{
	/**
	 * Textarea field
	 *
	 * @param    mixed  $name
	 * @param    string $value
	 * @param    string $attr
	 *
	 * @return    string
	 */
	function form_textarea( $name = '', $value = '', $attr = '' )
	{
		$defaults = [
			'name' => is_array( $name ) ? '' : $name,
			'cols' => '40',
			'rows' => '10',
		];

		if ( ! is_array( $name ) OR ! isset( $name[ 'value' ] ) )
		{
			$val = $value;
		}
		else
		{
			$val = $name[ 'value' ];
			unset( $name[ 'value' ] ); // textareas don't use the value attribute
		}

		return '<textarea ' . _parse_form_attributes( $name, $defaults ) . $attr . '>' . html_escape( $val ) . "</textarea>\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_multiselect' ) )
{
	/**
	 * Multi-select menu
	 *
	 * @param    string
	 * @param    array
	 * @param    mixed
	 * @param    string
	 *
	 * @return    string
	 */
	function form_multiselect( $name = '', $options = [ ], $selected = [ ], $attr = '' )
	{
		if ( ! strpos( $attr, 'multiple' ) )
		{
			$attr .= ' multiple="multiple"';
		}

		return form_dropdown( $name, $options, $selected, $attr );
	}
}

// --------------------------------------------------------------------

if ( ! function_exists( 'form_dropdown' ) )
{
	/**
	 * Drop-down Menu
	 *
	 * @param    mixed $name
	 * @param    mixed $options
	 * @param    mixed $selected
	 * @param    mixed $attr
	 *
	 * @return    string
	 */
	function form_dropdown( $name = '', $options = [ ], $selected = [ ], $attr = [ ] )
	{
		$defaults = [ ];

		if ( is_array( $name ) )
		{
			if ( isset( $name[ 'selected' ] ) )
			{
				$selected = $name[ 'selected' ];
				unset( $name[ 'selected' ] ); // select tags don't have a selected attribute
			}

			if ( isset( $name[ 'options' ] ) )
			{
				$options = $name[ 'options' ];
				unset( $name[ 'options' ] ); // select tags don't use an options attribute
			}
		}
		else
		{
			$defaults = [ 'name' => $name ];
		}

		is_array( $selected ) OR $selected = [ $selected ];
		is_array( $options ) OR $options = [ $options ];

		// If no selected state was submitted we will attempt to set it automatically
		if ( empty( $selected ) )
		{
			if ( is_array( $name ) )
			{
				if ( isset( $name[ 'name' ], $_POST[ $name[ 'name' ] ] ) )
				{
					$selected = [ $_POST[ $name[ 'name' ] ] ];
				}
			}
			elseif ( isset( $_POST[ $name ] ) )
			{
				$selected = [ $_POST[ $name ] ];
			}
		}

		$attr = _attributes_to_string( $attr );

		$multiple = ( count( $selected ) > 1 && strpos( $attr, 'multiple' ) === FALSE ) ? ' multiple="multiple"' : '';

		$form = '<select ' . rtrim( _parse_form_attributes( $name, $defaults ) ) . $attr . $multiple . ">\n";

		foreach ( $options as $key => $val )
		{
			$key = (string) $key;

			if ( is_array( $val ) )
			{
				if ( empty( $val ) )
				{
					continue;
				}

				$form .= '<optgroup label="' . $key . "\">\n";

				foreach ( $val as $optgroup_key => $optgroup_val )
				{
					$sel = in_array( $optgroup_key, $selected ) ? ' selected="selected"' : '';
					$form .= '<option value="' . html_escape( $optgroup_key ) . '"' . $sel . '>'
						. (string) $optgroup_val . "</option>\n";
				}

				$form .= "</optgroup>\n";
			}
			else
			{
				$form .= '<option value="' . html_escape( $key ) . '"'
					. ( in_array( $key, $selected ) ? ' selected="selected"' : '' ) . '>'
					. (string) $val . "</option>\n";
			}
		}

		return $form . "</select>\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_checkbox' ) )
{
	/**
	 * Checkbox Field
	 *
	 * @param    mixed
	 * @param    string
	 * @param    bool
	 * @param    string
	 *
	 * @return    string
	 */
	function form_checkbox( $name = '', $value = '', $checked = FALSE, $attr = '' )
	{
		$defaults = [ 'type' => 'checkbox', 'name' => ( ! is_array( $name ) ? $name : '' ), 'value' => $value ];

		if ( is_array( $name ) && array_key_exists( 'checked', $name ) )
		{
			$checked = $name[ 'checked' ];

			if ( $checked == FALSE )
			{
				unset( $name[ 'checked' ] );
			}
			else
			{
				$name[ 'checked' ] = 'checked';
			}
		}

		if ( $checked == TRUE )
		{
			$defaults[ 'checked' ] = 'checked';
		}
		else
		{
			unset( $defaults[ 'checked' ] );
		}

		return '<input ' . _parse_form_attributes( $name, $defaults ) . $attr . " />\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_radio' ) )
{
	/**
	 * Radio Button
	 *
	 * @param    mixed
	 * @param    string
	 * @param    bool
	 * @param    string
	 *
	 * @return    string
	 */
	function form_radio( $name = '', $value = '', $checked = FALSE, $attr = '' )
	{
		is_array( $name ) OR $name = [ 'name' => $name ];
		$name[ 'type' ] = 'radio';

		return form_checkbox( $name, $value, $checked, $attr );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_submit' ) )
{
	/**
	 * Submit Button
	 *
	 * @param    mixed
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function form_submit( $name = '', $value = '', $attr = '' )
	{
		$defaults = [
			'type'  => 'submit',
			'name'  => is_array( $name ) ? '' : $name,
			'value' => $value,
		];

		return '<input ' . _parse_form_attributes( $name, $defaults ) . $attr . " />\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_reset' ) )
{
	/**
	 * Reset Button
	 *
	 * @param    mixed
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function form_reset( $name = '', $value = '', $attr = '' )
	{
		$defaults = [
			'type'  => 'reset',
			'name'  => is_array( $name ) ? '' : $name,
			'value' => $value,
		];

		return '<input ' . _parse_form_attributes( $name, $defaults ) . $attr . " />\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_button' ) )
{
	/**
	 * Form Button
	 *
	 * @param    mixed
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function form_button( $name = '', $content = '', $attr = '' )
	{
		$defaults = [
			'name' => is_array( $name ) ? '' : $name,
			'type' => 'button',
		];

		if ( is_array( $name ) && isset( $name[ 'content' ] ) )
		{
			$content = $name[ 'content' ];
			unset( $name[ 'content' ] ); // content is not an attribute
		}

		return '<button ' . _parse_form_attributes( $name, $defaults ) . $attr . '>' . $content . "</button>\n";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_label' ) )
{
	/**
	 * Form Label Tag
	 *
	 * @param    string    The text to appear onscreen
	 * @param    string    The id the label applies to
	 * @param    string    Additional attributes
	 *
	 * @return    string
	 */
	function form_label( $label_text = '', $id = '', $attributes = [ ] )
	{

		$label = '<label';

		if ( $id !== '' )
		{
			$label .= ' for="' . $id . '"';
		}

		if ( is_array( $attributes ) && count( $attributes ) > 0 )
		{
			foreach ( $attributes as $key => $val )
			{
				$label .= ' ' . $key . '="' . $val . '"';
			}
		}

		return $label . '>' . $label_text . '</label>';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_fieldset' ) )
{
	/**
	 * Fieldset Tag
	 *
	 * Used to produce <fieldset><legend>text</legend>.  To close fieldset
	 * use form_fieldset_close()
	 *
	 * @param    string    The legend text
	 * @param    array     Additional attributes
	 *
	 * @return    string
	 */
	function form_fieldset( $legend_text = '', $attributes = [ ] )
	{
		$fieldset = '<fieldset' . _attributes_to_string( $attributes ) . ">\n";
		if ( $legend_text !== '' )
		{
			return $fieldset . '<legend>' . $legend_text . "</legend>\n";
		}

		return $fieldset;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_fieldset_close' ) )
{
	/**
	 * Fieldset Close Tag
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	function form_fieldset_close( $attr = '' )
	{
		return '</fieldset>' . $attr;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_close' ) )
{
	/**
	 * Form Close Tag
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	function form_close( $attr = '' )
	{
		return '</form>' . $attr;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_prep' ) )
{
	/**
	 * Form Prep
	 *
	 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
	 *
	 * @deprecated    3.0.0    An alias for html_escape()
	 *
	 * @param    string|string[] $str Value to escape
	 *
	 * @return    string|string[]    Escaped values
	 */
	function form_prep( $str )
	{
		return html_escape( $str, TRUE );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'set_value' ) )
{
	/**
	 * Form Value
	 *
	 * Grabs a value from the POST array for the specified field so you can
	 * re-populate an input field or textarea. If Form Validation
	 * is active it retrieves the info from the validation class
	 *
	 * @param    string $field   Field name
	 * @param    string $default Default value
	 *
	 * @return    string
	 */
	function set_value( $field, $default = '' )
	{
		if ( \O2System::Validation() instanceof \O2System\Glob\Helpers\Validation )
		{
			if ( \O2System::Validation()->has_rule( $field ) )
			{
				$value = \O2System::Validation()->set_value( $field, $default );
			}
			else
			{
				$value = \O2System::Input()->post( $field, FALSE );
			}
		}
		else
		{
			$value = \O2System::Input()->post( $field, FALSE );
		}

		return html_escape( $value === NULL ? $default : $value );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'set_select' ) )
{
	/**
	 * Set Select
	 *
	 * Let's you set the selected value of a <select> menu via data in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param    string
	 * @param    string
	 * @param    bool
	 *
	 * @return    string
	 */
	function set_select( $field, $value = '', $default = FALSE )
	{
		$system =& \O2System::instance();

		if ( isset( $system->form_validation ) && is_object( $system->form_validation ) && $system->form_validation->has_rule( $field ) )
		{
			return $system->form_validation->set_select( $field, $value, $default );
		}
		elseif ( ( $input = $system->input->post( $field, FALSE ) ) === NULL )
		{
			return ( $default === TRUE ) ? ' selected="selected"' : '';
		}

		$value = (string) $value;
		if ( is_array( $input ) )
		{
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ( $input as &$v )
			{
				if ( $value === $v )
				{
					return ' selected="selected"';
				}
			}

			return '';
		}

		return ( $input === $value ) ? ' selected="selected"' : '';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'set_checkbox' ) )
{
	/**
	 * Set Checkbox
	 *
	 * Let's you set the selected value of a checkbox via the value in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param    string
	 * @param    string
	 * @param    bool
	 *
	 * @return    string
	 */
	function set_checkbox( $field, $value = '', $default = FALSE )
	{
		$system =& \O2System::instance();

		if ( isset( $system->form_validation ) && is_object( $system->form_validation ) && $system->form_validation->has_rule( $field ) )
		{
			return $system->form_validation->set_checkbox( $field, $value, $default );
		}
		elseif ( ( $input = $system->input->post( $field, FALSE ) ) === NULL )
		{
			return ( $default === TRUE ) ? ' checked="checked"' : '';
		}

		$value = (string) $value;
		if ( is_array( $input ) )
		{
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ( $input as &$v )
			{
				if ( $value === $v )
				{
					return ' checked="checked"';
				}
			}

			return '';
		}

		return ( $input === $value ) ? ' checked="checked"' : '';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'set_radio' ) )
{
	/**
	 * Set Radio
	 *
	 * Let's you set the selected value of a radio field via info in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param    string $field
	 * @param    string $value
	 * @param    bool   $default
	 *
	 * @return    string
	 */
	function set_radio( $field, $value = '', $default = FALSE )
	{
		$system =& \O2System::instance();

		if ( isset( $system->form_validation ) && is_object( $system->form_validation ) && $system->form_validation->has_rule( $field ) )
		{
			return $system->form_validation->set_radio( $field, $value, $default );
		}
		elseif ( ( $input = $system->input->post( $field, FALSE ) ) === NULL )
		{
			return ( $default === TRUE ) ? ' checked="checked"' : '';
		}

		return ( $input === (string) $value ) ? ' checked="checked"' : '';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_error' ) )
{
	/**
	 * Form Error
	 *
	 * Returns the error for a specific form field. This is a helper for the
	 * form validation class.
	 *
	 * @param    string
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function form_error( $field = '', $prefix = '', $suffix = '' )
	{
		if ( FALSE === ( $OBJ =& _get_validation_object() ) )
		{
			return '';
		}

		return $OBJ->error( $field, $prefix, $suffix );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'validation_errors' ) )
{
	/**
	 * Validation Error String
	 *
	 * Returns all the errors associated with a form submission. This is a helper
	 * function for the form validation class.
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    string
	 */
	function validation_errors( $prefix = '', $suffix = '' )
	{
		if ( FALSE === ( $OBJ =& _get_validation_object() ) )
		{
			return '';
		}

		return $OBJ->error_string( $prefix, $suffix );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( '_parse_form_attributes' ) )
{
	/**
	 * Parse the form attributes
	 *
	 * Helper function used by some of the form helpers
	 *
	 * @param    array $attributes List of attributes
	 * @param    array $default    Default values
	 *
	 * @return    string
	 */
	function _parse_form_attributes( $attributes, $default )
	{
		if ( is_array( $attributes ) )
		{
			foreach ( $default as $key => $val )
			{
				if ( isset( $attributes[ $key ] ) )
				{
					$default[ $key ] = $attributes[ $key ];
					unset( $attributes[ $key ] );
				}
			}

			if ( count( $attributes ) > 0 )
			{
				$default = array_merge( $default, $attributes );
			}
		}

		$att = '';

		foreach ( $default as $key => $val )
		{
			if ( $key === 'value' )
			{
				$val = html_escape( $val );
			}
			elseif ( $key === 'name' && ! strlen( $default[ 'name' ] ) )
			{
				continue;
			}

			$att .= $key . '="' . $val . '" ';
		}

		return $att;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( '_attributes_to_string' ) )
{
	/**
	 * Attributes To String
	 *
	 * Helper function used by some of the form helpers
	 *
	 * @param    mixed
	 *
	 * @return    string
	 */
	function _attributes_to_string( $attributes )
	{
		if ( empty( $attributes ) )
		{
			return '';
		}

		if ( is_object( $attributes ) )
		{
			$attributes = (array) $attributes;
		}

		if ( is_array( $attributes ) )
		{
			$atts = '';

			foreach ( $attributes as $key => $val )
			{
				$atts .= ' ' . $key . '="' . $val . '"';
			}

			return $atts;
		}

		if ( is_string( $attributes ) )
		{
			return ' ' . $attributes;
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( '_get_validation_object' ) )
{
	/**
	 * Validation Object
	 *
	 * Determines what the form validation class was instantiated as, fetches
	 * the object and returns it.
	 *
	 * @return    mixed
	 */
	function &_get_validation_object()
	{
		$system =& \O2System::instance();

		// We set this as a variable since we're returning by reference.
		$return = FALSE;

		if ( FALSE !== ( $object = $system->load->is_loaded( 'Form_validation', 'string' ) ) )
		{
			if ( ! isset( $system->$object ) OR ! is_object( $system->$object ) )
			{
				return $return;
			}

			return $system->$object;
		}

		return $return;
	}
}


/**
 * Spinner Field
 *
 * Identical to the input function but adds the "number" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_spinner' ) )
{
	function form_spinner( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'data-min' => 0, 'data-max' => 300, 'data-step' => 1, 'class' => 'rounded-none spinner-input form-control', 'type' => 'text', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		$CI = \O2System::instance();
		$CI->load->helpers( [ 'html', 'array' ] );

		$name = array_combined_recursive( $defaults, $name );

		$spinner = element( 'spinner', $name, 'up-down-horizontal' );

		if ( $spinner == "up-down-vertical" )
		{
			$output = html( 'div', [ 'class' => 'spinner' ] );
			$output .= html( 'div', [ 'class' => 'input-group input-small', 'style' => 'width:150px;', 'data-trigger' => 'spinner' ] );
			$output .= "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
			$output .= html( 'div', [ 'class' => 'spinner-buttons input-group-btn btn-group-vertical' ] );
			$output .= html( 'button', [ 'data-spin' => 'up', 'type' => 'button', 'class' => 'btn spinner-up btn-xs btn-primary' ], '<i class="fa fa-angle-up"></i>' );
			$output .= html( 'button', [ 'data-spin' => 'down', 'type' => 'button', 'class' => 'btn spinner-up btn-xs btn-warning' ], '<i class="fa fa-angle-down"></i>' );
			$output .= html( '/div' );
			$output .= html( '/div' );
			$output .= html( '/div' );
		}
		elseif ( $spinner == 'up-down-horizontal' )
		{
			$output = html( 'div', [ 'class' => 'spinner' ] );
			$output .= html( 'div', [ 'class' => 'input-group input-small', 'style' => 'width:150px;', 'data-trigger' => 'spinner' ] );
			$output .= "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
			$output .= html( 'div', [ 'class' => 'spinner-buttons input-group-btn' ] );
			$output .= html( 'button', [ 'data-spin' => 'up', 'type' => 'button', 'class' => 'btn btn-primary spinner-up' ], '<i class="fa fa-angle-up"></i>' );
			$output .= html( 'button', [ 'data-spin' => 'down', 'type' => 'button', 'class' => 'btn btn-warning spinner-down' ], '<i class="fa fa-angle-down"></i>' );
			$output .= html( '/div' );
			$output .= html( '/div' );
			$output .= html( '/div' );
		}
		elseif ( $spinner == 'plus-min' )
		{
			$output = html( 'div', [ 'class' => 'spinner' ] );
			$output .= html( 'div', [ 'class' => 'input-group input-small', 'style' => 'width:150px;', 'data-trigger' => 'spinner' ] );
			$output .= html( 'div', [ 'class' => 'spinner-buttons input-group-btn' ] );
			$output .= html( 'button', [ 'data-spin' => 'up', 'type' => 'button', 'class' => 'btn spinner-up btn-primary' ], '<i class="fa fa-plus"></i>' );
			$output .= html( '/div' );
			$output .= "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
			$output .= html( 'div', [ 'class' => 'spinner-buttons input-group-btn' ] );
			$output .= html( 'button', [ 'data-spin' => 'down', 'type' => 'button', 'class' => 'btn spinner-down btn-warning' ], '<i class="fa fa-minus"></i>' );
			$output .= html( '/div' );
			$output .= html( '/div' );
			$output .= html( '/div' );
		}

		return $output;
	}
}

/**
 * Parse extras
 *
 * Takes an array of extras and spits them back in a string or just returns the string
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( '_parse_extras' ) )
{
	function _parse_extras( $attrs )
	{
		if ( ! is_array( $attrs ) )
		{
			return $attrs;
		}

		return ' ' . implode( ' ', $attrs );
	}
}

/**
 * AJAX Image Upload
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_image_ajax' ) )
{
	function form_image_ajax( $name = '', $value = '' )
	{
		$URL      = element( 'data-url', $name, applications_url( $name[ 'upload' ], 'url_suffix' ) );
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$output = html( 'div', [ 'id' => $name[ 'id' ], 'class' => 'fileinput fileinput-new', 'data-provider' => 'fileinput' ] );

		$preview_attr = [
			'id'    => $name[ 'id' ] . '-large',
			'class' => 'fileinput-preview thumbnail',
		];

		if ( $value )
		{
			$preview_attr[ 'href' ]  = image_url( 'large/' . $name[ 'src' ], $value );
			$preview_attr[ 'class' ] = $preview_attr[ 'class' ] . ' fancybox-image';
		}

		//print_out($name);

		$output .= html( 'a', $preview_attr );
		$output .= html( 'img', [ 'id' => $name[ 'id' ] . '-preview', 'src' => upload_url( $name[ 'thumb' ] . '/' . $name[ 'src' ], $value ) ], '/' );
		$output .= html( '/a' );
		$output .= html( 'div', [ 'class' => 'fileinput-group' ] );
		$output .= html( 'div', [ 'class' => 'form-control input-s', 'data-trigger' => 'fileinput' ] );
		$output .= html( 'i', [ 'class' => 'fa fa-file' ], '/i' );
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-filename', 'class' => 'fileinput-filename' ] );
		$output .= $value;
		$output .= html( '/span' );
		$output .= html( '/div' );
		$output .= html( 'div', [ 'class' => 'progress progress-xs' ] );
		$output .= html( 'div', [ 'id' => $name[ 'id' ] . '-bar', 'class' => 'progress-bar progress-bar-striped', 'role' => 'progressbar', 'style' => 'width:0%' ] );
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-status', 'class' => 'sr-only' ], '0% Complete' );
		$output .= html( '/div' );
		$output .= html( '/div' );
		$output .= html( 'div', [ 'class' => 'fileinput-buttons' ] );
		$output .= html( 'span', [ 'data-url' => $URL, 'class' => 'btn btn-primary fileinput-exists file-upload', 'rel' => $name[ 'id' ] ], '<i class="fa fa-upload"></i> ' . 'BTN_UPLOAD' );
		$output .= html( 'span', [ 'class' => 'btn btn-primary btn-file' ] );
		$output .= html( 'span', [ 'class' => 'fileinput-new' ], '<i class="fa fa-picture-o"></i> ' . 'BTN_SELECT_IMAGE' );
		$output .= html( 'span', [ 'class' => 'fileinput-change fileinput-exists' ], '<i class="fa fa-undo"></i> ' . 'BTN_CHANGE' );
		$output .= html( 'input', [ 'id' => $name[ 'id' ] . '-upload', 'type' => 'file', 'class' => 'fileinput-upload default', 'name' => 'userfile' ], '/' );
		$output .= html( 'input', [ 'id' => $name[ 'id' ] . '-input', 'type' => 'hidden', 'name' => $name[ 'name' ], 'value' => $value ], '/' );
		$output .= html( '/span' );
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-info', 'href' => '#modal-dialog', 'data-source' => upload_url( 'info/' . $name[ 'src' ], $value ), 'class' => 'fileinput-properties image-properties btn btn-info', 'data-toggle' => 'modal' ], '<i class="fa fa-file-text-o"></i> ' . 'BTN_PROPERTIES' );
		$output .= html( 'span', [ 'rel' => $name[ 'id' ], 'data-url' => upload_url( 'delete', '.html' ), 'href' => '#', 'class' => 'fileinput-remove btn btn-danger' ], '<i class="fa fa-ban"></i> ' . 'BTN_REMOVE' );
		$output .= html( '/div' );
		$output .= html( '/div' );
		$output .= html( '/div' );

		return $output;
	}
}


/**
 * Input Name
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_input_name' ) )
{
	function form_input_name( $name, $value = '', $attr = '' )
	{
		$fields      = element( 'fields', $name, [ 'first_name', 'middle_name', 'last_name' ] );
		$field_name  = element( 'name', $name, 'person' );
		$field_id    = element( 'id', $name, $field_name );
		$field_class = element( 'class', $name, 'input form-control' );

		$name = [ ];

		if ( ! is_array( $value ) AND ! empty( $value ) )
		{
			$value = explode( ' ', $value );

			if ( count( $value ) == 1 )
			{
				$name[ 'first_name' ]  = $value[ 0 ];
				$name[ 'middle_name' ] = '';
				$name[ 'last_name' ]   = '';
			}
			elseif ( count( $value ) == 2 )
			{
				$name[ 'first_name' ]  = $value[ 0 ];
				$name[ 'middle_name' ] = $value[ 1 ];
				$name[ 'last_name' ]   = '';
			}
			elseif ( count( $value ) > 2 )
			{
				$name[ 'first_name' ]  = $value[ 0 ];
				$name[ 'middle_name' ] = $value[ 1 ];

				$value = array_slice( $value, 2 );

				$name[ 'last_name' ] = implode( ' ', $value );
			}
		}
		else
		{
			foreach ( $value as $key => $val )
			{
				$name[ str_replace( '-', '_', $key ) ] = $val;
			}
		}

		unset( $name[ 'fields' ], $name[ 'id' ], $name[ 'name' ], $name[ 'class' ] );

		$output = '';
		foreach ( $fields as $i => $field )
		{
			$output .= html( 'div', [ 'class' => 'col-md-4' . ( $i == 0 ? ' clear-padding-left' : '' ) ] );

			$placeholder = str_replace( '-', '_', strtoupper( $field ) );
			$placeholder = ( $placeholder == '' ? str_capitalize( str_readable( $field ) ) : $placeholder );

			$attr = [
				'id'          => $field_id . '-' . $field,
				'type'        => 'text',
				'name'        => $field,
				'class'       => $field_class,
				'value'       => $name[ $field ],
				'placeholder' => $placeholder,
			];

			$attr = array_merge( $name, $attr );

			$output .= form_input( $attr );

			$output .= html( '/div' );
		}

		$output .= '<div class="clearfix"></div>';

		return $output;
	}
}


/**
 * Input Language
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_textarea_language' ) )
{
	function form_textarea_language( $name = '', $value = '', $attr = '' )
	{
		$CI =& get_instance();
		$CI->load->model( 'system_model' );
		$language = $CI->system_model->language();

		$value = ( is_serialized( $value ) ? unserialize( $value ) : $value );

		$output = html( 'ul', [ 'class' => 'nav nav-tabs bordered clear-margin-top' ] );
		$i      = 0;
		foreach ( $language as $lang )
		{
			$i++;
			$output .= html( 'li', [ 'class' => ( $i == 1 ? 'active align-center' : 'align-center' ) ] );
			$output .= html( 'a', [ 'data-toggle' => 'tab', 'href' => '#' . $name[ 'id' ] . '-content-textarea-' . $lang->code ], $lang->name );
			$output .= html( '/li' );
		}
		$output .= html( '/ul' );

		$output .= html( 'div', [ 'class' => 'tab-content' ] );
		$i = 0;
		foreach ( $language as $lang )
		{
			$i++;
			$output .= html( 'div', [ 'id' => '#' . $name[ 'id' ] . '-content-textarea-' . $lang->code, 'class' => 'tab-pane ' . ( $i == 1 ? 'active' : '' ) ] );
			$class = $name[ 'class' ] . 'scrollable input form-control';
			$output .= html( 'textarea', [ 'id' => $name[ 'id' ] . '-content-textarea-' . $lang->code, 'class' => $class, 'name' => $name[ 'name' ] . '[' . $lang->code . ']', 'row' => 6 ] ) . element( $lang->code, $value ) . html( '/textarea' );
			$output .= html( '/div' );
		}
		$output .= html( '/div' );

		return $output;
	}
}

// ------------------------------------------------------------------------
/**
 * Email Field
 *
 * Identical to the input function but adds the "email" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_email' ) )
{
	function form_email( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'email', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Telephone Field
 *
 * Identical to the input function but adds the "tel" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_telephone' ) )
{
	function form_telephone( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'tel', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * URL Field
 *
 * Identical to the input function but adds the "url" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_url' ) )
{
	function form_url( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'url', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Number Field
 *
 * Identical to the input function but adds the "number" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_number' ) )
{
	function form_number( $name = '', $value = '', array $attr = [ ] )
	{
		$attr[ 'type' ] = 'number';

		return form_input( $name, $value, $attr );
	}
}

// ------------------------------------------------------------------------

/**
 * Number Field
 *
 * Identical to the input function but adds the "number" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_size' ) )
{
	function form_size( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'number', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		$fields = explode( 'x', element( 'data-fields', $name, 'width x height' ) );
		$width  = ( count( $fields ) * 75 );

		if ( element( 'data-unit', $name ) )
		{
			$width = $width + ( strlen( $name[ 'data-unit' ] ) * 50 );
		}

		$output = html( 'div', [ 'class' => 'input-group', 'style' => 'width:' . $width . 'px;' ] );

		$i = 1;
		foreach ( $fields as $field )
		{
			$i++;
			$field = trim( $field );
			$class = ( $i == count( $fields ) ? 'input form-control' : 'input form-control rounded-none' );
			$output .= html( 'input', [ 'type' => 'text', 'class' => $class, 'name' => $name[ 'name' ] . '[' . $field . ']', 'value' => element( $field, $value ) ] ) . html( 'span', [ 'class' => 'input-group-addon', 'style' => 'background-color:#ebebeb; color:#fff;' ], element( 'data-unit', $name ) );
			$output .= ( $i <= count( $fields ) ? html( 'span', [ 'class' => 'input-group-addon rounded-none' ], 'x' ) : '' );
		}
		$output .= html( '/div' );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Spinner Field
 *
 * Identical to the input function but adds the "number" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_spinner' ) )
{
	function form_spinner( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'data-min' => 0, 'data-max' => 300, 'data-step' => 1, 'class' => 'rounded-none spinner-input form-control', 'type' => 'text', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		$CI =& get_instance();
		$CI->load->helper( [ 'html', 'array' ] );

		$name = array_combined( $defaults, $name );

		$spinner = element( 'spinner', $name, 'up-down-horizontal' );

		if ( $spinner == "up-down-vertical" )
		{
			$output = html( 'div', [ 'class' => 'spinner' ] );
			$output .= html( 'div', [ 'class' => 'input-group input-small', 'style' => 'width:150px;', 'data-trigger' => 'spinner' ] );
			$output .= "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
			$output .= html( 'div', [ 'class' => 'spinner-buttons input-group-btn btn-group-vertical' ] );
			$output .= html( 'button', [ 'data-spin' => 'up', 'type' => 'button', 'class' => 'btn spinner-up btn-xs btn-primary' ], '<i class="fa fa-angle-up"></i>' );
			$output .= html( 'button', [ 'data-spin' => 'down', 'type' => 'button', 'class' => 'btn spinner-up btn-xs btn-warning' ], '<i class="fa fa-angle-down"></i>' );
			$output .= html( '/div' );
			$output .= html( '/div' );
			$output .= html( '/div' );
		}
		elseif ( $spinner == 'up-down-horizontal' )
		{
			$output = html( 'div', [ 'class' => 'spinner' ] );
			$output .= html( 'div', [ 'class' => 'input-group input-small', 'style' => 'width:150px;', 'data-trigger' => 'spinner' ] );
			$output .= "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
			$output .= html( 'div', [ 'class' => 'spinner-buttons input-group-btn' ] );
			$output .= html( 'button', [ 'data-spin' => 'up', 'type' => 'button', 'class' => 'btn btn-primary spinner-up' ], '<i class="fa fa-angle-up"></i>' );
			$output .= html( 'button', [ 'data-spin' => 'down', 'type' => 'button', 'class' => 'btn btn-warning spinner-down' ], '<i class="fa fa-angle-down"></i>' );
			$output .= html( '/div' );
			$output .= html( '/div' );
			$output .= html( '/div' );
		}
		elseif ( $spinner == 'plus-min' )
		{
			$output = html( 'div', [ 'class' => 'spinner' ] );
			$output .= html( 'div', [ 'class' => 'input-group input-small', 'style' => 'width:150px;', 'data-trigger' => 'spinner' ] );
			$output .= html( 'div', [ 'class' => 'spinner-buttons input-group-btn' ] );
			$output .= html( 'button', [ 'data-spin' => 'up', 'type' => 'button', 'class' => 'btn spinner-up btn-primary' ], '<i class="fa fa-plus"></i>' );
			$output .= html( '/div' );
			$output .= "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
			$output .= html( 'div', [ 'class' => 'spinner-buttons input-group-btn' ] );
			$output .= html( 'button', [ 'data-spin' => 'down', 'type' => 'button', 'class' => 'btn spinner-down btn-warning' ], '<i class="fa fa-minus"></i>' );
			$output .= html( '/div' );
			$output .= html( '/div' );
			$output .= html( '/div' );
		}

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Range Field
 *
 * Identical to the input function but adds the "range" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_range' ) )
{
	function form_range( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'range', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_date' ) )
{
	function form_date( $name = '', $value = '', array $attr = [ ] )
	{
		$attr[ 'type' ] = 'date';

		return form_input( $name, $value, $attr );
	}
}

if ( ! function_exists( 'form_date_time' ) )
{
	function form_date_time( $name, $value )
	{
		$output = html( 'div', [ 'class' => 'date-and-time', 'style' => 'width:225px' ] );
		$date   = element( $name[ 'name' ] . '-date', $value );
		$date   = ( is_numeric( $date ) ? date( 'd-m-Y', $date ) : $date );

		$time = element( $name[ 'name' ] . '-time', $value );

		$output .= html(
			'input', [
			'type'        => 'text',
			'name'        => $name[ 'name' ] . '-date',
			'class'       => 'form-control datepicker ' . element( 'class', $name ),
			'data-format' => 'dd-mm-yyyy',
			'value'       => $date,
		], '/' );
		$output .= html(
			'input', [
			'type'               => 'text',
			'class'              => 'rounded-left form-control timepicker ' . element( 'class', $name ),
			'name'               => $name[ 'name' ] . '-time',
			'data-template'      => 'dropdown',
			'data-show-seconds'  => element( 'show-seconds', $name ),
			'data-show-meridian' => element( 'show-meridian', $name ),
			'data-minute-step'   => element( 'minute-step', $name, 5 ),
			'value'              => ( element( 'show-seconds', $name ) ? $time : substr( $time, 0, -3 ) ),
			'style'              => 'margin-top:1px',
		], '/' );
		$output .= html( '/div' );

		return $output;
	}
}

if ( ! function_exists( 'form_date_range' ) )
{
	function form_date_range( $name, $value )
	{
		$attr[ 'type' ]            = 'text';
		$attr[ 'name' ]            = element( 'name', $name, 'start-date:end-date' );
		$attr[ 'class' ]           = 'form-control daterange ' . element( 'class', $name );
		$attr[ 'data-format' ]     = 'DD-MM-YYYY';
		$attr[ 'data-separator' ]  = ' &mdash; ';
		$attr[ 'data-start-date' ] = date( 'd-m-Y', now() );
		$attr[ 'data-end-date' ]   = date( 'd-m-Y', now() );
		$attr[ 'value' ]           = $attr[ 'data-start-date' ] . $attr[ 'data-separator' ] . $attr[ 'data-end-date' ];

		if ( ! empty( $value ) )
		{
			if ( is_array( $value ) )
			{
				foreach ( $value as $field => $date )
				{
					$attr[ 'value' ][] = date( 'd-m-Y', strtotime( $date ) );
				}

				$attr[ 'value' ]           = implode( ' &mdash; ', $attr[ 'value' ] );
				$attr[ 'data-start-date' ] = date( 'd-m-Y', strtotime( @$value[ 0 ] ) );
				$attr[ 'data-end-date' ]   = date( 'd-m-Y', strtotime( @$value[ 1 ] ) );
			}
			else
			{
				$attr[ 'value' ] = $value;

				$x_date = explode( '&mdash;', $value );

				$attr[ 'data-start-date' ] = trim( @$x_date[ 0 ] );
				$attr[ 'data-end-date' ]   = trim( @$x_date[ 1 ] );
			}
		}

		return html( 'input', $attr );
	}
}

if ( ! function_exists( 'form_date_time_range' ) )
{
	function form_date_time_range( $name, $value )
	{
		$attr[ 'type' ]                       = 'text';
		$attr[ 'name' ]                       = 'start-date:end-date';
		$attr[ 'class' ]                      = 'form-control daterange ' . element( 'class', $name );
		$attr[ 'data-time-picker' ]           = 'true';
		$attr[ 'data-time-picker-increment' ] = element( 'increment', $name, 5 );
		$attr[ 'data-format' ]                = 'DD-MM-YYYY h:mm A';
		$attr[ 'value' ]                      = '';

		if ( ! empty( $value ) )
		{
			foreach ( $value as $field => $name )
			{
				$attr[ 'value' ][] = date( 'd-M-Y h:m a' );
			}

			$attr[ 'value' ] = implode( ' - ', $attr[ 'value' ] );
		}

		return html( 'input', $attr );
	}
}

/**
 * Time Field
 *
 * Identical to the input function but adds the "email" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_time' ) )
{
	function form_time( $name = '', $value = '', $attr = '' )
	{
		$minute_step = element( 'minute-step', $name, 5 );
		$output      = html( 'div', [ 'class' => 'input-group', 'style' => 'width:150px;' ] );
		$output .= html(
			'input', [
			'type'               => 'text',
			'class'              => 'rounded-left form-control timepicker',
			'name'               => $name[ 'name' ],
			'data-template'      => 'dropdown',
			'data-show-seconds'  => element( 'show-seconds', $name ),
			'data-default-time'  => element( 'default-time', $name, date( 'h:m a' ) ),
			'data-show-meridian' => element( 'show-meridian', $name ),
			'data-minute-step'   => element( 'minute-step', $name, 5 ),
			'value'              => $value,
		], '/' );
		$output .= html( 'span', [ 'class' => 'input-group-addon rounded-none btn-primary' ], '<i class="entypo-clock"></i>' );
		$output .= html( '/div' );

		return $output;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_time_range' ) )
{
	function form_time_range( $name = '', $value = '', $attr = '' )
	{
		$output = html( 'div', [ 'class' => 'input-group', 'style' => 'width:250px;' ] );
		$output .= html(
			'input', [
			'type'               => 'text',
			'class'              => 'rounded-left form-control timepicker',
			'name'               => 'start-time',
			'data-template'      => 'dropdown',
			'data-show-seconds'  => element( 'show-seconds', $name ),
			'data-default-time'  => element( 'default-time', $name, date( 'h:m a' ) ),
			'data-show-meridian' => element( 'show-meridian', $name ),
			'data-minute-step'   => element( 'minute-step', $name, 5 ),
			'value'              => element( 'start-time', $value ),
		], '/' );
		$output .= html( 'span', [ 'class' => 'input-group-addon rounded-none btn-primary' ], '<i class="entypo-clock"></i>' );
		$output .= html(
			'input', [
			'type'               => 'text',
			'class'              => 'rounded-left form-control timepicker',
			'name'               => 'end-time',
			'data-template'      => 'dropdown',
			'data-show-seconds'  => element( 'show-seconds', $name ),
			'data-default-time'  => element( 'default-time', $name, date( 'h:m a' ) ),
			'data-show-meridian' => element( 'show-meridian', $name ),
			'data-minute-step'   => element( 'minute-step', $name, 5 ),
			'value'              => elment( 'end-time', $value ),
		], '/' );
		$output .= html( '/div' );

		return $output;
	}
}

/**
 * Month Field
 *
 * Identical to the input function but adds the "month" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_month' ) )
{
	function form_month( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'month', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Week Field
 *
 * Identical to the input function but adds the "week" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_week' ) )
{
	function form_week( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'week', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Time Field
 *
 * Identical to the input function but adds the "time" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_time' ) )
{
	function form_time( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'time', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Datetime Field
 *
 * Identical to the input function but adds the "datetime" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_datetime' ) )
{
	function form_datetime( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'datetime', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Datetime Local Field
 *
 * Identical to the input function but adds the "datetime-local" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_datetime_local' ) )
{
	function form_datetime_local( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'datetime-local', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Search Field
 *
 * Identical to the input function but adds the "search" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_search' ) )
{
	function form_search( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'search', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Color Field
 *
 * Identical to the input function but adds the "color" type
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_color' ) )
{
	function form_color( $name = '', $value = '', $attr = '' )
	{
		$defaults = [ 'type' => 'color', 'name' => ( ( ! is_array( $name ) ) ? $name : '' ), 'value' => $value ];

		return "<input " . _parse_form_attributes( $name, $defaults ) . _parse_extras( $attr ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Data List
 *
 * Generates a data list
 *
 * @access  public
 *
 * @param   mixed
 * @param   array
 * @param   bool
 *
 * @return  string
 */
if ( ! function_exists( 'form_datalist' ) )
{
	function form_datalist( $name = '', $values = [ ], $use_label = FALSE )
	{
		$defaults = [ 'id' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$html = "<datalist " . _parse_form_attributes( $name, $defaults ) . ">";

		foreach ( $values as $label => $value )
		{
			$html .= '<option value="' . $value . '"';

			if ( $use_label )
			{
				$html .= 'label="' . $label . '"';
			}

			$html .= '></option>';
		}

		return $html .= "</datalist>";
	}
}

// ------------------------------------------------------------------------

/**
 * Keygen
 *
 * Generates <keygen> element
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_keygen' ) )
{
	function form_keygen( $name = '' )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		return "<keygen " . _parse_form_attributes( $name, $defaults ) . " />";
	}
}

// ------------------------------------------------------------------------

/**
 * Output
 *
 * Generates <output></output> element
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_output' ) )
{
	function form_output( $name = '' )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		return "<output " . _parse_form_attributes( $name, $defaults ) . "></output>";
	}
}

// ------------------------------------------------------------------------

/**
 * Output
 *
 * Generates <output></output> element
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_checkbox_multiple' ) )
{
	function form_checkbox_multiple( $name = '', $value, $options = [ ] )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$output = '';

		$class = element( 'class', $name, 'square-blue' );

		$output .= html( 'ul', [ 'class' => 'split-list', 'split' => element( 'split', $name, 3 ) ] );
		foreach ( $options as $option_label => $option_value )
		{
			$output .= html( 'li' );
			$attr = [ 'type' => 'checkbox', 'class' => $class, 'value' => $option_value, 'name' => $name[ 'name' ] . '[]' ];

			if ( $option_value == $value )
			{
				array_push( $attr, [ 'checked' => 'checked' ] );
			}

			$output .= html( 'div', [ 'class' => 'checkbox checkbox-replace' ] );
			$output .= html( 'input', $attr, '/' );
			$output .= html( 'label', [ 'class' => 'control-label icheck-inline-label' ], $option_label );
			$output .= html( '/div' );
			$output .= html( '/li' );
		}
		$output .= html( '/ul' );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Output
 *
 * Generates <output></output> element
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_checkbox_tree' ) )
{
	function form_checkbox_tree( $name = '', $value = [ ], $options = [ ], $is_child = FALSE )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$output = '';

		if ( $is_child == FALSE )
		{
			$output .= html( 'div', [ 'class' => 'btn-group treeview-control' ] );
			$output .= html( 'a', [ 'title' => '', 'href' => '#', 'class' => 'btn btn-primary btn-icon icon-left' ], '<i class="entypo-plus"></i> Expand All' );
			$output .= html( 'a', [ 'title' => '', 'href' => '#', 'class' => 'btn btn-info btn-icon icon-left' ], '<i class="entypo-minus"></i> Collapse All' );
			$output .= html( '/div' );
		}
		$output .= html( 'ul', [ 'class' => ( $is_child == FALSE ? 'treeview' : '' ) ] );
		foreach ( $options as $option_label => $option_value )
		{
			$output .= html( 'li' );
			if ( is_string( $option_value ) )
			{
				//$option_name = $name['name'].'['.alias($option_label).']';
				$option_name    = $name[ 'name' ] . '[]';
				$option_checked = ( in_array( $option_value, $value ) ? TRUE : FALSE );

				$output .= html( 'div', [ 'class' => 'checkbox checkbox-replace', 'style' => 'position:relative; top:-5px; left:2px;' ] );
				$output .= form_checkbox( [ 'name' => $option_name, 'value' => $option_value, 'checked' => $option_checked ] );
				$output .= html( 'label', [ 'class' => 'control-label' ], $option_label );
				$output .= html( '/div' );
			}
			elseif ( is_array( $option_value ) )
			{
				if ( isset( $option_value[ 0 ] ) )
				{
					//$option_name = $name['name'].'['.alias($option_label).'][0]';
					$option_name    = $name[ 'name' ] . '[]';
					$option_checked = ( in_array( $option_value[ 0 ], $value ) ? TRUE : FALSE );

					$output .= html( 'div', [ 'class' => 'checkbox checkbox-replace', 'style' => 'position:relative; top:-5px; left:2px;' ] );
					$output .= form_checkbox( [ 'name' => $option_name, 'value' => $option_value[ 0 ], 'checked' => $option_checked ] );
					$output .= html( 'label', [ 'class' => 'control-label' ], $option_label );
					$output .= html( '/div' );
					unset( $option_value[ 0 ] );
				}

				//$option_name = $name['name'].'['.alias($option_label).']';
				$option_name = $name[ 'name' ] . '[]';
				$output .= form_checkbox_tree( [ 'name' => $option_name ], $value, $option_value, TRUE );
			}
			$output .= html( '/li' );
		}
		$output .= html( '/ul' );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Output
 *
 * Generates <output></output> element
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_radio_tree' ) )
{
	function form_radio_tree( $name = '', $value, $options = [ ], $child = FALSE )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$output = '';

		if ( $child == FALSE )
		{
			$output .= html( 'div', [ 'class' => 'btn-group btn-group-sm treeview-control' ] );
			$output .= html( 'a', [ 'title' => '', 'href' => '#', 'class' => 'btn btn-primary btn-icon icon-left' ], '<i class="fa fa-minus"></i> Collapse All' );
			$output .= html( 'a', [ 'title' => '', 'href' => '#', 'class' => 'btn btn-info btn-icon icon-left' ], '<i class="fa fa-plus"></i> Expand All' );
			$output .= html( '/div' );
		}

		$output .= html( 'ul', [ 'class' => ( $child == FALSE ? 'treeview' : '' ) ] );
		foreach ( $options as $option_label => $option_value )
		{
			$output .= html( 'li' );
			if ( is_string( $option_value ) )
			{
				$option_name = $name[ 'name' ] . '[' . alias( $option_label ) . ']';

				$output .= html( 'div', [ 'class' => 'radio radio-replace', 'style' => 'position: relative; top: -5px; left: 3px;' ] );
				$output .= form_radio( [ 'name' => $option_name, 'class' => 'minimal-blue', 'value' => $option_value ] );
				$output .= html( 'label', [ 'class' => 'control-label' ], $option_label );
				$output .= html( '/div' );
			}
			elseif ( is_array( $option_value ) )
			{
				if ( isset( $option_value[ 0 ] ) )
				{
					$option_name = $name[ 'name' ] . '[' . alias( $option_label ) . '][0]';
					$output .= html( 'div', [ 'class' => 'radio radio-replace', 'style' => 'position: relative; top: -5px; left: 3px;' ] );
					$output .= form_radio( [ 'name' => $option_name, 'class' => 'minimal-blue', 'value' => $option_value[ 0 ] ] );
					$output .= html( 'label', [ 'class' => 'control-label' ], $option_label );
					$output .= html( '/div' );
					unset( $option_value[ 0 ] );
				}

				$option_name = $name[ 'name' ] . '[' . alias( $option_label ) . ']';
				$output .= form_radio_tree( [ 'name' => $option_name ], $value, $option_value, TRUE );
			}
			$output .= html( '/li' );
		}
		$output .= html( '/ul' );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Multiple Radio
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_radio_multiple' ) )
{
	function form_radio_multiple( $name = '', $value, $options = [ ] )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$output = '';

		$class = element( 'class', $name, 'square-blue' );

		$output .= html( 'ul', [ 'class' => 'split-list', 'split' => element( 'split', $name, 3 ) ] );
		foreach ( $options as $option_label => $option_value )
		{
			$output .= html( 'li' );
			$attr = [ 'type' => 'radio', 'class' => $class, 'value' => $option_value, 'name' => $name[ 'name' ] ];

			if ( $option_value == $value )
			{
				$attr = array_merge( $attr, [ 'checked' => 'checked' ] );
			}

			$output .= html( 'div', [ 'class' => 'radio radio-replace' ] );
			$output .= html( 'input', $attr, '/' );
			$output .= html( 'label', [ 'class' => 'label-icheck control-label' ], $option_label );
			$output .= html( '/div' );
			$output .= html( '/li' );
		}
		$output .= html( '/ul' );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Advanced File Upload
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_file_ajax' ) )
{
	function form_file_ajax( $name = '', $value = '' )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$output = html( 'div', [ 'class' => 'fileinput fileinput-new', 'data-provider' => 'fileinput' ] );
		$output .= html( 'div', [ 'class' => 'input-group' ] );
		$output .= html( 'div', [ 'class' => 'form-control', 'data-trigger' => 'fileinput' ] );
		$output .= html( 'i', [ 'class' => 'fa fa-file fileinput-exists' ], '/i' );
		$output .= html( 'span', [ 'class' => 'fileinput-filename', 'style' => 'padding-left:10px;' ], '/span' );
		$output .= html( '/div' );
		$output .= html( 'span', [ 'class' => 'input-group-addon btn btn-primary btn-file' ] );
		$output .= html( 'span', [ 'class' => 'fileinput-new' ], '<i class="fa fa-paperclip"></i> Select file' );
		$output .= html( 'span', [ 'class' => 'fileinput-exists' ], '<i class="fa fa-undo"></i> Change' );
		$output .= html( 'input', [ 'type' => 'file', 'class' => 'default', 'name' => $name[ 'name' ] ], '/' );
		$output .= html( '/span' );
		$output .= html( 'a', [ 'href' => '#', 'class' => 'input-group-addon btn btn-danger fileinput-exists', 'data-dismiss' => 'fileinput' ], '<i class="fa fa-ban"></i> Remove' );
		$output .= html( '/div' );
		$output .= html( '/div' );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * AJAX Image Upload
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_image_ajax' ) )
{
	function form_image_ajax( $name = '', $value = '' )
	{
		$URL      = element( 'data-url', $name, applications_url( $name[ 'upload' ], 'url_suffix' ) );
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$output = html( 'div', [ 'id' => $name[ 'id' ], 'class' => 'fileinput fileinput-new', 'data-provider' => 'fileinput' ] );

		$preview_attr = [
			'id'    => $name[ 'id' ] . '-large',
			'class' => 'fileinput-preview thumbnail',
		];

		if ( $value )
		{
			$preview_attr[ 'href' ]  = image_url( 'large/' . $name[ 'src' ], $value );
			$preview_attr[ 'class' ] = $preview_attr[ 'class' ] . ' fancybox-image';
		}

		//print_out($name);

		$output .= html( 'a', $preview_attr );
		$output .= html( 'img', [ 'id' => $name[ 'id' ] . '-preview', 'src' => image_url( $name[ 'thumb' ] . '/' . $name[ 'src' ], $value ) ], '/' );
		$output .= html( '/a' );
		$output .= html( 'div', [ 'class' => 'fileinput-group' ] );
		$output .= html( 'div', [ 'class' => 'form-control input-s', 'data-trigger' => 'fileinput' ] );
		$output .= html( 'i', [ 'class' => 'fa fa-file' ], '/i' );
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-filename', 'class' => 'fileinput-filename' ] );
		$output .= $value;
		$output .= html( '/span' );
		$output .= html( '/div' );
		$output .= html( 'div', [ 'class' => 'progress progress-xs' ] );
		$output .= html( 'div', [ 'id' => $name[ 'id' ] . '-bar', 'class' => 'progress-bar progress-bar-striped', 'role' => 'progressbar', 'style' => 'width:0%' ] );
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-status', 'class' => 'sr-only' ], '0% Complete' );
		$output .= html( '/div' );
		$output .= html( '/div' );
		$output .= html( 'div', [ 'class' => 'fileinput-buttons' ] );
		$output .= html( 'span', [ 'data-url' => $URL, 'class' => 'btn btn-primary fileinput-exists file-upload', 'rel' => $name[ 'id' ] ], '<i class="fa fa-upload"></i> ' . lang( 'BTN_UPLOAD' ) );
		$output .= html( 'span', [ 'class' => 'btn btn-primary btn-file' ] );
		$output .= html( 'span', [ 'class' => 'fileinput-new' ], '<i class="fa fa-picture-o"></i> ' . lang( 'BTN_SELECT_IMAGE' ) );
		$output .= html( 'span', [ 'class' => 'fileinput-change fileinput-exists' ], '<i class="fa fa-undo"></i> ' . lang( 'BTN_CHANGE' ) );
		$output .= html( 'input', [ 'id' => $name[ 'id' ] . '-upload', 'type' => 'file', 'class' => 'fileinput-upload default', 'name' => 'userfile' ], '/' );
		$output .= html( 'input', [ 'id' => $name[ 'id' ] . '-input', 'type' => 'hidden', 'name' => $name[ 'name' ], 'value' => $value ], '/' );
		$output .= html( '/span' );
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-info', 'href' => '#modal-dialog', 'data-source' => image_url( 'info/' . $name[ 'src' ], $value ), 'class' => 'fileinput-properties image-properties btn btn-info', 'data-toggle' => 'modal' ], '<i class="fa fa-file-text-o"></i> ' . lang( 'BTN_PROPERTIES' ) );
		$output .= html( 'span', [ 'rel' => $name[ 'id' ], 'data-url' => image_url( 'delete', '.html' ), 'href' => '#', 'class' => 'fileinput-remove btn btn-danger' ], '<i class="fa fa-ban"></i> ' . lang( 'BTN_REMOVE' ) );
		$output .= html( '/div' );
		$output .= html( '/div' );
		$output .= html( '/div' );

		return $output;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'form_images_browser' ) )
{
	function form_images_browser( $name = '', $value = '' )
	{
		$filename = ( $value == '' ? '/span' : pathinfo( $value, PATHINFO_BASENAME ) );

		$size = explode( 'x', $name[ 'size' ] );
		$size = [
			'width'  => $size[ 0 ],
			'height' => $size[ 1 ],
		];

		$output = html( 'div', [ 'id' => $name[ 'id' ], 'class' => 'gallery-env', 'data-form' => $name[ 'data-form' ] ] );

		$output .= html( 'div', [ 'class' => 'col-sm-2 col-xs-4 image-container' ] );
		$output .= html( 'article', [ 'class' => 'image-thumb', 'data-name' => $filename, 'data-path' => $value, 'style' => 'background-size: ' . ( $size[ 'width' ] - 20 ) . 'px ' . ( $size[ 'height' ] - 20 ) . 'px; background-image: url(' . app_url( 'images/thumbnail/' . $name[ 'size' ], '/no-image.png' ) . ');' ] );
		$output .= html( 'a', [ 'href' => app_url( 'images/large', '/' ) . $value, 'class' => 'image fancybox', 'action' => 'zoom' ] );
		$output .= img( app_url( 'images/thumbnail/' . $name[ 'size' ], '/' ) . $value );
		$output .= html( '/a' );
		$output .= html( '/article' );
		$output .= html( '/div' );

		$output .= html( 'div', [ 'class' => 'col-sm-8' ] );
		$output .= html( 'div', [ 'class' => 'input-group' ] );
		$output .= html( 'span', [ 'class' => 'input-group-addon' ], '<i class="entypo-picture"></i>' );
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-input', 'class' => 'filename form-control', 'readonly' => TRUE ], $filename );
		$output .= html( '/div' );

		$output .= html( 'input', [ 'type' => 'hidden', 'id' => $name[ 'id' ] . '-input', 'class' => 'form-control', 'name' => $name[ 'name' ], 'value' => $value ] );

		$output .= html( 'button', [ 'type' => 'button', 'href' => app_url( 'plugins/media/image/pop-up', 'url_suffix' ), 'class' => 'btn btn-primary btn-icon icon-left margin-top-md', 'data-fancybox-type' => 'iframe', 'action' => 'browser-images', 'data-target' => $name[ 'id' ] ], '<i class="entypo-search"></i> Browse' );
		$output .= html( 'button', [ 'type' => 'button', 'href' => app_url( 'images/info', '/' ), 'class' => ( $value == 'no-image.png' ? 'hidden ' : '' ) . 'btn btn-info btn-icon icon-left margin-top-md margin-left-sm', 'data-target' => $name[ 'id' ], 'action' => 'info-image' ], '<i class="entypo-doc-text"></i> Properties' );
		$output .= html( 'button', [ 'type' => 'button', 'class' => 'btn btn-danger btn-icon icon-left margin-top-md margin-left-sm', 'data-target' => $name[ 'id' ], 'action' => 'remove-image' ], '<i class="entypo-trash"></i> Remove' );
		$output .= html( '/div' );

		$output .= html( 'div', [ 'class' => 'clearfix' ], '/div' );
		$output .= html( '/div' );

		return $output;
	}
}

/**
 * Advanced Video Upload
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_upload_video' ) )
{
	function form_upload_video( $name = '', $value = '' )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$id = element( 'id', $name );

		$output = html( 'div', [ 'class' => 'fileinput fileinput-new', 'data-provider' => 'fileinput' ] );
		$output .= html( 'div', [ 'class' => 'input-group' ] );
		$output .= html( 'span', [ 'class' => 'input-group-addon btn-white' ] );
		$output .= html( 'i', [ 'id' => $id . '-icon', 'class' => 'fa fa-youtube-play' ], '/i' );
		$output .= html( '/span' );
		$output .= html( 'input', [ 'id' => $id . '-url-field', 'type' => 'text', 'class' => 'hidden form-control', 'name' => $name[ 'name' ] . '[url]', 'value' => element( 'url', $value ) ], '/' );
		$output .= html( 'div', [ 'id' => $id . '-localhost-trigger', 'class' => 'form-control', 'data-trigger' => 'fileinput' ] );
		//$output.= html('i',array('class' => 'fa fa-file fileinput-exists'),'/i');
		$output .= html( 'span', [ 'class' => 'fileinput-filename', 'style' => 'padding-left:10px;' ] );
		$output .= element( 'file', $value );
		$output .= html( '/span' );
		$output .= html( '/div' );
		$output .= html( 'span', [ 'id' => $id . '-localhost-field', 'class' => 'input-group-addon btn btn-primary btn-file rounded-none' ] );
		$output .= html( 'span', [ 'class' => 'fileinput-new' ], '<i class="fa fa-paperclip"></i> ' . lang( 'BTN_SELECT_VIDEO' ) );
		$output .= html( 'span', [ 'class' => 'fileinput-exists' ], '<i class="fa fa-undo"></i> ' . lang( 'BTN_CHANGE' ) );
		$output .= html( 'input', [ 'id' => $id . '-field', 'type' => 'file', 'class' => 'default', 'name' => $name[ 'name' ] . '[file]', 'value' => element( 'file', $value ) ], '/' );
		$output .= html( 'input', [ 'id' => $id . '-type', 'name' => element( 'name', $name ) . '[type]', 'type' => 'hidden', 'value' => element( 'type', $value ) ], '/' );
		$output .= html( '/span' );
		$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'class' => 'input-group-addon btn btn-danger fileinput-exists', 'data-dismiss' => 'fileinput' ], '<i class="fa fa-ban"></i> ' . lang( 'BTN_REMOVE' ) );
		$output .= html( 'div', [ 'class' => 'input-group-btn' ] );
		$output .= html( 'button', [ 'class' => 'btn btn-white dropdown-toggle', 'data-toggle' => 'dropdown' ] );
		$output .= lang( 'BTN_TYPE' ) . '&nbsp;';
		$output .= html( 'span', [ 'class' => 'caret' ], '/' );
		$output .= html( '/button' );
		$output .= html( 'ul', [ 'class' => 'dropdown-menu pull-right' ] );
		$output .= html( 'li' );
		$sources = [
			'Localhost' => 'fa-youtube-play',
			'YouTube'   => 'fa-youtube-square',
			'Vimeo'     => 'fa-vimeo-square',
			'Facebook'  => 'fa-facebook-square',
			'Google'    => 'fa-google-plus-square',
		];
		foreach ( $sources as $label => $icon )
		{
			$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'rel' => $id, 'class' => 'video-type', 'data-type' => alias( $label ) ] );
			$output .= html( 'i', [ 'class' => 'fa ' . $icon ], '/i' );
			$output .= '&nbsp;&nbsp;' . $label;
			$output .= html( '/a' );
		}
		$output .= html( '/li' );
		$output .= html( 'li', [ 'class' => 'divider' ], '/li' );
		$output .= html( 'li' );
		$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'rel' => $id, 'class' => 'video-preview' ] );
		$output .= html( 'i', [ 'class' => 'fa fa-film' ], '/i' );
		$output .= '&nbsp;&nbsp;' . lang( 'BTN_PREVIEW' );
		$output .= html( '/a' );
		$output .= html( '/li' );
		$output .= html( 'li' );
		$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'rel' => $id, 'class' => 'video-properties' ] );
		$output .= html( 'i', [ 'class' => 'fa fa-file-text-o' ], '/i' );
		$output .= '&nbsp;&nbsp;' . lang( 'BTN_PROPERTIES' );
		$output .= html( '/a' );
		$output .= html( '/li' );
		$output .= html( 'li' );
		$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'rel' => $id, 'class' => 'video-delete' ] );
		$output .= html( 'i', [ 'class' => 'fa fa-trash-o' ], '/i' );
		$output .= '&nbsp;&nbsp;' . lang( 'BTN_DELETE' );
		$output .= html( '/a' );
		$output .= html( '/li' );
		$output .= html( '/ul' );
		$output .= html( '/div' );
		$output .= html( '/div' );
		$output .= html( '/div' );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Advanced File Upload
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_file_advanced' ) )
{
	function form_file_advanced( $name = '', $value = '' )
	{
		$defaults = [ 'name' => ( ( ! is_array( $name ) ) ? $name : '' ) ];

		$id = element( 'id', $name );

		$output = html( 'div', [ 'class' => 'fileinput fileinput-new', 'data-provider' => 'fileinput' ] );
		$output .= html( 'div', [ 'class' => 'input-group' ] );
		$output .= html( 'span', [ 'class' => 'input-group-addon btn-white' ] );
		$output .= html( 'i', [ 'id' => $id . '-icon', 'class' => 'fa fa-paperclip' ], '/i' );
		$output .= html( '/span' );
		$output .= html( 'input', [ 'id' => $id . '-url-field', 'type' => 'text', 'class' => 'hidden form-control', 'name' => $name[ 'name' ] . '[url]', 'value' => element( 'url', $value ) ], '/' );
		$output .= html( 'div', [ 'id' => $id . '-localhost-trigger', 'class' => 'form-control', 'data-trigger' => 'fileinput' ] );
		//$output.= html('i',array('class' => 'fa fa-file fileinput-exists'),'/i');
		$output .= html( 'span', [ 'class' => 'fileinput-filename', 'style' => 'padding-left:10px;' ] );
		$output .= element( 'file', $value );
		$output .= html( '/span' );
		$output .= html( '/div' );
		$output .= html( 'span', [ 'id' => $id . '-localhost-field', 'class' => 'input-group-addon btn btn-primary btn-file rounded-none' ] );
		$output .= html( 'span', [ 'class' => 'fileinput-new' ], '<i class="fa fa-paperclip"></i> ' . lang( 'BTN_SELECT_FILE' ) );
		$output .= html( 'span', [ 'class' => 'fileinput-exists' ], '<i class="fa fa-undo"></i> ' . lang( 'BTN_CHANGE' ) );
		$output .= html( 'input', [ 'id' => $id . '-field', 'type' => 'file', 'class' => 'default', 'name' => $name[ 'name' ] . '[file]', 'value' => element( 'file', $value ) ], '/' );
		$output .= html( 'input', [ 'id' => $id . '-type', 'name' => element( 'name', $name ) . '[type]', 'type' => 'hidden', 'value' => element( 'type', $value ) ], '/' );
		$output .= html( '/span' );
		$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'class' => 'input-group-addon btn btn-danger fileinput-exists', 'data-dismiss' => 'fileinput' ], '<i class="fa fa-ban"></i> ' . lang( 'BTN_REMOVE' ) );
		$output .= html( 'div', [ 'class' => 'input-group-btn' ] );
		$output .= html( 'button', [ 'class' => 'btn btn-white dropdown-toggle', 'data-toggle' => 'dropdown' ] );
		$output .= lang( 'BTN_TYPE' ) . '&nbsp;';
		$output .= html( 'span', [ 'class' => 'caret' ], '/' );
		$output .= html( '/button' );
		$output .= html( 'ul', [ 'class' => 'dropdown-menu pull-right' ] );
		$output .= html( 'li' );
		$sources = [
			'Localhost' => 'fa-paperclip',
			'Dropbox'   => 'fa-dropbox',
			'Google'    => 'fa-google-plus-square',
			'URL'       => 'fa-link',
		];
		foreach ( $sources as $label => $icon )
		{
			$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'rel' => $id, 'class' => 'file-type', 'data-type' => alias( $label ) ] );
			$output .= html( 'i', [ 'class' => 'fa ' . $icon ], '/i' );
			$output .= '&nbsp;&nbsp;' . $label;
			$output .= html( '/a' );
		}
		$output .= html( '/li' );
		$output .= html( 'li', [ 'class' => 'divider' ], '/li' );
		$output .= html( 'li' );
		$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'rel' => $id, 'class' => 'file-properties' ] );
		$output .= html( 'i', [ 'class' => 'fa fa-file-text-o' ], '/i' );
		$output .= '&nbsp;&nbsp;' . lang( 'BTN_PROPERTIES' );
		$output .= html( '/a' );
		$output .= html( '/li' );
		$output .= html( 'li' );
		$output .= html( 'a', [ 'href' => 'javascript:void(0);', 'rel' => $id, 'class' => 'file-delete' ] );
		$output .= html( 'i', [ 'class' => 'fa fa-trash-o' ], '/i' );
		$output .= '&nbsp;&nbsp;' . lang( 'BTN_DELETE' );
		$output .= html( '/a' );
		$output .= html( '/li' );
		$output .= html( '/ul' );
		$output .= html( '/div' );
		$output .= html( '/div' );
		$output .= html( '/div' );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Image Upload with Library and Dropzone
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_image_library' ) )
{
	function form_image_library( $name = '', $value = '' )
	{
		$URL = element( 'data-source', $name );

		$output = html( 'div', [ 'class' => 'images-library-panel' ] );
		//$output.= html('a',array('action' => 'browse','href' => $URL,'data-fancybox-type' => 'iframe','type' => 'button','class' => 'iframe-popup btn btn-primary'),'<i class="fa fa-picture-o"></i> '.lang('BTN_SELECT_IMAGE'));
		$output .= html( 'a', [ 'rel' => $name[ 'id' ], 'action' => 'browse', 'data-source' => $URL, 'data-toggle' => 'modal', 'class' => 'btn-select-img btn btn-primary' ], '<i class="fa fa-picture-o"></i> ' . lang( 'BTN_SELECT_IMAGE' ) );
		$output .= html( 'a', [ 'rel' => $name[ 'id' ], 'action' => 'select-all', 'type' => 'button', 'class' => 'images-library-panel-btn btn btn-info' ], '<i class="fa fa-ban"></i> ' . lang( 'BTN_SELECT_ALL' ) );
		$output .= html( 'a', [ 'rel' => $name[ 'id' ], 'action' => 'delete', 'type' => 'button', 'class' => 'images-library-panel-btn btn btn-danger' ], '<i class="fa fa-ban"></i> ' . lang( 'BTN_REMOVE' ) );
		$output .= html( '/div' );

		$output .= html( 'div', [ 'class' => 'images-library-wrapper' ] );
		if ( empty( $value ) )
		{
			$output .= html( 'div', [ 'id' => $name[ 'id' ] . '-preview', 'class' => 'images-library-preview gallery media-gal' ], '/div' );
		}
		else
		{
			$output .= html( 'div', [ 'id' => $name[ 'id' ] . '-preview', 'class' => 'images-library-preview gallery media-gal' ] );
			foreach ( $value as $image )
			{
				$output .= html( 'div', [ 'class' => 'images item' ] );
				$output .= img( app_url( 'images/thumbnail/120x120', '/' ) . $image );
				$output .= html( 'div', [ 'class' => 'item-checkbox square-blue', 'style' => 'display:none;' ] );
				$output .= html( 'input', [ 'type' => 'checkbox', 'class' => 'browser-checkbox', 'value' => $image ], '/' );
				$output .= html( '/div' );
				$output .= html( '/div' );
			}
			$output .= html( '/div' );
		}
		$output .= html( 'div', [ 'class' => 'clearfix' ], '/div' );
		$json_value = str_replace( '"', "'", json_encode( $value ) );
		$output .= html( '/div' );
		$output .= html( 'input', [ 'type' => 'hidden', 'id' => $name[ 'id' ] . '-input', 'name' => $name[ 'name' ], 'value' => $json_value ] );

		return $output;
	}
}

// ------------------------------------------------------------------------

/**
 * Switch
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_switch' ) )
{
	function form_switch( $name = '', $value = '' )
	{
		$name[ 'checked' ] = ( $value == 1 ? TRUE : FALSE );

		return form_checkbox( $name, $value );
	}
}

// ------------------------------------------------------------------------

/**
 * Switch
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_map_picker' ) )
{
	function form_map_picker( $name = '', $value = '' )
	{
		$CI =& get_instance();
		$CI->template->assets->link_js( 'http://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places', 'header' );

		$default_value = [
			'address'   => 'Jakarta, Indonesia',
			'latitude'  => '-6.2087634',
			'longitude' => '106.84559899999999',
		];

		$value        = ( is_json( $value ) ? json_decode( $value ) : $value );
		$value        = ( empty( $value ) ? $default_value : $value );
		$hidden_value = ( is_array( $value ) ? json_encode( $value ) : $value );
		$hidden_value = str_replace( '"', "'", $hidden_value );

		$output = html( 'div', [ 'id' => $name[ 'id' ], 'class' => 'map-picker' ] );
		$output .= html( 'input', [ 'type' => 'text', 'id' => $name[ 'id' ] . '-search', 'class' => 'input form-control', 'placeholder' => 'Type street address', 'value' => element( 'address', $value ) ], '/' );
		$output .= html( '/div' );

		$output .= html( 'div', [ 'id' => $name[ 'id' ] . '-map', 'class' => 'rounded-left map-picker-preview col-md-7' ] );

		$output .= html( '/div' );

		$output .= html( 'div', [ 'id' => $name[ 'id' ] . '-address', 'class' => 'rounded-right map-picker-address col-md-5' ] );
		$output .= html( 'table', [ 'class' => 'table' ] );
		$output .= html( 'tr' );
		$output .= html( 'th', '', '<i class="fa fa-location-arrow"></i> ' . lang( 'LBL_ADDRESS' ) );
		$output .= html( '/tr' );
		$output .= html( 'tr' );
		$output .= html( 'td', [ 'id' => $name[ 'id' ] . '-full-address' ], element( 'address', $value, 'waiting...' ) );
		$output .= html( '/tr' );
		$output .= html( 'tr' );
		$output .= html( 'th', '', '<i class="fa fa-map-marker"></i> Latitude, Longitude' );
		$output .= html( '/tr' );
		$output .= html( 'tr' );
		$output .= html( 'td' );
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-latitude' ], element( 'latitude', $value, '-0.00' ) ) . ', ';
		$output .= html( 'span', [ 'id' => $name[ 'id' ] . '-longitude' ], element( 'longitude', $value, '0.00' ) ) . ', ';
		$output .= html( '/td' );
		$output .= html( '/tr' );
		$output .= html( '/table' );
		$output .= html( '/div' );

		$output .= html( 'input', [ 'name' => $name[ 'name' ], 'type' => 'hidden', 'id' => $name[ 'id' ] . '-input', 'value' => $hidden_value ], '/' );

		$output .= html( 'div', [ 'class' => 'clearfix' ], '/div' );

		return $output;
	}
}

// ------------------------------------------------------------------------


/**
 * Parse extras
 *
 * Takes an array of extras and spits them back in a string or just returns the string
 *
 * @access  public
 *
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( '_parse_extras' ) )
{
	function _parse_extras( $attrs )
	{
		if ( ! is_array( $attrs ) )
		{
			return $attrs;
		}

		return ' ' . implode( ' ', $attrs );
	}
}


/**
 * Help
 *
 * @access  public
 *
 * @param   mixed
 * @param   string
 * @param   mixed
 *
 * @return  string
 */
if ( ! function_exists( 'form_birthday' ) )
{
	function form_birthday( $name, $value = '' )
	{

		$field_name  = element( 'name', $name, 'person' );
		$field_id    = element( 'id', $name, $field_name );
		$field_class = element( 'class', $name, 'selectboxit focus form-control' );

		$fields = [ 'date', 'month', 'year' ];

		if ( $value != '' )
		{
			$birthday = strtotime( $value );

			$value = [
				'date'  => date( 'd', $birthday ),
				'month' => date( 'm', $birthday ),
				'year'  => date( 'Y', $birthday ),
			];

			//print_console($value);
		}

		$output = '';
		foreach ( $fields as $i => $field )
		{
			$output .= html( 'div', [ 'class' => 'col-md-4' . ( $i == 0 ? ' clear-padding-left' : ( $i == 1 ? ' clear-padding-side' : '' ) ) ] );
			$attr = [
				'id'    => $field_id . '-' . $field,
				'type'  => 'text',
				'class' => $field_class,
			];

			$options = [ ];

			if ( $field == 'date' )
			{
				foreach ( range( 1, 31 ) as $option )
				{
					$option             = str_pad( $option, 2, '0', STR_PAD_LEFT );
					$options[ $option ] = $option;
				}
			}

			if ( $field == 'month' )
			{
				foreach ( range( 1, 12 ) as $option )
				{
					$option = str_pad( $option, 2, '0', STR_PAD_LEFT );
					$label  = DateTime::createFromFormat( '!m', $option );

					$options[ $option ] = $label->format( 'F' );
				}
			}

			if ( $field == 'year' )
			{
				foreach ( range( 1900, date( 'Y' ) ) as $option )
				{
					$options[ $option ] = $option;
				}
			}

			$attr = array_merge( $name, $attr );

			if ( is_null( $value ) )
			{
				$value = [ ];
			}

			$output .= form_dropdown( $field_name . '-' . $field, $options, element( $field, $value ), parse_attributes( $attr ) );
			$output .= html( '/div' );
		}

		$output .= '<div class="clearfix"></div>';

		return $output;
	}
}

