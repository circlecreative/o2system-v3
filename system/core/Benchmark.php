<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * O2System
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative).
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
 * @package		O2System
 * @author		Steeven Andrian Salim
 * @copyright	Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license		http://circle-creative.com/products/o2system/license.html
 * @license	    http://opensource.org/licenses/MIT	MIT License
 * @link		http://circle-creative.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Benchmark Class
 *
 * This class enables you to mark points and calculate the time difference
 * between them.  Memory consumption can also be displayed.
 *
 * Borrowed Class from CodeIgniter 3.0-dev
 *
 * @author	    EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	    http://opensource.org/licenses/MIT	MIT License
 * @link	    http://codeigniter.com
 * @since	    Version 2.0.0
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @contributor Steeven Andrian Salim
 * @link		http://codeigniter.com/user_guide/libraries/benchmark.html
 */

class O2_Benchmark 
{
	/**
	 * List of all benchmark markers and when they were added
	 *
	 * @var array
	 */
	var $marker = array();
	var $elapsed_time = array();

	// --------------------------------------------------------------------

	/**
	 * Set a benchmark starting marker point
	 *
	 * Alias with additional string for starting marker point
	 *
	 * @access	public
	 * @param	string	$name	name of the marker
	 * @return	void
	 */
	function start($name)
	{
		$name = $name.':time_start';
		$this->mark($name);
	}

	/**
	 * Set a benchmark ending marker point
	 *
	 * Alias with additional string for ending marker point
	 *
	 * @access	public
	 * @param	string	$name	name of the marker
	 * @return	void
	 */
	function end($name)
	{
		$this->mark($name.':time_end');
		$this->elapsed_time[$name] = $this->elapsed_time($name.':time_start', $name.':time_end');
	}

	/**
	 * Set a benchmark marker
	 *
	 * Multiple calls to this function can be made so that several
	 * execution points can be timed
	 *
	 * @access	public
	 * @param	string	$name	name of the marker
	 * @return	void
	 */
	function mark($name)
	{
		$this->marker[$name] = microtime();
	}

	// --------------------------------------------------------------------

	/**
	 * Calculates the time difference between two marked points.
	 *
	 * If the first parameter is empty this function instead returns the
	 * {elapsed_time} pseudo-variable. This permits the full system
	 * execution time to be shown in a template. The output class will
	 * swap the real value for this variable.
	 *
	 * @access	public
	 * @param	string	a particular marked point
	 * @param	string	a particular marked point
	 * @param	integer	the number of decimal places
	 * @return	mixed
	 */
	function elapsed_time($point1 = '', $point2 = '', $decimals = 4)
	{
		if ($point1 == '')
		{
			$point1 = 'total_execution';
		}

		if (strpos($point1, ':') === false)
		{
			$point1 = $point1.':time_start';
		}

		if ( ! isset($this->marker[$point1]))
		{
			return '';
		}

		if (strpos($point2, ':') === false)
		{
			$point2 = $point2.':time_end';
		}

		if ( ! isset($this->marker[$point2]))
		{
			$this->marker[$point2] = microtime();
		}

		list($sm, $ss) = explode(' ', $this->marker[$point1]);
		list($em, $es) = explode(' ', $this->marker[$point2]);

		return number_format(($em + $es) - ($sm + $ss), $decimals);
	}

	// --------------------------------------------------------------------
}