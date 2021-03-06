<?php
/**
 * O2DB
 *
 * Open Source PHP Data Abstraction Layers
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative)
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
 * @package     O2DB
 * @author      PT. Lingkar Kreasi (Circle Creative)
 * @copyright   Copyright (c) 2005 - 2016, PT. Lingkar Kreasi (Circle Creative)
 * @license     http://circle-creative.com/products/o2db/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\DB\Drivers\MySQL;


use O2System\DB\Interfaces\ForgeInterface;

class Forge extends ForgeInterface
{
	/**
	 * CREATE DATABASE statement
	 *
	 * @var    string
	 */
	protected $_sql_create_database_statement = 'CREATE DATABASE %s CHARACTER SET %s COLLATE %s';

	/**
	 * CREATE TABLE keys flag
	 *
	 * Whether table keys are created from within the
	 * CREATE TABLE statement.
	 *
	 * @var    bool
	 */
	protected $_is_create_table_keys = TRUE;

	/**
	 * UNSIGNED support
	 *
	 * @var    array
	 */
	protected $_unsigned = [
		'TINYINT',
		'SMALLINT',
		'MEDIUMINT',
		'INT',
		'INTEGER',
		'BIGINT',
		'REAL',
		'DOUBLE',
		'DOUBLE PRECISION',
		'FLOAT',
		'DECIMAL',
		'NUMERIC',
	];

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var    string
	 */
	protected $_null = 'NULL';

	//--------------------------------------------------------------------

	/**
	 * CREATE TABLE attributes
	 *
	 * @param    array $attributes Associative array of table attributes
	 *
	 * @return    string
	 */
	protected function _prepareTableAttributes( $attributes )
	{
		$sql = '';

		foreach ( array_keys( $attributes ) as $key )
		{
			if ( is_string( $key ) )
			{
				$sql .= ' ' . strtoupper( $key ) . ' = ' . $attributes[ $key ];
			}
		}

		if ( ! empty( $this->_conn->charset ) && ! strpos( $sql, 'CHARACTER SET' ) && ! strpos( $sql, 'CHARSET' ) )
		{
			$sql .= ' DEFAULT CHARACTER SET = ' . $this->_conn->charset;
		}

		if ( ! empty( $this->_conn->collate ) && ! strpos( $sql, 'COLLATE' ) )
		{
			$sql .= ' COLLATE = ' . $this->_conn->collate;
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param    string $alter_type ALTER type
	 * @param    string $table      Table name
	 * @param    mixed  $field      Column definition
	 *
	 * @return    string|string[]
	 */
	protected function _generateAlterTableSQLStatement( $alter_type, $table, $field )
	{
		if ( $alter_type === 'DROP' )
		{
			return parent::_generateAlterTableSQLStatement( $alter_type, $table, $field );
		}

		$sql = 'ALTER TABLE ' . $this->_conn->escapeIdentifiers( $table );
		for ( $i = 0, $c = count( $field ); $i < $c; $i++ )
		{
			if ( $field[ $i ][ '_literal' ] !== FALSE )
			{
				$field[ $i ] = ( $alter_type === 'ADD' )
					? "\n\tADD " . $field[ $i ][ '_literal' ]
					: "\n\tMODIFY " . $field[ $i ][ '_literal' ];
			}
			else
			{
				if ( $alter_type === 'ADD' )
				{
					$field[ $i ][ '_literal' ] = "\n\tADD ";
				}
				else
				{
					$field[ $i ][ '_literal' ] = empty( $field[ $i ][ 'new_name' ] ) ? "\n\tMODIFY " : "\n\tCHANGE ";
				}

				$field[ $i ] = $field[ $i ][ '_literal' ] . $this->_processColumn( $field[ $i ] );
			}
		}

		return [ $sql . implode( ',', $field ) ];
	}

	//--------------------------------------------------------------------

	/**
	 * Process column
	 *
	 * @param    array $field
	 *
	 * @return    string
	 */
	protected function _generateFieldSQLStatement( $field )
	{
		$extra_clause = isset( $field[ 'after' ] )
			? ' AFTER ' . $this->_conn->escapeIdentifiers( $field[ 'after' ] ) : '';

		if ( empty( $extra_clause ) && isset( $field[ 'first' ] ) && $field[ 'first' ] === TRUE )
		{
			$extra_clause = ' FIRST';
		}

		return $this->_conn->escapeIdentifiers( $field[ 'name' ] )
		. ( empty( $field[ 'new_name' ] ) ? '' : ' ' . $this->_conn->escapeIdentifiers( $field[ 'new_name' ] ) )
		. ' ' . $field[ 'type' ] . $field[ 'length' ]
		. $field[ 'unsigned' ]
		. $field[ 'null' ]
		. $field[ 'default' ]
		. $field[ 'auto_increment' ]
		. $field[ 'unique' ]
		. ( empty( $field[ 'comment' ] ) ? '' : ' COMMENT ' . $field[ 'comment' ] )
		. $extra_clause;
	}

	//--------------------------------------------------------------------

	/**
	 * Process indexes
	 *
	 * @param    string $table (ignored)
	 *
	 * @return    string
	 */
	protected function _generateIndexesSQLStatement( $table )
	{
		$sql = '';

		for ( $i = 0, $c = count( $this->keys ); $i < $c; $i++ )
		{
			if ( is_array( $this->keys[ $i ] ) )
			{
				for ( $i2 = 0, $c2 = count( $this->keys[ $i ] ); $i2 < $c2; $i2++ )
				{
					if ( ! isset( $this->fields[ $this->keys[ $i ][ $i2 ] ] ) )
					{
						unset( $this->keys[ $i ][ $i2 ] );
						continue;
					}
				}
			}
			elseif ( ! isset( $this->fields[ $this->keys[ $i ] ] ) )
			{
				unset( $this->keys[ $i ] );
				continue;
			}

			is_array( $this->keys[ $i ] ) OR $this->keys[ $i ] = [ $this->keys[ $i ] ];

			$sql .= ",\n\tKEY " . $this->_conn->escapeIdentifiers( implode( '_', $this->keys[ $i ] ) )
				. ' (' . implode( ', ', $this->_conn->escapeIdentifiers( $this->keys[ $i ] ) ) . ')';
		}

		$this->keys = [ ];

		return $sql;
	}

	//--------------------------------------------------------------------
}