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

use O2System\Glob\ArrayObject;
use O2System\Parser\Interfaces\Driver;

/**
 * Latte Engine Adapter
 *
 * Parser Adapter for Latte Engine
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
class Latte extends Driver
{
	/**
	 * Static Engine Object
	 *
	 * @access  private
	 * @var  Engine Object
	 */
	private static $_engine;

	/**
	 * List of possible view file extensions
	 *
	 * @access  public
	 *
	 * @type array
	 */
	public $extensions = [ '.php', '.html', '.tpl' ];

	/**
	 * Setup Engine
	 *
	 * @param   $settings   Template Config
	 *
	 * @access  public
	 * @return  Parser Engine Adapter Object
	 */
	public function setup( $settings = [ ] )
	{
		static::$_engine = new ArrayObject(
			[
				'security' => new ArrayObject( $settings[ 'security' ] ),
			]
		);
	}

	/**
	 * Parse String
	 *
	 * @param   string   String Source Code
	 * @param   array    Array of variables data to be parsed
	 *
	 * @access  public
	 * @return  string  Parse Output Result
	 */
	public function parseString( $string, $vars = [ ] )
	{
		if ( static::$_engine->security->php_handling === FALSE )
		{
			$string = preg_replace( '/<\\?.*(\\?>|$)/Us', '', $string );
		}

		if ( static::$_engine->security->allow_super_globals === FALSE )
		{
			$string = str_replace(
				[
					'{$GLOBALS}',
					'{$GLOBALS[%%]}',
					'{$_SERVER}',
					'{$_SERVER[%%]}',
					'{$_GET}',
					'{$_GET[%%]}',
					'{$_POST}',
					'{$_POST[%%]}',
					'{$_FILES}',
					'{$_FILES[%%]}',
					'{$_COOKIE}',
					'{$_COOKIE[%%]}',
					'{$_SESSION}',
					'{$_SESSION[%%]}',
					'{$_REQUEST}',
					'{$_REQUEST[%%]}',
					'{$_ENV}',
					'{$_ENV[%%]}',
				], '', $string
			);
		}

		// php logical codes
		$logical_codes = [
			'{if(%%)}'     => '<?php if(\1): ?>',
			'{elseif(%%)}' => '<?php elseif(\1): ?>',
			'{/if}'        => '<?php endif; ?>',
			'{else}'       => '<?php else: ?>',
			'{continue}'   => '<?php continue; ?>',
			'{break}'      => '<?php break; ?>',

		];

		// php loop codes
		$loop_codes = [
			'{for(%%)}'     => '<?php for(\1): ?>',
			'{foreach(%%)}' => '<?php foreach(\1): ?>',
			'{while(%%)}'   => '<?php while(\1): ?>',
			'{/for}'        => '<?php endfor; ?>',
			'{/foreach}'    => '<?php endforeach; ?>',
			'{/while}'      => '<?php endwhile; ?>',
		];

		// php function codes
		if ( is_array( static::$_engine->security->allowed_modifiers ) )
		{
			foreach ( static::$_engine->security->allowed_modifiers as $function_name )
			{
				$functions_codes[ '{' . $function_name . '(%%)}' ] = '<?php echo ' . $function_name . '(\1); ?>';
			}
		}
		elseif ( static::$_engine->security->allowed_modifiers === FALSE )
		{
			$functions_codes = [
				'{%%(%%)}' => '',
			];
		}
		else
		{
			$functions_codes = [
				'{%%(%%)}' => '<?php echo \1(\2); ?>',
			];
		}

		// php variables codes
		$variables_codes = [
			'{$%%->%%}'  => '<?php echo $\1->\2; ?>',
			'{$%%[%%]}'  => '<?php echo $\1[\'\2\']; ?>',
			'{$%%.%%}'   => '<?php echo $\1[\'\2\']; ?>',
			'{$%% = %%}' => '<?php $\1 = \2; ?>',
			'{$%%++}'    => '<?php $\1++; ?>',
			'{$%%--}'    => '<?php $\1--; ?>',
			'{$%%}'      => '<?php echo $\1; ?>',
			'{/*}'       => '<?php /*',
			'{*/}'       => '*/ ?>',
		];

		if ( static::$_engine->security->allow_constants === TRUE )
		{
			$constants_variables = get_defined_constants( TRUE );

			if ( ! empty( $constants_variables[ 'user' ] ) )
			{
				foreach ( $constants_variables[ 'user' ] as $constant => $value )
				{
					$variables_codes[ '{' . $constant . '}' ] = '<?php echo ' . $constant . '; ?>';
				}
			}
		}

		$php_codes = array_merge( $logical_codes, $loop_codes, $functions_codes, $variables_codes );

		foreach ( $php_codes as $tpl_code => $php_code )
		{
			$patterns[] = '#' . str_replace( '%%', '(.+)', preg_quote( $tpl_code, '#' ) ) . '#U';
			$replace[]  = $php_code;
		}

		/*replace our pseudo language in template with php code*/

		$string = preg_replace( $patterns, $replace, $string );

		if ( ! empty( $vars ) )
		{
			extract( $vars );
		}

		ob_start();
		echo @eval( '?>' . $string );
		$output = ob_get_contents();
		@ob_end_clean();

		return $output;
	}

	/**
	 * Register Plugin
	 *
	 * Registers a plugin for use in a template.
	 *
	 * @access  public
	 */
	public function registerPlugin()
	{

	}
}