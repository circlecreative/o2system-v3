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


use O2System\DB\UtilityException;

abstract class UtilityInterface
{
	/**
	 * Database object
	 *
	 * @var    object
	 */
	protected $db;

	//--------------------------------------------------------------------

	/**
	 * List databases statement
	 *
	 * @var    string
	 */
	protected $listDatabases = FALSE;

	/**
	 * OPTIMIZE TABLE statement
	 *
	 * @var    string
	 */
	protected $optimizeTable = FALSE;

	/**
	 * REPAIR TABLE statement
	 *
	 * @var    string
	 */
	protected $repairTable = FALSE;

	//--------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param ConnectionInterface|object $db
	 */
	public function __construct( ConnectionInterface &$db )
	{
		$this->db =& $db;
	}

	//--------------------------------------------------------------------

	/**
	 * Determine if a particular database exists
	 *
	 * @param    string $database
	 *
	 * @return    bool
	 */
	public function isDatabaseExists( $database )
	{
		return in_array( $database, $this->getDatabaseLists() );
	}

	//--------------------------------------------------------------------

	/**
	 * List databases
	 *
	 * @return    array
	 */
	public function getDatabaseLists()
	{
		// Is there a cached result?
		if ( isset( $this->db->registry[ 'database' ] ) )
		{
			return $this->db->registry[ 'database' ];
		}
		elseif ( $this->listDatabases === FALSE )
		{
			if ( $this->db->is_debug_mode )
			{
				throw new UtilityException( 'Unsupported feature of the database platform you are using.' );
			}

			return FALSE;
		}

		$this->db->registry[ 'database' ] = [ ];

		$query = $this->db->query( $this->listDatabases );
		if ( $query === FALSE )
		{
			return $this->db->registry[ 'database' ];
		}

		for ( $i = 0, $query = $query->getResultArray(), $c = count( $query ); $i < $c; $i++ )
		{
			$this->db->registry[ 'database' ][] = current( $query[ $i ] );
		}

		return $this->db->registry[ 'database' ];
	}

	//--------------------------------------------------------------------

	/**
	 * Optimize Table
	 *
	 * @param    string $table_name
	 *
	 * @return    mixed
	 */
	public function optimizeTable( $table_name )
	{
		if ( $this->optimizeTable === FALSE )
		{
			if ( $this->db->DBDebug )
			{
				throw new UtilityException( 'Unsupported feature of the database platform you are using.' );
			}

			return FALSE;
		}

		$query = $this->db->query( sprintf( $this->optimizeTable, $this->db->escapeIdentifiers( $table_name ) ) );
		if ( $query !== FALSE )
		{
			$query = $query->getResultArray();

			return current( $query );
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Optimize Database
	 *
	 * @return    mixed
	 */
	public function optimizeDatabase()
	{
		if ( $this->optimizeTable === FALSE )
		{
			if ( $this->db->DBDebug )
			{
				throw new UtilityException( 'Unsupported feature of the database platform you are using.' );
			}

			return FALSE;
		}

		$result = [ ];
		foreach ( $this->db->listTables() as $table_name )
		{
			$res = $this->db->query( sprintf( $this->optimizeTable, $this->db->escapeIdentifiers( $table_name ) ) );
			if ( is_bool( $res ) )
			{
				return $res;
			}

			// Build the result array...
			$res  = $res->getResultArray();
			$res  = current( $res );
			$key  = str_replace( $this->db->database . '.', '', current( $res ) );
			$keys = array_keys( $res );
			unset( $res[ $keys[ 0 ] ] );

			$result[ $key ] = $res;
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Repair Table
	 *
	 * @param    string $table_name
	 *
	 * @return    mixed
	 */
	public function repairTable( $table_name )
	{
		if ( $this->repairTable === FALSE )
		{
			if ( $this->db->DBDebug )
			{
				throw new UtilityException( 'Unsupported feature of the database platform you are using.' );
			}

			return FALSE;
		}

		$query = $this->db->query( sprintf( $this->repairTable, $this->db->escapeIdentifiers( $table_name ) ) );
		if ( is_bool( $query ) )
		{
			return $query;
		}

		$query = $query->getResultArray();

		return current( $query );
	}

	//--------------------------------------------------------------------

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
	public function getCSVFromResult( ResultInterface $query, $delim = ',', $newline = "\n", $enclosure = '"' )
	{
		$out = '';
		// First generate the headings from the table column names
		foreach ( $query->getFieldNames() as $name )
		{
			$out .= $enclosure . str_replace( $enclosure, $enclosure . $enclosure, $name ) . $enclosure . $delim;
		}

		$out = substr( $out, 0, -strlen( $delim ) ) . $newline;

		// Next blast through the result array and build out the rows
		while ( $row = $query->getUnbufferedRow( 'array' ) )
		{
			$line = [ ];
			foreach ( $row as $item )
			{
				$line[] = $enclosure . str_replace( $enclosure, $enclosure . $enclosure, $item ) . $enclosure;
			}
			$out .= implode( $delim, $line ) . $newline;
		}

		return $out;
	}

	//--------------------------------------------------------------------

	/**
	 * Generate XML data from a query result object
	 *
	 * @param    object $query  Query result object
	 * @param    array  $params Any preferences
	 *
	 * @return    string
	 */
	public function getXMLFromResult( ResultInterface $query, $params = [ ] )
	{
		// Set our default values
		foreach ( [ 'root' => 'root', 'element' => 'element', 'newline' => "\n", 'tab' => "\t" ] as $key => $val )
		{
			if ( ! isset( $params[ $key ] ) )
			{
				$params[ $key ] = $val;
			}
		}

		// Create variables for convenience
		extract( $params );

		// Load the xml helper
//		get_instance()->load->helper('xml');

		// Generate the result
		$xml = '<' . $root . '>' . $newline;
		while ( $row = $query->getUnbufferedRow() )
		{
			$xml .= $tab . '<' . $element . '>' . $newline;
			foreach ( $row as $key => $val )
			{
				$xml .= $tab . $tab . '<' . $key . '>' . xml_convert( $val ) . '</' . $key . '>' . $newline;
			}
			$xml .= $tab . '</' . $element . '>' . $newline;
		}

		return $xml . '</' . $root . '>' . $newline;
	}

	//--------------------------------------------------------------------

	/**
	 * Database Backup
	 *
	 * @param    array $params
	 *
	 * @return    string
	 */
	public function backup( $params = [ ] )
	{
		// If the parameters have not been submitted as an
		// array then we know that it is simply the table
		// name, which is a valid short cut.
		if ( is_string( $params ) )
		{
			$params = [ 'tables' => $params ];
		}

		// Set up our default preferences
		$prefs = [
			'tables'             => [ ],
			'ignore'             => [ ],
			'filename'           => '',
			'format'             => 'gzip', // gzip, zip, txt
			'add_drop'           => TRUE,
			'add_insert'         => TRUE,
			'newline'            => "\n",
			'foreign_key_checks' => TRUE,
		];

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
			$prefs[ 'tables' ] = $this->db->listTables();
		}

		// Validate the format
		if ( ! in_array( $prefs[ 'format' ], [ 'gzip', 'zip', 'txt' ], TRUE ) )
		{
			$prefs[ 'format' ] = 'txt';
		}

		// Is the encoder supported? If not, we'll either issue an
		// error or use plain text depending on the debug settings
		if ( ( $prefs[ 'format' ] === 'gzip' && ! function_exists( 'gzencode' ) )
			OR ( $prefs[ 'format' ] === 'zip' && ! function_exists( 'gzcompress' ) )
		)
		{
			if ( $this->db->DBDebug )
			{
				throw new UtilityException( 'The file compression format you chose is not supported by your server.' );
			}

			$prefs[ 'format' ] = 'txt';
		}

		// Was a Zip file requested?
		if ( $prefs[ 'format' ] === 'zip' )
		{
			// Set the filename if not provided (only needed with Zip files)
			if ( $prefs[ 'filename' ] === '' )
			{
				$prefs[ 'filename' ] = ( count( $prefs[ 'tables' ] ) === 1 ? $prefs[ 'tables' ] : $this->db->database )
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
//			$CI =& get_instance();
//			$CI->load->library('zip');
//			$CI->zip->add_data($prefs['filename'], $this->_backup($prefs));
//			return $CI->zip->get_zip();
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

	//--------------------------------------------------------------------

	/**
	 * Platform dependent version of the backup function.
	 *
	 * @param array|null $preferences
	 *
	 * @return mixed
	 */
	abstract public function _backup( array $preferences = NULL );

	//--------------------------------------------------------------------
}