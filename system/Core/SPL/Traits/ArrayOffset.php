<?php
/**
 * Created by PhpStorm.
 * User: mihawk37
 * Date: 14/08/16
 * Time: 11.51 PM
 */

namespace O2System\Core\SPL\Traits;


use O2System\Core\SPL\ArrayIterator;
use O2System\Core\SPL\ArrayObject;

trait ArrayOffset
{
	/**
	 * Magic method __isset
	 *
	 * @param $offset
	 *
	 * @return bool
	 */
	public function __isset( $offset )
	{
		return $this->offsetExists( $offset );
	}

	/**
	 * Whether a offset exists
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists( $offset )
	{
		$offset = camelcase( $offset );

		if ( parent::offsetExists( $offset ) )
		{
			if ( is_null( parent::offsetGet( $offset ) ) )
			{
				return FALSE;
			}
			elseif ( is_array( parent::offsetGet( $offset ) ) )
			{
				if ( count( parent::offsetGet( $offset ) ) == 0 )
				{
					parent::offsetUnset( $offset );

					return FALSE;
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Key Exists
	 *
	 * Alias for offsetExists
	 *
	 * @param $offset
	 *
	 * @return bool
	 */
	public function keyExists( $offset )
	{
		return $this->offsetExists( $offset );
	}

	/**
	 * Get
	 *
	 * @param $offset
	 *
	 * @return mixed
	 */
	public function get( $offset )
	{
		return $this->offsetGet( $offset );
	}

	/**
	 * Offset to retrieve
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 *
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet( $offset )
	{
		if ( $this->offsetExists( $offset ) )
		{
			return parent::offsetGet( camelcase( $offset ) );
		}
		elseif ( parent::offsetExists( camelcase( $offset ) ) )
		{
			if ( is_array( parent::offsetGet( camelcase( $offset ) ) ) )
			{
				$value = parent::offsetGet( camelcase( $offset ) );

				if ( is_numeric( key( $value ) ) )
				{
					return new ArrayIterator( $value );
				}
				else
				{
					return new ArrayObject( $value );
				}
			}
		}
		elseif ( parent::offsetExists(  $offset ) )
		{
			if ( is_array( parent::offsetGet(  $offset ) ) )
			{
				$value = parent::offsetGet(  $offset  );

				if ( is_numeric( key( $value ) ) )
				{
					return new ArrayIterator( $value );
				}
				else
				{
					return new ArrayObject( $value );
				}
			}
		}

		return new ArrayObject();
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic method __get
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 */
	public function __get( $offset )
	{
		return $this->offsetGet( $offset );
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic method __set
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 */
	public function __set( $offset, $value )
	{
		$this->offsetSet( $offset, $value );
	}

	/**
	 * Offset to set
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet( $offset, $value )
	{
		if ( is_string( $offset ) AND is_array( $value ) AND count( $value ) > 0 )
		{
			if ( filter_var( key( $value ), FILTER_VALIDATE_INT ) === FALSE )
			{
				parent::offsetSet( camelcase( $offset ), new ArrayObject( $value ) );
			}
			else
			{
				parent::offsetSet( camelcase( $offset ), $value );
			}
		}
		else
		{
			parent::offsetSet( camelcase( $offset ), $value );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Set
	 *
	 * @param $offset
	 * @param $value
	 */
	public function set( $offset, $value = NULL )
	{
		if ( is_array( $offset ) )
		{
			$this->clearStorage();

			foreach ( $offset as $key => $value )
			{
				$this->offsetSet( $key, $value );
			}
		}
		else
		{
			$this->offsetSet( $offset, $value );
		}
	}

	// ------------------------------------------------------------------------

	public function fill()
	{
		$arrayFill = call_user_func_array( 'array_fill', func_get_args() );

		$this->merge( $arrayFill );
	}

	/**
	 * Exchange
	 *
	 * Exchange storage into new array values
	 *
	 * @param array $array
	 */
	public function exchange( array $array )
	{
		$this->exchangeArray( $array );
	}

	/**
	 * Offset to unset
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset( $offset )
	{
		if ( $this->offsetExists( $offset ) )
		{
			parent::offsetUnset( camelcase( $offset ) );
		}
	}

	/**
	 * Magic method __unset
	 *
	 * @param $offset
	 */
	public function __unset( $offset )
	{
		$this->offsetUnset( $offset );
	}

	/**
	 * Offset Filter
	 *
	 * Get filtered array storage value
	 *
	 * @param      $offset
	 * @param null $filter
	 *
	 * @return mixed|null
	 */
	public function offsetFilter( $offset, $filter = NULL )
	{
		if ( $this->offsetExists( $offset ) )
		{
			$storage = $this->offsetGet( $offset );

			if ( is_array( $storage ) AND is_array( $filter ) )
			{
				return filter_var_array( $offset, $filter );
			}
			elseif ( is_array( $storage ) AND isset( $filter ) )
			{
				foreach ( $storage as $key => $value )
				{
					$storage[ $key ] = filter_var( $value, $filter );
				}
			}
			elseif ( isset( $filter ) )
			{
				return filter_var( $storage, $filter );
			}

			return $storage;
		}

		return NULL;
	}

	/**
	 * Append
	 *
	 * Append mixed value into storage
	 *
	 * @param mixed $values
	 */
	public function append( $values )
	{
		if ( is_array( $values ) )
		{
			foreach ( $values as $offset => $value )
			{
				$this->offsetSet( $offset, $value );
			}
		}
		else
		{
			parent::append( $values );
		}
	}

	/**
	 * Merge
	 *
	 * Merge one or more arrays
	 */
	public function merge()
	{
		$lists = func_get_args();

		foreach ( $lists as $array )
		{
			$this->append( $array );
		}
	}
}