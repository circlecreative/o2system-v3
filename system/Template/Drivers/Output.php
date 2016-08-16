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

namespace O2System\Template\Drivers;

// ------------------------------------------------------------------------

use O2System\Cache;
use O2System\Core\Interfaces\DriverInterface;
use O2System\Core\Library\Driver;
use O2System\File;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Output Class
 *
 * Responsible for sending final output to the browser.
 *
 * @package        O2System
 * @subpackage     system/core
 * @category       Core Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/framework/user-guide/core/output.html
 */
class Output extends Driver
{
	/**
	 * Library Instance
	 *
	 * @access  protected
	 * @type    Template
	 */
	protected $library;

	protected $headers = [ ];

	protected $content     = '';
	protected $appendHead  = [ ];
	protected $prependBody = [ ];
	protected $appendBody  = [ ];

	protected $isZlibSupport = FALSE;

	protected $_cache;
	protected $_cache_lifetime = 0;

	protected $_etag;

	protected $_mime;

	public function __reconstruct()
	{
		$this->isZlibSupport = (bool) ini_get( 'zlib.output_compression' );

		$this->_etag = md5( $_SERVER[ 'REQUEST_URI' ] );

		if ( isset( $this->_config[ 'cache' ] ) )
		{
			$this->_cache = new Cache( $this->_config[ 'cache' ] );
		}
	}

	public function setCacheLifetime( $lifetime )
	{
		$this->_cache_lifetime = (int) $lifetime;
	}

	public function clearHeaders()
	{
		if ( ! headers_sent() )
		{
			foreach ( headers_list() as $header )
			{
				header_remove( $header );
			}
		}

		return $this;
	}

	public function setHeaderStatus( $code, $description = NULL )
	{
		\O2System\Glob\HttpHeaderStatus::setHeader( $code, $description );
	}

	/**
	 * Set a new default header to send on every request
	 *
	 * @param string $key   header name
	 * @param string $value header value
	 */
	public function setHeader( $key, $value = NULL )
	{
		// If zlib.output_compression is enabled it will compress the output,
		// but it will not modify the content-length header to compensate for
		// the reduction, causing the browser to hang waiting for more data.
		// We'll just skip content-length in those cases.
		if ( $this->isZlibSupport )
		{
			if ( strncasecmp( $key, 'content-length', 14 ) === 0 )
			{
				return $this;
			}
		}
		else
		{
			$this->headers[ $key ] = $value;

			return $this;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Set default headers
	 *
	 * @param array $headers headers array
	 */
	public function setHeaders( array $headers = [ ] )
	{
		foreach ( $headers as $header => $value )
		{
			$this->setHeader( $header, $value );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Content-Type Header
	 *
	 * @param    string $mime    Extension of the file we're outputting
	 * @param    string $charset Character set (default: NULL)
	 *
	 * @return    \O2System\Output
	 */
	public function setContentType( $mime, $charset = NULL )
	{
		static $mimes;

		if ( ! isset( $mimes ) )
		{
			$mimes = File::mimes();
		}

		if ( strpos( $mime, '/' ) === FALSE )
		{
			$extension = ltrim( $mime, '.' );

			// Is this extension supported?
			if ( isset( $mimes[ $extension ] ) )
			{
				$mime =& $mimes[ $extension ];

				if ( is_array( $mime ) )
				{
					$mime = current( $mime );
				}
			}
		}

		$this->_mime = $mime;

		$header = 'Content-Type: ' . $mime
			. ( empty( $charset ) ? '' : '; charset=' . $this->library->_charset );

		$this->headers[] = [ $header, TRUE ];

		return $this;
	}

	// --------------------------------------------------------------------


	public function setContent( $content )
	{
		$this->content = $content;

		return $this;
	}

	public function prependContent( $content )
	{
		$this->content = $content . $this->content;
	}

	public function appendContent( $content )
	{
		$this->content .= $content;

		return $this;
	}

	public function prependBody( $content )
	{
		$this->prependBody[] = $content;
	}

	public function appendBody( $content )
	{
		$this->appendBody[] = $content;
	}

	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Display Output
	 *
	 * Processes and sends finalized output data to the browser along
	 * with any server headers and profile data. It also stops benchmark
	 * timers so the page rendering speed and memory usage can be shown.
	 *
	 * Note: All "view" data is automatically put into $this->final_output
	 *     by controller class.
	 *
	 * @param    string $output Output data override
	 *
	 * @return    void
	 */
	public function render( $output = '', $is_cached = TRUE )
	{
		// Set the output data
		if ( $output === '' )
		{
			$output =& $this->content;
		}
		// --------------------------------------------------------------------

		if ( $this->_mime === 'text/html' )
		{
			$benchmark = \O2System::Benchmark();

			if ( ! empty( $benchmark ) )
			{
				$output = str_replace(
					[ '[elapsed_time]', '[memory_usage]', '[memory_peak_usage]', '[processor_usage]' ],
					(array) $benchmark->elapsed(),
					$output
				);
			}

			$doc                     = new \DomDocument();
			$doc->preserveWhiteSpace = TRUE;
			$doc->validateOnParse    = TRUE;

			$output = str_replace( [ '<!DOCTYPE html>', '<!doctype html>' ], '', $output );
			$output = mb_convert_encoding( $output, 'HTML-ENTITIES', 'UTF-8' );

			if ( ( empty( $output ) !== TRUE ) && ( @$doc->loadHTML( $output ) === TRUE ) )
			{
				$doc->formatOutput = TRUE;

				if ( ( $output = $doc->saveXML( $doc->documentElement, LIBXML_NOEMPTYTAG ) ) !== FALSE )
				{
					$regex = [
						'~' . preg_quote( '<![CDATA[', '~' ) . '~'                                                                     => '',
						'~' . preg_quote( ']]>', '~' ) . '~'                                                                           => '',
						'~></(?:area|base(?:font)?|br|col|command|embed|frame|hr|img|input|keygen|link|meta|param|source|track|wbr)>~' => ' />',
					];

					$output = '<!DOCTYPE html>' . "\n" . preg_replace( array_keys( $regex ), $regex, $output );
				}
			}

			// Fixing HTML5
			$output = preg_replace( '~></(?:area|base(?:font)?|br|col|command|embed|frame|hr|img|input|keygen|link|meta|param|source|track|wbr)>\b~i', '/>', $output );

			// Remove empty p tag
			$output = str_replace( '<p></p>', '', $output );

			if ( ! empty( $this->prependBody ) OR ! empty( $this->appendBody ) )
			{
				$HTMLDom = HtmlDomParser::str_get_html( $output );

				if ( is_object( $HTMLDom ) )
				{
					$body = $HTMLDom->find( 'body', 0 );

					if ( isset( $body->innertext ) )
					{
						$body->innertext = implode( PHP_EOL, $this->prependBody ) . $body->innertext . implode( PHP_EOL, $this->appendBody );
						$output          = $HTMLDom->outertext;
					}
				}
			}

			// Is minify requested
			if ( $this->_config[ 'minify' ] === TRUE )
			{
				$output = $this->_minifyHtml( $output );
			}
		}

		// Is compression requested?
		/*if ( $this->_config[ 'compress' ] === TRUE AND
			isset( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) AND
			substr_count( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ], 'gzip' ) !== FALSE
		)
		{
			ob_flush();
			ob_start( 'ob_gzhandler' );

			header( 'Content-Encoding: gzip' );
			header( 'Content-Length: ' . strlen( $output ) );
		}
		else
		{
			ob_start();
		}*/

		// --------------------------------------------------------------------

		// Are there any server headers to send?
		if ( count( $this->headers ) > 0 )
		{
			foreach ( $this->headers as $key => $value )
			{
				if ( is_string( $key ) )
				{
					header( $key . ': ' . $value );
				}
				else
				{
					header( $value[ 0 ], $value[ 1 ] );
				}
			}
		}

		// --------------------------------------------------------------------

		// Do we need to write a cache file? Only if the controller does not have its
		// own _output() method and we are not dealing with a cache file, which we
		// can determine by the existence of the $controller object above
		if ( $is_cached === TRUE AND
			isset( $this->_cache ) AND
			$this->_cache_lifetime > 0 AND
			PHP_SAPI !== 'cli' // avoiding caching for cli request
		)
		{
			$expired = time() + ( $this->_config[ 'cache' ][ 'lifetime' ] * 60 );

			$cache = [
				'headers'          => $this->headers,
				'output'           => $output,
				'create_timestamp' => time(),
				'expire_timestamp' => $expired,
			];

			$this->_cache->save( $this->_etag, $cache, $expired );
		}

		// --------------------------------------------------------------------

		echo $output; // Send it to the browser!
	}

	// --------------------------------------------------------------------

	public function cache()
	{
		if ( isset( $this->_cache ) )
		{
			if ( $cache = $this->_cache->get( $this->_etag ) )
			{
				if ( ! empty( $cache[ 'output' ] ) AND $cache[ 'output' ] !== '' )
				{
					$max_age = $cache[ 'expire_timestamp' ] - $_SERVER[ 'REQUEST_TIME' ];

					if ( isset( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] ) && $cache[ 'create_timestamp' ] <= strtotime( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] ) )
					{
						if ( strpos( PHP_SAPI, 'cgi' ) === 0 )
						{
							header( 'Status: 304 Not Modified', TRUE );
						}
					}
					else
					{
						header( 'Pragma: public' );
						header( 'Cache-Control: max-age=' . $max_age . ', public' );
						header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', $cache[ 'expire_timestamp' ] ) . ' GMT' );
						header( 'Last-modified: ' . gmdate( 'D, d M Y H:i:s', $cache[ 'create_timestamp' ] ) . ' GMT' );
					}

					echo $cache[ 'output' ]; // Send it to the browser!

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	protected function _compress( $output )
	{
		$search = [
			'/\n/',            // replace end of line by a space
			'/\>[^\S ]+/s',        // strip whitespaces after tags, except space
			'/[^\S ]+\</s',        // strip whitespaces before tags, except space
			'/(\s)+/s'        // shorten multiple whitespace sequences
		];

		$replace = [
			' ',
			'>',
			'<',
			'\\1',
		];

		$output = preg_replace( $search, $replace, $output );

		return $output;
	}

	protected function _compressCss( $css )
	{
		/* remove comments */
		$css = preg_replace( '!/*[^*]**+([^/][^*]**+)*/!', '', $css );

		/* remove tabs, spaces, newlines, etc. */
		$css = str_replace( [ "rn", "r", "n", "t", '  ', '    ', '    ' ], '', $css );

		return $css;
	}


	protected function _minifyHtml( $output )
	{
		// Find all the <pre>,<code>,<textarea>, and <javascript> tags
		// We'll want to return them to this unprocessed state later.
		preg_match_all( '{<pre.+</pre>}msU', $output, $pres_clean );
		preg_match_all( '{<code.+</code>}msU', $output, $codes_clean );
		preg_match_all( '{<textarea.+</textarea>}msU', $output, $textareas_clean );
		preg_match_all( '{<script.+</script>}msU', $output, $javascript_clean );

		// Replace multiple spaces with a single space.
		$output = preg_replace( '!\s{2,}!', ' ', $output );

		// Remove comments (non-MSIE conditionals)
		$output = preg_replace( '{\s*<!--[^\[<>].*(?<!!)-->\s*}msU', '', $output );

		// Remove spaces around block-level elements.
		$output = preg_replace( '/\s*(<\/?(html|head|title|meta|script|link|style|body|table|thead|tbody|tfoot|tr|th|td|h[1-6]|div|p|br)[^>]*>)\s*/is', '$1', $output );

		// Replace mangled <pre> etc. tags with unprocessed ones.
		if ( ! empty( $pres_clean ) )
		{
			preg_match_all( '{<pre.+</pre>}msU', $output, $pres_messed );
			$output = str_replace( $pres_messed[ 0 ], $pres_clean[ 0 ], $output );
		}

		if ( ! empty( $codes_clean ) )
		{
			preg_match_all( '{<code.+</code>}msU', $output, $codes_messed );
			$output = str_replace( $codes_messed[ 0 ], $codes_clean[ 0 ], $output );
		}

		if ( ! empty( $textareas_clean ) )
		{
			preg_match_all( '{<textarea.+</textarea>}msU', $output, $textareas_messed );
			$output = str_replace( $textareas_messed[ 0 ], $textareas_clean[ 0 ], $output );
		}

		return $output;
	}
}