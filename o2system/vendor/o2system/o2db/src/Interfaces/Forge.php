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

namespace O2System\DB\Interfaces;

// ------------------------------------------------------------------------
use O2System\DB\Exception;

/**
 * Database Forge Class
 *
 * Based on CodeIgniter Database Forge Class
 *
 * @category	Database
 * @author		O2System Developer Team
 * @link		http://o2system.in/features/o2db
 */
abstract class Forge
{
	/**
	 * Database object
	 *
	 * @type    object
	 */
	protected $_driver;

	/**
	 * Fields data
	 *
	 * @type    array
	 */
	public $fields = array();

	/**
	 * Keys data
	 *
	 * @type    array
	 */
	public $keys = array();

	/**
	 * Primary Keys data
	 *
	 * @type    array
	 */
	public $primary_keys = array();

	/**
	 * Database character set
	 *
	 * @type    string
	 */
	public $charset = '';

	// --------------------------------------------------------------------

	/**
	 * CREATE DATABASE statement
	 *
	 * @type    string
	 */
	protected $_create_database = 'CREATE DATABASE %s';

	/**
	 * DROP DATABASE statement
	 *
	 * @type    string
	 */
	protected $_drop_database = 'DROP DATABASE %s';

	/**
	 * CREATE TABLE statement
	 *
	 * @type    string
	 */
	protected $_create_table = "%s %s (%s\n)";

	/**
	 * CREATE TABLE IF statement
	 *
	 * @type    string
	 */
	protected $_create_table_if = 'CREATE TABLE IF NOT EXISTS';

	/**
	 * CREATE TABLE keys flag
	 *
	 * Whether table keys are created from within the
	 * CREATE TABLE statement.
	 *
	 * @type    bool
	 */
	protected $_create_table_keys = FALSE;

	/**
	 * DROP TABLE IF EXISTS statement
	 *
	 * @type    string
	 */
	protected $_drop_table_if = 'DROP TABLE IF EXISTS';

	/**
	 * RENAME TABLE statement
	 *
	 * @type    string
	 */
	protected $_rename_table = 'ALTER TABLE %s RENAME TO %s;';

	/**
	 * UNSIGNED support
	 *
	 * @type    bool|array
	 */
	protected $_unsigned = TRUE;

	/**
	 * NULL value representatin in CREATE/ALTER TABLE statements
	 *
	 * @type    string
	 */
	protected $_null = '';

	/**
	 * DEFAULT value representation in CREATE/ALTER TABLE statements
	 *
	 * @type    string
	 */
	protected $_default = ' DEFAULT ';

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param    object &$db Database object
	 *
	 * @return    void
	 */
	public function __construct( &$driver )
	{
		$this->_driver = clone $driver;
		$this->_driver->row_class_name = '\O2System\DB\Factory\Row';
		$this->_driver->row_class_args = NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Create database
	 *
	 * @param    string $db_name
	 *
	 * @return    bool
	 */
	public function create_database( $db_name )
	{
		if ( $this->_create_database === FALSE )
		{
			return FALSE;
		}
		elseif ( ! $this->_driver->query( sprintf( $this->_create_database, $db_name, $this->_driver->charset, $this->_driver->collate ) ) )
		{
			return FALSE;
		}

		if ( ! empty( $this->_driver->data_cache[ 'db_names' ] ) )
		{
			$this->_driver->data_cache[ 'db_names' ][] = $db_name;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @param    string $db_name
	 *
	 * @return    bool
	 */
	public function drop_database( $db_name )
	{
		if ( $this->_drop_database === FALSE )
		{
			throw new Exception('Unsupported feature of the database platform you are using.');
		}
		elseif ( ! $this->_driver->query( sprintf( $this->_drop_database, $db_name ) ) )
		{
			throw new Exception('Unable to drop the specified database.');
		}

		if ( ! empty( $this->_driver->data_cache[ 'db_names' ] ) )
		{
			$key = array_search( strtolower( $db_name ), array_map( 'strtolower', $this->_driver->data_cache[ 'db_names' ] ), TRUE );
			if ( $key !== FALSE )
			{
				unset( $this->_driver->data_cache[ 'db_names' ][ $key ] );
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Add Key
	 *
	 * @param    string $key
	 * @param    bool   $primary
	 *
	 * @return    O2DB_DB_forge
	 */
	public function add_key( $key, $primary = FALSE )
	{
		if ( $primary === TRUE && is_array( $key ) )
		{
			foreach ( $key as $one )
			{
				$this->add_key( $one, $primary );
			}

			return $this;
		}

		if ( $primary === TRUE )
		{
			$this->primary_keys[] = $key;
		}
		else
		{
			$this->keys[] = $key;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Add Field
	 *
	 * @param    array $field
	 *
	 * @return    O2DB_DB_forge
	 */
	public function add_field( $field )
	{
		if ( is_string( $field ) )
		{
			if ( $field === 'id' )
			{
				$this->add_field( array(
					                  'id' => array(
						                  'type'           => 'INT',
						                  'constraint'     => 9,
						                  'auto_increment' => TRUE,
					                  ),
				                  ) );
				$this->add_key( 'id', TRUE );
			}
			else
			{
				if ( strpos( $field, ' ' ) === FALSE )
				{
					throw new Exception( 'Field information is required for that operation.' );
				}

				$this->fields[] = $field;
			}
		}

		if ( is_array( $field ) )
		{
			$this->fields = array_merge( $this->fields, $field );
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Create Table
	 *
	 * @param    string $table         Table name
	 * @param    bool   $if_not_exists Whether to add IF NOT EXISTS condition
	 * @param    array  $attributes    Associative array of table attributes
	 *
	 * @return    bool
	 */
	public function create_table( $table, $if_not_exists = FALSE, array $attributes = array() )
	{
		if ( $table === '' )
		{
			throw new Exception( 'A table name is required for that operation.' );
		}
		else
		{
			$table = $this->_driver->table_prefix . $table;
		}

		if ( count( $this->fields ) === 0 )
		{
			throw new Exception( 'Field information is required.' );
		}

		$sql = $this->_create_table( $table, $if_not_exists, $attributes );

		if ( is_bool( $sql ) )
		{
			$this->_reset();
			if ( $sql === FALSE )
			{
				throw new Exception('Unsupported feature of the database platform you are using.');
			}
		}

		if ( ( $result = $this->_driver->query( $sql ) ) !== FALSE )
		{
			empty( $this->_driver->data_cache[ 'table_names' ] ) OR $this->_driver->data_cache[ 'table_names' ][] = $table;

			// Most databases don't support creating indexes from within the CREATE TABLE statement
			if ( ! empty( $this->keys ) )
			{
				for ( $i = 0, $sqls = $this->_process_indexes( $table ), $c = count( $sqls ); $i < $c; $i++ )
				{
					$this->_driver->query( $sqls[ $i ] );
				}
			}
		}

		$this->_reset();

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Create Table
	 *
	 * @param    string $table         Table name
	 * @param    bool   $if_not_exists Whether to add 'IF NOT EXISTS' condition
	 * @param    array  $attributes    Associative array of table attributes
	 *
	 * @return    mixed
	 */
	protected function _create_table( $table, $if_not_exists, $attributes )
	{
		if ( $if_not_exists === TRUE && $this->_create_table_if === FALSE )
		{
			if ( $this->_driver->table_exists( $table ) )
			{
				return TRUE;
			}
			else
			{
				$if_not_exists = FALSE;
			}
		}

		$sql = ( $if_not_exists )
			? sprintf( $this->_create_table_if, $this->_driver->escape_identifiers( $table ) )
			: 'CREATE TABLE';

		$columns = $this->_process_fields( TRUE );
		for ( $i = 0, $c = count( $columns ); $i < $c; $i++ )
		{
			$columns[ $i ] = ( $columns[ $i ][ '_literal' ] !== FALSE )
				? "\n\t" . $columns[ $i ][ '_literal' ]
				: "\n\t" . $this->_process_column( $columns[ $i ] );
		}

		$columns = implode( ',', $columns )
			. $this->_process_primary_keys( $table );

		// Are indexes created from within the CREATE TABLE statement? (e.g. in MySQL)
		if ( $this->_create_table_keys === TRUE )
		{
			$columns .= $this->_process_indexes( $table );
		}

		// _create_table will usually have the following format: "%s %s (%s\n)"
		$sql = sprintf( $this->_create_table . '%s',
		                $sql,
		                $this->_driver->escape_identifiers( $table ),
		                $columns,
		                $this->_create_table_attr( $attributes )
		);

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * CREATE TABLE attributes
	 *
	 * @param    array $attributes Associative array of table attributes
	 *
	 * @return    string
	 */
	protected function _create_table_attr( $attributes )
	{
		$sql = '';

		foreach ( array_keys( $attributes ) as $key )
		{
			if ( is_string( $key ) )
			{
				$sql .= ' ' . strtoupper( $key ) . ' ' . $attributes[ $key ];
			}
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	public function truncate_table( $table_name )
	{
		if ( $table_name === '' )
		{
			throw new Exception( 'A table name is required for that operation.' );
		}

		$query = $this->_truncate_table( $this->_driver->table_prefix . $table_name );
		
		if ( $query === FALSE )
		{
			throw new Exception('Unsupported feature of the database platform you are using.');
		}
		elseif ( $query === TRUE )
		{
			return TRUE;
		}
	}

	/**
	 * Drop Table
	 *
	 * @param    string $table_name Table name
	 * @param    bool   $if_exists  Whether to add an IF EXISTS condition
	 *
	 * @return    bool
	 */
	public function drop_table( $table_name, $if_exists = FALSE )
	{
		if ( $table_name === '' )
		{
			throw new Exception( 'A table name is required for that operation.' );
		}

		$query = $this->_driver->_drop_table( $this->_driver->table_prefix . $table_name, $if_exists );

		if ( $query === FALSE )
		{
			throw new Exception('Unsupported feature of the database platform you are using.');
		}
		elseif ( $query === TRUE )
		{
			return TRUE;
		}

		$query = $this->_driver->query( $query );

		// Update table list cache
		if ( $query && ! empty( $this->_driver->data_cache[ 'table_names' ] ) )
		{
			$key = array_search( strtolower( $this->_driver->table_prefix . $table_name ), array_map( 'strtolower', $this->_driver->data_cache[ 'table_names' ] ), TRUE );
			if ( $key !== FALSE )
			{
				unset( $this->_driver->data_cache[ 'table_names' ][ $key ] );
			}
		}

		return $query;
	}

	// --------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * Generates a platform-specific DROP TABLE string
	 *
	 * @param    string $table     Table name
	 * @param    bool   $if_exists Whether to add an IF EXISTS condition
	 *
	 * @return    string
	 */
	protected function _drop_table( $table, $if_exists )
	{
		$sql = 'DROP TABLE';

		if ( $if_exists )
		{
			if ( $this->_drop_table_if === FALSE )
			{
				if ( ! $this->_driver->table_exists( $table ) )
				{
					return TRUE;
				}
			}
			else
			{
				$sql = sprintf( $this->_drop_table_if, $this->_driver->escape_identifiers( $table ) );
			}
		}

		return $sql . ' ' . $this->_driver->escape_identifiers( $table );
	}

	// --------------------------------------------------------------------

	/**
	 * Rename Table
	 *
	 * @param    string $table_name     Old table name
	 * @param    string $new_table_name New table name
	 *
	 * @return    bool
	 */
	public function rename_table( $table_name, $new_table_name )
	{
		if ( $table_name === '' OR $new_table_name === '' )
		{
			throw new Exception( 'A table name is required for that operation.' );

			return FALSE;
		}
		elseif ( $this->_rename_table === FALSE )
		{
			throw new Exception('Unsupported feature of the database platform you are using.');
		}

		$result = $this->_driver->query( sprintf( $this->_rename_table,
		                                     $this->_driver->escape_identifiers( $this->_driver->table_prefix . $table_name ),
		                                     $this->_driver->escape_identifiers( $this->_driver->table_prefix . $new_table_name ) )
		);

		if ( $result && ! empty( $this->_driver->data_cache[ 'table_names' ] ) )
		{
			$key = array_search( strtolower( $this->_driver->table_prefix . $table_name ), array_map( 'strtolower', $this->_driver->data_cache[ 'table_names' ] ), TRUE );
			if ( $key !== FALSE )
			{
				$this->_driver->data_cache[ 'table_names' ][ $key ] = $this->_driver->table_prefix . $new_table_name;
			}
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Column Add
	 *
	 * @todo    Remove deprecated $_after option in 3.1+
	 *
	 * @param    string $table  Table name
	 * @param    array  $field  Column definition
	 * @param    string $_after Column for AFTER clause (deprecated)
	 *
	 * @return    bool
	 */
	public function add_column( $table, $field, $_after = NULL )
	{
		// Work-around for literal column definitions
		is_array( $field ) OR $field = array( $field );

		foreach ( array_keys( $field ) as $k )
		{
			// Backwards-compatibility work-around for MySQL/CUBRID AFTER clause (remove in 3.1+)
			if ( $_after !== NULL && is_array( $field[ $k ] ) && ! isset( $field[ $k ][ 'after' ] ) )
			{
				$field[ $k ][ 'after' ] = $_after;
			}

			$this->add_field( array( $k => $field[ $k ] ) );
		}

		$sqls = $this->_alter_table( 'ADD', $this->_driver->table_prefix . $table, $this->_process_fields() );
		$this->_reset();
		if ( $sqls === FALSE )
		{
			throw new Exception('Unsupported feature of the database platform you are using.');
		}

		for ( $i = 0, $c = count( $sqls ); $i < $c; $i++ )
		{
			if ( $this->_driver->query( $sqls[ $i ] ) === FALSE )
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Column Drop
	 *
	 * @param    string $table       Table name
	 * @param    string $column_name Column name
	 *
	 * @return    bool
	 */
	public function drop_column( $table, $column_name )
	{
		$sql = $this->_alter_table( 'DROP', $this->_driver->table_prefix . $table, $column_name );
		if ( $sql === FALSE )
		{
			throw new Exception('Unsupported feature of the database platform you are using.');
		}

		return $this->_driver->query( $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Column Modify
	 *
	 * @param    string $table Table name
	 * @param    string $field Column definition
	 *
	 * @return    bool
	 */
	public function modify_column( $table, $field )
	{
		// Work-around for literal column definitions
		is_array( $field ) OR $field = array( $field );

		foreach ( array_keys( $field ) as $k )
		{
			$this->add_field( array( $k => $field[ $k ] ) );
		}

		if ( count( $this->fields ) === 0 )
		{
			throw new Exception( 'Field information is required.' );
		}

		$sqls = $this->_alter_table( 'CHANGE', $this->_driver->table_prefix . $table, $this->_process_fields() );
		$this->_reset();
		if ( $sqls === FALSE )
		{
			throw new Exception('Unsupported feature of the database platform you are using.');
		}

		for ( $i = 0, $c = count( $sqls ); $i < $c; $i++ )
		{
			if ( $this->_driver->query( $sqls[ $i ] ) === FALSE )
			{
				return FALSE;
			}
		}

		return TRUE;
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
	protected function _alter_table( $alter_type, $table, $field )
	{
		$sql = 'ALTER TABLE ' . $this->_driver->escape_identifiers( $table ) . ' ';

		// DROP has everything it needs now.
		if ( $alter_type === 'DROP' )
		{
			return $sql . 'DROP COLUMN ' . $this->_driver->escape_identifiers( $field );
		}

		$sql .= ( $alter_type === 'ADD' )
			? 'ADD '
			: $alter_type . ' COLUMN ';

		$sqls = array();
		for ( $i = 0, $c = count( $field ); $i < $c; $i++ )
		{
			$sqls[] = $sql
				. ( $field[ $i ][ '_literal' ] !== FALSE ? $field[ $i ][ '_literal' ] : $this->_process_column( $field[ $i ] ) );
		}

		return $sqls;
	}

	// --------------------------------------------------------------------

	/**
	 * Process fields
	 *
	 * @param    bool $create_table
	 *
	 * @return    array
	 */
	protected function _process_fields( $create_table = FALSE )
	{
		$fields = array();

		foreach ( $this->fields as $key => $attributes )
		{
			if ( is_int( $key ) && ! is_array( $attributes ) )
			{
				$fields[] = array( '_literal' => $attributes );
				continue;
			}

			$attributes = array_change_key_case( $attributes, CASE_UPPER );

			if ( $create_table === TRUE && empty( $attributes[ 'TYPE' ] ) )
			{
				continue;
			}

			isset( $attributes[ 'TYPE' ] ) && $this->_attr_type( $attributes );

			$field = array(
				'name'           => $key,
				'new_name'       => isset( $attributes[ 'NAME' ] ) ? $attributes[ 'NAME' ] : NULL,
				'type'           => isset( $attributes[ 'TYPE' ] ) ? $attributes[ 'TYPE' ] : NULL,
				'length'         => '',
				'unsigned'       => '',
				'null'           => '',
				'unique'         => '',
				'default'        => '',
				'auto_increment' => '',
				'_literal'       => FALSE,
			);

			isset( $attributes[ 'TYPE' ] ) && $this->_attr_unsigned( $attributes, $field );

			if ( $create_table === FALSE )
			{
				if ( isset( $attributes[ 'AFTER' ] ) )
				{
					$field[ 'after' ] = $attributes[ 'AFTER' ];
				}
				elseif ( isset( $attributes[ 'FIRST' ] ) )
				{
					$field[ 'first' ] = (bool) $attributes[ 'FIRST' ];
				}
			}

			$this->_attr_default( $attributes, $field );

			if ( isset( $attributes[ 'NULL' ] ) )
			{
				if ( $attributes[ 'NULL' ] === TRUE )
				{
					$field[ 'null' ] = empty( $this->_null ) ? '' : ' ' . $this->_null;
				}
				else
				{
					$field[ 'null' ] = ' NOT NULL';
				}
			}
			elseif ( $create_table === TRUE )
			{
				$field[ 'null' ] = ' NOT NULL';
			}

			$this->_attr_auto_increment( $attributes, $field );
			$this->_attr_unique( $attributes, $field );

			if ( isset( $attributes[ 'COMMENT' ] ) )
			{
				$field[ 'comment' ] = $this->_driver->escape( $attributes[ 'COMMENT' ] );
			}

			if ( isset( $attributes[ 'TYPE' ] ) && ! empty( $attributes[ 'CONSTRAINT' ] ) )
			{
				switch ( strtoupper( $attributes[ 'TYPE' ] ) )
				{
					case 'ENUM':
					case 'SET':
						$attributes[ 'CONSTRAINT' ] = $this->_driver->escape( $attributes[ 'CONSTRAINT' ] );
						$field[ 'length' ] = is_array( $attributes[ 'CONSTRAINT' ] )
							? "('" . implode( "','", $attributes[ 'CONSTRAINT' ] ) . "')"
							: '(' . $attributes[ 'CONSTRAINT' ] . ')';
						break;
					default:
						$field[ 'length' ] = is_array( $attributes[ 'CONSTRAINT' ] )
							? '(' . implode( ',', $attributes[ 'CONSTRAINT' ] ) . ')'
							: '(' . $attributes[ 'CONSTRAINT' ] . ')';
						break;
				}
			}

			$fields[] = $field;
		}

		return $fields;
	}

	// --------------------------------------------------------------------

	/**
	 * Process column
	 *
	 * @param    array $field
	 *
	 * @return    string
	 */
	protected function _process_column( $field )
	{
		return $this->_driver->escape_identifiers( $field[ 'name' ] )
		. ' ' . $field[ 'type' ] . $field[ 'length' ]
		. $field[ 'unsigned' ]
		. $field[ 'default' ]
		. $field[ 'null' ]
		. $field[ 'auto_increment' ]
		. $field[ 'unique' ];
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
		// Usually overridden by drivers
	}

	// --------------------------------------------------------------------

	/**
	 * Field attribute UNSIGNED
	 *
	 * Depending on the _unsigned property value:
	 *
	 *    - TRUE will always set $field['unsigned'] to 'UNSIGNED'
	 *    - FALSE will always set $field['unsigned'] to ''
	 *    - array(TYPE) will set $field['unsigned'] to 'UNSIGNED',
	 *        if $attributes['TYPE'] is found in the array
	 *    - array(TYPE => UTYPE) will change $field['type'],
	 *        from TYPE to UTYPE in case of a match
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attr_unsigned( &$attributes, &$field )
	{
		if ( empty( $attributes[ 'UNSIGNED' ] ) OR $attributes[ 'UNSIGNED' ] !== TRUE )
		{
			return;
		}

		// Reset the attribute in order to avoid issues if we do type conversion
		$attributes[ 'UNSIGNED' ] = FALSE;

		if ( is_array( $this->_unsigned ) )
		{
			foreach ( array_keys( $this->_unsigned ) as $key )
			{
				if ( is_int( $key ) && strcasecmp( $attributes[ 'TYPE' ], $this->_unsigned[ $key ] ) === 0 )
				{
					$field[ 'unsigned' ] = ' UNSIGNED';

					return;
				}
				elseif ( is_string( $key ) && strcasecmp( $attributes[ 'TYPE' ], $key ) === 0 )
				{
					$field[ 'type' ] = $key;

					return;
				}
			}

			return;
		}

		$field[ 'unsigned' ] = ( $this->_unsigned === TRUE ) ? ' UNSIGNED' : '';
	}

	// --------------------------------------------------------------------

	/**
	 * Field attribute DEFAULT
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attr_default( &$attributes, &$field )
	{
		if ( $this->_default === FALSE )
		{
			return;
		}

		if ( array_key_exists( 'DEFAULT', $attributes ) )
		{
			if ( $attributes[ 'DEFAULT' ] === NULL )
			{
				$field[ 'default' ] = empty( $this->_null ) ? '' : $this->_default . $this->_null;

				// Override the NULL attribute if that's our default
				$attributes[ 'NULL' ] = TRUE;
				$field[ 'null' ] = empty( $this->_null ) ? '' : ' ' . $this->_null;
			}
			else
			{
				$field[ 'default' ] = $this->_default . $this->_driver->escape( $attributes[ 'DEFAULT' ] );
			}
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
			$field[ 'unique' ] = ' UNIQUE';
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
		if ( ! empty( $attributes[ 'AUTO_INCREMENT' ] ) && $attributes[ 'AUTO_INCREMENT' ] === TRUE && stripos( $field[ 'type' ], 'int' ) !== FALSE )
		{
			$field[ 'auto_increment' ] = ' AUTO_INCREMENT';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Process primary keys
	 *
	 * @param    string $table Table name
	 *
	 * @return    string
	 */
	protected function _process_primary_keys( $table )
	{
		$sql = '';

		for ( $i = 0, $c = count( $this->primary_keys ); $i < $c; $i++ )
		{
			if ( ! isset( $this->fields[ $this->primary_keys[ $i ] ] ) )
			{
				unset( $this->primary_keys[ $i ] );
			}
		}

		if ( count( $this->primary_keys ) > 0 )
		{
			$sql .= ",\n\tCONSTRAINT " . $this->_driver->escape_identifiers( 'pk_' . $table )
				. ' PRIMARY KEY(' . implode( ', ', $this->_driver->escape_identifiers( $this->primary_keys ) ) . ')';
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Process indexes
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _process_indexes( $table )
	{
		$sqls = array();

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

			is_array( $this->keys[ $i ] ) OR $this->keys[ $i ] = array( $this->keys[ $i ] );

			$sqls[] = 'CREATE INDEX ' . $this->_driver->escape_identifiers( $table . '_' . implode( '_', $this->keys[ $i ] ) )
				. ' ON ' . $this->_driver->escape_identifiers( $table )
				. ' (' . implode( ', ', $this->_driver->escape_identifiers( $this->keys[ $i ] ) ) . ');';
		}

		return $sqls;
	}

	// --------------------------------------------------------------------

	/**
	 * Reset
	 *
	 * Resets table creation vars
	 *
	 * @return    void
	 */
	protected function _reset()
	{
		$this->fields = $this->keys = $this->primary_keys = array();
	}
}