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

/**
 * Response
 *
 * CURL Response Factory Class
 *
 * @package O2System\CURL\Factory
 */
class Response
{
	/**
	 * Response Metadata
	 *
	 * @type \O2System\CURL\Factory\Metadata
	 */
	public $meta;

	/**
	 * Response RAW Body
	 *
	 * @type string
	 */
	public $raw_body;

	/**
	 * Response Body
	 *
	 * @type \O2System\CURL\Factory\Metadata|string
	 */
	public $body;

	/**
	 * Response Header
	 *
	 * @type \O2System\CURL\Factory\Metadata
	 */
	public $headers;

	// ------------------------------------------------------------------------

	/**
	 * Response constructor.
	 *
	 * @param $response
	 * @param $info
	 */
	public function __construct( $response, $info )
	{
		$this->meta = new Metadata( $info );
		$this->raw_body = $response;
		$this->body = $this->_parse_response( $response );
		$this->headers = $this->_parse_headers( $response );

		if ( isset( $this->body->meta ) )
		{
			foreach ( get_object_vars( $this->body->meta ) as $key => $value )
			{
				$this->meta[ $key ] = $value;
			}

			unset( $this->body->meta );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Parse Response
	 *
	 * @param $response
	 *
	 * @return \O2System\CURL\Factory\Metadata|string
	 */
	protected function _parse_response( $response )
	{
		if ( strpos( $response, 'HTTP' ) !== FALSE )
		{
			$response = substr( $response, $this->meta->header_size );
		}

		$json = new Metadata( json_decode( $response, TRUE ) );

		if ( json_last_error() === JSON_ERROR_NONE )
		{
			$response = $json;
		}

		return $response;
	}

	// ------------------------------------------------------------------------

	/**
	 * if PECL_HTTP is not available use a fall back function
	 *
	 * thanks to ricardovermeltfoort@gmail.com
	 * http://php.net/manual/en/function.http-parse-headers.php#112986
	 */
	protected function _parse_headers( $response )
	{
		$raw_headers = substr( $response, 0, $this->meta->header_size );

		if ( function_exists( 'http_parse_headers' ) )
		{
			return new Metadata( http_parse_headers( $raw_headers ) );
		}
		else
		{
			$headers = new Metadata( json_decode( $raw_headers, TRUE ) );

			if ( json_last_error() === JSON_ERROR_NONE )
			{
				return $headers;
			}

			$key = '';
			$headers = new Metadata();

			foreach ( explode( "\n", $raw_headers ) as $i => $h )
			{
				$h = explode( ':', $h, 2 );

				if ( isset( $h[ 1 ] ) )
				{
					if ( ! isset( $headers[ $h[ 0 ] ] ) )
					{
						$headers[ $h[ 0 ] ] = trim( $h[ 1 ] );
					}
					elseif ( is_array( $headers[ $h[ 0 ] ] ) )
					{
						$headers[ $h[ 0 ] ] = array_merge( $headers[ $h[ 0 ] ], array( trim( $h[ 1 ] ) ) );
					}
					else
					{
						$headers[ $h[ 0 ] ] = array_merge( array( $headers[ $h[ 0 ] ] ), array( trim( $h[ 1 ] ) ) );
					}

					$key = $h[ 0 ];
				}
				else
				{
					if ( substr( $h[ 0 ], 0, 1 ) == "\t" )
					{
						$headers[ $key ] .= "\r\n\t" . trim( $h[ 0 ] );
					}
					elseif ( ! $key )
					{
						$headers[ 0 ] = trim( $h[ 0 ] );
					}
				}
			}

			return $headers;
		}
	}
}