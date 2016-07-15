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

use O2System\Bootstrap\Interfaces\ContextualInterface;
use O2System\Bootstrap\Interfaces\FactoryInterface;
use O2System\Bootstrap\Interfaces\IconInterface;
use O2System\Bootstrap\Interfaces\SizeInterface;
use O2System\Bootstrap\Interfaces\LabelInterface;

/**
 * Class Bootstrap Alert Builder
 *
 * @package O2Boostrap\Factory
 */
class Button extends FactoryInterface
{
	use ContextualInterface;
	use IconInterface;
	use LabelInterface;
	use SizeInterface;

	const DEFAULT_BUTTON = 'default';
	const PRIMARY_BUTTON = 'primary';
	const SUCCESS_BUTTON = 'success';
	const INFO_BUTTON    = 'info';
	const WARNING_BUTTON = 'warning';
	const DANGER_BUTTON  = 'danger';
	const LINK_BUTTON    = 'link';

	const SUBMIT_BUTTON = 'SUBMIT';
	const RESET_BUTTON  = 'RESET';
	const CANCEL_BUTTON = 'CANCEL';
	const DELETE_BUTTON = 'DELETE';
	const COMMON_BUTTON = 'BUTTON';

	protected $_tag        = 'button';
	protected $_attributes = [
		'class' => [ 'btn' ],
		'type'  => 'button',
	];

	protected $_types = [
		'default',
		'primary',
		'success',
		'info',
		'warning',
		'danger',
		'link',
	];

	public function build()
	{
		$this->setContextualClassPrefix( 'btn' );
		$this->setSizeClassPrefix( 'btn' );

		@list( $label, $type, $attr ) = func_get_args();

		if ( is_array( $label ) )
		{
			$attr = $label;
		}
		elseif ( is_string( $label ) OR is_numeric( $label ) )
		{
			$this->_label[] = $label;
		}

		if ( isset( $type ) )
		{
			if ( is_string( $type ) )
			{
				if ( in_array( $type, $this->_types ) )
				{
					$this->addClass( 'btn-' . $type );
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

		return $this;
	}

	public function setTag( $tag )
	{
		if ( $tag !== 'button' )
		{
			$this->removeAttribute( 'type' );
		}

		$this->_tag = $tag;

		return $this;
	}

	/**
	 * block
	 *
	 * @return object
	 */
	public function isBlock()
	{
		$this->addClass( 'btn-block' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * active
	 *
	 * @return object
	 */
	public function isActive()
	{
		$this->addClass( 'active' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * disable
	 *
	 * @return object
	 */
	public function isDisabled()
	{
		$this->addClass( 'disabled' );

		return $this;
	}

	// ------------------------------------------------------------------------

	public function isSubmit()
	{
		$this->_attributes[ 'type' ] = 'submit';

		return $this;
	}

	public function isReset()
	{
		$this->_attributes[ 'type' ] = 'reset';

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
		if ( isset( $this->_label ) )
		{
			if ( isset( $this->icon ) )
			{
				$this->prependLabel( $this->icon->render() );
			}

			if ( empty( $this->_attributes[ 'class' ] ) )
			{
				$this->addClass( 'btn-default' );
			}

			return ( new Tag( $this->_tag, implode( PHP_EOL, $this->_label ), $this->_attributes ) )->render();
		}

		return '';
	}
}
