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

namespace O2System;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\Interfaces\ControllerInterface;

/**
 * Application Controller Class
 *
 * @package        O2System
 * @subpackage     core
 * @category       Core Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/controller.html
 */
abstract class Controller extends ControllerInterface
{
	/**
	 * Controller Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();

		// Powered By Vars
		$powered = new \stdClass();
		$powered->system_name = SYSTEM_NAME;
		$powered->system_version = SYSTEM_VERSION;
		$powered->proudly = 'Proudly powered by ' . SYSTEM_NAME . ' ' . SYSTEM_VERSION . ' &#8212; Open Source PHP Framework';
		$powered->line = SYSTEM_NAME . ' ' . SYSTEM_VERSION . ' &#8212; Open Source PHP Framework';
		$powered->url = 'http://www.o2system.in';

		$this->view->add_var( 'powered', $powered );

		// Set Default Template Metadata
		$this->view->metadata->add_meta( 'generator', $powered->line );

		// Load Applications Language
		\O2System::$language->load( DIR_APPLICATIONS );

		if ( \O2System::$active->offsetExists( 'module' ) )
		{
			\O2System::$language->load( \O2System::$active[ 'module' ]->parameter );

			if ( is_file( $setting_filepath = \O2System::$active[ 'module' ]->realpath . strtolower( \O2System::$active[ 'module' ]->type ) . '.settings' ) )
			{
				$settings = json_decode( file_get_contents( $setting_filepath ), TRUE );

				if ( json_last_error() === JSON_ERROR_NONE )
				{
					if ( isset( $settings[ 'metadata' ] ) )
					{
						$this->view->metadata->add_meta( $settings[ 'metadata' ] );
					}

					if ( isset( $settings[ 'assets' ] ) )
					{
						$this->view->assets->add_assets( $settings[ 'assets' ], 'plugin' );
					}
				}
			}

			// Load Application Assets
			$this->view->assets->add_asset( 'applications' );

			// Load Module Assets
			foreach ( \O2System::$active['modules'] as $module )
			{
				$assets_files[] = $module->parameter;
			}

			$assets_files[] = \O2System::$active[ 'module' ]->parameter;

			// Load Controller Assets
			if ( \O2System::$active[ 'module' ]->parameter === \O2System::$active[ 'controller' ]->parameter )
			{
				if ( \O2System::$active[ 'controller' ]->method === '_route' )
				{
					$method = empty( $params ) ? NULL : '-' . reset( $params );
					$assets_files[] = \O2System::$active[ 'controller' ]->parameter . $method;
				}
				else
				{
					$assets_files[] = \O2System::$active[ 'controller' ]->parameter . '-' . \O2System::$active[ 'controller' ]->method;
				}
			}
			else
			{
				$assets_files[] = \O2System::$active[ 'module' ]->parameter . '-' . \O2System::$active[ 'controller' ]->parameter;

				if ( \O2System::$active[ 'controller' ]->method === '_route' )
				{
					$method = empty( $params ) ? NULL : '-' . reset( $params );
					$assets_files[] = \O2System::$active[ 'module' ]->parameter . '-' . \O2System::$active[ 'controller' ]->parameter . $method;
				}
				else
				{
					$assets_files[] = \O2System::$active[ 'module' ]->parameter . '-' . \O2System::$active[ 'controller' ]->parameter . '-' . \O2System::$active[ 'controller' ]->method;
				}
			}

			$files = array_map(
				function ( $filename )
				{
					return str_replace( '_', '-', $filename );
				}, array_unique( $assets_files ) );

			$this->view->assets->add_assets( $files, 'custom' );

			// Load Module Model
			if ( ! $this->__get( $module_model_object_name = strtolower( \O2System::$active[ 'module' ]->type ) . '_model' ) )
			{
				$model_classes = array(
					rtrim( \O2System::$active[ 'namespace' ], '\\' ) . '\\' . 'Core\\Model',
					rtrim( \O2System::$active[ 'namespace' ], '\\' ) . '\\' . 'Models\\' . ucfirst( \O2System::$active[ 'controller' ]->parameter ),
				);

				foreach ( $model_classes as $model_class )
				{
					if ( class_exists( $model_class ) )
					{
						$this->{$module_model_object_name} = new $model_class();
						break;
					}
				}
			}
		}
		else
		{
			$files = array_map(
				function ( $filename )
				{
					return str_replace( '_', '-', $filename );
				}, array(
					   \O2System::$active[ 'controller' ]->parameter,
					   \O2System::$active[ 'controller' ]->parameter . '-' . trim( \O2System::$active[ 'controller' ]->method, '_' ),
				   ) );

			$this->view->assets->add_assets( $files, 'custom' );
		}

		// Load Controller Language
		\O2System::$language->load( \O2System::$active[ 'controller' ]->parameter );

		// Load Controller Model
		if ( empty( $this->controller_model ) )
		{
			$model_classes = array(
				rtrim( \O2System::$active[ 'namespace' ], '\\' ) . '\\' . 'Models\\' . prepare_class_name( \O2System::$active[ 'controller' ]->parameter ),
				rtrim( \O2System::$active[ 'namespace' ], '\\' ) . '\\' . 'Core\\Model',
			);

			foreach ( $model_classes as $model_class )
			{
				if ( class_exists( $model_class ) )
				{
					$this->controller_model = new $model_class();
					break;
				}
			}
		}

		// Load Controller Variables
		if ( method_exists( $this, '_build_vars' ) )
		{
			$this->_build_vars();
		}
	}
}