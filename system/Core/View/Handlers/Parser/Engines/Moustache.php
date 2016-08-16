<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 4:44 PM
 */

namespace O2System\Core\View\Handlers\Parser\Engines;

use O2System\Core\Collectors\Config;
use O2System\Core\View\Handlers\Parser;
use O2System\Core\View\Handlers\Parser\Interfaces\Engine;

class Moustache implements Engine
{
	use Config;
	
	/**
	 * Initialize Engine
	 *
	 * @param   $config   Parser Config
	 *
	 * @access  public
	 * @return  Parser Engine Adapter Object
	 */
	public function initialize( $config = [ ] )
	{
		$this->setConfig( $config );
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
		if ( $this->config->allowedPhpScripts === FALSE )
		{
			$string = preg_replace( '/<\\?.*(\\?>|$)/Us', '', $string );
		}

		if ( $this->config->allowedPhpGlobals === FALSE )
		{
			$string = str_replace(
				[
					'{{$GLOBALS}}',
					'{{$GLOBALS[%%]}}',
					'{{$_SERVER}}',
					'{{$_SERVER[%%]}}',
					'{{$_GET}}',
					'{{$_GET[%%]}}',
					'{{$_POST}}',
					'{{$_POST[%%]}}',
					'{{$_FILES}}',
					'{{$_FILES[%%]}}',
					'{{$_COOKIE}}',
					'{{$_COOKIE[%%]}}',
					'{{$_SESSION}}',
					'{{$_SESSION[%%]}}',
					'{{$_REQUEST}}',
					'{{$_REQUEST[%%]}}',
					'{{$_ENV}}',
					'{{$_ENV[%%]}}',
				], '', $string
			);
		}

		// php logical codes
		$logical_codes = [
			'{{if(%%)}}'     => '<?php if(\1): ?>',
			'{{elseif(%%)}}' => '<?php elseif(\1): ?>',
			'{{/if}}'        => '<?php endif; ?>',
			'{{else}}'       => '<?php else: ?>',
		];

		// php loop codes
		$loop_codes = [
			'{{for(%%)}}'     => '<?php for(\1): ?>',
			'{{/for}}'        => '<?php endfor; ?>',
			'{{foreach(%%)}}' => '<?php foreach(\1): ?>',
			'{{/foreach}}'    => '<?php endforeach; ?>',
			'{{while(%%)}}'   => '<?php while(\1): ?>',
			'{{/while}}'      => '<?php endwhile; ?>',
			'{{continue}}'    => '<?php continue; ?>',
			'{{break}}'       => '<?php break; ?>',
		];

		// php function codes
		$functions_codes = [ ];
		if ( $this->config->allowedPhpFunctions === FALSE )
		{
			$functions_codes = [
				'{{%%(%%)}}' => '',
			];
		}
		elseif ( is_array( $this->config->allowedPhpFunctions ) AND count( $this->config->allowedPhpFunctions ) > 0 )
		{
			foreach ( $this->config->allowedPhpFunctions as $function_name )
			{
				$functions_codes[ '{{' . $function_name . '(%%)}}' ] = '<?php echo ' . $function_name . '(\1); ?>';
			}
		}
		else
		{
			$functions_codes = [
				'{{%%(%%)}}' => '<?php echo \1(\2); ?>',
			];
		}

		// php variables codes
		$variables_codes = [
			'{{$%%->%%}}'  => '<?php echo $\1->\2; ?>',
			'{{$%%[%%]}}'  => '<?php echo $\1[\'\2\']; ?>',
			'{{$%%.%%}}'   => '<?php echo $\1[\'\2\']; ?>',
			'{{$%% = %%}}' => '<?php $\1 = \2; ?>',
			'{{$%%++}}'    => '<?php $\1++; ?>',
			'{{$%%--}}'    => '<?php $\1--; ?>',
			'{{$%%}}'      => '<?php echo $\1; ?>',
			'{{/*}}'       => '<?php /*',
			'{{*/}}'       => '*/ ?>',
		];

		if ( $this->config->allowedPhpConstants === TRUE )
		{
			$constants_variables = get_defined_constants( TRUE );

			if ( ! empty( $constants_variables[ 'user' ] ) )
			{
				foreach ( $constants_variables[ 'user' ] as $constant => $value )
				{
					$variables_codes[ '{{' . $constant . '}}' ] = '<?php echo ' . $constant . '; ?>';
				}
			}
		}

		$php_codes = array_merge( $logical_codes, $loop_codes, $functions_codes, $variables_codes );

		$patterns = $replace = [ ];
		foreach ( $php_codes as $tpl_code => $php_code )
		{
			$patterns[] = '#' . str_replace( '%%', '(.+)', preg_quote( $tpl_code, '#' ) ) . '#U';
			$replace[]  = $php_code;
		}

		/*replace our pseudo language in template with php code*/
		$string = preg_replace( $patterns, $replace, $string );

		return \O2System::$view->parser->parsePhp( $string, $vars );
	}

	/**
	 * Register Plugin
	 *
	 * Registers a plugin for use in a Twig template.
	 *
	 * @access  public
	 */
	public function registerPlugin()
	{
		// nothing to do here with moustache
	}
}