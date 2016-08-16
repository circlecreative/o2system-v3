<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/9/2016
 * Time: 4:10 AM
 */

namespace O2System\Core;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------


abstract class Controller
{
	/**
	 * Class constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		// Call Setup Autoload: Languages, Assets, Models, Classes
		$this->__setupAutoload();

		// Call Controller Setup Vars Method
		if ( method_exists( $this, '_setupVars' ) )
		{
			$this->_setupVars();
		}

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ get_called_class() ] );
	}

	// ------------------------------------------------------------------------

	final private function __setupAutoload()
	{
		$files = $this->request->controller->getNamespaces( TRUE );

		$controller = $this->request->controller->parameter;
		array_push( $files, $controller );

		$method = $this->request->controller->method;
		$method = $method === '_route' ? reset( $this->request->controller->params ) : $method;

		if ( $method !== 'index' )
		{
			array_push( $files, $method );
		}

		$assetsFiles = array_map( 'dash', $files );
		for ( $i = 0; $i < count( $files ); $i++ )
		{
			$xFiles = array_slice( $files, $i );
			$xFiles = array_map( 'dash', $xFiles );

			$slashFilename = implode( DIRECTORY_SEPARATOR, $xFiles );
			$dashFilename  = dash( $slashFilename );

			if ( ! in_array( $slashFilename, $assetsFiles ) )
			{
				$assetsFiles[] = $slashFilename;
			}

			if ( ! in_array( $dashFilename, $assetsFiles ) )
			{
				$assetsFiles[] = $dashFilename;
			}
		}

		if ( ! in_array( $controller, $assetsFiles ) )
		{
			array_push( $assetsFiles, $controller );
		}

		array_unshift( $assetsFiles, dash( trim( $this->config[ 'namespace' ], '\\' ) ) );

		$this->view->assets->add( $assetsFiles );
		$this->language->load( $assetsFiles );

		/**
		 * Autoload Modules
		 *
		 * Load active module languages, assets
		 */
		if ( $module = $this->request->modules->current() )
		{
			// Load Module Settings
			if ( is_file( $settingFilePath = $module->getDirectory() . strtolower( $module->type ) . '.settings' ) )
			{
				$settings = json_decode( file_get_contents( $settingFilePath ), TRUE );

				if ( json_last_error() === JSON_ERROR_NONE )
				{
					if ( isset( $settings[ 'metadata' ] ) )
					{
						$this->view->meta->add( $settings[ 'metadata' ] );
					}

					if ( isset( $settings[ 'assets' ] ) )
					{
						$this->view->assets->add( $settings[ 'assets' ], 'plugin' );
					}
				}
			}

			// Load Module Model
			if ( $this->models->offsetExists( $moduleModelObjectName = strtolower( $module->type ) ) === FALSE )
			{
				$modelClasses = [
					$module->namespace . 'Core\\Model',
					$module->namespace . 'Models\\' . prepare_class_name( $module->parameter ),
				];

				foreach ( $modelClasses as $modelClass )
				{
					if ( class_exists( $modelClass ) )
					{
						$this->models->offsetSet( $moduleModelObjectName, new $modelClass() );
						break;
					}
				}
			}
		}

		// Load Controller Model
		if ( $this->models->offsetExists( 'controller' ) === FALSE )
		{
			$controllerNamespace = $this->request->controller->getNamespace();

			$modelClasses = [
				$controllerNamespace . 'Models\\' . $this->request->controller->getCalledClass(),
				$controllerNamespace . 'Core\\Model',
			];

			foreach ( $modelClasses as $modelClass )
			{
				if ( class_exists( $modelClass ) )
				{
					if ( $this->models->offsetExists( 'module' ) )
					{
						if ( $this->models->module instanceof $modelClass )
						{
							$this->models->offsetSet( 'controller', clone $this->models->module );
						}
					}

					if ( $this->models->offsetExists( 'controller' ) === FALSE )
					{
						$this->models->offsetSet( 'controller', new $modelClass() );
					}

					break;
				}
			}
		}
	}

	// ------------------------------------------------------------------------

	public function __get( $property )
	{
		if ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}

		return \O2System::instance()->__get( $property );
	}

	// ------------------------------------------------------------------------

	public function __call( $method, array $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( [ $this, $method ], $args );
		}

		return \O2System::instance()->__call( $method, (array) $args );
	}
}