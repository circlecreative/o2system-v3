<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
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
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Controllers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Controller;
use O2System\Glob\HttpStatusCode;

/**
 * Pages Controller
 *
 * O2System Pages Default Controller
 *
 * @package        O2System
 * @subpackage     controllers
 * @category       Controller Class
 * @author         Steeven Andrian Salim
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/controllers/pages.html
 */
class Error extends Controller
{
	/**
	 * Controller Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// ------------------------------------------------------------------------

	/**
	 * Index Method
	 *
	 * @param   string $page Page Filename
	 *
	 * @access  public
	 */
	public function index( $code )
	{
		if($code == 404)
		{
			$this->view->load( 'errors/html/error_404', array(
				'code' => 404,
				'heading' => HttpStatusCode::getHeader( 404 ),
				'message' => HttpStatusCode::getDescription( 404 )
			) );
		}
		else
		{
			$this->view->load( 'errors/html/error_code', array(
				'code' => $code,
				'heading' => HttpStatusCode::getHeader( $code ),
				'message' => HttpStatusCode::getDescription( $code )
			) );
		}
	}
}