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

namespace O2System\ORM\Factory;

// ------------------------------------------------------------------------
use O2System\ORM\Model;
use O2System\ORM\Relations\Reference;

/**
 * ORM Mapper Factory Class
 *
 * @package         O2System
 * @subpackage      core/orm/factory
 * @category        core libraries driver factory
 * @author          Circle Creative Dev Team
 * @link            http://o2system.center/wiki/#ORM
 */
class Mapper
{
    /**
     * @type ORM
     */
    protected $_model;

    /**
     * @type array
     */
    protected $_map = array();

    protected $_relation = 'left';

    /**
     * Class Constructor
     *
     * @access  public
     *
     * @property-write  $_model
     *
     * @param Model     $model
     */
    public function __construct( Model $model )
    {
        $this->_model =& $model;

        if( ! isset( $this->_model->table ) )
        {
            $class_name = get_class( $model );
            $class_name = explode( '\\', $class_name );
            $table = strtolower( end( $class_name ) );

            if( $this->_model->db->table_exists( $table ) )
            {
                $this->_model->table = $table;
            }
        }
    }

    // ------------------------------------------------------------------------

    public function set( $table = NULL )
    {
        $this->_map = array();
        $table = isset( $table ) ? $this->_model->table : $table;

        if( isset( $table ) )
        {
            if( empty( $this->_model->fields ) )
            {
                $this->_model->fields = $this->_model->db->list_fields( $table );
            }

            if( empty( $this->_model->primary_key ) OR empty( $this->_model->primary_keys ) )
            {
                if( in_array( 'id', $this->_model->fields ) )
                {
                    $this->_model->_primary_key = 'id';
                    $this->_model->primary_keys[] = 'id';
                }

                if( in_array( 'id', $this->_model->fields ) AND in_array( 'lang', $this->_model->fields ) )
                {
                    $this->_model->primary_keys = [ 'id', 'lang' ];
                }
            }

            // Automapper
            foreach( $this->_model->fields as $field )
            {
                if( substr( $field, 0, 3 ) === 'id_' AND $field !== 'id_parent' )
                {
                    $this->add( $field, substr( $field, 3 ) );
                }
                elseif( substr( $field, -3 ) === '_id' AND $field !== 'parent_id' )
                {
                    $this->add( $field, substr( $field, 0, -3 ) );
                }
            }
        }
    }

    /**
     * Add
     *
     * Add relation mapper
     *
     * @access  public
     *
     * @uses    \ArrayObject()
     *
     * @return  $this
     */
    public function add( Reference $reference )
    {
        if( ! isset( $reference_table ) )
        {
            $x_reference_alias = explode( '_', $reference_alias );
            $x_reference_alias = array_map( array( $this, 'plural' ), $x_reference_alias );
            $reference_table = implode( '_', $x_reference_alias );
            $reference_index = trim( str_replace( $reference_alias, '', $foreign_key ), '_' );
        }

        foreach( $this->_model->table_prefixes as $table_prefix )
        {
            if( $this->_model->db->table_exists( $table_prefix . $reference_table ) )
            {
                if( empty( $reference_fields ) )
                {
                    $reference_fields = $this->_model->db->list_fields( $table_prefix . $reference_table );
                }

                $this->_map[ $reference_alias ] = new \ArrayObject( array(
                                                                        'reference_alias'  => $reference_alias,
                                                                        'foreign_key'      => $foreign_key,
                                                                        'reference_table'  => $table_prefix . $reference_table,
                                                                        'reference_index'  => $reference_index,
                                                                        'reference_fields' => $reference_fields
                                                                    ), \ArrayObject::ARRAY_AS_PROPS );

                return $this;
            }
        }


    }

    // ------------------------------------------------------------------------

    /**
     * Relation
     *
     * Set relation type
     *
     * @access  public
     *
     * @param string $relation relation type
     *
     * @return  $this
     */
    public function set_relation( $relation = 'left' )
    {
        $this->_relation = $relation;

        return $this;
    }

    /**
     * Build
     *
     * Set query builder join tables
     *
     * @access  public
     *
     * @property-read   $_model, $_map, $_relation
     */
    public function build()
    {
        $selects[] = $this->_model->table . '.*';

        if( ! empty( $this->_map ) )
        {
            foreach( $this->_map as $map )
            {
                $this->_model->db->join( $map->reference_table, $map->reference_table . '.' . $map->reference_index . ' = ' . $this->_model->table . '.' . $map->foreign_key, $this->_relation );

                foreach( $map->reference_fields as $field )
                {
                    $selects[] = $map->reference_table . '.' . $field . ' AS ' . $map->reference_alias . '_' . $field;
                }
            }
        }

        $this->_model->db->select( implode( ', ', $selects ) );
    }

    // ------------------------------------------------------------------------

    /**
     * Get References
     *
     * Getter for map references alias
     *
     * @used_by O2System\Core\ORM\Factory\Result()
     *
     * @return array|bool
     */
    public function get_references()
    {
        if( ! empty( $this->_map ) )
        {
            return $this->_map;
        }

        return FALSE;
    }
}