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
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\ORM;

// ------------------------------------------------------------------------

use O2System\DB;
use O2System\Gears\Tracer;
use O2System\Glob\Helpers\Inflector;
use O2System\ORM\Factory\Row;
use O2System\ORM\Interfaces\Table;

class Model
{
	/**
	 * O2DB Resource
	 *
	 * @access  public
	 * @type    \O2System\DB
	 */
	public $db = NULL;

	/**
	 * Model Table
	 *
	 * @access  public
	 * @type    string
	 */
	public $table = NULL;

	/**
	 * Model Table Fields
	 *
	 * @access  public
	 * @type    array
	 */
	public $fields = array();

	/**
	 * Model Table Primary Key
	 *
	 * @access  public
	 * @type    string
	 */
	public $primary_key = 'id';

	/**
	 * Model Table Primary Keys
	 *
	 * @access  public
	 * @type    array
	 */
	public $primary_keys = array();

	/**
	 * Model Table Relations
	 *
	 * @access  public
	 * @type    array
	 */
	public $relations = array();

	/**
	 * Model Table Record User Model
	 *
	 * @access  public
	 * @type    array
	 */
	public $record_user_model = NULL;

	// ------------------------------------------------------------------------

	/**
	 * Class Constructor
	 *
	 * @params  array $data
	 *
	 * @access  public
	 */
	public function __construct( array $data = array() )
	{
		// Set table fields
		if ( isset( $this->table ) )
		{
			$this->fields = $this->db->list_fields( $this->table );
		}

		if ( ! empty( $data ) )
		{
			$this->set_data( $data );
		}

		// Set Result Class
		//$this->db->set_row_class( '\O2System\ORM\Factory\Row', [ &$this ] );
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Data
	 *
	 * @params  array $field
	 * @params  mixed $value
	 *
	 * @access  public
	 */
	public function set_data( $field, $value = NULL )
	{
		if ( is_array( $field ) )
		{
			foreach ( $field as $key => $value )
			{
				$this->__set( $key, $value );
			}
		}
		else
		{
			$this->__set( $field, $value );
		}
	}

	/**
	 * Set Data
	 *
	 * @params  string $field
	 * @params  mixed  $value
	 *
	 * @access  public
	 */
	public function __set( $field, $value )
	{
		$setter = "set_" . Inflector::underscore( $field );

		if ( method_exists( $this, $setter ) )
		{
			$value = call_user_func( array( $this, $setter ), $value );
			$this->set_data( $field, $value );
		}
		else
		{
			$this->{$field} = $value;
		}
	}

	/**
	 * Get Data
	 *
	 * @params  string $field
	 *
	 * @access  public
	 */
	public function &__get( $field )
	{
		$prop[ 0 ] = '';

		if ( property_exists( $this, $field ) )
		{
			$prop[ 0 ] = $this->{$field};
		}
		elseif ( isset( $this->data->{$field} ) )
		{
			$getter = 'get_' . Inflector::underscore( $field );

			if ( method_exists( $this, $getter ) )
			{
				$prop[ 0 ] = $this->{$getter}();
			}
		}

		return $prop[ 0 ];
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic function __call
	 *
	 * @params  string $method
	 * @params  array  $args
	 *
	 * @access  public
	 */
	public function __call( $method, $args = array() )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		elseif ( method_exists( $this->db, $method ) )
		{
			return call_user_func_array( array( $this->db, $method ), $args );
		}
		elseif ( method_exists( $this, 'scope_' . $method ) )
		{
			return call_user_func_array( array( $this, 'scope_' . $method ), $args );
		}
		else
		{
			$table = plural( str_replace( 'get_', '', $method ) );

			if ( strpos( $method, 'master' ) !== FALSE )
			{
				$table = 'tm_' . str_replace( [ '_master', 'master_' ], '', $table );
			}
			elseif ( strpos( $method, 'buffer' ) !== FALSE OR strpos( $method, 'buffers' ) !== FALSE )
			{
				$table = 'tb_' . str_replace( [ '_buffer', '_buffers', 'buffer_', 'buffers_' ], '', $table );
			}
			elseif ( strpos( $method, 'relation' ) !== FALSE OR strpos( $method, 'relations' ) !== FALSE )
			{
				$table = 'tr_' . str_replace( [ '_relation', '_relations', 'relation_', 'relations_' ], '', $table );
			}
			elseif ( strpos( $method, 'statistic' ) !== FALSE OR strpos( $method, 'statistics' ) !== FALSE )
			{
				$table = 'ts_' . str_replace( [ '_statistic', '_statistics', 'statistic_', 'statistics_' ], '', $table );
			}

			foreach ( $this->table_prefixes as $prefix )
			{
				if ( $this->db->table_exists( $prefix . $table ) )
				{
					if ( ! empty( $params ) )
					{
						if ( ! is_array( reset( $params ) ) )
						{
							$params = array( 'id' => reset( $params ) );
						}
						else
						{
							$params = reset( $params );
						}

						return $this->all( $params );
					}
					else
					{
						return $this->all();
					}
				}
			}
		}

		return array();
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic function __callStatic
	 *
	 * @params  string $method
	 * @params  array  $args
	 *
	 * @access  public
	 */
	public static function __callStatic( $method, $args = array() )
	{
		$model = get_called_class();

		return ( new $model )->__call( $method, $args );
	}

	// ------------------------------------------------------------------------


	/**
	 * Paging
	 *
	 * @param int $page
	 * @param int $entries
	 */
	protected function page( $page = 1, $entries = 20 )
	{
		if ( $page == 1 )
		{
			$this->db->limit( $entries, 0 );
			$this->numbering = 1;
		}
		else
		{
			$offset = ( $page - 1 ) * $entries;
			$this->db->limit( $entries, $offset );
			$this->numbering = 1 + $offset;
		}

		return $this->all();
	}
}