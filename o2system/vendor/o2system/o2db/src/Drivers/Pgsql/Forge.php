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

namespace O2System\DB\Drivers\Pgsql;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Forge as ForgeInterface;

/**
 * PDO PostgreSQL Forge Class
 *
 * Based on CodeIgniter PDO PostgreSQL Forge Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Forge extends ForgeInterface
{
	/**
	 * DROP TABLE IF statement
	 *
	 * @type    string
	 */
	protected $_drop_table_if = 'DROP TABLE IF EXISTS';

	/**
	 * UNSIGNED support
	 *
	 * @type    array
	 */
	protected $_unsigned = array(
		'INT2'     => 'INTEGER',
		'SMALLINT' => 'INTEGER',
		'INT'      => 'BIGINT',
		'INT4'     => 'BIGINT',
		'INTEGER'  => 'BIGINT',
		'INT8'     => 'NUMERIC',
		'BIGINT'   => 'NUMERIC',
		'REAL'     => 'DOUBLE PRECISION',
		'FLOAT'    => 'DOUBLE PRECISION',
	);

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @type    string
	 */
	protected $_null = 'NULL';

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param    object &$db Database object
	 *
	 * @return    void
	 */
	public function __construct( &$db )
	{
		parent::__construct( $db );

		if ( version_compare( $this->_driver->version(), '9.0', '>' ) )
		{
			$this->create_table_if = 'CREATE TABLE IF NOT EXISTS';
		}
	}

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
		if ( in_array( $alter_type, array( 'DROP', 'ADD' ), TRUE ) )
		{
			return parent::_alterTable( $alter_type, $table, $field );
		}

		$sql = 'ALTER TABLE ' . $this->_driver->escapeIdentifiers( $table );
		$sqls = array();
		for ( $i = 0, $c = count( $field ); $i < $c; $i++ )
		{
			if ( $field[ $i ][ '_literal' ] !== FALSE )
			{
				return FALSE;
			}

			if ( version_compare( $this->_driver->version(), '8', '>=' ) && isset( $field[ $i ][ 'type' ] ) )
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->_driver->escapeIdentifiers( $field[ $i ][ 'name' ] )
					. ' TYPE ' . $field[ $i ][ 'type' ] . $field[ $i ][ 'length' ];
			}

			if ( ! empty( $field[ $i ][ 'default' ] ) )
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->_driver->escapeIdentifiers( $field[ $i ][ 'name' ] )
					. ' SET DEFAULT ' . $field[ $i ][ 'default' ];
			}

			if ( isset( $field[ $i ][ 'null' ] ) )
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->_driver->escapeIdentifiers( $field[ $i ][ 'name' ] )
					. ( $field[ $i ][ 'null' ] === TRUE ? ' DROP NOT NULL' : ' SET NOT NULL' );
			}

			if ( ! empty( $field[ $i ][ 'new_name' ] ) )
			{
				$sqls[] = $sql . ' RENAME COLUMN ' . $this->_driver->escapeIdentifiers( $field[ $i ][ 'name' ] )
					. ' TO ' . $this->_driver->escapeIdentifiers( $field[ $i ][ 'new_name' ] );
			}

			if ( ! empty( $field[ $i ][ 'comment' ] ) )
			{
				$sqls[] = 'COMMENT ON COLUMN '
					. $this->_driver->escapeIdentifiers( $table ) . '.' . $this->_driver->escapeIdentifiers( $field[ $i ][ 'name' ] )
					. ' IS ' . $field[ $i ][ 'comment' ];
			}
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
		// Reset field lenghts for data types that don't support it
		if ( isset( $attributes[ 'CONSTRAINT' ] ) && stripos( $attributes[ 'TYPE' ], 'int' ) !== FALSE )
		{
			$attributes[ 'CONSTRAINT' ] = NULL;
		}

		switch ( strtoupper( $attributes[ 'TYPE' ] ) )
		{
			case 'TINYINT':
				$attributes[ 'TYPE' ] = 'SMALLINT';
				$attributes[ 'UNSIGNED' ] = FALSE;

				return;
			case 'MEDIUMINT':
				$attributes[ 'TYPE' ] = 'INTEGER';
				$attributes[ 'UNSIGNED' ] = FALSE;

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
		if ( ! empty( $attributes[ 'AUTO_INCREMENT' ] ) && $attributes[ 'AUTO_INCREMENT' ] === TRUE )
		{
			$field[ 'type' ] = ( $field[ 'type' ] === 'NUMERIC' )
				? 'BIGSERIAL'
				: 'SERIAL';
		}
	}
}