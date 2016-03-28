<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/26/2016
 * Time: 7:47 PM
 */

namespace O2System\Bootstrap\Factory;


use O2System\Bootstrap\Interfaces\FactoryInterface;

class Pager extends Lists
{
	protected $_attributes = array(
		'class' => [ 'pager' ],
	);

	protected $_is_aligned = FALSE;

	public $previous;
	public $next;

	public function build()
	{
		@list( $previous, $next, $attr ) = func_get_args();

		if ( isset( $previous ) )
		{
			if ( is_array( $previous ) )
			{
				$attr = $previous;
			}
			else
			{
				$this->set_previous( $previous );
			}
		}

		if ( isset( $next ) )
		{
			if ( is_array( $next ) )
			{
				$attr = $next;
			}
			else
			{
				$this->set_next( $next );
			}
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	public function __clone()
	{
		foreach ( [ 'previous', 'next' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		return $this;
	}

	public function is_aligned()
	{
		$this->_is_aligned = TRUE;

		return $this;
	}

	public function set_previous( $previous, $link, $type = NULL, $attr = array() )
	{
		if ( is_array( $link ) )
		{
			$attr = $link;
		}

		if ( isset( $type ) )
		{
			if ( is_array( $type ) )
			{
				$attr = $type;
			}
		}

		if ( $previous instanceof FactoryInterface )
		{
			$this->previous = $previous;

			if ( $this->_is_aligned === TRUE )
			{
				$this->previous->add_class( 'previous' );
			}
		}
		else
		{
			if ( $this->_is_aligned === TRUE )
			{
				if ( isset( $attr[ 'class' ] ) )
				{
					if ( is_array( $attr[ 'class' ] ) )
					{
						array_push( $attr[ 'class' ], 'previous' );
					}
					else
					{
						$attr[ 'class' ] = $attr[ 'class' ] . ' previous';
					}
				}
				else
				{
					$attr[ 'class' ] = 'previous';
				}
			}

			$this->previous = new Link( $previous, $link, $attr );
		}

		parent::add_item( $this->previous, $type, $attr );

		return $this;
	}

	public function set_next( $next, $link, $type = NULL, $attr = array() )
	{
		if ( is_array( $link ) )
		{
			$attr = $link;
		}

		if ( isset( $type ) )
		{
			if ( is_array( $type ) )
			{
				$attr = $type;
			}
		}

		if ( $next instanceof FactoryInterface )
		{
			$this->next = $next;

			if ( $this->_is_aligned === TRUE )
			{
				$this->next->add_class( 'next' );
			}
		}
		else
		{
			if ( $this->_is_aligned === TRUE )
			{
				if ( isset( $attr[ 'class' ] ) )
				{
					if ( is_array( $attr[ 'class' ] ) )
					{
						array_push( $attr[ 'class' ], 'next' );
					}
					else
					{
						$attr[ 'class' ] = $attr[ 'class' ] . ' next';
					}
				}
				else
				{
					$attr[ 'class' ] = 'next';
				}
			}

			$this->next = new Link( $next, $link, $attr );
		}

		parent::add_item( $this->next, $type, $attr );

		return $this;
	}

	public function render()
	{
		if ( isset( $this->previous ) AND isset( $this->next ) )
		{
			return ( new Tag( 'nav', parent::render() ) )->render();
		}

		return '';
	}
}