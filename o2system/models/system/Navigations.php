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

namespace O2System\Models\System;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\DB\Factory\Row\Metadata;
use O2System\Models\System;
use O2System\ORM\Interfaces\Hierarchical;
use O2System\Metadata\Module;

class Navigations extends System
{
	use Hierarchical;

	public $table = 'sys_navigations';

	public function __construct()
	{
		parent::__construct();

		$this->load->helpers( [ 'url', 'html' ] );
	}

	public function get_positions( $module = NULL )
	{
		$this->db->distinct();
		$this->db->select(
			$this->table . '.position, ' . $this->table . '.code'
		);
		$this->db->from( $this->table );
		$this->db->join( 'sys_registries', 'sys_registries.code = ' . $this->table . '.code', 'left' );

		if( ! is_bool( $module ) )
		{
			if ( empty( $module ) )
			{
				if ( \O2System::$active->offsetExists( 'module' ) )
				{
					$this->where( 'sys_registries.code', \O2System::$active[ 'module' ]->code );
				}
			}
			elseif( $module instanceof Module )
			{
				$this->where( 'sys_registries.code', $module->code );
			}
		}

		$result = $this->db
			->where( $this->table . '.record_status', 'PUBLISH' )
			->order_by( $this->table . '.position' )
			->get();

		if ( $result->num_rows() > 0 )
		{
			foreach ( $result as $row )
			{
				$results[] = $row->position;
			}

			return $results;
		}

		return array();
	}

	/**
	 * System Navigations Getter
	 *
	 * @access  public
	 *
	 * @uses    O2System\Core\URI::app()
	 * @see     O2System\Core\URI
	 *
	 * @return object
	 */
	public function get_items( $position, $conditions = NULL )
	{
		$this->db->from( $this->table );

		if ( is_null( $conditions ) )
		{
			if ( \O2System::$active->offsetExists( 'module' ) )
			{
				$this->db->where( 'code', \O2System::$active[ 'module' ]->code );
			}
		}
		elseif ( is_array( $conditions ) )
		{
			$this->db->where( $conditions );
		}

		if ( is_array( $position ) )
		{
			$this->db->where( $position );
		}
		else
		{
			$this->db->where( 'position', $position );
		}

		$result = $this->db
			->where( 'record_status', 'PUBLISH' )
			->order_by( 'record_left', 'ASC' )
			->order_by( 'record_ordering', 'ASC' )
			->get();

		if ( $result->num_rows() > 0 )
		{
			foreach ( $result as $row )
			{
				if ( strpos( $row->href, 'http' ) === FALSE )
				{
					if ( strpos( $row->href, '#' ) === FALSE )
					{
						$row->href = domain_url( implode( '/', [ $row->app, $row->href ] ) );
					}
				}

				if ( $row->settings instanceof Metadata )
				{
					if ( $row->settings->offsetExists( 'attr' ) )
					{
						$row->attr = (array) $row->settings->attr;
					}

					if ( $row->settings->offsetExists( 'css_icon' ) )
					{
						$row->icon = (array) $row->settings->css_icon;
					}
				}

				if ( $this->has_childs( $row->id ) !== FALSE )
				{
					$row->css_class = trim( $row->css_class . ' has-sub' );

					if ( isset( $row->attr[ 'class' ] ) )
					{
						$row->attr[ 'class' ] = trim( $row->attr[ 'class' ] ) . ' has-sub';
					}
					else
					{
						$row->attr = ['class'=>'has-sub'];
					}

					$row->sub = $this->get_items( [ 'id_parent', $row->id ] );
				}

				$results[] = $row;
			}

			return $results;
		}

		return array();
	}
}