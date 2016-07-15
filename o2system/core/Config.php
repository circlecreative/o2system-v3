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
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://o2system.center
 * @since          Version 3.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System;

use O2System\Metadata\Domain;

defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * System Config
 *
 * This class contains functions that enable config files to be managed
 *
 * @package        O2System
 * @subpackage     core
 * @category       Core Library Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/config.html
 */
final class Config extends Glob\ArrayObject
{
	/**
	 * List of loaded config files
	 *
	 * @access  protected
	 * @type array
	 */
	protected $_is_loaded = [ ];

	/**
	 * Glob Class Constructor
	 *
	 * Sets the $config data from the primary config.php file as a class variable
	 *
	 * @access  public
	 */
	final public function __construct()
	{
		if ( is_file( $filepath = APPSPATH . 'config/' . ENVIRONMENT . '/config.php' ) )
		{
			include( $filepath );
		}
		elseif ( is_file( $filepath = APPSPATH . 'config/config.php' ) )
		{
			include( $filepath );
		}

		// Does the $config array exist in the file?
		if ( ! isset( $config ) OR ! is_array( $config ) )
		{
			throw new \Exception( 'Your config file does not appear to be formatted correctly.' );
			exit( 3 ); // EXIT_CONFIG
		}

		if ( empty( $config[ 'domain' ] ) AND ! is_cli() )
		{
			$config[ 'domain' ] = isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ];
		}

		// Set default timezone
		if ( isset( $config[ 'timezone' ] ) )
		{
			date_default_timezone_set( $config[ 'timezone' ] );
		}

		parent::__construct( $config );

		$this->_is_loaded[ 'config' ] = 'config.php';
	}

	// ------------------------------------------------------------------------

	/**
	 * Load
	 *
	 * This method is used for loading config file
	 *
	 * @access  public
	 *
	 * @param   string  $item   config filename
	 * @param   boolean $return set to TRUE if you want this method to return the config item value
	 *
	 * @return  mixed
	 */
	public function load( $item, $return = FALSE )
	{
		if ( is_file( $item ) )
		{
			include( $item );
			$item = pathinfo( $item, PATHINFO_FILENAME );
		}
		else
		{
			$config_paths = \O2System::Load()->getPackagePaths( 'config', TRUE );

			foreach ( $config_paths as $config_path )
			{
				if ( is_file( $filepath = $config_path . ENVIRONMENT . '/' . $item . '.php' ) )
				{
					include( $filepath );
				}
				elseif ( is_file( $filepath = $config_path . $item . '.php' ) )
				{
					include( $filepath );
				}
			}
		}

		if ( $item === 'config' )
		{
			if ( isset( $config ) )
			{
				foreach ( $config as $key => $value )
				{
					$this->offsetSet( $key, $value );
				}

				if ( $return === TRUE )
				{
					return $this;
				}

				return TRUE;
			}
		}
		else
		{
			if ( isset( $$item ) )
			{
				// Set Item
				$this->offsetSet( $item, $$item );

				if ( $return === TRUE )
				{
					return $this->offsetGet( $item );
				}

				return TRUE;
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Base URL
	 *
	 * Returns base_url [. uri_string]
	 *
	 * @param    string|string[] $uri URI string or an array of segments
	 * @param    string          $protocol
	 *
	 * @return    string
	 */
	public function baseUrl( $uri = NULL, $suffix = NULL, $query = [ ] )
	{
		$base_url = $this->offsetGet( 'base_url' );

		if ( empty( $base_url ) )
		{
			$base_url = \O2System::$active[ 'domain' ];
		}
		else
		{
			$base_url = new Domain( parse_domain( $base_url )->__toArray() );
		}

		return $base_url->url( $uri, $query );
	}
}