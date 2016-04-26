<?php
/**
 * O2Cache
 *
 * An open source PHP Cache Management for PHP 5.4 or newer
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
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2cache/license.html
 * @license        http://opensource.org/licenses/MIT   MIT License
 * @link           http://circle-creative.com/products/o2cache.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System
{
	use O2System\Cache\Exception;
	use O2System\Glob\Interfaces\DriverInterface;
	use O2System\Glob\Interfaces\LibraryInterface;

	/**
	 * Caching Class
	 *
	 * @package        o2cache
	 * @category       Bootstrap
	 * @author         O2System Developer Team
	 * @link
	 */
	class Cache extends LibraryInterface
	{
		/**
		 * Cache Storage Driver
		 *
		 * @access  protected
		 * @type    object
		 */
		protected $_driver;

		// ------------------------------------------------------------------------

		public function __reconstruct( array $config = array() )
		{
			parent::__reconstruct( $config );

			$this->initialize();
		}

		// ------------------------------------------------------------------------

		/**
		 * Setup Cache Driver
		 *
		 * @param array $config
		 *
		 * @return  mixed
		 * @throws  Exception
		 */
		public function initialize( $config = array() )
		{
			$config = empty( $config ) ? $this->_config : $config;

			if ( ! empty( $config ) )
			{
				if ( isset( $config[ 'storage' ] ) AND isset( $config[ 'failover' ] ) )
				{
					if ( $driver = $this->initialize( $config[ 'storage' ] ) === FALSE )
					{
						$driver = $this->initialize( $config[ 'failover' ] );
					}

					return $driver;
				}
				elseif( isset( $config ['storage' ] ) )
				{
					return $this->initialize( $config[ 'storage' ] );
				}
				elseif ( isset( $config[ 'driver' ] ) )
				{
					if(empty($this->_config))
					{
						$this->_config = $config;
					}

					$driver = $this->_loadDriver( $config[ 'driver' ] );
					$driver->setConfig( $config );

					if ( $driver === FALSE OR
						( $driver instanceof DriverInterface AND $driver->is_supported() === FALSE )
					)
					{
						throw new Cache\UnsupportedDriverException( 'CACHE_UNSUPPORTEDDRIVER', 2002, $config );
					}
					else
					{
						$this->_driver = $config[ 'driver' ];

						return $driver->initialize();
					}
				}

				throw new Cache\UndefinedDriverException( 'CACHE_UNDEFINEDDRIVER', 2001 );
			}
		}

		// ------------------------------------------------------------------------

		public function __call( $method, $args = array() )
		{
			if ( method_exists( $this->{$this->_driver}, $method ) )
			{
				return call_user_func_array( array( $this->{$this->_driver}, $method ), $args );
			}
		}
	}
}

namespace O2System\Cache
{
	use O2System\Glob\Interfaces\ExceptionInterface;

	/**
	 * Class Exception
	 *
	 * @package O2System\Cache
	 */
	class Exception extends ExceptionInterface
	{
		public $library = array(
			'name'        => 'O2System Cache (O2Cache)',
			'description' => 'Open Source Cache Management Driver Library',
			'version'     => '1.0',
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * Class UndefinedDriverException
	 *
	 * @package O2System\Cache
	 */
	class UndefinedDriverException extends Exception
	{
	}

	// ------------------------------------------------------------------------

	/**
	 * Class UnsupportedDriverException
	 *
	 * @package O2System\Cache
	 */
	class UnsupportedDriverException extends Exception
	{
		public function __construct( $message, $code, $args = array() )
		{
			$this->_args = $args;
			parent::__construct( $message, $code );
		}
	}
}
