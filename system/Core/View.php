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
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://o2system.center
 * @since          Version 3.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\Core;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core;
use O2System\Exception\ViewLoadException;

/**
 * Class View
 *
 * @package O2System
 */
class View extends Core\Library
{
	use Core\Collectors\Paths;
	use Core\Library\Traits\Handlers;
	use Core\Library\Traits\Collections;

	/**
	 * View Vars
	 *
	 * @type View\Metadata\Variables
	 */
	public $vars;

	/**
	 * View Meta
	 *
	 * @type \O2System\Core\HTML\Meta
	 */
	public $meta;

	/**
	 * Class Constructor
	 *
	 * @access    public
	 */
	public function __construct()
	{
		parent::__construct( \O2System::$config->load( 'view', TRUE ) );

		$this->vars = new View\Metadata\Variables(
			[
				'title' => new View\Metadata\Title(),
			] );

		// Add Powered by O2System Framework
		$this->vars->add( 'powered', $powered = new View\Metadata\Powered() );

		$this->meta = new View\Metadata\Meta();

		// Add Meta Generator by O2System PHP Framework
		$this->meta->add( 'generator', SYSTEM_NAME . ' ' . SYSTEM_VERSION );

		// Initialize Handlers Classes Lazy Init
		$this->__locateHandlers();

		// Initialize Collections Classes Lazy Init
		$this->__locateCollections();
	}

	public function initialize()
	{
		// Set View Paths
		$this->setPaths( \O2System::$request->directories->getArrayCopy() );

		// Initialize Theme
		$this->theme->initialize();

		// Initialize Assets
		$this->assets->initialize();

		// Initialize Parser
		$this->parser->initialize();

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	/**
	 * Page
	 *
	 * Load view page
	 *
	 * @param   string $page
	 * @param   array  $vars
	 * @param   bool   $return
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function page( $page, $vars = [ ], $return = FALSE )
	{
		if ( is_file( $page ) )
		{
			$path_info = pathinfo( $page );

			if ( isset( $vars[ 'title' ] ) )
			{
				$this->vars->title->add( $vars[ 'title' ] );
			}
			else
			{
				$title = readable( $path_info[ 'filename' ], TRUE );
				$this->vars->title->add( $title );
			}

			return $this->load( $page, $vars, $return );
		}
		elseif ( is_file( APPSPATH . 'pages/' . $page . '.phtml' ) )
		{
			if ( isset( $vars[ 'title' ] ) )
			{
				$this->vars->title->add( $vars[ 'title' ] );
			}
			else
			{
				$title = readable( $page );
				$this->vars->title->add( $title );
			}

			return $this->load( APPSPATH . 'pages/' . $page . '.phtml', $vars, $return );
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Load View File
	 *
	 * @param   string $view   View layout filename
	 * @param   array  $vars   View variables
	 * @param   bool   $return Return view string
	 *
	 * @access  public
	 * @return  string
	 */
	public function load( $view, array $vars = [ ], $return = FALSE )
	{
		if ( FALSE !== ( $filepath = $this->getFilePath( $view ) ) )
		{
			if ( $return === FALSE )
			{
				if ( $this->theme->active === FALSE )
				{
					$this->_setOutput( $filepath, $vars );
				}
				else
				{
					$this->_setOutputWithTheme( $filepath, $vars );
				}
			}
			else
			{
				// Append Additional Vars
				$this->vars->append( $vars );

				return $this->parser->parseFile( $filepath, $this->vars->getArrayCopy() );
			}
		}
		else
		{
			throw new ViewLoadException( 'E_VIEW_LOAD_FILE_NOT_FOUND', 116 );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Get File Path
	 *
	 * @param  string $view filename or file path
	 *
	 * @todo: load view replacement from theme views, module views, theme module views
	 *
	 * @return bool|string
	 */
	public function getFilePath( $view )
	{
		if ( is_file( $view ) )
		{
			return realpath( $view );
		}
		else
		{
			foreach ( \O2System::$load->getViewsDirectories( TRUE ) as $path )
			{
				foreach ( $this->parser->getExtensions() as $extension )
				{
					if ( is_file( $filepath = $path . $view . $extension ) )
					{
						return realpath( $filepath );
						break;
					}
				}
			}
		}

		return FALSE;
	}

	protected function _setOutput( $filepath, $vars )
	{
		// Append Vars
		$this->vars->append( $vars );

		// Setup Title
		$this->meta->add( 'title', $this->vars->title->browser->__toString() );

		$this->vars->add( 'meta', $this->meta );
		$this->vars->add( 'theme', $this->theme->active );
		$this->vars->add( 'assets', $this->assets );
		$this->vars->add( 'navigations', $this->navigations );
		$this->vars->add( 'partials', $this->partials );
		$this->vars->add( 'widgets', $this->widgets );

		$output = $this->parser->parseFile( $filepath );
		$this->output->setContent( $output );
	}

	// ------------------------------------------------------------------------

	protected function _setOutputWithTheme( $filepath, array $vars = [ ] )
	{
		// Append Vars
		$this->vars->append( $vars );

		// Add Meta Title
		$this->meta->add( 'title', $this->vars->title->browser->__toString() );

		// Add Theme Meta
		if ( isset( $this->theme->active->settings[ 'meta' ] ) )
		{
			$this->meta->add( $this->theme->active->settings[ 'meta' ] );
		}

		// TODO: Add Meta Opengraph

		// Register Theme Path
		$this->addPath( $this->theme->active->realpath );

		// Add Theme Assets
		$this->assets->add( $this->theme->active->settings[ 'assets' ], 'theme' );
		$this->assets->add( 'theme', 'theme' );

		// Add Theme Partials
		$this->partials->add( $this->theme->active->partials );

		// Add Partial Content
		$this->partials->add( 'content', $filepath );

		$this->vars->add( 'meta', $this->meta );
		$this->vars->add( 'theme', $this->theme->active );
		$this->vars->add( 'assets', $this->assets );
		$this->vars->add( 'navigations', $this->navigations );
		$this->vars->add( 'partials', $this->partials );
		$this->vars->add( 'widgets', $this->widgets );

		$output = $this->parser->parseFile( $this->theme->active->layout );

		$this->output->setContent( $output );
	}

	/**
	 * Output
	 *
	 * @param $output
	 */
	public function output()
	{
		if ( \O2System::$hooks->call( 'output_override' ) === FALSE )
		{
			if ( method_exists( \O2System::$controller, '__output' ) )
			{
				\O2System::$controller->__output( $this->output->getContent() );
			}

			$this->render();
		}
	}
}