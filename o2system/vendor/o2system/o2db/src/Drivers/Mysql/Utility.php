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

namespace O2System\DB\Drivers\Mysql;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Utility as UtilityInterface;

/**
 * PDO MySQL Utility Class
 *
 * Based on CodeIgniter PDO MySQL Utility Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Utility extends UtilityInterface
{
	/**
	 * List databases statement
	 *
	 * @type    string
	 */
	protected $_list_databases = 'SHOW DATABASES';

	/**
	 * OPTIMIZE TABLE statement
	 *
	 * @type    string
	 */
	protected $_optimize_table = 'OPTIMIZE TABLE %s';

	/**
	 * REPAIR TABLE statement
	 *
	 * @type    string
	 */
	protected $_repair_table = 'REPAIR TABLE %s';

	// --------------------------------------------------------------------

	/**
	 * Export
	 *
	 * @param    array $params Preferences
	 *
	 * @return    mixed
	 */
	protected function _backup( $params = array() )
	{
		if ( count( $params ) === 0 )
		{
			return FALSE;
		}

		// Extract the prefs for simplicity
		extract( $params );

		// Build the output
		$output = '';

		// Do we need to include a statement to disable foreign key checks?
		if ( $foreign_key_checks === FALSE )
		{
			$output .= 'SET foreign_key_checks = 0;' . $newline;
		}

		foreach ( (array) $tables as $table )
		{
			// Is the table in the "ignore" list?
			if ( in_array( $table, (array) $ignore, TRUE ) )
			{
				continue;
			}

			// Get the table schema
			$query = $this->_driver->query( 'SHOW CREATE TABLE ' . $this->_driver->escape_identifiers( $this->_driver->database . '.' . $table ) );

			// No result means the table name was invalid
			if ( $query === FALSE )
			{
				continue;
			}

			// Write out the table schema
			$output .= '#' . $newline . '# TABLE STRUCTURE FOR: ' . $table . $newline . '#' . $newline . $newline;

			if ( $add_drop === TRUE )
			{
				$output .= 'DROP TABLE IF EXISTS ' . $this->_driver->protect_identifiers( $table ) . ';' . $newline . $newline;
			}

			$i = 0;
			$result = $query->result_array();
			foreach ( $result[ 0 ] as $value )
			{
				if ( $i++ % 2 )
				{
					$output .= $value . ';' . $newline . $newline;
				}
			}

			// If inserts are not needed we're done...
			if ( $add_insert === FALSE )
			{
				continue;
			}

			// Grab all the data from the current table
			$query = $this->_driver->query( 'SELECT * FROM ' . $this->_driver->protect_identifiers( $table ) );

			if ( $query->num_rows() === 0 )
			{
				continue;
			}

			// Fetch the field names and determine if the field is an
			// integer type. We use this info to decide whether to
			// surround the data with quotes or not

			$i = 0;
			$field_str = '';
			$is_int = array();
			while ( $field = $query->result_id->fetch_field() )
			{
				// Most versions of MySQL store timestamp as a string
				$is_int[ $i ] = in_array( strtolower( $field->type ),
				                          array( 'tinyint', 'smallint', 'mediumint', 'int', 'bigint' ), //, 'timestamp'),
				                          TRUE );

				// Create a string of field names
				$field_str .= $this->_driver->escape_identifiers( $field->name ) . ', ';
				$i++;
			}

			// Trim off the end comma
			$field_str = preg_replace( '/, $/', '', $field_str );

			// Build the insert string
			foreach ( $query->result_array() as $row )
			{
				$val_str = '';

				$i = 0;
				foreach ( $row as $v )
				{
					// Is the value NULL?
					if ( $v === NULL )
					{
						$val_str .= 'NULL';
					}
					else
					{
						// Escape the data if it's not an integer
						$val_str .= ( $is_int[ $i ] === FALSE ) ? $this->_driver->escape( $v ) : $v;
					}

					// Append a comma
					$val_str .= ', ';
					$i++;
				}

				// Remove the comma at the end of the string
				$val_str = preg_replace( '/, $/', '', $val_str );

				// Build the INSERT string
				$output .= 'INSERT INTO ' . $this->_driver->protect_identifiers( $table ) . ' (' . $field_str . ') VALUES (' . $val_str . ');' . $newline;
			}

			$output .= $newline . $newline;
		}

		// Do we need to include a statement to re-enable foreign key checks?
		if ( $foreign_key_checks === FALSE )
		{
			$output .= 'SET foreign_key_checks = 1;' . $newline;
		}

		return $output;
	}
}