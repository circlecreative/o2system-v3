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
use O2System\Bootstrap\Interfaces\ContextualInterface;
use O2System\Bootstrap\Interfaces\IconInterface;
use O2System\Bootstrap\Interfaces\PrintableInterface;
use O2System\Bootstrap\Interfaces\ResponsiveInterface;
use O2System\Bootstrap\Interfaces\SizeInterface;
use O2System\Bootstrap\Interfaces\TypographyInterface;

/**
 *
 * @package Label
 */
class Label extends FactoryInterface
{
	use AlignmentInterface;
	use TypographyInterface;
	use IconInterface;
	use SizeInterface;
	use ContextualInterface;
	use ResponsiveInterface;
	use PrintableInterface;

	const DEFAULT_LABEL = 'default';
	const PRIMARY_LABEL = 'primary';
	const SUCCESS_LABEL = 'success';
	const INFO_LABEL    = 'info';
	const WARNING_LABEL = 'warning';
	const DANGER_LABEL  = 'danger';

	protected $_tag        = 'span';
	protected $_label      = NULL;
	public $heading    = NULL;
	protected $_attributes = array(
		'class' => [ 'label' ],
	);

	// ------------------------------------------------------------------------

	/**
	 * build
	 *
	 * @param string | array $label
	 * @param string         $type
	 *
	 * @return object
	 */
	public function build()
	{
		$this->set_size_class_prefix( 'label' );
		$this->set_contextual_class_prefix( 'label' );

		@list( $label, $for, $attr, $type ) = func_get_args();

		if ( $label instanceof Factory )
		{
			$this->_label = $label;
		}
		elseif ( is_string( $label ) )
		{
			$this->_label = $label;
		}
		elseif ( is_array( $label ) )
		{
			$this->add_attributes( $label );
		}

		if ( isset( $for ) )
		{
			if ( is_array( $for ) )
			{
				$this->add_attributes( $for );
			}
			elseif ( is_string( $for ) )
			{
				if ( in_array( $for, $this->_contextual_classes ) )
				{
					$this->{'is_' . $for}();
				}
				elseif ( in_array( $for, $this->_sizes ) )
				{
					$this->{'is_' . $for}();
				}
				else
				{
					$this->_tag = 'label';
					$this->add_attribute( 'for', $for );
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
				elseif ( in_array( $attr, $this->_sizes ) )
				{
					$this->{'is_' . $attr}();
				}
			}
		}

		if ( isset( $type ) )
		{
			if ( is_string( $type ) AND in_array( $type, $this->_contextual_classes ) )
			{
				$this->{'is_' . $type}();
			}
		}

		return $this;

	}

	// ------------------------------------------------------------------------

	public function set_heading( $heading, $attr = array() )
	{
		$this->heading = new Tag( 'h3', $heading, $attr );
		$this->heading->add_class( 'label-heading' );

		return $this;
	}

	/**
	 * Render
	 *
	 * @return null|string
	 */
	public function render()
	{
		if ( isset( $this->_label ) )
		{
			if ( isset( $this->heading ) )
			{
				$label = new Label( $this->_label, $this->_attributes );
				$label->set_tag( $this->_tag );

				$this->heading->append_content( $label );

				return $this->heading->render();
			}
			else
			{
				return ( new Tag( $this->_tag, $this->_label, $this->_attributes ) )->render();
			}
		}

		return '';
	}
}