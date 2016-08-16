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

namespace O2System\DB\Interfaces;


use O2System\DB\ForgeException;

abstract class ForgeInterfaceOld extends ConfigInterface
{
	/**
	 * The active database connection.
	 *
	 * @var ConnectionInterface
	 */
	protected $_conn;

	/**
	 * List of fields.
	 *
	 * @var array
	 */
	protected $fields = [ ];

	/**
	 * List of keys.
	 *
	 * @var array
	 */
	protected $keys = [ ];

	/**
	 * List of primary keys.
	 *
	 * @var array
	 */
	protected $primaryKeys = [ ];

	/**
	 * Character set used.
	 *
	 * @var string
	 */
	protected $charset = '';

	//--------------------------------------------------------------------

	/**
	 * CREATE DATABASE statement
	 *
	 * @var    string
	 */
	protected $_sql_create_database_statement = 'CREATE DATABASE %s';

	/**
	 * DROP DATABASE statement
	 *
	 * @var    string
	 */
	protected $_sql_drop_database_statement = 'DROP DATABASE %s';

	/**
	 * CREATE TABLE statement
	 *
	 * @var    string
	 */
	protected $_sql_create_table_statement = "%s %s (%s\n)";

	/**
	 * CREATE TABLE IF statement
	 *
	 * @var    string
	 */
	protected $_sql_create_table_if_statement = 'CREATE TABLE IF NOT EXISTS';

	/**
	 * CREATE TABLE keys flag
	 *
	 * Whether table keys are created from within the
	 * CREATE TABLE statement.
	 *
	 * @var    bool
	 */
	protected $_is_create_table_keys = FALSE;

	/**
	 * DROP TABLE IF EXISTS statement
	 *
	 * @var    string
	 */
	protected $_sql_drop_table_if_exists_statement = 'DROP TABLE IF EXISTS';

	/**
	 * RENAME TABLE statement
	 *
	 * @var    string
	 */
	protected $_sql_rename_table_statement = 'ALTER TABLE %s RENAME TO %s;';

	/**
	 * UNSIGNED support
	 *
	 * @var    bool|array
	 */
	protected $unsigned = TRUE;

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var    string
	 */
	protected $null = '';

	/**
	 * DEFAULT value representation in CREATE/ALTER TABLE statements
	 *
	 * @var    string
	 */
	protected $default = ' DEFAULT ';

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param ConnectionInterface $db
	 */
	public function __construct( ConnectionInterface $db )
	{
		$this->_conn =& $db;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides access to the forge's current database connection.
	 *
	 * @return ConnectionInterface
	 */
	public function getConnection()
	{
		return $this->_conn;
	}

	//--------------------------------------------------------------------


	/**
	 * Create database
	 *
	 * @param    string $database
	 *
	 * @return    bool
	 */
	public function createDatabase( $database )
	{
		if ( $this->_sql_create_database_statement === FALSE )
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'This feature is not available for the database you are using.' );
			}

			return FALSE;
		}
		elseif ( ! $this->_conn->query(
			sprintf(
				$this->_sql_create_database_statement, $database, $this->_conn->charset,
				$this->_conn->collate ) )
		)
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'Unable to drop the specified database.' );
			}

			return FALSE;
		}

		if ( ! empty( $this->_conn->registry[ 'database' ] ) )
		{
			$this->_conn->registry[ 'database' ][] = $database;
		}

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @param    string $database
	 *
	 * @return    bool
	 */
	public function dropDatabase( $database )
	{
		if ( $this->_sql_drop_database_statement === FALSE )
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'This feature is not available for the database you are using.' );
			}

			return FALSE;
		}
		elseif ( ! $this->_conn->query( sprintf( $this->_sql_drop_database_statement, $database ) ) )
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'Unable to drop the specified database.' );
			}

			return FALSE;
		}

		if ( ! empty( $this->_conn->registry[ 'database' ] ) )
		{
			$key = array_search( strtolower( $database ), array_map( 'strtolower', $this->_conn->registry[ 'database' ] ), TRUE );
			if ( $key !== FALSE )
			{
				unset( $this->_conn->registry[ 'database' ][ $key ] );
			}
		}

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Create Table
	 *
	 * @param    string $table         Table name
	 * @param    bool   $if_not_exists Whether to add IF NOT EXISTS condition
	 * @param    array  $attributes    Associative array of table attributes
	 *
	 * @return    bool
	 */
	public function createTable( $table, $if_not_exists = FALSE, array $attributes = [ ] )
	{
		if ( $table === '' )
		{
			throw new \InvalidArgumentException( 'A table name is required for that operation.' );
		}
		else
		{
			$table = $this->_conn->DBPrefix . $table;
		}

		if ( count( $this->fields ) === 0 )
		{
			throw new \RuntimeException( 'Field information is required.' );
		}

		$sql = $this->_generateTableSQLStatement( $table, $if_not_exists, $attributes );

		if ( is_bool( $sql ) )
		{
			$this->_reset();
			if ( $sql === FALSE )
			{
				if ( $this->_conn->is_debug_mode )
				{
					throw new ForgeException( 'This feature is not available for the database you are using.' );
				}

				return FALSE;
			}
		}

		if ( ( $result = $this->_conn->query( $sql ) ) !== FALSE )
		{
			empty( $this->_conn->registry[ 'table_names' ] ) OR $this->_conn->registry[ 'table_names' ][] = $table;

			// Most databases don't support creating indexes from within the CREATE TABLE statement
			if ( ! empty( $this->keys ) )
			{
				for ( $i = 0, $sqls = $this->_generateIndexesSQLStatement( $table ), $c = count( $sqls ); $i < $c; $i++ )
				{
					$this->_conn->query( $sqls[ $i ] );
				}
			}
		}

		$this->_reset();

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Create Table
	 *
	 * @param    string $table         Table name
	 * @param    bool   $if_not_exists Whether to add 'IF NOT EXISTS' condition
	 * @param    array  $attributes    Associative array of table attributes
	 *
	 * @return    mixed
	 */
	protected function _generateTableSQLStatement( $table, $if_not_exists, $attributes )
	{
		if ( $if_not_exists === TRUE && $this->_sql_create_table_if_statement === FALSE )
		{
			if ( $this->_conn->isTableExists( $table ) )
			{
				return TRUE;
			}
			else
			{
				$if_not_exists = FALSE;
			}
		}

		$sql = ( $if_not_exists )
			? sprintf( $this->_sql_create_table_if_statement, $this->_conn->escapeIdentifiers( $table ) )
			: 'CREATE TABLE';

		$columns = $this->_prepareTableFields( TRUE );
		for ( $i = 0, $c = count( $columns ); $i < $c; $i++ )
		{
			$columns[ $i ] = ( $columns[ $i ][ '_literal' ] !== FALSE )
				? "\n\t" . $columns[ $i ][ '_literal' ]
				: "\n\t" . $this->_generateFieldSQLStatement( $columns[ $i ] );
		}

		$columns = implode( ',', $columns )
			. $this->_generatePrimaryKeysSQLStatement( $table );

		// Are indexes created from within the CREATE TABLE statement? (e.g. in MySQL)
		if ( $this->_is_create_table_keys === TRUE )
		{
			$columns .= $this->_generateIndexesSQLStatement( $table );
		}

		// createTableStr will usually have the following format: "%s %s (%s\n)"
		$sql = sprintf(
			$this->_sql_create_table_statement . '%s',
			$sql,
			$this->_conn->escapeIdentifiers( $table ),
			$columns,
			$this->_prepareTableAttributes( $attributes )
		);

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Process fields
	 *
	 * @param    bool $create_table
	 *
	 * @return    array
	 */
	protected function _prepareTableFields( $create_table = FALSE )
	{
		$fields = [ ];

		foreach ( $this->fields as $key => $attributes )
		{
			if ( is_int( $key ) && ! is_array( $attributes ) )
			{
				$fields[] = [ '_literal' => $attributes ];
				continue;
			}

			$attributes = array_change_key_case( $attributes, CASE_UPPER );

			if ( $create_table === TRUE && empty( $attributes[ 'TYPE' ] ) )
			{
				continue;
			}

			isset( $attributes[ 'TYPE' ] ) && $this->_attributeType( $attributes );

			$field = [
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
			];

			isset( $attributes[ 'TYPE' ] ) && $this->_attributeUnsigned( $attributes, $field );

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

			$this->_attributeDefault( $attributes, $field );

			if ( isset( $attributes[ 'NULL' ] ) )
			{
				if ( $attributes[ 'NULL' ] === TRUE )
				{
					$field[ 'null' ] = empty( $this->null ) ? '' : ' ' . $this->null;
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

			$this->_attributeAutoIncrement( $attributes, $field );
			$this->_attributeUnique( $attributes, $field );

			if ( isset( $attributes[ 'COMMENT' ] ) )
			{
				$field[ 'comment' ] = $this->_conn->escape( $attributes[ 'COMMENT' ] );
			}

			if ( isset( $attributes[ 'TYPE' ] ) && ! empty( $attributes[ 'CONSTRAINT' ] ) )
			{
				switch ( strtoupper( $attributes[ 'TYPE' ] ) )
				{
					case 'ENUM':
					case 'SET':
						$attributes[ 'CONSTRAINT' ] = $this->_conn->escape( $attributes[ 'CONSTRAINT' ] );
						$field[ 'length' ]          = is_array( $attributes[ 'CONSTRAINT' ] )
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

	//--------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 *
	 * Performs a data type mapping between different databases.
	 *
	 * @param    array &$attributes
	 *
	 * @return    void
	 */
	protected function _attributeType( &$attributes )
	{
		// Usually overridden by drivers
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute UNSIGNED
	 *
	 * Depending on the unsigned property value:
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
	protected function _attributeUnsigned( &$attributes, &$field )
	{
		if ( empty( $attributes[ 'UNSIGNED' ] ) OR $attributes[ 'UNSIGNED' ] !== TRUE )
		{
			return;
		}

		// Reset the attribute in order to avoid issues if we do type conversion
		$attributes[ 'UNSIGNED' ] = FALSE;

		if ( is_array( $this->unsigned ) )
		{
			foreach ( array_keys( $this->unsigned ) as $key )
			{
				if ( is_int( $key ) && strcasecmp( $attributes[ 'TYPE' ], $this->unsigned[ $key ] ) === 0 )
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

		$field[ 'unsigned' ] = ( $this->unsigned === TRUE ) ? ' UNSIGNED' : '';
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute DEFAULT
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attributeDefault( &$attributes, &$field )
	{
		if ( $this->default === FALSE )
		{
			return;
		}

		if ( array_key_exists( 'DEFAULT', $attributes ) )
		{
			if ( $attributes[ 'DEFAULT' ] === NULL )
			{
				$field[ 'default' ] = empty( $this->null ) ? '' : $this->default . $this->null;

				// Override the NULL attribute if that's our default
				$attributes[ 'NULL' ] = TRUE;
				$field[ 'null' ]      = empty( $this->null ) ? '' : ' ' . $this->null;
			}
			else
			{
				$field[ 'default' ] = $this->default . $this->_conn->escape( $attributes[ 'DEFAULT' ] );
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute AUTO_INCREMENT
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attributeAutoIncrement( &$attributes, &$field )
	{
		if ( ! empty( $attributes[ 'AUTO_INCREMENT' ] ) && $attributes[ 'AUTO_INCREMENT' ] === TRUE &&
			stripos( $field[ 'type' ], 'int' ) !== FALSE
		)
		{
			$field[ 'auto_increment' ] = ' AUTO_INCREMENT';
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute UNIQUE
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attributeUnique( &$attributes, &$field )
	{
		if ( ! empty( $attributes[ 'UNIQUE' ] ) && $attributes[ 'UNIQUE' ] === TRUE )
		{
			$field[ 'unique' ] = ' UNIQUE';
		}
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
		return $this->_conn->escapeIdentifiers( $field[ 'name' ] )
		. ' ' . $field[ 'type' ] . $field[ 'length' ]
		. $field[ 'unsigned' ]
		. $field[ 'default' ]
		. $field[ 'null' ]
		. $field[ 'auto_increment' ]
		. $field[ 'unique' ];
	}

	//--------------------------------------------------------------------

	/**
	 * Process primary keys
	 *
	 * @param    string $table Table name
	 *
	 * @return    string
	 */
	protected function _generatePrimaryKeysSQLStatement( $table )
	{
		$sql = '';

		for ( $i = 0, $c = count( $this->primaryKeys ); $i < $c; $i++ )
		{
			if ( ! isset( $this->fields[ $this->primaryKeys[ $i ] ] ) )
			{
				unset( $this->primaryKeys[ $i ] );
			}
		}

		if ( count( $this->primaryKeys ) > 0 )
		{
			$sql .= ",\n\tCONSTRAINT " . $this->_conn->escapeIdentifiers( 'pk_' . $table )
				. ' PRIMARY KEY(' . implode( ', ', $this->_conn->escapeIdentifiers( $this->primaryKeys ) ) . ')';
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Process indexes
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _generateIndexesSQLStatement( $table )
	{
		$sqls = [ ];

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

			$sqls[] = 'CREATE INDEX ' . $this->_conn->escapeIdentifiers( $table . '_' . implode( '_', $this->keys[ $i ] ) )
				. ' ON ' . $this->_conn->escapeIdentifiers( $table )
				. ' (' . implode( ', ', $this->_conn->escapeIdentifiers( $this->keys[ $i ] ) ) . ');';
		}

		return $sqls;
	}

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
				$sql .= ' ' . strtoupper( $key ) . ' ' . $attributes[ $key ];
			}
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Reset
	 *
	 * Resets table creation vars
	 *
	 * @return    void
	 */
	protected function _reset()
	{
		$this->fields = $this->keys = $this->primaryKeys = [ ];
	}

	//--------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * @param    string $table_name Table name
	 * @param    bool   $if_exists  Whether to add an IF EXISTS condition
	 *
	 * @return    bool
	 */
	public function dropTable( $table_name, $if_exists = FALSE )
	{
		if ( $table_name === '' )
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'A table name is required for that operation.' );
			}

			return FALSE;
		}

		if ( ( $query = $this->_generateDropTableSQLStatement( $this->_conn->DBPrefix . $table_name, $if_exists ) ) === TRUE )
		{
			return TRUE;
		}

		$query = $this->_conn->query( $query );

		// Update table list cache
		if ( $query && ! empty( $this->_conn->registry[ 'table_names' ] ) )
		{
			$key = array_search(
				strtolower( $this->_conn->DBPrefix . $table_name ),
				array_map( 'strtolower', $this->_conn->registry[ 'table_names' ] ), TRUE );
			if ( $key !== FALSE )
			{
				unset( $this->_conn->registry[ 'table_names' ][ $key ] );
			}
		}

		return $query;
	}

	//--------------------------------------------------------------------

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
	protected function _generateDropTableSQLStatement( $table, $if_exists )
	{
		$sql = 'DROP TABLE';

		if ( $if_exists )
		{
			if ( $this->_sql_drop_table_if_exists_statement === FALSE )
			{
				if ( ! $this->_conn->isTableExists( $table ) )
				{
					return TRUE;
				}
			}
			else
			{
				$sql = sprintf( $this->_sql_drop_table_if_exists_statement, $this->_conn->escapeIdentifiers( $table ) );
			}
		}

		return $sql . ' ' . $this->_conn->escapeIdentifiers( $table );
	}

	//--------------------------------------------------------------------

	/**
	 * Rename Table
	 *
	 * @param    string $table_name     Old table name
	 * @param    string $new_table_name New table name
	 *
	 * @return    bool
	 */
	public function renameTable( $table_name, $new_table_name )
	{
		if ( $table_name === '' OR $new_table_name === '' )
		{
			throw new \InvalidArgumentException( 'A table name is required for that operation.' );
		}
		elseif ( $this->_sql_rename_table_statement === FALSE )
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'This feature is not available for the database you are using.' );
			}

			return FALSE;
		}

		$result = $this->_conn->query(
			sprintf(
				$this->_sql_rename_table_statement,
				$this->_conn->escapeIdentifiers( $this->_conn->DBPrefix . $table_name ),
				$this->_conn->escapeIdentifiers( $this->_conn->DBPrefix . $new_table_name ) )
		);

		if ( $result && ! empty( $this->_conn->registry[ 'table_names' ] ) )
		{
			$key = array_search(
				strtolower( $this->_conn->DBPrefix . $table_name ),
				array_map( 'strtolower', $this->_conn->registry[ 'table_names' ] ), TRUE );
			if ( $key !== FALSE )
			{
				$this->_conn->registry[ 'table_names' ][ $key ] = $this->_conn->DBPrefix . $new_table_name;
			}
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Column Add
	 *
	 * @param    string $table  Table name
	 * @param    array  $column Column definition
	 * @param    string $_after Column for AFTER clause (deprecated)
	 *
	 * @return    bool
	 */
	public function addColumn( $table, $column, $_after = NULL )
	{
		// Work-around for literal column definitions
		is_array( $column ) OR $column = [ $column ];

		foreach ( array_keys( $column ) as $field )
		{
			$this->addField( [ $field => $column[ $field ] ] );
		}

		$sqls = $this->_generateAlterTableSQLStatement( 'ADD', $this->_conn->table_prefix . $table, $this->_prepareTableFields() );
		$this->_reset();
		if ( $sqls === FALSE )
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'This feature is not available for the database you are using.' );
			}

			return FALSE;
		}

		for ( $i = 0, $c = count( $sqls ); $i < $c; $i++ )
		{
			if ( $this->_conn->query( $sqls[ $i ] ) === FALSE )
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Add Field
	 *
	 * @param    array $field
	 *
	 * @return    ForgeInterface
	 */
	public function addField( $field )
	{
		if ( is_string( $field ) )
		{
			if ( $field === 'id' )
			{
				$this->addField(
					[
						'id' => [
							'type'           => 'INT',
							'constraint'     => 9,
							'auto_increment' => TRUE,
						],
					] );
				$this->addKey( 'id', TRUE );
			}
			else
			{
				if ( strpos( $field, ' ' ) === FALSE )
				{
					throw new \InvalidArgumentException( 'Field information is required for that operation.' );
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

	//--------------------------------------------------------------------

	/**
	 * Add Key
	 *
	 * @param    string $key
	 * @param    bool   $primary
	 *
	 * @return    ForgeInterface
	 */
	public function addKey( $key, $primary = FALSE )
	{
		if ( is_array( $key ) )
		{
			foreach ( $key as $one )
			{
				$this->addKey( $one, $primary );
			}

			return $this;
		}

		if ( $primary === TRUE )
		{
			$this->primaryKeys[] = $key;
		}
		else
		{
			$this->keys[] = $key;
		}

		return $this;
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
		$sql = 'ALTER TABLE ' . $this->_conn->escapeIdentifiers( $table ) . ' ';

		// DROP has everything it needs now.
		if ( $alter_type === 'DROP' )
		{
			return $sql . 'DROP COLUMN ' . $this->_conn->escapeIdentifiers( $field );
		}

		$sql .= ( $alter_type === 'ADD' )
			? 'ADD '
			: $alter_type . ' COLUMN ';

		$sqls = [ ];
		for ( $i = 0, $c = count( $field ); $i < $c; $i++ )
		{
			$sqls[] = $sql
				. ( $field[ $i ][ '_literal' ] !== FALSE ? $field[ $i ][ '_literal' ] : $this->_generateFieldSQLStatement( $field[ $i ] ) );
		}

		return $sqls;
	}

	//--------------------------------------------------------------------

	/**
	 * Column Drop
	 *
	 * @param    string $table  Table name
	 * @param    string $column Column name
	 *
	 * @return    bool
	 */
	public function dropColumn( $table, $column )
	{
		$sql = $this->_generateAlterTableSQLStatement( 'DROP', $this->_conn->table_prefix . $table, $column );
		if ( $sql === FALSE )
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'This feature is not available for the database you are using.' );
			}

			return FALSE;
		}

		return $this->_conn->query( $sql );
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------

	/**
	 * Column Modify
	 *
	 * @param    string $table  Table name
	 * @param    string $column Column definition
	 *
	 * @return    bool
	 */
	public function modifyColumn( $table, $column )
	{
		// Work-around for literal column definitions
		is_array( $column ) OR $column = [ $column ];

		foreach ( array_keys( $column ) as $field )
		{
			$this->addField( [ $field => $column[ $field ] ] );
		}

		if ( count( $this->fields ) === 0 )
		{
			throw new \RuntimeException( 'Field information is required' );
		}

		$sqls = $this->_generateAlterTableSQLStatement( 'CHANGE', $this->_conn->table_prefix . $table, $this->_prepareTableFields() );
		$this->_reset();
		if ( $sqls === FALSE )
		{
			if ( $this->_conn->is_debug_mode )
			{
				throw new ForgeException( 'This feature is not available for the database you are using.' );
			}

			return FALSE;
		}

		for ( $i = 0, $c = count( $sqls ); $i < $c; $i++ )
		{
			if ( $this->_conn->query( $sqls[ $i ] ) === FALSE )
			{
				return FALSE;
			}
		}

		return TRUE;
	}
}