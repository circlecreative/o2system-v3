<?php
/**
 * O2Parser
 *
 * An open source template engines driver for PHP 5.4+
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
 * @package     O2System
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2parser/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2parser.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Parser\Drivers;

// ------------------------------------------------------------------------

use O2System\Parser\Exception;
use O2System\Parser\Interfaces\Driver;

/**
 * Smarty Engine Adapter
 *
 * Parser Adapter for Smarty Engine
 *
 * @package       O2TED
 * @subpackage    drivers/Engine
 * @category      Adapter Class
 * @author        Steeven Andrian Salim
 * @copyright     Copyright (c) 2005 - 2014
 * @license       http://www.circle-creative.com/products/o2ted/license.html
 * @link          http://circle-creative.com
 *                http://o2system.center
 */
class Smarty extends Driver
{
	/**
	 * List of possible view file extensions
	 *
	 * @access  public
	 *
	 * @type array
	 */
	public $extensions = array( '.php', '.html', '.tpl' );

	/**
	 * Static Engine Object
	 *
	 * @access  private
	 * @var  Engine Object
	 */
	private static $_engine;

	// ------------------------------------------------------------------------

	/**
	 * Setup Engine
	 *
	 * @param   array $settings
	 *
	 * @return \O2System\Parser\Drivers\Parser Engine Adapter Object
	 * @throws \O2System\Parser\Exception
	 * @access  public
	 */
	public function setup( $settings = array() )
	{
		if ( ! class_exists( 'Smarty' ) )
		{
			throw new Exception( 'The Smarty Template Engine must be loaded to use Parser with Smarty Driver.' );
		}

		if ( ! isset( static::$_engine ) )
		{
			static::$_engine = new \Smarty();
		}

		$cache_path = $settings[ 'cache' ] . DIRECTORY_SEPARATOR . 'compiler' . DIRECTORY_SEPARATOR;
		$compiler_path = $settings[ 'cache' ] . DIRECTORY_SEPARATOR . 'compiler' . DIRECTORY_SEPARATOR;

		static::$_engine->setCompileDir( $cache_path );
		static::$_engine->setCacheDir( $compiler_path );
		static::$_engine->caching = FALSE;

		if ( isset( $settings[ 'security' ] ) )
		{
			$security = new \Smarty_Security( static::$_engine );

			if ( isset( $settings[ 'security' ][ 'php_handling' ] ) AND $settings[ 'security' ][ 'php_handling' ] === FALSE )
			{
				$security->php_handling = \Smarty::PHP_REMOVE;
			}

			if ( ! empty( $settings[ 'security' ][ 'allowed_modifiers' ] ) )
			{
				$security->allowed_modifiers = $settings[ 'security' ][ 'allowed_modifiers' ];
			}

			if ( isset( $settings[ 'security' ][ 'allow_constants' ] ) AND $settings[ 'security' ][ 'allow_constants' ] === FALSE )
			{
				$security->allow_constants = FALSE;
			}

			if ( isset( $settings[ 'security' ][ 'allow_super_globals' ] ) AND $settings[ 'security' ][ 'allow_super_globals' ] === FALSE )
			{
				$security->allow_super_globals = FALSE;
			}

			if ( isset( $settings[ 'security' ][ 'allowed_tags' ] ) AND $settings[ 'security' ][ 'allowed_tags' ] === FALSE )
			{
				$security->allowed_tags = FALSE;
			}

			//print_out($security);

			static::$_engine->enableSecurity( $security );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Parse String
	 *
	 * @param   string   String Source Code
	 * @param   array    Array of variables data to be parsed
	 *
	 * @access  public
	 * @return  string  Parse Output Result
	 */
	public function parse_string( $string, $vars = array() )
	{
		if ( ! is_string( $string ) ) return '';

		foreach ( $vars as $_assign_key => $_assign_value )
		{
			static::$_engine->assign( $_assign_key, $_assign_value );
		}

		return static::$_engine->fetch( 'string:' . $string );
	}

	// ------------------------------------------------------------------------

	/**
	 * Registers plugin to be used in templates
	 *
	 * @return  when the plugin tag is invalid
	 * @internal param string $type plugin type
	 * @internal param string $tag name of template tag
	 * @internal param callable $callback PHP callback to register
	 * @internal param bool $cacheable if true (default) this fuction is cachable
	 * @internal param array $cache_attr caching attributes if any
	 *
	 */
	public function register_plugin()
	{
		@list( $type, $tag, $callback, $cacheable, $cache_attr ) = func_get_args();

		return static::$_engine->registerPlugin( $type, $tag, $callback, $cacheable, $cache_attr );
	}
}