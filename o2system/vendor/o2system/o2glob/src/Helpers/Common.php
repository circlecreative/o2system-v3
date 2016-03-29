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

if ( ! function_exists( 'is_php' ) )
{
	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param    string
	 *
	 * @return    bool    TRUE if the current version is $version or higher
	 */
	function is_php( $version )
	{
		static $_is_php;
		$version = (string) $version;

		if ( ! isset( $_is_php[ $version ] ) )
		{
			$_is_php[ $version ] = version_compare( PHP_VERSION, $version, '>=' );
		}

		return $_is_php[ $version ];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_really_writable' ) )
{
	/**
	 * Tests for file writability
	 *
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute. is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @link    https://bugs.php.net/bug.php?id=54709
	 *
	 * @param    string
	 *
	 * @return    void
	 */
	function is_really_writable( $file )
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if ( DIRECTORY_SEPARATOR === '/' && ( is_php( '5.4' ) || ! ini_get( 'safe_mode' ) ) )
		{
			return is_writable( $file );
		}

		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if ( is_dir( $file ) )
		{
			$file = rtrim( $file, '/' ) . '/' . md5( mt_rand() );
			if ( ( $fp = @fopen( $file, 'ab' ) ) === FALSE )
			{
				return FALSE;
			}

			fclose( $fp );
			@chmod( $file, 0777 );
			@unlink( $file );

			return TRUE;
		}
		elseif ( ! is_file( $file ) || ( $fp = @fopen( $file, 'ab' ) ) === FALSE )
		{
			return FALSE;
		}

		fclose( $fp );

		return TRUE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_https' ) )
{
	/**
	 * Is HTTPS?
	 *
	 * Determines if the application is accessed via an encrypted
	 * (HTTPS) connection.
	 *
	 * @return    bool
	 */
	function is_https()
	{
		if ( ! empty( $_SERVER[ 'HTTPS' ] ) && strtolower( $_SERVER[ 'HTTPS' ] ) !== 'off' )
		{
			return TRUE;
		}
		elseif ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] === 'https' )
		{
			return TRUE;
		}
		elseif ( ! empty( $_SERVER[ 'HTTP_FRONT_END_HTTPS' ] ) && strtolower( $_SERVER[ 'HTTP_FRONT_END_HTTPS' ] ) !== 'off' )
		{
			return TRUE;
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_cli' ) )
{
	/**
	 * Is CLI?
	 *
	 * Test to see if a request was made from the command line.
	 *
	 * @return    bool
	 */
	function is_cli()
	{
		return ( PHP_SAPI === 'cli' || defined( 'STDIN' ) );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'set_status_header' ) )
{
	/**
	 * Set HTTP Status Header
	 *
	 * @param    int    the status code
	 * @param    string
	 *
	 * @return    void
	 */
	function set_status_header( $code = 200, $description = '' )
	{
		( new \O2System\Glob\HttpStatusCode )->setResponse( $code, $description );
	}
}

// --------------------------------------------------------------------

if ( ! function_exists( 'remove_invisible_characters' ) )
{
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param    string
	 * @param    bool
	 *
	 * @return    string
	 */
	function remove_invisible_characters( $str, $url_encoded = TRUE )
	{
		$non_displayables = array();

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ( $url_encoded )
		{
			$non_displayables[] = '/%0[0-8bcef]/';    // url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';    // url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace( $non_displayables, '', $str, -1, $count );
		}
		while ( $count );

		return $str;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'html_escape' ) )
{
	/**
	 * Returns HTML escaped variable.
	 *
	 * @param    mixed $var           The input string or array of strings to be escaped.
	 * @param    bool  $double_encode $double_encode set to FALSE prevents escaping twice.
	 *
	 * @return    mixed            The escaped string or array of strings as a result.
	 */
	function html_escape( $var, $double_encode = TRUE )
	{
		if ( is_array( $var ) )
		{
			return array_map( 'html_escape', $var, array_fill( 0, count( $var ), $double_encode ) );
		}

		return htmlspecialchars( $var, ENT_QUOTES, \O2System::$config->charset, $double_encode );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( '_stringify_attributes' ) )
{
	/**
	 * Stringify attributes for use in HTML tags.
	 *
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 *
	 * @param    mixed    string, array, object
	 * @param    bool
	 *
	 * @return    string
	 */
	function _stringify_attributes( $attributes, $js = FALSE )
	{
		$atts = NULL;

		if ( empty( $attributes ) )
		{
			return $atts;
		}

		if ( is_string( $attributes ) )
		{
			return ' ' . $attributes;
		}

		$attributes = (array) $attributes;

		foreach ( $attributes as $key => $val )
		{
			$atts .= ( $js ) ? $key . '=' . $val . ',' : ' ' . $key . '="' . $val . '"';

		}


		return rtrim( $atts, ',' );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'function_usable' ) )
{
	/**
	 * Function usable
	 *
	 * Executes a function_exists() check, and if the Suhosin PHP
	 * extension is loaded - checks whether the function that is
	 * checked might be disabled in there as well.
	 *
	 * This is useful as function_exists() will return FALSE for
	 * functions disabled via the *disable_functions* php.ini
	 * setting, but not for *suhosin.executor.func.blacklist* and
	 * *suhosin.executor.disable_eval*. These settings will just
	 * terminate script execution if a disabled function is executed.
	 *
	 * The above described behavior turned out to be a bug in Suhosin,
	 * but even though a fix was commited for 0.9.34 on 2012-02-12,
	 * that version is yet to be released. This function will therefore
	 * be just temporary, but would probably be kept for a few years.
	 *
	 * @link    http://www.hardened-php.net/suhosin/
	 *
	 * @param    string $function_name Function to check for
	 *
	 * @return    bool    TRUE if the function exists and is safe to call,
	 *            FALSE otherwise.
	 */
	function function_usable( $function_name )
	{
		static $_suhosin_func_blacklist;

		if ( function_exists( $function_name ) )
		{
			if ( ! isset( $_suhosin_func_blacklist ) )
			{
				if ( extension_loaded( 'suhosin' ) )
				{
					$_suhosin_func_blacklist = explode( ',', trim( ini_get( 'suhosin.executor.func.blacklist' ) ) );

					if ( ! in_array( 'eval', $_suhosin_func_blacklist, TRUE ) && ini_get( 'suhosin.executor.disable_eval' ) )
					{
						$_suhosin_func_blacklist[] = 'eval';
					}
				}
				else
				{
					$_suhosin_func_blacklist = array();
				}
			}

			return ! in_array( $function_name, $_suhosin_func_blacklist, TRUE );
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'path_to_url' ) )
{
	function path_to_url( $path )
	{
		$path = str_replace( '/', DIRECTORY_SEPARATOR, $path );

		$base_url = is_https() ? 'https' : 'http';
		$base_url .= '://' . ( isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ] );

		// Add server port if needed
		$base_url .= $_SERVER[ 'SERVER_PORT' ] !== '80' ? ':' . $_SERVER[ 'SERVER_PORT' ] : '';

		// Add base path
		$base_url .= dirname( $_SERVER[ 'SCRIPT_NAME' ] );
		$base_url = str_replace( DIRECTORY_SEPARATOR, '/', $base_url );
		$base_url = trim( $base_url, '/' ) . '/';

		// Vendor directory
		$base_dir = explode( 'vendor' . DIRECTORY_SEPARATOR . 'o2system', __DIR__ );
		$base_dir = str_replace( [ 'o2system', '/' ], [ '', DIRECTORY_SEPARATOR ], $base_dir[ 0 ] );
		$base_dir = trim( $base_dir, DIRECTORY_SEPARATOR );

		$path = str_replace( [ $base_dir, DIRECTORY_SEPARATOR ], [ '', '/' ], $path );
		$path = trim( $path, '/' );

		$path = str_replace( DIRECTORY_SEPARATOR, '/', $path );
		$path = str_replace( '//', '/', $path );

		return trim( $base_url . $path, '/' );
	}
}

// ------------------------------------------------------------------------

/**
 * Return the namespace from class
 *
 * @param   string $path Class name
 *
 * @return  string  namespace class
 */
if ( ! function_exists( 'get_namespace' ) )
{
	function get_namespace( $class )
	{
		$class = is_object( $class ) ? get_class( $class ) : prepare_class_name( $class );

		$x_class = explode( '\\', $class );
		$x_class = array_slice( $x_class, 0, count( $x_class ) - 1 );

		return implode( '\\', $x_class ) . '\\';
	}
}

// ------------------------------------------------------------------------

/**
 * Return the class name of namespace class
 *
 * @param   string $path Class name
 *
 * @return  string  namespace class
 */
if ( ! function_exists( 'get_class_name' ) )
{
	function get_class_name( $class )
	{
		$class = is_object( $class ) ? get_class( $class ) : prepare_class_name( $class );
		$x_class = explode( '\\', $class );

		return end( $x_class );
	}
}

// ------------------------------------------------------------------------

/**
 * Returns Fixed Class Name according to O2System Class Name standards
 *
 * @param    $class    String class name
 *
 * @return   mixed
 */
if ( ! function_exists( 'prepare_class_name' ) )
{
	function prepare_class_name( $class )
	{
		$class = str_replace( [ '/', DIRECTORY_SEPARATOR, '.php' ], [ '\\', '\\', '' ], $class );
		$class = trim( $class );

		$segments = explode( '\\', $class );

		foreach ( $segments as $segment )
		{
			$patterns = array(
				'/[\s]+/',
				'/[-]+/',
				'/[_]+/',
			);

			$segment = preg_replace( $patterns, '_', $segment );

			if ( strpos( $segment, '_' ) !== FALSE )
			{
				$x_segment = array_map( 'ucfirst', explode( '_', $segment ) );
				$x_class[] = implode( '_', $x_segment );
			}
			else
			{
				$x_class[] = ucfirst( $segment );
			}
		}

		return implode( '\\', $x_class );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'prepare_filename' ) )
{
	/**
	 * Returns Fixed Class Name according to O2System Class Name standards
	 *
	 * @param    $class    String class name
	 *
	 * @return   mixed
	 */
	function prepare_filename( $filename )
	{
		$filename = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $filename );
		$filename = trim( $filename );

		$segments = explode( DIRECTORY_SEPARATOR, $filename );

		foreach ( $segments as $segment )
		{
			$patterns = array(
				'/[\s]+/',
				'/[-]+/',
				'/[_]+/',
			);

			$segment = preg_replace( $patterns, '_', $segment );

			if ( strpos( $segment, '_' ) !== FALSE )
			{
				$x_segment = array_map( 'ucfirst', explode( '_', $segment ) );
				$x_class[] = implode( '_', $x_segment );
			}
			else
			{
				$x_class[] = ucfirst( $segment );
			}
		}

		return implode( DIRECTORY_SEPARATOR, $x_class );
	}
}

// ------------------------------------------------------------------------

/**
 * Return a valid namespace class
 *
 * @param    string $class class name with namespace
 *
 * @return   string     valid string namespace
 */
if ( ! function_exists( 'prepare_namespace' ) )
{
	function prepare_namespace( $class, $get_namespace = TRUE )
	{
		return ( $get_namespace === TRUE ? get_namespace_class( $class ) : prepare_filename( $class ) );
	}
}

// ------------------------------------------------------------------------

/**
 * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
 *
 * @param    string $str String in camel case format
 *
 * @return    string            $str Translated into underscore format
 */
if ( ! function_exists( 'underscore' ) )
{
	function underscore( $string )
	{
		$string[ 0 ] = strtolower( $string[ 0 ] );
		$function = create_function( '$c', 'return "_" . strtolower($c[1]);' );

		return preg_replace_callback( '/([A-Z])/', $function, $string );
	}
}

if ( ! function_exists( 'parse_domain' ) )
{
	function parse_domain( $domain = NULL )
	{
		$domain = isset( $domain ) ? $domain : ( isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ] );

		$result = new \O2System\Glob\ArrayObject( array(
													  'scheme'     => is_https() ? 'https://' : 'http://',
			                                          'origin'     => $domain,
			                                          'host'       => NULL,
			                                          'www'        => FALSE,
			                                          'port'       => 80,
			                                          'domain'     => NULL,
			                                          'sub_domain' => NULL,
			                                          'tld'        => NULL,
		                                          ) );

		if ( strpos( $domain, ':' ) !== FALSE )
		{
			$x_domain = explode( ':', $domain );
			$domain = reset( $x_domain );
			$result[ 'port' ] = end( $x_domain );
		}

		$x_domain = explode( '.', $domain );

		$result[ 'www' ] = FALSE;
		if ( $x_domain[ 0 ] === 'www' )
		{
			$result[ 'www' ] = TRUE;
			array_shift( $x_domain );
		}

		$result[ 'tld' ] = array();
		foreach ( $x_domain as $key => $hostname )
		{
			if ( strlen( $hostname ) <= 3 AND $key >= 1 )
			{
				$result[ 'tld' ][] = $hostname;
			}
		}

		if ( empty( $result[ 'tld' ] ) )
		{
			$result[ 'tld' ][] = end( $x_domain );
		}

		$x_domain = array_diff( $x_domain, $result[ 'tld' ] );
		$x_domain = count($x_domain) == 0 ? $result[ 'tld' ] : $x_domain;

		if ( count( $x_domain ) == 1 )
		{
			$result[ 'sub_domain' ] = NULL;
			$result[ 'domain' ] = implode( '.', array_merge( $x_domain, $result[ 'tld' ] ) );
			$result[ 'host' ] = implode( '.', $x_domain );
		}
		else
		{
			$result[ 'sub_domain' ] = $x_domain[ 0 ];
			$x_domain = array_slice( $x_domain, 1 );
			$result[ 'domain' ] = implode( '.', array_merge( $x_domain, $result[ 'tld' ] ) );
			$result[ 'host' ] = implode( '.', $x_domain );
		}

		if( $result['tld'][0] === $result['domain'] )
		{
			$result[ 'tld' ] = NULL;
		}
		else
		{
			$result[ 'tld' ] = '.' . implode( '.', $result[ 'tld' ] );
		}


		return $result;
	}
}

if ( ! function_exists( 'parse_request' ) )
{
	function parse_request( $request = NULL )
	{

	}
}




