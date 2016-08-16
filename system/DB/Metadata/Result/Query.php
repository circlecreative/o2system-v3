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

use O2System\DB\Metadata\ErrorInfo;

class Query
{
	protected $_table;

	/**
	 * The query string, as provided by the user.
	 *
	 * @var string
	 */
	protected $_string;

	/**
	 * The start time in seconds with microseconds
	 * for when this query was executed.
	 *
	 * @var float
	 */
	protected $_time_start;

	/**
	 * The end time in seconds with microseconds
	 * for when this query was executed.
	 *
	 * @var float
	 */
	protected $_time_end;

	protected $_error = FALSE;

	/**
	 * Sets the raw query string to use for this statement.
	 *
	 * @param string $sql
	 * @param array  $binds
	 *
	 * @return mixed
	 */
	public function setString( $sql )
	{
		$this->_string = $sql;

		return $this;
	}

	//--------------------------------------------------------------------

	public function setTable( $table )
	{
		$this->_table = $table;
	}

	public function getTable()
	{
		return $this->_table;
	}

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 *
	 * @return mixed
	 */
	public function getString()
	{
		return $this->_string;

	}

	//--------------------------------------------------------------------

	public function setTimeStart()
	{
		$this->_time_start = microtime( TRUE );
	}

	public function setTimeEnd()
	{
		$this->_time_end = microtime( TRUE );
	}

	/**
	 * Returns the start time in seconds with microseconds.
	 *
	 * @param bool $returnRaw
	 * @param int  $decimals
	 *
	 * @return mixed
	 */
	public function getTimeStart( $returnRaw = FALSE, $decimals = 6 )
	{
		if ( $returnRaw )
		{
			return $this->_time_start;
		}

		return number_format( $this->_time_start, $decimals );
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the start time in seconds with microseconds.
	 *
	 * @param bool $returnRaw
	 * @param int  $decimals
	 *
	 * @return mixed
	 */
	public function getTimeEnd( $returnRaw = FALSE, $decimals = 6 )
	{
		if ( $returnRaw )
		{
			return $this->_time_end;
		}

		return number_format( $this->_time_end, $decimals );
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the duration of this query during execution, or null if
	 * the query has not been executed yet.
	 *
	 * @param int $decimals The accuracy of the returned time.
	 *
	 * @return mixed
	 */
	public function getDuration( $decimals = 6 )
	{
		return number_format( ( $this->_time_end - $this->_time_start ), $decimals );
	}

	//--------------------------------------------------------------------

	public function setError( ErrorInfo $error )
	{
		$this->_error = $error;
	}

	/**
	 * Returns the last error encountered by this connection.
	 *
	 * @return ErrorInfo|bool
	 */
	public function getError()
	{
		return $this->_error;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @return bool
	 */
	public function isWriteType()
	{
		return (bool) preg_match(
			'/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i',
			$this->_string );
	}

	//--------------------------------------------------------------------

	/**
	 * Return text representation of the query
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->_string;
	}

	//--------------------------------------------------------------------
}