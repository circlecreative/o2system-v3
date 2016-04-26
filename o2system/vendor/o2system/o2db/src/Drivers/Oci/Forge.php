<?php
/**
 * O2DB
 *
 * Open Source PHP Data Object Wrapper for PHP 5.4.0 or newer
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     O2ORM
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, .
 * @license     http://circle-creative.com/products/o2db/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\DB\Drivers\Oci;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Forge as ForgeInterface;

/**
 * PDO Oracle Forge Class
 *
 * Based on CodeIgniter PDO Oracle Forge Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Forge extends ForgeInterface
{
	/**
	 * CREATE DATABASE statement
	 *
	 * @type    string
	 */
	protected $_create_database = FALSE;

	/**
	 * DROP DATABASE statement
	 *
	 * @type    string
	 */
	protected $_drop_database = FALSE;

	/**
	 * CREATE TABLE IF statement
	 *
	 * @type    string
	 */
	protected $_create_table_if = 'CREATE TABLE IF NOT EXISTS';

	/**
	 * UNSIGNED support
	 *
	 * @type    bool|array
	 */
	protected $_unsigned = FALSE;

	// --------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param    string $alter_type ALTER type
	 * @param    string $table      Table name
	 * @param    mixed  $field      Column definition
	 *
	 * @return    string|string[]
	 */
	protected function _alter_table( $alter_type, $table, $field )
	{
		if ( $alter_type === 'DROP' )
		{
			return parent::_alter_table( $alter_type, $table, $field );
		}
		elseif ( $alter_type === 'CHANGE' )
		{
			$alter_type = 'MODIFY';
		}

		$sql = 'ALTER TABLE ' . $this->_driver->escape_identifiers( $table );
		$sqls = array();
		for ( $i = 0, $c = count( $field ); $i < $c; $i++ )
		{
			if ( $field[ $i ][ '_literal' ] !== FALSE )
			{
				$field[ $i ] = "\n\t" . $field[ $i ][ '_literal' ];
			}
			else
			{
				$field[ $i ][ '_literal' ] = "\n\t" . $this->_process_column( $field[ $i ] );

				if ( ! empty( $field[ $i ][ 'comment' ] ) )
				{
					$sqls[] = 'COMMENT ON COLUMN '
						. $this->_driver->escape_identifiers( $table ) . '.' . $this->_driver->escape_identifiers( $field[ $i ][ 'name' ] )
						. ' IS ' . $field[ $i ][ 'comment' ];
				}

				if ( $alter_type === 'MODIFY' && ! empty( $field[ $i ][ 'new_name' ] ) )
				{
					$sqls[] = $sql . ' RENAME COLUMN ' . $this->_driver->escape_identifiers( $field[ $i ][ 'name' ] )
						. ' ' . $this->_driver->escape_identifiers( $field[ $i ][ 'new_name' ] );
				}
			}
		}

		$sql .= ' ' . $alter_type . ' ';
		$sql .= ( count( $field ) === 1 )
			? $field[ 0 ]
			: '(' . implode( ',', $field ) . ')';

		// RENAME COLUMN must be executed after MODIFY
		array_unshift( $sqls, $sql );

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Field attribute AUTO_INCREMENT
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attr_auto_increment( &$attributes, &$field )
	{
		// Not supported - sequences and triggers must be used instead
	}
}