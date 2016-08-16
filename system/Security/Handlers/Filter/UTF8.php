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
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Security\Handlers\Filter;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/*
 * ------------------------------------------------------
 * Important charset-related stuff
 * ------------------------------------------------------
 *
 * Configure mbstring and/or iconv if they are enabled
 * and set MB_ENABLED and ICONV_ENABLED constants, so
 * that we don't repeatedly do extension_loaded() or
 * function_exists() calls.
 *
 * Note: UTF-8 class depends on this. It used to be done
 * in it's constructor, but it's _not_ class-specific.
 *
 */
$charset = strtoupper( \O2System::$config[ 'charset' ] );
ini_set( 'default_charset', $charset );

if ( extension_loaded( 'mbstring' ) )
{
	define( 'MB_ENABLED', TRUE );

	// mbstring.internal_encoding is deprecated starting with PHP 5.6
	// and it's usage triggers E_DEPRECATED messages.
	if ( is_php( '5.6', '<=' ) )
	{
		@ini_set( 'mbstring.internal_encoding', $charset );
	}

	// This is required for mb_convert_encoding() to strip invalid characters.
	// That's utilized by UTF8 Class, but it's also done for consistency with iconv.
	mb_substitute_character( 'none' );
}
else
{
	define( 'MB_ENABLED', FALSE );
}

// There's an ICONV_IMPL constant, but the PHP manual says that using
// iconv's predefined constants is "strongly discouraged".
if ( extension_loaded( 'iconv' ) )
{
	define( 'ICONV_ENABLED', TRUE );

	// iconv.internal_encoding is deprecated starting with PHP 5.6
	// and it's usage triggers E_DEPRECATED messages.

	if ( is_php( '5.6', '<=' ) )
	{
		@ini_set( 'iconv.internal_encoding', $charset );
	}
}
else
{
	define( 'ICONV_ENABLED', FALSE );
}

if ( is_php( '5.6' ) )
{
	ini_set( 'php.internal_encoding', $charset );
}

/**
 * UTF8 Class
 *
 * Based on CodeIgniter UTF8 Class
 *
 * Provides support for UTF-8 environments
 *
 * @package        O2System
 * @subpackage     Core
 * @category       UTF-8
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/utf8.html
 */
final class UTF8
{
	protected $isEnabled = FALSE;

	/**
	 * Class constructor
	 *
	 * Determines if UTF-8 support is to be enabled.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		if (
			defined( 'PREG_BAD_UTF8_ERROR' )                // PCRE must support UTF-8
			AND ( ICONV_ENABLED === TRUE || MB_ENABLED === TRUE )    // iconv or mbstring must be installed
			AND strtoupper( \O2System::$config[ 'charset' ] ) === 'UTF-8'    // Application charset must be UTF-8
		)
		{
			$this->isEnabled = TRUE;
			\O2System::$log->debug( 'LOG_DEBUG_UTF8_SUPPORT_ENABLED' );
		}
		else
		{
			$this->isEnabled = FALSE;
			\O2System::$log->debug( 'LOG_DEBUG_UTF8_SUPPORT_DISABLED' );
		}

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	// --------------------------------------------------------------------

	public function isEnabled()
	{
		return (bool) $this->isEnabled;
	}

	/**
	 * Clean UTF-8 strings
	 *
	 * Ensures strings contain only valid UTF-8 characters.
	 *
	 * @param    string $string String to clean
	 *
	 * @return    string
	 */
	public function cleanString( $string )
	{
		if ( $this->isAscii( $string ) === FALSE )
		{
			if ( MB_ENABLED )
			{
				$string = mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
			}
			elseif ( ICONV_ENABLED )
			{
				$string = @iconv( 'UTF-8', 'UTF-8//IGNORE', $string );
			}
		}

		return $string;
	}

	// --------------------------------------------------------------------

	/**
	 * Is ASCII?
	 *
	 * Tests if a string is standard 7-bit ASCII or not.
	 *
	 * @param    string $string String to check
	 *
	 * @return    bool
	 */
	public function isAscii( $string )
	{
		return ( preg_match( '/[^\x00-\x7F]/S', $string ) === 0 );
	}

	// --------------------------------------------------------------------

	/**
	 * Remove ASCII control characters
	 *
	 * Removes all ASCII control characters except horizontal tabs,
	 * line feeds, and carriage returns, as all others can cause
	 * problems in XML.
	 *
	 * @param    string $string String to clean
	 *
	 * @return    string
	 */
	public function safeAsciiForXML( $string )
	{
		return remove_invisible_characters( $string, FALSE );
	}

	// --------------------------------------------------------------------

	/**
	 * Convert to UTF-8
	 *
	 * Attempts to convert a string to UTF-8.
	 *
	 * @param    string $string   Input string
	 * @param    string $encoding Input encoding
	 *
	 * @return    string    $str encoded in UTF-8 or FALSE on failure
	 */
	public function convertString( $string, $encoding )
	{
		if ( MB_ENABLED )
		{
			return mb_convert_encoding( $string, 'UTF-8', $encoding );
		}
		elseif ( ICONV_ENABLED )
		{
			return @iconv( $encoding, 'UTF-8', $string );
		}

		return FALSE;
	}
}