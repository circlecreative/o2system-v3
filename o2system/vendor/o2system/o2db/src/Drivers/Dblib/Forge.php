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

namespace O2System\DB\Drivers\Dblib;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Forge as ForgeInterface;

/**
 * PDO DBLIB Forge Class
 *
 * Based on CodeIgniter PDO DBLIB Forge Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Forge extends ForgeInterface
{
	/**
	 * CREATE TABLE IF statement
	 *
	 * @type    string
	 */
	protected $_create_table_if = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE ID = object_id(N'%s') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)\nCREATE TABLE";

	/**
	 * DROP TABLE IF statement
	 *
	 * @type    string
	 */
	protected $_drop_table_if = "IF EXISTS (SELECT * FROM sysobjects WHERE ID = object_id(N'%s') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)\nDROP TABLE";

	/**
	 * UNSIGNED support
	 *
	 * @type    array
	 */
	protected $_unsigned = array(
		'TINYINT'  => 'SMALLINT',
		'SMALLINT' => 'INT',
		'INT'      => 'BIGINT',
		'REAL'     => 'FLOAT',
	);

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
	protected function _alterTable($alter_type, $table, $field )
	{
		if ( in_array( $alter_type, array( 'ADD', 'DROP' ), TRUE ) )
		{
			return parent::_alterTable( $alter_type, $table, $field );
		}

		$sql = 'ALTER TABLE ' . $this->_driver->escapeIdentifiers( $table ) . ' ALTER COLUMN ';
		$sqls = array();
		for ( $i = 0, $c = count( $field ); $i < $c; $i++ )
		{
			$sqls[] = $sql . $this->_processColumn( $field[ $i ] );
		}

		return $sqls;
	}

	// --------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 *
	 * Performs a data type mapping between different databases.
	 *
	 * @param    array &$attributes
	 *
	 * @return    void
	 */
	protected function _attrType(&$attributes )
	{
		switch ( strtoupper( $attributes[ 'TYPE' ] ) )
		{
			case 'MEDIUMINT':
				$attributes[ 'TYPE' ] = 'INTEGER';
				$attributes[ 'UNSIGNED' ] = FALSE;

				return;
			case 'INTEGER':
				$attributes[ 'TYPE' ] = 'INT';

				return;
			default:
				return;
		}
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
	protected function _attrAutoIncrement(&$attributes, &$field )
	{
		if ( ! empty( $attributes[ 'AUTO_INCREMENT' ] ) && $attributes[ 'AUTO_INCREMENT' ] === TRUE && stripos( $field[ 'type' ], 'int' ) !== FALSE )
		{
			$field[ 'auto_increment' ] = ' IDENTITY(1,1)';
		}
	}
}