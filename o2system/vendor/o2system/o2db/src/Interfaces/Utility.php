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
use O2System\DB\Factory\Row;

/**
 * Database Utility Class
 *
 * Based on CodeIgniter Database Utility Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
abstract class Utility
{
	/**
	 * Database object
	 *
	 * @type    object
	 */
	protected $_driver;

	// --------------------------------------------------------------------

	/**
	 * List databases statement
	 *
	 * @type    string
	 */
	protected $_list_databases = FALSE;

	/**
	 * OPTIMIZE TABLE statement
	 *
	 * @type    string
	 */
	protected $_optimize_table = FALSE;

	/**
	 * REPAIR TABLE statement
	 *
	 * @type    string
	 */
	protected $_repair_table = FALSE;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param    object &$db Database object
	 *
	 * @return    void
	 */
	public function __construct( $driver )
	{
		$this->_driver = clone $driver;
		$this->_driver->row_class_name = '\O2System\DB\Factory\Row';
		$this->_driver->row_class_args = NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * List databases
	 *
	 * @return    array
	 */
	public function list_databases()
	{
		// Is there a cached result?
		if ( isset( $this->_driver->data_cache[ 'db_names' ] ) )
		{
			return $this->_driver->data_cache[ 'db_names' ];
		}
		elseif ( $this->_list_databases === FALSE )
		{
			return FALSE;
		}

		$this->_driver->data_cache[ 'db_names' ] = array();

		$results = $this->_driver->query( $this->_list_databases );

		if ( $results->num_rows() > 0 )
		{
			foreach ( $results as $row )
			{
				if ( ! isset( $key ) )
				{
					if ( isset( $row[ 'Database' ] ) )
					{
						$key = 'Database';
					}
					else
					{
						$key = key( $row );
					}
				}

				$this->_driver->data_cache[ 'db_names' ][] = $row[ $key ];
			}
		}

		return $this->_driver->data_cache[ 'db_names' ];
	}

	// --------------------------------------------------------------------

	/**
	 * Determine if a particular database exists
	 *
	 * @param    string $database_name
	 *
	 * @return    bool
	 */
	public function database_exists( $database_name )
	{
		return in_array( $database_name, $this->list_databases() );
	}

	// --------------------------------------------------------------------

	/**
	 * Optimize Table
	 *
	 * @param    string $table_name
	 *
	 * @return    mixed
	 */
	public function optimize_table( $table_name )
	{
		if ( $this->_optimize_table === FALSE )
		{
			return FALSE;
		}

		$results = $this->_driver->query( sprintf( $this->_optimize_table, $this->_driver->escape_identifiers( $table_name ) ) );

		if($results->num_rows() > 0)
		{
			return $results;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Optimize Database
	 *
	 * @return    mixed
	 */
	public function optimize_database()
	{
		if ( $this->_optimize_table === FALSE )
		{
			return FALSE;
		}

		$result = array();
		foreach ( $this->_driver->list_tables() as $table_name )
		{
			$results = $this->_driver->query( sprintf( $this->_optimize_table, $this->_driver->escape_identifiers( $table_name ) ) );

			if($results->num_rows() > 0)
			{
				$result[ $table_name ] = $results;
			}
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Repair Table
	 *
	 * @param    string $table_name
	 *
	 * @return    mixed
	 */
	public function repair_table( $table_name )
	{
		if ( $this->_repair_table === FALSE )
		{
			return FALSE;
		}

		$results = $this->_driver->query( sprintf( $this->_repair_table, $this->_driver->escape_identifiers( $table_name ) ) );

		if($results->num_rows() > 0)
		{
			return $results;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate CSV from a query result object
	 *
	 * @param    object $query     Query result object
	 * @param    string $delim     Delimiter (default: ,)
	 * @param    string $newline   Newline character (default: \n)
	 * @param    string $enclosure Enclosure (default: ")
	 *
	 * @return    string
	 */
	public function csv_from_result( $query, $delim = ',', $newline = "\n", $enclosure = '"' )
	{
		if ( ! is_object( $query ) OR ! method_exists( $query, 'list_fields' ) )
		{
			throw new Exception( 'You must submit a valid result object' );
		}

		$out = '';
		// First generate the headings from the table column names
		foreach ( $query->list_fields() as $name )
		{
			$out .= $enclosure . str_replace( $enclosure, $enclosure . $enclosure, $name ) . $enclosure . $delim;
		}

		$out = substr( rtrim( $out ), 0, -strlen( $delim ) ) . $newline;

		// Next blast through the result array and build out the rows
		while ( $row = $query->unbuffered_row( 'array' ) )
		{
			foreach ( $row as $item )
			{
				$out .= $enclosure . str_replace( $enclosure, $enclosure . $enclosure, $item ) . $enclosure . $delim;
			}
			$out = substr( rtrim( $out ), 0, -strlen( $delim ) ) . $newline;
		}

		return $out;
	}

	// --------------------------------------------------------------------

	/**
	 * Database Backup
	 *
	 * @param    array $params
	 *
	 * @return    string
	 */
	public function backup( $params = array() )
	{
		// If the parameters have not been submitted as an
		// array then we know that it is simply the table
		// name, which is a valid short cut.
		if ( is_string( $params ) )
		{
			$params = array( 'tables' => $params );
		}

		// Set up our default preferences
		$prefs = array(
			'tables'             => array(),
			'ignore'             => array(),
			'filename'           => '',
			'format'             => 'gzip', // gzip, zip, txt
			'add_drop'           => TRUE,
			'add_insert'         => TRUE,
			'newline'            => "\n",
			'foreign_key_checks' => TRUE,
		);

		// Did the user submit any preferences? If so set them....
		if ( count( $params ) > 0 )
		{
			foreach ( $prefs as $key => $val )
			{
				if ( isset( $params[ $key ] ) )
				{
					$prefs[ $key ] = $params[ $key ];
				}
			}
		}

		// Are we backing up a complete database or individual tables?
		// If no table names were submitted we'll fetch the entire table list
		if ( count( $prefs[ 'tables' ] ) === 0 )
		{
			$prefs[ 'tables' ] = $this->_driver->list_tables();
		}

		// Validate the format
		if ( ! in_array( $prefs[ 'format' ], array( 'gzip', 'zip', 'txt' ), TRUE ) )
		{
			$prefs[ 'format' ] = 'txt';
		}

		// Is the encoder supported? If not, we'll either issue an
		// error or use plain text depending on the debug settings
		if ( ( $prefs[ 'format' ] === 'gzip' && ! function_exists( 'gzencode' ) )
			OR ( $prefs[ 'format' ] === 'zip' && ! function_exists( 'gzcompress' ) )
		)
		{
			if ( $this->_driver->debug_enabled )
			{
				throw new Exception( 'The file compression format you chose is not supported by your server.' );
			}

			$prefs[ 'format' ] = 'txt';
		}

		// Was a Zip file requested?
		if ( $prefs[ 'format' ] === 'zip' )
		{
			// Set the filename if not provided (only needed with Zip files)
			if ( $prefs[ 'filename' ] === '' )
			{
				$prefs[ 'filename' ] = ( count( $prefs[ 'tables' ] ) === 1 ? $prefs[ 'tables' ] : $this->_driver->database )
					. date( 'Y-m-d_H-i', time() ) . '.sql';
			}
			else
			{
				// If they included the .zip file extension we'll remove it
				if ( preg_match( '|.+?\.zip$|', $prefs[ 'filename' ] ) )
				{
					$prefs[ 'filename' ] = str_replace( '.zip', '', $prefs[ 'filename' ] );
				}

				// Tack on the ".sql" file extension if needed
				if ( ! preg_match( '|.+?\.sql$|', $prefs[ 'filename' ] ) )
				{
					$prefs[ 'filename' ] .= '.sql';
				}
			}

			// Load the Zip class and output it
			$zip = new \O2System\File\Factory\Zip();

			$zip->add_data( $prefs[ 'filename' ], $this->_backup( $prefs ) );

			return $zip->get_zip();
		}
		elseif ( $prefs[ 'format' ] === 'txt' ) // Was a text file requested?
		{
			return $this->_backup( $prefs );
		}
		elseif ( $prefs[ 'format' ] === 'gzip' ) // Was a Gzip file requested?
		{
			return gzencode( $this->_backup( $prefs ) );
		}

		return;
	}
}