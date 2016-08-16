<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 9:10 AM
 */

namespace O2System\Core\HTML;


class Tag
{
	protected $element;
	protected $attributes;

	public function __construct( $element, array $attributes = [ ] )
	{
		$this->element = $element;
		$this->addAttributes( $attributes );
	}

	public function getElement()
	{
		return $this->element;
	}

	public function addAttributes( array $attributes )
	{
		foreach ( $attributes as $key => $value )
		{
			$this->addAttribute( $key, $value );
		}

		return $this;
	}

	public function addAttribute( $key, $value )
	{
		$this->attributes[ dash( $key ) ] = $value;

		return $this;
	}

	public function setAttributes( array $attributes )
	{
		$this->attributes = $attributes;

		return $this;
	}

	public function getAttributes()
	{
		return $this->attributes;
	}

	public function open()
	{
		$attr = $this->attributes;
		unset( $attr[ 'realpath' ] );

		return '<' . $this->element . $this->_attributesToString( $attr ) . '>';
	}

	public function close()
	{
		return '</' . $this->element . '>';
	}

	public function removeAttribute( $attribute )
	{
		unset( $this->attributes[ $attribute ] );

		return $this;
	}

	public function hasAttribute( $attribute )
	{
		return (bool) array_key_exists( $attribute, $this->attributes );
	}

	public function getAttribute( $key )
	{
		return isset( $this->attributes[ $key ] ) ? $this->attributes[ $key ] : NULL;
	}

	protected function _attributesToString( array $attributes = [ ] )
	{
		$attributes = empty( $attributes ) ? $this->attributes : $attributes;

		if ( is_object( $attributes ) && count( $attributes ) > 0 )
		{
			$attributes = (array) $attributes;
		}

		if ( is_array( $attributes ) )
		{
			$attr = '';

			if ( count( $attributes ) === 0 )
			{
				return $attr;
			}

			foreach ( $attributes as $key => $value )
			{
				if ( $key === 'class' )
				{
					if ( is_string( $value ) )
					{
						$value = explode( ' ', $value );
					}

					$value = array_map( 'trim', array_unique( $value ) );

					$value = implode( ' ', $value );
				}

				if ( is_array( $value ) )
				{
					$value = implode( ', ', $value );
				}

				if ( is_bool( $value ) )
				{
					$value = $value === TRUE ? 'true' : 'false';
				}

				if ( $key === 'js' )
				{
					$attr .= $key . '=' . $value . ',';
				}
				else
				{
					$attr .= ' ' . $key . '="' . htmlspecialchars( $value ) . '"';
				}
			}

			return rtrim( $attr, ',' );
		}
		elseif ( is_string( $attributes ) && strlen( $attributes ) > 0 )
		{
			return ' ' . $attributes;
		}

		return $attributes;
	}

	public function render()
	{
		$self_closing_tags = [
			'area',
			'base',
			'br',
			'col',
			'command',
			'embed',
			'hr',
			'img',
			'input',
			'keygen',
			'link',
			'meta',
			'param',
			'source',
			'track',
			'wbr',
		];

		if ( in_array( $this->element, $self_closing_tags ) )
		{
			unset( $this->attributes[ 'realpath' ] );

			return '<' . $this->element . $this->_attributesToString( $this->attributes ) . ' />';
		}
		else
		{
			$output[] = $this->open();

			if ( empty( $this->_content ) )
			{
				$output[] = $this->close();

				return implode( '', $output );
			}
			else
			{
				$output[] = implode( PHP_EOL, $this->_content );
				$output[] = $this->close();

				return implode( PHP_EOL, $output );
			}
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}
}