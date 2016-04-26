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

/**
 * Metadata
 *
 * CURL Metadata ArrayObject
 *
 * @package O2System\CURL\Factory
 */
class Metadata extends \ArrayObject
{
	public function __construct( $data = array() )
	{
		parent::__construct( [ ], \ArrayObject::ARRAY_AS_PROPS );

		if ( ! empty( $data ) )
		{
			foreach ( $data as $key => $value )
			{
				$this->__set( $key, $value );
			}
		}
	}

	public function __set( $index, $value )
	{
		if ( is_array( $value ) )
		{
			$value = new Metadata( $value );
		}

		$this->offsetSet( $index, $value );
	}
}