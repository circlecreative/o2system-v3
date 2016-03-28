<?php
/**
 * O2Bootstrap
 *
 * An open source bootstrap components factory for PHP 5.4+
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
 * @package     O2Bootstrap
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2bootstrap/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2parser.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System
{
	/**
	 * Bootstrap
	 *
	 * Bootstrap Library
	 *
	 * @package       O2Bootstrap
	 * @author        Steeven Andrian Salim
	 * @copyright     Copyright (c) 2005 - 2015
	 * @license       http://www.circle-creative.com/products/o2bootstrap/license.html
	 * @link          http://circle-creative.com
	 *                http://o2system.in
	 */
	class Bootstrap
	{
		/**
		 * Valid Bootstrap Factories
		 *
		 * @access    protected
		 */
		protected $_valid_factories = array();

		// ------------------------------------------------------------------------

		/**
		 * Class Constructor
		 *
		 * @access    public
		 */
		public function __construct()
		{
			foreach ( glob( __DIR__ . DIRECTORY_SEPARATOR . 'Factory' . DIRECTORY_SEPARATOR . '*.php' ) as $filepath )
			{
				$this->_valid_factories[ strtolower( pathinfo( $filepath, PATHINFO_FILENAME ) ) ] = $filepath;
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * @param $factory
		 *
		 * @return object
		 */
		public function __get( $factory )
		{
			if ( array_key_exists( $factory, $this->_valid_factories ) )
			{
				return $this->_load_factory( $factory );
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Magic Call Method
		 *
		 * @param       $factory
		 * @param array $args
		 *
		 * @return mixed
		 */
		public function __call( $factory, $args = array() )
		{
			if ( array_key_exists( $factory, $this->_valid_factories ) )
			{
				$factory =& $this->_load_factory( $factory );

				return call_user_func_array( array( $factory, 'build' ), $args );
			}
		}

		// ------------------------------------------------------------------------

		/**
		 * Load driver
		 *
		 * Separate load_driver call to support explicit driver load by library or user
		 *
		 * @param   string $factory driver class name (lowercase)
		 *
		 * @return    object    Driver class
		 */
		protected function &_load_factory( $factory )
		{
			if ( empty( $this->{$factory} ) )
			{
				if ( is_file( $filepath = $this->_valid_factories[ $factory ] ) )
				{
					require_once( $filepath );

					$class_name = get_called_class() . '\\Factory\\' . ucfirst( $factory );

					if ( class_exists( $class_name, FALSE ) )
					{
						$this->{$factory} = new $class_name();
					}
				}
			}

			return $this->{$factory};
		}
	}
}

namespace O2System\Bootstrap
{
	use O2System\Glob\Interfaces\ExceptionInterface;

	/**
	 * Class Exception
	 *
	 * @package O2System\Bootstrap
	 */
	class Exception extends ExceptionInterface
	{
		/**
		 * Exception constructor.
		 *
		 * @param string $message
		 * @param int    $code
		 *
		 * @access  public
		 */
		public function __construct( $message, $code = 0 )
		{
			parent::__construct( $message, $code );
		}
	}
}