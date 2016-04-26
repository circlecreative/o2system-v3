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

class Pagination extends Lists
{
	protected $_num_pages   = 0;
	protected $_num_active  = 1;
	protected $_num_display = 5;
	protected $_attributes  = array(
		'class' => [ 'pagination' ],
	);

	protected $_link = NULL;

	public function build()
	{
		$this->set_size_class_prefix( 'pagination' );

		@list( $pages, $display, $attr ) = func_get_args();

		if ( isset( $pages ) )
		{
			if ( is_numeric( $pages ) )
			{
				$this->set_num_pages( $pages );
			}
			elseif ( is_array( $pages ) )
			{
				$attr = $pages;
			}
		}

		if ( isset( $display ) )
		{
			if ( is_numeric( $display ) )
			{
				$this->set_num_display( $display );
			}
			elseif ( is_array( $display ) )
			{
				$attr = $display;
			}
		}

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	public function set_num_pages( $pages )
	{
		if ( is_numeric( $pages ) )
		{
			$this->_num_pages = (int) $pages;
		}

		return $this;
	}

	public function set_num_active( $active )
	{
		if ( is_numeric( $active ) )
		{
			$this->_num_active = (int) $active;
		}

		return $this;
	}

	public function set_num_display( $display )
	{
		if ( is_numeric( $display ) )
		{
			$this->_num_display = (int) $display;
		}

		return $this;
	}

	public function set_link( $link )
	{
		$this->_link = $link;

		return $this;
	}

	public function render()
	{
		// Try get active page
		$http_query = $_GET;

		if ( isset( $_GET[ 'page' ] ) )
		{
			$this->_num_active = (int) $_GET[ 'page' ];
			unset( $http_query[ 'page' ] );

			if ( empty( $http_query ) )
			{
				$link = $this->_link . '/?page=';
			}
			else
			{
				$link = $this->_link . '/?' . http_build_query( $http_query ) . '&page=';
			}
		}
		else
		{
			if ( class_exists( 'O2System', FALSE ) )
			{
				$segments = \O2System::URI()->segments;
				if ( $key = array_search( 'page', $segments ) )
				{
					if ( isset( $segments[ $key + 1 ] ) )
					{
						$this->_num_active = $segments[ $key + 1 ];
					}

					$link = str_replace( \O2System::$config[ 'URI' ][ 'suffix' ], '/page/', $this->_link );
				}
				else
				{
					$link = $this->_link . '/?' . http_build_query( $http_query ) . '&page=';
				}
			}
			else
			{
				$link = $this->_link . '/?' . http_build_query( $http_query ) . '&page=';
			}
		}

		$link = str_replace('?&', '?', $link);

		$this->_num_active = $this->_num_active == 0 ? 1 : $this->_num_active;

		$num_previous = $this->_num_active - 1;
		$num_previous = $num_previous == 0 ? 1 : $num_previous;

		if ( $num_previous > 1 )
		{
			$this->add_item( new Link( new Tag( 'span', '&laquo;', [ 'aria-hidden' => TRUE ] ), $link . $num_previous ) );
		}
		else
		{
			$this->add_item( new Link( new Tag( 'span', '&laquo;', [ 'aria-hidden' => TRUE ] ), '#' ), Lists::ITEM_DISABLED );
		}

		$total_pages = ( $this->_num_active + $this->_num_display );
		$total_pages = $total_pages > $this->_num_pages ? $this->_num_pages : $total_pages;

		$pages = range( $this->_num_active, $total_pages );

		if ( count( $pages ) > $this->_num_display )
		{
			if ( end( $pages ) < $this->_num_pages OR end( $pages ) == $this->_num_pages )
			{
				array_pop( $pages );
			}
		}
		elseif ( count( $pages ) < $this->_num_display )
		{
			$start_page = $this->_num_active - ( $this->_num_display - count( $pages ) );
			$pages = range( $start_page, $total_pages );
		}

		foreach ( $pages as $page )
		{
			if ( $page <= $this->_num_pages AND $page > 0 )
			{
				if ( $page == $this->_num_active )
				{
					$this->add_item( new Link( $page, $link . $page ), Lists::ITEM_ACTIVE );
				}
				else
				{
					$this->add_item( new Link( $page, $link . $page ) );
				}
			}
		}

		$num_next = $this->_num_active + 1;

		if ( $num_next < $this->_num_pages )
		{
			$this->add_item( new Link( new Tag( 'span', '&raquo;', [ 'aria-hidden' => TRUE ] ), $link . $num_next ) );
		}
		else
		{
			$this->add_item( new Link( new Tag( 'span', '&raquo;', [ 'aria-hidden' => TRUE ] ), '#' ), Lists::ITEM_DISABLED );
		}

		if ( ! empty( $this->_items ) )
		{
			return ( new Tag( 'nav', parent::render() ) )->render();
		}

		return '';
	}
}