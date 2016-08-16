<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 4:25 PM
 */

namespace O2System\Core\View\Handlers;


use O2System\Core\Library\Handler;
use O2System\Core\View\Parser\Interfaces\EngineInterface;
use O2System\Exception\ViewParserException;

class Parser extends Handler
{
	protected $config = [
		'engine' => 'moustache',
		'cache'  => '',
		'parse'  => [
			'php'        => TRUE,
			'markdown'   => FALSE,
			'bbcode'     => FALSE,
			'shortcodes' => FALSE,
			'string'     => [
				'allowed_php_scripts'   => TRUE,
				'allowed_php_functions' => TRUE,
				'allowed_php_constants' => TRUE,
				'allowed_php_globals'   => TRUE,
			],
		],
	];

	/**
	 * Engine
	 *
	 * @type EngineInterface
	 */
	protected $engine;

	/**
	 * Is PHP File Flag
	 *
	 * @type bool
	 */
	protected $isPHPFile = FALSE;

	/**
	 * Is Parse PHP Flag
	 *
	 * @type bool
	 */
	protected $isParsePHP = TRUE;

	/**
	 * Is Parse Markdown Flag
	 *
	 * @type bool
	 */
	protected $isParseMarkdown = FALSE;

	/**
	 * Is Parse BBCode Flag
	 *
	 * @type bool
	 */
	protected $isParseBBCode = FALSE;

	/**
	 * Is Parse Shortcodes Flag
	 *
	 * @type bool
	 */
	protected $isParseShortcodes = FALSE;

	/**
	 * List of possible view file extensions
	 *
	 * @access  protected
	 *
	 * @type array
	 */
	protected $extensions = [ '.php', '.phtml', '.html', '.tpl' ];

	/**
	 * Initialize
	 *
	 * @param array $config
	 *
	 * @throws \O2System\Exception\ViewParserException
	 */
	public function initialize( array $config = [ ] )
	{
		$this->config->merge( $config );

		$this->isParsePHP        = (bool) $this->config[ 'parse' ][ 'php' ];
		$this->isParseMarkdown   = (bool) $this->config[ 'parse' ][ 'markdown' ];
		$this->isParseBBCode     = (bool) $this->config[ 'parse' ][ 'bbcode' ];
		$this->isParseShortcodes = (bool) $this->config[ 'parse' ][ 'shortcodes' ];

		if ( isset( $this->config[ 'engine' ] ) )
		{
			$engineClass = 'O2System\Core\View\Handlers\Parser\Engines\\' . studlycapcase( $this->config[ 'engine' ] );

			if ( class_exists( $engineClass ) )
			{
				$config = (array) $this->config[ 'parse' ][ 'string' ];

				if ( empty( $this->config[ 'cache' ] ) )
				{
					$config[ 'cache' ] = APPSPATH . 'cache' . DIRECTORY_SEPARATOR . 'parser' . DIRECTORY_SEPARATOR;
				}
				else
				{
					$config[ 'cache' ] = $this->config[ 'cache' ];
				}

				$this->engine = new $engineClass();
				$this->engine->initialize( $config );
			}
			else
			{
				throw new ViewParserException( 'E_VIEW_PARSER_ENGINE_NOT_SUPPORTED', 103 );
			}
		}
		else
		{
			throw new ViewParserException( 'E_VIEW_PARSER_ENGINE_UNDEFINED', 104 );
		}

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	/**
	 * Get Extensions
	 *
	 * Get list of file extensions
	 *
	 * @return array
	 */
	public function getExtensions()
	{
		return $this->extensions;
	}

	public function isParsePHP()
	{
		return (bool) $this->isParsePHP;
	}

	/**
	 * Parse File
	 *
	 * @param string $file File Path
	 * @param array  $vars
	 *
	 * @return bool|string
	 */
	public function parseFile( $file, $vars = [ ] )
	{
		if ( is_file( $file ) )
		{
			$this->isPHPFile = FALSE;

			if ( in_array( pathinfo( $file, PATHINFO_EXTENSION ), [ 'php', 'phtml' ] ) )
			{
				$this->isPHPFile = TRUE;
			}

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
	public function parseSourceCode( $source_code = '', $vars = [ ] )
	{
		if ( $this->isPHPFile )
		{
			// Parse PHP
			if ( $this->isParsePHP === TRUE )
			{
				$source_code = $this->parsePhp( $source_code, $vars );
			}
		}

		// Parse String
		$source_code = $this->parseString( $source_code, $vars );

		// Parse Shortcode
		if ( $this->isParseShortcodes === TRUE )
		{
			$source_code = $this->parseShortcode( $source_code );
		}

		// Parse Markdown
		if ( $this->isParseMarkdown === TRUE )
		{
			$source_code = $this->parseMarkdown( $source_code );
		}

		// Parse BBCode
		if ( $this->isParseBBCode === TRUE )
		{
			$source_code = $this->parseBbcode( $source_code );
		}

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
	public function parsePhp( $source_code, $vars = [ ] )
	{
		\O2System::$view->vars->append( $vars );
		$source_code = htmlspecialchars_decode( $source_code );

		extract( \O2System::$view->vars->getArrayCopy() );

		// Core Class
		$system     = \O2System::instance();
		$config     = \O2System::$config;
		$language   = \O2System::$language;
		$benchmark  = \O2System::$benchmark;
		$session    = \O2System::$session;
		$request    = \O2System::$request;
		$view       = \O2System::$view;
		$controller = \O2System::$controller;

		// Registry
		$active  = \O2System::$active;
		$globals = \O2System::$registry->globals;
		$server  = \O2System::$registry->server;

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
			$this->config[ 'parse' ][ 'short_tags' ] === TRUE AND
			function_usable( 'eval' )
		)
		{
			echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', str_replace( '<?=', '<?php echo ', $source_code ) ) );
		}
		else
		{
			echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', $source_code ) );
		}

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
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
	public function parseString( $source_code, $vars = [ ] )
	{
		if ( isset( $this->engine ) AND method_exists( $this->engine, 'parseString' ) )
		{
			return $this->engine->parseString( $source_code, $vars );
		}

		return $source_code;
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
	public function parseShortcode( $source_code )
	{
		if ( $shortcodes = $this->shortcode->fetch( $source_code ) )
		{
			$source_code = $this->shortcode->parse( $source_code );
		}

		// Fixed Output
		$source_code = str_replace(
			[ '_shortcode', '[?php', '?]' ], [ 'shortcode', '&lt;?php', '?&gt;' ],
			$source_code );

		return $source_code;
	}

	/**
	 * Parse Markdown
	 *
	 * @param string $source_code
	 * @param string $flavour
	 *
	 * @return string
	 * @throws \O2System\Parser\Exception
	 */
	public function parseMarkdown( $source_code, $flavour = 'default' )
	{
		if ( $flavour === 'github' )
		{
			if ( ! class_exists( 'cebe\markdown\GithubMarkdown' ) )
			{
				$this->throwError( 'E_VIEW_PARSER_MARKDOWN_GITHUB', 106, [ ], 'O2System\Exception\ViewParserException' );
			}

			// use github markdown
			$markdown                      = new \cebe\markdown\GithubMarkdown();
			$markdown->html5               = TRUE;
			$markdown->keepListStartNumber = TRUE;
			$markdown->enableNewlines      = TRUE;

			return $markdown->parse( $source_code );
		}
		elseif ( $flavour === 'github-paragraph' )
		{
			if ( ! class_exists( 'cebe\markdown\GithubMarkdown' ) )
			{
				$this->throwError( 'E_VIEW_PARSER_MARKDOWN_GITHUB', 106, [ ], 'O2System\Exception\ViewParserException' );
			}

			// parse only inline elements (useful for one-line descriptions)
			$markdown                      = new \cebe\markdown\GithubMarkdown();
			$markdown->html5               = TRUE;
			$markdown->keepListStartNumber = TRUE;
			$markdown->enableNewlines      = TRUE;

			return $markdown->parseParagraph( $source_code );
		}
		elseif ( $flavour === 'extra' )
		{
			if ( ! class_exists( 'cebe\markdown\MarkdownExtra' ) )
			{
				$this->throwError( 'E_VIEW_PARSER_MARKDOWN_GITHUB', 106, [ ], 'O2System\Parser\HandlerException' );
			}

			// use markdown extra
			$markdown                      = new \cebe\markdown\MarkdownExtra();
			$markdown->html5               = TRUE;
			$markdown->keepListStartNumber = TRUE;
			$markdown->enableNewlines      = TRUE;

			return $markdown->parse( $source_code );
		}

		if ( ! class_exists( 'cebe\markdown\Markdown' ) )
		{
			$this->throwError( 'E_VIEW_PARSER_MARKDOWN', 105, [ ], 'O2System\Parser\HandlerException' );
		}

		// traditional markdown and parse full text
		$markdown                      = new \cebe\markdown\Markdown();
		$markdown->html5               = TRUE;
		$markdown->keepListStartNumber = TRUE;
		$markdown->enableNewlines      = TRUE;

		return $markdown->parse( $source_code );
	}

	/**
	 * Parse BBCode
	 *
	 * @param $source_code
	 *
	 * @return string
	 * @throws \O2System\Parser\Exception
	 */
	public function parseBbcode( $source_code )
	{
		if ( ! class_exists( 'JBBCode\Parser' ) )
		{
			$this->throwError( 'E_VIEW_PARSER_BBCODE', 107, [ ], 'O2System\Parser\HandlerException' );
		}

		$bbcode = new \JBBCode\Parser();
		$bbcode->addCodeDefinitionSet( new \JBBCode\DefaultCodeDefinitionSet() );

		$bbcode->parse( $source_code );

		return $bbcode->getAsHTML();
	}
}