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

namespace O2System\Core\Input;

class Validation
{
	/**
	 * Validation Rules
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_rules = [ ];

	/**
	 * Validation Errors
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_errors = [ ];

	/**
	 * Validation Messages
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_custom_messages = [ ];

	/**
	 * Source Variables
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_source_vars = [ ];

	// ------------------------------------------------------------------------

	/**
	 * Class Constructor
	 *
	 * @param array $source_vars
	 */
	public function __construct( $source_vars = [ ], $language = 'en' )
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
	public function setSource( array $source_vars )
	{
		$this->_source_vars = $source_vars;
	}

	// ------------------------------------------------------------------------


	public function setRule( $field, $label, $rules, $messages = [ ] )
	{
		$this->_rules[ $field ] = [
			'field'    => $field,
			'label'    => $label,
			'rules'    => $rules,
			'messages' => $messages,
		];
	}

	// ------------------------------------------------------------------------

	public function setRules( array $rules )
	{
		foreach ( $rules as $rule )
		{
			$this->setRule( $rule[ 'field' ], $rule[ 'label' ], $rule[ 'rules' ], $rule[ 'messages' ] );
		}
	}

	// ------------------------------------------------------------------------

	public function hasRule( $field )
	{
		if ( array_key_exists( $field, $this->_rules ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function setValue( $field, $default = '' )
	{
		if ( $this->hasRule( $field ) )
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

	public function setMessage( $field, $message )
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
				if ( isset( $this->_source_vars[ $field ] ) && ! empty ( $this->_source_vars[ $field ] ) )
				{
					if ( is_callable( $rule[ 'rules' ] ) )
					{
						if ( $rule[ 'rules' ]( $this->_source_vars[ $field ] ) )
						{
							if ( ! empty( $rule[ 'messages' ] ) )
							{
								$this->_setError( $rule[ 'messages' ], $rule[ 'label' ] );
							}
							else
							{
								$this->_setError( $field, $rule[ 'label' ] );
							}
						}
					}
					elseif ( is_string( $rule[ 'rules' ] ) )
					{
						$methods = explode( '|', $rule[ 'rules' ] );

						foreach ( $methods as $method )
						{
							$vars = [ trim( $this->_source_vars[ $field ] ) ];

							// Is the field name an array? If it is an array, we break it apart
							// into its components so that we can fetch the corresponding POST data later
							if ( preg_match_all( '/\[(.*?)\]/', $method, $matches ) )
							{
								$method = str_replace( $matches[ 0 ], '', $method );
								$vars   = array_merge( $vars, $matches[ 1 ] );
							}

							$method = 'is' . ucfirst( $method );

							if ( method_exists( __CLASS__, $method ) )
							{
								$call_args = $vars;

								if ( $method === 'isMatches' )
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
										$this->_setError( $rule[ 'messages' ], $vars );
									}
									elseif ( array_key_exists( $field, $this->_custom_messages ) )
									{
										$this->_setError( $field, $vars );
									}
									else
									{
										$this->_setError( strtoupper( 'is_' . join( '', explode( 'is', $method ) ) ), $vars );
									}

									break;
								}
							}
						}
					}
				}
				else
				{
					if ( is_callable( $rule[ 'rules' ] ) )
					{
						if ( $rule[ 'rules' ]( $this->_source_vars[ $field ] ) )
						{
							if ( ! empty( $rule[ 'messages' ] ) )
							{
								$this->_setError( $rule[ 'messages' ], $rule[ 'label' ] );
							}
							else
							{
								$this->_setError( $field, $rule[ 'label' ] );
							}
						}
					}
					elseif ( is_string( $rule[ 'rules' ] ) )
					{
						$methods = explode( '|', $rule[ 'rules' ] );

						foreach ( $methods as $method )
						{
							$vars = [ trim( $field ) ];

							// Is the field name an array? If it is an array, we break it apart
							// into its components so that we can fetch the corresponding POST data later
							if ( preg_match_all( '/\[(.*?)\]/', $method, $matches ) )
							{
								$method = str_replace( $matches[ 0 ], '', $method );
								$vars   = array_merge( $vars, $matches[ 1 ] );
							}

							$method = 'is' . ucfirst( $method );

							if ( method_exists( __CLASS__, $method ) )
							{
								$call_args = $vars;

								if ( $method === 'isMatches' )
								{
									if ( isset( $this->_source_vars[ $vars[ 1 ] ] ) )
									{
										$call_args[ 1 ] = $this->_source_vars[ $vars[ 1 ] ];
									}
								}
								else
								{
									array_unshift( $vars, $rule[ 'label' ] );

									if ( ! empty( $rule[ 'messages' ] ) )
									{
										$this->_setError( $rule[ 'messages' ], $vars );
									}
									elseif ( array_key_exists( $field, $this->_custom_messages ) )
									{
										$this->_setError( $field, $vars );
									}
									else
									{
										$this->_setError( strtoupper( 'is_' . join( '', explode( 'is', $method ) ) ), $vars );
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

	protected function _setError( $error, $vars = [ ] )
	{
		if ( array_key_exists( $error, $this->_custom_messages ) )
		{
			$error = $this->_custom_messages[ $error ];
		}
		else
		{
			\O2System::$language->load( 'validation' );
			$line = \O2System::$language->line( $error );

			if ( ! empty( $line ) )
			{
				$error = $line;
			}
		}

		array_unshift( $vars, $error );
		$this->_errors[] = call_user_func_array( 'sprintf', $vars );
	}

	// ------------------------------------------------------------------------

	public function getErrors()
	{
		return $this->_errors;
	}

	// ------------------------------------------------------------------------

	public static function isRequired( $string )
	{
		if ( empty( $string ) OR strlen( $string ) == 0 )
		{
			return FALSE;
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	public static function isMatches( $string, $match )
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
	public static function isRegexMatch( $string, $regex )
	{
		return (bool) preg_match( $regex, $string );
	}

	// --------------------------------------------------------------------


	public static function isFloat( $string )
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
	public static function isMinLength( $string, $length )
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
	public static function isMaxLength( $string, $length )
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
	public static function isExactLength( $string, $length )
	{
		if ( ! is_numeric( $length ) )
		{
			return FALSE;
		}

		return ( mb_strlen( $string ) === (int) $length );
	}

	public static function isDimension( $string, $format = 'W x H x L' )
	{
		$string   = strtolower( $string );
		$string   = preg_replace( '/\s+/', '', $string );
		$x_string = explode( 'x', $string );

		$format   = strtolower( $format );
		$format   = preg_replace( '/\s+/', '', $format );
		$x_format = explode( 'x', $format );

		if ( count( $x_string ) == count( $x_format ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public static function isIpv4( $string )
	{
		return filter_var( $string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
	}

	// ------------------------------------------------------------------------

	public static function isIpv6( $string )
	{
		return filter_var( $string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
	}

	// ------------------------------------------------------------------------

	public static function isUrl( $string )
	{
		if ( preg_match( '/^(?:([^:]*)\:)?\/\/(.+)$/', $string, $matches ) )
		{
			if ( empty( $matches[ 2 ] ) )
			{
				return FALSE;
			}
			elseif ( ! in_array( $matches[ 1 ], [ 'http', 'https' ], TRUE ) )
			{
				return FALSE;
			}

			$string = $matches[ 2 ];
		}

		$string = 'http://' . $string;

		return filter_var( $string, FILTER_VALIDATE_URL );
	}

	// ------------------------------------------------------------------------

	public static function isEmail( $string )
	{
		if ( function_exists( 'idn_to_ascii' ) && $strpos = strpos( $string, '@' ) )
		{
			$string = substr( $string, 0, ++$strpos ) . idn_to_ascii( substr( $string, $strpos ) );
		}

		return (bool) filter_var( $string, FILTER_VALIDATE_EMAIL );
	}

	// ------------------------------------------------------------------------

	public static function isDomain( $string )
	{
		return ( preg_match( "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $string ) //valid chars check
			&& preg_match( "/^.{1,253}$/", $string ) //overall length check
			&& preg_match( "/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $string ) ); //length of each label
	}

	// ------------------------------------------------------------------------

	public static function isBool( $string )
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
	public static function isAlpha( $string )
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
	public static function isAlphaSpaces( $string )
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
	public static function isAlphaNumeric( $string )
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
	public static function isAlphaNumericSpaces( $string )
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
	public static function isAlphaDash( $string )
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
	public static function isAlphaUnderscore( $string )
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
	public static function isAlphaUnderscoreDash( $string )
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
	public static function isNumeric( $str )
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
	public static function isInteger( $str )
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
	public static function isDecimal( $string )
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
	public static function isGreater( $string, $min )
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
	public static function isGreaterEqual( $string, $min )
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
	public static function isLess( $string, $max )
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
	public static function isLessEqual( $string, $max )
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
	public static function isListed( $string, $list )
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
	public static function isNatural( $string )
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
	public static function isNaturalNoZero( $string )
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
	public function isBase64( $string )
	{
		return (bool) ( base64_encode( base64_decode( $string ) ) === $string );
	}

	// --------------------------------------------------------------------

	public static function isMd5( $string )
	{
		return preg_match( '/^[a-f0-9]{32}$/i', $string );
	}

	// ------------------------------------------------------------------------

	public static function isMsisdn( $string, $leading = '62' )
	{
		return (bool) preg_match( '/^(' . $leading . '[1-9]{1}[0-9]{1,2})[0-9]{6,8}$/', $string );
	}

	// ------------------------------------------------------------------------

	public static function isDate( $string, $format = 'Y-m-d' )
	{
		$date_time = \DateTime::createFromFormat( $format, $string );

		return (bool) $date_time !== FALSE && ! array_sum( $date_time->getLastErrors() );
	}

	// ------------------------------------------------------------------------

	public static function isPassword( $string, $length = 8, $format = 'uppercase, lowercase, number, special' )
	{
		// Length
		if ( self::isMinLength( $string, $length ) === FALSE )
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

	public static function isValidIp( $string, $which = NULL )
	{
		switch ( strtolower( $which ) )
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = NULL;
				break;
		}

		return (bool) filter_var( $string, FILTER_VALIDATE_IP, $which );
	}

	/**
	 * Is ASCII?
	 *
	 * Tests if a string is standard 7-bit ASCII or not.
	 *
	 * @param    string $string String to check
	 *
	 * @return    bool
	 */
	public static function isAscii( $string )
	{
		return ( preg_match( '/[^\x00-\x7F]/S', $string ) === 0 );
	}
}