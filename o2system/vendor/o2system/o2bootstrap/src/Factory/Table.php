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

class Table extends FactoryInterface
{
	/**
	 * Table Caption
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $_caption = NULL;

	/**
	 * Table Headers
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_headers = array();

	/**
	 * Table Footers
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_footers = array();

	protected $_colgroup = array();

	/**
	 * Table Rows
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_rows = array();

	protected $_tag = 'table';

	/**
	 * Table Attributes
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_attributes = array(
		'class' => [ 'table' ],
	);

	/**
	 * Table Responsive Flag
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $_is_responsive = FALSE;


	/**
	 * build
	 */
	public function build()
	{
		@list( $attr ) = func_get_args();

		if ( isset( $attr ) )
		{
			$this->add_attributes( $attr );
		}
	}

	public function set_caption( $caption, $attr = array() )
	{
		if ( $caption instanceof FactoryInterface )
		{
			$caption->set_tag( 'caption' );
		}
		else
		{
			$caption = new Tag( 'caption', $caption, $attr );
		}

		$this->_caption = $caption;

		return $this;
	}

	/**
	 * Set Table Headers
	 *
	 * @param   array $headers
	 *
	 * @access  public
	 * @return  $this
	 */
	public function set_headers( array $headers )
	{
		$this->_headers = $headers;

		return $this;
	}

	/**
	 * Set Table Footers
	 *
	 * @param   array $headers
	 *
	 * @access  public
	 * @return  $this
	 */
	public function set_footers( array $footers )
	{
		$this->_footers = $footers;

		return $this;
	}

	/**
	 * Set Rows
	 *
	 * @param   array $rows
	 *
	 * @access  public
	 * @return  $this
	 */
	public function set_rows( array $rows )
	{
		foreach ( $rows as $row )
		{
			$this->add_row( $row );
		}

		return $this;
	}

	public function set_colgroup( array $cols )
	{

	}

	public function add_row( array $row, $attr = array() )
	{
		$values = array_values( $row );

		if ( count( $values ) == count( $this->_headers ) )
		{
			$row = new Tag( 'tr', $attr );

			foreach ( $values as $value )
			{
				$row->append_content( new Tag( 'td', $value ) );
			}

			$this->_rows[] = $row;
		}

		return $this;
	}

	/**
	 * table stripped
	 *
	 * @return object
	 */
	public function is_striped()
	{
		$this->add_class( 'table-striped' );

		return $this;
	}

	/**
	 * table border
	 *
	 * @return object
	 */
	public function is_bordered()
	{
		$this->add_class( 'table-bordered' );

		return $this;
	}

	/**
	 * table hover
	 *
	 * @return object
	 */
	public function is_hovered()
	{
		$this->add_class( 'table-hover' );

		return $this;
	}

	/**
	 * table condensed
	 *
	 * @return object
	 */
	public function is_condensed()
	{
		$this->add_class( 'table-condensed' );

		return $this;
	}

	/**
	 * table responsive
	 *
	 * @return object
	 */
	public function is_responsive()
	{
		$this->_is_responsive = TRUE;

		return $this;
	}

	/**
	 * Render
	 *
	 * @access  public
	 * @return  string
	 */
	public function render( array $attr = array() )
	{
		if ( ! empty( $this->_rows ) )
		{
			// Set Table Caption
			if ( ! empty( $this->_caption ) )
			{
				$output[] = $this->_caption;
			}

			// Set Table Headers
			$thead = new Tag( 'tr' );

			foreach ( $this->_headers as $column )
			{
				$thead->append_content( new Tag( 'th', $column ) );
			}

			$output[] = new Tag( 'thead', $thead->render() );

			// Set Table Body
			$output[] = new Tag( 'tbody', implode( PHP_EOL, $this->_rows ) );

			// Set Table Footers
			if ( ! empty( $this->_footers ) )
			{
				$tfoot = new Tag( 'tr' );

				foreach ( $this->_footers as $column )
				{
					$tfoot->append_content( new Tag( 'th', $column ) );
				}

				$output[] = new Tag( 'tfoot', $tfoot );
			}

			$table = new Tag( $this->_tag, implode( PHP_EOL, $output ), $this->_attributes );

			if ( $this->_is_responsive )
			{
				return ( new Tag( 'div', $table, [ 'class' => 'table-responsive' ] ) )->render();
			}

			return $table->render();
		}

		return '';
	}
}