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

namespace O2System\Bootstrap\Factory;


use O2System\Bootstrap\Interfaces\FactoryInterface;
use O2System\Bootstrap\Interfaces\PrintableInterface;
use O2System\Bootstrap\Interfaces\ResponsiveInterface;
use O2System\Bootstrap\Interfaces\TypographyInterface;

class Badge extends FactoryInterface
{
	use PrintableInterface;
	use ResponsiveInterface;
	use TypographyInterface;

	protected $_tag      = 'span';
	protected $_value    = NULL;
	public    $container = NULL;

	protected $_attributes = array(
		'class' => [ 'badge' ],
	);

	public function build()
	{
		@list( $value, $container, $attr ) = func_get_args();

		if ( is_string( $value ) OR is_numeric( $value ) )
		{
			$this->_value = $value;
		}
		elseif ( $value instanceof FactoryInterface )
		{
			$container = $value;
		}
		elseif ( is_array( $value ) )
		{
			$attr = $value;
		}

		if ( isset( $container ) )
		{
			$this->setContainer( $container );
		}

		if ( isset( $attr ) )
		{
			$this->addAttributes( $attr );
		}

		return $this;
	}

	public function setValue($badge )
	{
		$this->_value = $badge;

		return $this;
	}

	public function setContainer($container )
	{
		$this->container = $container;

		return $this;
	}

	public function __clone()
	{
		if ( is_object( $this->container ) )
		{
			$this->container = clone $this->container;
		}

		return $this;
	}

	/**
	 * Render
	 *
	 * @return null|string
	 */
	public function render()
	{
		if ( isset( $this->_value ) )
		{
			if ( isset( $this->container ) )
			{
				if ( method_exists( $this->container, 'appendLabel' ) )
				{
					$this->container->appendLabel( new Tag( $this->_tag, $this->_value, $this->_attributes ) );
				}
				elseif ( method_exists( $this->container, 'appendContent' ) )
				{
					$this->container->appendContent( new Tag( $this->_tag, $this->_value, $this->_attributes ) );
				}

				return $this->container->render();
			}
			else
			{
				return ( new Tag( $this->_tag, $this->_value, $this->_attributes ) )->render();
			}
		}

		return '';
	}
}