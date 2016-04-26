<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, PT. Lingkar Kreasi (Circle Creative).
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
 * @copyright      Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System
{
	/**
	 * Class Template
	 *
	 * @package     O2Template
	 * @author      O2System Developer Team
	 * @link        http://o2system.in/features/o2template
	 */
	class Template extends Glob\Interfaces\LibraryInterface
	{
		protected $_charset = 'UTF-8';
		protected $_paths   = array();

		protected $_title_separator = '-';
		protected $_title_browser   = array();
		protected $_title_page      = array();

		protected $_cached_vars = array();

		/**
		 * Class Constructor
		 *
		 * @param array $config
		 *
		 * @access  public
		 */
		public function initialize()
		{
			// Load Template Language
			if ( class_exists( 'O2System', FALSE ) )
			{
				\O2System::$language->load( 'template' );
			}
			else
			{
				\O2System\Glob::$language->load( 'template' );
			}

			// Set Template Parser
			if ( isset( $this->_config[ 'parser' ] ) )
			{
				$this->__set( 'parser', new Parser( $this->_config[ 'parser' ] ) );
			}
			else
			{
				$this->__set( 'parser', new Parser() );
			}

			// Set Template Assets
			if ( isset( $this->_config[ 'assets' ][ 'autoload' ] ) )
			{
				$assets = $this->_config[ 'assets' ][ 'autoload' ];
				unset( $this->_config[ 'assets' ][ 'autoload' ] );

				$this->assets->add_assets( $assets, 'core' );
			}

			// Set Template Theme
			if ( isset( $this->_config[ 'theme' ][ 'default' ] ) )
			{
				$this->theme->set( $this->_config[ 'theme' ][ 'default' ] );
			}
			else
			{
				$this->theme->set( FALSE );
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Set Charset
		 *
		 * @param $charset
		 *
		 * @return $this
		 */
		public function set_charset( $charset )
		{
			$_valid_charsets = array(
				'UTF-8', // HTML5
				'UTF-16', // HTML5
				'ISO-8859-8' // HTML4
			);

			$charset = strtoupper( $charset );

			if ( in_array( $charset, $_valid_charsets ) )
			{
				$this->_charset = $charset;
			}

			return $this;
		}

		/**
		 * Set Title Separator
		 *
		 * @param $separator
		 */
		public function set_title_separator( $separator )
		{
			$this->_title_separator = $separator;
		}

		/**
		 * Set Title
		 *
		 * Set Browser Title and Page Title at once
		 *
		 * @param $title
		 */
		public function set_title( $title )
		{
			$this->set_title_browser( $title );
			$this->set_title_page( $title );
		}

		/**
		 * Add Title
		 *
		 * Add Browser Title and Page Title at once
		 *
		 * @param $title
		 */
		public function add_title( $title )
		{
			$this->add_title_browser( $title );
			$this->add_title_page( $title );
		}

		/**
		 * Set Title Browser
		 *
		 * @param $browser_title
		 */
		public function set_title_browser( $browser_title )
		{
			$this->_title_browser = array( $browser_title );
		}

		/**
		 * Add Title Browser
		 *
		 * @param $browser_title
		 */
		public function add_title_browser( $browser_title )
		{
			if ( ! in_array( $browser_title, $this->_title_browser ) )
			{
				$this->_title_browser[] = $browser_title;
			}
		}

		/**
		 * Set Title Page
		 *
		 * @param $page_title
		 */
		public function set_title_page( $page_title )
		{
			$this->_title_page = array( $page_title );
		}

		/**
		 * Add Title Page
		 *
		 * @param $page_title
		 */
		public function add_title_page( $page_title )
		{
			if ( ! in_array( $page_title, $this->_title_page ) )
			{
				$this->_title_page[] = $page_title;
			}
		}

		/**
		 * Set Vars
		 *
		 * Set Template Cached Vars
		 *
		 * @param array $vars
		 *
		 * @return $this
		 */
		public function set_vars( array $vars )
		{
			$this->_cached_vars = $vars;

			return $this;
		}

		/**
		 * Add Vars
		 *
		 * Add multiple template cached vars
		 *
		 * @param array $vars
		 *
		 * @return $this
		 */
		public function add_vars( array $vars )
		{
			$this->_cached_vars = array_merge( $this->_cached_vars, $vars );

			return $this;
		}

		/**
		 * Add Var
		 *
		 * Add single template cached vars
		 *
		 * @param $index
		 * @param $value
		 */
		public function add_var( $index, $value )
		{
			$this->_cached_vars[ $index ] = $value;
		}

		/**
		 * Set Paths
		 *
		 * Set searchable views and assets paths
		 *
		 * @param array $paths
		 *
		 * @return $this
		 */
		public function set_paths( array $paths )
		{
			// Add O2Template Path
			array_unshift( $paths, __DIR__ . DIRECTORY_SEPARATOR );

			$this->_paths = array_reverse( array_unique( $paths ) );

			return $this;
		}

		/**
		 * Add Paths
		 *
		 * Add searchable views and assets paths
		 *
		 * @param array $paths
		 *
		 * @return $this
		 */
		public function add_paths( array $paths )
		{
			foreach ( $paths as $path )
			{
				$this->add_path( $path );
			}

			return $this;
		}

		/**
		 * Add Path
		 *
		 * Add searchable views and asset path
		 *
		 * @param $path
		 *
		 * @return $this
		 */
		public function add_path( $path )
		{
			if ( ! in_array( $path, $this->_paths ) )
			{
				array_unshift( $this->_paths, $path );
			}

			return $this;
		}

		/**
		 * Render
		 *
		 * Render template output
		 *
		 * @param            $view
		 * @param array      $vars
		 */
		public function render( $view = NULL, array $vars = array() )
		{
			$this->_cached_vars = array_merge( $this->_cached_vars, $vars );

			// Set Charset Metadata
			$this->metadata->add_meta( 'charset', $this->_charset );

			// Set Browser Title
			$this->_cached_vars[ 'browser_title' ] = implode( ' ' . $this->_title_separator . ' ', $this->_title_browser );
			$this->_cached_vars[ 'page_title' ] = implode( ' ' . $this->_title_separator . ' ', $this->_title_page );
			$this->_cached_vars[ 'navigations' ] = $this->navigations;
			$this->_cached_vars[ 'forms' ] = $this->forms;

			if ( class_exists( 'O2System', FALSE ) )
			{
				$this->_cached_vars[ 'active' ] =& \O2System::$active;
				$this->_cached_vars[ 'language' ] =& \O2System::$language;

				foreach ( \O2System::instance()->getStorage() as $key => $value )
				{
					if ( ! in_array( $key, array( 'exceptions', 'cache', 'log', 'db' ) ) )
					{
						$this->_cached_vars[ $key ] = $value;
					}
				}
			}

			// Set Metadata Title
			if ( $this->metadata->offsetExists( 'title' ) === FALSE )
			{
				$this->metadata->add_meta( 'title', $this->_cached_vars[ 'browser_title' ] );
			}

			$this->_cached_vars[ 'metadata' ] = $this->metadata;

			if ( $this->theme->active === FALSE )
			{
				$this->_cached_vars[ 'partials' ] = $this->partials;
				$this->_cached_vars[ 'assets' ] = $this->assets;
				$this->_cached_vars[ 'widgets' ] = $this->widgets;

				// Load Layout
				$output = $this->view->load( $view, $this->_cached_vars, TRUE );
			}
			else
			{
				$this->_cached_vars[ 'theme' ] = $this->theme->active;
				$this->_cached_vars[ 'widgets' ] = $this->widgets;

				if ( isset( $this->theme->active[ 'partials' ] ) )
				{
					$this->partials->add_partials( $this->theme->active[ 'partials' ] );
				}

				if ( $this->partials->offsetExists( 'content' ) === FALSE )
				{
					$this->partials->add_partial( 'content', $view );
				}

				$this->_cached_vars[ 'partials' ] = $this->partials;

				if ( isset( $this->theme->active[ 'settings' ][ 'metadata' ] ) )
				{
					$this->metadata->add_meta( $this->theme->active[ 'settings' ][ 'metadata' ] );
				}

				if ( isset( $this->theme->active[ 'settings' ][ 'assets' ] ) )
				{
					$this->assets->add_assets( $this->theme->active[ 'settings' ][ 'assets' ], 'theme' );
				}

				$this->assets->add_asset( pathinfo( $this->theme->active[ 'layout' ], PATHINFO_FILENAME ), 'custom' );

				$this->_cached_vars[ 'metadata' ] = $this->metadata;
				$this->_cached_vars[ 'assets' ] = $this->assets;

				// Load Layout
				$output = $this->parser->parse_source_code( file_get_contents( $this->theme->active[ 'layout' ] ), $this->_cached_vars );
			}

			// Send Final Output to Browser
			$this->output->set_content_type( 'text/html' );
			$this->output->set_content( $output );
		}
	}
}

// ------------------------------------------------------------------------

namespace O2System\Template
{

	use O2System\Glob\Interfaces\ExceptionInterface;

	/**
	 * Class Exception
	 *
	 * @package O2System\Template
	 */
	class Exception extends ExceptionInterface
	{
		public $library = array(
			'name'        => 'O2System Template (O2Template)',
			'description' => 'Open Source Template Management Library',
			'version'     => '1.0',
		);

		public $view_exception = 'template_exception.php';
	}

	// ------------------------------------------------------------------------

	/**
	 * Class RunTimeException
	 *
	 * @package O2System\Template
	 */
	class RunTimeException extends Exception
	{

	}

	// ------------------------------------------------------------------------

	/**
	 * Class ViewException
	 *
	 * @package O2System\Template
	 */
	class ViewException extends Exception
	{
		public function __construct( $message, $code, $args = array() )
		{
			$this->_args = $args;
			parent::__construct( $message, $code );
		}
	}
}
