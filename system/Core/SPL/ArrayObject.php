<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 6:57 PM
 */

namespace O2System\Core\SPL;

use O2System\Core\SPL\Traits\ArrayConversion;
use O2System\Core\SPL\Traits\ArrayOffset;
use O2System\Core\SPL\Traits\ArrayStorage;

/**
 * Class ArrayObject
 *
 * @package O2System\Glob
 */
class ArrayObject extends \ArrayObject
{
	use ArrayConversion;
	use ArrayOffset;
	use ArrayStorage;

	/**
	 * ArrayObject constructor.
	 *
	 * @param array $array
	 * @param int   $option
	 */
	public function __construct( array $array = [ ], $option = \ArrayObject::ARRAY_AS_PROPS )
	{
		parent::__construct( [ ], $option );

		if ( ! empty( $array ) )
		{
			foreach ( $array as $offset => $value )
			{
				$this->offsetSet( $offset, $value );
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Fill Offsets
	 *
	 * Fill storage with values, specifying offsets
	 *
	 * @param array $offsets
	 * @param       $value
	 */
	public function fillOffsets( array $offsets, $value )
	{
		$arrayFillKeys = array_fill_keys( $offsets, $value );

		$this->set( $arrayFillKeys );
	}

	/**
	 * __call
	 *
	 * ArrayObject __call Magic method
	 *
	 * @param       $method
	 * @param array $args
	 *
	 * @return array|mixed|object
	 */
	public function __call( $method, array $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( [ $this, $method ], $args );
		}

		$method = camelcase( $method );

		if ( $this->offsetExists( $method ) )
		{
			if ( empty( $args ) )
			{
				return $this->offsetGet( $method );
			}

			// Let's get the registry values
			$registry = $this->offsetGet( $method );

			// List arguments
			@list( $offset, $action ) = $args;

			if ( isset( $registry->{$offset} ) )
			{
				$value = $registry->{$offset};
			}
			elseif ( isset( $registry[ $offset ] ) )
			{
				$value = $registry[ $offset ];
			}

			if ( isset( $action ) )
			{
				if ( is_callable( $action ) )
				{
					return $action( $value );
				}
				elseif ( function_exists( $action ) )
				{
					$value = is_object( $value ) ? get_object_vars( $value ) : $value;

					return array_map( $action, $value );
				}
				elseif ( in_array( $action, [ 'array', 'object', 'keys', 'values' ] ) )
				{
					switch ( $action )
					{
						default:
						case 'array':
							$value = ( is_array( $value ) ? $value : (array) $value );
							break;
						case 'object':
							$value = ( is_object( $value ) ? $value : (object) $value );
							break;
						case 'keys':
							$value = is_object( $value ) ? get_object_vars( $value ) : $value;
							$value = array_keys( $value );
							break;
						case 'values':
							$value = is_object( $value ) ? get_object_vars( $value ) : $value;
							$value = array_values( $value );
							break;
					}

					if ( isset( $args[ 2 ] ) )
					{
						if ( is_callable( $args[ 2 ] ) )
						{
							return $args[ 2 ]( $value );
						}
						elseif ( function_exists( $args[ 2 ] ) )
						{
							return array_map( $args[ 2 ], $value );
						}
					}
					else
					{
						return $value;
					}
				}
				elseif ( in_array( $action, [ 'json', 'serialize', 'flatten', 'flatten_keys', 'flatten_values' ] ) )
				{
					switch ( $action )
					{
						default:
						case 'json':
							return json_encode( $value );
							break;
						case 'serialize':
							return serialize( $value );
							break;
						case 'flatten':
							$value = is_object( $value ) ? get_object_vars( $value ) : $value;
							$glue  = isset( $args[ 2 ] ) ? $args[ 2 ] : ', ';

							foreach ( $value as $key => $val )
							{
								if ( is_bool( $val ) )
								{
									$val = $val === TRUE ? 'true' : 'false';
								}

								if ( is_numeric( $key ) )
								{
									$result[] = $val;
								}
								elseif ( is_string( $key ) )
								{
									if ( is_array( $val ) )
									{
										$val = implode( $glue, $val );
									}

									$result[] = $key . ' : ' . $val;
								}
							}

							return implode( $glue, $result );

							break;
						case 'flatten_keys':
							$value = is_object( $value ) ? get_object_vars( $value ) : $value;
							$glue  = isset( $args[ 2 ] ) ? $args[ 2 ] : ', ';

							return implode( $glue, array_keys( $value ) );
							break;
						case 'flatten_values':
							$value = is_object( $value ) ? get_object_vars( $value ) : $value;
							$glue  = isset( $args[ 2 ] ) ? $args[ 2 ] : ', ';

							foreach ( array_values( $value ) as $val )
							{
								if ( is_bool( $val ) )
								{
									$val = $val === TRUE ? 'true' : 'false';
								}

								$result[] = $val;
							}

							return implode( $glue, $result );
							break;
					}
				}
			}
			elseif ( isset( $value ) )
			{
				return $value;
			}
			else
			{
				return $registry;
			}
		}
	}

	public function getArrayKeys( $searchValue = NULL, $strict = FALSE )
	{
		if ( is_null( $searchValue ) )
		{
			return array_keys( $this->getArrayCopy() );
		}

		return array_keys( $this->getArrayCopy(), $searchValue, $strict );
	}
}