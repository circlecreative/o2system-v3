<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * Number Helpers
 *
 * @package        O2System
 * @subpackage     helpers
 * @category       Helpers
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/helpers/number.html
 */
// ------------------------------------------------------------------------

if ( ! function_exists( 'number_price' ) )
{
	/**
	 * Number Price
	 *
	 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
	 *
	 * @param   int    $price      Price Num
	 * @param   string $currency   Price Currency
	 * @param   int    $decimal    Num of decimal
	 * @param   bool   $accounting Is Accounting Mode
	 *
	 * @return  string
	 */
	function number_price( $price, $currency = NULL, $decimal = 0, $accounting = FALSE )
	{
		$currency = isset( $currency ) ? $currency : \O2System::$config[ 'currency' ];
		$currency = trim( $currency );

		if ( is_bool( $decimal ) )
		{
			$accounting = $decimal;
			$decimal = 0;
		}

		if ( $accounting == TRUE )
		{
			return '<span data-role="price-currency" data-currency="' . $currency . '" class="pull-left">' . $currency . '</span> <span data-role="price-nominal" data-price="' . (int) $price . '" class="pull-right">' . number_format( (int) $price, (int) $decimal, ',', '.' ) . '</span>';
		}

		return $currency . ' ' . number_format( (int) $price, (int) $decimal, ',', '.' );
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'number_weight' ) )
{
	/**
	 * Number Price
	 *
	 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
	 *
	 * @param   int    $price      Price Num
	 * @param   string $currency   Price Currency
	 * @param   int    $decimal    Num of decimal
	 * @param   bool   $accounting Is Accounting Mode
	 *
	 * @return  string
	 */
	function number_weight( $weight, $unit = NULL, $decimal = 0, $accounting = FALSE )
	{
		$unit = isset( $unit ) ? $unit : \O2System::$config[ 'weight' ];
		$unit = trim( $unit );

		if ( is_bool( $decimal ) )
		{
			$accounting = $decimal;
			$decimal = 0;
		}

		if ( $accounting == TRUE )
		{
			return '<span data-role="weight-amount" class="pull-left" data-amount="' . (int) $weight . '">' . number_format( (int) $weight, (int) $decimal, ',', '.' ) . '</span> <span data-role="weight-unit" class="pull-right">&nbsp;' . $unit . '</span>';
		}

		return number_format( (int) $weight, (int) $decimal, ',', '.' ) . ' ' . $unit;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'number_weight' ) )
{
	/**
	 * Number Price
	 *
	 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
	 *
	 * @param   int    $price      Price Num
	 * @param   string $currency   Price Currency
	 * @param   int    $decimal    Num of decimal
	 * @param   bool   $accounting Is Accounting Mode
	 *
	 * @return  string
	 */
	function number_unit( $amount, $unit, $decimal = 0, $accounting = FALSE )
	{
		$unit = trim( $unit );

		if ( is_bool( $decimal ) )
		{
			$accounting = $decimal;
			$decimal = 0;
		}

		if ( $accounting == TRUE )
		{
			return '<span data-role="number-amount" class="pull-left" data-amount="' . (int) $amount . '">' . number_format( (int) $amount, (int) $decimal, ',', '.' ) . '</span> <span data-role="number-unit" class="pull-right">&nbsp;' . $unit . '</span>';
		}

		return number_format( (int) $amount, (int) $decimal, ',', '.' ) . ' ' . $unit;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'is_positive' ) )
{
	/**
	 * Is Odd
	 *
	 * Check if an odd number
	 *
	 * @param   int $number Number
	 *
	 * @return  bool
	 */
	function is_positive( $number )
	{
		if ( $number > 0 )
		{
			return TRUE;
		}

		return FALSE;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'is_odd' ) )
{
	/**
	 * Is Odd
	 *
	 * Check if an odd number
	 *
	 * @param   int $number Number
	 *
	 * @return  bool
	 */
	function is_odd( $number )
	{
		if ( $number % 2 == 0 )
		{
			return TRUE;
		}

		return FALSE;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'is_even' ) )
{
	/**
	 * Is Even
	 *
	 * Check if an even number
	 *
	 * @param   int $number Number
	 *
	 * @return  bool
	 */
	function is_even( $number )
	{
		if ( $number % 2 == 0 )
		{
			return FALSE;
		}

		return TRUE;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'prices_ranges' ) )
{
	/**
	 * Prices Ranges
	 *
	 * @param   int $start_price Start Price
	 * @param   int $end_price   End Price
	 * @param   int $multiply    Multiply Step
	 *
	 * @return  array
	 */
	function prices_ranges( $start_price, $end_price, $multiply = 0 )
	{
		$start_price = str_replace( '.', '', $start_price );
		$end_price = str_replace( '.', '', $end_price );
		$multiply = str_replace( '.', '', $multiply );
		$multiplier = $multiply * 20;
		$e = $end_price / $start_price;
		$x = $multiplier / $start_price / 100;
		foreach ( range( 0, $e, $x ) as $y )
		{
			if ( $y == 0 )
			{
				$result[] = $start_price;
			}
			else
			{
				$result[] = $y * $start_price / 2 * 10;
			}
		}
		for ( $i = 0; $i < count( $result ); $i++ )
		{
			if ( $result[ $i ] == $end_price )
			{
				break;
			}
			else
			{
				$price[ $result[ $i ] ] = ( $result[ $i + 1 ] == 0 ) ? $result[ $i ] * 2 : $result[ $i + 1 ];
			}
		}

		return $price;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'number_to_words' ) )
{
	/**
	 * Number To Words
	 *
	 * Convert Number To Words
	 *
	 * @param   int $number  Number
	 * @param   int $decimal Num of decimals
	 *
	 * @return  string
	 */
	function number_to_words( $number, $decimal = 4 )
	{
		$stext = array(
			"Nol",
			"Satu",
			"Dua",
			"Tiga",
			"Empat",
			"Lima",
			"Enam",
			"Tujuh",
			"Delapan",
			"Sembilan",
			"Sepuluh",
			"Sebelas",
		);
		$say = array(
			"Ribu",
			"Juta",
			"Milyar",
			"Triliun",
			"Biliun",
			// remember limitation of float
			"--apaan---"  ///setelah biliun namanya apa?
		);
		$w = "";
		if ( $number < 0 )
		{
			$w = "Minus ";
			//make positive
			$number *= -1;
		}
		$snumber = number_format( $number, $decimal, ",", "." );
		$strnumber = explode( ".", substr( $snumber, 0, strrpos( $snumber, "," ) ) );
		//parse decimalnya
		$decimal = substr( $snumber, strrpos( $snumber, "," ) + 1 );
		$isone = substr( $number, 0, 1 ) == 1;
		if ( count( $strnumber ) == 1 )
		{
			$number = $strnumber[ 0 ];
			switch ( strlen( $number ) )
			{
				case 1 :
				case 2 :
					if ( ! isset( $stext[ $strnumber[ 0 ] ] ) )
					{
						if ( $number < 19 )
						{
							$w .= $stext[ substr( $number, 1 ) ] . " Belas";
						}
						else
						{
							$w .= $stext[ substr( $number, 0, 1 ) ] . " Puluh " . ( intval( substr( $number, 1 ) ) == 0 ? "" : $stext[ substr( $number, 1 ) ] );
						}
					}
					else
					{
						$w .= $stext[ $strnumber[ 0 ] ];
					}
					break;
				case 3 :
					$w .= ( $isone ? "Seratus" : number_to_words( substr( $number, 0, 1 ) ) . " Ratus" ) . " " . ( intval( substr( $number, 1 ) ) == 0 ? "" : number_to_words( substr( $number, 1 ) ) );
					break;
				case 4 :
					$w .= ( $isone ? "Seribu" : number_to_words( substr( $number, 0, 1 ) ) . " Ribu" ) . " " . ( intval( substr( $number, 1 ) ) == 0 ? "" : number_to_words( substr( $number, 1 ) ) );
					break;
				default :
					break;
			}
		}
		else
		{
			$text = $say[ count( $strnumber ) - 2 ];
			$w = ( $isone && strlen( $strnumber[ 0 ] ) == 1 && count( $strnumber ) <= 3 ? "Se" . strtolower( $text ) : number_to_words( $strnumber[ 0 ] ) . ' ' . $text );
			array_shift( $strnumber );
			$i = count( $strnumber ) - 2;
			foreach ( $strnumber as $k => $v )
			{
				if ( intval( $v ) )
				{
					$w .= ' ' . number_to_words( $v ) . ' ' . ( $i >= 0 ? $say[ $i ] : "" );
				}
				$i--;
			}
		}
		$w = trim( $w );
		if ( $decimal = intval( $decimal ) )
		{
			$w .= " Koma " . number_to_words( $decimal );
		}

		return trim( $w );
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists( 'calculate' ) )
{
	/**
	 * Calculate
	 *
	 * Calculate from string
	 *
	 * @param   string $formula
	 *
	 * @return  string
	 */
	function calculate( $formula )
	{
		static $function_map = array(
			'floor'   => 'floor',
			'ceil'    => 'ceil',
			'round'   => 'round',
			'sin'     => 'sin',
			'cos'     => 'cos',
			'tan'     => 'tan',
			'asin'    => 'asin',
			'acos'    => 'acos',
			'atan'    => 'atan',
			'abs'     => 'abs',
			'log'     => 'log',
			'pi'      => 'pi',
			'exp'     => 'exp',
			'min'     => 'min',
			'max'     => 'max',
			'rand'    => 'rand',
			'fmod'    => 'fmod',
			'sqrt'    => 'sqrt',
			'deg2rad' => 'deg2rad',
			'rad2deg' => 'rad2deg',
		);

		// Remove any whitespace
		$formula = strtolower( preg_replace( '~\s+~', '', $formula ) );

		// Empty formula
		if ( $formula === '' )
		{
			trigger_error( 'Empty formula', E_USER_ERROR );

			return NULL;
		}

		// Illegal function
		$formula = preg_replace_callback( '~\b[a-z]\w*\b~', function ( $match ) use ( $function_map )
		{
			$function = $match[ 0 ];
			if ( ! isset( $function_map[ $function ] ) )
			{
				trigger_error( "Illegal function '{$match[0]}'", E_USER_ERROR );

				return '';
			}

			return $function_map[ $function ];
		}, $formula );

		// Invalid function calls
		if ( preg_match( '~[a-z]\w*(?![\(\w])~', $formula, $match ) > 0 )
		{
			trigger_error( "Invalid function call '{$match[0]}'", E_USER_ERROR );

			return NULL;
		}

		// Legal characters
		if ( preg_match( '~[^-+/%*&|<>!=.()0-9a-z,]~', $formula, $match ) > 0 )
		{
			trigger_error( "Illegal character '{$match[0]}'", E_USER_ERROR );

			return NULL;
		}

		return eval( "return({$formula});" );
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists( 'number_to_hertz' ) )
{
	/**
	 * Number To Hertz
	 *
	 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
	 *
	 * @param   int $num       Number
	 * @param   int $precision Num of precision
	 *
	 * @return  string
	 */
	function number_to_hertz( $num, $precision = 1 )
	{
		if ( $num >= 1000000000000 )
		{
			$num = round( $num / 1099511627776, $precision );
			$unit = 'THz';
		}
		elseif ( $num >= 1000000000 )
		{
			$num = round( $num / 1073741824, $precision );
			$unit = 'GHz';
		}
		elseif ( $num >= 1000000 )
		{
			$num = round( $num / 1048576, $precision );
			$unit = 'MHz';
		}
		elseif ( $num >= 1000 )
		{
			$num = round( $num / 1024, $precision );
			$unit = 'KHz';
		}
		else
		{
			$unit = 'Hz';

			return number_format( $num ) . ' ' . $unit;
		}

		return number_format( $num, '', $precision ) . ' ' . $unit;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'number_to_roman' ) )
{
	/**
	 * Number to Roman
	 *
	 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
	 *
	 * @param   int $num Number
	 *
	 * @return  string
	 */
	function number_to_roman( $num )
	{
		$romans = array(
			'M'  => 1000,
			'CM' => 900,
			'D'  => 500,
			'CD' => 400,
			'C'  => 100,
			'XC' => 90,
			'L'  => 50,
			'XL' => 40,
			'X'  => 10,
			'IX' => 9,
			'V'  => 5,
			'IV' => 4,
			'I'  => 1,
		);

		$return = '';

		while ( $num > 0 )
		{
			foreach ( $romans as $rom => $arb )
			{
				if ( $num >= $arb )
				{
					$num -= $arb;
					$return .= $rom;
					break;
				}
			}
		}

		return $return;
	}
}

if ( ! function_exists( 'number_shorten' ) )
{
	function number_shorten( $num, $precision = 0, $divisors = NULL )
	{
		// Setup default $divisors if not provided
		if ( ! isset( $divisors ) )
		{
			$divisors = array(
				pow( 1000, 0 ) => '', // 1000^0 == 1
				pow( 1000, 1 ) => 'K', // Thousand
				pow( 1000, 2 ) => 'M', // Million
				pow( 1000, 3 ) => 'B', // Billion
				pow( 1000, 4 ) => 'T', // Trillion
				pow( 1000, 5 ) => 'Qa', // Quadrillion
				pow( 1000, 6 ) => 'Qi', // Quintillion
			);
		}

		// Loop through each $divisor and find the
		// lowest amount that matches
		foreach ( $divisors as $divisor => $shorthand )
		{
			if ( $num < ( $divisor * 1000 ) )
			{
				// We found a match!
				break;
			}
		}

		// We found our match, or there were no matches.
		// Either way, use the last defined value for $divisor.
		return number_format( $num / $divisor, $precision ) . $shorthand;
	}
}

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
 * @license      http://opensource.org/licenses/MIT MIT License
 * @link         http://codeigniter.com
 * @since        Version 1.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Number Helpers
 *
 * @package        CodeIgniter
 * @subpackage     Helpers
 * @category       Helpers
 * @author         EllisLab Dev Team
 * @link           http://codeigniter.com/user_guide/helpers/number_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists( 'byte_format' ) )
{
	/**
	 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
	 *
	 * @param    mixed    will be cast as int
	 * @param    int
	 *
	 * @return    string
	 */
	function byte_format( $num, $precision = 1 )
	{
		\O2System::$language->load( 'number' );

		if ( $num >= 1000000000000 )
		{
			$num = round( $num / 1099511627776, $precision );
			$unit = \O2System::$language->line( 'TERABYTE_ABBR' );
		}
		elseif ( $num >= 1000000000 )
		{
			$num = round( $num / 1073741824, $precision );
			$unit = \O2System::$language->line( 'GIGABYTE_ABBR' );
		}
		elseif ( $num >= 1000000 )
		{
			$num = round( $num / 1048576, $precision );
			$unit = \O2System::$language->line( 'MEGABYTE_ABBR' );
		}
		elseif ( $num >= 1000 )
		{
			$num = round( $num / 1024, $precision );
			$unit = \O2System::$language->line( 'KILOBYTE_ABBR' );
		}
		else
		{
			$unit = \O2System::$language->line( 'BYTES' );

			return number_format( $num ) . ' ' . $unit;
		}

		return number_format( $num, $precision ) . ' ' . $unit;
	}
}
