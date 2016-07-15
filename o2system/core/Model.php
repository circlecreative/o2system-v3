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

namespace O2System;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\Helpers\Inflector;
use O2System\Glob\Interfaces\ModelInterface;

/**
 * Model Class
 *
 * @package        O2System
 * @subpackage     core
 * @category       Core Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/core/model.html
 */
class Model extends ModelInterface
{
	/**
	 * O2DB Resource
	 *
	 * @access  public
	 * @type    \O2System\DB
	 */
	public $db = NULL;


	/**
	 * Class constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic Method __call
	 *
	 * @param       $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function __call( $method, $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( [ $this, $method ], $args );
		}
		elseif ( method_exists( $this->db, $method ) )
		{
			return call_user_func_array( [ $this->db, $method ], $args );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Insert Data
	 *
	 * Method to input data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   string $table Table Name
	 * @param   array  $data  Array of Input Data
	 *
	 * @return bool
	 */
	final public function insert( $row, $table = NULL )
	{
		$table = isset( $table ) ? $table : $this->table;

		$row = $this->_beforeProcess( $row, $table );

		if ( $last_insert_id = $this->db->insert( $table, $row ) )
		{
			$report = $this->_afterProcess();

			if ( empty( $report ) )
			{
				return $last_insert_id;
			}

			return $report;
		}

		return FALSE;
	}
	// ------------------------------------------------------------------------

	/**
	 * Update Data
	 *
	 * Method to update data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   string $table Table Name
	 * @param   array  $row   Array of Update Data
	 *
	 * @return bool
	 */
	final public function update( $row = [ ], $table = NULL )
	{
		$table       = isset( $table ) ? $table : $this->table;
		$primary_key = isset( $this->primary_key ) ? $this->primary_key : 'id';

		if ( empty( $this->primary_keys ) )
		{
			$this->db->where( $primary_key, $row[ $primary_key ] );
		}
		else
		{
			foreach ( $this->primary_keys as $primary_key )
			{
				$this->db->where( $primary_key, $row[ $primary_key ] );
			}
		}

		// Reset Primary Keys
		$this->primary_key  = 'id';
		$this->primary_keys = [ ];

		$row = $this->_beforeProcess( $row, $table );

		if ( $affected_rows = $this->db->update( $table, $row ) )
		{
			$report = $this->_afterProcess();

			if ( empty( $report ) )
			{
				return $affected_rows;
			}

			return $report;
		}

		return FALSE;
	}
	// ------------------------------------------------------------------------

	/**
	 * Update Data
	 *
	 * Method to update data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   string $table Table Name
	 * @param   array  $row   Array of Update Data
	 *
	 * @return bool
	 */
	final public function softDelete( $id, $table = NULL )
	{
		$table       = isset( $table ) ? $table : $this->table;
		$primary_key = isset( $this->primary_key ) ? $this->primary_key : 'id';

		$row[ 'record_status' ] = 'DELETE';

		if ( empty( $this->primary_keys ) )
		{
			$this->db->where( $primary_key, $id );
			$row[ $primary_key ] = $id;
		}
		elseif ( is_array( $id ) )
		{
			foreach ( $this->primary_keys as $primary_key )
			{
				$this->db->where( $primary_key, $row[ $primary_key ] );
				$row[ $primary_key ] = $id[ $primary_key ];
			}
		}
		else
		{
			foreach ( $this->primary_keys as $primary_key )
			{
				$this->db->where( $primary_key, $row[ $primary_key ] );
			}

			$row[ reset( $this->primary_keys ) ] = $id;
		}

		// Reset Primary Keys
		$this->primary_key  = 'id';
		$this->primary_keys = [ ];

		$row = $this->_beforeProcess( $row, $table );

		if ( $affected_rows = $this->db->update( $table, $row ) )
		{
			$report = $this->_afterProcess();

			if ( empty( $report ) )
			{
				return $affected_rows;
			}

			return $report;
		}

		return FALSE;
	}
	// ------------------------------------------------------------------------

	/**
	 * Delete Data
	 *
	 * Method to delete data with all childs and file
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   string $table Table Name
	 * @param   int    $id    Data ID
	 * @param   bool   $force Force Delete
	 *
	 * @return bool
	 */
	public function delete( $id, $table = NULL, $force = FALSE )
	{
		if ( ( isset( $table ) AND is_bool( $table ) ) OR ! isset( $table ) )
		{
			$table = $this->table;
		}

		if ( isset( $this->_adjacency_enabled ) )
		{
			if ( $this->hasChildren( $id, $table ) )
			{
				if ( $force === TRUE )
				{
					if ( $childrens = $this->getChildren( $id, $table ) )
					{
						foreach ( $childrens as $children )
						{
							$report[ $children->id ] = $this->delete( $children->id, $force, $table );
						}
					}
				}
			}
		}

		// Recursive Search File
		$fields = [ 'file', 'document', 'image', 'picture', 'cover', 'avatar', 'photo', 'video' ];

		foreach ( $fields as $field )
		{
			if ( $this->db->fieldExists( $field, $table ) )
			{
				$primary_key = isset( $this->primary_key ) ? $this->primary_key : 'id';

				if ( empty( $this->primary_keys ) )
				{
					$this->db->where( $primary_key, $id );
				}
				elseif ( is_array( $id ) )
				{
					foreach ( $this->primary_keys as $primary_key )
					{
						$this->db->where( $primary_key, $id[ $primary_key ] );
					}
				}
				else
				{
					$this->db->where( reset( $this->primary_keys ), $id );
				}

				$result = $this->db->select( $field )->limit( 1 )->get( $table );

				if ( $result->numRows() > 0 )
				{
					if ( ! empty( $result->first()->{$field} ) )
					{
						$directory = new \RecursiveDirectoryIterator( APPSPATH );
						$iterator  = new \RecursiveIteratorIterator( $directory );
						$results   = new \RegexIterator( $iterator, '/' . $result->first()->{$field} . '/i', \RecursiveRegexIterator::GET_MATCH );

						foreach ( $results as $file )
						{
							if ( is_array( $file ) )
							{
								foreach ( $file as $filepath )
								{
									@unlink( $filepath );
								}
							}
						}
					}
				}
			}
		}

		$primary_key = isset( $this->primary_key ) ? $this->primary_key : 'id';

		if ( empty( $this->primary_keys ) )
		{
			$this->db->where( $primary_key, $id );
		}
		elseif ( is_array( $id ) )
		{
			foreach ( $this->primary_keys as $primary_key )
			{
				$this->db->where( $primary_key, $id[ $primary_key ] );
			}
		}
		else
		{
			$this->db->where( reset( $this->primary_keys ), $id );
		}

		// Reset Primary Keys
		$this->primary_key  = 'id';
		$this->primary_keys = [ ];

		if ( $affected_rows = $this->db->delete( $table ) )
		{
			$report = $this->_afterProcess();

			if ( empty( $report ) )
			{
				return (int) $affected_rows;
			}

			return $report;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Before Process
	 *
	 * Process row data before insert or update
	 *
	 * @param $row
	 * @param $table
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function _beforeProcess( $row, $table )
	{
		if ( ! empty( $this->_before_process ) )
		{
			foreach ( $this->_before_process as $process_method )
			{
				$row = $this->{$process_method}( $row, $table );
			}
		}

		return $row;
	}

	// ------------------------------------------------------------------------

	/**
	 * After Process
	 *
	 * Runs all after process method actions
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function _afterProcess()
	{
		$report = [ ];

		if ( ! empty( $this->_after_process ) )
		{
			foreach ( $this->_after_process as $process_method )
			{
				$report[ $process_method ] = $this->{$process_method}();
			}
		}

		return $report;
	}
}