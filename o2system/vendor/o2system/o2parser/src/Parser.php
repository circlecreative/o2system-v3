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

namespace O2System
{

	use O2System\Parser\Exception;

	/**
	 * Parser Library
	 *
	 * @package          Template
	 * @subpackage       Library
	 * @category         Driver
	 * @version          1.0 Build 11.09.2012
	 * @author           Steeven Andrian Salim
	 * @copyright        Copyright (c) 2005 - 2014
	 * @license          http://www.circle-creative.com/products/o2system/license.html
	 * @link             http://www.circle-creative.com
	 */
	// ------------------------------------------------------------------------
	class Parser extends Glob\Interfaces\LibraryInterface
	{
		/**
		 * Parser Configuration
		 *
		 * @access protected
		 */
		protected $_config = array(
			'driver'             => 'smarty',
			'php'                => TRUE,
			'markdown'           => FALSE,
			'shortcode'          => FALSE,
			'rewrite_short_tags' => FALSE,
		);


		/**
		 * Active parser engine driver
		 *
		 * @access  protected
		 *
		 * @type string
		 */
		protected $_driver;

		/**
		 * List of possible view file extensions
		 *
		 * @access  protected
		 *
		 * @type array
		 */
		public $extensions = array( '.php', '.phtml', '.html', '.tpl' );

		// ------------------------------------------------------------------------

		public function __reconstruct( array $config = array() )
		{
			if ( class_exists( 'O2System' ) )
			{
				if ( empty( $config[ 'cache' ] ) )
				{
					$config[ 'cache' ] = APPSPATH . 'cache' . DIRECTORY_SEPARATOR . 'parser' . DIRECTORY_SEPARATOR;
				}
			}

			parent::__reconstruct( $config );
		}

		/**
		 * Glob Libraries Class Constructor
		 *
		 * @access  public
		 *
		 * @uses    O2System\Core\Loader
		 * @uses    O2System\Core\Gears\Logger
		 *
		 */
		public function initialize()
		{
			$this->setDriver( $this->_config[ 'driver' ] );
		}

		/**
		 * Set Render Engine Driver
		 *
		 * @access  public
		 *
		 * @param   string $driver String of driver name
		 *
		 * @thrown  throw new Exception()
		 *
		 * @return \O2System\Libraries\Template Instance of O2System\Core\Template class
		 */
		public function setDriver($driver )
		{
			$driver = strtolower( $driver );

			if ( ! array_key_exists( $driver, $this->_valid_drivers ) )
			{
				throw new Exception( 'Unsupported Parser Driver: ' . $driver );
			}

			$class_name = get_class( $this ) . '\\Drivers\\' . ucfirst( $driver );

			$this->_driver = new $class_name();
			$this->_driver->setup( $this->_config );

			if ( isset( $this->_driver->extensions ) )
			{
				$this->extensions = array_merge( $this->extensions, $this->_driver->extensions );
				$this->extensions = array_unique( $this->extensions );
			}
		}

		public function parseFile($file, $vars = array() )
		{
			if ( is_file( $file ) )
			{
				return $this->parseSourceCode( file_get_contents( $file ), $vars );
			}

			return FALSE;
		}

		/**
		 * Parse HTML Source Code
		 *
		 * Parse HTML source code using active parser engine
		 *
		 * @access  public
		 *
		 * @param string $source_code HTML source code
		 * @param array  $vars        Array of parse data variables
		 *
		 * @return string
		 */
		public function parseSourceCode($source_code = '', $vars = array() )
		{
			// Parse PHP
			if ( $this->_config[ 'php' ] === TRUE )
			{
				$source_code = $this->parsePhp( $source_code, $vars );
			}

			// Parse Shortcode
			if ( $this->_config[ 'shortcode' ] === TRUE )
			{
				$source_code = $this->parseShortcode( $source_code );
			}

			// Parse String
			$source_code = $this->parseString( $source_code, $vars );

			// Parse Markdown
			if ( $this->_config[ 'markdown' ] === TRUE )
			{
				$source_code = $this->parseMarkdown( $source_code );
			}

			// Parse BBCode
			if ( $this->_config[ 'bbcode' ] === TRUE )
			{
				$source_code = $this->parseBbcode( $source_code );
			}

			return $source_code;
		}

		/**
		 * Parse String
		 *
		 * Parse String Syntax Code of Render Engine inside HTML source code
		 *
		 * @access  public
		 *
		 * @param string $source_code HTML Source Code
		 * @param array  $vars        Array of parsing data variables
		 *
		 * @return string
		 */
		public function parseString($source_code, $vars = array() )
		{
			return $this->_driver->parseString( $source_code, $vars );
		}

		/**
		 * Parse Markdown
		 *
		 * Parse Markdown Code inside HTML source code
		 *
		 * @access  public
		 *
		 * @param $source_code  HTML source code
		 *
		 * @return string
		 */
		public function parseMarkdown($source_code, $flavour = 'default' )
		{
			if ( $flavour === 'github' )
			{
				if ( ! class_exists( 'cebe\markdown\GithubMarkdown' ) )
				{
					throw new Exception( 'The Cebe Markdown GithubMarkdown Parser must be loaded to use Parser with Github Flavour.' );
				}

				// use github markdown
				$markdown = new \cebe\markdown\GithubMarkdown();
				$markdown->html5 = TRUE;
				$markdown->keepListStartNumber = TRUE;
				$markdown->enableNewlines = TRUE;

				return $markdown->parse( $source_code );
			}
			elseif ( $flavour === 'github-paragraph' )
			{
				if ( ! class_exists( 'cebe\markdown\GithubMarkdown' ) )
				{
					throw new Exception( 'The Cebe Markdown GithubMarkdown Parser must be loaded to use Parser with Github Flavour.' );
				}

				// parse only inline elements (useful for one-line descriptions)
				$markdown = new \cebe\markdown\GithubMarkdown();
				$markdown->html5 = TRUE;
				$markdown->keepListStartNumber = TRUE;
				$markdown->enableNewlines = TRUE;

				return $markdown->parseParagraph( $source_code );
			}
			elseif ( $flavour === 'extra' )
			{
				if ( ! class_exists( 'cebe\markdown\MarkdownExtra' ) )
				{
					throw new Exception( 'The Cebe Markdown Extra Parser must be loaded to use Parser with Extra Flavour.' );
				}

				// use markdown extra
				$markdown = new \cebe\markdown\MarkdownExtra();
				$markdown->html5 = TRUE;
				$markdown->keepListStartNumber = TRUE;
				$markdown->enableNewlines = TRUE;

				return $markdown->parse( $source_code );
			}

			if ( ! class_exists( 'cebe\markdown\Markdown' ) )
			{
				throw new Exception( 'The Cebe Markdown Parser must be loaded to use Parser with Markdown.' );
			}

			// traditional markdown and parse full text
			$markdown = new \cebe\markdown\Markdown();
			$markdown->html5 = TRUE;
			$markdown->keepListStartNumber = TRUE;
			$markdown->enableNewlines = TRUE;

			return $markdown->parse( $source_code );
		}

		/**
		 * Parse BBCode
		 *
		 * Parse BBCode Code inside HTML source code
		 *
		 * @access  public
		 *
		 * @param $source_code  HTML source code
		 *
		 * @return string
		 */
		public function parseBbcode($source_code )
		{
			if ( ! class_exists( 'JBBCode\Parser' ) )
			{
				throw new Exception( 'The JBBCode Parser must be loaded to use Parser with BBCode.' );
			}

			$bbcode = new \JBBCode\Parser();
			$bbcode->addCodeDefinitionSet( new \JBBCode\DefaultCodeDefinitionSet() );

			$bbcode->parse( $source_code );

			return $bbcode->getAsHtml();
		}

		/**
		 * Parse Shortcode
		 *
		 * Parse Wordpress a Like Shortcode Code inside HTML source code
		 *
		 * @access  public
		 *
		 * @param $source_code  HTML source code
		 *
		 * @return string
		 */
		public function parseShortcode($source_code )
		{
			if ( $shortcodes = $this->shortcode->fetch( $source_code ) )
			{
				$source_code = $this->shortcode->parse( $source_code );
			}

			// Fixed Output
			$source_code = str_replace( array( '_shortcode', '[?php', '?]' ), array( 'shortcode', '&lt;?php', '?&gt;' ),
			                            $source_code );

			return $source_code;
		}

		/**
		 * Parse PHP
		 *
		 * Parse PHP Code inside HTML source code
		 *
		 * @access  public
		 *
		 * @param string $source_code HTML source code
		 * @param array  $vars        Array of parse data variables
		 *
		 * @return string
		 */
		public function parsePhp($source_code, $vars = array() )
		{
			$source_code = htmlspecialchars_decode( $source_code );
			$vars = is_object( $vars ) ? get_object_vars( $vars ) : $vars;

			extract( $vars );

			if ( class_exists( 'O2System', FALSE ) )
			{
				$active = \O2System::$active;
				$language = \O2System::$language;

				extract( \O2System::instance()->getStorage()->getArrayCopy() );
			}

			/*
			 * Buffer the output
			 *
			 * We buffer the output for two reasons:
			 * 1. Speed. You get a significant speed boost.
			 * 2. So that the final rendered template can be post-processed by
			 *  the output class. Why do we need post processing? For one thing,
			 *  in order to show the elapsed page load time. Unless we can
			 *  intercept the content right before it's sent to the browser and
			 *  then stop the timer it won't be accurate.
			 */
			ob_start();

			// If the PHP installation does not support short tags we'll
			// do a little string replacement, changing the short tags
			// to standard PHP echo statements.
			if ( ! ini_get( 'short_open_tag' ) AND
				$this->_config[ 'rewrite_short_tags' ] === TRUE AND
				functionUsable( 'eval' )
			)
			{
				echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', str_replace( '<?=', '<?php echo ', $source_code ) ) );
			}
			else
			{
				echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', $source_code ) );
			}

			$output = ob_get_contents();
			@ob_end_clean();

			return $output;
		}
	}
}

namespace O2System\Parser
{

	use O2System\Glob\Interfaces\ExceptionInterface;

	/**
	 * Class Exception
	 *
	 * @package     O2Parser
	 *
	 * @author      O2System Developer Team
	 * @link        http://o2system.in/features/o2template
	 */
	class Exception extends ExceptionInterface
	{
		public $library = array(
			'name'        => 'O2System Parser (O2Parser)',
			'description' => 'Open Source PHP Parser Driver Library',
			'version'     => '1.0.0',
		);
	}
}
