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

/**
 *
 * @package Image
 */
class Image extends FactoryInterface
{
	const CIRCLE_IMAGE     = 'circle';
	const RESPONSIVE_IMAGE = 'responsive';
	const THUMBNAIL_IMAGE  = 'thumbnail';

	protected $_tag        = 'img';
	protected $_attributes = array(
		'class' => [ 'img' ],
	);

	protected $_realpath = NULL;

	// ------------------------------------------------------------------------

	/**
	 * build
	 *
	 * @return object
	 */
	public function build()
	{
		@list( $source, $type, $attr ) = func_get_args();

		if ( isset( $source ) )
		{
			if ( is_string( $source ) )
			{
				if ( in_array( $source, [ 'circle', 'responsive', 'thumbnail' ] ) )
				{
					$type = $source;
				}
				else
				{
					$this->set_source( $source );
				}
			}
			elseif ( is_array( $source ) )
			{
				$attr = $source;
			}
		}

		if ( isset( $type ) )
		{
			if ( is_string( $type ) )
			{
				if ( in_array( $type, [ 'circle', 'responsive', 'thumbnail' ] ) )
				{
					$this->add_class( 'img-' . $type );
				}
			}
			elseif ( is_array( $type ) )
			{
				$attr = $type;
			}
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function set_source( $source )
	{
		if ( is_file( $source ) )
		{
			$this->_realpath = $source;
			$source = path_to_url( $source );
			$this->_attributes[ 'src' ] = $source;
		}
		elseif ( strpos( $source, 'http' ) !== FALSE )
		{
			$this->_attributes[ 'src' ] = $source;
		}

		return $this;
	}

	/**
	 * SizeInterface
	 *
	 * @param string $width
	 * @param string $height
	 *
	 * @return object
	 */
	public function set_size( array $size )
	{
		$key = key( $size );

		if ( is_numeric( $key ) )
		{
			$this->set_width( reset( $size ) );
			$this->set_height( end( $size ) );
		}
		elseif ( isset( $size[ 'width' ] ) )
		{
			$this->set_width( $size[ 'width' ] );
			$this->set_height( $size[ 'height' ] );
		}

		return $this;
	}

	public function set_width( $width )
	{
		$this->_attributes[ 'width' ] = $width;

		return $this;
	}

	public function set_height( $height )
	{
		$this->_attributes[ 'height' ] = $height;

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * alt
	 *
	 * @param string $alt
	 *
	 * @return object
	 */
	public function set_alt( $alt )
	{
		$this->_attributes[ 'alt' ] = $alt;

		return $this;
	}

	// ------------------------------------------------------------------------

	public function get_realpath()
	{
		return $this->_realpath;
	}

	/**
	 * Render
	 *
	 * @return null|string
	 */
	public function render()
	{
		if ( isset( $this->_attributes[ 'src' ] ) )
		{
			return ( new Tag( $this->_tag, $this->_attributes ) )->render();
		}

		return '';
	}
}