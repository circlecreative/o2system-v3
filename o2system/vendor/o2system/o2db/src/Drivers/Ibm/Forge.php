<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 11/10/2015
 * Time: 6:44 PM
 */

namespace O2System\DB\Drivers\Mysql;


class Forge
{
	/**
	 * RENAME TABLE IF statement
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
		'SMALLINT' => 'INTEGER',
		'INT'      => 'BIGINT',
		'INTEGER'  => 'BIGINT',
	);

	/**
	 * DEFAULT value representation in CREATE/ALTER TABLE statements
	 *
	 * @type    string
	 */
	protected $_default = FALSE;

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
			$field[ 'unique' ] = ' UNIQUE';

			// UNIQUE must be used with NOT NULL
			$field[ 'null' ] = ' NOT NULL';
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