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

/**
 *
 * @package progress
 */
class Progress extends FactoryInterface
{
	use ContextualInterface;
	
	const SUCCESS_PROGRESS_BAR = 'success';
	const INFO_PROGRESS_BAR    = 'info';
	const WARNING_PROGRESS_BAR = 'warning';
	const DANGER_PROGRESS_BAR  = 'danger';

	protected $_tag        = 'div';
	protected $_attributes = array(
		'class' => [ 'progress-bar' ],
		'role'  => 'progressbar',
	);

	public $progress = NULL;

	// ------------------------------------------------------------------------

	/**
	 * build
	 *
	 * @return object
	 */
	public function build()
	{
		@list( $progress, $type, $attr ) = func_get_args();

		$this->set_contextual_class_prefix( 'progress-bar' );

		if ( isset( $progress ) )
		{
			$this->set_progress( $progress );
		}

		if ( isset( $type ) )
		{
			if ( is_array( $type ) )
			{
				$this->add_attributes( $type );
			}
			elseif ( is_string( $type ) )
			{
				if ( in_array( $type, $this->_contextual_classes ) AND !
					in_array( $type, [ 'default', 'primary' ] )
				)
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
				if ( in_array( $attr, $this->_contextual_classes ) AND !
					in_array( $attr, [ 'default', 'primary' ] )
				)
				{
					$this->{'is_' . $attr}();
				}
			}
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function __clone()
	{
		if ( is_object( $this->progress ) )
		{
			$this->progress = clone $this->progress;
		}

		return $this;
	}

	public function set_progress( $value )
	{
		$this->progress = new Tag( 'span', (int) $value . '%', [ 'class' => 'sr-only' ] );
		$this->add_attribute( 'aria-valuenow', (int) $value );
		$this->add_attribute( 'style', 'width:' . (int) $value . '%' );

		return $this;
	}

	public function set_min_value( $value )
	{
		$this->add_attribute( 'aria-valuemin', (int) $value );

		return $this;
	}

	public function set_max_value( $value )
	{
		$this->add_attribute( 'aria-valuemax', (int) $value );

		return $this;
	}

	/**
	 * stripped
	 *
	 * @param string $active
	 *
	 * @return object
	 */
	public function is_striped()
	{
		$this->add_class( 'progress-bar-striped' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * stripped
	 *
	 * @param string $active
	 *
	 * @return object
	 */
	public function is_animated()
	{
		$this->is_active();

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * stripped
	 *
	 * @param string $active
	 *
	 * @return object
	 */
	public function is_active()
	{
		$this->add_class( 'active' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Render
	 *
	 * @return null|string
	 */
	public function render()
	{
		if ( isset( $this->progress ) )
		{
			if ( ! isset( $this->_attributes[ 'aria-valuemin' ] ) )
			{
				$this->add_attribute( 'aria-valuemin', 0 );
			}

			if ( ! isset( $this->_attributes[ 'aria-valuemax' ] ) )
			{
				$this->add_attribute( 'aria-valuemax', 100 );
			}

			return ( new Tag( $this->_tag, new Tag( $this->_tag, $this->progress, $this->_attributes ), [ 'class' => 'progress' ] ) )->render();
		}

		return '';
	}
}