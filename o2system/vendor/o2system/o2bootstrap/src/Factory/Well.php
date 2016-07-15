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
use O2System\Bootstrap\Interfaces\SizeInterface;
use O2System\Bootstrap\Interfaces\ContentInterface;

/**
 *
 * @package well
 */
class Well extends FactoryInterface
{
	use ContentInterface;
	use SizeInterface;

	const SMALL_WELL  = 'small';
	const MEDIUM_WELL = 'medium';
	const LARGE_WELL  = 'large';

	protected $_tag        = 'div';
	protected $_attributes = [
		'class' => [ 'well' ],
	];

	// ------------------------------------------------------------------------

	/**
	 * build
	 *
	 * @return object
	 */
	public function build()
	{
		@list( $content, $type, $attr ) = func_get_args();

		$this->setSizeClassPrefix( 'well' );

		if ( is_array( $content ) )
		{
			if ( ! isset( $content[ 'id' ] ) OR
				! isset( $content[ 'class' ] ) OR
				! isset( $content[ 'style' ] )
			)
			{
				$this->setContent( $content );
			}
			else
			{
				$attr = $content;
			}
		}
		elseif ( is_string( $content ) )
		{
			if ( in_array( $content, $this->_sizes ) AND $content !== 'tiny' )
			{
				$this->{'is' . studlycapcase( $content )}();
			}
			else
			{
				$this->setContent( $content );
			}
		}

		if ( isset( $type ) )
		{
			if ( is_array( $type ) )
			{
				$this->addAttributes( $type );
			}
			elseif ( is_string( $type ) )
			{
				if ( in_array( $type, $this->_sizes ) AND $type !== 'tiny' )
				{
					$this->{'is' . studlycapcase( $type )}();
				}
			}
		}

		if ( isset( $attr ) )
		{
			if ( is_array( $attr ) )
			{
				$this->addAttributes( $attr );
			}
			elseif ( is_string( $attr ) )
			{
				if ( in_array( $attr, $this->_sizes ) AND $attr !== 'tiny' )
				{
					$this->{'is' . studlycapcase( $attr )}();
				}
			}
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Render
	 *
	 * @return null|string
	 */
	public function render()
	{
		if ( ! empty( $this->_content ) )
		{
			return ( new Tag( $this->_tag, implode( PHP_EOL, $this->_content ), $this->_attributes ) )->render();
		}

		return '';
	}
}