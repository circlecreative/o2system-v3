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
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Core;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Registry\Metadata\Module;
use O2System\Registry\Metadata\Plugin;
use O2System\Registry\Metadata\Widget;

/**
 * Hooks Class
 *
 * Provides a mechanism to extend the base system without hacking.
 *
 * @package       O2System
 * @subpackage    core
 * @category      Core Class
 * @author        Steeven Andrian Salim
 * @link          http://o2system.center/framework/user-guide/core/hooks.html
 */
final class Hooks
{
	/**
	 * List of all hooks set in config/hooks.php
	 *
	 * @type    array
	 */
	protected $_hooks;

	/**
	 * Array with class objects to use hooks methods
	 *
	 * @type array
	 */
	protected $_objects = [ ];

	/**
	 * In progress flag
	 *
	 * Determines whether hook is in progress, used to prevent infinte loops
	 *
	 * @type    bool
	 */
	protected static $_in_progress = FALSE;

	// --------------------------------------------------------------------

	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		// If hooks are not enabled in the config file
		// there is nothing else to do
		if ( \O2System::$config[ 'enable_hooks' ] === FALSE )
		{
			return;
		}

		$this->_hooks = \O2System::$config->load( 'hooks', TRUE );

		\O2System::$log->info( 'LOG_INFO_HOOKS_CLASS_INITIALIZED' );
	}

	// --------------------------------------------------------------------

	/**
	 * Call Hook
	 *
	 * Calls a particular hook. Called by O2System.php.
	 *
	 * @param    string $which Hook name
	 *
	 * @return    bool    TRUE on success or FALSE on failure
	 */
	public function call( $which = '' )
	{
		if ( ! \O2System::$config[ 'enable_hooks' ] || ! isset( $this->_hooks[ $which ] ) )
		{
			return FALSE;
		}

		if ( is_array( $this->_hooks[ $which ] ) && ! isset( $this->_hooks[ $which ][ 'function' ] ) )
		{
			foreach ( $this->_hooks[ $which ] as $val )
			{
				$this->_run( $val );
			}
		}
		else
		{
			$this->_run( $this->_hooks[ $which ] );
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Run Hook
	 *
	 * Runs a particular hook
	 *
	 * @param    array $data Hook details
	 *
	 * @return    bool    TRUE on success or FALSE on failure
	 */
	protected function _run( $data )
	{
		// Closures/lambda functions and array($object, 'method') callables
		if ( is_callable( $data ) )
		{
			is_array( $data ) ? $data[ 0 ]->{$data[ 1 ]}() : $data();

			return TRUE;
		}
		elseif ( ! is_array( $data ) )
		{
			return FALSE;
		}

		// -----------------------------------
		// Safety - Prevents run-away loops
		// -----------------------------------

		// If the script being called happens to have the same
		// hook call within it a loop can happen
		if ( self::$_in_progress === TRUE )
		{
			return;
		}

		// -----------------------------------
		// Set file path
		// -----------------------------------

		if ( isset( $data[ 'filepath' ], $data[ 'filename' ] ) )
		{
			$filepath = APPSPATH . $data[ 'filepath' ] . '/' . $data[ 'filename' ];

			if ( ! is_file( $filepath ) )
			{
				return FALSE;
			}
		}

		// Determine and class and/or function names
		$class    = empty( $data[ 'class' ] ) ? FALSE : $data[ 'class' ];
		$function = empty( $data[ 'function' ] ) ? FALSE : $data[ 'function' ];
		$params   = isset( $data[ 'params' ] ) ? $data[ 'params' ] : [ ];

		if ( empty( $function ) )
		{
			return FALSE;
		}

		// Set the _in_progress flag
		self::$_in_progress = TRUE;

		// Call the requested class and/or function
		if ( $class !== FALSE )
		{
			// The object is stored?
			if ( isset( $this->_objects[ $class ] ) )
			{
				if ( method_exists( $this->_objects[ $class ], $function ) )
				{
					call_user_func_array( [ $this->_objects[ $class ], $function ], $params );
				}
				else
				{
					return self::$_in_progress = FALSE;
				}
			}
			else
			{
				class_exists( $class, FALSE ) || require_once( $filepath );

				if ( ! class_exists( $class, FALSE ) || ! method_exists( $class, $function ) )
				{
					return self::$_in_progress = FALSE;
				}

				// Store the object and execute the method
				$this->_objects[ $class ] = new $class();
				call_user_func_array( [ $this->_objects[ $class ], $function ], $params );
			}
		}
		else
		{
			function_exists( $function ) || require_once( $filepath );

			if ( ! function_exists( $function ) )
			{
				return self::$_in_progress = FALSE;
			}

			$function( $params );
		}

		self::$_in_progress = FALSE;

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Hooks Modules Caller
	 *
	 * @param   array $modules
	 *
	 * @access  public
	 */
	public function __callModules( array $modules )
	{
		if ( count( $modules ) > 0 )
		{
			foreach ( $modules as $module )
			{
				if ( empty( \O2System::$request->modules[ $module->parameter ] ) )
				{
					$this->__callModule( modules );
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Hooks Module Caller
	 *
	 * @param   Module $module
	 *
	 * @access  public
	 */
	public function __callModule( Module $module )
	{
		if ( is_file( ROOTPATH . $module->realpath . 'core' . DIRECTORY_SEPARATOR . 'Controller.php' ) )
		{
			$controller_class = $module->namespace . 'Core\Controller';

			if ( class_exists( $controller_class ) )
			{
				$module_object           = new $controller_class();
				$module_object->metadata = $module;

				if ( is_file( ROOTPATH . $module->realpath . 'core' . DIRECTORY_SEPARATOR . 'Model.php' ) )
				{
					$model_class = $module->namespace . 'Core\Model';
				}
				elseif ( is_file( ROOTPATH . $module->realpath . 'models' . DIRECTORY_SEPARATOR . prepare_filename( $module->parameter ) ) )
				{
					$model_class = $module->namespace . 'Models\\' . studlycapcase( $module->parameter );
				}

				$module_object->model = new $model_class();

				if ( isset( $_POST[ $module->parameter ] ) )
				{
					$post = $_POST[ $module->parameter ];
					unset( $_POST );

					// Run Fieldset Process
					if ( method_exists( $module_object, '__postModuleProcess' ) )
					{
						$module_object->__postModuleProcess( $post );
					}
				}
				else
				{
					// Run Plugin Settings
					if ( method_exists( $module_object, '__hookModuleController' ) )
					{
						$module_object->__hookModuleController();
					}

					\O2System::$request->modules[ $module->parameter ] = $module_object;
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Hooks Plugins Caller
	 *
	 * @param   array $plugins
	 *
	 * @access  public
	 */
	public function __callPlugins( array $plugins )
	{
		if ( count( $plugins ) > 0 )
		{
			foreach ( $plugins as $plugin )
			{
				if ( empty( \O2System::$active[ 'plugins' ][ $plugin->parameter ] ) )
				{
					$this->__callPlugin( $plugin );
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Hooks Plugin Caller
	 *
	 * @param   Plugin $plugin
	 *
	 * @access  public
	 */
	public function __callPlugin( Plugin $plugin )
	{
		if ( is_file( ROOTPATH . $plugin->realpath . 'core' . DIRECTORY_SEPARATOR . 'Controller.php' ) )
		{
			$controller_class = $plugin->namespace . 'Core\Controller';

			if ( class_exists( $controller_class ) )
			{
				$plugin_object           = new $controller_class();
				$plugin_object->metadata = $plugin;

				if ( is_file( ROOTPATH . $plugin->realpath . 'core' . DIRECTORY_SEPARATOR . 'Model.php' ) )
				{
					$model_class = $plugin->namespace . 'Core\Model';
				}
				elseif ( is_file( ROOTPATH . $plugin->realpath . 'models' . DIRECTORY_SEPARATOR . prepare_filename( $plugin->parameter ) ) )
				{
					$model_class = $plugin->namespace . 'Models\\' . studlycapcase( $plugin->parameter );
				}

				$plugin_object->model = new $model_class();

				if ( isset( $_POST[ $plugin->parameter ] ) )
				{
					$post = $_POST[ $plugin->parameter ];
					unset( $_POST );

					// Run Fieldset Process
					if ( method_exists( $plugin_object, '__postPluginProcess' ) )
					{
						$plugin_object->__postPluginProcess( $post );
					}
				}
				else
				{
					// Run Plugin Settings
					if ( method_exists( $plugin_object, '__hookPluginController' ) )
					{
						$plugin_object->__hookPluginController();
					}

					\O2System::$active[ 'plugins' ][ $plugin->parameter ] = $plugin_object;
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Hooks Widgets Caller
	 *
	 * @param   array $widgets
	 *
	 * @access  public
	 */
	public function __callWidgets( array $widgets )
	{
		if ( count( $widgets ) > 0 )
		{
			foreach ( $widgets as $widget )
			{
				if ( empty( \O2System::$active[ 'widgets' ][ $widget->segments ] ) )
				{
					$this->__callWidget( $widget );
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Hooks Widget Caller
	 *
	 * @param   Widget $widget
	 *
	 * @access  public
	 */
	public function __callWidget( Widget $widget )
	{
		if ( is_file( $filepath = ROOTPATH . $widget->realpath . prepare_filename( $widget->parameter ) . '.php' ) )
		{
			require_once( $filepath );

			$controller_class = $widget->namespace . prepare_class_name( $widget->parameter );

			if ( class_exists( $controller_class ) )
			{
				$widget_object           = new $controller_class();
				$widget_object->metadata = $widget;

				if ( is_file( ROOTPATH . $widget->realpath . 'core' . DIRECTORY_SEPARATOR . 'Model.php' ) )
				{
					$model_class = $widget->namespace . 'Core\Model';
				}
				elseif ( is_file( ROOTPATH . $widget->realpath . 'models' . DIRECTORY_SEPARATOR . prepare_filename( $widget->parameter ) ) )
				{
					$model_class = $widget->namespace . 'Models\\' . studlycapcase( $widget->parameter );
				}

				$widget_object->model = new $model_class();

				// Run Plugin Settings
				if ( method_exists( $widget_object, '__hookWidgetController' ) )
				{
					$widget_object->__hookWidgetController();
				}

				\O2System::$view->widgets[ $widget->segments ] = $widget_object;
			}
		}
	}
}