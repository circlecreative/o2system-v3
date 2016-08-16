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

	use O2System\Core\Collectors\Paths;
	use O2System\Core\Library\Interfaces\BaseCollectionsInterface;
	use O2System\Core\Library\Interfaces\BaseDriversInterface;

	/**
	 * Class Template
	 *
	 * @package     O2Template
	 * @author      O2System Developer Team
	 * @link        http://o2system.in/features/o2template
	 */
	class Template extends Core\Library\Base
	{
		use Paths;
		use BaseDriversInterface;
		use BaseCollectionsInterface;

		/**
		 * Template Parser
		 *
		 * @type Parser
		 */
		public $parser;

		protected $title = [
			'separator' => '-',
			'browser'   => [ ],
			'page'      => [ ],
		];

		/**
		 * Template Cached Vars
		 *
		 * @type array
		 */
		private $cachedVars = [ ];

		/**
		 * Template constructor.
		 *
		 * @param array $config
		 */
		public function __construct( array $config = [ ] )
		{
			parent::__construct( $config );

			$this->__locateDrivers();
			$this->__locateCollections();
		}

		/**
		 * Initialize
		 *
		 * @access  public
		 */
		public function initialize()
		{
			// Load Template Language
			Core::$language->load( 'template' );

			// Set Template Parser
			if ( isset( $this->config[ 'parser' ] ) )
			{
				$this->parser = new Parser( $this->config[ 'parser' ] );
			}
			else
			{
				$this->parser = new Parser();
			}

			$this->parser->initialize();

			// Set Template Assets
			if ( isset( $this->config[ 'assets' ][ 'autoload' ] ) )
			{
				$assets = $this->config[ 'assets' ][ 'autoload' ];
				unset( $this->config[ 'assets' ][ 'autoload' ] );

				$this->assets->addAssets( $assets, 'core' );
			}

			// Set Template Theme
			if ( isset( $this->config[ 'theme' ][ 'default' ] ) )
			{
				$this->theme->set( $this->config[ 'theme' ][ 'default' ] );
			}
			else
			{
				$this->theme->set( FALSE );
			}
		}

		// ------------------------------------------------------------------------

		public function __get( $key )
		{
			if ( array_key_exists( $key, $this->loadedCollections ) )
			{
				return $this->_loadCollection( $key );
			}
			elseif ( array_key_exists( $key, $this->loadedDrivers ) )
			{
				return $this->_loadDriver( $key );
			}
			elseif ( isset( $this->cachedVars[ $key ] ) )
			{
				return $this->cachedVars[ $key ];
			}

			return parent::__get( $key );
		}

		/**
		 * Set Charset
		 *
		 * @param $charset
		 *
		 * @return $this
		 */
		public function setCharset( $charset )
		{
			$_valid_charsets = [
				'UTF-8', // HTML5
				'UTF-16', // HTML5
				'ISO-8859-8' // HTML4
			];

			$charset = strtoupper( $charset );

			if ( in_array( $charset, $_valid_charsets ) )
			{
				$this->charset = $charset;
			}

			return $this;
		}

		/**
		 * Set Title Separator
		 *
		 * @param $separator
		 */
		public function setTitleSeparator( $separator )
		{
			$this->title[ 'separator' ] = $separator;
		}

		/**
		 * Set Title
		 *
		 * Set Browser Title and Page Title at once
		 *
		 * @param $title
		 */
		public function setTitle( $title )
		{
			$this->setTitleBrowser( $title );
			$this->setTitlePage( $title );
		}

		/**
		 * Add Title
		 *
		 * Add Browser Title and Page Title at once
		 *
		 * @param $title
		 */
		public function addTitle( $title )
		{
			$this->addTitleBrowser( $title );
			$this->addTitlePage( $title );
		}

		/**
		 * Set Title Browser
		 *
		 * @param $browser_title
		 */
		public function setTitleBrowser( $browser_title )
		{
			$this->title[ 'browser' ] = [ $browser_title ];
		}

		/**
		 * Add Title Browser
		 *
		 * @param $browser_title
		 */
		public function addTitleBrowser( $browser_title )
		{
			if ( ! in_array( $browser_title, $this->title[ 'browser' ] ) )
			{
				$this->title[ 'browser' ][] = $browser_title;
			}
		}

		/**
		 * Set Title Page
		 *
		 * @param $page_title
		 */
		public function setTitlePage( $page_title )
		{
			$this->title[ 'page' ] = [ $page_title ];
		}

		/**
		 * Add Title Page
		 *
		 * @param $page_title
		 */
		public function addTitlePage( $page_title )
		{
			if ( ! in_array( $page_title, $this->title[ 'page' ] ) )
			{
				$this->title[ 'page' ][] = $page_title;
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
		public function setVars( array $vars )
		{
			$this->cachedVars = $vars;

			return $this;
		}

		public function getVars()
		{
			return $this->cachedVars;
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
		public function addVars( array $vars )
		{
			$this->cachedVars = array_merge( $this->cachedVars, $vars );

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
		public function addVar( $index, $value )
		{
			$this->cachedVars[ $index ] = $value;
		}

		/**
		 * Render
		 *
		 * Render template output
		 *
		 * @param            $view
		 * @param array      $vars
		 */
		public function render( $view = NULL, array $vars = [ ] )
		{
			$this->cachedVars = array_merge( $this->cachedVars, $vars );

			// Set Charset Metadata
			$this->metadata->addMeta( 'charset', $this->charset );

			// Set Browser Title
			$this->cachedVars[ 'browser_title' ] = implode( ' ' . $this->title[ 'separator' ] . ' ', $this->title[ 'browser' ] );
			$this->cachedVars[ 'page_title' ]    = implode( ' ' . $this->title[ 'separator' ] . ' ', $this->title[ 'page' ] );
			$this->cachedVars[ 'navigations' ]   = $this->navigations;

			$this->cachedVars[ 'active' ]   =& \O2System::$active;
			$this->cachedVars[ 'language' ] =& \O2System::$language;

			foreach ( \O2System::instance()->getStorage() as $key => $value )
			{
				if ( ! in_array( $key, [ 'exceptions', 'cache', 'log', 'db' ] ) )
				{
					$this->cachedVars[ $key ] = $value;
				}
			}

			// Set Metadata Title
			if ( $this->metadata->offsetExists( 'title' ) === FALSE )
			{
				$this->metadata->addMeta( 'title', $this->cachedVars[ 'browser_title' ] );
			}

			$this->cachedVars[ 'metadata' ] = $this->metadata;

			if ( $this->theme->active === FALSE )
			{
				$this->cachedVars[ 'partials' ] = $this->partials;
				$this->cachedVars[ 'assets' ]   = $this->assets;
				$this->cachedVars[ 'widgets' ]  = $this->widgets;

				// Load Layout
				$output = $this->view->load( $view, $this->cachedVars, TRUE );
			}
			else
			{
				$this->cachedVars[ 'theme' ]   = $this->theme->active;
				$this->cachedVars[ 'widgets' ] = $this->widgets;

				if ( isset( $this->theme->active[ 'partials' ] ) )
				{
					$this->partials->addPartials( $this->theme->active[ 'partials' ] );
				}

				if ( $this->partials->offsetExists( 'content' ) === FALSE )
				{
					$this->partials->addPartial( 'content', $view );
				}

				$this->cachedVars[ 'partials' ] = $this->partials;

				// OpenGraph
				if ( $this->metadata->opengraph->count() > 0 )
				{
					foreach ( $this->metadata->opengraph as $key => $value )
					{
						$this->metadata->addOpengraph( $key, $value );
					}
				}

				if ( isset( $this->theme->active[ 'settings' ][ 'metadata' ] ) )
				{
					$this->metadata->addMeta( $this->theme->active[ 'settings' ][ 'metadata' ] );
				}

				if ( isset( $this->theme->active[ 'settings' ][ 'assets' ] ) )
				{
					$this->assets->addAssets( $this->theme->active[ 'settings' ][ 'assets' ], 'theme' );
				}

				$this->assets->addAsset( pathinfo( $this->theme->active[ 'layout' ], PATHINFO_FILENAME ), 'custom' );

				$this->cachedVars[ 'metadata' ] = $this->metadata;
				$this->cachedVars[ 'assets' ]   = $this->assets;

				// Load Layout
				$output = $this->parser->parseSourceCode( file_get_contents( $this->theme->active[ 'layout' ] ), $this->cachedVars );
			}

			// Send Final Output to Browser
			$this->output->setContentType( 'text/html' );
			$this->output->setContent( $output );
		}
	}
}

// ------------------------------------------------------------------------

namespace O2System\Template
{
	/**
	 * Class Exception
	 *
	 * @package O2System\Template
	 */
	class Exception extends \O2System\Core\Exception
	{
		public $library = [
			'name'        => 'O2System Template (O2Template)',
			'description' => 'Open Source Template Management Library',
			'version'     => '1.0',
		];

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
		public function __construct( $message, $code, $args = [ ] )
		{
			$this->args = $args;
			parent::__construct( $message, $code );
		}
	}
}
