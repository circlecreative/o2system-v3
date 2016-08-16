<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 9:08 AM
 */

namespace O2System\Core\HTML;


use O2System\Core\SPL\ArrayObject;

class Meta extends ArrayObject
{
	/**
	 * Set Metadata Variables
	 *
	 * @access public
	 *
	 * @param   array $tags List of Metadata Tags Variables
	 *
	 * @return $this
	 */
	public function set($meta, $value = NULL)
	{
		$this->clearStorage();
		$this->add( $meta );

		return $this;
	}

	// ------------------------------------------------------------------------

	public function add( $meta, $content = NULL )
	{
		if ( is_array( $meta ) )
		{
			foreach ( $meta as $name => $content )
			{
				$this->add( $name, $content );
			}
		}
		elseif ( is_string( $meta ) )
		{
			if ( $meta === 'http-equiv' )
			{
				$value = key( $content );

				$this->offsetSet(
					'http_equiv_' . $value,
					new Tag(
						'meta', [
						'http-equiv' => $value,
						'content'    => $content[ $value ],
					] ) );
			}
			elseif ( $meta === 'property' )
			{
				$value = key( $content );

				$this->offsetSet(
					'property_' . $value,
					new Tag(
						'meta', [
						'property' => $value,
						'content'  => $content[ $value ],
					] ) );
			}
			else
			{
				$this->offsetSet(
					underscore( $meta ),
					new Tag(
						'meta', [
						'name'    => $meta,
						'content' => ( is_array( $content ) ? implode( ', ', $content ) : $content ),
					] ) );
			}
		}

		return $this;
	}

	public function setCharset( $charset )
	{
		$this->offsetSet( 'charset', $charset );

		return $this;
	}

	/**
	 * Render Metadata
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function render()
	{
		if ( $this->isEmpty() === FALSE )
		{
			return implode( PHP_EOL, $this->getArrayCopy() );
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}
}