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

namespace O2System;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core;

/**
 * Security Class
 *
 * @package        O2System
 * @subpackage     core
 * @category       Core Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/security.html
 */
final class Security extends Core\Library
{
	use Core\Library\Traits\Handlers;

	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct( \O2System::$config[ 'security' ] );

		$this->__locateHandlers();

		/*
		 * ------------------------------------------------------
		 * Security procedures
		 * ------------------------------------------------------
		 */
		$this->protection->initialize( $this->config[ 'protection' ] );

		$this->_registerGlobals();

		\O2System::$load->helper( 'security' );

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	protected function _registerGlobals()
	{
		ini_set( 'magic_quotes_runtime', 0 );

		if ( (bool) ini_get( 'register_globals' ) )
		{
			$_protected = [
				'_SERVER', '_GET', '_POST', '_FILES', '_REQUEST', '_SESSION', '_ENV', '_COOKIE', 'GLOBALS',
				'HTTP_RAW_POST_DATA', 'system_path', 'application_folder', 'view_folder', '_protected',
				'_registered',
			];

			$_registered = ini_get( 'variables_order' );

			foreach ( [
				          'E' => '_ENV', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER',
			          ] as $key => $superglobal )
			{
				if ( strpos( $_registered, $key ) === FALSE )
				{
					continue;
				}

				foreach ( array_keys( $$superglobal ) as $var )
				{
					if ( isset( $GLOBALS[ $var ] ) && ! in_array( $var, $_protected, TRUE ) )
					{
						$GLOBALS[ $var ] = NULL;
					}
				}
			}

			\O2System::$log->debug( 'LOG_DEBUG_PERFORMING_SECURITY_REGISTER_GLOBALS' );
		}

		$this->_sanitizeGlobals();
	}

	/**
	 * Sanitize Globals
	 *
	 * Internal method serving for the following purposes:
	 *
	 *    - Unsets $_GET data, if query strings are not enabled
	 *    - Cleans POST, COOKIE and SERVER data
	 *    - Standardizes newline characters to PHP_EOL
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _sanitizeGlobals()
	{
		// Clean $_GET Data
		if ( is_array( $_GET ) AND count( $_GET ) > 0 )
		{
			foreach ( $_GET as $key => $value )
			{
				$_GET[ $this->filter->cleanKeys( $key ) ] = $this->filter->cleanValues( $value );
			}
		}

		// Clean $_POST Data
		if ( is_array( $_POST ) AND count( $_POST ) > 0 )
		{
			foreach ( $_POST as $key => $value )
			{
				$_POST[ $this->filter->cleanKeys( $key ) ] = $this->filter->cleanValues( $value );
			}
		}

		// Clean $_COOKIE Data
		if ( is_array( $_COOKIE ) AND count( $_COOKIE ) > 0 )
		{
			// Also get rid of specially treated cookies that might be set by a server
			// or silly application, that are of no use to a CI application anyway
			// but that when present will trip our 'Disallowed Key Characters' alarm
			// http://www.ietf.org/rfc/rfc2109.txt
			// note that the key names below are single quoted strings, and are not PHP variables
			unset(
				$_COOKIE[ '$Version' ],
				$_COOKIE[ '$Path' ],
				$_COOKIE[ '$Domain' ]
			);

			foreach ( $_COOKIE as $key => $value )
			{
				if ( ( $cookie_key = $this->filter->cleanKeys( $key ) ) !== FALSE )
				{
					$_COOKIE[ $cookie_key ] = $this->filter->cleanValues( $value );
				}
				else
				{
					unset( $_COOKIE[ $key ] );
				}
			}
		}

		// Sanitize PHP_SELF
		$_SERVER[ 'PHP_SELF' ] = strip_tags( $_SERVER[ 'PHP_SELF' ] );

		\O2System::$log->debug( 'LOG_DEBUG_PERFORMING_SECURITY_SANITIZED_GLOBALS' );
	}

	// --------------------------------------------------------------------
}