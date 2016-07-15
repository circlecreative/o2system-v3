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
 * Query Builder Class
 *
 * This is the platform-independent base Query Builder implementation class.
 *
 * Based on CodeIgniter Database Query Builder Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Query
{
	/**
	 * Return DELETE SQL flag
	 *
	 * @type    bool
	 */
	protected $return_delete_sql = FALSE;

	/**
	 * Reset DELETE data flag
	 *
	 * @type    bool
	 */
	protected $reset_delete_data = FALSE;

	/**
	 * QB SELECT data
	 *
	 * @type    array
	 */
	protected $qb_select = [ ];

	/**
	 * QB DISTINCT flag
	 *
	 * @type    bool
	 */
	protected $qb_distinct = FALSE;

	/**
	 * QB FROM data
	 *
	 * @type    array
	 */
	protected $qb_from = [ ];

	/**
	 * QB JOIN data
	 *
	 * @type    array
	 */
	protected $qb_join = [ ];

	/**
	 * QB WHERE data
	 *
	 * @type    array
	 */
	protected $qb_where = [ ];

	/**
	 * QB GROUP BY data
	 *
	 * @type    array
	 */
	protected $qb_group_by = [ ];

	/**
	 * QB HAVING data
	 *
	 * @type    array
	 */
	protected $qb_having = [ ];

	/**
	 * QB keys
	 *
	 * @type    array
	 */
	protected $qb_keys = [ ];

	/**
	 * QB LIMIT data
	 *
	 * @type    int
	 */
	protected $qb_limit = FALSE;

	/**
	 * QB OFFSET data
	 *
	 * @type    int
	 */
	protected $qb_offset = FALSE;

	/**
	 * QB ORDER BY data
	 *
	 * @type    array
	 */
	protected $qb_order_by = [ ];

	/**
	 * QB data sets
	 *
	 * @type    array
	 */
	protected $qb_set = [ ];

	/**
	 * QB aliased tables list
	 *
	 * @type    array
	 */
	protected $qb_aliased_tables = [ ];

	/**
	 * QB WHERE group started flag
	 *
	 * @type    bool
	 */
	protected $qb_where_group_started = FALSE;

	/**
	 * QB WHERE group count
	 *
	 * @type    int
	 */
	protected $qb_where_group_count = 0;

	// Query Builder Caching variables

	/**
	 * QB Caching flag
	 *
	 * @type    bool
	 */
	protected $qb_caching = FALSE;

	/**
	 * QB Cache exists list
	 *
	 * @type    array
	 */
	protected $qb_cache_exists = [ ];

	/**
	 * QB Cache SELECT data
	 *
	 * @type    array
	 */
	protected $qb_cache_select = [ ];

	/**
	 * QB Cache FROM data
	 *
	 * @type    array
	 */
	protected $qb_cache_from = [ ];

	/**
	 * QB Cache JOIN data
	 *
	 * @type    array
	 */
	protected $qb_cache_join = [ ];

	/**
	 * QB Cache WHERE data
	 *
	 * @type    array
	 */
	protected $qb_cache_where = [ ];

	/**
	 * QB Cache GROUP BY data
	 *
	 * @type    array
	 */
	protected $qb_cache_group_by = [ ];

	/**
	 * QB Cache HAVING data
	 *
	 * @type    array
	 */
	protected $qb_cache_having = [ ];

	/**
	 * QB Cache ORDER BY data
	 *
	 * @type    array
	 */
	protected $qb_cache_order_by = [ ];

	/**
	 * QB Cache data sets
	 *
	 * @type    array
	 */
	protected $qb_cache_set = [ ];

	/**
	 * QB No Escape data
	 *
	 * @type    array
	 */
	protected $qb_no_escape = [ ];

	/**
	 * QB Cache No Escape data
	 *
	 * @type    array
	 */
	protected $qb_cache_no_escape = [ ];

	// --------------------------------------------------------------------

	/**
	 * Select
	 *
	 * Generates the SELECT portion of the query
	 *
	 * @param    string
	 * @param    mixed
	 *
	 * @return    Query
	 */
	public function select( $select = '*', $escape = NULL )
	{
		if ( is_string( $select ) )
		{
			$select = explode( ',', $select );
		}

		// If the escape value was not set, we will base it on the global setting
		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		foreach ( $select as $field )
		{
			if ( is_array( $field ) )
			{
				$field = key( $field ) . ' AS ' . $field[ key( $field ) ];
			}

			$field = trim( $field );

			if ( $field !== '' )
			{
				$this->qb_select[]    = $field;
				$this->qb_no_escape[] = $escape;

				if ( $this->qb_caching === TRUE )
				{
					$this->qb_cache_select[]    = $field;
					$this->qb_cache_exists[]    = 'select';
					$this->qb_cache_no_escape[] = $escape;
				}
			}
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Select Max
	 *
	 * Generates a SELECT MAX(field) portion of a query
	 *
	 * @param    string    the field
	 * @param    string    an alias
	 *
	 * @return    Query
	 */
	public function max( $select = '', $alias = '' )
	{
		return $this->_prepareAggregate( $select, $alias, 'MAX' );
	}

	// --------------------------------------------------------------------

	/**
	 * Select Min
	 *
	 * Generates a SELECT MIN(field) portion of a query
	 *
	 * @param    string    the field
	 * @param    string    an alias
	 *
	 * @return    Query
	 */
	public function min( $select = '', $alias = '' )
	{
		return $this->_prepareAggregate( $select, $alias, 'MIN' );
	}

	// --------------------------------------------------------------------

	/**
	 * Select Average
	 *
	 * Generates a SELECT AVG(field) portion of a query
	 *
	 * @param    string    the field
	 * @param    string    an alias
	 *
	 * @return    Query
	 */
	public function avg( $select = '', $alias = '' )
	{
		return $this->_prepareAggregate( $select, $alias, 'AVG' );
	}

	// --------------------------------------------------------------------

	/**
	 * Select Sum
	 *
	 * Generates a SELECT SUM(field) portion of a query
	 *
	 * @param    string    the field
	 * @param    string    an alias
	 *
	 * @return    Query
	 */
	public function sum( $select = '', $alias = '' )
	{
		return $this->_prepareAggregate( $select, $alias, 'SUM' );
	}

	// --------------------------------------------------------------------

	/**
	 * SELECT [MAX|MIN|AVG|SUM]()
	 *
	 * @used-by    select_max()
	 * @used-by    select_min()
	 * @used-by    select_avg()
	 * @used-by    select_sum()
	 *
	 * @param    string $select Field name
	 * @param    string $alias
	 * @param    string $type
	 *
	 * @return    Query
	 */
	protected function _prepareAggregate( $select = '', $alias = '', $type = 'MAX' )
	{
		if ( ! is_string( $select ) OR $select === '' )
		{
			throw new Exception( 'The query you submitted is not valid.' );
		}

		$type = strtoupper( $type );

		if ( ! in_array( $type, [ 'MAX', 'MIN', 'AVG', 'SUM' ] ) )
		{
			throw new Exception( 'Invalid function type: ' . $type );
		}

		if ( $alias === '' )
		{
			$alias = $this->_createAliasFromTable( trim( $select ) );
		}

		$sql = $type . '(' . $this->protectIdentifiers( trim( $select ) ) . ') AS ' . $this->escapeIdentifiers( trim( $alias ) );

		$this->qb_select[]    = $sql;
		$this->qb_no_escape[] = NULL;

		if ( $this->qb_caching === TRUE )
		{
			$this->qb_cache_select[] = $sql;
			$this->qb_cache_exists[] = 'select';
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Determines the alias name based on the table
	 *
	 * @param    string $item
	 *
	 * @return    string
	 */
	protected function _createAliasFromTable( $item )
	{
		if ( strpos( $item, '.' ) !== FALSE )
		{
			$item = explode( '.', $item );

			return end( $item );
		}

		return $item;
	}

	// --------------------------------------------------------------------

	/**
	 * DISTINCT
	 *
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 *
	 * @param    bool $val
	 *
	 * @return    Query
	 */
	public function distinct( $val = TRUE )
	{
		$this->qb_distinct = is_bool( $val ) ? $val : TRUE;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * From
	 *
	 * Generates the FROM portion of the query
	 *
	 * @param    mixed $from can be a string or array
	 *
	 * @return    Query
	 */
	public function from( $from )
	{
		foreach ( (array) $from as $val )
		{
			if ( strpos( $val, ',' ) !== FALSE )
			{
				foreach ( explode( ',', $val ) as $v )
				{
					$v = trim( $v );
					$this->_trackAliases( $v );

					$this->qb_from[] = $v = $this->protectIdentifiers( $v, TRUE, NULL, FALSE );

					if ( $this->qb_caching === TRUE )
					{
						$this->qb_cache_from[]   = $v;
						$this->qb_cache_exists[] = 'from';
					}
				}
			}
			else
			{
				$val = trim( $val );

				// Extract any aliases that might exist. We use this information
				// in the protectIdentifiers to know whether to add a table prefix
				$this->_trackAliases( $val );

				$this->qb_from[] = $val = $this->protectIdentifiers( $val, TRUE, NULL, FALSE );

				if ( $this->qb_caching === TRUE )
				{
					$this->qb_cache_from[]   = $val;
					$this->qb_cache_exists[] = 'from';
				}
			}
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * JOIN
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @param    string
	 * @param    string    the join condition
	 * @param    string    the type of join
	 * @param    string    whether not to try to escape identifiers
	 *
	 * @return    Query
	 */
	public function join( $table, $cond, $type = '', $escape = NULL )
	{
		if ( $type !== '' )
		{
			$type = strtoupper( trim( $type ) );

			if ( ! in_array( $type, [ 'LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER' ], TRUE ) )
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}

		// Extract any aliases that might exist. We use this information
		// in the protectIdentifiers to know whether to add a table prefix
		$this->_trackAliases( $table );

		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		// Split multiple conditions
		if ( $escape === TRUE && preg_match_all( '/\sAND\s|\sOR\s/i', $cond, $m, PREG_OFFSET_CAPTURE ) )
		{
			$newcond  = '';
			$m[ 0 ][] = [ '', strlen( $cond ) ];

			for ( $i = 0, $c = count( $m[ 0 ] ), $s = 0;
			      $i < $c;
			      $s = $m[ 0 ][ $i ][ 1 ] + strlen( $m[ 0 ][ $i ][ 0 ] ), $i++ )
			{
				$temp = substr( $cond, $s, ( $m[ 0 ][ $i ][ 1 ] - $s ) );

				$newcond .= preg_match( "/([\[\]\w\.'-]+)(\s*[^\"\[`'\w]+\s*)(.+)/i", $temp, $match )
					? $this->protectIdentifiers( $match[ 1 ] ) . $match[ 2 ] . $this->protectIdentifiers( $match[ 3 ] )
					: $temp;

				$newcond .= $m[ 0 ][ $i ][ 0 ];
			}

			$cond = ' ON ' . $newcond;
		}
		// Split apart the condition and protect the identifiers
		elseif ( $escape === TRUE && preg_match( "/([\[\]\w\.'-]+)(\s*[^\"\[`'\w]+\s*)(.+)/i", $cond, $match ) )
		{
			$cond = ' ON ' . $this->protectIdentifiers( $match[ 1 ] ) . $match[ 2 ] . $this->protectIdentifiers( $match[ 3 ] );
		}
		elseif ( ! $this->_hasOperator( $cond ) )
		{
			$cond = ' USING (' . ( $escape ? $this->escapeIdentifiers( $cond ) : $cond ) . ')';
		}
		else
		{
			$cond = ' ON ' . $cond;
		}

		// Do we want to escape the table name?
		if ( $escape === TRUE )
		{
			$table = $this->protectIdentifiers( $table, TRUE, NULL, FALSE );
		}

		// Assemble the JOIN statement
		$this->qb_join[] = $join = $type . 'JOIN ' . $table . $cond;

		if ( $this->qb_caching === TRUE )
		{
			$this->qb_cache_join[]   = $join;
			$this->qb_cache_exists[] = 'join';
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * WHERE
	 *
	 * Generates the WHERE portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param    mixed
	 * @param    mixed
	 * @param    bool
	 *
	 * @return    Query
	 */
	public function where( $key, $value = NULL, $escape = NULL )
	{
		return $this->_prepareWhereHaving( 'qb_where', $key, $value, 'AND ', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * OR WHERE
	 *
	 * Generates the WHERE portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param    mixed
	 * @param    mixed
	 * @param    bool
	 *
	 * @return    Query
	 */
	public function orWhere( $key, $value = NULL, $escape = NULL )
	{
		return $this->_prepareWhereHaving( 'qb_where', $key, $value, 'OR ', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * WHERE, HAVING
	 *
	 * @used-by    where()
	 * @used-by    or_where()
	 * @used-by    having()
	 * @used-by    or_having()
	 *
	 * @param    string $qb_key 'qb_where' or 'qb_having'
	 * @param    mixed  $key
	 * @param    mixed  $value
	 * @param    string $type
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	protected function _prepareWhereHaving( $qb_key, $key, $value = NULL, $type = 'AND ', $escape = NULL )
	{
		$qb_cache_key = ( $qb_key === 'qb_having' ) ? 'qb_cache_having' : 'qb_cache_where';

		if ( ! is_array( $key ) )
		{
			$key = [ $key => $value ];
		}

		// If the escape value was not set will base it on the global setting
		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		foreach ( $key as $k => $v )
		{
			$prefix = ( count( $this->$qb_key ) === 0 && count( $this->$qb_cache_key ) === 0 )
				? $this->_groupGetType( '' )
				: $this->_groupGetType( $type );

			if ( $v !== NULL )
			{
				if ( $escape === TRUE )
				{
					$v = ' ' . $this->escape( $v );
				}

				if ( ! $this->_hasOperator( $k ) )
				{
					$k .= ' = ';
				}
			}
			elseif ( ! $this->_hasOperator( $k ) )
			{
				// value appears not to have been set, assign the test to IS NULL
				$k .= ' IS NULL';
			}
			elseif ( preg_match( '/\s*(!?=|<>|IS(?:\s+NOT)?)\s*$/i', $k, $match, PREG_OFFSET_CAPTURE ) )
			{
				$k = substr( $k, 0, $match[ 0 ][ 1 ] ) . ( $match[ 1 ][ 0 ] === '=' ? ' IS NULL' : ' IS NOT NULL' );
			}

			$this->{$qb_key}[] = [ 'condition' => $prefix . $k . $v, 'escape' => $escape ];
			if ( $this->qb_caching === TRUE )
			{
				$this->{$qb_cache_key}[] = [ 'condition' => $prefix . $k . $v, 'escape' => $escape ];
				$this->qb_cache_exists[] = substr( $qb_key, 3 );
			}

		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * WHERE IN
	 *
	 * Generates a WHERE field IN('item', 'item') SQL query,
	 * joined with 'AND' if appropriate.
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function whereIn( $key = NULL, $values = NULL, $escape = NULL )
	{
		return $this->_whereIn( $key, $values, FALSE, 'AND ', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * OR WHERE IN
	 *
	 * Generates a WHERE field IN('item', 'item') SQL query,
	 * joined with 'OR' if appropriate.
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function orWhereIn( $key = NULL, $values = NULL, $escape = NULL )
	{
		return $this->_whereIn( $key, $values, FALSE, 'OR ', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * WHERE NOT IN
	 *
	 * Generates a WHERE field NOT IN('item', 'item') SQL query,
	 * joined with 'AND' if appropriate.
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function whereNotIn( $key = NULL, $values = NULL, $escape = NULL )
	{
		return $this->_whereIn( $key, $values, TRUE, 'AND ', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * OR WHERE NOT IN
	 *
	 * Generates a WHERE field NOT IN('item', 'item') SQL query,
	 * joined with 'OR' if appropriate.
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function orWhereNotIn( $key = NULL, $values = NULL, $escape = NULL )
	{
		return $this->_whereIn( $key, $values, TRUE, 'OR ', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * Internal WHERE IN
	 *
	 * @used-by    where_in()
	 * @used-by    or_where_in()
	 * @used-by    where_not_in()
	 * @used-by    or_where_not_in()
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $not    If the statement would be IN or NOT IN
	 * @param    string $type
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	protected function _whereIn( $key = NULL, $values = NULL, $not = FALSE, $type = 'AND ', $escape = NULL )
	{
		if ( $key === NULL OR $values === NULL )
		{
			return $this;
		}

		if ( ! is_array( $values ) )
		{
			$values = [ $values ];
		}

		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		$not = ( $not ) ? ' NOT' : '';

		$where_in = [ ];
		foreach ( $values as $value )
		{
			$where_in[] = $this->escape( $value );
		}

		$prefix   = ( count( $this->qb_where ) === 0 ) ? $this->_groupGetType( '' ) : $this->_groupGetType( $type );
		$where_in = [
			'condition' => $prefix . $key . $not . ' IN(' . implode( ', ', $where_in ) . ')',
			'escape'    => $escape,
		];

		$this->qb_where[] = $where_in;
		if ( $this->qb_caching === TRUE )
		{
			$this->qb_cache_where[]  = $where_in;
			$this->qb_cache_exists[] = 'where';
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * LIKE
	 *
	 * Generates a %LIKE% portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $side
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function like( $field, $match = '', $side = 'both', $escape = NULL )
	{
		return $this->_like( $field, $match, 'AND ', $side, '', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * NOT LIKE
	 *
	 * Generates a NOT LIKE portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $side
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function notLike( $field, $match = '', $side = 'both', $escape = NULL )
	{
		return $this->_like( $field, $match, 'AND ', $side, 'NOT', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * OR LIKE
	 *
	 * Generates a %LIKE% portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $side
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function orLike( $field, $match = '', $side = 'both', $escape = NULL )
	{
		return $this->_like( $field, $match, 'OR ', $side, '', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * OR NOT LIKE
	 *
	 * Generates a NOT LIKE portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $side
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function orNotLike( $field, $match = '', $side = 'both', $escape = NULL )
	{
		return $this->_like( $field, $match, 'OR ', $side, 'NOT', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * Internal LIKE
	 *
	 * @used-by    like()
	 * @used-by    or_like()
	 * @used-by    not_like()
	 * @used-by    or_not_like()
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $type
	 * @param    string $side
	 * @param    string $not
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	protected function _like( $field, $match = '', $type = 'AND ', $side = 'both', $not = '', $escape = NULL )
	{
		if ( ! is_array( $field ) )
		{
			$field = [ $field => $match ];
		}

		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		foreach ( $field as $k => $v )
		{
			$prefix = ( count( $this->qb_where ) === 0 && count( $this->qb_cache_where ) === 0 )
				? $this->_groupGetType( '' ) : $this->_groupGetType( $type );

			$v = $this->escapeLikeString( $v );

			if ( $side === 'none' )
			{
				$like_statement = "{$prefix} {$k} {$not} LIKE '{$v}'";
			}
			elseif ( $side === 'before' )
			{
				$like_statement = "{$prefix} {$k} {$not} LIKE '%{$v}'";
			}
			elseif ( $side === 'after' )
			{
				$like_statement = "{$prefix} {$k} {$not} LIKE '{$v}%'";
			}
			else
			{
				$like_statement = "{$prefix} {$k} {$not} LIKE '%{$v}%'";
			}

			// some platforms require an escape sequence definition for LIKE wildcards
			if ( $this->_like_escape_string !== '' )
			{
				$like_statement .= sprintf( $this->_like_escape_string, $this->_like_escape_character );
			}

			$this->qb_where[] = [ 'condition' => $like_statement, 'escape' => $escape ];
			if ( $this->qb_caching === TRUE )
			{
				$this->qb_cache_where[]  = [ 'condition' => $like_statement, 'escape' => $escape ];
				$this->qb_cache_exists[] = 'where';
			}
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Starts a query group.
	 *
	 * @param    string $not  (Internal use only)
	 * @param    string $type (Internal use only)
	 *
	 * @return    Query
	 */
	public function groupStart( $not = '', $type = 'AND ' )
	{
		$type = $this->_groupGetType( $type );

		$this->qb_where_group_started = TRUE;
		$prefix                       = ( count( $this->qb_where ) === 0 && count( $this->qb_cache_where ) === 0 ) ? '' : $type;
		$where                        = [
			'condition' => $prefix . $not . str_repeat( ' ', ++$this->qb_where_group_count ) . ' (',
			'escape'    => FALSE,
		];

		$this->qb_where[] = $where;
		if ( $this->qb_caching )
		{
			$this->qb_cache_where[] = $where;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Starts a query group, but ORs the group
	 *
	 * @return    Query
	 */
	public function orGroupStart()
	{
		return $this->groupStart( '', 'OR ' );
	}

	// --------------------------------------------------------------------

	/**
	 * Starts a query group, but NOTs the group
	 *
	 * @return    Query
	 */
	public function notGroupStart()
	{
		return $this->groupStart( 'NOT ', 'AND ' );
	}

	// --------------------------------------------------------------------

	/**
	 * Starts a query group, but OR NOTs the group
	 *
	 * @return    Query
	 */
	public function orNotGroupStart()
	{
		return $this->groupStart( 'NOT ', 'OR ' );
	}

	// --------------------------------------------------------------------

	/**
	 * Ends a query group
	 *
	 * @return    Query
	 */
	public function groupEnd()
	{
		$this->qb_where_group_started = FALSE;
		$where                        = [
			'condition' => str_repeat( ' ', $this->qb_where_group_count-- ) . ')',
			'escape'    => FALSE,
		];

		$this->qb_where[] = $where;
		if ( $this->qb_caching )
		{
			$this->qb_cache_where[] = $where;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Group_get_type
	 *
	 * @used-by    group_start()
	 * @used-by    _like()
	 * @used-by    _wh()
	 * @used-by    _where_in()
	 *
	 * @param    string $type
	 *
	 * @return    string
	 */
	protected function _groupGetType( $type )
	{
		if ( $this->qb_where_group_started )
		{
			$type                         = '';
			$this->qb_where_group_started = FALSE;
		}

		return $type;
	}

	// --------------------------------------------------------------------

	/**
	 * GROUP BY
	 *
	 * @param    string $by
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function groupBy( $by, $escape = NULL )
	{
		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		if ( is_string( $by ) )
		{
			$by = ( $escape === TRUE )
				? explode( ',', $by )
				: [ $by ];
		}

		foreach ( $by as $val )
		{
			$val = trim( $val );

			if ( $val !== '' )
			{
				$val = [ 'field' => $val, 'escape' => $escape ];

				$this->qb_group_by[] = $val;
				if ( $this->qb_caching === TRUE )
				{
					$this->qb_cache_group_by[] = $val;
					$this->qb_cache_exists[]   = 'group_by';
				}
			}
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * HAVING
	 *
	 * Separates multiple calls with 'AND'.
	 *
	 * @param    string $key
	 * @param    string $value
	 * @param    bool   $escape
	 *
	 * @return    object
	 */
	public function having( $key, $value = NULL, $escape = NULL )
	{
		return $this->_prepareWhereHaving( 'qb_having', $key, $value, 'AND ', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * OR HAVING
	 *
	 * Separates multiple calls with 'OR'.
	 *
	 * @param    string $key
	 * @param    string $value
	 * @param    bool   $escape
	 *
	 * @return    object
	 */
	public function orHaving( $key, $value = NULL, $escape = NULL )
	{
		return $this->_prepareWhereHaving( 'qb_having', $key, $value, 'OR ', $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * ORDER BY
	 *
	 * @param    string $order_by
	 * @param    string $direction ASC, DESC or RANDOM
	 * @param    bool   $escape
	 *
	 * @return    Query
	 */
	public function orderBy( $order_by, $direction = '', $escape = NULL )
	{
		$direction = strtoupper( trim( $direction ) );

		if ( $direction === 'RANDOM' )
		{
			$direction = '';

			// Do we have a seed value?
			$order_by = ctype_digit( (string) $order_by )
				? sprintf( $this->_random_keywords[ 1 ], $order_by )
				: $this->_random_keywords[ 0 ];
		}
		elseif ( empty( $order_by ) )
		{
			return $this;
		}
		elseif ( $direction !== '' )
		{
			$direction = in_array( $direction, [ 'ASC', 'DESC' ], TRUE ) ? ' ' . $direction : '';
		}

		is_bool( $escape ) OR $escape = $this->_protect_identifiers; //_protect_identifiers

		if ( $escape === FALSE )
		{
			$qb_order_by[] = [ 'field' => $order_by, 'direction' => $direction, 'escape' => FALSE ];
		}
		else
		{
			$qb_order_by = [ ];
			foreach ( explode( ',', $order_by ) as $field )
			{
				$qb_order_by[] = ( $direction === '' && preg_match( '/\s+(ASC|DESC)$/i', rtrim( $field ), $match, PREG_OFFSET_CAPTURE ) )
					? [ 'field' => ltrim( substr( $field, 0, $match[ 0 ][ 1 ] ) ), 'direction' => ' ' . $match[ 1 ][ 0 ], 'escape' => TRUE ]
					: [ 'field' => trim( $field ), 'direction' => $direction, 'escape' => TRUE ];
			}
		}

		$this->qb_order_by = array_merge( $this->qb_order_by, $qb_order_by );
		if ( $this->qb_caching === TRUE )
		{
			$this->qb_cache_order_by = array_merge( $this->qb_cache_order_by, $qb_order_by );
			$this->qb_cache_exists[] = 'order_by';
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * LIMIT
	 *
	 * @param    int $value  LIMIT value
	 * @param    int $offset OFFSET value
	 *
	 * @return    Query
	 */
	public function limit( $value, $offset = 0 )
	{
		is_null( $value ) OR $this->qb_limit = (int) $value;
		empty( $offset ) OR $this->qb_offset = (int) $offset;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Sets the OFFSET value
	 *
	 * @param    int $offset OFFSET value
	 *
	 * @return    Query
	 */
	public function offset( $offset )
	{
		empty( $offset ) OR $this->qb_offset = (int) $offset;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * LIMIT string
	 *
	 * Generates a platform-specific LIMIT clause.
	 *
	 * @param    string $sql SQL Query
	 *
	 * @return    string
	 */
	protected function _limit( $sql )
	{
		return $sql . ' LIMIT ' . ( $this->qb_offset ? $this->qb_offset . ', ' : '' ) . $this->qb_limit;
	}

	// --------------------------------------------------------------------

	/**
	 * The "set" function.
	 *
	 * Allows key/value pairs to be set for inserting or updating
	 *
	 * @param    mixed
	 * @param    string
	 * @param    bool
	 *
	 * @return    Query
	 */
	public function set( $key, $value = '', $escape = NULL )
	{
		$key = $this->_objectToArray( $key );

		if ( ! is_array( $key ) )
		{
			$key = [ $key => $value ];
		}

		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		foreach ( $key as $k => $v )
		{
			if ( is_array( $v ) )
			{
				$v = json_encode( $v );
			}
			elseif ( is_object( $v ) )
			{
				$v = serialize( $v );
			}
			else
			{
				$v = trim( $v );
			}

			$this->qb_set[ $this->protectIdentifiers( $k, FALSE, $escape ) ] = ( $escape )
				? $this->escape( $v ) : $v;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get SELECT query string
	 *
	 * Compiles a SELECT query string and returns the sql.
	 *
	 * @param    string    the table name to select from (optional)
	 * @param    bool      TRUE: resets QB values; FALSE: leave QB vaules alone
	 *
	 * @return    string
	 */
	public function getCompiledSelect( $table = '', $reset = TRUE )
	{
		if ( $table !== '' )
		{
			$this->_trackAliases( $table );
			$this->from( $table );
		}

		$select = $this->_compileSelect();

		if ( $reset === TRUE )
		{
			$this->_resetSelect();
		}

		return $select;
	}

	// --------------------------------------------------------------------

	/**
	 * Get
	 *
	 * Compiles the select statement based on the other functions called
	 * and runs the query
	 *
	 * @param    string    the table
	 * @param    string    the limit clause
	 * @param    string    the offset clause
	 *
	 * @return    object
	 */
	public function get( $table = '', $limit = NULL, $offset = NULL )
	{
		if ( $table !== '' )
		{
			$this->_trackAliases( $table );
			$this->from( $table );
		}

		if ( ! empty( $limit ) )
		{
			$this->limit( $limit, $offset );
		}

		$result = $this->query( $this->_compileSelect() );
		$this->_resetSelect();

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * "Count All Results" query
	 *
	 * Generates a platform-specific query string that counts all records
	 * returned by an Query Builder query.
	 *
	 * @param    string
	 * @param    bool    the reset clause
	 *
	 * @return    int
	 */
	public function countAll( $table = '', $reset = TRUE )
	{
		if ( $table !== '' )
		{
			$this->_trackAliases( $table );
			$this->from( $table );
		}

		$result = ( $this->qb_distinct === TRUE )
			? $this->query( $this->_count_string . $this->protectIdentifiers( 'numrows' ) . "\nFROM (\n" . $this->_compileSelect() . "\n) O2DB_count_all_results" )
			: $this->query( $this->_compileSelect( $this->_count_string . $this->protectIdentifiers( 'numrows' ) ) );

		if ( $reset === TRUE )
		{
			$this->_resetSelect();
		}

		if ( $result->numRows() === 0 )
		{
			return 0;
		}

		$row = $result->row();

		return (int) $row->numrows;
	}

	// --------------------------------------------------------------------

	/**
	 * Get_Where
	 *
	 * Allows the where clause, limit and offset to be added directly
	 *
	 * @param    string $table
	 * @param    string $where
	 * @param    int    $limit
	 * @param    int    $offset
	 *
	 * @return    object
	 */
	public function getWhere( $table = '', $where = NULL, $limit = NULL, $offset = NULL )
	{
		if ( $table !== '' )
		{
			$this->from( $table );
		}

		if ( $where !== NULL )
		{
			$this->where( $where );
		}

		if ( ! empty( $limit ) )
		{
			$this->limit( $limit, $offset );
		}

		$result = $this->query( $this->_compileSelect() );
		$this->_resetSelect();

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert_Batch
	 *
	 * Compiles batch insert strings and runs the queries
	 *
	 * @param    string $table  Table to insert into
	 * @param    array  $set    An associative array of insert values
	 * @param    bool   $escape Whether to escape values and identifiers
	 *
	 * @return    int    Number of rows inserted or FALSE on failure
	 */
	public function insertBatch( $table = '', $set = NULL, $escape = NULL )
	{
		if ( $set !== NULL )
		{
			$this->setInsertBatch( $set, '', $escape );
		}

		if ( count( $this->qb_set ) === 0 )
		{
			// No valid data array. Folds in cases where keys and values did not match up
			throw new Exception( 'You must use the "set" method to update an entry.' );
		}

		if ( $table === '' )
		{
			if ( ! isset( $this->qb_from[ 0 ] ) )
			{
				throw new Exception( 'You must set the database table to be used with your query.' );
			}

			$table = $this->qb_from[ 0 ];
		}

		// Batch this baby
		$affected_rows = 0;
		for ( $i = 0, $total = count( $this->qb_set ); $i < $total; $i += 100 )
		{
			$this->query( $this->_insertBatch( $this->protectIdentifiers( $table, TRUE, $escape, FALSE ), $this->qb_keys, array_slice( $this->qb_set, $i, 100 ) ) );
			$affected_rows += $this->affectedRows();
		}

		$this->_resetWrite();

		return $affected_rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data.
	 *
	 * @param    string $table  Table name
	 * @param    array  $keys   INSERT keys
	 * @param    array  $values INSERT values
	 *
	 * @return    string
	 */
	protected function _insertBatch( $table, $keys, $values )
	{
		return 'INSERT INTO ' . $table . ' (' . implode( ', ', $keys ) . ') VALUES ' . implode( ', ', $values );
	}

	// --------------------------------------------------------------------

	/**
	 * The "set_insert_batch" function.  Allows key/value pairs to be set for batch inserts
	 *
	 * @param    mixed
	 * @param    string
	 * @param    bool
	 *
	 * @return    Query
	 */
	public function setInsertBatch( $key, $value = '', $escape = NULL )
	{
		$key = $this->_objectToArrayBatch( $key );

		if ( ! is_array( $key ) )
		{
			$key = [ $key => $value ];
		}

		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		$keys = array_keys( $this->_objectToArray( current( $key ) ) );
		sort( $keys );

		foreach ( $key as $row )
		{
			$row = $this->_objectToArray( $row );
			if ( count( array_diff( $keys, array_keys( $row ) ) ) > 0 OR count( array_diff( array_keys( $row ), $keys ) ) > 0 )
			{
				// batch function above returns an error on an empty array
				$this->qb_set[] = [ ];

				return;
			}

			ksort( $row ); // puts $row in the same order as our keys

			if ( $escape !== FALSE )
			{
				$clean = [ ];
				foreach ( $row as $value )
				{
					$clean[] = $this->escape( $value );
				}

				$row = $clean;
			}

			$this->qb_set[] = '(' . implode( ',', $row ) . ')';
		}

		foreach ( $keys as $k )
		{
			$this->qb_keys[] = $this->protectIdentifiers( $k, FALSE, $escape );
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get INSERT query string
	 *
	 * Compiles an insert query and returns the sql
	 *
	 * @param    string    the table to insert into
	 * @param    bool      TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return    string
	 */
	public function getCompiledInsert( $table = '', $reset = TRUE )
	{
		if ( $this->_validateInsert( $table ) === FALSE )
		{
			return FALSE;
		}

		$sql = $this->_insert(
			$this->protectIdentifiers(
				$this->qb_from[ 0 ], TRUE, NULL, FALSE
			),
			array_keys( $this->qb_set ),
			array_values( $this->qb_set )
		);

		if ( $reset === TRUE )
		{
			$this->_resetWrite();
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert
	 *
	 * Compiles an insert string and runs the query
	 *
	 * @param         string    the table to insert data into
	 * @param         array     an associative array of insert values
	 * @param    bool $escape   Whether to escape values and identifiers
	 *
	 * @return    object
	 */
	public function insert( $table = '', $set = NULL, $escape = NULL )
	{
		if ( $set !== NULL )
		{
			$this->set( $set, '', $escape );
		}

		if ( $this->_validateInsert( $table ) === FALSE )
		{
			return FALSE;
		}

		$sql = $this->_insertStatement(
			$this->protectIdentifiers(
				$this->qb_from[ 0 ], TRUE, $escape, FALSE
			),
			array_keys( $this->qb_set ),
			array_values( $this->qb_set )
		);

		$this->_resetWrite();

		return $this->query( $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Validate Insert
	 *
	 * This method is used by both insert() and get_compiled_insert() to
	 * validate that the there data is actually being set and that table
	 * has been chosen to be inserted into.
	 *
	 * @param    string    the table to insert data into
	 *
	 * @return    string
	 */
	protected function _validateInsert( $table = '' )
	{
		if ( count( $this->qb_set ) === 0 )
		{
			throw new Exception( 'You must use the "set" method to update an entry.' );
		}

		if ( $table !== '' )
		{
			$this->qb_from[ 0 ] = $table;
		}
		elseif ( ! isset( $this->qb_from[ 0 ] ) )
		{
			throw new Exception( 'You must set the database table to be used with your query.' );
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Replace
	 *
	 * Compiles an replace into string and runs the query
	 *
	 * @param    string    the table to replace data into
	 * @param    array     an associative array of insert values
	 *
	 * @return    object
	 */
	public function replace( $table = '', $set = NULL )
	{
		if ( $set !== NULL )
		{
			$this->set( $set );
		}

		if ( count( $this->qb_set ) === 0 )
		{
			throw new Exception( 'You must use the "set" method to update an entry.' );
		}

		if ( $table === '' )
		{
			if ( ! isset( $this->qb_from[ 0 ] ) )
			{
				throw new Exception( 'You must set the database table to be used with your query.' );
			}

			$table = $this->qb_from[ 0 ];
		}

		$sql = $this->_replace( $this->protectIdentifiers( $table, TRUE, NULL, FALSE ), array_keys( $this->qb_set ), array_values( $this->qb_set ) );

		$this->_resetWrite();

		return $this->query( $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @param    string    the table name
	 * @param    array     the insert keys
	 * @param    array     the insert values
	 *
	 * @return    string
	 */
	protected function _replace( $table, $keys, $values )
	{
		return 'REPLACE INTO ' . $table . ' (' . implode( ', ', $keys ) . ') VALUES (' . implode( ', ', $values ) . ')';
	}

	// --------------------------------------------------------------------

	/**
	 * FROM tables
	 *
	 * Groups tables in FROM clauses if needed, so there is no confusion
	 * about operator precedence.
	 *
	 * Note: This is only used (and overridden) by MySQL and CUBRID.
	 *
	 * @return    string
	 */
	protected function _fromTables()
	{
		return implode( ', ', $this->qb_from );
	}

	// --------------------------------------------------------------------

	/**
	 * Get UPDATE query string
	 *
	 * Compiles an update query and returns the sql
	 *
	 * @param    string    the table to update
	 * @param    bool      TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return    string
	 */
	public function getCompiledUpdate( $table = '', $reset = TRUE )
	{
		// Combine any cached components with the current statements
		$this->_mergeCache();

		if ( $this->_validateUpdate( $table ) === FALSE )
		{
			return FALSE;
		}

		$sql = $this->_update( $this->protectIdentifiers( $this->qb_from[ 0 ], TRUE, NULL, FALSE ), $this->qb_set );

		if ( $reset === TRUE )
		{
			$this->_resetWrite();
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * UPDATE
	 *
	 * Compiles an update string and runs the query.
	 *
	 * @param    string $table
	 * @param    array  $set An associative array of update values
	 * @param    mixed  $where
	 * @param    int    $limit
	 *
	 * @return    object
	 */
	public function update( $table = '', $set = NULL, $where = NULL, $limit = NULL )
	{
		// Combine any cached components with the current statements
		$this->_mergeCache();

		if ( $set !== NULL )
		{
			$this->set( $set );
		}

		if ( $this->_validateUpdate( $table ) === FALSE )
		{
			return FALSE;
		}

		if ( $where !== NULL )
		{
			$this->where( $where );
		}

		if ( ! empty( $limit ) )
		{
			$this->limit( $limit );
		}

		$sql = $this->_updateStatement( $this->protectIdentifiers( $this->qb_from[ 0 ], TRUE, NULL, FALSE ), $this->qb_set );
		$this->_resetWrite();

		return $this->query( $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Validate Update
	 *
	 * This method is used by both update() and get_compiled_update() to
	 * validate that data is actually being set and that a table has been
	 * chosen to be update.
	 *
	 * @param    string    the table to update data on
	 *
	 * @return    bool
	 */
	protected function _validateUpdate( $table = '' )
	{
		if ( count( $this->qb_set ) === 0 )
		{
			throw new Exception( 'You must use the "set" method to update an entry.' );
		}

		if ( $table !== '' )
		{
			$this->qb_from[ 0 ] = $table;
		}
		elseif ( ! isset( $this->qb_from[ 0 ] ) )
		{
			throw new Exception( 'You must set the database table to be used with your query.' );
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param    string    the table to retrieve the results from
	 * @param    array     an associative array of update values
	 * @param    string    the where key
	 *
	 * @return    int    number of rows affected or FALSE on failure
	 */
	public function updateBatch( $table = '', $set = NULL, $index = NULL )
	{
		// Combine any cached components with the current statements
		$this->_mergeCache();

		if ( $index === NULL )
		{
			throw new Exception( 'You must specify an index to match on for batch updates.' );
		}

		if ( $set !== NULL )
		{
			$this->setUpdateBatch( $set, $index );
		}

		if ( count( $this->qb_set ) === 0 )
		{
			throw new Exception( 'You must use the "set" method to update an entry.' );
		}

		if ( $table === '' )
		{
			if ( ! isset( $this->qb_from[ 0 ] ) )
			{
				throw new Exception( 'You must set the database table to be used with your query.' );
			}

			$table = $this->qb_from[ 0 ];
		}

		// Batch this baby
		$affected_rows = 0;
		for ( $i = 0, $total = count( $this->qb_set ); $i < $total; $i += 100 )
		{
			$this->query( $this->_updateBatchStatement( $this->protectIdentifiers( $table, TRUE, NULL, FALSE ), array_slice( $this->qb_set, $i, 100 ), $this->protectIdentifiers( $index ) ) );
			$affected_rows += $this->affectedRows();
			$this->qb_where = [ ];
		}

		$this->_resetWrite();

		return $affected_rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @param    string $table  Table name
	 * @param    array  $values Update data
	 * @param    string $index  WHERE key
	 *
	 * @return    string
	 */
	protected function _updateBatchStatement( $table, $values, $index )
	{
		$ids = [ ];
		foreach ( $values as $key => $val )
		{
			$ids[] = $val[ $index ];

			foreach ( array_keys( $val ) as $field )
			{
				if ( $field !== $index )
				{
					$final[ $field ][] = 'WHEN ' . $index . ' = ' . $val[ $index ] . ' THEN ' . $val[ $field ];
				}
			}
		}

		$cases = '';
		foreach ( $final as $k => $v )
		{
			$cases .= $k . " = CASE \n"
				. implode( "\n", $v ) . "\n"
				. 'ELSE ' . $k . ' END, ';
		}

		$this->where( $index . ' IN(' . implode( ',', $ids ) . ')', NULL, FALSE );

		return 'UPDATE ' . $table . ' SET ' . substr( $cases, 0, -2 ) . $this->_compileWhereHaving( 'qb_where' );
	}

	// --------------------------------------------------------------------

	/**
	 * The "set_update_batch" function.  Allows key/value pairs to be set for batch updating
	 *
	 * @param    array
	 * @param    string
	 * @param    bool
	 *
	 * @return    Query
	 */
	public function setUpdateBatch( $key, $index = '', $escape = NULL )
	{
		$key = $this->_objectToArrayBatch( $key );

		if ( ! is_array( $key ) )
		{
			throw new Exception( 'Batch update required array of data sets' );
		}

		is_bool( $escape ) OR $escape = $this->_protect_identifiers;

		foreach ( $key as $k => $v )
		{
			$index_set = FALSE;
			$clean     = [ ];
			foreach ( $v as $k2 => $v2 )
			{
				if ( $k2 === $index )
				{
					$index_set = TRUE;
				}

				$clean[ $this->protectIdentifiers( $k2, FALSE, $escape ) ] = ( $escape === FALSE ) ? $v2 : $this->escape( $v2 );
			}

			if ( $index_set === FALSE )
			{
				throw new Exception( 'One or more rows submitted for batch updating is missing the specified index.' );
			}

			$this->qb_set[] = $clean;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Empty Table
	 *
	 * Compiles a delete string and runs "DELETE FROM table"
	 *
	 * @param    string    the table to empty
	 *
	 * @return    object
	 */
	public function emptyTable( $table = '' )
	{
		if ( $table === '' )
		{
			if ( ! isset( $this->qb_from[ 0 ] ) )
			{
				throw new Exception( 'You must set the database table to be used with your query.' );
			}

			$table = $this->qb_from[ 0 ];
		}
		else
		{
			$table = $this->protectIdentifiers( $table, TRUE, NULL, FALSE );
		}

		$sql = $this->_delete( $table );
		$this->_resetWrite();

		return $this->query( $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Truncate
	 *
	 * Compiles a truncate string and runs the query
	 * If the database does not support the truncate() command
	 * This function maps to "DELETE FROM table"
	 *
	 * @param    string    the table to truncate
	 *
	 * @return    object
	 */
	public function truncate( $table = '' )
	{
		if ( $table === '' )
		{
			if ( ! isset( $this->qb_from[ 0 ] ) )
			{
				throw new Exception( 'You must set the database table to be used with your query.' );
			}

			$table = $this->qb_from[ 0 ];
		}
		else
		{
			$table = $this->protectIdentifiers( $table, TRUE, NULL, FALSE );
		}

		$sql = $this->_truncateStatement( $table );
		$this->_resetWrite();

		return $this->query( $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 *
	 * If the database does not support the truncate() command,
	 * then this method maps to 'DELETE FROM table'
	 *
	 * @param    string    the table name
	 *
	 * @return    string
	 */
	protected function _truncateStatement( $table )
	{
		return 'TRUNCATE ' . $table;
	}

	// --------------------------------------------------------------------

	/**
	 * Get DELETE query string
	 *
	 * Compiles a delete query string and returns the sql
	 *
	 * @param    string    the table to delete from
	 * @param    bool      TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return    string
	 */
	public function getCompiledDelete( $table = '', $reset = TRUE )
	{
		$this->return_delete_sql = TRUE;
		$sql                     = $this->delete( $table, '', NULL, $reset );
		$this->return_delete_sql = FALSE;

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param    mixed    the table(s) to delete from. String or array
	 * @param    mixed    the where clause
	 * @param    mixed    the limit clause
	 * @param    bool
	 *
	 * @return    mixed
	 */
	public function delete( $table = '', $where = '', $limit = NULL, $reset_data = TRUE )
	{
		// Combine any cached components with the current statements
		$this->_mergeCache();

		if ( $table === '' )
		{
			if ( ! isset( $this->qb_from[ 0 ] ) )
			{
				throw new Exception( 'You must set the database table to be used with your query.' );
			}

			$table = $this->qb_from[ 0 ];
		}
		elseif ( is_array( $table ) )
		{
			foreach ( $table as $single_table )
			{
				$this->delete( $single_table, $where, $limit, $reset_data );
			}

			return;
		}
		else
		{
			$table = $this->protectIdentifiers( $table, TRUE, NULL, FALSE );
		}

		if ( $where !== '' )
		{
			$this->where( $where );
		}

		if ( ! empty( $limit ) )
		{
			$this->limit( $limit );
		}

		if ( count( $this->qb_where ) === 0 )
		{
			throw new Exception( 'Deletes are not allowed unless they contain a "where" or "like" clause.' );
		}

		$sql = $this->_delete( $table );
		if ( $reset_data )
		{
			$this->_resetWrite();
		}

		return ( $this->return_delete_sql === TRUE ) ? $sql : $this->query( $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param    string    the table name
	 *
	 * @return    string
	 */
	protected function _delete( $table )
	{
		return 'DELETE FROM ' . $table . $this->_compileWhereHaving( 'qb_where' )
		. ( $this->qb_limit ? ' LIMIT ' . $this->qb_limit : '' );
	}

	// --------------------------------------------------------------------

	/**
	 * DB Prefix
	 *
	 * Prepends a database prefix if one exists in configuration
	 *
	 * @param    string    the table
	 *
	 * @return    string
	 */
	public function tablePrefix( $table = '' )
	{
		if ( $table === '' )
		{
			throw new Exception( 'A table name is required for that operation.' );
		}

		return $this->table_prefix . $table;
	}

	// --------------------------------------------------------------------

	/**
	 * Set DB Prefix
	 *
	 * Set's the DB Prefix to something new without needing to reconnect
	 *
	 * @param    string    the prefix
	 *
	 * @return    string
	 */
	public function setTablePrefix( $prefix = '' )
	{
		return $this->table_prefix = $prefix;
	}

	// --------------------------------------------------------------------

	/**
	 * Track Aliases
	 *
	 * Used to track SQL statements written with aliased tables.
	 *
	 * @param    string    The table to inspect
	 *
	 * @return    string
	 */
	protected function _trackAliases( $table )
	{
		if ( is_array( $table ) )
		{
			foreach ( $table as $t )
			{
				$this->_trackAliases( $t );
			}

			return;
		}

		// Does the string contain a comma?  If so, we need to separate
		// the string into discreet statements
		if ( strpos( $table, ',' ) !== FALSE )
		{
			return $this->_trackAliases( explode( ',', $table ) );
		}

		// if a table alias is used we can recognize it by a space
		if ( strpos( $table, ' ' ) !== FALSE )
		{
			// if the alias is written with the AS keyword, remove it
			$table = preg_replace( '/\s+AS\s+/i', ' ', $table );

			// Grab the alias
			$table = trim( strrchr( $table, ' ' ) );

			// Store the alias, if it doesn't already exist
			if ( ! in_array( $table, $this->qb_aliased_tables ) )
			{
				$this->qb_aliased_tables[] = $table;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.
	 *
	 * @param    bool $select_override
	 *
	 * @return    string
	 */
	protected function _compileSelect( $select_override = FALSE )
	{
		// Combine any cached components with the current statements
		$this->_mergeCache();

		// Write the "select" portion of the query
		if ( $select_override !== FALSE )
		{
			$sql = $select_override;
		}
		else
		{
			$sql = ( ! $this->qb_distinct ) ? 'SELECT ' : 'SELECT DISTINCT ';

			if ( count( $this->qb_select ) === 0 )
			{
				$sql .= '*';
			}
			else
			{
				// Cycle through the "select" portion of the query and prep each column name.
				// The reason we protect identifiers here rather then in the select() function
				// is because until the user calls the from() function we don't know if there are aliases
				foreach ( $this->qb_select as $key => $val )
				{
					$no_escape               = isset( $this->qb_no_escape[ $key ] ) ? $this->qb_no_escape[ $key ] : NULL;
					$this->qb_select[ $key ] = $this->protectIdentifiers( $val, FALSE, $no_escape );
				}

				$sql .= implode( ', ', $this->qb_select );
			}
		}

		// Write the "FROM" portion of the query
		if ( count( $this->qb_from ) > 0 )
		{
			$sql .= "\nFROM " . $this->_fromTables();
		}

		// Write the "JOIN" portion of the query
		if ( count( $this->qb_join ) > 0 )
		{
			$sql .= "\n" . implode( "\n", $this->qb_join );
		}

		$sql .= $this->_compileWhereHaving( 'qb_where' )
			. $this->_compileGroupBy()
			. $this->_compileWhereHaving( 'qb_having' )
			. $this->_compileOrderBy(); // ORDER BY

		// LIMIT
		if ( $this->qb_limit )
		{
			return $this->_limit( $sql . "\n" );
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Compile WHERE, HAVING statements
	 *
	 * Escapes identifiers in WHERE and HAVING statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of wether
	 * where(), or_where(), having(), or_having are called prior to from(),
	 * join() and dbprefix is added only if needed.
	 *
	 * @param    string $qb_key 'qb_where' or 'qb_having'
	 *
	 * @return    string    SQL statement
	 */
	protected function _compileWhereHaving( $qb_key )
	{
		if ( count( $this->$qb_key ) > 0 )
		{
			for ( $i = 0, $c = count( $this->$qb_key ); $i < $c; $i++ )
			{
				// Is this condition already compiled?
				if ( is_string( $this->{$qb_key}[ $i ] ) )
				{
					continue;
				}
				elseif ( $this->{$qb_key}[ $i ][ 'escape' ] === FALSE )
				{
					$this->{$qb_key}[ $i ] = $this->{$qb_key}[ $i ][ 'condition' ];
					continue;
				}

				// Split multiple conditions
				$conditions = preg_split(
					'/(\s*AND\s+|\s*OR\s+)/i',
					$this->{$qb_key}[ $i ][ 'condition' ],
					-1,
					PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
				);

				for ( $ci = 0, $cc = count( $conditions ); $ci < $cc; $ci++ )
				{
					if ( ( $op = $this->_getOperator( $conditions[ $ci ] ) ) === FALSE
						OR ! preg_match( '/^(\(?)(.*)(' . preg_quote( $op, '/' ) . ')\s*(.*(?<!\)))?(\)?)$/i', $conditions[ $ci ], $matches )
					)
					{
						continue;
					}

					// $matches = array(
					//	0 => '(test <= foo)',	/* the whole thing */
					//	1 => '(',		/* optional */
					//	2 => 'test',		/* the field name */
					//	3 => ' <= ',		/* $op */
					//	4 => 'foo',		/* optional, if $op is e.g. 'IS NULL' */
					//	5 => ')'		/* optional */
					// );

					if ( ! empty( $matches[ 4 ] ) )
					{
						$this->_isLiteral( $matches[ 4 ] ) OR $matches[ 4 ] = $this->protectIdentifiers( trim( $matches[ 4 ] ) );
						$matches[ 4 ] = ' ' . $matches[ 4 ];
					}

					$conditions[ $ci ] = $matches[ 1 ] . $this->protectIdentifiers( trim( $matches[ 2 ] ) )
						. ' ' . trim( $matches[ 3 ] ) . $matches[ 4 ] . $matches[ 5 ];
				}

				$this->{$qb_key}[ $i ] = implode( '', $conditions );
			}

			return ( $qb_key === 'qb_having' ? "\nHAVING " : "\nWHERE " )
			. implode( "\n", $this->$qb_key );
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Compile GROUP BY
	 *
	 * Escapes identifiers in GROUP BY statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of wether
	 * group_by() is called prior to from(), join() and dbprefix is added
	 * only if needed.
	 *
	 * @return    string    SQL statement
	 */
	protected function _compileGroupBy()
	{
		if ( count( $this->qb_group_by ) > 0 )
		{
			for ( $i = 0, $c = count( $this->qb_group_by ); $i < $c; $i++ )
			{
				// Is it already compiled?
				if ( is_string( $this->qb_group_by[ $i ] ) )
				{
					continue;
				}

				$this->qb_group_by[ $i ] = ( $this->qb_group_by[ $i ][ 'escape' ] === FALSE OR $this->_isLiteral( $this->qb_group_by[ $i ][ 'field' ] ) )
					? $this->qb_group_by[ $i ][ 'field' ]
					: $this->protectIdentifiers( $this->qb_group_by[ $i ][ 'field' ] );
			}

			return "\nGROUP BY " . implode( ', ', $this->qb_group_by );
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Compile ORDER BY
	 *
	 * Escapes identifiers in ORDER BY statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of wether
	 * order_by() is called prior to from(), join() and dbprefix is added
	 * only if needed.
	 *
	 * @return    string    SQL statement
	 */
	protected function _compileOrderBy()
	{
		if ( is_array( $this->qb_order_by ) && count( $this->qb_order_by ) > 0 )
		{
			for ( $i = 0, $c = count( $this->qb_order_by ); $i < $c; $i++ )
			{
				if ( $this->qb_order_by[ $i ][ 'escape' ] !== FALSE && ! $this->_isLiteral( $this->qb_order_by[ $i ][ 'field' ] ) )
				{
					$this->qb_order_by[ $i ][ 'field' ] = $this->protectIdentifiers( $this->qb_order_by[ $i ][ 'field' ] );
				}

				$this->qb_order_by[ $i ] = $this->qb_order_by[ $i ][ 'field' ] . $this->qb_order_by[ $i ][ 'direction' ];
			}

			return $this->qb_order_by = "\nORDER BY " . implode( ', ', $this->qb_order_by );
		}
		elseif ( is_string( $this->qb_order_by ) )
		{
			return $this->qb_order_by;
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param    object
	 *
	 * @return    array
	 */
	protected function _objectToArray( $object )
	{
		if ( ! is_object( $object ) )
		{
			return $object;
		}

		$array = [ ];
		foreach ( get_object_vars( $object ) as $key => $val )
		{
			// There are some built in keys we need to ignore for this conversion
			if ( ! is_object( $val ) && ! is_array( $val ) && $key !== '_parent_name' )
			{
				$array[ $key ] = $val;
			}
		}

		return $array;
	}

	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param    object
	 *
	 * @return    array
	 */
	protected function _objectToArrayBatch( $object )
	{
		if ( ! is_object( $object ) )
		{
			return $object;
		}

		$array  = [ ];
		$out    = get_object_vars( $object );
		$fields = array_keys( $out );

		foreach ( $fields as $val )
		{
			// There are some built in keys we need to ignore for this conversion
			if ( $val !== '_parent_name' )
			{
				$i = 0;
				foreach ( $out[ $val ] as $data )
				{
					$array[ $i++ ][ $val ] = $data;
				}
			}
		}

		return $array;
	}

	// --------------------------------------------------------------------

	/**
	 * Start Cache
	 *
	 * Starts QB caching
	 *
	 * @return    Query
	 */
	public function startCache()
	{
		$this->qb_caching = TRUE;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Stop Cache
	 *
	 * Stops QB caching
	 *
	 * @return    Query
	 */
	public function stopCache()
	{
		$this->qb_caching = FALSE;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Flush Cache
	 *
	 * Empties the QB cache
	 *
	 * @return    Query
	 */
	public function flushCache()
	{
		$this->_resetRun(
			[
				'qb_cache_select'    => [ ],
				'qb_cache_from'      => [ ],
				'qb_cache_join'      => [ ],
				'qb_cache_where'     => [ ],
				'qb_cache_group_by'  => [ ],
				'qb_cache_having'    => [ ],
				'qb_cache_order_by'  => [ ],
				'qb_cache_set'       => [ ],
				'qb_cache_exists'    => [ ],
				'qb_cache_no_escape' => [ ],
			] );

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Merge Cache
	 *
	 * When called, this function merges any cached QB arrays with
	 * locally called ones.
	 *
	 * @return    void
	 */
	protected function _mergeCache()
	{
		if ( count( $this->qb_cache_exists ) === 0 )
		{
			return;
		}
		elseif ( in_array( 'select', $this->qb_cache_exists, TRUE ) )
		{
			$qb_no_escape = $this->qb_cache_no_escape;
		}

		foreach ( array_unique( $this->qb_cache_exists ) as $val ) // select, from, etc.
		{
			$qb_variable  = 'qb_' . $val;
			$qb_cache_var = 'qb_cache_' . $val;
			$qb_new       = $this->$qb_cache_var;

			for ( $i = 0, $c = count( $this->$qb_variable ); $i < $c; $i++ )
			{
				if ( ! in_array( $this->{$qb_variable}[ $i ], $qb_new, TRUE ) )
				{
					$qb_new[] = $this->{$qb_variable}[ $i ];
					if ( $val === 'select' )
					{
						$qb_no_escape[] = $this->qb_no_escape[ $i ];
					}
				}
			}

			$this->$qb_variable = $qb_new;
			if ( $val === 'select' )
			{
				$this->qb_no_escape = $qb_no_escape;
			}
		}

		// If we are "protecting identifiers" we need to examine the "from"
		// portion of the query to determine if there are any aliases
		if ( $this->_protectIdentifiers === TRUE && count( $this->qb_cache_from ) > 0 )
		{
			$this->_trackAliases( $this->qb_from );
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Is literal
	 *
	 * Determines if a string represents a literal value or a field name
	 *
	 * @param    string $str
	 *
	 * @return    bool
	 */
	protected function _isLiteral( $str )
	{
		$str = trim( $str );

		if ( empty( $str ) OR ctype_digit( $str ) OR (string) (float) $str === $str OR in_array( strtoupper( $str ), [ 'TRUE', 'FALSE' ], TRUE ) )
		{
			return TRUE;
		}

		static $_str;

		if ( empty( $_str ) )
		{
			$_str = ( $this->_escape_character !== '"' )
				? [ '"', "'" ] : [ "'" ];
		}

		return in_array( $str[ 0 ], $_str, TRUE );
	}

	// --------------------------------------------------------------------

	/**
	 * Reset Query Builder values.
	 *
	 * Publicly-visible method to reset the QB values.
	 *
	 * @return    Query
	 */
	public function resetQuery()
	{
		$this->_resetSelect();
		$this->_resetWrite();

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Resets the query builder values.  Called by the get() function
	 *
	 * @param    array    An array of fields to reset
	 *
	 * @return    void
	 */
	protected function _resetRun( $qb_reset_items )
	{
		foreach ( $qb_reset_items as $item => $default_value )
		{
			$this->$item = $default_value;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Resets the query builder values.  Called by the get() function
	 *
	 * @return    void
	 */
	protected function _resetSelect()
	{
		$this->_resetRun(
			[
				'qb_select'         => [ ],
				'qb_from'           => [ ],
				'qb_join'           => [ ],
				'qb_where'          => [ ],
				'qb_group_by'       => [ ],
				'qb_having'         => [ ],
				'qb_order_by'       => [ ],
				'qb_aliased_tables' => [ ],
				'qb_no_escape'      => [ ],
				'qb_distinct'       => FALSE,
				'qb_limit'          => FALSE,
				'qb_offset'         => FALSE,
			] );
	}

	// --------------------------------------------------------------------

	/**
	 * Resets the query builder "write" values.
	 *
	 * Called by the insert() update() insert_batch() update_batch() and delete() functions
	 *
	 * @return    void
	 */
	protected function _resetWrite()
	{
		$this->_resetRun(
			[
				'qb_set'      => [ ],
				'qb_from'     => [ ],
				'qb_join'     => [ ],
				'qb_where'    => [ ],
				'qb_order_by' => [ ],
				'qb_keys'     => [ ],
				'qb_limit'    => FALSE,
			] );
	}
}