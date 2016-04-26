<?php
/**
 * O2Glob
 *
 * Global Common Class Libraries for PHP 5.4 or newer
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2glob/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\Glob\Helpers;


use O2System\Glob;

class Validation
{
	/**
	 * Validation Rules
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_rules = array();

	/**
	 * Validation Errors
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_errors = array();

	/**
	 * Validation Messages
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_custom_messages = array();

	/**
	 * Source Variables
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_source_vars = array();

	// ------------------------------------------------------------------------

	/**
	 * Class Constructor
	 *
	 * @param array $source_vars
	 */
	public function __construct( $source_vars = array(), $language = 'en' )
	{
		if ( ! empty( $source_vars ) )
		{
			if ( $source_vars instanceof \ArrayObject )
			{
				$source_vars = $source_vars->getArrayCopy();
			}

			$this->_source_vars = $source_vars;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Source
	 *
	 * @param array $source_vars
	 *
	 * @access  public
	 */
	public function set_source( array $source_vars )
	{
		$this->_source_vars = $source_vars;
	}

	// ------------------------------------------------------------------------


	public function set_rule( $field, $label, $rules, $messages = array() )
	{
		$this->_rules[ $field ] = array(
			'field'    => $field,
			'label'    => $label,
			'rules'    => $rules,
			'messages' => $messages,
		);
	}

	// ------------------------------------------------------------------------

	public function set_rules( array $rules )
	{
		foreach ( $rules as $rule )
		{
			$this->set_rule( $rule[ 'field' ], $rule[ 'label' ], $rule[ 'rules' ], $rule[ 'messages' ] );
		}
	}

	// ------------------------------------------------------------------------

	public function has_rule( $field )
	{
		if ( array_key_exists( $this->_rules[ $field ] ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function set_value( $field, $default = '' )
	{
		if ( $this->has_rule( $field ) )
		{
			if ( empty( $this->_source_vars[ $field ] ) )
			{
				$this->_source_vars[ $field ] = $default;
			}

			return $this->_source_vars[ $field ];
		}

		return NULL;
	}

	// ------------------------------------------------------------------------

	public function set_message( $field, $message )
	{
		$this->_custom_messages[ $field ] = $message;
	}

	// ------------------------------------------------------------------------

	public function validate()
	{
		if ( ! empty( $this->_source_vars ) )
		{
			foreach ( $this->_rules as $field => $rule )
			{
				if ( isset( $this->_source_vars[ $field ] ) )
				{
					if ( is_callable( $rule[ 'rules' ] ) )
					{
						if ( $rule[ 'rules' ]( $this->_source_vars[ $field ] ) )
						{
							if ( ! empty( $rule[ 'messages' ] ) )
							{
								$this->_set_error( $rule[ 'messages' ], $rule[ 'label' ] );
							}
							else
							{
								$this->_set_error( $field, $rule[ 'label' ] );
							}
						}
					}
					elseif ( is_string( $rule[ 'rules' ] ) )
					{
						$methods = explode( '|', $rule[ 'rules' ] );

						foreach ( $methods as $method )
						{
							$vars = array( trim( $this->_source_vars[ $field ] ) );

							// Is the field name an array? If it is an array, we break it apart
							// into its components so that we can fetch the corresponding POST data later
							if ( preg_match_all( '/\[(.*?)\]/', $method, $matches ) )
							{
								$method = str_replace( $matches[ 0 ], '', $method );
								$vars = array_merge( $vars, $matches[ 1 ] );
							}

							$method = 'is_' . $method;

							if ( method_exists( __CLASS__, $method ) )
							{
								$call_args = $vars;

								if ( $method === 'is_matches' )
								{
									if ( isset( $this->_source_vars[ $vars[ 1 ] ] ) )
									{
										$call_args[ 1 ] = $this->_source_vars[ $vars[ 1 ] ];
									}
								}

								if ( call_user_func_array( 'self::' . $method, $call_args ) === FALSE )
								{
									array_unshift( $vars, $rule[ 'label' ] );

									if ( ! empty( $rule[ 'messages' ] ) )
									{
										$this->_set_error( $rule[ 'messages' ], $vars );
									}
									elseif ( array_key_exists( $field, $this->_custom_messages ) )
									{
										$this->_set_error( $field, $vars );
									}
									else
									{
										$this->_set_error( $method, $vars );
									}

									break;
								}
							}
						}
					}
				}
			}
		}

		return empty( $this->_errors ) ? TRUE : FALSE;
	}

	// ------------------------------------------------------------------------

	protected function _set_error( $error, $vars = array() )
	{
		if ( array_key_exists( $error, $this->_custom_messages ) )
		{
			$error = $this->_custom_messages[ $error ];
		}
		elseif ( class_exists( 'O2System', FALSE ) )
		{
			\O2System::$language->load( 'validation' );
			$line = \O2System::$language->line( $error );

			if ( ! empty( $line ) )
			{
				$error = $line;
			}
		}
		else
		{
			$error_messages = array(
				'IS_REQUIRED'              => "The %s field is required.",
				'IS_MATCHES'               => "The %s field does not match the %s field.",
				'IS_REGEX_MATCH'           => "The %s field is not in the correct format.",
				'IS_MIN_LENGTH'            => "The %s field must be at least %s characters in length.",
				'IS_MAX_LENGTH'            => "The %s field can not exceed %s characters in length.",
				'IS_EXACT_LENGTH'          => "The %s field must be exactly %s characters in length.",
				'IS_EMAIL'                 => "The %s field must contain a valid email address.",
				'IS_URL'                   => "The %s field must contain a valid URL.",
				'IS_IPV4'                  => "The %s field must contain a valid IPv4.",
				'IS_IPV6'                  => "The %s field must contain a valid IPv6.",
				'IS_ALPHA'                 => "The %s field may only contain alphabetical characters.",
				'IS_ALPHA_NUMERIC'         => "The %s field may only contain alpha-numeric characters.",
				'IS_ALPHA_DASH'            => "The %s field may only contain alpha-numeric characters and dashes.",
				'IS_ALPHA_NUMERIC_SPACES'  => "The %s field may only contain alpha-numeric characters and spaces.",
				'IS_ALPHA_UNDERSCORE'      => "The %s field may only contain alpha-numeric characters and underscores.",
				'IS_ALPHA_UNDERSCORE_DASH' => "The %s field may only contain alpha-numeric characters, underscores, and dashes.",
				'IS_NUMERIC'               => "The %s field must contain only numeric characters.",
				'IS_INTEGER'               => "The %s field must contain an integer.",
				'IS_UNIQUE'                => "The %s field must contain a unique value.",
				'IS_NATURAL'               => "The %s field must contain only positive numbers.",
				'IS_NATURAL_NO_ZERO'       => "The %s field must contain a number greater than zero.",
				'IS_DECIMAL'               => "The %s field must contain a decimal number.",
				'IS_LESS'                  => "The %s field must contain a number less than %s.",
				'IS_LESS_EQUAL'            => "The %s field must contain a number less or equal with %s.",
				'IS_GREATER'               => "The %s field must contain a number greater than %s.",
				'IS_GREATER_EQUAL'         => "The %s field must contain a number greater or equal with %s.",
				'IS_DIMENSION'             => "The %s field must contain a valid dimension format (%s).",
				'IS_URL'                   => "The %s field must contain a valid URL.",
				'IS_DOMAIN'                => "The %s field must contain a valid domain name.",
				'IS_BOOL'                  => "The %s field must contain a valid boolean value (true or false).",
				'IS_LISTED'                => "The %s field must contain a listed value (%s).",
				'IS_BASE64'                => "The %s field must contain a valid string Base64 Encode format.",
				'IS_MD5'                   => "The %s field must contain a valid string MD5 format.",
				'IS_DATE'                  => "The %s field must contain a valid date format (%s).",
				'IS_MSISDN'                => "The %s field must contain a valid MSISDN with leading %s.",
				'IS_PASSWORD'              => "The %s field length must be greater or equal than %s and contains %s",
			);

			if ( array_key_exists( $error, $error_messages ) )
			{
				$error = $error_messages[ $error ];
			}
		}

		array_unshift( $vars, $error );
		$this->_errors[] = call_user_func_array( 'sprintf', $vars );
	}

	// ------------------------------------------------------------------------

	public function get_errors()
	{
		return $this->_errors;
	}

	// ------------------------------------------------------------------------

	public static function is_required( $string )
	{
		if ( empty( $string ) OR strlen( $string ) == 0 )
		{
			return FALSE;
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	public static function is_matches( $string, $match )
	{
		if ( $string === $match )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Performs a Regular Expression match test.
	 *
	 * @param    string
	 * @param    string    regex
	 *
	 * @return    bool
	 */
	public static function is_regex_match( $string, $regex )
	{
		return (bool) preg_match( $regex, $string );
	}

	// --------------------------------------------------------------------


	public static function is_float( $string )
	{
		return filter_var( $string, FILTER_VALIDATE_FLOAT );
	}

	// ------------------------------------------------------------------------

	/**
	 * Minimum Length
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_min_length( $string, $length )
	{
		if ( ! is_numeric( $length ) )
		{
			return FALSE;
		}

		return ( $length <= mb_strlen( $string ) );
	}

	// --------------------------------------------------------------------

	/**
	 * Max Length
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_max_length( $string, $length )
	{
		if ( ! is_numeric( $length ) )
		{
			return FALSE;
		}

		return ( $length >= mb_strlen( $string ) );
	}

	// --------------------------------------------------------------------

	/**
	 * Exact Length
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_exact_length( $string, $length )
	{
		if ( ! is_numeric( $length ) )
		{
			return FALSE;
		}

		return ( mb_strlen( $string ) === (int) $length );
	}

	public static function is_dimension( $string, $format = 'W x H x L' )
	{
		$string = strtolower( $string );
		$string = preg_replace( '/\s+/', '', $string );
		$x_string = explode( 'x', $string );

		$format = strtolower( $format );
		$format = preg_replace( '/\s+/', '', $format );
		$x_format = explode( 'x', $format );

		if ( count( $x_string ) == count( $x_format ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public static function is_ipv4( $string )
	{
		return filter_var( $string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
	}

	// ------------------------------------------------------------------------

	public static function is_ipv6( $string )
	{
		return filter_var( $string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
	}

	// ------------------------------------------------------------------------

	public static function is_url( $string )
	{
		if ( preg_match( '/^(?:([^:]*)\:)?\/\/(.+)$/', $string, $matches ) )
		{
			if ( empty( $matches[ 2 ] ) )
			{
				return FALSE;
			}
			elseif ( ! in_array( $matches[ 1 ], array( 'http', 'https' ), TRUE ) )
			{
				return FALSE;
			}

			$string = $matches[ 2 ];
		}

		$string = 'http://' . $string;

		return filter_var( $string, FILTER_VALIDATE_URL );
	}

	// ------------------------------------------------------------------------

	public static function is_email( $string )
	{
		if ( function_exists( 'idn_to_ascii' ) && $strpos = strpos( $string, '@' ) )
		{
			$string = substr( $string, 0, ++$strpos ) . idn_to_ascii( substr( $string, $strpos ) );
		}

		return (bool) filter_var( $string, FILTER_VALIDATE_EMAIL );
	}

	// ------------------------------------------------------------------------

	public static function is_domain( $string )
	{
		return ( preg_match( "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $string ) //valid chars check
			&& preg_match( "/^.{1,253}$/", $string ) //overall length check
			&& preg_match( "/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $string ) ); //length of each label
	}

	// ------------------------------------------------------------------------

	public static function is_bool( $string )
	{
		return filter_var( $string, FILTER_VALIDATE_BOOLEAN );
	}

	// ------------------------------------------------------------------------

	/**
	 * Alpha
	 *
	 * @param    string $string
	 *
	 * @return    bool
	 */
	public static function is_alpha( $string )
	{
		return ctype_alpha( $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha
	 *
	 * @param    string $string
	 *
	 * @return    bool
	 */
	public static function is_alpha_spaces( $string )
	{
		return (bool) preg_match( '/^[A-Z ]+$/i', $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric
	 *
	 * @param    string $string
	 *
	 * @return    bool
	 */
	public static function is_alpha_numeric( $string )
	{
		return ctype_alnum( (string) $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric w/ spaces
	 *
	 * @param    string $string
	 *
	 * @return    bool
	 */
	public static function is_alpha_numeric_spaces( $string )
	{
		return (bool) preg_match( '/^[A-Z0-9 ]+$/i', $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_alpha_dash( $string )
	{
		return (bool) preg_match( '/^[a-z0-9-]+$/i', $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_alpha_underscore( $string )
	{
		return (bool) preg_match( '/^[a-z0-9_]+$/i', $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_alpha_underscore_dash( $string )
	{
		return (bool) preg_match( '/^[a-z0-9_-]+$/i', $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Numeric
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_numeric( $str )
	{
		return (bool) preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str );

	}

	// --------------------------------------------------------------------

	/**
	 * Integer
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_integer( $str )
	{
		return (bool) preg_match( '/^[\-+]?[0-9]+$/', $str );
	}

	// --------------------------------------------------------------------

	/**
	 * Decimal number
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_decimal( $string )
	{
		return (bool) preg_match( '/^[\-+]?[0-9]+\.[0-9]+$/', $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Greater than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public static function is_greater( $string, $min )
	{
		return is_numeric( $string ) ? ( $string > $min ) : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Equal to or Greater than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public static function is_greater_equal( $string, $min )
	{
		return is_numeric( $string ) ? ( $string >= $min ) : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Less than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public static function is_less( $string, $max )
	{
		return is_numeric( $string ) ? ( $string < $max ) : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Equal to or Less than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public static function is_less_equal( $string, $max )
	{
		return is_numeric( $string ) ? ( $string <= $max ) : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Value should be within an array of values
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_listed( $string, $list )
	{
		if ( is_string( $list ) )
		{
			$list = explode( ',', $list );
			$list = array_map( 'trim', $list );
		}

		return in_array( $string, $list, TRUE );
	}

	// --------------------------------------------------------------------

	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_natural( $string )
	{
		return ctype_digit( (string) $string );
	}

	// --------------------------------------------------------------------

	/**
	 * Is a Natural number, but not a zero  (1,2,3, etc.)
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public static function is_natural_no_zero( $string )
	{
		return ( $string != 0 && ctype_digit( (string) $string ) );
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function is_base64( $string )
	{
		return (bool) ( base64_encode( base64_decode( $string ) ) === $string );
	}

	// --------------------------------------------------------------------

	public static function is_md5( $string )
	{
		return preg_match( '/^[a-f0-9]{32}$/i', $string );
	}

	// ------------------------------------------------------------------------

	public static function is_msisdn( $string, $leading = '62' )
	{
		return (bool) preg_match( '/^(' . $leading . '[1-9]{1}[0-9]{1,2})[0-9]{6,8}$/', $string );
	}

	// ------------------------------------------------------------------------

	public static function is_date( $string, $format = 'Y-m-d' )
	{
		$date_time = \DateTime::createFromFormat( $format, $string );

		return (bool) $date_time !== FALSE && ! array_sum( $date_time->getLastErrors() );
	}

	// ------------------------------------------------------------------------

	public static function is_password( $string, $length = 8, $format = 'uppercase, lowercase, number, special' )
	{
		// Length
		if ( self::is_min_length( $string, $length ) === FALSE )
		{
			return FALSE;
		}

		$format = strtolower( $format );
		$format = explode( ',', $format );
		$format = array_map( 'trim', $format );

		foreach ( $format as $type )
		{
			switch ( $type )
			{
				case 'uppercase':
					if ( preg_match_all( '/[A-Z]/', $string, $uppercase ) )
					{
						$valid[ $type ] = count( $uppercase[ 0 ] );
					}
					break;
				case 'lowercase':
					if ( preg_match_all( '/[a-z]/', $string, $lowercase ) )
					{
						$valid[ $type ] = count( $lowercase[ 0 ] );
					}
					break;
				case 'number':
				case 'numbers':
					if ( preg_match_all( '/[0-9]/', $string, $numbers ) )
					{
						$valid[ $type ] = count( $numbers[ 0 ] );
					}
					break;
				case 'special character':
				case 'special-character':
				case 'special':
					// Special Characters
					if ( preg_match_all( '/[!@#$%^&*()\-_=+{};:,<.>]/', $string, $special ) )
					{
						$valid[ $type ] = count( $special[ 0 ] );
					}
					break;
			}
		}

		$diff = array_diff( $format, array_keys( $valid ) );

		return empty( $diff ) ? TRUE : FALSE;
	}
}