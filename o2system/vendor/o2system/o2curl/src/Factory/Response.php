<?php
/**
 * O2CURL
 *
 * Lightweight HTTP Request Libraries for PHP 5.4+
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
 * @package     o2curl
 * @author      O2System Developer Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2curl/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2curl.html
 */
// ------------------------------------------------------------------------

namespace O2System\CURL\Factory;

// ------------------------------------------------------------------------
use O2System\CURL\Metadata\Error;
use O2System\CURL\Metadata\Headers;
use O2System\CURL\Metadata\Info;
use O2System\CURL\Metadata\SimpleJSONElement;
use O2System\CURL\Metadata\SimpleQueryElement;
use O2System\Glob\ArrayObject;

/**
 * Response
 *
 * CURL Response Factory Class
 *
 * @package O2System\CURL\Factory
 */
class Response extends ArrayObject
{
	/**
	 * Set Response Metadata
	 *
	 * @param   array $metadata
	 *
	 * @access  public
	 */
	public function setMetadata( array $metadata )
	{
		$this->info = new Info( $metadata );
	}

	/**
	 * Set Response Body
	 *
	 * @param   string $body
	 *
	 * @access  public
	 */
	public function setBody( $body )
	{
		if ( is_string( $body ) )
		{
			$this->body    = (string) $body;
			$this->headers = $this->__parseHeaders( $body );
			$this->data    = $this->__parseBody( $body );
		}
	}

	public function setError( $code, $message )
	{
		$this->error = new Error();
		$this->error->setCode( $code );
		$this->error->setMessage( $message );
	}

	/**
	 * Parse Response Body
	 *
	 * @param   string $raw_body
	 *
	 * @return  mixed
	 */
	private function __parseBody( $raw_body )
	{
		$content_type = isset( $this->headers->content_type ) ? $this->headers->content_type : $this->info->content_type;
		$content_type = explode( ';', $content_type );
		$content_type = array_map( 'trim', $content_type );
		$content_type = reset( $content_type );
		$content_type = strtolower( $content_type );

		if ( ! empty( $content_type ) )
		{
			if ( $content_type === 'application/json' )
			{
				$json_body = json_decode( $raw_body, TRUE );

				if ( json_last_error() === JSON_ERROR_NONE )
				{
					return new SimpleJSONElement( $json_body );
				}
			}
			elseif ( $content_type === 'application/xml' )
			{
				return simplexml_load_string( $raw_body );
			}
		}

		$raw_body        = trim( $raw_body );
		$substr_raw_body = substr( $raw_body, $this->info->header_size );

		$raw_body = empty( $substr_raw_body ) ? $raw_body : $substr_raw_body;

		$json_body = json_decode( $raw_body, TRUE );

		if ( is_array( $json_body ) AND json_last_error() === JSON_ERROR_NONE )
		{
			return new SimpleJSONElement( (array) $json_body );
		}
		else
		{
			parse_str( $raw_body, $query_string );

			if ( is_array( $query_string ) AND count( $query_string ) > 0 )
			{
				return new SimpleQueryElement( (array) $query_string );
			}
			elseif ( ! empty( $raw_body ) )
			{
				$DomDocument = new \DOMDocument();
				$DomDocument->loadHTML( $raw_body );

				return $DomDocument;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * if PECL_HTTP is not available use a fall back function
	 *
	 * thanks to ricardovermeltfoort@gmail.com
	 * http://php.net/manual/en/function.http-parse-headers.php#112986
	 */
	private function __parseHeaders( $body )
	{
		$raw_headers = substr( $body, 0, $this->info->header_size );
		$raw_headers = http_parse_headers( $raw_headers );
		$raw_headers = empty( $raw_headers ) ? [ ] : $raw_headers;

		$headers = new Headers();

		foreach ( $raw_headers as $key => $value )
		{
			$key = $key === 0 ? 'http' : $key;

			$headers->offsetSet( underscore( $key ), $value );
		}

		return $headers;
	}
}