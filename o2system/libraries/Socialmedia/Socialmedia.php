<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
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
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------
namespace O2System\Libraries
{
	defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

	use O2System\Glob\Interfaces\LibraryInterface;

	class Socialmedia extends LibraryInterface
	{
		protected $_media = [ ];

		public function initialize( array $media = [ ] )
		{
			if ( $config = \O2System::$config->load( 'social', TRUE ) )
			{
				$this->_config = $config;
			}

			$media = empty( $media ) ? array_keys( $this->_config ) : $media;

			$this->setMedia( $media );
		}

		public function setMedia( array $media = [ ] )
		{
			foreach ( $media as $driver )
			{
				if ( array_key_exists( $driver, $this->_valid_drivers ) )
				{
					$this->_media[] = $driver;
				}
			}
		}

		public function getShareLinks( $link = NULL )
		{
			$link  = isset( $link ) ? $link : current_url();
			$links = [ ];

			if ( count( $this->_media ) > 0 )
			{
				foreach ( $this->_media as $media )
				{
					if ( method_exists( $this->{$media}, 'getShareLink' ) )
					{
						$links[ $media ] = $this->{$media}->getShareLink( $link );
					}
				}
			}

			return $links;
		}

		public function getAuthorizeLinks( $callback_url )
		{
			$links = [ ];

			if ( count( $this->_media ) > 0 )
			{
				foreach ( $this->_media as $media )
				{
					if ( method_exists( $this->{$media}, 'getAuthorizeLink' ) )
					{
						$links[ $media ] = $this->{$media}->getAuthorizeLink( rtrim( $callback_url, '/' ) . '/' . $media );
					}
				}
			}

			return $links;
		}

		public function post_feed( array $feed )
		{

		}

		public function post_link( array $link )
		{

		}

		public function post_media( array $media )
		{

		}

		public function share( $url )
		{

		}

		/**
		 *
		 * @todo store database
		 *
		 * @param $driver
		 * @param $session
		 */
		public function store_access( $driver, Session $session )
		{
			// save to db
			print_out( $session->storage( 'json' ) );
		}
	}
}

namespace O2System\Libraries\Socialmedia
{
	use O2System\Glob\Interfaces\ExceptionInterface;

	/**
	 * Class Exception
	 *
	 * @package O2System\Cache
	 */
	class Exception extends ExceptionInterface
	{
		public $library = [
			'name'        => 'O2System Socialmedia (O2Socialmedia)',
			'description' => 'Commercial PHP Socialmedia Management Library',
			'version'     => '1.0',
		];
	}

	// ------------------------------------------------------------------------
}