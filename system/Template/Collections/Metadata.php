<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Template\Collections;

// ------------------------------------------------------------------------

use O2System\Bootstrap\Components\Tag;
use O2System\Core\Library\Collections;
use O2System\Template;

/**
 * Template Themes Driver
 *
 * @package          Template
 * @subpackage       Library
 * @category         Driver
 * @version          1.0 Build 11.09.2012
 * @author           Circle Creative Developer Team
 * @copyright        Copyright (c) 2005 - 2014
 * @license          http://www.circle-creative.com/products/o2system/license.html
 * @link             http://www.circle-creative.com
 */
// ------------------------------------------------------------------------

class Metadata extends Collections
{
	/**
	 * Library Instance
	 *
	 * @access  protected
	 * @type    Template
	 */
	protected $library;

	/**
	 * Metadata OpenGraph
	 *
	 * @type \O2System\Template\Collections\Metadata\Opengraph
	 */
	public $opengraph;

	public function __construct()
	{
		$this->opengraph = new Metadata\Opengraph();
	}

	/**
	 * Set Metadata Variables
	 *
	 * @access public
	 *
	 * @param   array $tags List of Metadata Tags Variables
	 *
	 * @return $this
	 */
	public function setMeta( array $meta )
	{
		$this->storage = [ ];
		$this->addMeta( $meta );

		return $this;
	}

	// ------------------------------------------------------------------------

	public function addMeta( $meta, $content = NULL )
	{
		if ( is_array( $meta ) )
		{
			foreach ( $meta as $name => $content )
			{
				$this->addMeta( $name, $content );
			}
		}
		elseif ( is_string( $meta ) )
		{
			if ( $meta === 'http-equiv' )
			{
				$value = key( $content );

				$this->storage[ 'http_equiv_' . $value ] = new Tag(
					'meta', [
					'http-equiv' => $value,
					'content'    => $content[ $value ],
				] );
			}
			elseif ( $meta === 'property' )
			{
				$value = key( $content );

				$this->storage[ 'property_' . $value ] = new Tag(
					'meta', [
					'property' => $value,
					'content'  => $content[ $value ],
				] );
			}
			else
			{
				$this->storage[ $meta ] = new Tag(
					'meta', [
					'name'    => $meta,
					'content' => ( is_array( $content ) ? implode( ', ', $content ) : $content ),
				] );
			}
		}
	}

	public function setCharset( $charset )
	{
		$this->storage[ 'charset' ] = $charset;
	}

	/**
	 * Render Metadata
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function render()
	{
		if ( ! empty( $this->storage ) )
		{
			return implode( PHP_EOL, $this->storage );
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}
}