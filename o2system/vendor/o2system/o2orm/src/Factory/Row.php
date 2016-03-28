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

namespace O2System\ORM\Factory;

// ------------------------------------------------------------------------

use O2System\DB\Factory\Row as RowInterface;

/**
 * Row Object Class
 *
 * @author      O2System Developer Team
 * @link        http://o2system.in/features/o2db/metadata/result
 */
class Row extends RowInterface
{
	protected $_model;

	public function __construct( $model )
	{
		$this->_model = $model;
	}

	// ------------------------------------------------------------------------

	/**
	 * Call Override
	 *
	 * This method is act as magic method, inspired from Laravel Eloquent ORM
	 *
	 * @access  public
	 *
	 * @param   string $method
	 * @param   array  $args
	 *
	 * @return  mixed
	 */
	public function __call( $method, $args = array() )
	{
		foreach ( get_object_vars( $this ) as $key => $value )
		{
			$this->_model->__set( $key, $value );
		}

		return $this->_model->__call( $method, $args );
	}

	// ------------------------------------------------------------------------
}