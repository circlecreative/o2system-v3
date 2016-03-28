<?php
/**
 * O2Bootstrap
 *
 * An open source bootstrap components factory for PHP 5.4+
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
 * @package     O2Bootstrap
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2bootstrap/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2parser.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Bootstrap\Interfaces;

use O2System\Bootstrap\Factory\Lists;

trait ItemsInterface
{
	protected $_items        = array();
	protected $_num_rows     = 2;
	protected $_num_per_rows = 3;

	public function is_empty()
	{
		return $this->__isEmpty();
	}

	public function __isEmpty()
	{
		return (bool) empty( $this->_items );
	}

	public function add_lists( Lists $lists )
	{
		if ( $lists instanceof Lists )
		{
			$this->_items = $lists;
		}

		return $this;
	}

	public function add_items( array $items )
	{
		foreach ( $items as $item )
		{
			$this->add_item( $item );
		}

		return $this;
	}

	public function add_item( $item )
	{
		if ( $this->_items instanceof Lists )
		{
			$this->_items->add_item( $item );
		}
		else
		{
			$this->_items[] = $item;
		}

		return $this;
	}

	public function set_items_map( array $map )
	{
		foreach ( $map as $key => $index )
		{
			if ( isset( $this->_items[ $key ] ) )
			{
				$this->_items[ $index ] = $this->_items[ $key ];
				unset( $this->_items[ $key ] );
			}
		}
	}


	public function set_num_rows( $rows )
	{
		if ( is_numeric( $rows ) )
		{
			$this->_num_rows = (int) $rows;
		}

		return $this;
	}

	public function set_num_per_rows( $num )
	{
		if ( is_numeric( $num ) )
		{
			$this->_num_per_rows = (int) $num;
		}

		return $this;
	}
}