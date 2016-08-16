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

use O2System\DB\Config;

abstract class ConfigInterface
{
	/**
	 * @var Config
	 */
	protected static $config;

	/**
	 * New Line String
	 *
	 * @access  protected
	 * @type    string
	 */
	protected static $newLineString = PHP_EOL;

	/**
	 * Active Record Where Clauses Group Keys
	 *
	 * @access  protected
	 * @type    array
	 */
	protected static $activeRecordWhereClausesKeys = [
		'WHERE'          => 'WHERE',
		'OR'             => 'OR_WHERE',
		'IN'             => 'WHERE',
		'OR_IN'          => 'OR_WHERE',
		'NOT_IN'         => 'WHERE',
		'OR_NOT_IN'      => 'OR_WHERE',
		'BETWEEN'        => 'WHERE',
		'OR_BETWEEN'     => 'OR_WHERE',
		'NOT_BETWEEN'    => 'WHERE',
		'OR_NOT_BETWEEN' => 'OR_WHERE',
	];

	/**
	 * SQL Statements Operators
	 *
	 * @access  protected
	 * @type    array
	 */
	protected static $sqlOperators = [
		'EQUAL'         => '=',
		'NOT'           => '!=',
		'GREATER'       => '>',
		'LESS'          => '<',
		'GREATER_EQUAL' => '>=',
		'LESS_EQUAL'    => '<=',
	];

	/**
	 * SQL Aggregate Functions
	 *
	 * SQL aggregate functions return a single value, calculated from values in a column.
	 *
	 * @access  protected
	 * @type array
	 */
	protected static $sqlAggregateFunctions = [
		'AVG'   => 'AVG(%s)', // Returns the average value
		'COUNT' => 'COUNT(%s)', // Returns the number of rows
		'FIRST' => 'FIRST(%s)', // Returns the first value
		'LAST'  => 'LAST(%s)', // Returns the largest value
		'MAX'   => 'MAX(%s)', // Returns the largest value
		'MIN'   => 'MIN(%s)', // Returns the smallest value
		'SUM'   => 'SUM(%s)' // Returns the sum
	];

	/**
	 * SQL Scalar functions
	 *
	 * SQL scalar functions return a single value, based on the input value.
	 *
	 * @access  protected
	 * @type array
	 */
	protected static $sqlScalarFunctions = [
		'UCASE'  => 'UCASE(%s) AS %s', // Converts a field to uppercase
		'LCASE'  => 'LCASE(%s) AS %s', // Converts a field to lowercase
		'MID'    => 'MID(%s) AS %s', // Extract characters from a text field
		'LEN'    => 'LEN(%s) AS %s', // Returns the length of a text field
		'ROUND'  => 'ROUND(%s) AS %s', // Rounds a numeric field to the number of decimals specified
		'FORMAT' => 'FORMAT(%s, %s) AS %s' // Formats how a field is to be displayed
	];

	/**
	 * SQL Date Functions
	 *
	 * SQL aggregate functions return a single value, calculated from values in a column.
	 *
	 * @access  protected
	 * @type array
	 */
	protected static $sqlDateFunctions = [
		'NOW'          => 'NOW()', // Returns the current date and time
		'CURRENT_DATE' => 'CURDATE()', // Returns the current date
		'CURRENT_TIME' => 'CURTIME()', // 	Returns the current time
		'DATE'         => 'DATE(%s)', // Extracts the date part of a date or date/time expression
		'DATE_EXTRACT' => 'EXTRACT(%s FROM %s)', // Returns a single part of a date/time
		'DATE_ADD'     => 'DATE_ADD(%s, INTERVAL %s)', // Adds a specified time interval to a date
		'DATE_SUB'     => 'DATE_SUB(%s, INTERVAL %s)', // Subtracts a specified time interval from a date
		'DATE_DIFF'    => 'DATEDIFF(%s, %s)', // 	Returns the number of days between two dates
		'DATE_FORMAT'  => 'DATE_FORMAT(%s, %s)' // Displays date/time data in different formats
	];

	/**
	 * SQL DATE value types
	 *
	 * @type array
	 */
	protected static $sqlDateTypes = [
		'MICROSECOND',
		'SECOND',
		'MINUTE',
		'HOUR',
		'DAY',
		'WEEK',
		'MONTH',
		'QUARTER',
		'YEAR',
		'SECOND_MICROSECOND',
		'MINUTE_MICROSECOND',
		'MINUTE_SECOND',
		'HOUR_MICROSECOND',
		'HOUR_SECOND',
		'HOUR_MINUTE',
		'DAY_MICROSECOND',
		'DAY_SECOND',
		'DAY_MINUTE',
		'DAY_HOUR',
		'YEAR_MONTH',
	];

	/**
	 * SQL Statements Generator Sequence
	 *
	 * @access  protected
	 * @type array
	 */
	protected static $sqlStatementsSequence = [
		'SELECT',
		'INTO',
		'FROM',
		'JOIN',
		'LIKE',
		'WHERE',
		'GROUP_BY',
		'HAVING',
		'ORDER_BY',
		'UNION',
		'LIMIT',
	];

	/**
	 * SQL Statements
	 *
	 * @access  protected
	 * @type array
	 */
	protected static $sqlStatements = [
		'SELECT'            => 'SELECT %s',
		'SELECT_AS'         => '%s AS %s',
		'SELECT_DISTINCT'   => 'SELECT DISTINCT %s',
		'SELECT_AGGREGATE'  => '%s AS %s',
		'UNION'             => 'UNION',
		'UNION_ALL'         => 'UNION ALL',
		'INTO'              => 'INTO %s',
		'INTO_IN'           => 'INTO %s IN %s',
		'FROM'              => 'FROM %s',
		'FROM_AS'           => '%s AS %s',
		'JOIN'              => '%s',
		'WHERE'             => 'WHERE %s',
		'INSERT'            => 'INSERT INTO %s(%s) VALUES(%s)',
		'INSERT_BATCH'      => 'INSERT INTO %s(%s) VALUES%s;',
		'INSERT_INTO'       => 'INSERT INTO %s(%s) %s',
		'UPDATE'            => 'UPDATE %s SET %s WHERE %s',
		'UPDATE_BATCH'      => "UPDATE %s SET %s \r\nWHERE %s IN(%s)",
		'UPDATE_BATCH_CASE' => "\r\n%s = CASE \r\n%s \r\n ELSE %s END", // field = CASE case_field [WHEN %s THEN %s] END
		'UPDATE_BATCH_WHEN' => '  WHEN %s = %s THEN %s',
		'DELETE'            => 'DELETE FROM %s WHERE %s',
		'REPLACE'           => 'REPLACE INTO %s(%s) VALUES(%s)',
		'REPLACE_BATCH'     => 'REPLACE INTO %s(%s) VALUES%s;',
		'LIMIT'             => 'LIMIT %s',
		'LIMIT_OFFSET'      => 'LIMIT %s,%s',
		'GROUP_BY'          => 'GROUP BY %s',
		'HAVING'            => 'HAVING %s',
		'ORDER_BY'          => 'ORDER BY %s',
	];

	/**
	 * SQL Join Clauses Statements
	 *
	 * @access  protected
	 * @type    array
	 */
	protected static $sqlJoinStatements = [
		'JOIN'        => 'JOIN %s ON %s = %s',
		'LEFT'        => 'LEFT JOIN %s ON %s = %s',
		'LEFT_OUTER'  => 'LEFT OUTER JOIN  %s ON %s = %s',
		'RIGHT'       => 'RIGHT JOIN %s ON %s = %s',
		'RIGHT_OUTER' => 'RIGHT JOIN %s ON %s = %s',
		'INNER'       => 'INNER JOIN %s ON %s = %s',
		'OUTER'       => 'OUTER JOIN %s ON %s = %s',
		'FULL'        => 'FULL OUTER JOIN %s ON %s = %s',
	];

	/**
	 * SQL Join Clauses Statements
	 *
	 * @access  protected
	 * @type    array
	 */
	protected static $sqlLikeStatements = [
		'LIKE'                    => '%s LIKE %s',
		'LIKE_ESCAPE'             => '%s LIKE %s ESCAPE \'%s\'', // table LIKE %match% ESCAPE '(like character)'
		'LIKE_INSENSITIVE'        => '%s LOWER(%s) LIKE %s',
		'LIKE_INSENSITIVE_ESCAPE' => '%s LOWER(%s) LIKE %s ESCAPE \'%s\'', // table LIKE %match% ESCAPE '(like character)'
	];

	/**
	 * SQL Where Clauses Statements
	 *
	 * @access  protected
	 * @type    array
	 */
	protected static $sqlWhereStatements = [
		'WHERE'          => '%s %s',
		'OR'             => '%s %s',
		'IN'             => '%s IN (%s)',
		'OR_IN'          => '%s IN (%s)',
		'NOT_IN'         => '%s NOT IN (%s)',
		'OR_NOT_IN'      => '%s NOT IN (%s)',
		'BETWEEN'        => '%s BETWEEN %s',
		'OR_BETWEEN'     => '%s BETWEEN %s',
		'NOT_BETWEEN'    => '%s NOT BETWEEN %s',
		'OR_NOT_BETWEEN' => '%s NOT BETWEEN %s',
		'WHEN_THEN'      => 'WHEN %s = %s THEN %s',
		'CASE_ELSE'      => '%s = CASE %s ELSE %s END',
	];

	protected static $sqlForgeStatements = [
		'CREATE_DATABASE'            => 'CREATE DATABASE %s',
		'DROP_DATABASE'              => 'DROP DATABASE %s',
		'CREATE_TABLE'               => "%s %s (%s\n)",
		'CREATE_TABLE_IF_NOT_EXISTS' => 'CREATE TABLE IF NOT EXISTS',
		'DROP_TABLE_IF_EXISTS'       => 'DROP TABLE IF EXISTS',
		'RENAME_TABLE'               => 'ALTER TABLE %s RENAME TO %s;',
	];

	protected static $sqlUtiliyStatements = [
		'SHOW_DATABASES' => 'SHOW DATABASES',
		'SHOW_TABLES'    => 'SHOW TABLES FROM %s',
		'SHOW_COLUMNS'   => 'SHOW COLUMNS FROM %s',
	];

	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @var    string[]
	 */
	protected static $reservedIdentifiers = [ '*' ];

	/**
	 * Identifier escape character
	 *
	 * @var    string
	 */
	protected static $escapeCharacter = '"';

	/**
	 * ESCAPE LIKE string
	 *
	 * @var    string
	 */
	protected static $escapeLikeString = '!';

	/**
	 * ESCAPE LIKE character
	 *
	 * @var    string
	 */
	protected static $escapeLikeCharacter = '!';

	/**
	 * ORDER BY directions
	 *
	 * @var    array
	 */
	protected static $orderByDirections = [ 'ASC', 'DESC' ];

	/**
	 * ORDER BY random keyword
	 *
	 * @var    array
	 */
	protected static $orderByRandomKeywords = [ 'RANDOM()' ];

	/**
	 * Protect identifiers flag
	 *
	 * @var    bool
	 */
	protected static $isProtectIdentifiers = TRUE;

	/**
	 * Persistent connection flag
	 *
	 * @var bool
	 */
	protected $_is_persistent = TRUE;

	/**
	 * Debug mode flag
	 *
	 * @var bool
	 */
	protected $_is_debug_mode = TRUE;

	/**
	 * Connection Database
	 *
	 * @var    string
	 */
	protected $_database;

	/**
	 * Table Prefix
	 *
	 * @var string
	 */
	protected $_table_prefix = NULL;

	/**
	 * Swap Table Prefix
	 *
	 * @var string
	 */
	protected $_table_swap_prefix = NULL;

	/**
	 * Array value conversion method JSON|SERIALIZE
	 *
	 * @var string
	 */
	protected $_array_value_conversion_method = 'JSON';

	public function setConfig( Config $config )
	{
		static::$config = $config;

		return $this;
	}

	/**
	 * Set Table Prefix
	 *
	 * @param   string $prefix
	 */
	public function setTablePrefix( $prefix )
	{
		$this->_table_prefix = $prefix;
	}

	/**
	 * Set ArrayObject Value Conversion Method
	 *
	 * @param $method
	 *
	 * @return $this
	 */
	public function setArrayValueConversionMethod( $method )
	{
		$method = strtoupper( $method );

		if ( in_array( $method, [ 'JSON', 'SERIALIZE' ] ) )
		{
			$this->_array_value_conversion_method = $method;
		}
	}

	/**
	 * Protect Identifiers
	 *
	 * This function is used extensively by the Query Builder class, and by
	 * a couple functions in this class.
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it. Some logic is necessary in order to deal with
	 * column names that include the path. Consider a query like this:
	 *
	 * SELECT hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @param    string|array
	 * @param    bool
	 * @param    mixed
	 * @param    bool
	 *
	 * @return    string
	 */
	protected function _protectIdentifiers( $field, $prefix = TRUE, $operator = NULL )
	{
		$x_field = explode( ' ', $field );

		if ( count( $x_field ) == 2 )
		{
			$field    = reset( $x_field );
			$operator = end( $x_field );
		}

		if ( strpos( $field, ':' ) !== FALSE )
		{
			$x_fields = explode( ':', $field );

			foreach ( $x_fields as $key => $value )
			{
				if ( array_key_exists( strtoupper( $value ), static::$sqlOperators ) AND $operator !== FALSE )
				{
					$operator = static::$sqlOperators[ strtoupper( $value ) ];
					array_shift( $x_fields );
				}
				elseif ( array_key_exists( strtoupper( $value ), static::$sqlAggregateFunctions ) )
				{
					$aggregate = static::$sqlAggregateFunctions[ strtoupper( $value ) ];
				}
				elseif ( array_key_exists( strtoupper( $value ), static::$sqlScalarFunctions ) )
				{
					$scalar = static::$sqlScalarFunctions[ strtoupper( $value ) ];
				}
				else
				{
					$field = $value;
				}
			}
		}

		if ( strpos( $field, '.' ) !== FALSE )
		{
			$x_strings      = explode( '.', $field );
			$x_strings[ 0 ] = $prefix === TRUE && ! empty( $this->_table_prefix ) ? $this->_table_prefix . $x_strings[ 0 ] : $x_strings[ 0 ];

			$x_strings = array_map( [ &$this, 'escapeIdentifiers' ], $x_strings );

			// Collects Tables
			$table = $this->escapeIdentifiers( $x_strings[ 0 ] );

			if ( isset( $this->activeRecords ) )
			{
				if ( isset( $this->activeRecords->from[ $table ] ) )
				{
					$x_strings[ 0 ] = $this->activeRecords->from[ $table ];
				}
				else
				{
					$this->activeRecords->from[ $table ] = NULL;
				}
			}

			$field = implode( '.', $x_strings );
		}
		else
		{
			$field = $prefix === TRUE && ! empty( $this->_table_prefix ) ? $this->escapeIdentifiers( $this->_table_prefix . $field ) : $this->escapeIdentifiers( $field );
		}

		if ( isset( $aggregate ) )
		{
			$field = sprintf( $aggregate, $field );
		}

		if ( isset( $scalar ) )
		{
			$field = sprintf( $scalar, $field );
		}

		return isset( $operator ) && $operator !== FALSE ? $field . ' ' . $operator : $field;
	}

	// ------------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param    mixed
	 *
	 * @return    mixed
	 */
	public function escapeIdentifiers( $item )
	{
		if ( static::$escapeCharacter === '' OR empty( $item ) OR in_array( $item, static::$reservedIdentifiers ) )
		{
			return $item;
		}
		elseif ( is_array( $item ) )
		{
			foreach ( $item as $key => $value )
			{
				$item[ $key ] = $this->escapeIdentifiers( $value );
			}

			return $item;
		}
		// Avoid breaking functions and literal values inside queries
		elseif ( ctype_digit( $item ) OR $item[ 0 ] === "'" OR ( static::$escapeCharacter !== '"' && $item[ 0 ] === '"' ) OR
			strpos( $item, '(' ) !== FALSE
		)
		{
			return $item;
		}

		static $preg_escaped_string = [ ];

		if ( empty( $preg_escaped_string ) )
		{
			if ( is_array( static::$escapeCharacter ) )
			{
				$preg_escaped_string = [
					preg_quote( static::$escapeCharacter[ 0 ], '/' ),
					preg_quote( static::$escapeCharacter[ 1 ], '/' ),
					static::$escapeCharacter[ 0 ],
					static::$escapeCharacter[ 1 ],
				];
			}
			else
			{
				$preg_escaped_string[ 0 ] = $preg_escaped_string[ 1 ] = preg_quote( static::$escapeCharacter, '/' );
				$preg_escaped_string[ 2 ] = $preg_escaped_string[ 3 ] = static::$escapeCharacter;
			}
		}

		foreach ( static::$reservedIdentifiers as $id )
		{
			if ( strpos( $item, '.' . $id ) !== FALSE )
			{
				return preg_replace(
					'/' . $preg_escaped_string[ 0 ] . '?([^' . $preg_escaped_string[ 1 ] . '\.]+)' . $preg_escaped_string[ 1 ] . '?\./i',
					$preg_escaped_string[ 2 ] . '$1' . $preg_escaped_string[ 3 ] . '.', $item );
			}
		}

		return preg_replace(
			'/' . $preg_escaped_string[ 0 ] . '?([^' . $preg_escaped_string[ 1 ] . '\.]+)' . $preg_escaped_string[ 1 ] . '?(\.)?/i',
			$preg_escaped_string[ 2 ] . '$1' . $preg_escaped_string[ 3 ] . '$2', $item );
	}

	//--------------------------------------------------------------------

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type.
	 * Sets boolean and null types
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	public function escape( $string )
	{
		if ( is_array( $string ) )
		{
			$string = array_map( [ &$this, 'escape' ], $string );

			return $string;
		}
		elseif ( is_string( $string ) OR ( is_object( $string ) && method_exists( $string, '__toString' ) ) )
		{
			return "'" . $this->escapeString( $string ) . "'";
		}
		elseif ( is_bool( $string ) )
		{
			return ( $string === FALSE ) ? 0 : 1;
		}
		elseif ( $string === NULL )
		{
			return 'NULL';
		}

		return $string;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape String
	 *
	 * @param    string|string[] $string Input string
	 * @param    bool            $like   Whether or not the string will be used in a LIKE condition
	 *
	 * @return    string
	 */
	public function escapeString( $string, $like = FALSE )
	{
		if ( is_array( $string ) )
		{
			foreach ( $string as $key => $value )
			{
				$string[ $key ] = $this->escapeString( $value, $like );
			}

			return $string;
		}

		$string = str_replace( "'", "\\'", remove_invisible_characters( $string ) );

		// escape LIKE condition wildcards
		if ( $like === TRUE )
		{
			return str_replace(
				[ static::$escapeLikeCharacter, '%', '_' ],
				[ static::$escapeLikeCharacter . static::$escapeLikeCharacter, static::$escapeLikeCharacter . '%', static::$escapeLikeCharacter . '_' ],
				$string
			);
		}

		return $string;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape LIKE String
	 *
	 * Calls the individual driver for platform
	 * specific escaping for LIKE conditions
	 *
	 * @param    string|string[]
	 *
	 * @return    mixed
	 */
	public function escapeLikeString( $string )
	{
		return $this->escapeString( $string, TRUE );
	}

	//--------------------------------------------------------------------
}