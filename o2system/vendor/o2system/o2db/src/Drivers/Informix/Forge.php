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

namespace O2System\DB\Drivers\Informix;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Forge as ForgeInterface;

/**
 * PDO Informix Forge Class
 *
 * Based on CodeIgniter PDO Informix Forge Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Forge extends ForgeInterface
{
	/**
	 * RENAME TABLE statement
	 *
	 * @type    string
	 */
	protected $_rename_table = 'RENAME TABLE %s TO %s';

	/**
	 * UNSIGNED support
	 *
	 * @type    array
	 */
	protected $_unsigned = array(
		'SMALLINT'   => 'INTEGER',
		'INT'        => 'BIGINT',
		'INTEGER'    => 'BIGINT',
		'REAL'       => 'DOUBLE PRECISION',
		'SMALLFLOAT' => 'DOUBLE PRECISION',
	);

	/**
	 * DEFAULT value representation in CREATE/ALTER TABLE statements
	 *
	 * @type    string
	 */
	protected $_default = ', ';

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
		if ( $alter_type === 'CHANGE' )
		{
			$alter_type = 'MODIFY';
		}

		return parent::_alter_table( $alter_type, $table, $field );
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
	protected function _attr_type( &$attributes )
	{
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
			case 'BYTE':
			case 'TEXT':
			case 'BLOB':
			case 'CLOB':
				$attributes[ 'UNIQUE' ] = FALSE;
				if ( isset( $attributes[ 'DEFAULT' ] ) )
				{
					unset( $attributes[ 'DEFAULT' ] );
				}

				return;
			default:
				return;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Field attribute UNIQUE
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attr_unique( &$attributes, &$field )
	{
		if ( ! empty( $attributes[ 'UNIQUE' ] ) && $attributes[ 'UNIQUE' ] === TRUE )
		{
			$field[ 'unique' ] = ' UNIQUE CONSTRAINT ' . $this->_driver->escape_identifiers( $field[ 'name' ] );
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
	protected function _attr_auto_increment( &$attributes, &$field )
	{
		// Not supported
	}
}