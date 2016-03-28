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

class Embed extends FactoryInterface
{
	protected $_tag          = 'div';
	protected $_aspect_ratio = '16:9';
	protected $_attributes   = array(
		'class' => [ 'embed-responsive' ],
	);

	public $source = NULL;

	public function build()
	{
		@list( $src, $aspect_ratio, $attr ) = func_get_args();

		if ( is_string( $src ) )
		{
			if ( in_array( $src, [ '16:9', '4:3' ] ) )
			{
				$this->set_aspect_ratio( $src );
			}
			else
			{
				$this->set_source( $src );
			}
		}
		elseif ( is_array( $src ) )
		{
			$attr = $src;
		}

		if ( is_array( $aspect_ratio ) )
		{
			$attr = $aspect_ratio;
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}
	}

	public function set_aspect_ratio( $aspect_ratio )
	{
		if ( in_array( $aspect_ratio, [ '16:9', '4:3' ] ) )
		{
			$this->_aspect_ratio = $aspect_ratio;
		}

		return $this;
	}

	public function set_source( $source, $tag = 'iframe', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
			$tag = 'iframe';
		}

		$attr[ 'src' ] = str_replace( array(
			                              'youtube.com/',
			                              'youtu.be',
		                              ), array(
			                              'youtube.com/embed/',
			                              'youtube.com/embed/',
		                              ), $source );

		if ( strpos( $attr[ 'src' ], 'youtube.com' ) !== FALSE )
		{
			$attr[ 'src' ] = $attr[ 'src' ] . '?rel=0';
		}

		if ( isset( $attr[ 'class' ] ) )
		{
			if ( is_array( $attr[ 'class' ] ) )
			{
				array_push( $attr[ 'class' ], 'embed-responsive-item' );
			}
			else
			{
				$attr[ 'class' ] = $attr[ 'class' ] . ' embed-responsive-item';
			}
		}
		else
		{
			$attr[ 'class' ] = 'embed-responsive-item';
		}

		if ( $tag === 'iframe' )
		{
			$attr[ 'allowfullscreen' ] = '';
		}

		$this->source = new Tag( $tag, $attr );

		return $this;
	}

	public function render()
	{
		if ( ! empty( $this->source ) )
		{
			switch ( $this->_aspect_ratio )
			{
				case '4:3':
					
					$this->add_class( 'embed-responsive-4by3' );

					break;
				
				default:
					
					$this->add_class( 'embed-responsive-16by9' );

					break;
			}

			return ( new Tag( $this->_tag, $this->source, $this->_attributes ) )->render();
		}

		return '';
	}
}