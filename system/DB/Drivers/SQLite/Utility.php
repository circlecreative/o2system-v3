<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 26-Jul-16
 * Time: 8:59 AM
 */

namespace O2System\DB\Drivers\SQLite;


use O2System\DB\Interfaces\UtilityInterface;

class Utility extends UtilityInterface
{

	/**
	 * Platform dependent version of the backup function.
	 *
	 * @param array|null $preferences
	 *
	 * @return mixed
	 */
	public function _backup( array $preferences = [ ] )
	{
		if ( count( $preferences ) === 0 )
		{
			return FALSE;
		}

		// Extract the prefs for simplicity
		extract( $preferences );

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
			$query = $this->db->query( 'SHOW CREATE TABLE ' . $this->db->escapeIdentifiers( $this->db->database . '.' . $table ) );

			// No result means the table name was invalid
			if ( $query === FALSE )
			{
				continue;
			}

			// Write out the table schema
			$output .= '#' . $newline . '# TABLE STRUCTURE FOR: ' . $table . $newline . '#' . $newline . $newline;

			if ( $add_drop === TRUE )
			{
				$output .= 'DROP TABLE IF EXISTS ' . $this->_driver->protectIdentifiers( $table ) . ';' . $newline . $newline;
			}

			$i      = 0;
			$result = $query->resultArray();
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
			$result = $this->db->query( 'SELECT * FROM ' . $this->db->protectIdentifiers( $table ) );

			if ( $query->numRows() === 0 )
			{
				continue;
			}

			// Fetch the field names and determine if the field is an
			// integer type. We use this info to decide whether to
			// surround the data with quotes or not

			$i         = 0;
			$field_str = '';
			$is_int    = [ ];
			while ( $field = $query->result_id->fetchField() )
			{
				// Most versions of SQLite store timestamp as a string
				$is_int[ $i ] = in_array(
					strtolower( $field->type ),
					[ 'tinyint', 'smallint', 'mediumint', 'int', 'bigint' ], //, 'timestamp'),
					TRUE );

				// Create a string of field names
				$field_str .= $this->db->escapeIdentifiers( $field->name ) . ', ';
				$i++;
			}

			// Trim off the end comma
			$field_str = preg_replace( '/, $/', '', $field_str );

			// Build the insert string
			foreach ( $result as $row )
			{
				$value_statement = '';

				$i = 0;
				foreach ( $row as $value )
				{
					// Is the value NULL?
					if ( $value === NULL )
					{
						$value_statement .= 'NULL';
					}
					else
					{
						// Escape the data if it's not an integer
						$value_statement .= ( $is_int[ $i ] === FALSE ) ? $this->db->escape( $value ) : $value;
					}

					// Append a comma
					$value_statement .= ', ';
					$i++;
				}

				// Remove the comma at the end of the string
				$value_statement = preg_replace( '/, $/', '', $value_statement );

				// Build the INSERT string
				$output .= 'INSERT INTO ' . $this->db->protectIdentifiers( $table ) . ' (' . $field_str . ') VALUES (' . $value_statement . ');' . $newline;
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