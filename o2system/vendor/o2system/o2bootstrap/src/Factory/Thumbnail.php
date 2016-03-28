<?php
/**
 * Created by PhpStorm.
 * User: Steeven
 * Date: 28/10/2015
 * Time: 13:24
 *
 */

namespace O2System\Bootstrap\Factory;

use O2System\Bootstrap\Interfaces\FactoryInterface;

class Thumbnail extends FactoryInterface
{
	protected $_tag        = 'div';
	protected $_attributes = array(
		'class' => [ 'thumbnail' ],
	);

	protected $_template = NULL;

	public $image       = NULL;
	public $caption     = NULL;
	public $description = NULL;
	public $link        = NULL;
	public $buttons     = array();

	public function build()
	{
		@list( $image, $attr ) = func_get_args();

		if ( is_string( $image ) )
		{
			$this->set_image( $image );
		}
		elseif ( $image instanceof Image )
		{
			$this->image = $image;
		}
		elseif ( is_array( $image ) )
		{
			$attr = $image;
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

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
		elseif ( $image instanceof Embed )
		{
			$this->image = $image;
		}
		else
		{
			$this->image = new Image( Image::THUMBNAIL_IMAGE );
			$this->image->set_source( $image );
		}

		return $this;
	}

	public function set_caption( $caption, $tag = 'h3', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
			$tag = 'h3';
		}

		if ( $caption instanceof FactoryInterface )
		{
			$this->caption = $caption;
		}
		else
		{
			$this->caption = new Tag( $tag, $caption, $attr );
		}

		$this->caption->add_class('thumbnail-caption');

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

		$this->description->add_class( 'thumbnail-description' );

		return $this;
	}

	public function set_template( $template )
	{
		if ( is_file( $template ) )
		{
			$this->_template = file_get_contents( $template );
		}
		else
		{
			$this->_template = $template;
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

	public function render()
	{
		if ( ! empty( $this->image ) )
		{
			if ( isset( $this->_template ) )
			{
				$vars = get_object_vars( $this );
				extract( $vars );

				ob_start();

				// If the PHP installation does not support short tags we'll
				// do a little string replacement, changing the short tags
				// to standard PHP echo statements.
				if ( ! ini_get( 'short_open_tag' ) AND
					function_usable( 'eval' )
				)
				{
					echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', str_replace( '<?=', '<?php echo ', $this->_template ) ) );
				}
				else
				{
					echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', $this->_template ) );
				}

				$output = ob_get_contents();
				ob_end_clean();

				return $output;
			}
			else
			{
				if ( isset( $this->link ) )
				{
					$output[] = new Link( $this->image, $this->link );
					$caption = new Link( $this->caption, $this->link, [ 'class' => 'caption' ] );
				}
				else
				{
					$output[] = $this->image;
					$caption = new Tag( 'div', $this->caption, [ 'class' => 'caption' ] );
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

				$output[] = $caption;

				return ( new Tag( $this->_tag, implode( PHP_EOL, $output ), $this->_attributes ) )->render();
			}
		}

		return '';
	}
}