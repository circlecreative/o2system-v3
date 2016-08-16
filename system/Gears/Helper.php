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
 * @package     O2System
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, .
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * System Developer
 *
 * Add developer functions and class.
 *
 * @package        O2System
 * @subpackage     system/core
 * @category       Developer
 * @author         Steeven Andrian Salim
 * @link           http://circle-creative.com/products/o2system/user-guide/core/developer.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists( 'print_out' ) )
{
	/**
	 * Print Out
	 *
	 * Gear up developer to echo any type of variables to browser screen
	 *
	 * @uses \O2System\Gears\Output::printScreen()
	 *
	 * @param mixed $vars string|array|object|integer|boolean
	 * @param bool  $halt set FALSE to disabled halt output
	 */
	function print_out( $vars, $halt = TRUE )
	{
		O2System\Gears\Output::printScreen( $vars, $halt );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'print_line' ) )
{
	/**
	 * Print Line
	 *
	 * Gear up developer to print line by line any type of variables
	 * to browser screen from anywhere in source code
	 *
	 * @uses \O2System\Gears\Output::printLine()
	 *
	 * @param mixed $line       string|array|object|integer|boolean
	 * @param mixed $halt       set TRUE to halt output
	 *                          set (string) FLUSH to flush previous sets of lines output
	 */
	function print_line( $line = '', $halt = FALSE )
	{
		O2System\Gears\Output::printLine( $line, $halt );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'print_code' ) )
{
	/**
	 * Print Code
	 *
	 * Gear up developer to echo output with <pre> tag to browser screen
	 *
	 * @uses \O2System\Gears\Output::printCode()
	 *
	 * @param mixed $vars string|array|object|integer|boolean
	 * @param mixed $halt set FALSE to disabled halt output
	 */
	function print_code( $vars, $halt = FALSE )
	{
		O2System\Gears\Output::printCode( $vars, $halt );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'print_dump' ) )
{
	/**
	 * Print Code
	 *
	 * Gear up developer to do var_dump output with <pre> tag to browser screen
	 *
	 * @uses \O2System\Gears\Output::printDump()
	 *
	 * @param mixed $vars string|array|object|integer|boolean
	 * @param mixed $halt set FALSE to disabled halt output
	 */
	function print_dump( $vars, $halt = TRUE )
	{
		O2System\Gears\Output::printDump( $vars, $halt );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'print_json' ) )
{
	/**
	 * Print JSON
	 *
	 * Gear up developer to do print_out output in json format to browser screen
	 *
	 * @uses \O2System\Gears\Output::printJSON()
	 *
	 * @param mixed $vars   string|array|object|integer|boolean
	 * @param mixed $option bool|integer JSON Encode Option
	 * @param mixed $halt   bool set FALSE to disabled halt output
	 */
	function print_json( $vars, $option = NULL, $halt = TRUE )
	{
		O2System\Gears\Output::printJSON( $vars, $option, $halt );
	}
}

if ( ! function_exists( 'console' ) )
{
	/**
	 * Console
	 *
	 * Gear up developer to do send output to browser console
	 *
	 * @uses \O2System\Gears\Output::printConsole()
	 *
	 * @param string $title output title
	 * @param mixed  $vars  string|array|object|integer|boolean
	 * @param int    $type  \O2System\Gears\Output\Console type
	 */
	function console( $title, $vars = [ ], $type = \O2System\Gears\Console::LOG )
	{
		O2System\Gears\Output::printConsole( $title, $vars, $type );
	}
}

if ( ! function_exists( 'pre_open' ) )
{
	function pre_open( $attr = [ ] )
	{
		echo '<pre>';
	}
}

if ( ! function_exists( 'pre_line' ) )
{
	function pre_line( $line, $printR = FALSE )
	{
		if ( is_array( $line ) )
		{
			if ( $printR === FALSE )
			{
				echo implode( PHP_EOL, $line );
			}
			else
			{
				print_r( $line );
			}
		}
		else
		{
			echo $line . PHP_EOL;
		}
	}
}

if ( ! function_exists( 'pre_close' ) )
{
	function pre_close( $halt = FALSE )
	{
		echo '</pre>';

		if ( $halt )
		{
			die;
		}
	}
}