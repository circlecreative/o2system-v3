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

if ( ! function_exists( 'http_authorization_parse' ) )
{
	/**
	 * HTTP Authorization Parse
	 *
	 * @param   string  $string
	 * @param   string  $identifier
	 * @param           bool    whether or not the content is an image file
	 *
	 * @return  bool|array
	 */
	function http_authorization_parse( $string, $identifier )
	{
		$string = trim( $string );

		if ( preg_match( '[^' . $identifier . ']', $string ) )
		{
			$string = trim( str_replace( [ $identifier, ',' ], [ '', PHP_EOL ], $string ) );
			$result = parse_ini_string( $string );

			if ( is_array( $result ) )
			{
				return new \O2System\Glob\ArrayObject( $result );
			}
		}

		return FALSE;
	}
}

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

/**
 * CodeIgniter Security Helpers
 *
 * @package        CodeIgniter
 * @subpackage     Helpers
 * @category       Helpers
 * @author         EllisLab Dev Team
 * @link           http://codeigniter.com/user_guide/helpers/security_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists( 'xss_clean' ) )
{
	/**
	 * XSS Filtering
	 *
	 * @param    string
	 * @param    bool    whether or not the content is an image file
	 *
	 * @return    string
	 */
	function xss_clean( $str, $is_image = FALSE )
	{
		return O2System::Security()->xss_clean( $str, $is_image );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'sanitize_filename' ) )
{
	/**
	 * Sanitize Filename
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	function sanitize_filename( $filename )
	{
		return O2System::Security()->sanitize_filename( $filename );
	}
}

// --------------------------------------------------------------------

if ( ! function_exists( 'strip_image_tags' ) )
{
	/**
	 * Strip Image Tags
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	function strip_image_tags( $str )
	{
		return O2System::Security()->strip_image_tags( $str );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'encode_php_tags' ) )
{
	/**
	 * Convert PHP tags to entities
	 *
	 * @param    string
	 *
	 * @return    string
	 */
	function encode_php_tags( $str )
	{
		return str_replace( array( '<?', '?>' ), array( '&lt;?', '?&gt;' ), $str );
	}
}
