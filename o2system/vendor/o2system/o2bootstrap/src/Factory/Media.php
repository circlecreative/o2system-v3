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

use O2System\Bootstrap\Interfaces\AlignmentInterface;
use O2System\Bootstrap\Interfaces\FactoryInterface;

class Media extends FactoryInterface
{
	protected $_tag        = 'div';
	protected $_attributes = array(
		'class' => [ 'media' ],
	);

	protected $_is_align_bottom = FALSE;
	protected $_is_align_middle = FALSE;

	public $left_media  = NULL;
	public $right_media = NULL;
	public $heading     = NULL;
	public $description = NULL;
	public $link        = NULL;
	public $childs      = array();

	public function build()
	{
		@list( $media, $attr ) = func_get_args();

		if ( is_string( $media ) )
		{
			$this->set_media( $media );
		}
		elseif ( $media instanceof Image )
		{
			$this->media = $media;
		}
		elseif ( is_array( $media ) )
		{
			$attr = $media;
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	public function __clone()
	{
		foreach ( [ 'left_media', 'right_media', 'heading', 'description', 'link' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		foreach ( $this->childs as $key => $child )
		{
			$this->childs[ $key ] = clone $child;
		}

		return $this;
	}

	public function set_media( $media )
	{
		return $this->set_left_media( $media );
	}

	public function set_left_media( $media )
	{
		if ( $media instanceof Image )
		{
			$this->left_media = $media;
		}
		elseif ( $media instanceof Embed )
		{
			$this->left_media = $media;
		}
		else
		{
			$this->left_media = new Image();
			$this->left_media->set_source( $media );
		}

		$this->left_media->add_class( 'media-object' );

		return $this;
	}

	public function set_alt_media( $media, $link = NULL )
	{
		return $this->set_right_media( $media, $link );
	}

	public function set_right_media( $media, $link = NULL )
	{
		if ( $media instanceof Image )
		{
			$this->right_media = $media;
		}
		elseif ( $media instanceof Embed )
		{
			$this->right_media = $media;
		}
		else
		{
			$this->right_media = new Image();
			$this->right_media->set_source( $media );
		}

		$this->right_media->add_class( 'media-object' );

		if ( isset( $link ) )
		{
			if ( isset( $this->left_media ) )
			{
				if ( $link instanceof Link )
				{
					$link = clone $link;
					$link->set_label( $this->right_media );
					$this->right_media = $link;
				}
				else
				{
					$this->right_media = new Link( $this->right_media, $link );
				}
			}
			else
			{
				$this->set_link( $link );
			}
		}

		return $this;
	}

	public function set_heading( $heading, $tag = 'h4', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
			$tag = 'h3';
		}

		if ( $heading instanceof Tag )
		{
			$this->heading = $heading;
		}
		else
		{
			$this->heading = new Tag( $tag, $heading, $attr );
		}

		$this->heading->add_class( 'media-caption' );

		return $this;
	}

	public function set_description( $description, $tag = 'p', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
			$tag = 'p';
		}

		if ( $description instanceof Tag )
		{
			$this->description = $description;
		}
		else
		{
			$this->description = new Tag( $tag, $description, $attr );
		}

		$this->description->add_class( 'media-description' );

		return $this;
	}

	/**
	 * link
	 *
	 * @param string $link
	 * @param string $href
	 * @param string $attributes
	 *
	 * @return object
	 */
	public function set_link( $link, $attr = array() )
	{
		if ( $link instanceof Link )
		{
			$this->link = clone $link;
		}
		else
		{
			$this->link = new Link( '', $link, $attr );
		}

		return $this;
	}

	public function align_bottom()
	{
		$this->_is_align_bottom = TRUE;

		return $this;
	}

	public function align_middle()
	{
		$this->_is_align_middle = TRUE;

		return $this;
	}

	public function add_child( Media $child )
	{
		$this->childs[] = $child;

		return $this;
	}

	public function render()
	{
		if ( ! empty( $this->left_media ) OR ! empty( $this->right_media ) )
		{
			if ( isset( $this->left_media ) )
			{
				if ( isset( $this->link ) )
				{
					$media = clone $this->link;
					$media->set_label( $this->left_media );
				}
				else
				{
					$media = $this->left_media;
				}

				$left_classes[] = 'media-left';

				if ( $this->_is_align_bottom === TRUE )
				{
					$left_classes[] = 'media-bottom';
				}
				elseif ( $this->_is_align_middle === TRUE )
				{
					$left_classes[] = 'media-middle';
				}

				$output[ 'left' ] = new Tag( 'div', $media, [ 'class' => $left_classes ] );
			}

			$body = new Tag( 'div', [ 'class' => 'media-body' ] );

			if ( isset( $this->link ) )
			{
				$heading = clone $this->link;
				$heading->set_label( $this->heading );
			}
			else
			{
				$heading = $this->heading;
			}

			$body->append_content( $heading );


			if ( isset( $this->description ) )
			{
				$body->append_content( $this->description );
			}

			if ( ! empty( $this->childs ) )
			{
				foreach ( $this->childs as $child )
				{
					$body->append_content( $child );
				}
			}

			$output[ 'body' ] = $body;

			if ( isset( $this->right_media ) )
			{
				if ( isset( $this->link ) )
				{
					$media = clone $this->link;
					$media->set_label( $this->right_media );
				}
				else
				{
					$media = $this->right_media;
				}

				$right_classes[] = 'media-right';

				if ( $this->_is_align_bottom === TRUE )
				{
					$right_classes[] = 'media-bottom';
				}
				elseif ( $this->_is_align_middle === TRUE )
				{
					$right_classes[] = 'media-middle';
				}

				$output[ 'right' ] = new Tag( 'div', $media, [ 'class' => $right_classes ] );
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $output ), $this->_attributes ) )->render();
		}

		return '';
	}
}