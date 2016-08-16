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

use O2System\DB\Metadata\Result;
use O2System\DB\Metadata\Result\Write;
use O2System\DB\QueryBuilderException;

abstract class QueryInterface extends ConfigInterface
{
	/**
	 * Record Table
	 *
	 * var string
	 */
	public $_table;

	/**
	 * Record Table Aliases
	 *
	 * var string
	 */
	public $_table_aliases = [ ];

	/**
	 * Record Union
	 *
	 * @var array
	 */
	public $_union = [ ];

	/**
	 * Record Select Into
	 *
	 * var string
	 */
	public $_into;

	/**
	 * Record Select
	 *
	 * @var array
	 */
	public $_select = [ ];

	/**
	 * Record From
	 *
	 * @var array
	 */
	public $_from = [ ];

	/**
	 * Record Join
	 *
	 * var array
	 */
	public $_join = [ ];

	/**
	 * Record Where Clauses
	 *
	 * var array
	 */
	public $_where = [ ];

	/**
	 * Record Or Where Clauses
	 *
	 * var array
	 */
	public $_or_where = [ ];

	/**
	 * Record Where Clauses
	 *
	 * var array
	 */
	public $_like = [ ];

	/**
	 * Record Or Where Clauses
	 *
	 * var array
	 */
	public $_or_like = [ ];

	/**
	 * Record Where Having Clauses
	 *
	 * var array
	 */
	public $_having = [ ];

	/**
	 * Record Group By
	 *
	 * var array
	 */
	public $_group_by = [ ];

	/**
	 * Record Group By
	 *
	 * var array
	 */
	public $_order_by = [ ];

	/**
	 * Record Limit
	 *
	 * @var bool|int
	 */
	public $_limit = FALSE;

	/**
	 * Record Offset
	 *
	 * @var bool|int
	 */
	public $_offset = FALSE;

	/**
	 * Record Sets
	 *
	 * @var array
	 */
	public $_sets = [ ];

	/**
	 * Record Binds
	 *
	 * @var array
	 */
	public $_binds = [ ];

	/**
	 * Record Distinct Flag
	 *
	 * @var bool
	 */
	public $_is_distinct = FALSE;

	/**
	 * Record Union All Flag
	 *
	 * @var bool
	 */
	public $_is_union_all = FALSE;

	/**
	 * Is Grouped Flag
	 *
	 * @var bool
	 */
	public $_is_grouped = FALSE;

	/**
	 * SQL String
	 *
	 * @var string
	 */
	public $_string;

	/**
	 * SQL String
	 *
	 * @var string
	 */
	public $_compiled_string;

	//--------------------------------------------------------------------

	/**
	 * Clone
	 *
	 * @internal call by PHP automatically when perform
	 *           clone $builder
	 *
	 * @return QueryInterface
	 */
	public function __clone()
	{
		$clone = $this;

		return $clone;
	}

	//--------------------------------------------------------------------

	/**
	 * Select
	 *
	 * Add SELECT SQL statement portions into Query Builder
	 *
	 * @param string|array     $field    String of field name
	 *                                   Array list of string field names
	 *                                   Array list of QueryInterface
	 * @param null|string|bool $alias    Null
	 *                                   String of alias field name
	 *                                   Boolean of UNION ALL FLAG (TRUE|FALSE)
	 *
	 * @return $this
	 */
	public function select( $field = '*', $alias = NULL )
	{
		if ( is_array( $field ) AND is_bool( $alias ) )
		{
			foreach ( $field as $index => $union )
			{
				if ( $union instanceof QueryInterface )
				{
					foreach ( $union->_binds as $parameter => $value )
					{
						if ( $this->isBindExists( $parameter ) )
						{
							$replaced_originals[]  = $parameter;
							$replaced_parameters[] = $parameter = str_replace( ':', ':union_' . $index, $parameter );
						}

						$this->addBind( $parameter, $value );
					}

					$sql = $union->getSQLString( TRUE );

					if ( isset( $replaced_originals ) AND isset( $replaced_parameters ) )
					{
						$sql = str_replace( $replaced_originals, $replaced_parameters, $sql );
					}

					$this->_union[] = $sql;
				}
			}

			$this->_is_union_all = (bool) $alias;
		}
		elseif ( is_string( $field ) AND is_null( $alias ) )
		{
			$field = rtrim( $field, ',' );

			if ( strpos( $field, ',' ) !== FALSE )
			{
				$field = explode( ',', $field );
				$field = array_map( 'trim', $field );

				foreach ( $field as $field_name => $field_alias )
				{
					if ( is_numeric( $field_name ) )
					{
						if ( strpos( $field_alias, ' AS ' ) !== FALSE )
						{
							$x_field_alias = explode( ' AS ', $field_alias );
							$x_field_alias = array_map( 'trim', $x_field_alias );

							@list( $field_name, $field_alias ) = $x_field_alias;
						}
						elseif ( strpos( $field_alias, ' as ' ) !== FALSE )
						{
							$x_field_alias = explode( ' as ', $field_alias );
							$x_field_alias = array_map( 'trim', $x_field_alias );

							@list( $field_name, $field_alias ) = $x_field_alias;
						}
						else
						{
							$field_name  = trim( $field_alias );
							$field_alias = NULL;
						}

						$field_name = $this->_protectIdentifiers( $field_name, TRUE );

						if ( ! array_key_exists( $field_name, $this->_select ) )
						{
							$this->_select[ $field_name ] = empty( $field_alias ) ? NULL : $field_alias;
						}
					}
					elseif ( is_string( $field_name ) )
					{
						$field_name = $this->_protectIdentifiers( $field_name, TRUE );

						if ( ! array_key_exists( $field_name, $this->_select ) )
						{
							$this->_select[ $field_name ] = empty( $field_alias ) ? NULL : $field_alias;
						}
					}
				}
			}
			elseif ( strpos( $field, 'AS' ) !== FALSE )
			{
				$x_field = explode( ' AS ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field, $alias ) = $x_field;

				$field = $this->_protectIdentifiers( $field, TRUE );

				if ( ! array_key_exists( $field, $this->_select ) )
				{
					$this->_select[ $field ] = empty( $alias ) ? NULL : $alias;
				}
			}
			elseif ( strpos( $field, ' as' ) !== FALSE )
			{
				$x_field = explode( ' as ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field, $alias ) = $x_field;

				$field = $this->_protectIdentifiers( $field, TRUE );

				if ( ! array_key_exists( $field, $this->_select ) )
				{
					$this->_select[ $field ] = empty( $alias ) ? NULL : $alias;
				}
			}
			else
			{
				$field = $this->_protectIdentifiers( trim( $field ), TRUE );

				if ( ! array_key_exists( $field, $this->_select ) )
				{
					$this->_select[ $field ] = NULL;
				}
			}
		}
		elseif ( is_array( $field ) AND is_null( $alias ) )
		{
			foreach ( $field as $field_name => $field_alias )
			{
				if ( is_string( $field_name ) AND $field_alias instanceof QueryInterface )
				{
					foreach ( $field_alias->_binds as $parameter => $value )
					{
						$value = trim( $value, '\'' );

						if ( $this->isBindExists( $parameter ) )
						{
							$replaced_originals[]  = $parameter;
							$replaced_parameters[] = $parameter = str_replace( ':', ':subquery_' . $field_name, $parameter );
						}

						$this->addBind( $parameter, $value );
					}

					$sql = $field_alias->getSQLString( TRUE );

					if ( isset( $replaced_originals ) AND isset( $replaced_parameters ) )
					{
						$sql = str_replace( $replaced_originals, $replaced_parameters, $sql );
					}

					$this->_select[ '( ' . $sql . ' )' ] = $this->_protectIdentifiers( $field_name );
				}
				elseif ( is_numeric( $field_name ) )
				{
					if ( strpos( $field_alias, ' AS ' ) !== FALSE )
					{
						$x_field_alias = explode( ' AS ', $field_alias );
						$x_field_alias = array_map( 'trim', $x_field_alias );

						@list( $field_name, $field_alias ) = $x_field_alias;
					}
					elseif ( strpos( $field_alias, ' as ' ) !== FALSE )
					{
						$x_field_alias = explode( ' as ', $field_alias );
						$x_field_alias = array_map( 'trim', $x_field_alias );

						@list( $field_name, $field_alias ) = $x_field_alias;
					}
					else
					{
						$field_name  = trim( $field_alias );
						$field_alias = NULL;
					}

					if ( strpos( $field_name, ',' ) !== FALSE AND strpos( $field_name, '+' ) === FALSE )
					{
						$field_name = explode( ',', $field_name );
						$field_name = array_map( 'trim', $field_name );

						$protected_fields = [ ];
						$i                = 0;
						foreach ( $field_name as $field_column_name )
						{
							$protected_fields[] = $this->escape( ( $i > 0 ? '+' : '' ) . $field_column_name . ( $i < count( $field_name ) - 1 ? '+' : '' ) );
							$i++;
						}

						$field_name = implode( ', ', $protected_fields );
					}
					else
					{
						$field_name = $this->_protectIdentifiers( $field_name, TRUE );
					}

					if ( ! array_key_exists( $field_name, $this->_select ) )
					{
						$this->_select[ $field_name ] = empty( $field_alias ) ? NULL : $field_alias;
					}
				}
				elseif ( is_string( $field_name ) AND strpos( $field_name, ',' ) !== FALSE )
				{
					$field_name = explode( ',', $field_name );
					$field_name = array_map( 'trim', $field_name );

					$protected_fields = [ ];
					$i                = 0;
					foreach ( $field_name as $field_column_name )
					{
						$protected_fields[] = $this->escape( ( $i > 0 ? '+' : '' ) . $field_column_name . ( $i < count( $field_name ) - 1 ? '+' : '' ) );
						$i++;
					}

					$protected_fields = implode( ', ', $protected_fields );

					$this->_select[ $protected_fields ] = $field_alias;

				}
				elseif ( is_string( $field_name ) )
				{
					$field_name = $this->_protectIdentifiers( $field_name, TRUE );

					if ( ! array_key_exists( $field_name, $this->_select ) )
					{
						$this->_select[ $field_name ] = empty( $field_alias ) ? NULL : $field_alias;
					}
				}
			}
		}
		elseif ( is_array( $field ) AND is_string( $alias ) AND $alias !== '' )
		{
			$protected_fields = [ ];
			$i                = 0;
			foreach ( $field as $field_name )
			{
				$protected_fields[] = $this->escape( ( $i > 0 ? '+' : '' ) . $field_name . ( $i < count( $field ) - 1 ? '+' : '' ) );
				$i++;
			}

			$protected_fields = implode( ', ', $protected_fields );

			$this->_select[ $protected_fields ] = $alias;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Is Bind Exists
	 *
	 * Performing validation whether binding parameter already bind at Query Builder.
	 *
	 * @param  string $parameter
	 *
	 * @return bool
	 */
	public function isBindExists( $parameter )
	{
		return (bool) array_key_exists( $parameter, $this->_binds );
	}

	//--------------------------------------------------------------------

	/**
	 * Add Bind
	 *
	 * Bind parameter and value into Query Builder
	 *
	 * @param string $parameter Binding key parameter
	 * @param mixed  $value     Binding value
	 * @param string $prefix    Binding key parameter prefix
	 *
	 * @return QueryInterface
	 */
	public function addBind( $parameter, $value, $prefix = '' )
	{
		$parameter = $this->_prepareBindParameter( $parameter, $prefix );

		if ( $this->isBindExists( $parameter ) === FALSE )
		{
			$this->_binds[ $parameter ] = $this->escape( $value );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Prepare Bind Parameter
	 *
	 * @param string $parameter Binding key parameter
	 * @param string $prefix    Binding key parameter prefix
	 *
	 * @return string
	 */
	protected function _prepareBindParameter( $parameter, $prefix = '' )
	{
		$prefix    = ':' . rtrim( strtolower( $prefix ), '_' ) . '_';
		$parameter = str_replace( '.', '_', $parameter );
		$parameter = strtolower( $parameter );
		$parameter = preg_replace( "/[^A-Za-z0-9_]/", '', $parameter );

		return camelcase( $prefix . $parameter );
	}

	//--------------------------------------------------------------------

	/**
	 * Get SQL String
	 *
	 * Perform SQL string compiler from Query Builder.
	 *
	 * @param bool $binds Whether compiles bindings parameters or not
	 * @param bool $reset Whether reset the Query Builder or not
	 *
	 * @uses    QueryInterface::_compileSelectSQLStatement()
	 * @uses    QueryInterface::_compileFromSQLStatement()
	 * @uses    QueryInterface::_compileJoinSQLStatement()
	 * @uses    QueryInterface::_compileWhereSQLStatement()
	 * @uses    QueryInterface::_compileLikeSQLStatement()
	 * @uses    QueryInterface::_compileGroupBySQLStatement()
	 * @uses    QueryInterface::_compileHavingSQLStatement()
	 * @uses    QueryInterface::_compileOrderBySQLStatement()
	 * @uses    QueryInterface::_compileLimitSQLStatement()
	 *
	 * @access  public
	 * @return  string
	 */
	public function getSQLString( $binds = FALSE, $reset = FALSE )
	{
		if ( isset( $this->_string ) )
		{
			$sql_string = $this->_string;
		}
		else
		{
			$statements = [ ];
			foreach ( static::$sqlStatementsSequence as $compile_function_name )
			{
				$statements[] = call_user_func( [ &$this, '_compile' . studlycapcase( $compile_function_name . 'sqlStatement' ) ] );
			}

			$statement = array_filter( $statements );

			$sql_string = $this->_string = implode( static::$newLineString, $statement );
		}

		if ( $binds === TRUE )
		{
			foreach ( $this->_binds as $key => $value )
			{
				$sql_string = preg_replace( '/:\b' . ltrim( $key, ':' ) . '\b/i', $this->escape( $value ), $sql_string );
			}

			if ( $reset === TRUE )
			{
				$this->resetAll();
			}

			return $this->_compiled_string = $sql_string;
		}

		if ( $reset === TRUE )
		{
			$this->resetAll();
		}

		return $sql_string;
	}

	//--------------------------------------------------------------------

	/**
	 * Reset All
	 *
	 * Perform reset all Query Builder objects.
	 *
	 * @access  public
	 * @return  void
	 */
	public function resetAll()
	{
		$this->_resetExec(
			[
				'table'           => NULL,
				'table_aliases'   => [ ],
				'union'           => [ ],
				'select'          => [ ],
				'from'            => [ ],
				'join'            => [ ],
				'where'           => [ ],
				'or_where'        => [ ],
				'like'            => [ ],
				'or_like'         => [ ],
				'having'          => [ ],
				'or_having'       => [ ],
				'group_by'        => [ ],
				'order_by'        => [ ],
				'limit'           => FALSE,
				'offset'          => FALSE,
				'binds'           => [ ],
				'is_distinct'     => FALSE,
				'is_union_all'    => FALSE,
				'is_grouped'      => FALSE,
				'string'          => NULL,
				'compiled_string' => NULL,
			] );
	}

	//--------------------------------------------------------------------

	/**
	 * Reset EXEC
	 *
	 * Perform reset Query Builder objects execution.
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function _resetExec( $reset_items )
	{
		foreach ( $reset_items as $item => $default_value )
		{
			$this->{'_' . $item} = $default_value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * SELECT INTO
	 *
	 * Add SELECT INTO SQL statement portions into Query Builder
	 *
	 * @param string      $table    Table name
	 * @param string|null $database Other database name
	 *
	 * @return $this
	 */
	public function into( $table, $database = NULL )
	{
		$this->_into = $this->_protectIdentifiers( $table ) . empty( $database ) ? '' : ' IN ' . $this->escape( $database );

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * AVG()
	 *
	 * Add SELECT AVG(field) AS alias statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function avg( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareAggregateStatement( $field, $alias, $is_return_statement, 'AVG' );
	}

	//--------------------------------------------------------------------

	/**
	 * Prepare string of SQL Aggregate Functions statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 * @param string $type                AVG|COUNT|FIRST|LAST|MAX|MIN|SUM
	 *
	 * @return QueryInterface
	 */
	protected function _prepareAggregateStatement( $field = '', $alias = '', $is_return_statement = FALSE, $type = '' )
	{
		if ( array_key_exists( $type, static::$sqlAggregateFunctions ) )
		{
			if ( empty( $alias ) OR $alias === '' )
			{
				if ( strpos( $field, '.' ) !== FALSE )
				{
					$x_field = explode( '.', $field );
					$x_field = array_map( 'trim', $x_field );

					$alias = @$x_field[ 1 ];
				}
			}

			if ( $field !== '*' )
			{
				$field = $this->_protectIdentifiers( $field, TRUE );
			}

			$statement = sprintf( static::$sqlAggregateFunctions[ $type ], $field );

			if ( $is_return_statement === TRUE )
			{
				return $statement . $alias === '' ? '' : ' AS ' . $this->_protectIdentifiers( $alias, FALSE );
			}

			$this->_select[ $statement ] = $alias === '' ? NULL : $alias;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * FIRST()
	 *
	 * Add SELECT FIRST(field) AS alias statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function first( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareAggregateStatement( $field, $alias, $is_return_statement, 'FIRST' );
	}

	//--------------------------------------------------------------------

	/**
	 * LAST()
	 *
	 * Add SELECT LAST(field) AS alias statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function last( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareAggregateStatement( $field, $alias, $is_return_statement, 'LAST' );
	}

	//--------------------------------------------------------------------

	/**
	 * MAX()
	 *
	 * Add SELECT MAX(field) AS alias statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function max( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareAggregateStatement( $field, $alias, $is_return_statement, 'MAX' );
	}

	//--------------------------------------------------------------------

	/**
	 * MIN()
	 *
	 * Add SELECT MIN(field) AS alias statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function min( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareAggregateStatement( $field, $alias, $is_return_statement, 'MIN' );
	}

	//--------------------------------------------------------------------

	/**
	 * SUM()
	 *
	 * Add SELECT SUM(field) AS alias statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function sum( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareAggregateStatement( $field, $alias, $is_return_statement, 'SUM' );
	}

	//--------------------------------------------------------------------

	/**
	 * UCASE()
	 *
	 * Add SELECT UCASE(field) AS alias statement
	 *
	 * @see http://www.w3schools.com/sql/sql_func_ucase.asp
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function ucase( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareScalarStatement( $field, $alias, $is_return_statement, 'UCASE' );
	}

	//--------------------------------------------------------------------

	/**
	 * Prepare string of SQL Scalar Functions statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 * @param string $type                UCASE|LCASE|MID|LEN|ROUND|FORMAT
	 *
	 * @return QueryInterface
	 */
	protected function _prepareScalarStatement( $field = '', $alias = '', $is_return_statement = FALSE, $type = '' )
	{
		if ( array_key_exists( $type, static::$sqlScalarFunctions ) )
		{
			if ( $type === 'FORMAT' )
			{
				list( $field, $format ) = $field;

				if ( empty( $alias ) OR $alias === '' )
				{
					if ( strpos( $field, '.' ) !== FALSE )
					{
						$x_field = explode( '.', $field );
						$x_field = array_map( 'trim', $x_field );

						$alias = @$x_field[ 1 ];
					}
				}

				$field = $this->_protectIdentifiers( $field, TRUE );
				$alias = $this->_protectIdentifiers( $alias, FALSE );

				$statement = sprintf( static::$sqlScalarFunctions[ 'FORMAT' ], $field, $format, $alias );
			}
			else
			{
				if ( empty( $alias ) OR $alias === '' )
				{
					if ( strpos( $field, '.' ) !== FALSE )
					{
						$x_field = explode( '.', $field );
						$x_field = array_map( 'trim', $x_field );

						$alias = @$x_field[ 1 ];
					}
				}

				if ( $field !== '*' )
				{
					$field = $this->_protectIdentifiers( $field, TRUE );
				}

				$alias = $this->_protectIdentifiers( $alias, FALSE );

				$statement = sprintf( static::$sqlScalarFunctions[ $type ], $field, $alias );
			}

			if ( $is_return_statement === TRUE )
			{
				return $statement;
			}

			$this->_select[] = $statement;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * LCASE()
	 *
	 * Add SELECT LCASE(field) AS alias statement
	 *
	 * @see http://www.w3schools.com/sql/sql_func_lcase.asp
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function lcase( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareScalarStatement( $field, $alias, $is_return_statement, 'LCASE' );
	}

	//--------------------------------------------------------------------

	/**
	 * MID()
	 *
	 * Add SELECT MID(field) AS alias statement
	 *
	 * @see http://www.w3schools.com/sql/sql_func_mid.asp
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function mid( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareScalarStatement( $field, $alias, $is_return_statement, 'MID' );
	}

	//--------------------------------------------------------------------

	/**
	 * LEN()
	 *
	 * Add SELECT LEN(field) AS alias statement
	 *
	 * @see http://www.w3schools.com/sql/sql_func_len.asp
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function len( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareScalarStatement( $field, $alias, $is_return_statement, 'LEN' );
	}

	//--------------------------------------------------------------------

	/**
	 * ROUND()
	 *
	 * Add SELECT ROUND(field) AS alias statement
	 *
	 * @see http://www.w3schools.com/sql/sql_func_round.asp
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function round( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareScalarStatement( $field, $alias, $is_return_statement, 'ROUND' );
	}

	//--------------------------------------------------------------------

	/**
	 * FORMAT()
	 *
	 * Add SELECT FORMAT(field, format) AS alias statement
	 *
	 * @see http://www.w3schools.com/sql/sql_func_format.asp
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function format( $field, $format, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareScalarStatement( [ $field => $format ], $alias, $is_return_statement, 'FORMAT' );
	}

	//--------------------------------------------------------------------

	/**
	 * NOW
	 *
	 * Add / Create SELECT NOW() SQL statement
	 *
	 * @param bool $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function now( $is_return_statement = FALSE )
	{
		if ( isset( static::$sqlDateFunctions[ 'NOW' ] ) )
		{
			if ( $is_return_statement )
			{
				return static::$sqlDateFunctions[ 'NOW' ];
			}

			$this->_select[ static::$sqlDateFunctions[ 'NOW' ] ] = NULL;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * CURRENT
	 *
	 * Add / Create SELECT CURDATE() / CURTIME() SQL statement
	 *
	 * @see http://www.w3schools.com/sql/func_curdate.asp
	 *      http://www.w3schools.com/sql/func_curtime.asp
	 *
	 * @param string $type                DATE|TIME
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function current( $type = 'DATE', $is_return_statement = FALSE )
	{
		if ( isset( static::$sqlDateFunctions[ 'CURRENT_DATE' ] ) AND isset( static::$sqlDateFunctions[ 'CURRENT_TIME' ] ) )
		{
			switch ( strtoupper( $type ) )
			{
				case 'DATE':
					if ( $is_return_statement )
					{
						return static::$sqlDateFunctions[ 'CURRENT_DATE' ];
					}

					$this->_select[ static::$sqlDateFunctions[ 'CURRENT_DATE' ] ] = NULL;
					break;

				case 'TIME':
					if ( $is_return_statement )
					{
						return static::$sqlDateFunctions[ 'CURRENT_TIME' ];
					}

					$this->_select[ static::$sqlDateFunctions[ 'CURRENT_TIME' ] ] = NULL;
					break;
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Extract
	 *
	 * Add / Create SELECT EXTRACT(unit FROM field) AS alias SQL statement
	 *
	 * @see http://www.w3schools.com/sql/func_extract.asp
	 *
	 * @param string $field               Field name
	 * @param string $unit                UPPERCASE unit value
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function extract( $field, $unit, $is_return_statement = FALSE )
	{
		$unit = strtoupper( $unit );

		if ( isset( static::$sqlDateFunctions[ 'DATE_EXTRACT' ] ) AND in_array( $unit, static::$sqlDateTypes ) )
		{
			if ( is_array( $field ) )
			{
				$field_name  = key( $field );
				$field_alias = $field[ $field_name ];
			}
			elseif ( strpos( $field, ' AS ' ) !== FALSE )
			{
				$x_field = explode( ' AS ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field_name, $field_alias ) = $x_field;
			}
			elseif ( strpos( $field, ' as ' ) !== FALSE )
			{
				$x_field = explode( ' as ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field_name, $field_alias ) = $x_field;
			}

			if ( strpos( $field_name, '.' ) !== FALSE AND empty( $field_alias ) )
			{
				$x_field_name = explode( '.', $field_name );
				$x_field_name = array_map( 'trim', $x_field_name );

				$field_alias = end( $x_field_name );
			}

			$statement = sprintf( static::$sqlDateFunctions[ 'DATE_EXTRACT' ], $unit, $this->_protectIdentifiers( $field_name ) );

			if ( $is_return_statement )
			{
				return $statement . ( empty( $field_alias ) ? '' : ' AS ' . $this->_protectIdentifiers( $field_alias ) );
			}

			$this->_select[ $statement ] = $field_alias;
		}

		return $this;
	}

	/**
	 * DATE
	 *
	 * Add / Create SELECT DATE(field) AS alias SQL statement
	 *
	 * @see http://www.w3schools.com/sql/func_date.asp
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field name alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function date( $field, $alias = NULL, $is_return_statement = FALSE )
	{
		if ( isset( static::$sqlDateFunctions[ 'DATE' ] ) )
		{
			if ( is_array( $field ) )
			{
				$field_name  = key( $field );
				$field_alias = $field[ $field_name ];
			}
			elseif ( strpos( $field, ' AS ' ) !== FALSE )
			{
				$x_field = explode( ' AS ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field_name, $field_alias ) = $x_field;
			}
			elseif ( strpos( $field, ' as ' ) !== FALSE )
			{
				$x_field = explode( ' as ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field_name, $field_alias ) = $x_field;
			}

			if ( strpos( $field_name, '.' ) !== FALSE AND empty( $field_alias ) )
			{
				$x_field_name = explode( '.', $field_name );
				$x_field_name = array_map( 'trim', $x_field_name );

				$field_alias = end( $x_field_name );
			}

			$field_alias = isset( $alias ) ? $alias : $field_alias;

			$statement = sprintf( static::$sqlDateFunctions[ 'DATE' ], $this->_protectIdentifiers( $field_name ) );

			if ( $is_return_statement )
			{
				return $statement . ( empty( $field_alias ) ? '' : ' AS ' . $this->_protectIdentifiers( $field_alias ) );
			}

			$this->_select[ $statement ] = $alias;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * DATE ADD
	 *
	 * Add / Create SELECT DATE_ADD(field, INTERVAL expression type) AS alias SQL statement
	 *
	 * @see http://www.w3schools.com/sql/func_date.asp
	 *
	 * @param string      $field               Field name
	 * @param int         $expression          Number of interval expression
	 * @param string|null $type                UPPERCASE interval expression type
	 * @param bool        $is_return_statement Whether including into select active record or returning string
	 *
	 * @throws QueryBuilderException
	 * @return QueryInterface|string
	 */
	public function dateAdd( $field, $expression, $type = NULL, $is_return_statement = FALSE )
	{
		if ( $this->_hasDateType( $expression ) )
		{
			$x_expression = explode( ' ', $expression );
			$x_expression = array_map( 'trim', $x_expression );

			@list( $expression, $type ) = $x_expression;
		}

		if ( empty( $type ) )
		{
			throw new QueryBuilderException( 'Undefined DATE add expression type' );
		}

		if ( isset( static::$sqlDateFunctions[ 'DATE_ADD' ] ) AND in_array( $type, static::$sqlDateTypes ) )
		{
			if ( is_array( $field ) )
			{
				$field_name  = key( $field );
				$field_alias = $field[ $field_name ];
			}
			elseif ( strpos( $field, ' AS ' ) !== FALSE )
			{
				$x_field = explode( ' AS ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field_name, $field_alias ) = $x_field;
			}
			elseif ( strpos( $field, ' as ' ) !== FALSE )
			{
				$x_field = explode( ' as ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field_name, $field_alias ) = $x_field;
			}

			if ( strpos( $field_name, '.' ) !== FALSE AND empty( $field_alias ) )
			{
				$x_field_name = explode( '.', $field_name );
				$x_field_name = array_map( 'trim', $x_field_name );

				$field_alias = end( $x_field_name );
			}

			$statement = sprintf( static::$sqlDateFunctions[ 'DATE_ADD' ], $this->_protectIdentifiers( $field_name ), (int) $expression, $type );

			if ( $is_return_statement )
			{
				return $statement . ( empty( $field_alias ) ? '' : ' AS ' . $this->_protectIdentifiers( $field_alias ) );
			}

			$this->_select[ $statement ] = $field_alias;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Has Date Type
	 *
	 * Validate whether the string has an SQL Date unit type
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	protected function _hasDateType( $string )
	{
		return (bool) preg_match(
			'/(' . implode( '|\s', static::$sqlDateTypes ) . '\s*\(|\s)/i',
			trim( $string ) );
	}

	// ------------------------------------------------------------------------

	/**
	 * DATE SUBSTRACTS
	 *
	 * Add / Create SELECT DATE_SUB(field, INTERVAL expression type) AS alias SQL statement
	 *
	 * @see http://www.w3schools.com/sql/func_date.asp
	 *
	 * @param string      $field               Field name
	 * @param int         $expression          Number of interval expression
	 * @param string|null $type                UPPERCASE interval expression type
	 * @param bool        $is_return_statement Whether including into select active record or returning string
	 *
	 * @throws QueryBuilderException
	 * @return QueryInterface|string
	 */
	public function dateSub( $field, $expression, $type = NULL, $is_return_statement = FALSE )
	{
		if ( $this->_hasDateType( $expression ) )
		{
			$x_expression = explode( ' ', $expression );
			$x_expression = array_map( 'trim', $x_expression );

			@list( $expression, $type ) = $x_expression;
		}

		if ( empty( $type ) )
		{
			throw new QueryBuilderException( 'Undefined DATE substracts expression type' );
		}

		if ( isset( static::$sqlDateFunctions[ 'DATE_SUB' ] ) AND in_array( $type, static::$sqlDateTypes ) )
		{
			if ( is_array( $field ) )
			{
				$field_name  = key( $field );
				$field_alias = $field[ $field_name ];
			}
			elseif ( strpos( $field, ' AS ' ) !== FALSE )
			{
				$x_field = explode( ' AS ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field_name, $field_alias ) = $x_field;
			}
			elseif ( strpos( $field, ' as ' ) !== FALSE )
			{
				$x_field = explode( ' as ', $field );
				$x_field = array_map( 'trim', $x_field );

				@list( $field_name, $field_alias ) = $x_field;
			}

			if ( strpos( $field_name, '.' ) !== FALSE AND empty( $field_alias ) )
			{
				$x_field_name = explode( '.', $field_name );
				$x_field_name = array_map( 'trim', $x_field_name );

				$field_alias = end( $x_field_name );
			}

			$statement = sprintf( static::$sqlDateFunctions[ 'DATE_SUB' ], $this->_protectIdentifiers( $field_name ), (int) $expression, $type );

			if ( $is_return_statement )
			{
				return $statement . ( empty( $field_alias ) ? '' : ' AS ' . $this->_protectIdentifiers( $field_alias ) );;
			}

			$this->_select[ $statement ] = $field_alias;
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * DATEDIFF
	 *
	 * Add / Create SELECT DATEDIFF(datetime_start, datetime_end) AS alias SQL statement
	 *
	 * @see http://www.w3schools.com/sql/func_datediff_mysql.asp
	 *
	 * @param array       $fields              [datetime_start => datetime_end]
	 * @param string|null $alias               Field alias
	 * @param bool        $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface
	 */
	public function dateDiff( array $fields, $alias, $is_return_statement = FALSE )
	{
		if ( isset( static::$sqlDateFunctions[ 'DATE_DIFF' ] ) )
		{
			$datetime_start = key( $fields );
			$datetime_end   = $fields[ $datetime_start ];

			if ( preg_match( "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $datetime_start ) )
			{
				$datetime_start = $this->escape( $datetime_start );
			}
			else
			{
				$datetime_start = $this->_protectIdentifiers( $datetime_start );
			}

			if ( preg_match( "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $datetime_end ) )
			{
				$datetime_end = $this->escape( $datetime_end );
			}
			else
			{
				$datetime_end = $this->_protectIdentifiers( $datetime_end );
			}

			$statement = sprintf( static::$sqlDateFunctions[ 'DATE_DIFF' ], $datetime_start, $datetime_end );

			if ( $is_return_statement )
			{
				return $statement . ( empty( $alias ) ? '' : ' AS ' . $this->_protectIdentifiers( $alias ) );
			}

			$this->_select[ $statement ] = $this->_protectIdentifiers( $alias );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * DISTINCT
	 *
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 * keyword on SELECT statement
	 *
	 * @param    bool $distinct
	 *
	 * @return    QueryInterface
	 */
	public function distinct( $distinct = TRUE )
	{
		$this->_is_distinct = is_bool( $distinct ) ? $distinct : TRUE;

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * JOIN
	 *
	 * Add JOIN SQL statement portions into Query Builder
	 *
	 * @param string $table     Table name
	 * @param null   $condition Join conditiions: table.column = other_table.column
	 * @param string $type      UPPERCASE join type LEFT|LEFT_OUTER|RIGHT|RIGHT_OUTER|INNER|OUTER|FULL|JOIN
	 *
	 * @return $this
	 */
	public function join( $table, $condition = NULL, $type = 'LEFT' )
	{
		if ( is_array( $table ) )
		{
			foreach ( $table as $name => $condition )
			{
				$this->join( $name, $condition, $type );
			}

			return $this;
		}
		else
		{
			if ( strpos( $table, ':' ) !== FALSE )
			{
				$x_table = explode( ':', $table );

				foreach ( $x_table as $string )
				{
					if ( array_key_exists( strtoupper( $string ), static::$sqlJoinStatements ) )
					{
						$statement = static::$sqlJoinStatements[ strtoupper( $string ) ];
					}
					else
					{
						$table = $string;
					}
				}
			}

			if ( ! isset( $statement ) )
			{
				$statement = static::$sqlJoinStatements[ strtoupper( $type ) ];
				$table     = $this->_protectIdentifiers( $table, TRUE );
			}

			if ( is_string( $condition ) )
			{
				$condition = explode( '=', $condition );
				$condition = array_map( 'trim', $condition );
			}
			elseif ( is_array( $condition ) )
			{
				$condition = array_merge( array_keys( $condition ), array_values( $condition ) );
			}

			$this->_join[] = sprintf( $statement, $table, $this->_protectIdentifiers( $condition[ 0 ], TRUE ), $this->_protectIdentifiers( $condition[ 1 ], TRUE ) );

			return $this;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * OR WHERE
	 *
	 * Add OR WHERE SQL statement portions into Query Builder
	 *
	 * @param string|array $field Field name, array of [field => value] (grouped where)
	 * @param null|string  $value Field criteria or UPPERCASE grouped type AND|OR
	 *
	 * @return QueryInterface
	 */
	public function orWhere( $field, $value = NULL )
	{
		return $this->_prepareWhereClauses( $field, $value, 'OR' );
	}

	// ------------------------------------------------------------------------

	/**
	 * Prepare Where Clauses
	 *
	 * @param string|array      $field  Field name, array of fields names or array of QueryInterface instance
	 * @param null|string|array $value  Where clause criteria values
	 * @param string            $clause UPPERCASE Clause Type
	 *
	 * @return $this
	 */
	public function _prepareWhereClauses( $field, $value = NULL, $clause = 'WHERE' )
	{
		if ( array_key_exists( $clause, static::$activeRecordWhereClausesKeys ) )
		{
			if ( is_array( $field ) AND count( $field ) > 1 )
			{
				$grouped = [ ];
				$value   = in_array( $value, [ 'AND', 'OR' ], TRUE ) ? $value : 'AND';

				foreach ( $field as $key => $val )
				{
					if ( is_numeric( $key ) AND $val instanceof QueryInterface )
					{
						$builder = $this->_fetchBuilder( $val, NULL, NULL );

						if ( empty( $builder->_or_where ) )
						{
							$grouped[] = implode( ' ' . $value . ' ', $builder->_where );
						}
						else
						{
							$grouped[] = '( ' . $builder->getSQLString() . ' )';
						}
					}
					elseif ( $key instanceof QueryInterface )
					{
						$val = in_array( $val, [ 'AND', 'OR' ], TRUE ) ? $val : 'AND';

						$builder   = $this->_fetchBuilder( $key, NULL, NULL );
						$grouped[] = implode( ' ' . $val . ' ', $builder->_where );

						if ( empty( $builder->_or_where ) )
						{
							$grouped[] = implode( ' ' . $value . ' ', $builder->_where );
						}
						else
						{
							$grouped[] = '( ' . $key->getSQLString() . ' )';
						}
					}
					else
					{
						$grouped[] = $this->_prepareWhereClauseStatement( $key, $val, $clause );
					}
				}

				$clause_key = '_' . strtolower( static::$activeRecordWhereClausesKeys[ $clause ] );

				$this->{$clause_key}[] = '( ' . implode( ' ' . $value . ' ', $grouped ) . ' )';
			}
			elseif ( $field instanceof QueryInterface )
			{
				$clause_key = '_' . strtolower( static::$activeRecordWhereClausesKeys[ $clause ] );

				$builder = $this->_fetchBuilder( $field, NULL, NULL );

				$this->{$clause_key}[] = '( ' . $builder->getSQLString() . ' )';
			}
			else
			{
				if ( is_array( $field ) )
				{
					$value = @$field[ key( $field ) ];
					$field = key( $field );
				}

				$clause_key = '_' . strtolower( static::$activeRecordWhereClausesKeys[ $clause ] );

				$this->{$clause_key}[] = $this->_prepareWhereClauseStatement( $field, $value, $clause );
			}
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch Builder
	 *
	 * @param QueryInterface $builder
	 * @param null|string    $field  Field name
	 * @param null|string    $method QueryInterface method callback
	 *
	 * @return QueryInterface|string
	 */
	protected function _fetchBuilder( QueryInterface $builder, $field = NULL, $method = NULL )
	{
		foreach ( $builder->_binds as $parameter => $value )
		{
			if ( $this->isBindExists( $parameter ) )
			{
				$replaced_originals[] = $parameter;

				$new_parameter = $this->_prepareBindParameter( $field, ( $this->_is_grouped ? 'grouped_' : 'subquery_' ) );

				$replaced_parameters[] = $parameter = str_replace( ':', $new_parameter, $parameter );
			}

			$this->_binds[ $parameter ] = $value;
		}

		if ( isset( $method ) )
		{
			$sql_string = call_user_func( [ $builder, $method ] );

			if ( isset( $replaced_originals ) AND isset( $replaced_parameters ) )
			{
				$sql_string = str_replace( $replaced_originals, $replaced_parameters, $sql_string );
			}

			return $sql_string;
		}

		return $builder;
	}

	// ------------------------------------------------------------------------

	/**
	 * Prepare Where Clause Statement
	 *
	 * @param string       $field  Field name
	 * @param string|array $value  Field criteria value
	 * @param strign       $clause UPPERCASE clause type
	 *
	 * @return mixed
	 */
	protected function _prepareWhereClauseStatement( $field, $value, $clause )
	{
		if ( in_array( $clause, [ 'WHERE', 'OR' ], TRUE ) )
		{
			$field = $this->_protectIdentifiers( $field, TRUE, '=' );
		}
		else
		{
			$field = $this->_protectIdentifiers( $field, TRUE, FALSE );
		}

		$parameter = $this->_prepareBindParameter( $field, $clause );

		if ( is_array( $value ) )
		{
			/*$parameters = [ ];
			foreach ( $value as $key => $val )
			{
				$this->_binds[ $parameters[] = $parameter . $key ] = $this->escape( $val );
			}*/

			$value = array_map( [ $this, 'escape' ], $value );
			//print_out($value);

			if ( $clause === 'BETWEEN' OR $clause === 'NOT_BETWEEN' )
			{
				$value = implode( ' AND ', $value );
			}
			elseif ( $clause === 'OR_BETWEEN' OR $clause === 'OR_NOT_BETWEEN' )
			{
				$value = implode( ' OR ', $value );
			}
			else
			{
				$value = implode( ', ', $value );
			}

			return sprintf( static::$sqlWhereStatements[ $clause ], $field, $value );
		}
		elseif ( $value instanceof QueryInterface )
		{
			$value = $this->_fetchBuilder( $value, $field, 'getSQLString' );

			return sprintf( static::$sqlWhereStatements[ $clause ], $field, $value );
		}
		else
		{
			//$this->_binds[ $parameter ] = $this->escape( $value );

			return sprintf( static::$sqlWhereStatements[ $clause ], $field, $this->escape( $value ) );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * OR WHERE IN
	 *
	 * Add OR WHERE IN SQL statement portions into Query Builder
	 *
	 * @param string $field  Field name
	 * @param array  $values Array of values criteria
	 *
	 * @return QueryInterface
	 */
	public function orWhereIn( $field, $values = [ ] )
	{
		return $this->_prepareWhereClauses( $field, $values, 'OR_IN' );
	}

	// ------------------------------------------------------------------------

	/**
	 * WHERE NOT IN
	 *
	 * Add WHERE NOT IN SQL statement portions into Query Builder
	 *
	 * @param string $field  Field name
	 * @param array  $values Array of values criteria
	 *
	 * @return QueryInterface
	 */
	public function whereNotIn( $field, $values = [ ] )
	{
		return $this->_prepareWhereClauses( $field, $values, 'NOT_IN' );
	}

	// ------------------------------------------------------------------------

	/**
	 * OR WHERE NOT IN
	 *
	 * Add OR WHERE NOT IN SQL statement portions into Query Builder
	 *
	 * @param string $field  Field name
	 * @param array  $values Array of values criteria
	 *
	 * @return QueryInterface
	 */
	public function orWhereNotIn( $field, $values = [ ] )
	{
		return $this->_prepareWhereClauses( $field, $values, 'OR_NOT_IN' );
	}

	// ------------------------------------------------------------------------

	/**
	 * WHERE BETWEEN
	 *
	 * Add WHERE BETWEEN SQL statement portions into Query Builder
	 *
	 * @param string $field  Field name
	 * @param array  $values Array of between values
	 *
	 * @return QueryInterface
	 */
	public function whereBetween( $field, array $values = [ ] )
	{
		return $this->_prepareWhereClauses( $field, $values, 'BETWEEN' );
	}

	// ------------------------------------------------------------------------

	/**
	 * OR WHERE BETWEEN
	 *
	 * Add OR WHERE BETWEEN SQL statement portions into Query Builder
	 *
	 * @param string $field  Field name
	 * @param array  $values Array of between values
	 *
	 * @return QueryInterface
	 */
	public function orWhereBetween( $field, array $values = [ ] )
	{
		return $this->_prepareWhereClauses( $field, $values, 'OR_BETWEEN' );
	}

	// ------------------------------------------------------------------------

	/**
	 * WHERE NOT BETWEEN
	 *
	 * Add WHERE NOT BETWEEN SQL statement portions into Query Builder
	 *
	 * @param string $field  Field name
	 * @param array  $values Array of between values
	 *
	 * @return QueryInterface
	 */
	public function whereNotBetween( $field, array $values = [ ] )
	{
		return $this->_prepareWhereClauses( $field, $values, 'NOT_BETWEEN' );
	}

	// ------------------------------------------------------------------------

	/**
	 * OR WHERE NOT BETWEEN
	 *
	 * Add OR WHERE NOT BETWEEN SQL statement portions into Query Builder
	 *
	 * @param string $field  Field name
	 * @param array  $values Array of between values
	 *
	 * @return QueryInterface
	 */
	public function orWhereNotBetween( $field, array $values = [ ] )
	{
		return $this->_prepareWhereClauses( $field, $values, 'OR_NOT_BETWEEN' );
	}

	// ------------------------------------------------------------------------

	/**
	 * LIKE
	 *
	 * Add LIKE SQL statement portions into Query Builder
	 *
	 * @param   string $field          Field name
	 * @param   string $match          Field criteria match
	 * @param   string $wildcard       UPPERCASE positions of wildcard character BOTH|LEFT|RIGHT
	 * @param   bool   $case_sensitive Whether perform case sensitive LIKE or not
	 *
	 * @return QueryInterface
	 */
	public function like( $field, $match = '', $wildcard = 'BOTH', $case_sensitive = TRUE )
	{
		return $this->_prepareWhereLikeClauses( $field, $match, $wildcard, $case_sensitive, 'LIKE' );
	}

	// ------------------------------------------------------------------------

	/**
	 * Prepare Where Like Clause
	 *
	 * @param string|array $field          Field name or array of fields name or array of QueryInterface instance
	 * @param string       $match          Field match criteria
	 * @param string       $wildcard       Using LIKE wildcard character
	 * @param bool         $case_sensitive Whether run case sensitive LIKE match
	 * @param string       $clause         UPPERCASE Clause type
	 *
	 * @return QueryInterface
	 */
	protected function _prepareWhereLikeClauses( $field, $match = '', $wildcard = 'BOTH', $case_sensitive = TRUE, $clause = 'LIKE' )
	{
		if ( is_array( $field ) )
		{
			foreach ( $field as $field_name => $field_match )
			{
				if ( is_numeric( $field_name ) )
				{
					$field_name  = $field_match;
					$field_match = $match;
				}

				$this->_prepareWhereLikeClauses( $field_name, $field_match, $wildcard, $case_sensitive, $clause );
			}
		}
		else
		{
			$like_clause = $case_sensitive === TRUE ? 'LIKE' : 'LIKE_INSENSITIVE';

			// Bind Param
			//$parameter = $this->_prepareBindParameter( $field, $clause );
			$match = $this->escapeLikeString( $match );

			//$this->_binds[ $parameter ] = $match;

			switch ( strtoupper( $wildcard ) )
			{
				default:
				case 'BOTH':
					$match = "%" . $match . "%";
					break;
				case 'BEFORE':
					$match = "%" . $match;
					break;
				case 'AFTER':
					$match = $match . "%";
					break;
				case 'NONE':
					// nothing to do
					break;
			}

			if ( static::$isProtectIdentifiers === TRUE AND static::$escapeLikeCharacter !== '' )
			{
				$like_clause .= '_ESCAPE';

				if ( array_key_exists( $like_clause, static::$sqlLikeStatements ) )
				{
					$statement = sprintf( static::$sqlLikeStatements[ $like_clause ], $this->_protectIdentifiers( $field ), $this->escape( $match ), static::$escapeLikeCharacter );
				}
			}
			elseif ( array_key_exists( $like_clause, static::$sqlLikeStatements ) )
			{
				$statement = sprintf( static::$sqlLikeStatements[ $like_clause ], $this->_protectIdentifiers( $field ), $this->escape( $match ) );
			}

			if ( isset( $statement ) )
			{
				if ( strpos( $clause, 'NOT' ) !== FALSE )
				{
					$statement = str_replace( 'LIKE', 'NOT LIKE', $statement );
				}

				if ( strpos( $clause, 'OR' ) !== FALSE )
				{
					$this->_or_like[] = $statement;
				}
				else
				{
					$this->_like[] = $statement;
				}
			}
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * OR LIKE
	 *
	 * Add OR LIKE SQL statement portions into Query Builder
	 *
	 * @param   string $field          Field name
	 * @param   string $match          Field criteria match
	 * @param   string $wildcard       UPPERCASE positions of wildcard character BOTH|LEFT|RIGHT
	 * @param   bool   $case_sensitive Whether perform case sensitive LIKE or not
	 *
	 * @return QueryInterface
	 */
	public function orLike( $field, $match = '', $wildcard = 'BOTH', $case_sensitive = TRUE )
	{
		$this->_prepareWhereLikeClauses( $field, $match, $wildcard, $case_sensitive, 'OR_LIKE' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * NOT LIKE
	 *
	 * Add NOT LIKE SQL statement portions into Query Builder
	 *
	 * @param   string $field          Field name
	 * @param   string $match          Field criteria match
	 * @param   string $wildcard       UPPERCASE positions of wildcard character BOTH|LEFT|RIGHT
	 * @param   bool   $case_sensitive Whether perform case sensitive LIKE or not
	 *
	 * @return QueryInterface
	 */
	public function notLike( $field, $match = '', $wildcard = 'BOTH', $case_sensitive = TRUE )
	{
		$this->_prepareWhereLikeClauses( $field, $match, $wildcard, $case_sensitive, 'NOT_LIKE' );

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * OR NOT LIKE
	 *
	 * Add OR NOT LIKE SQL statement portions into Query Builder
	 *
	 * @param   string $field          Field name
	 * @param   string $match          Field criteria match
	 * @param   string $wildcard       UPPERCASE positions of wildcard character BOTH|LEFT|RIGHT
	 * @param   bool   $case_sensitive Whether perform case sensitive LIKE or not
	 *
	 * @return QueryInterface
	 */
	public function orNotLike( $field, $match = '', $wildcard = 'BOTH', $case_sensitive = TRUE )
	{
		$this->_prepareWhereLikeClauses( $field, $match, $wildcard, $case_sensitive, 'OR_NOT_LIKE' );

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Union
	 *
	 * Performing SQL UNION Operator, combining two or more SELECT statements.
	 *  SELECT column FROM table
	 *      UNION
	 *  SELECT column_other FROM table_other
	 *
	 * @return QueryInterface Clean QueryInterface
	 */
	public function union()
	{
		$union = clone $this;
		$union->resetAll();

		return $union;
	}

	/**
	 * Group
	 *
	 * Performing Grouping WHERE or LIKE conditions WHERE (column = 'something' OR ...) AND ...
	 *
	 * @return QueryInterface Clean QueryInterface
	 */
	public function group()
	{
		$group = clone $this;
		$group->resetAll();
		$group->_is_grouped = TRUE;

		return $group;
	}

	// ------------------------------------------------------------------------

	/**
	 * SubQuery
	 *
	 * Performing WHERE IN or NOT IN with SubQuery WHERE IN(SELECT ... )
	 *
	 * @return QueryInterface Clean QueryInterface
	 */
	public function subQuery()
	{
		$sub_query = clone $this;
		$sub_query->resetAll();

		return $sub_query;
	}

	/**
	 * Add Binds
	 *
	 * Performing multiple bindings.
	 *
	 * @param array  $binds
	 * @param string $prefix Binding key parameter prefix
	 *
	 * @return QueryInterface
	 */
	public function addBinds( array $binds, $prefix = '' )
	{
		foreach ( $binds as $parameter => $value )
		{
			$this->addBind( $parameter, $value, $prefix );
		}

		return $this;
	}

	/**
	 * OFFSET
	 *
	 * Add OFFSET SQL statement into Query Builder.
	 *
	 * @param    int $offset OFFSET value
	 *
	 * @return    QueryInterface
	 */
	public function offset( $offset )
	{
		if ( ! empty( $offset ) )
		{
			$this->_offset = (int) $offset;
		}

		return $this;
	}

	/**
	 * Page
	 *
	 * Auto Set LIMIT, OFFSET SQL statement by page number and entries.
	 *
	 * @param int  $page    Page number
	 * @param null $entries Num entries of each page
	 *
	 * @return QueryInterface
	 */
	public function page( $page = 1, $entries = NULL )
	{
		$page    = (int) intval( $page );
		$entries = (int) ( isset( $entries ) ? $entries : ( $this->_limit === FALSE ? 5 : $this->_limit ) );
		$offset  = ( $page - 1 ) * $entries;

		$this->limit( $entries, $offset );

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * LIMIT
	 *
	 * Add LIMIT,OFFSET SQL statement into Query Builder.
	 *
	 * @param    int $limit  LIMIT value
	 * @param    int $offset OFFSET value
	 *
	 * @return    QueryInterface
	 */
	public function limit( $limit, $offset = NULL )
	{
		if ( isset( $limit ) )
		{
			$this->_limit = (int) $limit;
		}

		if ( isset( $offset ) )
		{
			$this->_offset = (int) $offset;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * GROUP BY
	 *
	 * Add GROUP BY SQL statement into Query Builder.
	 *
	 * @param $field
	 *
	 * @return $this
	 */
	public function groupBy( $field )
	{
		if ( is_array( $field ) )
		{
			foreach ( $field as $field_name )
			{
				$this->groupBy( $field_name );
			}
		}
		else
		{
			$this->_group_by[] = $this->_protectIdentifiers( $field );
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Having
	 *
	 * Add HAVING SQL statement portions into Query Builder.
	 *
	 * @param string                $field    Field name
	 * @param null|array|string|int $criteria Having criteria
	 * @param string                $type     Keyword statement AND|OR for array of criteria
	 *
	 * @return QueryInterface
	 */
	public function having( $field, $criteria = NULL, $type = 'AND' )
	{
		if ( isset( static::$sqlStatements[ 'HAVING' ] ) )
		{
			$field = $this->_protectIdentifiers( $field, TRUE, '=' );

			if ( is_array( $criteria ) )
			{
				$criteria = array_map( [ $this, 'escape' ], $criteria );
				$type     = in_array( strtoupper( $type ), [ 'AND', 'OR' ] ) ? $type : 'AND';

				$this->_having[] = $field . ' ' . implode( $type . ' ', $criteria );
			}
			else
			{
				$criteria        = $this->escape( $criteria );
				$this->_having[] = $field . ' ' . $criteria;
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * ORDER BY
	 *
	 * Add ORDER BY SQL statement portions into Query Builder.
	 *
	 * @param        $field
	 * @param string $direction
	 *
	 * @return $this
	 */
	public function orderBy( $field, $direction = 'ASC' )
	{
		$direction = strtoupper( trim( $direction ) );

		if ( is_array( $field ) )
		{
			foreach ( $field as $field_name => $field_direction )
			{
				if ( is_numeric( $field_name ) )
				{
					$field_name      = $field_direction;
					$field_direction = $direction;
				}

				$this->orderBy( $field_name, $field_direction );
			}
		}
		else
		{
			if ( $direction === 'RANDOM' )
			{
				$direction = '';

				$random_keywords = $this->_connID->getOrderByRandomKeywords();

				// Do we have a seed value?
				$field = ctype_digit( (string) $field )
					? sprintf( $random_keywords[ 1 ], $field )
					: $random_keywords[ 0 ];
			}
			elseif ( $direction !== '' )
			{
				$direction = in_array( $direction, [ 'ASC', 'DESC' ], TRUE ) ? $direction : '';
			}

			$this->_order_by[] = $this->_protectIdentifiers( $field ) . ' ' . $direction;
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get
	 *
	 * Perform execution of SQL Query Builder and run ConnectionInterface::query()
	 *
	 * @param null|string $table
	 * @param null|int    $limit
	 * @param null|int    $offset
	 *
	 * @return  Results
	 */
	public function get( $table = NULL, $limit = NULL, $offset = NULL )
	{
		if ( isset( $table ) )
		{
			if ( is_numeric( $table ) )
			{
				$offset = $limit;
				$limit  = $table;
			}
			else
			{
				$this->from( $table );
			}
		}

		if ( isset( $limit ) )
		{
			$this->limit( $limit, $offset );
		}

		$query = clone $this;
		$this->resetSelect();

		return $this->query( $query );
	}

	//--------------------------------------------------------------------

	/**
	 * From
	 *
	 * Add FROM SQL statement portions into Query Builder
	 *
	 * @param   string|array $table
	 * @param   string       $alias
	 *
	 * @return  QueryInterface
	 */
	public function from( $table, $alias = NULL )
	{
		if ( is_array( $table ) )
		{
			foreach ( $table as $name => $alias )
			{
				if ( is_numeric( $name ) )
				{
					$name  = $alias;
					$alias = NULL;
				}

				if ( strpos( $name, ' AS ' ) !== FALSE )
				{
					$x_name = explode( 'AS', $name );
					$x_name = array_map( 'trim', $x_name );

					list( $name, $alias ) = $x_name;
				}

				$this->from( $name, $alias );
			}
		}
		elseif ( is_string( $table ) )
		{
			if ( strpos( $table, ' AS ' ) !== FALSE )
			{
				$x_table = explode( 'AS', $table );
				$x_table = array_map( 'trim', $x_table );

				list( $table, $alias ) = $x_table;
			}

			$table = $this->_protectIdentifiers( $table, TRUE );

			if ( empty( $this->_table ) )
			{
				$this->_table = $table;
			}

			if ( isset( $alias ) )
			{
				$alias = $this->escapeIdentifiers( $alias );

				// Collect Tables Aliases
				if ( ! array_key_exists( $table, $this->_table_aliases ) )
				{
					$this->_table_aliases[ $table ] = $alias;
				}
			}

			if ( ! in_array( $table, $this->_from, TRUE ) )
			{
				$this->_from[ $table ] = isset( $alias ) ? $alias : NULL;
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Reset Select
	 *
	 * Perform reset select Query Builder objects.
	 *
	 * @access  public
	 * @return  void
	 */
	public function resetSelect()
	{
		$this->_resetExec(
			[
				'table'           => NULL,
				'table_aliases'   => [ ],
				'union'           => [ ],
				'select'          => [ ],
				'from'            => [ ],
				'join'            => [ ],
				'where'           => [ ],
				'or_where'        => [ ],
				'like'            => [ ],
				'or_like'         => [ ],
				'having'          => [ ],
				'or_having'       => [ ],
				'group_by'        => [ ],
				'order_by'        => [ ],
				'limit'           => FALSE,
				'offset'          => FALSE,
				'binds'           => [ ],
				'is_distinct'     => FALSE,
				'is_union_all'    => FALSE,
				'is_grouped'      => FALSE,
				'string'          => NULL,
				'compiled_string' => NULL,
			] );
	}

	//--------------------------------------------------------------------

	/**
	 * Get Where
	 *
	 * Perform execution of SQL Query Builder and run ConnectionInterface::query()
	 *
	 * @param null  $table
	 * @param array $where
	 * @param null  $limit
	 * @param null  $offset
	 *
	 * @access  public
	 * @return  Results
	 */
	public function getWhere( $table = NULL, array $where = [ ], $limit = NULL, $offset = NULL )
	{
		if ( isset( $table ) )
		{
			$this->from( $table );
		}

		foreach ( $where as $field => $value )
		{
			$this->where( $field, $value );
		}

		if ( isset( $limit ) )
		{
			$this->limit( $limit, $offset );
		}

		$query = clone $this;
		$this->resetSelect();

		return $this->query( $query );
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE
	 *
	 * Add WHERE SQL statement portions into Query Builder
	 *
	 * @param string|array $field Field name, array of [field => value] (grouped where)
	 * @param null|string  $value Field criteria or UPPERCASE grouped type AND|OR
	 *
	 * @return QueryInterface
	 */
	public function where( $field, $value = NULL )
	{
		return $this->_prepareWhereClauses( $field, $value, 'WHERE' );
	}

	//--------------------------------------------------------------------

	/**
	 * Count All
	 *
	 * Perform execution of count all records of a table.
	 *
	 * @param string $table Table name
	 * @param bool   $reset Whether perform reset Query Builder or not
	 *
	 * @access  public
	 * @return  int
	 */
	public function countAll( $table = '*', $reset = TRUE )
	{
		if ( $table === '*' )
		{
			$table = $this->_table;
		}
		elseif ( strpos( $table, '.' ) !== FALSE )
		{
			$x_table = explode( '.', $table );
			$x_table = array_map( 'trim', $x_table );

			$table = $x_table[ 0 ];
			$field = $x_table[ 1 ];
		}

		$this->from( $table );

		$field = empty( $field ) ? '*' : $table . '.' . $field;
		$this->count( $field, 'num_rows' );

		$num_rows = 0;

		$statement[] = $this->_compileSelectSQLStatement();
		$statement[] = $this->_compileFromSQLStatement();

		$sqlQueryString = implode( static::$newLineString, $statement );

		$result = $this->execute( $sqlQueryString );

		if ( $reset )
		{
			$this->resetAll();
		}

		if ( $result->numRows() > 0 )
		{
			return $result->first()->num_rows;
		}

		return (int) $num_rows;
	}

	// ------------------------------------------------------------------------

	/**
	 * COUNT()
	 *
	 * Add SELECT COUNT(field) AS alias statement
	 *
	 * @param string $field               Field name
	 * @param string $alias               Field alias
	 * @param bool   $is_return_statement Whether including into select active record or returning string
	 *
	 * @return QueryInterface|string
	 */
	public function count( $field, $alias = '', $is_return_statement = FALSE )
	{
		return $this->_prepareAggregateStatement( $field, $alias, $is_return_statement, 'COUNT' );
	}

	// ------------------------------------------------------------------------

	/**
	 * Compile SELECT SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileSelectSQLStatement()
	{
		if ( $this->_is_grouped === FALSE )
		{
			if ( empty( $this->_union ) )
			{
				$fields = [ ];

				if ( empty( $this->_select ) )
				{
					$fields = [ '*' ];
				}
				elseif ( isset( static::$sqlStatements[ 'SELECT_AS' ] ) )
				{
					foreach ( $this->_select as $field => $alias )
					{
						if ( isset( $alias ) )
						{
							$fields[] = sprintf( static::$sqlStatements[ 'SELECT_AS' ], $field, $alias );
						}
						else
						{
							$fields[] = $field;
						}
					}
				}

				return sprintf( static::$sqlStatements[ ( $this->_is_distinct === FALSE ? 'SELECT' : 'SELECT_DISTINCT' ) ], ( count( $fields ) > 1 ? static::$newLineString : '' ) . implode( ', ' . static::$newLineString, $fields ) );
			}
			elseif ( isset( static::$sqlStatements[ 'UNION' ] ) AND isset( static::$sqlStatements[ 'UNION_ALL' ] ) )
			{
				$statement_format = $this->_is_union_all === TRUE ? static::$sqlStatements[ 'UNION_ALL' ] : static::$sqlStatements[ 'UNION' ];

				return implode( static::$newLineString . $statement_format . static::$newLineString, $this->_union );
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Compile FROM SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileFromSQLStatement()
	{
		if ( isset( $this->_table ) AND $this->_is_grouped === FALSE )
		{
			$from = [ ];

			foreach ( $this->_from as $table => $alias )
			{
				if ( isset( $this->_table_aliases[ $table ] ) )
				{
					$from[] = sprintf( static::$sqlStatements[ 'FROM_AS' ], $table, $alias );
				}
				else
				{
					$from[] = $table;
				}
			}

			return sprintf( static::$sqlStatements[ 'FROM' ], implode( ', ', $from ) );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Count All Result
	 *
	 * Perform execution of count all result from Query Builder along with WHERE, LIKE, HAVING, GROUP BY, and LIMIT SQL
	 * statement.
	 *
	 * @param string $table Table name
	 * @param bool   $reset Whether perform reset Query Builder or not
	 *
	 * @access  public
	 * @return  int
	 */
	public function countAllResults( $table = '*', $reset = TRUE )
	{
		$field = '*';

		if ( $table === '*' )
		{
			$field = $table;
			$table = $this->_table;
		}
		elseif ( strpos( $table, '.' ) !== FALSE )
		{
			$x_table = explode( '.', $table );
			$x_table = array_map( 'trim', $x_table );

			$table = $x_table[ 0 ];
			$field = $x_table[ 1 ];
		}

		$this->from( $table );

		$field = $field === '*' ? '*' : $table . '.' . $field;
		$this->count( $field, 'num_rows' );

		$num_rows = 0;

		$sqlQueryString = $this->getSQLString( TRUE );

		$result = $this->execute( $sqlQueryString );

		if ( $reset )
		{
			$this->resetAll();
		}

		if ( $result->numRows() > 0 )
		{
			return $result->first()->num_rows;
		}

		return (int) $num_rows;
	}

	// ------------------------------------------------------------------------

	/**
	 * INSERT
	 *
	 * Execute INSERT SQL Query
	 *
	 * @param string $table  Table Name
	 * @param array  $sets   Array of data sets [field => value]
	 * @param string $return Whether return BOOL|affectedRows|Write
	 *
	 * @return bool|int|Write
	 */
	public function insert( $table, array $sets, $return = 'BOOL' )
	{
		$this->from( $table );
		$this->sets( $sets );

		if ( ! empty( $this->_sets ) )
		{
			if ( array_key_exists( 'INSERT', static::$sqlStatements ) )
			{
				$this->_string = sprintf(
					static::$sqlStatements[ 'INSERT' ], $this->_table,
					implode( ', ', array_keys( $this->_sets ) ),
					implode( ', ', array_values( $this->_sets ) )
				);

				$result = $this->query( clone $this );
				$this->resetWrite();

				if ( $result instanceof Write )
				{
					if ( strtoupper( $return ) === 'BOOL' )
					{
						return TRUE;
					}
					elseif ( $result->offsetExists( $return = camelcase( $return ) ) )
					{
						return $result->offsetGet( $return );
					}

					return $result;
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Sets
	 *
	 * Perform sets binding field and values for write process
	 *
	 * @param array $sets Array of [field => value]
	 *
	 * @uses    QueryInterface::set()
	 *
	 * @access  public
	 * @return  QueryInterface
	 */
	public function sets( array $sets )
	{
		foreach ( $sets as $field => $value )
		{
			$this->set( $field, $value );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set
	 *
	 * Perform binding single set of field and value
	 *
	 * @param string                            $field Field name
	 * @param string|null|int|bool|array|object $value Field value
	 *                                                 if the value type is array will automatically convert base on
	 *                                                 ConfigInterface::$_array_value_conversion_method whether JSON
	 *                                                 string or SERIALIZE string if the value type is object will
	 *                                                 automatically convert into JSON string format
	 *
	 * @return QueryInterface
	 */
	public function set( $field, $value )
	{
		if ( is_array( $value ) )
		{
			$value = $this->trim( $value );

			switch ( $this->_array_value_conversion_method )
			{
				case 'JSON':
					$value = json_encode( $value );
					break;
				default:
				case 'SERIALIZE':
					$value = serialize( $value );
					break;
			}
		}
		elseif ( is_object( $value ) )
		{
			$value = json_encode( $value, JSON_FORCE_OBJECT );
		}
		else
		{
			$value = str_replace( "\\", "\\\\\\", trim( $value ) );
		}

		$parameter = $this->_prepareBindParameter( $field, 'set' );

		$this->_binds[ $parameter ] = $value;

		$this->_sets[ $this->_protectIdentifiers( $field ) ] = $parameter;

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Reset Write
	 *
	 * Perform reset write Query Builder objects.
	 *
	 * @access  public
	 * @return  void
	 */
	public function resetWrite()
	{
		$this->_resetExec(
			[
				'table'           => NULL,
				'table_aliases'   => [ ],
				'from'            => [ ],
				'where'           => [ ],
				'or_where'        => [ ],
				'like'            => [ ],
				'or_like'         => [ ],
				'having'          => [ ],
				'or_having'       => [ ],
				'sets'            => [ ],
				'binds'           => [ ],
				'is_grouped'      => FALSE,
				'string'          => NULL,
				'compiled_string' => NULL,
			] );
	}

	// ------------------------------------------------------------------------

	/**
	 * INSERT Batch
	 *
	 * Execute INSERT batch SQL Query
	 *
	 * @param string $table      Table Name
	 * @param array  $sets       Array of data sets[][field => value]
	 * @param int    $batch_size Maximum batch size
	 * @param string $return     Whether return BOOL|affectedRows|Write
	 *
	 * @return bool|int|Write
	 */
	public function insertBatch( $table, array $sets, $batch_size = 1000, $return = 'BOOL' )
	{
		$this->from( $table );

		$fields = array_keys( reset( $sets ) );

		foreach ( $fields as $field )
		{
			$bind_fields[] = $this->_protectIdentifiers( $field );
			$bind_sets[]   = $field;
		}

		$i = 1;
		foreach ( $sets as $set )
		{
			if ( $i < $batch_size )
			{
				foreach ( $set as $field => $value )
				{
					if ( in_array( $field, $bind_sets ) )
					{
						$this->set( $field . '_' . $i, $value );
						$set_values[ $i ][] = end( $this->_sets );
					}
				}

				$values[ $i ] = '(' . implode( ', ', $set_values[ $i ] ) . ')';

				$i++;
			}
		}

		if ( ! empty( $this->_sets ) )
		{
			if ( array_key_exists( 'INSERT_BATCH', static::$sqlStatements ) AND isset( $bind_fields ) AND isset( $values ) )
			{
				$this->_string = sprintf(
					static::$sqlStatements[ 'INSERT_BATCH' ], $this->_table,
					implode( ', ', $bind_fields ),
					implode( ', ', $values )
				);

				$result = $this->query( clone $this );
				$this->resetWrite();

				if ( $result instanceof Write )
				{
					if ( strtoupper( $return ) === 'BOOL' )
					{
						return TRUE;
					}
					elseif ( $result->offsetExists( $return = camelcase( $return ) ) )
					{
						return $result->offsetGet( $return );
					}

					return $result;
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * UPDATE
	 *
	 * Execute UPDATE SQL Query
	 *
	 * @param string $table  Table Name
	 * @param array  $sets   Array of data sets[][field => value]
	 * @param array  $where  WHERE [field => match]
	 * @param string $return Whether return BOOL|affectedRows|Write
	 *
	 * @return bool|int|Write
	 */
	public function update( $table, array $sets, array $where = [ ], $return = 'BOOL' )
	{
		$this->from( $table );
		$this->sets( $sets );

		if ( ! empty( $where ) )
		{
			foreach ( $where as $field => $value )
			{
				$this->where( $field, $value );
			}
		}

		if ( ! empty( $this->_sets ) AND ! empty( $this->_where ) )
		{
			if ( array_key_exists( 'UPDATE', static::$sqlStatements ) )
			{
				$sets = [ ];
				foreach ( $this->_sets as $field => $value )
				{
					$sets[] = $field . ' = ' . $value;
				}

				$this->_string = sprintf(
					static::$sqlStatements[ 'UPDATE' ], $this->_table,
					implode( ', ', $sets ),
					implode( ' AND ', $this->_where )
				);

				$result = $this->query( clone $this );
				$this->resetWrite();

				if ( $result instanceof Write )
				{
					if ( strtoupper( $return ) === 'BOOL' )
					{
						return TRUE;
					}
					elseif ( $result->offsetExists( $return = camelcase( $return ) ) )
					{
						return $result->offsetGet( $return );
					}

					return $result;
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * UPDATE Batch
	 *
	 * Execute UPDATE batch SQL Query
	 *
	 * @param string $table      Table Name
	 * @param array  $sets       Array of data sets[][field => value]
	 * @param string $index      Index field
	 * @param int    $batch_size Maximum batch size
	 * @param string $return     Whether return BOOL|affectedRows|Write
	 *
	 * @return bool|int|Write
	 */
	public function updateBatch( $table, array $sets, $index = NULL, $batch_size = 1000, $return = 'BOOL' )
	{
		$this->from( $table );

		$fields = array_keys( reset( $sets ) );

		foreach ( $fields as $field )
		{
			$bind_fields[] = $field;
		}

		$index = empty( $index ) ? $index = reset( $bind_fields ) : $index;

		$i = 1;
		foreach ( $sets as $set )
		{
			if ( $i < $batch_size )
			{
				foreach ( $set as $field => $value )
				{
					if ( in_array( $field, $bind_fields ) )
					{
						$this->set( $field . '_' . $i, $value );
						$set_value = end( $this->_sets );

						if ( $field === $index )
						{
							$bind_ids[] = $value;
						}
						else
						{
							$cases[ $this->_protectIdentifiers( $field ) ][] = sprintf( static::$sqlStatements[ 'UPDATE_BATCH_WHEN' ], $index, $set[ $index ], $set_value );
						}
					}
				}

				$i++;
			}
		}

		if ( isset( $cases ) )
		{
			$bind_values = [ ];
			foreach ( $cases as $case => $when )
			{
				$bind_values[] = sprintf( static::$sqlStatements[ 'UPDATE_BATCH_CASE' ], $case, implode( static::$newLineString, $when ), $case );
			}
		}

		if ( ! empty( $this->_sets ) )
		{
			if ( array_key_exists( 'UPDATE_BATCH', static::$sqlStatements ) AND isset( $index ) AND isset( $bind_ids ) AND isset( $bind_values ) )
			{
				$this->_string = sprintf(
					static::$sqlStatements[ 'UPDATE_BATCH' ],
					$this->_table,
					implode( ', ', $bind_values ),
					$this->_protectIdentifiers( $index ),
					implode( ',', $bind_ids )
				);

				$result = $this->query( clone $this );
				$this->resetWrite();

				if ( $result instanceof Write )
				{
					if ( strtoupper( $return ) === 'BOOL' )
					{
						return TRUE;
					}
					elseif ( $result->offsetExists( $return = camelcase( $return ) ) )
					{
						return $result->offsetGet( $return );
					}

					return $result;
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * REPLACE
	 *
	 * Execute REPLACE SQL Query
	 *
	 * @param string $table  Table Name
	 * @param array  $sets   Array of data sets [field => value]
	 * @param string $return Whether return BOOL|affectedRows|Write
	 *
	 * @return bool|int|Write
	 */
	public function replace( $table, array $sets, $return = 'BOOL' )
	{
		$this->from( $table );
		$this->sets( $sets );

		if ( ! empty( $this->_sets ) )
		{
			if ( array_key_exists( 'REPLACE', static::$sqlStatements ) )
			{
				$this->_string = sprintf(
					static::$sqlStatements[ 'REPLACE' ], $this->_table,
					implode( ', ', array_keys( $this->_sets ) ),
					implode( ', ', array_values( $this->_sets ) )
				);

				$result = $this->query( clone $this );
				$this->resetWrite();

				if ( $result instanceof Write )
				{
					if ( strtoupper( $return ) === 'BOOL' )
					{
						return TRUE;
					}
					elseif ( $result->offsetExists( $return = camelcase( $return ) ) )
					{
						return $result->offsetGet( $return );
					}

					return $result;
				}
			}
		}

		return FALSE;
	}

	/**
	 * REPLACE Batch
	 *
	 * Execute REPLACE batch SQL Query
	 *
	 * @param string $table      Table Name
	 * @param array  $sets       Array of data sets[][field => value]
	 * @param int    $batch_size Maximum batch size
	 * @param string $return     Whether return BOOL|affectedRows|Write
	 *
	 * @return bool|int|Write
	 */
	public function replaceBatch( $table, array $sets, $batch_size = 1000, $return = 'BOOL' )
	{
		$this->from( $table );

		$fields = array_keys( reset( $sets ) );

		foreach ( $fields as $field )
		{
			$bind_fields[] = $this->_protectIdentifiers( $field );
			$bind_sets[]   = $field;
		}

		$i = 1;
		foreach ( $sets as $set )
		{
			if ( $i < $batch_size )
			{
				foreach ( $set as $field => $value )
				{
					if ( in_array( $field, $bind_sets ) )
					{
						$this->set( $field . '_' . $i, $value );
						$set_values[ $i ][] = end( $this->_sets );
					}
				}

				$values[ $i ] = '(' . implode( ', ', $set_values[ $i ] ) . ')';

				$i++;
			}
		}

		if ( ! empty( $this->_sets ) )
		{
			if ( array_key_exists( 'REPLACE_BATCH', static::$sqlStatements ) AND isset( $bind_fields ) AND isset( $values ) )
			{
				$this->_string = sprintf(
					static::$sqlStatements[ 'REPLACE_BATCH' ], $this->_table,
					implode( ', ', $bind_fields ),
					implode( ', ', $values )
				);

				$result = $this->query( clone $this );
				$this->resetWrite();

				if ( $result instanceof Write )
				{
					if ( strtoupper( $return ) === 'BOOL' )
					{
						return TRUE;
					}
					elseif ( $result->offsetExists( $return = camelcase( $return ) ) )
					{
						return $result->offsetGet( $return );
					}

					return $result;
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * DELETE
	 *
	 * Execute DELETE SQL Query
	 *
	 * @param string $table  Table Name
	 * @param array  $where  WHERE [field => match]
	 * @param string $return Whether return BOOL|affectedRows|Write
	 *
	 * @return bool|int|Write
	 */
	public function delete( $table, $return = 'BOOL' )
	{
		$this->from( $table );

		if ( ! empty( $where ) )
		{
			foreach ( $where as $field => $value )
			{
				$this->where( $field, $value );
			}
		}

		if ( ! empty( $this->_where ) )
		{
			if ( array_key_exists( 'DELETE', static::$sqlStatements ) )
			{
				$sets = [ ];
				foreach ( $this->_sets as $field => $value )
				{
					$sets[] = $field . ' = ' . $value;
				}

				$this->_string = sprintf(
					static::$sqlStatements[ 'DELETE' ], $this->_table,
					implode( ' AND ', $this->_where )
				);

				$result = $this->query( clone $this );
				$this->resetWrite();

				if ( $result instanceof Write )
				{
					if ( strtoupper( $return ) === 'BOOL' )
					{
						return TRUE;
					}
					elseif ( $result->offsetExists( $return = camelcase( $return ) ) )
					{
						return $result->offsetGet( $return );
					}

					return $result;
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * DELETE Batch
	 *
	 * Execute DELETE batch SQL Query
	 *
	 * @param string $table      Table Name
	 * @param array  $where      WHERE IN (field => [match, ...])
	 * @param int    $batch_size Maximum batch size
	 * @param string $return     Whether return BOOL|affectedRows|Write
	 *
	 * @return bool|int|Write
	 */
	public function deleteBatch( $table, array $where, $batch_size = 1000, $return = 'BOOL' )
	{
		$this->from( $table );

		foreach ( $where as $field => $values )
		{
			if ( count( $values ) < $batch_size )
			{
				$this->whereIn( $field, $values );
			}
		}

		if ( ! empty( $this->_where ) )
		{
			if ( array_key_exists( 'DELETE', static::$sqlStatements ) )
			{
				$where = [ ];
				foreach ( $this->_sets as $field => $value )
				{
					$where[] = $field . ' = ' . $value;
				}

				$this->_string = sprintf(
					static::$sqlStatements[ 'DELETE' ], $this->_table,
					implode( ' AND ', $this->_where )
				);

				$result = $this->query( clone $this );
				$this->resetWrite();

				if ( $result instanceof Write )
				{
					if ( strtoupper( $return ) === 'BOOL' )
					{
						return TRUE;
					}
					elseif ( $result->offsetExists( $return = camelcase( $return ) ) )
					{
						return $result->offsetGet( $return );
					}

					return $result;
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * WHERE IN
	 *
	 * Add WHERE IN SQL statement portions into Query Builder
	 *
	 * @param string $field  Field name
	 * @param array  $values Array of values criteria
	 *
	 * @return QueryInterface
	 */
	public function whereIn( $field, $values = [ ] )
	{
		return $this->_prepareWhereClauses( $field, $values, 'IN' );
	}

	// ------------------------------------------------------------------------

	/**
	 * Compile SELECT SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileIntoSQLStatement()
	{
		if ( $this->_is_grouped === FALSE )
		{
			if ( isset( $this->_into ) )
			{
				return $this->_into;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Compile JOIN SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileJoinSQLStatement()
	{
		if ( $this->_is_grouped === FALSE )
		{
			return empty( $this->_join ) ? NULL : implode( static::$newLineString, $this->_join );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Compile WHERE LIKE SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileLikeSQLStatement()
	{
		if ( $this->_is_grouped === FALSE )
		{

			if ( ! empty( $this->_like ) )
			{
				if ( count( $this->_like ) == 1 )
				{
					$this->_where[] = implode( static::$newLineString, $this->_like );
				}
				else
				{
					$this->_where[] = '(' . static::$newLineString . implode( static::$newLineString, $this->_like ) . static::$newLineString . ')';
				}
			}

			if ( ! empty( $this->_or_like ) )
			{
				if ( count( $this->_or_like ) == 1 )
				{
					$this->_or_where[] = implode( static::$newLineString, $this->_or_like );
				}
				else
				{
					$this->_or_where[] = '(' . static::$newLineString . implode( static::$newLineString, $this->_or_like ) . static::$newLineString . ')';
				}
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Compile WHERE SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileWhereSQLStatement()
	{
		$conditions = [ ];

		// WHERE
		if ( ! empty( $this->_where ) )
		{
			$conditions[] = implode( static::$newLineString . ' AND ', $this->_where );
		}

		// OR WHERE
		if ( ! empty( $this->_or_where ) )
		{
			$conditions [] = ( empty( $conditions ) ? NULL : ' OR ' ) . implode( static::$newLineString . ' OR ', $this->_or_where );
		}

		if ( ! empty( $conditions ) )
		{
			if ( $this->_is_grouped === FALSE )
			{
				return sprintf( static::$sqlStatements[ 'WHERE' ], implode( static::$newLineString, $conditions ) );
			}

			return implode( ( count( $conditions ) > 2 ? static::$newLineString : '' ), $conditions );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Compile GROUP BY SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileGroupBySQLStatement()
	{
		if ( $this->_is_grouped === FALSE AND isset( static::$sqlStatements[ 'GROUP_BY' ] ) AND ! empty( $this->_group_by ) )
		{
			return sprintf( static::$sqlStatements[ 'GROUP_BY' ], implode( ', ', $this->_group_by ) );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Compile HAVING SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileHavingSQLStatement()
	{
		if ( $this->_is_grouped === FALSE AND isset( static::$sqlStatements[ 'HAVING' ] ) AND ! empty( $this->_having ) )
		{
			return sprintf( static::$sqlStatements[ 'HAVING' ], implode( ', ', $this->_having ) );
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Compile ORDER BY SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileOrderBySQLStatement()
	{
		if ( $this->_is_grouped === FALSE AND isset( static::$sqlStatements[ 'ORDER_BY' ] ) AND ! empty( $this->_order_by ) )
		{
			return sprintf( static::$sqlStatements[ 'ORDER_BY' ], implode( ', ', $this->_order_by ) );
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Compile LIMIT SQL statement portions.
	 *
	 * @access  protected
	 * @return  string|void
	 */
	protected function _compileLimitSQLStatement()
	{
		if ( $this->_limit AND $this->_is_grouped === FALSE )
		{
			if ( $this->_offset )
			{
				return sprintf( static::$sqlStatements[ 'LIMIT_OFFSET' ], $this->_limit, $this->_offset );
			}

			return sprintf( static::$sqlStatements[ 'LIMIT' ], $this->_limit );
		}
	}

	//--------------------------------------------------------------------
}