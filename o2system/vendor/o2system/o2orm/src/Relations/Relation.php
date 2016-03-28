<?php
/**
 * O2ORM
 *
 * Open Source PHP Object Relations Mapper Library for PHP 5.4 or newer
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
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\ORM\Relations;

// ------------------------------------------------------------------------

use O2System\ORM\Model;
use O2System\Glob\Helpers\Inflector;

/**
 * ORM With Relation Factory Class
 *
 * @package         o2orm
 * @author          Circle Creative Dev Team
 * @link            http://o2system.in/features/o2orm/with
 */
class Relation
{
    protected $_self_model;
	public $model = NULL;
	public $table = NULL;
	public $primary_key = 'id';
    public $foreign_key = NULL;
	public $object_key = NULL;
	public $fields = array();

	/**
     * List of Possibles Table Prefixes
     *
     * @access  public
     * @type    array
     */
    public static $table_prefixes = array(
        '', // none prefix
        'tm_', // table master prefix
        't_', // table data prefix
        'tr_', // table relation prefix
        'ts_', // table statistic prefix
        'tb_', // table buffer prefix
    );

    public function __construct($relation, Model $self_model)
    {
        // Set Related Model
        $this->_self_model =& $self_model;

        // Try to load reference model
        $relation_model = $this->_load_model( $relation );

        if( $relation_model instanceof Model )
        {
            $this->model =& $relation_model;

            $this->_set_table( $relation_model->table );
            $this->_set_primary_key( $relation_model->primary_key );
        }
        else
        {
            if( strpos( $relation, '.' ) !== FALSE )
            {
                $x_reference = explode( '.', $relation );

                $this->_set_table( $x_reference[ 0 ] );
                $this->_set_primary_key( $x_reference[ 1 ] );
            }
            else
            {
                $this->_set_table( $relation );
                $this->_set_primary_key();
            }
        }
    }

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
    protected function _set_primary_key( $primary_key = NULL )
    {
        $primary_key = isset($primary_key) ? $primary_key : $this->primary_key;

        if( in_array( $primary_key, $this->fields ))
        {
            $this->primary_key = $primary_key;
            $this->set_related_key( $primary_key );
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
    public function set_related_key( $foreign_key )
    {
        $foreign_keys = array(
            $foreign_key . '_' . $this->object_key,
            $this->object_key . '_' . $foreign_key
        );

        foreach( $foreign_keys as $index_key )
        {
            if( in_array( $index_key, $this->_self_model->fields ) )
            {
                $this->foreign_key = $index_key;
            }
        }

        if( ! isset( $this->foreign_key ) )
        {
            $x_table = explode( '_', $this->_self_model->table );

            foreach( $x_table as $key => $segment )
            {
                if( in_array( $segment.'_', static::$table_prefixes ) )
                {
                    unset( $x_table[ $key ] );
                }
                else
                {
                    $x_table[ $key ] = Inflector::singularize( $segment );
                }
            }

            $x_table = implode( '_', $x_table );

            $foreign_keys = array(
                $foreign_key . '_' . $x_table,
                $x_table . '_' . $foreign_key
            );

            foreach( $foreign_keys as $index_key )
            {
                if( in_array( $index_key, $this->fields ) )
                {
                    $this->foreign_key = $index_key;
                }
            }
        }
    }

    // ------------------------------------------------------------------------

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
    protected function _set_table( $table )
    {
        $table = str_replace( static::$table_prefixes, '', $table);

        foreach( static::$table_prefixes as $prefix )
        {
            if( in_array( $prefix . $table, $this->_self_model->db->list_tables() ) )
            {
                // Set Reference Table
                $this->table = $prefix . $table;

                // Set Reference Fields
                $this->fields = array_keys( $this->_self_model->db->list_fields( $this->table ) );

                $x_table = explode( '_', $table );
                $x_table = array_map( 'Inflector::singular', $x_table );

                // Set Reference Object
                $this->object_key = implode( '_', $x_table );

                break;
            }
        }

        if( ! isset($this->table) )
        {
            $prefix = $this->_self_model->table . '_' ;

            if( in_array( $prefix . $table, $this->_self_model->db->list_tables() ) )
            {
                // Set Reference Table
                $this->table = $prefix . $table;

                // Set Reference Fields
                $this->fields = array_keys( $this->_self_model->db->list_fields( $this->table ) );

                $x_table = explode( '_', $table );
                $x_table = array_map( 'Inflector::singular', $x_table );

                // Set Reference Object
                $this->object_key = implode( '_', $x_table );
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
    protected function _load_model( $relation )
    {
        if( $relation instanceof Model )
        {
            return $relation;
        }
        else
        {
            $class_name = prepare_namespace( $relation );

            if( class_exists( $class_name ) )
            {
                return new $class_name();
            }
        }
    }

    // ------------------------------------------------------------------------
}