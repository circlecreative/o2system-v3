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
	protected $_is_loaded = array();

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
	public function baseURL( $uri = NULL, $suffix = NULL, $protocol = NULL )
	{
		// Set the base_url automatically if none was provided
		if ( $this->offsetExists( 'domain' ) )
		{
			$domain = $this->offsetGet( 'domain' );
		}

		if ( empty( $domain ) )
		{
			if ( \O2System::$active->offsetExists( 'domain' ) )
			{
				$domain = \O2System::$active->offsetGet( 'domain' );
			}
		}

		if ( \O2System::$active->offsetExists( 'sub_domain' ) )
		{
			$sub_domain = \O2System::$active->offsetGet( 'sub_domain' );

			if ( ! empty( $sub_domain ) )
			{
				$domain = $sub_domain . '.' . str_replace( 'www.', '', $domain );
			}
		}

		$base_url = $this->offsetGet( 'base_url' );

		if ( empty( $base_url ) )
		{
			$base_url = is_https() ? 'https' : 'http';
			$base_url .= '://' . ( isset( $domain ) ? $domain : '' );

			// Add server port if needed
			if ( isset( $_SERVER[ 'SERVER_PORT' ] ) )
			{
				$base_url .= $_SERVER[ 'SERVER_PORT' ] !== '80' ? ':' . $_SERVER[ 'SERVER_PORT' ] : '';
			}

			// Add base path
			if ( ! is_cli() )
			{
				$base_url .= dirname( $_SERVER[ 'SCRIPT_NAME' ] );
			}
			$base_url = str_replace( DIRECTORY_SEPARATOR, '/', $base_url );
			$base_url = trim( $base_url, '/' ) . '/';
		}

		if ( isset( $uri ) )
		{
			$uri = is_array( $uri ) ? implode( '/', $uri ) : $uri;
			$base_url = $base_url . trim( $uri, '/' );
		}

		if ( isset( $suffix ) )
		{
			if ( is_bool( $suffix ) )
			{
				$URI = $this->offsetGet( 'URI' );
				$suffix = ( empty( $URI[ 'suffix' ] ) ) ? '' : $URI[ 'suffix' ];

				$base_url = rtrim( $base_url, $suffix );
			}
			elseif ( is_array( $suffix ) )
			{
				$http_query = (array) $_GET;
				$http_query = array_diff($suffix, $http_query);

				$suffix = array_merge( $suffix, $http_query );
				$suffix = array_unique( $suffix );

				$suffix = '/?' . http_build_query( $suffix );
			}
		}

		if ( ! empty( $base_url ) )
		{
			$extension = pathinfo( $base_url, PATHINFO_EXTENSION );

			if ( empty( $extension ) )
			{
				$base_url = $base_url . $suffix;
			}
		}

		if ( isset( $protocol ) )
		{
			$base_url = $protocol . substr( $base_url, strpos( $base_url, '://' ) );
		}

		return $base_url;
	}
}