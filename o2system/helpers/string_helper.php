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
 * String Helpers
 *
 * @package        O2System
 * @subpackage     helpers
 * @category       Helpers
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/helpers/string.html
 */
// ------------------------------------------------------------------------

if ( ! function_exists( 'str_capitalize' ) )
{
	/**
	 * String Capitalize
	 *
	 * Capitalize a string
	 *
	 * @param    string $string     Source String
	 * @param    bool   $first_word First Word Only
	 *
	 * @return    string
	 */
	function str_capitalize( $string, $first_word = FALSE )
	{
		if ( ! empty( $string ) )
		{
			if ( $first_word )
			{
				return ucfirst( $string );
			}
			else
			{
				$string = explode( ' ', $string );

				$strings = array_map( function ( $string )
				{
					return ucfirst( $string );
				}, $string );

				return implode( ' ', $strings );
			}
		}

		return NULL;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_default_str' ) )
{
	/**
	 * Is Default String
	 *
	 * Check if value is (not) the same as the default value
	 *
	 * @param    string $string  Source String
	 * @param    int    $default Default Value
	 *
	 * @return  string
	 */
	function is_default_str( $string, $default )
	{
		return ( $string == $default ) ? TRUE : FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_echo' ) )
{
	/**
	 * Show string or a formatted string with the value in it when the value exists
	 *
	 * @access    public
	 *
	 * @param    string
	 * @param    integer    number of repeats
	 *
	 * @return    string
	 */
	function str_echo( $value, $start_string = '', $end_string = '', $type = 'text' )
	{
		if ( ! empty( $value ) && $value !== '' )
		{
			switch ( $type )
			{
				default :
				case 'text' :
					echo $start_string . $value . $end_string;
					break;
				case 'email' :
					echo $start_string . safe_mailto( $value, $value ) . $end_string;
					break;
				case 'url' :
					if ( preg_match( '[http://]', $value ) or preg_match( '[https://]', $value ) )
					{
						$url = $value;
					}
					else
					{
						$url = 'http://' . $value;
					}

					echo $start_string . '<a href="' . $url . '" target="_blank">' . $value . '</a>' . $end_string;
					break;
				case 'yahoo_messenger' :
					echo $start_string . '<a href="ymsgr:sendim?' . $value . '">' . $value . '</a>' . $end_string;
					break;
				case 'msn_messenger' :
					echo $start_string . '<a href="msnim:chat?contact=' . $value . '">' . $value . '</a>' . $end_string;
					break;
				case 'gtalk_messenger' :
					echo $start_string . '<a href="googletalk:chat?jid=' . $value . '">' . $value . '</a>' . $end_string;
					break;
				case 'skype_messenger' :
					echo $start_string . '<a href="skype:' . $value . '?chat">' . $value . '</a>' . $end_string;
					break;
			}
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_email' ) )
{
	/**
	 * Save display email
	 *
	 * @access    public
	 *
	 * @param    string
	 * @param    integer    number of repeats
	 *
	 * @return    string
	 */
	function str_email( $string )
	{
		if ( ! empty( $string ) or $string != '' )
		{
			return str_replace( array(
				                    '.',
				                    '@',
			                    ), array(
				                    ' [dot] ',
				                    ' [at] ',
			                    ), trim( $string ) );
		}

		return $string;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_alphanumeric' ) )
{
	/**
	 * Remove Non AlphaNumeric Characters
	 *
	 * @access    public
	 *
	 * @param    string
	 * @param    integer    number of repeats
	 *
	 * @return    string
	 */
	function str_alphanumeric( $string )
	{
		if ( ! empty( $string ) or $string != '' )
		{
			$new_string = preg_replace( "/[^a-zA-Z0-9\s]/", "", $string );
		}

		return trim( $new_string );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_numeric' ) )
{
	/**
	 * Remove Non Numeric Characters
	 *
	 * @access    public
	 *
	 * @param    string
	 * @param    integer    number of repeats
	 *
	 * @return    string
	 */
	function str_numeric( $string )
	{
		if ( ! empty( $string ) or $string != '' )
		{
			$new_string = preg_replace( "/[^0-9\s]/", "", $string );

			return trim( $new_string );
		}

		return '';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_alias' ) )
{
	/**
	 * Create Alias From String
	 *
	 * @access    public
	 *
	 * @param    string
	 * @param    integer    number of repeats
	 *
	 * @return    string
	 */
	function str_alias( $string, $delimiter = '-' )
	{
		if ( ! empty( $string ) || $string != '' )
		{
			$string = strtolower( trim( $string ) );
			$string = preg_replace( "/[^A-Za-z0-9 ]/", '', $string );

			if ( $delimiter === '-' )
			{
				return preg_replace( '/[ _]+/', '-', $string );
			}
			elseif ( $delimiter === '_' )
			{
				return preg_replace( '/[ -]+/', '_', $string );
			}
		}

		return $string;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_readable' ) )
{

	/**
	 * Normalize Alias Into a Capitalize String
	 *
	 * @access    public
	 *
	 * @param    string
	 * @param    integer    number of repeats
	 *
	 * @return    string
	 */
	function str_readable( $string, $capitalize = FALSE )
	{
		if ( ! empty( $string ) or $string != '' )
		{
			$string = str_replace( '-', ' ', $string );
			$string = str_replace( '_', ' ', $string );
			if ( $capitalize == TRUE )
			{
				return capitalize( $string );
			}

			return $string;
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_truncate' ) )
{
	/**
	 * Truncates a string to a certain length
	 *
	 * @param string $string
	 * @param int    $limit
	 * @param string $ending
	 *
	 * @return string
	 */
	function str_truncate( $string, $limit = 25, $ending = '' )
	{
		if ( strlen( $string ) > $limit )
		{
			$string = strip_tags( $string );
			$string = substr( $string, 0, $limit );
			$string = substr( $string, 0, -( strlen( strrchr( $string, ' ' ) ) ) );
			$string = $string . $ending;
		}

		return $string;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_shorten' ) )
{
	/**
	 * If a string is too long, shorten it in the middle
	 *
	 * @param string $string
	 * @param int    $limit
	 *
	 * @return string
	 */
	function str_shorten( $string, $limit = 25 )
	{
		if ( strlen( $string ) > $limit )
		{
			$pre = substr( $string, 0, ( $limit / 2 ) );
			$suf = substr( $string, -( $limit / 2 ) );
			$string = $pre . ' ... ' . $suf;
		}

		return $string;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'str_obfuscate' ) )
{
	/**
	 * Scrambles the source of a string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	function str_obfuscate( $string )
	{
		$length = strlen( $string );
		$scrambled = '';
		for ( $i = 0; $i < $length; ++$i )
		{
			$scrambled .= '&#' . ord( substr( $string, $i, 1 ) ) . ';';
		}

		return $scrambled;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'symbols_to_entities' ) )
{

	/**
	 * Converts high-character symbols into their respective html entities.
	 *
	 * @return string
	 *
	 * @param  string $string
	 */
	function symbols_to_entities( $string )
	{
		static $symbols = array(
			'‚', 'ƒ', '"', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹', 'Œ', "'", "'", '"', '"', '•', '–', '—', '˜',
			'™', 'š', '›', 'œ', 'Ÿ', '€', 'Æ', 'Á', 'Â', 'À', 'Å', 'Ã', 'Ä', 'Ç',
			'Ð',
			'É',
			'Ê',
			'È',
			'Ë',
			'Í',
			'Î',
			'Ì',
			'Ï',
			'Ñ',
			'Ó',
			'Ô',
			'Ò',
			'Ø',
			'Õ',
			'Ö',
			'Þ',
			'Ú',
			'Û',
			'Ù',
			'Ü',
			'Ý',
			'á',
			'â',
			'æ',
			'à',
			'å',
			'ã',
			'ä',
			'ç',
			'é',
			'ê',
			'è',
			'ð',
			'ë',
			'í',
			'î',
			'ì',
			'ï',
			'ñ',
			'ó',
			'ô',
			'ò',
			'ø',
			'õ',
			'ö',
			'ß',
			'þ',
			'ú',
			'û',
			'ù',
			'ü',
			'ý',
			'ÿ',
			'¡',
			'£',
			'¤',
			'¥',
			'¦',
			'§',
			'¨',
			'©',
			'ª',
			'«',
			'¬',
			'­',
			'®',
			'¯',
			'°',
			'±',
			'²',
			'³',
			'´',
			'µ',
			'¶',
			'·',
			'¸',
			'¹',
			'º',
			'»',
			'¼',
			'½',
			'¾',
			'¿',
			'×',
			'÷',
			'¢',
			'…',
			'µ',
		);
		static $entities = array(
			'&#8218;',
			'&#402;',
			'&#8222;',
			'&#8230;',
			'&#8224;',
			'&#8225;',
			'&#710;',
			'&#8240;',
			'&#352;',
			'&#8249;',
			'&#338;',
			'&#8216;',
			'&#8217;',
			'&#8220;',
			'&#8221;',
			'&#8226;',
			'&#8211;',
			'&#8212;',
			'&#732;',
			'&#8482;',
			'&#353;',
			'&#8250;',
			'&#339;',
			'&#376;',
			'&#8364;',
			'&aelig;',
			'&aacute;',
			'&acirc;',
			'&agrave;',
			'&aring;',
			'&atilde;',
			'&auml;',
			'&ccedil;',
			'&eth;',
			'&eacute;',
			'&ecirc;',
			'&egrave;',
			'&euml;',
			'&iacute;',
			'&icirc;',
			'&igrave;',
			'&iuml;',
			'&ntilde;',
			'&oacute;',
			'&ocirc;',
			'&ograve;',
			'&oslash;',
			'&otilde;',
			'&ouml;',
			'&thorn;',
			'&uacute;',
			'&ucirc;',
			'&ugrave;',
			'&uuml;',
			'&yacute;',
			'&aacute;',
			'&acirc;',
			'&aelig;',
			'&agrave;',
			'&aring;',
			'&atilde;',
			'&auml;',
			'&ccedil;',
			'&eacute;',
			'&ecirc;',
			'&egrave;',
			'&eth;',
			'&euml;',
			'&iacute;',
			'&icirc;',
			'&igrave;',
			'&iuml;',
			'&ntilde;',
			'&oacute;',
			'&ocirc;',
			'&ograve;',
			'&oslash;',
			'&otilde;',
			'&ouml;',
			'&szlig;',
			'&thorn;',
			'&uacute;',
			'&ucirc;',
			'&ugrave;',
			'&uuml;',
			'&yacute;',
			'&yuml;',
			'&iexcl;',
			'&pound;',
			'&curren;',
			'&yen;',
			'&brvbar;',
			'&sect;',
			'&uml;',
			'&copy;',
			'&ordf;',
			'&laquo;',
			'&not;',
			'&shy;',
			'&reg;',
			'&macr;',
			'&deg;',
			'&plusmn;',
			'&sup2;',
			'&sup3;',
			'&acute;',
			'&micro;',
			'&para;',
			'&middot;',
			'&cedil;',
			'&sup1;',
			'&ordm;',
			'&raquo;',
			'&frac14;',
			'&frac12;',
			'&frac34;',
			'&iquest;',
			'&times;',
			'&divide;',
			'&cent;',
			'...',
			'&micro;',
		);

		return str_replace( $symbols, $entities, $string );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_parse_string' ) )
{
	/**
	 * Is Parse String
	 *
	 * @return string
	 *
	 * @params string $string
	 */
	function is_parse_string( $string )
	{
		if ( preg_match( '[=]', $string ) )
		{
			return TRUE;
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'parse_string' ) )
{
	/**
	 * Is Parse String
	 *
	 * @return string
	 *
	 * @params string $string
	 */
	function parse_string( $string )
	{
		if ( preg_match( '[=]', $string ) )
		{
			parse_str( html_entity_decode( $string ), $string );
		}

		return $string;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'mb_stripos_all' ) )
{
	/**
	 * mb_stripos all occurences
	 * based on http://www.php.net/manual/en/function.strpos.php#87061
	 *
	 * Find all occurrences of a needle in a haystack
	 *
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return array or false
	 */
	function mb_stripos_all( $haystack, $needle )
	{

		$s = 0;
		$i = 0;

		while ( is_integer( $i ) )
		{

			$i = mb_stripos( $haystack, $needle, $s );

			if ( is_integer( $i ) )
			{
				$aStrPos[] = $i;
				$s = $i + mbStrlen( $needle );
			}
		}

		if ( isset( $aStrPos ) )
		{
			return $aStrPos;
		}
		else
		{
			return FALSE;
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_serialized' ) )
{
	/**
	 * Is Serialized
	 *
	 * Check is the string is serialized array
	 *
	 * @param   string $string Source string
	 *
	 * @return  bool
	 */
	function is_serialized( $string )
	{
		if ( ! is_string( $string ) )
		{
			return FALSE;
		}
		if ( trim( $string ) == '' )
		{
			return FALSE;
		}
		if ( preg_match( "/^(i|s|a|o|d)(.*);/si", $string ) )
		{
			$is_valid = @unserialize( $string );

			if ( empty( $is_valid ) )
			{
				return FALSE;
			}

			return TRUE;
		}

		return FALSE;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'is_json' ) )
{
	/**
	 * Is JSON
	 *
	 * Check is the string is json array or object
	 *
	 * @item    string or array
	 * @return  boolean (true or false)
	 */
	function is_json( $string )
	{
		// make sure provided input is of type string
		if ( ! is_string( $string ) )
		{
			return FALSE;
		}
		// trim white spaces
		$string = trim( $string );
		// get first character
		$first_char = substr( $string, 0, 1 );
		// get last character
		$last_char = substr( $string, -1 );
		// check if there is a first and last character
		if ( ! $first_char || ! $last_char )
		{
			return FALSE;
		}
		// make sure first character is either { or [
		if ( $first_char !== '{' && $first_char !== '[' )
		{
			return FALSE;
		}
		// make sure last character is either } or ]
		if ( $last_char !== '}' && $last_char !== ']' )
		{
			return FALSE;
		}
		// let's leave the rest to PHP.
		// try to decode string
		json_decode( $string );
		// check if error occurred
		$is_valid = json_last_error() === JSON_ERROR_NONE;

		return $is_valid;
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
 * CodeIgniter String Helpers
 *
 * @package        CodeIgniter
 * @subpackage     Helpers
 * @category       Helpers
 * @author         EllisLab Dev Team
 * @link           http://codeigniter.com/user_guide/helpers/string_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists( 'strip_slashes' ) )
{
	/**
	 * Strip Slashes
	 *
	 * Removes slashes contained in a string or in an array
	 *
	 * @param    mixed    string or array
	 *
	 * @return    mixed    string or array
	 */
	function strip_slashes( $str )
	{
		if ( ! is_array( $str ) )
		{
			return stripslashes( $str );
		}

		foreach ( $str as $key => $val )
		{
			$str[ $key ] = strip_slashes( $val );
		}

		return $str;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'strip_quotes' ) )
{
	/**
	 * Strip Quotes
	 *
	 * Removes single and double quotes from a string
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	function strip_quotes( $str )
	{
		return str_replace( array( '"', "'" ), '', $str );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'quotes_to_entities' ) )
{
	/**
	 * Quotes to Entities
	 *
	 * Converts single and double quotes to entities
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	function quotes_to_entities( $str )
	{
		return str_replace( array( "\'", "\"", "'", '"' ), array( "&#39;", "&quot;", "&#39;", "&quot;" ), $str );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'reduce_double_slashes' ) )
{
	/**
	 * Reduce Double Slashes
	 *
	 * Converts double slashes in a string to a single slash,
	 * except those found in http://
	 *
	 * http://www.some-site.com//index.php
	 *
	 * becomes:
	 *
	 * http://www.some-site.com/index.php
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	function reduce_double_slashes( $str )
	{
		return preg_replace( '#(^|[^:])//+#', '\\1/', $str );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'reduce_multiples' ) )
{
	/**
	 * Reduce Multiples
	 *
	 * Reduces multiple instances of a particular character.  Example:
	 *
	 * Fred, Bill,, Joe, Jimmy
	 *
	 * becomes:
	 *
	 * Fred, Bill, Joe, Jimmy
	 *
	 * @param    string
	 * @param    string    the character you wish to reduce
	 * @param    bool      TRUE/FALSE - whether to trim the character from the beginning/end
	 *
	 * @return    string
	 */
	function reduce_multiples( $str, $character = ',', $trim = FALSE )
	{
		$str = preg_replace( '#' . preg_quote( $character, '#' ) . '{2,}#', $character, $str );

		return ( $trim === TRUE ) ? trim( $str, $character ) : $str;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'random_string' ) )
{
	/**
	 * Create a Random String
	 *
	 * Useful for generating passwords or hashes.
	 *
	 * @param    string    type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
	 * @param    int       number of characters
	 *
	 * @return    string
	 */
	function random_string( $type = 'alnum', $len = 8 )
	{
		switch ( $type )
		{
			case 'basic':
				return mt_rand();
			case 'alnum':
			case 'numeric':
			case 'nozero':
			case 'alpha':
				switch ( $type )
				{
					case 'alpha':
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric':
						$pool = '0123456789';
						break;
					case 'nozero':
						$pool = '123456789';
						break;
				}

				return substr( str_shuffle( str_repeat( $pool, ceil( $len / strlen( $pool ) ) ) ), 0, $len );
			case 'unique': // todo: remove in 3.1+
			case 'md5':
				return md5( uniqid( mt_rand() ) );
			case 'encrypt': // todo: remove in 3.1+
			case 'sha1':
				return sha1( uniqid( mt_rand(), TRUE ) );
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'increment_string' ) )
{
	/**
	 * Add's _1 to a string or increment the ending number to allow _2, _3, etc
	 *
	 * @param    string    required
	 * @param    string    What should the duplicate number be appended with
	 * @param    string    Which number should be used for the first dupe increment
	 *
	 * @return    string
	 */
	function increment_string( $str, $separator = '_', $first = 1 )
	{
		preg_match( '/(.+)' . $separator . '([0-9]+)$/', $str, $match );

		return isset( $match[ 2 ] ) ? $match[ 1 ] . $separator . ( $match[ 2 ] + 1 ) : $str . $separator . $first;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'alternator' ) )
{
	/**
	 * Alternator
	 *
	 * Allows strings to be alternated. See docs...
	 *
	 * @param    string (as many parameters as needed)
	 *
	 * @return    string
	 */
	function alternator( $args )
	{
		static $i;

		if ( func_num_args() === 0 )
		{
			$i = 0;

			return '';
		}
		$args = func_get_args();

		return $args[ ( $i++ % count( $args ) ) ];
	}
}