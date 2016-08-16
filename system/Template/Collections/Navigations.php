<?php

namespace O2System\Template\Collections
{

	use O2System\Core\Library\Collections;

	class Navigations extends Collections
	{
		/**
		 * Library Instance
		 *
		 * @access  protected
		 * @type    Template
		 */
		protected $library;
		
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
	}
}

namespace O2System\Template\Collections\Navigations
{

	use O2System\Bootstrap\Components\Nav;
	use O2System\Bootstrap\Interfaces\FactoryInterface;
	use O2System\Core\SPL\ArrayObject;

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

						$link = new \O2System\Bootstrap\Components\Link( $navigation->label, $navigation->url, $attr );

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

						$links = new \O2System\Bootstrap\Components\Links( $navigation->label, $attr );

						if ( ! empty( $navigation->icon ) )
						{
							$links->addIcon( $navigation->icon );
						}

						foreach ( $navigation->childs as $child )
						{
							$child->url = isset( $child->url ) ? $child->url : '#';

							$attr            = $child->offsetGet( 'attr' )->__toArray();
							$attr[ 'title' ] = $child->offsetGet( 'title' )->page;

							$links->addItem( new \O2System\Bootstrap\Components\Link( $child->label, $child->url, $attr ) );
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
					if ( array_key_exists( \O2System::$request->language->parameter, $value ) )
					{
						$link[ $key ] = $value[ \O2System::$request->language->parameter ];
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
								if ( array_key_exists( \O2System::$request->language->parameter, $sub_value ) )
								{
									$link[ $key ][ $sub_key ] = $sub_value[ \O2System::$request->language->parameter ];
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