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

namespace O2System\ORM\Relations;

// ------------------------------------------------------------------------

use O2System\ORM\Model;
use O2System\ORM\Interfaces\Relations;
use O2System\ORM\Factory\Result;

/**
 * ORM Belongs To Relationship Factory Class
 *
 * @package         O2System
 * @subpackage      core/orm/factory
 * @category        core libraries driver factory
 * @author          Circle Creative Dev Team
 * @link            http://o2system.center/wiki/#ORMBelongsTo
 */
class Belongs_To extends Relations
{
    /**
     * Result
     *
     * Belongs to query result
     *
     * @access  public
     *
     * @uses    O2System\ORM\Factory\Query
     *
     * @return  mixed
     */
    public function result()
    {
        if( $this->_related_model instanceof Model )
        {
            return $this->_related_model->find( $this->_reference_model->{ $this->_reference_field }, $this->_related_field );
        }
        else
        {
            $query = $this->_reference_model->db->get_where( $this->_related_table, array(
                $this->_related_field => $this->_reference_model->{ $this->_reference_field }
            ), 1 );

            if($query->num_rows() > 0)
            {
                return $query->first_row();
            }
        }

        return NULL;
    }
}