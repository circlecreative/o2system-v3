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
		$this->set_pull_class_prefix( 'navbar' );

		@list( $target, $type, $attr ) = func_get_args();

		if ( isset( $target ) )
		{
			if ( is_string( $target ) )
			{
				if ( in_array( $target, [ self::NAVBAR_DEFAULT, self::NAVBAR_INVERSE ] ) )
				{
					$this->set_type( $target );
				}
				else
				{
					$this->set_target( $target );
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
					$this->set_type( $type );
				}
			}
			elseif ( is_array( $type ) )
			{
				$attr = $type;
			}
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		// Initialize Left & Right Menu
		$this->left = new Nav();
		$this->left->add_class( 'navbar-nav' );

		$this->right = new Nav();
		$this->right->add_classes( [ 'navbar-nav', 'navbar-right' ] );

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

	public function set_target( $target )
	{
		$this->_target = str_replace( [ '_', '.', '#' ], [ '-', '', '' ], $target );

		return $this;
	}

	public function set_type( $type )
	{
		$types = array(
			self::NAVBAR_DEFAULT => 'navbar-default',
			self::NAVBAR_INVERSE => 'navbar-inverse',
		);

		if ( array_key_exists( $type, $types ) )
		{
			$this->add_class( $types[ $type ] );
		}

		return $this;
	}

	public function set_brand( $brand, $href = '#' )
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

	public function set_finder(array $param)
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

	public function add_items( array $items, $position = self::NAV_LEFT )
	{
		foreach ( $items as $item )
		{
			$this->add_item( $item, $position );
		}
	}

	public function add_item( $item, $position = self::NAV_LEFT, $describe = NULL )
	{
		if ( $item instanceof Nav )
		{
			$item->add_class( 'navbar-nav' );
		}
		elseif ( $item instanceof Form )
		{
			$item->add_class( 'navbar-form' );
		}
		elseif ( $item instanceof Button )
		{
			$item->add_class( 'navbar-button' );
		}
		elseif ( $item instanceof Link )
		{
			$item->add_class( 'navbar-link' );
		}
		elseif ( is_string( $item ) )
		{
			$item = new Tag( 'p', $item, [ 'class' => 'navbar-text' ] );
		}

		switch ( $position )
		{
			default:
			case self::NAV_LEFT:

				$this->left->add_item( $item, $describe );

				break;

			case self::NAV_RIGHT:

				$this->right->add_item( $item, $describe );

				break;
		}

		return $this;
	}

	/**
	 * top
	 *
	 * @return object
	 */
	public function is_fixed_top()
	{
		$this->add_class( 'navbar-fixed-top' );

		return $this;
	}

	/**
	 * bottom
	 *
	 * @return type
	 */
	public function is_fixed_bottom()
	{
		$this->add_class( 'navbar-fixed-bottom' );

		return $this;
	}

	/**
	 * top
	 *
	 * @return object
	 */
	public function is_static_top()
	{
		$this->add_class( 'navbar-static-top' );

		return $this;
	}

	public function is_fluid()
	{
		$this->_is_fluid = TRUE;

		return $this;
	}

	public function is_full_width()
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
			$toggle->append_label( new Tag( 'span', 'Toggle Navigation', [ 'class' => 'sr-only' ] ) );
			$icons[] = new Tag( 'span', [ 'class' => 'icon-bar' ] );
			$icons[] = new Tag( 'span', [ 'class' => 'icon-bar' ] );
			$icons[] = new Tag( 'span', [ 'class' => 'icon-bar' ] );
			$toggle->append_label( implode( PHP_EOL, $icons ) );

			$toggle->add_attributes( array(
				                         'class'       => 'navbar-toggle',
				                         'data-toggle' => 'collapse',
				                         'data-target' => '#' . $this->_target,
			                         ) );
			$output[ 'header' ] = new Tag( 'div', implode( PHP_EOL, [ $toggle, $this->brand ] ), [ 'class' => 'navbar-header' ] );
		}


		$output[ 'collapse' ] = new Tag( 'div', [ 'id' => $this->_target, 'class' => 'collapse navbar-collapse' ] );

		$output[ 'collapse' ]->append_content( $this->left );
		$output[ 'collapse' ]->append_content( $this->form );
		$output[ 'collapse' ]->append_content( $this->right );

		$container = new Tag( 'div', implode( PHP_EOL, $output ) );

		if ( $this->_is_full_width === FALSE )
		{
			if ( $this->_is_fluid === TRUE )
			{
				$container->add_class( 'container-fluid' );
			}
			else
			{
				$container->add_class( 'container' );
			}
		}
		else
		{
			$container->add_class( 'container-full-width' );
		}

		return ( new Tag( $this->_tag, $container, $this->_attributes ) )->render();
	}
}
