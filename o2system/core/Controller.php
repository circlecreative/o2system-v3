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

use O2System\Glob\ArrayObject;
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
		$powered = new ArrayObject(
			[
				'system_name'    => SYSTEM_NAME,
				'system_version' => SYSTEM_VERSION,
				'proudly'        => 'Proudly powered by ' . SYSTEM_NAME . ' ' . SYSTEM_VERSION . ' &#8212; Open Source PHP Framework',
				'line'           => SYSTEM_NAME . ' ' . SYSTEM_VERSION . ' &#8212; Open Source PHP Framework',
				'url'            => 'https://www.o2system.io',
			] );

		$this->view->addVar( 'powered', $powered );

		// Set Default Template Metadata
		$this->view->metadata->addMeta( 'generator', $powered->line );

		// Load Applications Language
		\O2System::$language->load( DIR_APPLICATIONS );

		if ( $module = \O2System::$active[ 'modules' ]->current() )
		{
			\O2System::$language->load( $module->parameter );

			if ( is_file( $setting_filepath = $module->realpath . strtolower( $module->type ) . '.settings' ) )
			{
				$settings = json_decode( file_get_contents( $setting_filepath ), TRUE );

				if ( json_last_error() === JSON_ERROR_NONE )
				{
					if ( isset( $settings[ 'metadata' ] ) )
					{
						$this->view->metadata->addMeta( $settings[ 'metadata' ] );
					}

					if ( isset( $settings[ 'assets' ] ) )
					{
						$this->view->assets->addAssets( $settings[ 'assets' ], 'plugin' );
					}
				}
			}

			// Load Application Assets
			$this->view->assets->addAsset( 'applications' );

			// Load Module Assets
			foreach ( \O2System::$active[ 'modules' ] as $module )
			{
				$assets_files[] = $module->parameter;
			}

			$assets_files[] = $module->parameter;

			// Load Controller Assets
			if ( $module->parameter === \O2System::$active[ 'controller' ]->parameter )
			{
				if ( \O2System::$active[ 'controller' ]->method === '_route' )
				{
					$method         = empty( $params ) ? NULL : '-' . reset( $params );
					$assets_files[] = \O2System::$active[ 'controller' ]->parameter . $method;
				}
				else
				{
					$assets_files[] = \O2System::$active[ 'controller' ]->parameter . '-' . \O2System::$active[ 'controller' ]->method;
				}
			}
			else
			{
				$assets_files[] = $module->parameter . '-' . \O2System::$active[ 'controller' ]->parameter;

				if ( \O2System::$active[ 'controller' ]->method === '_route' )
				{
					$method         = empty( $params ) ? NULL : '-' . reset( $params );
					$assets_files[] = $module->parameter . '-' . \O2System::$active[ 'controller' ]->parameter . $method;
				}
				else
				{
					$assets_files[] = $module->parameter . '-' . \O2System::$active[ 'controller' ]->parameter . '-' . \O2System::$active[ 'controller' ]->method;
				}
			}

			$files = array_map(
				function ( $filename )
				{
					return str_replace( '_', '-', $filename );
				}, array_unique( $assets_files ) );

			$this->view->assets->addAssets( $files, 'custom' );

			// Load Module Model
			if ( empty( $this->{$module_model_object_name = strtolower( $module->type ) . '_model'} ) )
			{
				$model_classes = [
					rtrim( \O2System::$active[ 'namespaces' ]->current(), '\\' ) . '\\' . 'Core\\Model',
					rtrim( \O2System::$active[ 'namespaces' ]->current(), '\\' ) . '\\' . 'Models\\' . prepare_class_name( $module->parameter ),
				];

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
				}, [
					   \O2System::$active[ 'controller' ]->parameter,
					   \O2System::$active[ 'controller' ]->parameter . '-' . trim( \O2System::$active[ 'controller' ]->method, '_' ),
				   ] );

			$this->view->assets->addAssets( $files, 'custom' );
		}

		// Load Controller Language
		\O2System::$language->load( \O2System::$active[ 'controller' ]->parameter );

		// Load Controller Model
		if ( empty( $this->controller_model ) )
		{
			$model_classes = [
				rtrim( \O2System::$active[ 'namespaces' ]->current(), '\\' ) . '\\' . 'Models\\' . prepare_class_name( \O2System::$active[ 'controller' ]->parameter ),
				rtrim( \O2System::$active[ 'namespaces' ]->current(), '\\' ) . '\\' . 'Core\\Model',
			];

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
		if ( method_exists( $this, '_buildVars' ) )
		{
			$this->_buildVars();
		}
	}
}