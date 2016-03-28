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

namespace O2System\Template\Drivers;

// ------------------------------------------------------------------------

use O2System\Glob\Interfaces\DriverInterface;
use O2System\Template\Exception;
use O2System\Template\ViewException;

/**
 * Template Assets
 *
 * Manage Template Assets Library
 *
 * @package       o2system
 * @subpackage    libraries
 * @category      Library Driver
 * @author        Steeven Andrian Salim
 * @link          http://o2system.center/framework/user-guide/library/template/drivers/assets.html
 */
final class View extends DriverInterface
{
	public function is_file( $view )
	{
		if ( is_file( $view ) )
		{
			return $view;
		}
		else
		{
			$paths = $this->_library->_paths;

			if ( isset( $this->_library->theme->active->realpath ) )
			{
				if ( class_exists( 'O2System', FALSE ) )
				{
					if ( \O2System::$active->offsetExists( 'module' ) )
					{
						$module_path = $this->_library->theme->active->realpath . strtolower( \O2System::$active[ 'module' ]->type ) . DIRECTORY_SEPARATOR . \O2System::$active[ 'module' ]->parameter . DIRECTORY_SEPARATOR;

						if ( is_dir( $module_path ) )
						{
							$paths[] = $module_path;
						}
					}
				}
			}

			$paths = array_reverse( $paths );

			foreach ( $paths as $path )
			{
				foreach ( $this->_library->parser->extensions as $extension )
				{
					if ( is_file( $path . 'views' . DIRECTORY_SEPARATOR . $view . $extension ) )
					{
						return $path . 'views' . DIRECTORY_SEPARATOR . $view . $extension;
						break;
					}
				}
			}
		}

		return FALSE;
	}

	public function load( $view, $vars = array(), $return = FALSE )
	{
		$vars = array_merge( $this->_library->_cached_vars, $vars );

		if ( $view_filepath = $this->is_file( $view ) )
		{
			if ( pathinfo( $view_filepath, PATHINFO_EXTENSION ) === 'php' )
			{
				$output = $this->_library->parser->parse_php( file_get_contents( $view_filepath ), $vars );
			}
			else
			{
				$output = $this->_library->parser->parse_string( file_get_contents( $view_filepath ), $vars );
			}

			if ( $return === FALSE )
			{
				return $this->_library->output->append_content( $output );
			}

			return $output;
		}

		throw new ViewException( 'TEMPLATE_VIEWUNABLETOLOCATEFILE', 12007, [ $view ] );
	}
}