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

namespace O2System\ORM\Interfaces;

// ------------------------------------------------------------------------
use O2System\Glob\Helpers\Inflector;
use O2System\Glob\Helpers\Convension;
use O2System\ORM\Model;
use O2System\ORM\Relations\Related;

/**
 * ORM Relation Factory Class
 *
 * @package         O2System
 * @subpackage      core/orm/factory
 * @category        core libraries driver factory
 * @author          Circle Creative Dev Team
 * @link            http://o2system.center/wiki/#ORM
 */
abstract class Relations
{
	/**
	 * Reference of active ORM model instance
	 *
	 * @access  protected
	 *
	 * @type    object  Instance of O2System\ORM model
	 */
	protected $_reference_model = NULL;
	protected $_reference_table = NULL;
	protected $_reference_field   = 'id';

	protected $_related_model = NULL;
	protected $_related_table = NULL;
	protected $_related_field   = 'id';

	/**
	 * Class Constructor
	 *
	 * @access  public
	 * @final   this method can't be overwrite
	 *
	 * @uses    O2System\Core\Loader::helper()
	 *
	 * @property-write  $_model, $_db
	 *
	 * @param ORM       $model
	 */
	public function __construct( Model $reference_model )
	{
		// set reference of ORM model
		$this->_reference_model = $reference_model;
		$this->_reference_table = $this->_reference_model->table;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Reference
	 *
	 * Set reference from table name, model name or model instance
	 *
	 * @access  public
	 *
	 * @property-read       $_related_model
	 * @property-write      $_related_table
	 *
	 * @param string|object $relation table name, model name or instance of ORM model
	 */
	public function set_relation( $relation )
	{
		// Try to load reference model
		$relation_model = $this->_load_relation_model( $relation );

		if ( $relation_model instanceof Model )
		{
			$this->_related_model =& $relation_model;

			$this->_set_relation_table( $relation_model->table );
			$this->_set_relation_field( $relation_model->primary_key );
		}
		else
		{
			if ( strpos( $relation, '.' ) !== FALSE )
			{
				$x_reference = explode( '.', $relation );

				$this->_set_relation_table( $x_reference[ 0 ] );
				$this->_set_relation_field( $x_reference[ 1 ] );
			}
			else
			{
				$this->_set_relation_table( $relation );
				$this->_set_relation_field();
			}
		}
	}

	// ------------------------------------------------------------------------

	public function set_reference_field( $reference_field = NULL )
	{
		if ( isset( $reference_field ) )
		{
			$this->_reference_field = $reference_field;

			return;
		}

		$related_table = str_replace( Table::$prefixes, '', $this->_related_table );
		$related_field = Inflector::singularize( $related_table );

		$reference_fields = array(
			'id_' . $related_field,
			$related_field . '_id',
		);

		foreach ( $reference_fields as $reference_field )
		{
			if ( in_array( $reference_field, $this->_reference_model->fields ) )
			{
				$this->_reference_field = $reference_field;
			}
		}
	}

	/**
	 * Set Reference Table
	 *
	 * @access  protected
	 *
	 * @property-read   $_model
	 * @property-write  $_related_table
	 *
	 * @param   string  $table
	 */
	protected function _set_relation_table( $table = NULL )
	{
		if ( isset( $table ) )
		{
			$this->_related_table = $table;

			return;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Foreign Key
	 *
	 * @access  public
	 *
	 * @property-read       $_related_table, $_model, $_foreign_key
	 * @property-write      $_index_key
	 *
	 * @param   string|null $primary_key   working table foreign key
	 */
	protected function _set_relation_field( $relation_field = NULL )
	{
		if ( isset( $relation_field ) )
		{
			$this->_related_field = $relation_field;

			return;
		}

		$reference_table = str_replace( Table::$prefixes, '', $this->_reference_table );
		$reference_field = Inflector::singularize( $reference_table );

		$relation_fields = array(
			'id_' . $reference_field,
			$reference_field . '_id',
		);

		foreach ( $relation_fields as $relation_field )
		{
			if( isset($this->_related_model) )
			{
				$related_fields = $this->_related_model->fields;
			}
			else
			{
				$related_fields = $this->_reference_model->db->list_fields( $this->_related_table );
			}

			if ( in_array( $relation_field, $related_fields ) )
			{
				$this->_related_field = $relation_field;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Load Reference Model
	 *
	 * @access  protected
	 *
	 * @property-write        $_related_model
	 *
	 * @param   string|object $related model name or instance of ORM model
	 */
	protected function _load_relation_model( $relation )
	{
		if ( $relation instanceof Model )
		{
			return $relation;
		}
		else
		{
			$class_name = Convension::to_namespace( $relation );

			if ( class_exists( $class_name ) )
			{
				return new $class_name();
			}
		}
	}

	// ------------------------------------------------------------------------



	/**
	 * Result
	 *
	 * Abstract: extended class of Relation must implements result method
	 *
	 * @return mixed
	 */
	abstract public function result();
}