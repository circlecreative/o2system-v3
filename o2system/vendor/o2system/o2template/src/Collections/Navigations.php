<?php

namespace O2System\Template\Collections
{

	use IteratorAggregate;
	use Countable;
	use ArrayAccess;
	use O2System\Bootstrap\Factory\Nav;
	use O2System\Template\Collections\Navigations\Link;
	use Traversable;

	class Navigations implements IteratorAggregate, Countable, ArrayAccess
	{
		private $storage = [ ];

		public function __construct( array $navigations = [ ] )
		{
			foreach ( $navigations as $position => $navigation )
			{
				if ( is_numeric( $position ) )
				{
					if ( isset( $navigation[ 'parameter' ] ) )
					{
						$this->storage[ strtolower( $navigation[ 'parameter' ] ) ] = new Navigations\Navigation( $navigation );
					}
				}
				else
				{
					$this->storage[ $position ] = new Navigations\Navigation( $navigation );
				}
			}
		}

		public function __get( $property )
		{
			$property = str_replace( '-', '_', $property );

			if ( $this->offsetExists( $property ) )
			{
				return $this->offsetGet( $property );
			}
		}

		public function __call( $position, $args = [ ] )
		{
			if ( $this->offsetExists( $position ) )
			{

			}

			return '';
		}

		public function __set( $offset, $value )
		{
			$this->offsetSet( $offset, $value );
		}

		/**
		 * Retrieve an external iterator
		 *
		 * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
		 * @return Traversable An instance of an object implementing <b>Iterator</b> or
		 *        <b>Traversable</b>
		 * @since 5.0.0
		 */
		public function getIterator()
		{
			return new \ArrayIterator( $this->storage );
		}

		/**
		 * Whether a offset exists
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
		 *
		 * @param mixed $offset <p>
		 *                      An offset to check for.
		 *                      </p>
		 *
		 * @return boolean true on success or false on failure.
		 * </p>
		 * <p>
		 * The return value will be casted to boolean if non-boolean was returned.
		 * @since 5.0.0
		 */
		public function offsetExists( $offset )
		{
			return (bool) isset( $this->storage[ $offset ] );
		}

		/**
		 * Offset to retrieve
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to retrieve.
		 *                      </p>
		 *
		 * @return mixed Can return all value types.
		 * @since 5.0.0
		 */
		public function offsetGet( $offset )
		{
			return $this->offsetExists( $offset ) ? $this->storage[ $offset ] : NULL;
		}

		/**
		 * Offset to set
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to assign the value to.
		 *                      </p>
		 * @param mixed $value  <p>
		 *                      The value to set.
		 *                      </p>
		 *
		 * @return void
		 * @since 5.0.0
		 */
		public function offsetSet( $offset, $value )
		{
			$offset = str_replace( '-', '_', $offset );

			$this->storage[ $offset ] = $value;
		}

		/**
		 * Offset to unset
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to unset.
		 *                      </p>
		 *
		 * @return void
		 * @since 5.0.0
		 */
		public function offsetUnset( $offset )
		{
			unset( $this->storage[ $offset ] );
		}

		/**
		 * Count elements of an object
		 *
		 * @link  http://php.net/manual/en/countable.count.php
		 * @return int The custom count as an integer.
		 *        </p>
		 *        <p>
		 *        The return value is cast to an integer.
		 * @since 5.1.0
		 */
		public function count()
		{
			return count( $this->storage );
		}

		public function isEmpty()
		{
			return (bool) empty( $this->storage );
		}
	}
}

namespace O2System\Template\Collections\Navigations
{

	use O2System\Bootstrap\Factory\Nav;
	use O2System\Bootstrap\Interfaces\FactoryInterface;
	use O2System\Glob\ArrayObject;

	class Navigation extends ArrayObject
	{
		public function __construct( array $links )
		{
			foreach ( $links as $key => $link )
			{
				$key                = isset( $link[ 'parameter' ] ) ? strtolower( $link[ 'parameter' ] ) : $key;
				$navigation[ $key ] = new Link( $link );
			}

			parent::__construct( $navigation );
		}

		public function __call( $method, $args = [ ] )
		{
			static $nav = NULL;

			if ( empty( $nav ) )
			{
				$nav = new Nav();

				foreach ( $this->getArrayCopy() as $navigation )
				{
					$navigation->url = isset( $navigation->url ) ? $navigation->url : '#';

					if ( $navigation instanceof FactoryInterface )
					{
						$nav->addItem( $navigation );
					}
					elseif ( empty( $navigation->childs ) )
					{
						$attr            = $navigation->offsetGet( 'attr' )->__toArray();
						$attr[ 'title' ] = $navigation->offsetGet( 'title' )->page;

						$link = new \O2System\Bootstrap\Factory\Link( $navigation->label, $navigation->url, $attr );

						if ( ! empty( $navigation->icon ) )
						{
							$link->addIcon( $navigation->icon );
						}

						if ( $navigation->url === current_url() )
						{
							$nav->addItem( $link, Nav::ITEM_ACTIVE );
						}
						else
						{
							$nav->addItem( $link );
						}
					}
					else
					{
						$attr            = $navigation->offsetGet( 'attr' )->__toArray();
						$attr[ 'title' ] = $navigation->offsetGet( 'title' )->page;

						$links = new \O2System\Bootstrap\Factory\Links( $navigation->label, $attr );

						if ( ! empty( $navigation->icon ) )
						{
							$links->addIcon( $navigation->icon );
						}

						foreach ( $navigation->childs as $child )
						{
							$child->url = isset( $child->url ) ? $child->url : '#';

							$attr            = $child->offsetGet( 'attr' )->__toArray();
							$attr[ 'title' ] = $child->offsetGet( 'title' )->page;

							$links->addItem( new \O2System\Bootstrap\Factory\Link( $child->label, $child->url, $attr ) );
						}

						if ( $navigation->url === current_url() )
						{
							$nav->addItem( $links, Nav::ITEM_ACTIVE );
						}
						else
						{
							$nav->addItem( $links );
						}
					}
				}
			}

			if ( method_exists( $nav, $method ) )
			{
				return call_user_func_array( [ $nav, $method ], $args );
			}

			return $nav;
		}

		public function __toString()
		{
			return $this->__call( 'render' );
		}

		public function isEmpty()
		{
			return $this->__isEmpty();
		}
	}

	class Link extends ArrayObject
	{
		public function __construct( array $link )
		{
			foreach ( $link as $key => $value )
			{
				if ( is_array( $value ) )
				{
					if ( array_key_exists( \O2System::$active[ 'language' ]->parameter, $value ) )
					{
						$link[ $key ] = $value[ \O2System::$active[ 'language' ]->parameter ];
					}
					elseif ( $key === 'childs' )
					{
						$link[ $key ] = new Navigation( $value );
					}
					else
					{
						$link[ $key ] = new ArrayObject();

						foreach ( $value as $sub_key => $sub_value )
						{
							if ( is_array( $sub_value ) )
							{
								if ( array_key_exists( \O2System::$active[ 'language' ]->parameter, $sub_value ) )
								{
									$link[ $key ][ $sub_key ] = $sub_value[ \O2System::$active[ 'language' ]->parameter ];
								}
								else
								{
									$link[ $key ][ $sub_key ] = new ArrayObject( $sub_value );
								}
							}
							else
							{
								$link[ $key ][ $sub_key ] = $sub_value;
							}
						}
					}
				}
			}

			parent::__construct( $link );
		}

		public function __toString()
		{
			$attr            = $this->offsetGet( 'attr' )->__toArray();
			$attr[ 'title' ] = $this->offsetGet( 'title' )->page;

			return ( new Link( $this->offsetGet( 'label', $this->offsetGet( 'url' ), $attr ) ) )->render();
		}

		public function isEmpty()
		{
			return $this->__isEmpty();
		}
	}
}