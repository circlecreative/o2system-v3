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
use O2System\Glob\ArrayObject;

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
abstract class Sso extends Controller
{
	public function request()
	{
		if ( $origin = $this->input->get( 'origin' ) )
		{
			$origin = parse_domain( $origin );
		}
		elseif ( $http_origin = $this->input->server( 'HTTP_ORIGIN' ) )
		{
			$origin = parse_url( $http_origin, PHP_URL_HOST );
			$origin = parse_domain( $origin );
		}

		if ( $origin = $this->_validate_origin( $origin ) )
		{
			if ( $token = $this->access->user->get_sso_token() )
			{
				redirect( prep_url( $origin->scheme . $origin->origin . '/sso/signin?' . http_build_query( [ 'token' => $token ] ) ), 'refresh' );
			}
			else
			{
				redirect( prep_url( $origin->scheme . $origin->origin ), 'refresh' );
			}
		}

		redirect( 'error/403', 'refresh' );
	}

	public function signin()
	{
		if ( $token = $this->input->get( 'token' ) )
		{
			$this->access->login->sso( $token );

			if ( $redirect = $this->input->get( 'redirect' ) )
			{
				redirect( $redirect, 'refresh' );
			}

			redirect( '/' );
		}

		redirect( 'error/403', 'refresh' );
	}

	public function signout()
	{
		if ( $origin = $this->input->get( 'origin' ) )
		{
			$this->access->user->logout();
			redirect( prep_url( $origin ) );
		}

		redirect( 'error/403', 'refresh' );
	}

	abstract protected function _validate_origin( ArrayObject $origin );
}