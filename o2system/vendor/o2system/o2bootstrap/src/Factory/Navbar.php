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
use O2System\Bootstrap\Interfaces\AlignmentInterface;

/**
 *
 * @package Navbar
 */
class Navbar extends FactoryInterface
{
	use AlignmentInterface;

	const NAVBAR_DEFAULT = 'NAVBAR_DEFAULT';
	const NAVBAR_INVERSE = 'NAVBAR_INVERSE';
	const NAV_LEFT       = 'NAV_LEFT';
	const NAV_RIGHT      = 'NAV_RIGHT';

	protected $_tag    = 'nav';
	protected $_target = NULL;

	protected $_attributes = array(
		'class' => [ 'navbar' ],
		'role'  => 'navigation',
	);

	public $brand      = NULL;
	public $brand_logo = NULL;
	public $left       = NULL;
	public $right      = NULL;
	public $form       = NULL;

	protected $_is_fluid      = FALSE;
	protected $_is_full_width = FALSE;

	/**
	 * build
	 *
	 * @return type
	 */
	public function build()
	{
		$this->setPullClassPrefix( 'navbar' );

		@list( $target, $type, $attr ) = func_get_args();

		if ( isset( $target ) )
		{
			if ( is_string( $target ) )
			{
				if ( in_array( $target, [ self::NAVBAR_DEFAULT, self::NAVBAR_INVERSE ] ) )
				{
					$this->setType( $target );
				}
				else
				{
					$this->setTarget( $target );
				}
			}
			elseif ( is_array( $target ) )
			{
				$attr = $target;
			}
		}

		if ( isset( $type ) )
		{
			if ( is_string( $type ) )
			{
				if ( in_array( $type, [ self::NAVBAR_DEFAULT, self::NAVBAR_INVERSE ] ) )
				{
					$this->setType( $type );
				}
			}
			elseif ( is_array( $type ) )
			{
				$attr = $type;
			}
		}

		if ( isset( $attr ) )
		{
			$this->addAttributes( $attr );
		}

		// Initialize Left & Right Menu
		$this->left = new Nav();
		$this->left->addClass( 'navbar-nav' );

		$this->right = new Nav();
		$this->right->addClasses( [ 'navbar-nav', 'navbar-right' ] );

		//$this->form = new Form();

		return $this;
	}

	public function __clone()
	{
		foreach ( [ 'brand', 'brand_logo', 'left', 'right', 'form' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		return $this;
	}

	public function setTarget($target )
	{
		$this->_target = str_replace( [ '_', '.', '#' ], [ '-', '', '' ], $target );

		return $this;
	}

	public function setType($type )
	{
		$types = array(
			self::NAVBAR_DEFAULT => 'navbar-default',
			self::NAVBAR_INVERSE => 'navbar-inverse',
		);

		if ( array_key_exists( $type, $types ) )
		{
			$this->addClass( $types[ $type ] );
		}

		return $this;
	}

	public function setBrand($brand, $href = '#' )
	{
		// Brand Logo
		if ( function_exists( 'base_url' ) AND $href === '#' )
		{
			$href = base_url();
		}

		if ( $brand instanceof Image )
		{
			$this->brand_logo = $brand;
		}
		elseif ( is_string( $brand ) )
		{
			if ( strpos( $brand, 'http' ) !== FALSE )
			{
				$this->brand_logo = new Image( $brand );
			}
			elseif ( is_file( $brand ) )
			{
				$this->brand_logo = new Image( $brand );
			}
			else
			{
				$this->brand_logo = new Tag( 'span', $brand );
			}
		}

		$this->brand = new Link( $this->brand_logo, $href, [ 'class' => 'navbar-brand' ] );

		return $this;
	}

	public function setFinder(array $param)
	{
		$this->form = '
				            <form action="'.$param['action'].'" method="'.$param['method'].'" class="navbar-form '.$param['position'].'" role="form">
				                <div class="input-group">
				                    <input type="text" class="form-control" name="'.$param['name'].'" placeholder="'.$param['placeholder'].'">
				                    <span class="input-group-btn">
				                        <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
				                    </span>
				                </div>
				            </form>
				        ';
		return $this;
	}

	public function addItems(array $items, $position = self::NAV_LEFT )
	{
		foreach ( $items as $item )
		{
			$this->addItem( $item, $position );
		}
	}

	public function addItem($item, $position = self::NAV_LEFT, $describe = NULL )
	{
		if ( $item instanceof Nav )
		{
			$item->addClass( 'navbar-nav' );
		}
		elseif ( $item instanceof Form )
		{
			$item->addClass( 'navbar-form' );
		}
		elseif ( $item instanceof Button )
		{
			$item->addClass( 'navbar-button' );
		}
		elseif ( $item instanceof Link )
		{
			$item->addClass( 'navbar-link' );
		}
		elseif ( is_string( $item ) )
		{
			$item = new Tag( 'p', $item, [ 'class' => 'navbar-text' ] );
		}

		switch ( $position )
		{
			default:
			case self::NAV_LEFT:

				$this->left->addItem( $item, $describe );

				break;

			case self::NAV_RIGHT:

				$this->right->addItem( $item, $describe );

				break;
		}

		return $this;
	}

	/**
	 * top
	 *
	 * @return object
	 */
	public function isFixedTop()
	{
		$this->addClass( 'navbar-fixed-top' );

		return $this;
	}

	/**
	 * bottom
	 *
	 * @return type
	 */
	public function isFixedBottom()
	{
		$this->addClass( 'navbar-fixed-bottom' );

		return $this;
	}

	/**
	 * top
	 *
	 * @return object
	 */
	public function isStaticTop()
	{
		$this->addClass( 'navbar-static-top' );

		return $this;
	}

	public function isFluid()
	{
		$this->_is_fluid = TRUE;

		return $this;
	}

	public function isFullWidth()
	{
		$this->_is_full_width = TRUE;
	}

	/**
	 * render
	 *
	 * @return object
	 */
	public function render()
	{
		if ( isset( $this->brand ) )
		{
			if ( empty( $this->_target ) )
			{
				$this->_target = uniqid( 'navbar-' );
			}

			$toggle = new Button();
			$toggle->appendLabel( new Tag( 'span', 'Toggle Navigation', [ 'class' => 'sr-only' ] ) );
			$icons[] = new Tag( 'span', [ 'class' => 'icon-bar' ] );
			$icons[] = new Tag( 'span', [ 'class' => 'icon-bar' ] );
			$icons[] = new Tag( 'span', [ 'class' => 'icon-bar' ] );
			$toggle->appendLabel( implode( PHP_EOL, $icons ) );

			$toggle->addAttributes( array(
				                         'class'       => 'navbar-toggle',
				                         'data-toggle' => 'collapse',
				                         'data-target' => '#' . $this->_target,
			                         ) );
			$output[ 'header' ] = new Tag( 'div', implode( PHP_EOL, [ $toggle, $this->brand ] ), [ 'class' => 'navbar-header' ] );
		}


		$output[ 'collapse' ] = new Tag( 'div', [ 'id' => $this->_target, 'class' => 'collapse navbar-collapse' ] );

		$output[ 'collapse' ]->appendContent( $this->left );
		$output[ 'collapse' ]->appendContent( $this->form );
		$output[ 'collapse' ]->appendContent( $this->right );

		$container = new Tag( 'div', implode( PHP_EOL, $output ) );

		if ( $this->_is_full_width === FALSE )
		{
			if ( $this->_is_fluid === TRUE )
			{
				$container->addClass( 'container-fluid' );
			}
			else
			{
				$container->addClass( 'container' );
			}
		}
		else
		{
			$container->addClass( 'container-full-width' );
		}

		return ( new Tag( $this->_tag, $container, $this->_attributes ) )->render();
	}
}
