<?php
/**
 * O2DB
 *
 * Open Source PHP Data Abstraction Layers
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative)
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
 * @package     O2DB
 * @author      PT. Lingkar Kreasi (Circle Creative)
 * @copyright   Copyright (c) 2005 - 2016, PT. Lingkar Kreasi (Circle Creative)
 * @license     http://circle-creative.com/products/o2db/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\DB\Metadata\Result;


use O2System\Core\SPL\ArrayObject;

class Field extends ArrayObject
{
	public function __construct( array $field = [ ] )
	{
		if ( count( $field ) > 0 )
		{
			foreach ( $field as $index => $value )
			{
				$this->offsetSet( $index, $value );
			}
		}
		else
		{
			parent::__construct(
				[
					'name'    => '',
					'type'    => '',
					'null'    => '',
					'key'     => '',
					'default' => '',
					'extra'   => '',
				] );
		}
	}

	public function offsetSet( $index, $value )
	{
		$index = strtolower( $index );
		$index = $index === 'field' ? 'name' : $index;

		parent::offsetSet( $index, $value );
	}
}