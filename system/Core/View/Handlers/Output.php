<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 4:06 PM
 */

namespace O2System\Core\View\Handlers;

use O2System\Core\File\Interfaces\MIME;
use O2System\Core\Library\Handler;
use O2System\Core\Request\Response\Header;

class Output extends Handler
{
	/**
	 * ZLib Supported Flag
	 *
	 * @type bool
	 */
	protected $isZlibSupported = FALSE;

	protected $isCached = TRUE;

	/**
	 * Output Content
	 *
	 * @type string
	 */
	protected $content;

	/**
	 * Output Head Tag Append
	 *
	 * @type array
	 */
	protected $appendHead = [ ];

	/**
	 * Output Body Tag Prepend
	 *
	 * @type array
	 */
	protected $prependBody = [ ];

	/**
	 * Output Body Tag Append
	 *
	 * @type array
	 */
	protected $appendBody = [ ];

	public function __construct()
	{
		$this->isZlibSupported = (bool) ini_get( 'zlib.output_compression' );

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
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

	public function appendHead( $head )
	{
		$this->appendHead[] = $head;
	}

	public function prependBody( $body )
	{
		$this->prependBody[] = $body;
	}

	public function appendBody( $body )
	{
		$this->appendBody[] = $body;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function setContent( $content )
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Set Cache
	 *
	 * @param int|bool $time FALSE will disabled cached | int will change cache lifetime
	 */
	public function setCache( $time = 0 )
	{
		if ( is_bool( $time ) )
		{
			$this->isCached = (bool) $time;
		}
		elseif ( is_numeric( $time ) )
		{
			if ( $time > 0 )
			{
				$this->config[ 'cache' ][ 'lifetime' ] = $time;
			}
			else
			{
				$this->isCached = FALSE;
			}
		}
	}

	public function render()
	{
		if ( \O2System::$hooks->call( 'output_override' ) === FALSE )
		{
			if ( method_exists( \O2System::$controller, '__output' ) )
			{
				\O2System::$controller->__output( $this->content );
			}
		}

		if ( empty( $this->content ) )
		{
			// @todo: send status no content
		}
		elseif ( is_string( $this->content ) )
		{
			$header = \O2System::$request->response->getHeader();
			$body   = \O2System::$request->response->getBody();

			if ( in_array( $body->getMimeType(), [ MIME::TEXT_HTML, MIME::TEXT_HTML_UTF8 ] ) )
			{
				$body->loadString( $this->content );

				// Modify HTML Content
				if (
					count( $this->appendHead ) > 0 OR
					count( $this->prependBody ) > 0 OR
					count( $this->appendBody ) > 0
				)
				{
					$DOMXPath    = new \DOMXPath( $body->DOMDocument );
					$HTMLDOMHead = $DOMXPath->query( '//head' )->item( 0 );
					$HTMLDOMBody = $DOMXPath->query( '//body' )->item( 0 );

					$appendHead = new \DOMText( implode( PHP_EOL, $this->appendHead ) );
					$HTMLDOMHead->appendChild( $appendHead );

					$prependBody = new \DOMText( implode( PHP_EOL, $this->prependBody ) );
					$HTMLDOMBody->insertBefore( $prependBody, $HTMLDOMBody->firstChild );

					$appendBody = new \DOMText( implode( PHP_EOL, $this->appendBody ) );
					$HTMLDOMBody->appendChild( $appendBody );
				}

				// Is Compress requested
				if ( $this->config[ 'compress' ] === TRUE )
				{
					$html = $body->save();

					if ( substr_count( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ], 'gzip' ) AND $this->isZlibSupported === TRUE )
					{
						$header->setLine( Header::RESPONSE_CONTENT_ENCODING, 'gzip' );
						ob_start( "ob_gzhandler" );
					}
					else
					{
						ob_start();
					}

					echo $html;

					$html = ob_get_contents();
					ob_end_clean();

					if ( $this->config[ 'minify' ] === TRUE )
					{
						$html = $this->_minifyHTML( $html );
					}
					else
					{
						$html = $this->_compressHTML( $html );
					}
				}
				elseif ( $this->config[ 'minify' ] === TRUE AND $this->config[ 'beautify' ] === FALSE )
				{
					$html = $body->save();
					$html = $this->_minifyHTML( $html );
				}
				else
				{
					$html = $body->save( [ 'beautify' => (bool) $this->config[ 'beautify' ] ] );
				}

				if ( $this->isCached === TRUE AND $this->config[ 'cache' ][ 'lifetime' ] > 0 )
				{
					if ( $handler = \O2System::$cache->load( 'output' ) )
					{
						$cache = [
							'header'    => $header->getProperties(),
							'body'      => $html,
							'timestamp' => [
								'create' => $lastModified = time(),
								'expire' => $expires = time() + $this->config[ 'cache' ][ 'lifetime' ],
							],
						];

						$header->setLastModified( $lastModified );
						$header->setExpires( $expires );

						$handler->save( md5( \O2System::$request->uri->string ) . '.view', $cache, $this->config[ 'cache' ][ 'lifetime' ] );
					}
				}

				if ( $this->config[ 'profiler' ] === TRUE )
				{
					$html .= PHP_EOL . '<!-- Page rendered in (elapsed_time) - Memory Usage. (memory_usage) - Memory Peak Usage. (memory_peak_usage) CPU Usage. - (cpu_usage) -->';
					$html = str_replace(
						[ '(elapsed_time)', '(memory_usage)', '(memory_peak_usage)', '(cpu_usage)' ],
						(array) \O2System::$benchmark->elapsed(),
						$html
					);
				}

				$header->send();

				echo $html;
			}
			else
			{
				$header->setLine( Header::REQUEST_CONTENT_TYPE, $body->getMimeType() );
				$header->setLine( Header::RESPONSE_CONTENT_LENGTH, strlen( $this->content ) );

				$header->send();

				echo $this->content;
			}
		}
	}

	// --------------------------------------------------------------------

	protected function _minifyHTML( $output )
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

	protected function _compressHTML( $output )
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

	public function cache()
	{
		if ( $handler = \O2System::$cache->load( 'output' ) )
		{
			$cache = $handler->get( md5( \O2System::$request->uri->string ) . '.view' );

			if ( isset( $cache[ 'header' ] ) )
			{
				$header = new Header();
				$header->setFromProperties( $cache[ 'header' ] );

				if ( empty( $cache[ 'body' ] ) )
				{
					$header->setStatus( Header::STATUS_NO_CONTENT );
				}
				else
				{
					if ( FALSE !== ( $last_modified = \O2System::$registry->server->get( 'HTTP_IF_MODIFIED_SINCE' ) ) )
					{
						if ( $cache[ 'timestamp' ][ 'create' ] <= strtotime( $last_modified ) )
						{
							if ( strpos( PHP_SAPI, 'cgi' ) === 0 )
							{
								$header->setStatus( Header::STATUS_NOT_MODIFIED );
							}
						}
					}
					else
					{
						$header->setLine( 'pragma', 'public' );
						$header->setLastModified( $cache[ 'timestamp' ][ 'create' ] );
						$header->setExpires( $cache[ 'timestamp' ][ 'expire' ] );
					}

					$header->send();

					echo $cache[ 'body' ];
				}

				return TRUE;
			}
		}

		return FALSE;
	}
}