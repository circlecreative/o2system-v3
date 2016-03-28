<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/26/2016
 * Time: 9:50 PM
 */

namespace O2System\Bootstrap\Factory;

use O2System\Bootstrap\Interfaces\FactoryInterface;

class Slide extends FactoryInterface
{
	protected $_tag        = 'div';
	protected $_attributes = array(
		'class' => [ 'container' ],
	);

	public $image       = NULL;
	public $caption     = NULL;
	public $description = NULL;
	public $link        = NULL;
	public $buttons     = array();

	// ------------------------------------------------------------------------

	/**
	 * build
	 *
	 * @return object
	 */
	public function build()
	{
		@list( $attr ) = func_get_args();

		if ( is_array( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function __clone()
	{
		foreach ( [ 'image', 'caption', 'description', 'link' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		foreach ( $this->buttons as $key => $button )
		{
			$this->buttons[ $key ] = clone $button;
		}

		return $this;
	}

	public function set_image( $image )
	{
		if ( $image instanceof Image )
		{
			$this->image = $image;
		}
		else
		{
			$this->image = new Image( Image::RESPONSIVE_IMAGE );
			$this->image->set_source( $image );
		}

		return $this;
	}

	/**
	 * Alert Title
	 *
	 * @param string $caption
	 * @param string $tag
	 *
	 * @return object
	 */
	public function set_caption( $caption, $tag = 'h1', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
		}

		if ( $caption instanceof FactoryInterface )
		{
			$this->caption = clone $caption;
			$this->caption->set_tag( $tag );
			$this->caption->add_class( 'carousel-header' );
		}
		else
		{
			if ( isset( $attr[ 'class' ] ) )
			{
				if ( is_array( $attr[ 'class' ] ) )
				{
					array_push( $attr[ 'class' ], 'carousel-header' );
				}
				else
				{
					$attr[ 'class' ] = $attr[ 'class' ] . ' carousel-header';
				}
			}
			else
			{
				$attr[ 'class' ] = 'carousel-header';
			}

			$this->caption = new Tag( $tag, ucwords( $caption ), $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

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

	// ------------------------------------------------------------------------

	public function add_button( $label, $attr = array() )
	{
		if ( $label instanceof Button )
		{
			$this->buttons[] = $label;
		}
		elseif ( $label instanceof Link )
		{
			$this->buttons[] = $label;
		}
		else
		{
			$this->buttons = new Button( $label, $attr );
		}

		return $this;
	}

	/**
	 * Render
	 *
	 * @return
	 */
	public function render()
	{
		if ( ! empty( $this->image ) )
		{
			if ( isset( $this->link ) )
			{
				$image = clone $this->link;
				$image->set_label( $this->image );

				$this->link->set_label( $this->caption );
				$caption = new Tag( 'div', $this->link, [ 'class' => 'carousel-caption' ] );
			}
			else
			{
				$image = $this->image;
				$caption = new Tag( 'div', $this->caption, [ 'class' => 'carousel-caption' ] );
			}

			if ( isset( $this->description ) )
			{
				$caption->append_content( $this->description );
			}

			if ( ! empty( $this->buttons ) )
			{
				$button = new Tag( 'p', implode( PHP_EOL, $this->buttons ), [ 'class' => 'thumbnail-buttons' ] );

				$caption->append_content( $button );
			}

			return $image . ( new Tag( $this->_tag, $caption, $this->_attributes ) )->render();
		}

		return '';
	}
}