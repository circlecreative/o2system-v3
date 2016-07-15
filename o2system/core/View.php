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

namespace O2System;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\Helpers\Inflector;
use O2System\Interfaces\Widget;

/**
 * View
 *
 * This class contains functions that enable config files to be managed
 *
 * @package        O2System
 * @subpackage     core
 * @category       Core Library Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/view.html
 */
class View extends Template
{
	/**
	 * Class Constructor
	 *
	 * @access    public
	 */
	public function __reconstruct( array $config = [ ] )
	{
		// Set View Config
		$config = \O2System::$config->load( 'view', TRUE );

		// Set View Paths
		$this->setPaths( \O2System::Load()->getPackagePaths( NULL, TRUE ) );

		parent::__reconstruct( $config );
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
				$this->addTitle( $vars[ 'title' ] );
			}
			else
			{
				$title = readable( $path_info[ 'filename' ] );
				$this->addTitle( $title );
			}

			return $this->load( $page, $vars, $return );
		}
		elseif ( is_file( APPSPATH . 'pages/' . $page . '.phtml' ) )
		{
			if ( isset( $vars[ 'title' ] ) )
			{
				$this->addTitle( $vars[ 'title' ] );
			}
			else
			{
				$title = readable( $page );
				$this->addTitle( $title );
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
		if ( $return === TRUE )
		{
			return $this->view->load( $view, $vars, TRUE );
		}
		else
		{
			$this->render( $view, $vars );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache
	 *
	 * View Cache
	 *
	 * @access  public
	 * @return  bool
	 */
	public function cache()
	{
		if ( \O2System::Hooks()->call( 'output_cache' ) === FALSE AND
			$this->output->cache() === TRUE AND
			is_cli() === FALSE
		)
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Output
	 *
	 * @param $output
	 */
	public function output()
	{
		if ( \O2System::Hooks()->call( 'output_override' ) === FALSE )
		{
			if ( method_exists( \O2System::Controller(), '__output' ) )
			{
				\O2System::Controller()->__output( $this->output->getContent() );
			}

			$this->output->render();
		}
	}

	// ------------------------------------------------------------------------
}