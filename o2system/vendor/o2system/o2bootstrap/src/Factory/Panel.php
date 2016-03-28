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
use O2System\Bootstrap\Interfaces\ContextualInterface;
use O2System\Bootstrap\Interfaces\ContentInterface;

class Panel extends FactoryInterface
{
	use ContextualInterface;
	use ContentInterface;

	const DEFAULT_PANEL = 'default';
	const PRIMARY_PANEL = 'primary';
	const SUCCESS_PANEL = 'success';
	const INFO_PANEL    = 'info';
	const WARNING_PANEL = 'warning';
	const DANGER_PANEL  = 'danger';

	protected $_tag        = 'div';
	protected $_attributes = array(
		'class' => array( 'panel' ),
	);

	public $title   = NULL;
	public $options = NULL;
	public $table   = NULL;
	public $lists   = NULL;
	public $footer  = NULL;

	public function build()
	{
		$this->set_contextual_class_prefix( 'panel' );

		@list( $title, $type, $attr ) = func_get_args();

		if ( isset( $title ) )
		{
			if ( is_array( $title ) )
			{
				$this->add_attributes( $title );
			}
			elseif ( is_string( $title ) )
			{
				if ( in_array( $title, $this->_contextual_classes ) )
				{
					$this->{'is_' . $title}();
				}
				else
				{
					$this->set_title( $title );
				}
			}
		}

		if ( isset( $type ) )
		{
			if ( is_array( $type ) )
			{
				$this->add_attributes( $type );
			}
			elseif ( is_string( $type ) )
			{
				if ( in_array( $type, $this->_contextual_classes ) )
				{
					$this->{'is_' . $type}();
				}
			}
		}

		if ( isset( $attr ) )
		{
			if ( is_array( $attr ) )
			{
				$this->add_attributes( $attr );
			}
			elseif ( is_string( $attr ) )
			{
				if ( in_array( $attr, $this->_contextual_classes ) )
				{
					$this->{'is_' . $attr}();
				}
			}
		}

		return $this;
	}

	public function __clone()
	{
		foreach ( [ 'title', 'options', 'table', 'lists', 'footer' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		return $this;
	}

	/**
	 * Set Panel Header
	 *
	 * @param string $title
	 * @param string $tag
	 *
	 * @return object
	 */
	public function set_title( $title, $tag = 'h3', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
		}

		if ( $title instanceof Link )
		{
			$this->title = clone $title;
			$this->title->add_attributes( $attr );
			$this->title->add_class( 'panel-title' );
		}
		elseif ( $title instanceof Tag )
		{
			$this->title = clone $title;
			$this->title->set_tag( $tag );
			$this->title->add_attributes( $attr );
			$this->title->add_class( 'panel-title' );
		}
		else
		{
			$this->title = new Tag( $tag, ucwords( $title ), $attr );
		}

		return $this;
	}

	public function set_options( $options )
	{
		if ( $options instanceof FactoryInterface )
		{
			$this->options = clone $options;
			$this->options->add_class( 'panel-options pull-right' );
		}

		return $this;
	}

	public function set_body( $body )
	{
		return $this->set_content( $body );
	}

	public function reset_body()
	{
		return $this->reset_content();
	}

	public function prepend_body( $body )
	{
		return $this->prepend_content( $body );
	}

	public function append_body( $body )
	{
		return $this->append_content( $body );
	}

	/**
	 * Set Panel Footer
	 *
	 * @param string $title
	 * @param string $tag
	 *
	 * @return object
	 */
	public function set_footer( $title, $tag = 'div', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
		}

		if ( $title instanceof Tag )
		{
			$this->footer = clone $title;
			$this->footer->set_tag( $tag );
			$this->footer->add_class( 'panel-footer' );
		}
		else
		{
			if ( isset( $attr[ 'class' ] ) )
			{
				if ( is_array( $attr[ 'class' ] ) )
				{
					array_push( $attr[ 'class' ], 'panel-footer' );
				}
				else
				{
					$attr[ 'class' ] = $attr[ 'class' ] . ' panel-footer';
				}
			}
			else
			{
				$attr[ 'class' ] = 'panel-footer';
			}

			$this->footer = new Tag( $tag, ucwords( $title ), $attr );
		}

		return $this;
	}

	public function set_table( Tag $table )
	{
		$this->table = $table;

		return $this;
	}

	public function set_lists( Lists $lists )
	{
		$this->lists = $lists;

		return $this;
	}

	/**
	 * Render
	 *
	 * @return null|string
	 */
	public function render()
	{
		if ( ! empty( $this->_content ) )
		{
			if ( isset( $this->title ) AND isset( $this->options ) )
			{
				$grid = new Grid();
				$grid->set_class( 'panel-heading' );
				$grid->set_num_per_rows( 2 );
				$grid->add_item( $this->title );
				$grid->add_item( $this->options );

				$output[] = $grid;
			}
			elseif ( isset( $this->title ) )
			{
				$output[] = new Tag( 'div', $this->title, [ 'class' => 'panel-heading' ] );
			}

			$output[] = new Tag( $this->_tag, implode( PHP_EOL, $this->_content ), [ 'class' => 'panel-body' ] );

			if ( isset( $this->table ) )
			{
				$output[] = $this->table;
			}

			if ( isset( $this->lists ) )
			{
				$output[] = $this->lists;
			}

			if ( isset( $this->footer ) )
			{
				$output[] = $this->footer;
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $output ), $this->_attributes ) )->render();
		}
		elseif ( isset( $this->table ) )
		{
			if ( isset( $this->title ) AND isset( $this->options ) )
			{
				$grid = new Grid();
				$grid->set_class( 'panel-heading' );
				$grid->set_num_per_rows( 2 );
				$grid->add_item( $this->title );
				$grid->add_item( $this->options );

				$output[] = $grid;
			}
			elseif ( isset( $this->title ) )
			{
				$output[] = new Tag( 'div', $this->title, [ 'class' => 'panel-heading' ] );
			}

			$output[] = $this->table;

			if ( isset( $this->lists ) )
			{
				$output[] = $this->lists;
			}

			if ( isset( $this->footer ) )
			{
				$output[] = $this->footer;
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $output ), $this->_attributes ) )->render();
		}
		elseif ( isset( $this->lists ) )
		{
			if ( isset( $this->title ) AND isset( $this->options ) )
			{
				$grid = new Grid();
				$grid->set_class( 'panel-heading' );
				$grid->set_num_per_rows( 2 );
				$grid->add_item( $this->title );
				$grid->add_item( $this->options );

				$output[] = $grid;
			}
			elseif ( isset( $this->title ) )
			{
				$output[] = new Tag( 'div', $this->title, [ 'class' => 'panel-heading' ] );
			}

			$output[] = $this->lists;

			if ( isset( $this->table ) )
			{
				$output[] = $this->table;
			}

			if ( isset( $this->footer ) )
			{
				$output[] = $this->footer;
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $output ), $this->_attributes ) )->render();
		}

		return '';
	}
}
