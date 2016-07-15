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
	protected $_attributes = [
		'class' => [ 'media' ],
	];

	protected $_is_align_bottom = FALSE;
	protected $_is_align_middle = FALSE;

	public $left_media  = NULL;
	public $right_media = NULL;
	public $heading     = NULL;
	public $description = NULL;
	public $link        = NULL;
	public $childs      = [ ];

	public function build()
	{
		@list( $media, $attr ) = func_get_args();

		if ( is_string( $media ) )
		{
			$this->setMedia( $media );
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
			$this->addAttributes( $attr );
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

	public function setMedia( $media )
	{
		return $this->setLeftMedia( $media );
	}

	public function setLeftMedia( $media )
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
			$this->left_media->setSource( $media );
		}

		$this->left_media->addClass( 'media-object' );

		return $this;
	}

	public function setAltMedia( $media, $link = NULL )
	{
		return $this->setRightMedia( $media, $link );
	}

	public function setRightMedia( $media, $link = NULL )
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
			$this->right_media->setSource( $media );
		}

		$this->right_media->addClass( 'media-object' );

		if ( isset( $link ) )
		{
			if ( isset( $this->left_media ) )
			{
				if ( $link instanceof Link )
				{
					$link = clone $link;
					$link->setLabel( $this->right_media );
					$this->right_media = $link;
				}
				else
				{
					$this->right_media = new Link( $this->right_media, $link );
				}
			}
			else
			{
				$this->setLink( $link );
			}
		}

		return $this;
	}

	public function setHeading( $heading, $tag = 'h4', $attr = [ ] )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
			$tag  = 'h3';
		}

		if ( $heading instanceof Tag )
		{
			$this->heading = $heading;
		}
		else
		{
			$this->heading = new Tag( $tag, $heading, $attr );
		}

		$this->heading->addClass( 'media-caption' );

		return $this;
	}

	public function setDescription( $description, $tag = 'p', $attr = [ ] )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
			$tag  = 'p';
		}

		if ( $description instanceof Tag )
		{
			$this->description = $description;
		}
		else
		{
			$this->description = new Tag( $tag, $description, $attr );
		}

		$this->description->addClass( 'media-description' );

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
	public function setLink( $link, $attr = [ ] )
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

	public function alignBottom()
	{
		$this->_is_align_bottom = TRUE;

		return $this;
	}

	public function alignMiddle()
	{
		$this->_is_align_middle = TRUE;

		return $this;
	}

	public function addChild( Media $child )
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
					$media->setLabel( $this->left_media );
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
				$heading->setLabel( $this->heading );
			}
			else
			{
				$heading = $this->heading;
			}

			$body->appendContent( $heading );


			if ( isset( $this->description ) )
			{
				$body->appendContent( $this->description );
			}

			if ( ! empty( $this->childs ) )
			{
				foreach ( $this->childs as $child )
				{
					$body->appendContent( $child );
				}
			}

			$output[ 'body' ] = $body;

			if ( isset( $this->right_media ) )
			{
				if ( isset( $this->link ) )
				{
					$media = clone $this->link;
					$media->setLabel( $this->right_media );
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