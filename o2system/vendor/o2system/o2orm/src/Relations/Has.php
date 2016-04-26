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

use O2System\Glob\Helpers\Inflector;
use O2System\ORM\Interfaces\Relations;
use O2System\ORM\Interfaces\Table;

/**
 * ORM Has Relation Factory Class
 *
 * @package         o2orm
 * @author          Circle Creative Dev Team
 * @link            http://o2system.in/features/o2orm/with
 */
class Has extends Relations
{
    protected $_relationships = array();

    /**
     * Set Relations
     *
     * @access  public
     *
     * @param   array $references list of references
     */
    public function set_relationships( array $relationsips )
    {
        foreach ( $relationsips as $relationship )
        {
            $this->_set_relationship( $relationship );
        }
    }

    // ------------------------------------------------------------------------

    protected function _set_relationship( $relation )
    {
        // Try to load reference model
        $relation_model = $this->_load_relation_model( $relation );

        $relationship = new \stdClass();

        if ( $relation_model instanceof Model )
        {
            $relationship->model = $relation_model;
            $relationship->table = $relation_model->table;
        }
        else
        {
            if ( strpos( $relation, '.' ) !== FALSE )
            {
                $x_reference = explode( '.', $relation );

                $relationship->table = $x_reference[ 0 ];
                $relationship->field = $x_reference[ 1 ];
            }
            else
            {
                $relationship->table = $relation;
            }
        }

        if(!  isset($relationship->field))
        {
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
                    $relationship->fields = $this->_related_model->fields;
                }
                else
                {
                    $relationship->fields = $this->_reference_model->db->list_fields( $relationship->table );
                }

                if ( in_array( $relation_field, $relationship->fields ) )
                {
                    $relationship->field = $relation_field;
                }
            }
        }

        $prefixes = Table::$prefixes;
        array_unshift( $prefixes, $this->_reference_table );
        $relationship->index = trim( str_replace($prefixes,'', $relationship->table), '_' );

        $this->_relationships[ $relationship->index ] = $relationship;
    }


    /**
     * Result
     *
     * Only for implements Relation::result() method
     *
     * @return NULL
     */
    public function result()
    {
        if( ! empty( $this->_relationships ) )
        {
            $selects[] = $this->_reference_table . '.*';

            foreach( $this->_relationships as $relationship )
            {
                $this->_reference_model->db->join( $relationship->table, $relationship->table . '.' . $relationship->field . ' = ' . $this->_reference_table . '.' . $this->_reference_field );

                foreach( $relationship->fields as $field )
                {
                    $selects[] = $relationship->table . '.' . $field . ' AS ' . $relationship->index . '_' . $field;
                }
            }

            $this->_reference_model->db->select( implode( ', ', $selects ) );

            return $this->_relationships;
        }

        return array();
    }
}