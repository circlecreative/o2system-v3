<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Circle Creative Developer Team
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Models;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Metadata\Setting;
use O2System\Model;

class System extends Model
{
	/**
	 * @param        $key
	 * @param string $return
	 *
	 * @return array|bool|mixed
	 */
	public function settings( $key )
	{
		if ( $registry = \O2System::$registry->find( $key, 'modules' ) )
		{
			$this->db->where( 'code', $registry->code );
		}

		$result = $this->db->get( 'sys_registries', 1 );

		if ( $result->numRows() > 0 )
		{
			if ( ! is_null( $result->first()->settings ) )
			{
				if ( isset( $registry['settings'] ) )
				{
					$settings = array_merge( (array) $registry[ 'settings' ], get_object_vars( $result->first()->settings ) );
				}
				else
				{
					$settings = get_object_vars( $result->first()->settings );
				}
			}
			elseif ( $registry->offsetExists( 'settings' ) )
			{
				$settings = (array) $registry[ 'settings' ];
			}

			if ( isset( $settings ) )
			{
				return new Setting( $settings );
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Track View
	 *
	 * @return bool
	 */
	public function languages( $conditions = array() )
	{
		$this->db->select( 'id, label, code_iso, code_idiom' );

		if ( empty( $conditions ) )
		{
			$query = $this->db->orderBy( 'record_ordering', 'ASC' )->getWhere( 'sys_languages', [ 'record_status' => 2 ] );
		}
		else
		{
			$this->db->where( $conditions );
			$query = $this->db->orderBy( 'record_ordering', 'ASC' )->getWhere( 'sys_languages', [ 'record_status' => 2 ] );
		}

		if ( $query->numRows() == 1 )
		{
			return $query->first();
		}
		elseif ( $query->numRows() > 1 )
		{
			return $query->result();
		}

		return FALSE;
	}

	/**
	 * System Domain
	 *
	 * System domain registry getter
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function domains( $conditions = array() )
	{
		if ( empty( $conditions ) )
		{
			$query = $this->db->getWhere( 'sys_domains', [ 'record_status' => 2 ] );
		}
		else
		{
			$this->db->where( $conditions );
			$query = $this->db->getWhere( 'sys_domains', [ 'record_status' => 2 ] );
		}

		if ( $query->numRows() == 1 )
		{
			return $query->first();
		}
		elseif ( $query->numRows() > 1 )
		{
			return $query->result();
		}

		return FALSE;
	}

	public function registries($rsegment)
	{
		$this->db->from('sys_registries');
		$this->db->where('sys_registries.segments', $rsegment);
		$result = $this->db->get();

		if ($result->numRows()>0)
		{
			$row = $result->row();

			return $row->checksum;
		}

		return FALSE;
	}
}