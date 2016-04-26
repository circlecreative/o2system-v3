<?php
/**
 * O2DB
 *
 * An open source PDO Wrapper for PHP 5.2.4 or newer
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
 * @since       Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\DB\Factory;

// ------------------------------------------------------------------------

use O2System\DB;

/**
 * Query Interface Class
 *
 * @package     O2DB
 * @subpackage  Interfaces
 * @category    Interface Class
 * @author      Circle Creative Developer Team
 * @link        http://circle-creative.com/products/o2db.html
 */
class Query
{
    /**
     * New Line Character
     *
     * @access  protected
     * @type    string
     */
    protected $_new_line = "\r\n";

    /**
     * Query Builders Statements
     *
     * @access  protected
     * @type    array
     */
    protected $_statements = array();

    protected $_params = array();

    protected $_table  = NULL;
    protected $_tables = array();

    /**
     * Builded SQL String
     *
     * @access  protected
     * @type    null
     */
    protected $_string = NULL;

    /**
     * SQL Where Clauses Statements Keys
     *
     * @access  protected
     * @type    array
     */
    protected static $_where_clauses = array(
        'WHERE'          => 'WHERE',
        'OR'             => 'OR_WHERE',
        'LIKE'           => 'WHERE',
        'OR_LIKE'        => 'OR_WHERE',
        'NOT_LIKE'       => 'WHERE',
        'OR_NOT_LIKE'    => 'OR_WHERE',
        'IN'             => 'WHERE',
        'OR_IN'          => 'OR_WHERE',
        'NOT_IN'         => 'WHERE',
        'OR_NOT_IN'      => 'OR_WHERE',
        'HAVING'         => 'HAVING',
        'OR_HAVING'      => 'OR_HAVING',
        'BETWEEN'        => 'WHERE',
        'OR_BETWEEN'     => 'OR_WHERE',
        'NOT_BETWEEN'    => 'WHERE',
        'OR_NOT_BETWEEN' => 'OR_WHERE'
    );

    /**
     * SQL Statements Operators
     *
     * @access  protected
     * @type    array
     */
    protected static $_operators_statements = array(
        'EQUAL'         => '=',
        'NOT'           => '!=',
        'GREATER'       => '>',
        'LESS'          => '<',
        'GREATER_EQUAL' => '>=',
        'LESS_EQUAL'    => '<='
    );

    /**
     * SQL Aggregate Functions
     *
     * SQL aggregate functions return a single value, calculated from values in a column.
     *
     * @access  protected
     * @type array
     */
    protected static $_sql_aggregate_functions = array(
        'AVG'   => 'AVG(%s)', // Returns the average value
        'COUNT' => 'COUNT(%s)', // Returns the number of rows
        'FIRST' => 'FIRST(%s)', // Returns the first value
        'LAST'  => 'LAST(%s)', // Returns the largest value
        'MAX'   => 'MAX(%s)', // Returns the largest value
        'MIN'   => 'MIN(%s)', // Returns the smallest value
        'SUM'   => 'SUM(%s)' // Returns the sum
    );

    /**
     * SQL Scalar functions
     *
     * SQL scalar functions return a single value, based on the input value.
     *
     * @access  protected
     * @type array
     */
    protected static $_sql_scalar_functions = array(
        'UCASE'  => 'UCASE(%s)', // Converts a field to uppercase
        'LCASE'  => 'LCASE(%s)', // Converts a field to lowercase
        'MID'    => 'MID(%s)', // Extract characters from a text field
        'LEN'    => 'LEN(%s)', // Returns the length of a text field
        'ROUND'  => 'ROUND(%s)', // Rounds a numeric field to the number of decimals specified
        'NOW'    => 'NOW(%s)', // Returns the current system date and time
        'FORMAT' => 'FORMAT(%s)' // Formats how a field is to be displayed
    );

    /**
     * SQL Building Sequence
     *
     * @access  protected
     * @type array
     */
    protected static $_sql_statements_sequence = array(
        'SELECT',
        'FROM',
        'JOIN',
        'WHERE',
        'GROUP_BY',
        'HAVING',
        'ORDER_BY',
        'UNION',
        'LIMIT'
    );

    /**
     * SQL Statements
     *
     * @access  protected
     * @type array
     */
    protected static $_sql_statements = array(
        'SELECT'   => 'SELECT %s',
        'DISTINCT' => 'SELECT DISTINCT %s',
        'FROM'     => 'FROM %s',
        'JOIN'     => '%s',
        'WHERE'    => 'WHERE %s',
        'INSERT'   => 'INSERT INTO %s(%s) VALUES(%s)',
        'UPDATE'   => 'UPDATE %s SET %s WHERE %s',
        'DELETE'   => 'DELETE FROM %s WHERE %s',
        'ORDER_BY' => 'ORDER BY %s',
    );

    /**
     * SQL Join Clauses Statements
     *
     * @access  protected
     * @type    array
     */
    protected static $_join_statements = array(
        'JOIN'  => 'JOIN %s ON %s = %s',
        'LEFT'  => 'LEFT JOIN %s ON %s = %s',
        'RIGHT' => 'RIGHT JOIN %s ON %s = %s',
        'INNER' => 'INNER JOIN %s ON %s = %s',
        'FULL'  => 'FULL OUTER JOIN %s ON %s = %s'
    );

    /**
     * SQL Where Clauses Statements
     *
     * @access  protected
     * @type    array
     */
    protected static $_where_statements = array(
        'WHERE'          => '%s %s',
        'OR'             => '%s %s',
        'LIKE'           => '%s LIKE %s',
        'OR_LIKE'        => '%s LIKE %s',
        'NOT_LIKE'       => '%s NOT LIKE %s',
        'OR_NOT_LIKE'    => '%s NOT LIKE %s',
        'IN'             => '%s IN (%s)',
        'OR_IN'          => '%s IN (%s)',
        'NOT_IN'         => '%s NOT IN (%s)',
        'OR_NOT_IN'      => '%s NOT IN (%s)',
        'HAVING'         => '%s HAVING %s',
        'OR_HAVING'      => '%s HAVING %s',
        'BETWEEN'        => '%s BETWEEN %s',
        'OR_BETWEEN'     => '%s BETWEEN %s',
        'NOT_BETWEEN'    => '%s NOT BETWEEN %s',
        'OR_NOT_BETWEEN' => '%s NOT BETWEEN %s'
    );

    /**
     * Connection Class Object
     *
     * @access  protected
     * @type    Connection
     */
    protected $_driver;

    /**
     * Class Constructor
     *
     * @param   Connection $connection DB Connection
     *
     * @access  public
     */
    public function __construct( &$driver )
    {
        $this->_driver =& $driver;
    }

    // ------------------------------------------------------------------------

    /**
     * Get String
     *
     * Get SQL Query statement string
     *
     * @access  public
     * @return  string|Result
     */
    public function get_string()
    {
        if( empty( $this->_string ) )
        {
            $this->_prepare_string();
        }

        return $this->_string;
    }

    // ------------------------------------------------------------------------

    public function get_params()
    {
        return $this->_params;
    }

    /**
     * Last Query
     *
     * Last run query
     *
     * @access  public
     * @return  mixed
     */
    public function last_query()
    {
        return end( $this->_queries );
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare String
     *
     * Prepare sql statement string
     *
     * @access  protected
     * @return  null|string
     */
    protected function _prepare_string()
    {
        //print_code( $this );
        $statement = array();

        foreach( static::$_sql_statements_sequence as $_string_sequence )
        {
            $statement[ ] = trim( $this->{'_prepare_' . strtolower( $_string_sequence ) . '_statement'}() );
        }

        $statement = array_filter( $statement );

        $this->_string = implode( $this->_new_line, $statement );

        return $this->_string;
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Select Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_select_statement()
    {
        if( empty( $this->_statements[ 'SELECT' ] ) )
        {
            $statement = '*';
        }
        else
        {
            $statement = implode( ', ', array_unique( $this->_statements[ 'SELECT' ] ) );
        }

        return sprintf( static::$_sql_statements[ ( isset( $this->_statements[ 'DISTINCT' ] ) ? 'DISTINCT' : 'SELECT' ) ], $statement );
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare From Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_from_statement()
    {
        if( ! empty( $this->_table ) )
        {
            if( isset( $this->_tables[ $this->_table ] ) )
            {
                $alias = $this->_tables[ $this->_table ];
            }

            $from = empty( $alias ) ? $this->_table : $this->_table . ' AS ' . $alias;

            return sprintf( static::$_sql_statements[ 'FROM' ], $from );
        }
        elseif( ! empty( $this->_tables ) )
        {
            foreach( $this->_tables as $table => $alias )
            {
                if( is_null( $alias ) )
                {
                    $from[ ] = $table;
                }
                else
                {
                    $from[ ] = $table . ' AS ' . $alias;
                }
            }

            return sprintf( static::$_sql_statements[ 'FROM' ], implode( ', ', $from ) );
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Join Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_join_statement()
    {
        return empty( $this->_statements[ 'JOIN' ] ) ? NULL : implode( $this->_new_line, $this->_statements[ 'JOIN' ] );
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Where Clause Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_where_statement()
    {
        $statement = array();
        if( ! empty( $this->_statements[ 'WHERE' ] ) )
        {
            $statement[ ] = implode( $this->_new_line . 'AND ', $this->_statements[ 'WHERE' ] );
        }

        // OR WHERE
        if( ! empty( $this->_statements[ 'OR_WHERE' ] ) )
        {
            $statement [ ] = ( empty( $statement ) ? NULL : 'OR ' ) . implode( $this->_new_line . 'OR ', $this->_statements[ 'OR_WHERE' ] );
        }

        if( ! empty( $statement ) )
        {
            return sprintf( static::$_sql_statements[ 'WHERE' ], implode( $this->_new_line, $statement ) );
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Having Clause Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_having_statement()
    {
        if( ! empty( $this->_statements[ 'HAVING' ] ) )
        {
            $statement = 'HAVING ' . implode( ', ', $this->_statements[ 'HAVING' ] );

            if( ! empty( $this->_statements[ 'OR_HAVING' ] ) )
            {
                $statement .= ' OR ' . implode( ', ', $this->_statements[ 'OR_HAVING' ] );
            }

            return $statement;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Group By Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_group_by_statement()
    {
        return empty( $this->_statements[ 'GROUP_BY' ] ) ? NULL : 'GROUP BY ' . implode( ', ', $this->_statements[ 'GROUP_BY' ] );
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Order By Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_order_by_statement()
    {
        return empty( $this->_statements[ 'ORDER_BY' ] ) ? NULL : sprintf( static::$_sql_statements[ 'ORDER_BY' ], implode( ', ', $this->_statements[ 'ORDER_BY' ] ) );
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Union Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_union_statement()
    {
        return empty( $this->_statements[ 'UNION' ] ) ? NULL : ( isset( $this->_statements[ 'UNION_ALL' ] ) ? 'UNION ALL' : 'UNION' ) . $this->_new_line . $this->_statements[ 'UNION' ];
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Limit Statement
     *
     * @access  protected
     * @return  string
     */
    protected function _prepare_limit_statement()
    {
        if( ! empty( $this->_statements[ 'LIMIT' ] ) )
        {
            $statement = 'LIMIT ' . (int)$this->_statements[ 'LIMIT' ];

            if( ! empty( $this->_statements[ 'OFFSET' ] ) )
            {
                $statement .= ', ' . (int)$this->_statements[ 'OFFSET' ];
            }

            return $statement;
        }
    }

    // ------------------------------------------------------------------------

    protected function _prepare_identifier( $field, $prefix = FALSE, $operator = NULL )
    {
        $x_field = explode( ' ', $field );

        if( count( $x_field ) == 2 )
        {
            $field = reset( $x_field );
            $operator = end( $x_field );
        }

        if( strpos( $field, ':' ) !== FALSE )
        {
            $x_fields = explode( ':', $field );

            foreach( $x_fields as $key => $value )
            {
                if( array_key_exists( strtoupper( $value ), static::$_operators_statements ) AND $operator !== FALSE )
                {
                    $operator = static::$_operators_statements[ strtoupper( $value ) ];
                    array_shift( $x_fields );
                }
                elseif( array_key_exists( strtoupper( $value ), static::$_sql_aggregate_functions ) )
                {
                    $aggregate = static::$_sql_aggregate_functions[ strtoupper( $value ) ];
                }
                elseif( array_key_exists( strtoupper( $value ), static::$_sql_scalar_functions ) )
                {
                    $scalar = static::$_sql_scalar_functions[ strtoupper( $value ) ];
                }
                else
                {
                    $field = $value;
                }
            }
        }

        if( strpos( $field, '.' ) !== FALSE )
        {
            $x_strings = explode( '.', $field );
            $x_strings[ 0 ] = $prefix === TRUE && ! empty( $this->_driver->prefix ) ? $this->_driver->prefix . $x_strings[ 0 ] : $x_strings[ 0 ];

            $x_strings = array_map( array( $this->_driver, 'escape_identifier' ), $x_strings );

            // Collects Tables
            $table = $this->_driver->escape_identifier( $x_strings[ 0 ] );

            if( isset( $this->_tables[ $table ] ) )
            {
                $x_strings[ 0 ] = $this->_tables[ $table ];
            }
            else
            {
                $this->_tables[ $table ] = NULL;
            }

            $field = implode( '.', $x_strings );
        }
        else
        {
            $field = $prefix === TRUE && ! empty( $this->_driver->prefix ) ? $this->_driver->escape_identifier( $this->_driver->prefix . $field ) : $this->_driver->escape_identifier( $field );
        }

        if( isset( $aggregate ) )
        {
            $field = sprintf( $aggregate, $field );
        }

        if( isset( $scalar ) )
        {
            $field = sprintf( $scalar, $field );
        }

        return isset( $operator ) && $operator !== FALSE ? $field . ' ' . $operator : $field;
    }

    // ------------------------------------------------------------------------

    /**
     * Get
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param   string  table name
     * @param   int     limit query
     * @param   int     offset query
     *
     * @return  object
     */
    public function get( $table = NULL, $limit = NULL, $offset = NULL )
    {
        if( ! empty( $table ) ) $this->from( $table );

        if( isset( $limit ) )
        {
            $this->_statements[ 'LIMIT' ] = $limit;
        }

        if( isset( $offset ) )
        {
            $this->_statements[ 'OFFSET' ] = $offset;
        }

        return new Result( $this->_driver );
    }

    // ------------------------------------------------------------------------

    /**
     * Get Where
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param   string  table name
     * @param   array   conditions
     * @param   int     limit query
     * @param   int     offset query
     *
     * @return  object
     */
    public function get_where( $table = NULL, $conditions = array(), $limit = NULL, $offset = NULL )
    {
        if( ! empty( $table ) ) $this->from( $table );

        $this->where( $conditions );

        if( isset( $limit ) )
        {
            $this->_statements[ 'LIMIT' ] = $limit;
        }

        if( isset( $offset ) )
        {
            $this->_statements[ 'OFFSET' ] = $offset;
        }

        return new Result( $this->_driver );
    }

    // ------------------------------------------------------------------------

    public function union( $query, $all = FALSE )
    {
        if( $all === TRUE )
        {
            $this->_statements[ 'UNION_ALL' ] = TRUE;
        }

        $this->_statements[ 'UNION' ] = $query;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Select
     *
     * Generates the SELECT portion of the query
     *
     * @param    string
     *
     * @return  O2ORM Database::Query
     */
    public function select( $select )
    {
        if( is_string( $select ) )
        {
            $select = explode( ',', $select );
            $select = array_map( 'trim', $select );
        }

        foreach( $select as $field => $alias )
        {
            if( is_numeric( $field ) )
            {
                if( strpos( $alias, ' AS ' ) !== FALSE )
                {
                    $x_alias = explode( ' AS ', $alias );
                    $field = trim( reset( $x_alias ) );
                    $alias = trim( end( $x_alias ) );
                }

                if( is_string( $field ) )
                {
                    $this->_statements[ 'SELECT' ][ ] = $this->_prepare_identifier( $field, TRUE ) . ' AS ' . $this->_prepare_identifier( $alias );
                }
                else
                {
                    $this->_statements[ 'SELECT' ][ ] = $this->_prepare_identifier( $alias, TRUE );
                }
            }
            elseif( is_string( $field ) )
            {
                $this->_statements[ 'SELECT' ][ ] = $this->_prepare_identifier( $field, TRUE ) . ' AS ' . $this->_prepare_identifier( $alias );
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * From
     *
     * Generates the FROM portion of the query
     *
     * @param    mixed    can be a string or array
     *
     * @return    O2ORM Database::Query
     */
    public function from( $table, $alias = NULL )
    {
        $this->_table = $this->_prepare_identifier( $table, TRUE );

        if( isset( $alias ) )
        {
            $alias = $this->_driver->escape_identifier( $alias );
        }

        // Collect Tables
        $this->_tables[ $this->_table ] = $alias;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * DISTINCT
     *
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @param    bool
     *
     * @return    O2ORM Database::Query
     */
    public function distinct( $distinct = TRUE )
    {
        $this->_statements[ 'DISTINCT' ] = $distinct;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Join
     *
     * Generates the JOIN portion of the query
     *
     * @param    string
     * @param    string    the join condition
     * @param    string    the type of join
     *
     * @return    object
     */
    public function join( $table, $condition = NULL, $type = 'LEFT' )
    {
        if( is_array( $table ) )
        {
            foreach( $table as $name => $condition )
            {
                $this->join( $name, $condition, $type );
            }

            return $this;
        }
        else
        {
            if( strpos( $table, ':' ) !== FALSE )
            {
                $x_table = explode( ':', $table );

                foreach( $x_table as $string )
                {
                    if( array_key_exists( strtoupper( $string ), static::$_join_statements ) )
                    {
                        $statement = static::$_join_statements[ strtoupper( $string ) ];
                    }
                    else
                    {
                        $table = $string;
                    }
                }
            }

            if( ! isset( $statement ) )
            {
                $statement = static::$_join_statements[ strtoupper( $type ) ];
                $table = $this->_prepare_identifier( $table, TRUE );
            }

            if( is_string( $condition ) )
            {
                $condition = explode( '=', $condition );
                $condition = array_map( 'trim', $condition );
            }
            elseif( is_array( $condition ) )
            {
                $condition = array_merge( array_keys( $condition ), array_values( $condition ) );
            }

            $this->_statements[ 'JOIN' ][ ] = sprintf( $statement, $table, $this->_prepare_identifier( $condition[ 0 ], TRUE ), $this->_prepare_identifier( $condition[ 1 ], TRUE ) );

            return $this;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * GROUP BY
     *
     * @param    string $by
     *
     * @return    O2ORM Database::Query
     */
    public function group_by( $by )
    {
        $this->_statements[ 'GROUP_BY' ][ ] = $this->_prepare_identifier( $by, TRUE );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with AND
     *
     * @param    mixed
     * @param    mixed
     *
     * @return    object
     */
    public function where( $fields, $value = NULL )
    {
        return $this->_prepare_condition( $fields, $value, 'WHERE' );
    }

    // ------------------------------------------------------------------------

    /**
     * OR Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with OR
     *
     * @param   mixed
     * @param   mixed
     *
     * @return  object
     */
    public function or_where( $fields, $value = NULL )
    {
        return $this->_prepare_condition( $fields, $value, 'OR' );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Where_in
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * AND if appropriate
     *
     * @param   string  The field to search
     * @param   array   The values searched on
     *
     * @return  object
     */
    public function where_in( $fields, array $values = array() )
    {
        return $this->_prepare_condition( $fields, $values, 'IN' );
    }

    // ------------------------------------------------------------------------

    /**
     * Where_in_or
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * OR if appropriate
     *
     * @param   string  The field to search
     * @param   array   The values searched on
     *
     * @return  object
     */
    public function or_where_in( $fields, array $values = array() )
    {
        return $this->_prepare_condition( $fields, $values, 'OR_IN' );
    }

    // ------------------------------------------------------------------------

    /**
     * Where_not_in
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with AND if appropriate
     *
     * @param   string  The field to search
     * @param   array   The values searched on
     *
     * @return  object
     */
    public function where_not_in( $fields, array $values = array() )
    {
        return $this->_prepare_condition( $fields, $values, 'NOT_IN' );
    }

    // ------------------------------------------------------------------------

    /**
     * Where_not_in_or
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with OR if appropriate
     *
     * @param   string  The field to search
     * @param   array   The values searched on
     *
     * @return  object
     */
    public function or_where_not_in( $fields, array $values = array() )
    {
        return $this->_prepare_condition( $fields, $values, 'OR_NOT_IN' );
    }

    // ------------------------------------------------------------------------

    public function where_between( $fields, array $values = array() )
    {
        $values = array_map( [ $this, 'quote_string' ], $values );

        return $this->_prepare_condition( $fields, implode( ' AND ', $values ), 'BETWEEN' );
    }

    // ------------------------------------------------------------------------

    public function or_where_between( $fields, array $values = array() )
    {
        $values = array_map( [ $this, 'quote_string' ], $values );

        return $this->_prepare_condition( $fields, implode( ' AND ', $values ), 'OR_BETWEEN' );
    }

    // ------------------------------------------------------------------------

    public function where_not_between( $fields, array $values = array() )
    {
        return $this->_prepare_condition( $fields, $values, 'NOT_BETWEEN' );
    }

    // ------------------------------------------------------------------------

    public function or_where_not_between( $fields, array $values = array() )
    {
        return $this->_prepare_condition( $fields, $values, 'OR_NOT_BETWEEN' );
    }

    // ------------------------------------------------------------------------


    public function like( $fields, $match = '', $wildcard = 'none' )
    {
        return $this->_prepare_like( $fields, $match, $wildcard, 'LIKE' );
    }

    /**
     * OR Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with OR
     *
     * @param   mixed
     * @param   mixed
     *
     * @return  object
     */
    public function or_like( $field, $match = '', $wildcard = 'none' )
    {
        $this->_prepare_like( $field, $match, $wildcard, 'OR_LIKE' );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with AND
     *
     * @param   mixed
     * @param   mixed
     *
     * @return  object
     */
    public function not_like( $field, $match = '', $wildcard = 'none' )
    {
        $this->_prepare_like( $field, $match, $wildcard, 'NOT_LIKE' );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * OR Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with OR
     *
     * @param   mixed
     * @param   mixed
     *
     * @return  object
     */
    public function or_not_like( $field, $match = '', $wildcard = 'both' )
    {
        $$this->_prepare_like( $field, $match, $wildcard, 'OR_NOT_LIKE' );

        return $this;
    }

    // ------------------------------------------------------------------------

    protected function _prepare_like( $fields, $match = '', $wildcard = 'none', $clause = 'LIKE' )
    {
        if( is_array( $fields ) )
        {
            foreach( $fields as $field => $match )
            {
                $this->like( $field, $match, $wildcard, $clause );
            }

            return $this;
        }
        else
        {
            switch( $wildcard )
            {
                default:
                case 'both':
                    $match = "%" . $match . "%";
                    break;
                case 'before':
                    $match = "%" . $match;
                    break;
                case 'after':
                    $match = $match . "%";
                    break;
                case 'none':
                    // nothing to do
                    break;
            }

            $this->_prepare_condition( $fields, $match, $clause );

            return $this;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Sets the HAVING value
     *
     * Separates multiple calls with AND
     *
     * @param   string|array $fields Table field
     * @param   string       $value  Having criteria
     *
     * @return  Query
     */
    public function having( $fields, $value = NULL )
    {
        return $this->_prepare_condition( $fields, $value, 'HAVING' );
    }

    // ------------------------------------------------------------------------

    /**
     * Sets the OR HAVING value
     *
     * Separates multiple calls with OR
     *
     * @param   string $field Table Field
     * @param   string $value Or having criteria
     *
     * @return  Query
     */
    public function or_having( $field, $value = '' )
    {
        $this->having( $field, $value, 'OR_HAVING' );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Prepare Query Condition
     *
     * @param string|array $field
     * @param string       $value
     * @param string       $clause
     *
     * @access  public
     * @return  Query
     */
    public function _prepare_condition( $field, $value = NULL, $clause = 'WHERE' )
    {
        if( is_array( $field ) )
        {
            foreach( $field as $key => $value )
            {
                $this->_prepare_condition( $key, $value, $clause );
            }
        }
        else
        {
            if( array_key_exists( $clause, static::$_where_clauses ) )
            {
                if( in_array( $clause, array( 'WHERE', 'OR' ) ) )
                {
                    $field = $this->_prepare_identifier( $field, TRUE, '=' );
                }
                else
                {
                    $field = $this->_prepare_identifier( $field, TRUE, FALSE );
                }

                $parameter = strtolower( $clause ) . '_' . str_replace( '.', '_', $field );
                $parameter = ':' . preg_replace( "/[^A-Za-z0-9_]/", '', $parameter );

                $value = is_array( $value ) ? $this->_driver->escape_array( $value ) : $value;

                $this->_params[ $parameter ] = $value;

                $this->_statements[ static::$_where_clauses[ $clause ] ][ ] = sprintf( static::$_where_statements[ $clause ], $field, $parameter );
                $this->_statements[ static::$_where_clauses[ $clause ] ] = array_unique( $this->_statements[ static::$_where_clauses[ $clause ] ] );
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * LIMIT
     *
     * @param   int $limit  LIMIT value
     * @param   int $offset OFFSET value
     *
     * @return  Query
     */
    public function limit( $limit, $offset = NULL )
    {
        $this->_statements[ 'LIMIT' ] = $limit;

        if( isset( $offset ) )
        {
            $this->_statements[ 'OFFSET' ] = $offset;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Order By
     *
     * @param   string $table
     * @param   string $sort Sorting Direction ASC, DESC or RANDOM
     *
     * @return    Query
     */
    public function order_by( $table, $sort = 'ASC' )
    {
        $this->_statements[ 'ORDER_BY' ][ ] = $this->_prepare_identifier( $table, TRUE ) . ' ' . strtoupper( $sort );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Insert data
     *
     * @param   string
     * @param   array
     *
     * @access  public
     * @return  int
     */
    public function insert( $table, array $data = array(), $return_string = FALSE )
    {
        $fields = $this->_driver->list_fields( $table );

        foreach( $data as $field => $value )
        {
            if( is_array( $value ) )
            {
                $value = json_encode( $value );
            }
            elseif( is_object( $value ) )
            {
                $value = serialize( $value );
            }
            else
            {
                $value = trim( $value );
            }

            if( ! empty( $fields ) )
            {
                if( in_array( $field, $fields ) )
                {
                    $parameter = ':insert_' . $field;

                    $insert_fields[ ] = $this->_prepare_identifier( $field );
                    $insert_values[ ] = $parameter;
                    $this->_params[ $parameter ] = $value;
                }
            }
            else
            {
                $parameter = ':insert_' . $field;

                $insert_fields[ ] = $this->_prepare_identifier( $field );
                $insert_values[ ] = $parameter;
                $this->_params[ $parameter ] = $this->_driver->escape_string( $value );
            }
        }

        $insert_string = static::$_sql_statements[ 'INSERT' ];
        $insert_string = sprintf( $insert_string, $this->_prepare_identifier( $table ), implode( ', ', $insert_fields ), implode( ', ', $insert_values ) );

        if( $return_string === TRUE )
        {
            return $insert_string;
        }
        else
        {
            $this->_driver->execute( $insert_string, $this->_params );

            $last_insert_id = $this->_driver->last_insert_id();

            if( $last_insert_id == 0 )
            {
                return FALSE;
            }

            return $last_insert_id;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Update data
     *
     * @param   string
     * @param   array
     *
     * @return  object
     */
    public function update( $table, array $data = array(), array $conditions = array(), $return_string = FALSE )
    {
        $fields = $this->_driver->list_fields( $table );

        if( ! empty( $conditions ) )
        {
            $this->_prepare_condition( $conditions );
        }

        foreach( $data as $field => $value )
        {
            if( is_array( $value ) )
            {
                $value = json_encode( $value );
            }
            elseif( is_object( $value ) )
            {
                $value = serialize( $value );
            }
            else
            {
                $value = trim( $value );
            }

            if( ! empty( $fields ) )
            {
                if( in_array( $field, $fields ) )
                {
                    $parameter = ':update_' . $field;
                    $this->_params[ $parameter ] = $value;

                    $sets[ ] = $this->_prepare_identifier( $field ) . ' = ' . $parameter;
                }
            }
            else
            {
                $parameter = ':update_' . $field;
                $this->_params[ $parameter ] = $value;

                $sets[ ] = $this->_prepare_identifier( $field ) . ' = ' . $parameter;
            }
        }

        $update_string = static::$_sql_statements[ 'UPDATE' ];
        $update_string = sprintf( $update_string, $this->_prepare_identifier( $table ), implode( ', ', $sets ), str_replace( 'WHERE ', '', $this->_prepare_where_statement() ) );

        if( $return_string === TRUE )
        {
            return $update_string;
        }
        else
        {
            $this->_driver->execute( $update_string, $this->_params );
            $affected_rows = $this->_driver->affected_rows();

            if( $affected_rows == 0 )
            {
                return FALSE;
            }

            return (int)$affected_rows;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Delete data
     *
     * @param   string
     * @param   array
     *
     * @return  object
     */
    public function delete( $table, array $conditions = array() )
    {
        $this->_params = array();

        if( ! empty( $conditions ) )
        {
            $this->_prepare_condition( $conditions );
        }

        $sql = static::$_sql_statements[ 'DELETE' ];
        $sql = sprintf( $sql, $this->_prepare_identifier( $table ), str_replace( 'WHERE ', '', $this->_prepare_where_statement() ) );

        $this->_driver->execute( $sql );

        $affected_rows = $this->_driver->affected_rows();

        if( $affected_rows == 0 )
        {
            return FALSE;
        }

        return (int)$affected_rows;
    }

    // ------------------------------------------------------------------------
}
