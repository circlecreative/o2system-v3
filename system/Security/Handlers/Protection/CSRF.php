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

namespace O2System\Security\Handlers\Protection;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\Library\Handler;

/**
 * Class CSRF
 *
 * @package O2System\Core\Security\Protection
 */
class CSRF extends Handler
{
	private $isEnabled = FALSE;
	private $token;

	public function initialize()
	{
		if ( FALSE === ( $this->token = $this->getToken() ) )
		{
			$this->regenerate();
		}

		$this->isEnabled = TRUE;
	}

	public function getToken()
	{
		if ( \O2System::$session->has( 'csrf' ) )
		{
			return \O2System::$session->get( 'csrf' );
		}

		return FALSE;
	}

	public function validateToken( $token )
	{
		if ( FALSE !== ( $this->getToken() === $token ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	public function regenerate()
	{
		$this->token = md5( uniqid( mt_rand(), TRUE ) . 'CSRF' );

		\O2System::$session->set( 'csrf', $this->token );
	}

	public function isEnabled()
	{
		return (bool) $this->isEnabled;
	}
}