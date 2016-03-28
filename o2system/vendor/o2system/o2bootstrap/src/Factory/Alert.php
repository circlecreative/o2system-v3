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
use O2System\Bootstrap\Interfaces\IconInterface;
use O2System\Bootstrap\Interfaces\PrintableInterface;
use O2System\Bootstrap\Interfaces\ContentInterface;

/**
 * Class Bootstrap Alert Builder
 *
 * @package O2Boostrap\Factory
 */
class Alert extends FactoryInterface
{
	use PrintableInterface;
	use IconInterface;
	use ContextualInterface;
	use ContentInterface;

	const SUCCESS_ALERT = 'success';
	const INFO_ALERT    = 'info';
	const WARNING_ALERT = 'warning';
	const DANGER_ALERT  = 'danger';

	protected $_tag = 'div';

	protected $_attributes = array(
		'class' => [ 'alert' ],
		'role'  => 'alert',
	);

	protected $_is_dismissible = FALSE;

	public $title = NULL;

	// ------------------------------------------------------------------------

	/**
	 * Builder
	 *
	 * @return object
	 */
	public function build()
	{
		@list( $content, $type, $attr ) = func_get_args();

		$this->set_contextual_class_prefix( 'alert' );

		if ( is_array( $content ) )
		{
			$lists = new Lists( Lists::LIST_UNSTYLED );
			$lists->add_class( 'alert-list' );

			foreach ( $content as $list )
			{
				$lists->add_item( $list );
			}

			$this->_content[] = $lists;
		}
		elseif ( is_string( $content ) )
		{
			if ( in_array( $content, $this->_contextual_classes ) AND !
				in_array( $content, [ 'default', 'primary' ] )
			)
			{
				$this->{'is_' . $content}();
			}
			else
			{
				$this->_content[] = $content;
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
		if ( is_object( $this->title ) )
		{
			$this->title = clone $this->title;
		}

		return $this;
	}

	/**
	 * Dismissible Alert
	 *
	 * @return object
	 */
	public function is_dismissible()
	{
		$this->_is_dismissible = TRUE;
		$this->add_class( 'alert-dismissible' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Alert Title
	 *
	 * @param string $title
	 * @param string $tag
	 *
	 * @return object
	 */
	public function set_title( $title, $tag = 'strong', $attr = array() )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
		}

		if ( $title instanceof Factory )
		{
			$this->title = clone $title;
			$this->title->set_tag( $tag );
			$this->title->add_class( 'alert-title' );
		}
		else
		{
			if ( isset( $attr[ 'class' ] ) )
			{
				if ( is_array( $attr[ 'class' ] ) )
				{
					array_push( $attr[ 'class' ], 'alert-title' );
				}
				else
				{
					$attr[ 'class' ] = $attr[ 'class' ] . ' alert-title';
				}
			}
			else
			{
				$attr[ 'class' ] = 'alert-title';
			}

			if ( isset( $attr[ 'style' ] ) )
			{
				$attr[ 'style' ] = $attr[ 'style' ] . ' margin-top: 0px;';
			}
			else
			{
				$attr[ 'style' ] = 'margin-top: 0px;';
			}

			$this->title = new Tag( $tag, ucwords( $title ), $attr );
		}

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
		if ( ! empty( $this->_content ) )
		{
			if ( $this->_is_dismissible === TRUE )
			{
				$output[] = ( new Tag( 'button',
				                       new Tag( 'span', '&times;', [ 'aria-hidden' => 'true' ] ),
				                       array(
					                       'type'         => 'button',
					                       'class'        => 'close',
					                       'data-dismiss' => 'alert',
					                       'aria-label'   => 'Close',
				                       ) ) );
			}

			if ( isset( $this->title ) )
			{
				if ( isset( $this->icon ) )
				{
					$this->icon->add_class( 'alert-icon' );

					$this->title->prepend_content( $this->icon );
				}

				$output[] = $this->title;
			}
			elseif ( isset( $this->icon ) )
			{
				$output[] = $this->icon;
			}

			$content = implode( '<br />', $this->_content );

			$DOMDocument = new \DOMDocument();
			libxml_use_internal_errors( TRUE );
			$DOMDocument->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			libxml_clear_errors();
			$links = $DOMDocument->getElementsByTagName( 'a' );

			if ( $links->length > 0 )
			{
				foreach ( $links as $link )
				{
					$class = $link->getAttribute( 'class' );
					$class = $class . ' alert-link';

					$link->setAttribute( 'class', trim( $class ) );
				}

				$content = $DOMDocument->saveHTML();
			}

			$output[] = $content;

			return ( new Tag( $this->_tag, implode( PHP_EOL, $output ), $this->_attributes ) )->render();
		}

		return '';
	}
}