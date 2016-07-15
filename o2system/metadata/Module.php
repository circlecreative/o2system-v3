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
 * @author         Circle Creative Developer Team
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Metadata;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\ArrayObject;

class Module extends ArrayObject
{
	public function __construct( $data = [ ] )
	{
		parent::__construct( (array) $data, \ArrayObject::ARRAY_AS_PROPS );
	}

	public function hasController( $segments, $hierarchy = FALSE, $return = FALSE )
	{
		if ( is_string( $segments ) )
		{
			$segments = explode( '/', $segments );
		}

		$modules[] = $this;

		if ( $hierarchy === TRUE )
		{
			$modules = array_merge( $modules, $this->getModuleParents() );
		}

		$sub_directory = '';
		foreach ( $modules as $module )
		{
			foreach ( $segments as $segment )
			{
				$segment       = str_replace( '-', '_', $segment );
				$directory     = ROOTPATH . $module->realpath . 'controllers' . DIRECTORY_SEPARATOR;
				$sub_directory = empty( $sub_directory ) ? '' : $sub_directory . DIRECTORY_SEPARATOR;

				if ( is_dir( $directory . $sub_directory . $segment ) AND
					! is_file( $directory . rtrim( $sub_directory, DIRECTORY_SEPARATOR ) . prepare_filename( $segment, '.php' ) )
				)
				{
					if ( is_file( $directory . $sub_directory . $segment . DIRECTORY_SEPARATOR . prepare_filename( next( $segments ), '.php' ) ) )
					{
						$sub_directory .= $segment;
					}
				}

				if ( is_file( $directory . rtrim( $sub_directory, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . prepare_filename( $segment, '.php' ) ) )
				{
					if ( $return )
					{
						return explode( '/', $module->segments );
					}

					return TRUE;
					break;
				}
			}
		}

		return FALSE;
	}

	public function getModuleParents()
	{
		return \O2System::$registry->getParents( $this );
	}
}