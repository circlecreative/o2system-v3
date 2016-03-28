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
use O2System\File;
use O2System\Glob\Interfaces\DriverInterface;
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
class Output extends DriverInterface
{
	protected $_headers = array();

	protected $_content      = '';
	protected $_prepend_body = array();
	protected $_append_body  = array();

	protected $_zlib_support = FALSE;

	protected $_cache;
	protected $_cache_lifetime = 0;

	protected $_etag;

	protected $_mime;

	public function __reconstruct()
	{
		$this->_zlib_support = (bool) ini_get( 'zlib.output_compression' );

		$this->_etag = md5( $_SERVER[ 'REQUEST_URI' ] );

		if ( isset( $this->_config[ 'cache' ] ) )
		{
			$this->_cache = new Cache( $this->_config[ 'cache' ] );
		}
	}

	public function set_cache_lifetime( $lifetime )
	{
		$this->_cache_lifetime = (int) $lifetime;
	}

	public function set_header_status( $code, $description = NULL )
	{
		\O2System\Glob\HttpStatusCode::setHeader( $code, $description );
	}

	/**
	 * Set a new default header to send on every request
	 *
	 * @param string $key   header name
	 * @param string $value header value
	 */
	public function set_header( $key, $value = NULL )
	{
		// If zlib.output_compression is enabled it will compress the output,
		// but it will not modify the content-length header to compensate for
		// the reduction, causing the browser to hang waiting for more data.
		// We'll just skip content-length in those cases.
		if ( $this->_zlib_support )
		{
			if ( strncasecmp( $key, 'content-length', 14 ) === 0 )
			{
				return $this;
			}
		}
		else
		{
			$this->_headers[ $key ] = $value;

			return $this;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Set default headers
	 *
	 * @param array $headers headers array
	 */
	public function set_headers( array $headers = array() )
	{
		foreach ( $headers as $header => $value )
		{
			$this->set_header( $header, $value );
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
	public function set_content_type( $mime, $charset = NULL )
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
			. ( empty( $charset ) ? '' : '; charset=' . $this->_library->_charset );

		$this->_headers[] = array( $header, TRUE );

		return $this;
	}

	// --------------------------------------------------------------------


	public function set_content( $content )
	{
		$this->_content = $content;

		return $this;
	}

	public function prepend_content( $content )
	{
		$this->_content = $content . $this->_content;
	}

	public function append_content( $content )
	{
		$this->_content .= $content;

		return $this;
	}

	public function prepend_body( $content )
	{
		$this->_prepend_body[] = $content;
	}

	public function append_body( $content )
	{
		$this->_append_body[] = $content;
	}

	public function get_content()
	{
		return $this->_content;
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
			$output =& $this->_content;
		}
		// --------------------------------------------------------------------

		if ( $this->_mime === 'text/html' )
		{
			if ( class_exists( 'O2System', FALSE ) )
			{
				$benchmark = \O2System::Benchmark();

				if ( ! empty( $benchmark ) )
				{
					$output = str_replace(
						array( '[elapsed_time]', '[memory_usage]', '[memory_peak_usage]', '[cpu_usage]' ),
						(array) $benchmark->elapsed(),
						$output
					);
				}
			}

			//remove optional ending tags (see http://www.w3.org/TR/html5/syntax.html#syntax-tag-omission )
			$remove = array(
				'</option>', '</li>', '</dt>', '</dd>', '</tr>', '</th>', '</td>',
			);
			$output = str_ireplace( $remove, '', $output );

			$doc = new \DomDocument();
			$doc->preserveWhiteSpace = TRUE;
			$doc->validateOnParse = TRUE;

			$output = mb_convert_encoding( $output, 'HTML-ENTITIES', 'UTF-8' );
			$output = preg_replace( array( '~\R~u', '~>[[:space:]]++<~m' ), array( "\n", '><' ), $output );

			if ( ( empty( $output ) !== TRUE ) && ( @$doc->loadHTML( $output ) === TRUE ) )
			{
				$doc->formatOutput = TRUE;

				if ( ( $output = $doc->saveXML( $doc->documentElement, LIBXML_NOEMPTYTAG ) ) !== FALSE )
				{
					$regex = array
					(
						'~' . preg_quote( '<![CDATA[', '~' ) . '~'                                                                     => '',
						'~' . preg_quote( ']]>', '~' ) . '~'                                                                           => '',
						'~></(?:area|base(?:font)?|br|col|command|embed|frame|hr|img|input|keygen|link|meta|param|source|track|wbr)>~' => ' />',
					);

					$output = '<!DOCTYPE html>' . "\n" . preg_replace( array_keys( $regex ), $regex, $output );
				}
			}

			// Fixing HTML5
			$output = preg_replace( '~></(?:area|base(?:font)?|br|col|command|embed|frame|hr|img|input|keygen|link|meta|param|source|track|wbr)>\b~i', '/>', $output );

			// Remove empty p tag
			$output = str_replace( '<p></p>', '', $output );

			$HTMLDom = HtmlDomParser::str_get_html( $output );

			if ( is_object( $HTMLDom ) )
			{
				$body = $HTMLDom->find( 'body', 0 );

				if ( isset( $body->innertext ) )
				{
					$body->innertext = implode( PHP_EOL, $this->_prepend_body ) . $body->innertext . implode( PHP_EOL, $this->_append_body );
					$output = $HTMLDom->outertext;
				}
			}

			// Is minify requested
			/*if ( $this->_config[ 'minify' ] === TRUE )
			{
				$output = $this->_minify_html( $output );
			}*/
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


		$output = $this->_compress( $output );

		// --------------------------------------------------------------------

		// Are there any server headers to send?
		if ( count( $this->_headers ) > 0 )
		{
			foreach ( $this->_headers as $key => $value )
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

			$cache = array(
				'headers'          => $this->_headers,
				'output'           => $output,
				'create_timestamp' => time(),
				'expire_timestamp' => $expired,
			);

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
		$search = array(
			'/\n/',            // replace end of line by a space
			'/\>[^\S ]+/s',        // strip whitespaces after tags, except space
			'/[^\S ]+\</s',        // strip whitespaces before tags, except space
			'/(\s)+/s'        // shorten multiple whitespace sequences
		);

		$replace = array(
			' ',
			'>',
			'<',
			'\\1',
		);

		$output = preg_replace( $search, $replace, $output );

		return $output;
	}

	protected function _compress_css( $css )
	{
		/* remove comments */
		$css = preg_replace( '!/*[^*]**+([^/][^*]**+)*/!', '', $css );

		/* remove tabs, spaces, newlines, etc. */
		$css = str_replace( array( "rn", "r", "n", "t", '  ', '    ', '    ' ), '', $css );

		return $css;
	}


	protected function _minify_html( $output )
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