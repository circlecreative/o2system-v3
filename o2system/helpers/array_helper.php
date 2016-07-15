<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * Array Helpers
 *
 * @package      Application
 * @subpackage   Helpers
 * @category     Helpers
 * @author       Steeven Andrian Salim
 * @copyright    Copyright (c) 2010 - 2013
 * @link         http://o2system.net/user_guide/helpers/array_helper.html
 */
// ------------------------------------------------------------------------
if ( ! function_exists( 'offset' ) )
{
	/**
	 * Offset
	 *
	 * Lets you determine whether an array index is set and whether it has a value.
	 * If the offset is empty it returns NULL (or whatever you specify as the default value.)
	 * Works with ArrayObject, array or class object
	 *
	 * @param      $item
	 * @param      $ArrayObject
	 * @param null $default
	 *
	 * @return null
	 */
	function offset( $item, $ArrayObject, $default = NULL )
	{
		if ( method_exists( $ArrayObject, 'offsetExists' ) )
		{
			if ( $ArrayObject->offsetExists( $item ) )
			{
				return $ArrayObject->offsetGet( $item );
			}
		}
		elseif ( is_array( $ArrayObject ) AND isset( $ArrayObject[ $item ] ) )
		{
			return $ArrayObject[ $item ];
		}
		elseif ( is_object( $ArrayObject ) AND isset( $ArrayObject->{$item} ) )
		{
			return $ArrayObject->{$item};
		}

		return $default;
	}
}

if ( ! function_exists( 'array_limit' ) )
{
	/**
	 * Array Limit
	 *
	 * Limit amount of array
	 *
	 * @param   array      $array Array Content
	 * @param   int|string $limit Num of limit
	 *
	 * @return  array
	 */
	function array_limit( array $array, $limit )
	{
		$return = [ ];

		if ( empty( $array ) )
		{
			return $return;
		}

		$i = 0;
		foreach ( $array as $key => $value )
		{
			if ( $i < $limit )
			{
				$return[ $key ] = $value;
			}
			$i++;
		}

		return $return;
	}
}


if ( ! function_exists( 'array_to_object' ) )
{
	/**
	 * Array to Object
	 *
	 * Convert multidimensional array into multidimensional object
	 *
	 * @param   array      $array Array Content
	 * @param   int|string $depth Num of depth
	 *
	 * @internal    int $depth_counter  Depth Counter
	 *
	 * @return  mixed
	 */
	function array_to_object( $array, $depth = 'ALL', $depth_counter = 0 )
	{
		if ( empty( $array ) OR ! is_array( $array ) )
		{
			return FALSE;
		}

		$object = new stdClass;
		$depth_counter++;
		foreach ( $array as $key => $value )
		{
			if ( strlen( $key ) )
			{
				if ( is_array( $value ) )
				{
					if ( $depth === 'ALL' )
					{
						$object->{$key} = array_to_object( $value, $depth ); // Recursive
					}
					elseif ( is_numeric( $depth ) )
					{
						if ( $depth_counter != $depth )
						{
							$object->{$key} = array_to_object( $value, $depth, $depth_counter ); // Recursive
						}
						else
						{
							$object->{$key} = $value;
						}
					}
					elseif ( is_string( $depth ) && $key == $depth )
					{
						$object->{$key} = $value;
					}
					elseif ( is_array( $depth ) && in_array( $key, $depth ) )
					{
						$object->{$key} = $value;
					}
					else
					{
						$object->{$key} = array_to_object( $value, $depth ); // Recursive
					}
				}
				else
				{
					$object->{$key} = $value;
				}
			}
		}

		return $object;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'array_combined_recursive' ) )
{
	/**
	 * Array Combined
	 *
	 * Combined Multidimensional Array Values
	 *
	 * @example
	 * array_combined_recursive(array1(), array2())
	 *
	 * @return  array
	 */
	function array_combined_recursive()
	{
		$source          = func_get_args();
		$default_methods = [ 'implode' => 'space' ];
		$methods         = [ ];

		foreach ( $source as $content )
		{
			if ( is_array( $content ) )
			{
				$array[] = $content;
			}
			else
			{
				$raw_methods = $content;
			}
		}

		$combined = call_user_func_array( 'array_merge_recursive', $array );

		if ( ! empty( $raw_methods ) )
		{
			$_raw_methods = parse_ini_string( $raw_methods );
			foreach ( $_raw_methods as $key => $method )
			{
				$method = preg_replace( '(\\[(.*?)\\:(.*?)\\])', '$1=$2', $method );
				$method = parse_ini_string( $method );

				$methods[ $key ] = $method;
			}
			// update default method
			$default_methods = element( 'default', $methods, $default_methods );
		}

		$result = [ ];

		foreach ( $combined as $key => $value )
		{
			if ( is_array( $value ) )
			{
				if ( is_multidimensional( $value ) )
				{
					$result[ $key ] = array_combined_recursive( $value, $value, ( isset( $raw_methods ) ? $raw_methods : '' ) ); // Recursive
				}
				else
				{
					$value = array_unique( $value );

					// Define Method
					$method = element( $key, $methods, $default_methods );

					if ( key( $method ) == 'implode' )
					{
						$result[ $key ] = trim( $method[ 'implode' ] == 'space' ? implode( ' ', $value ) : implode( $method[ 'implode' ], $value ) );
					}
					elseif ( key( $method ) == 'replace' )
					{
						$seq = '';

						if ( $method[ 'replace' ] == 'first' )
						{
							$seq = 0;
						}
						elseif ( $method[ 'replace' ] == 'last' )
						{
							$seq = count( $value ) - 1;
						}
						elseif ( is_numeric( $method[ 'replace' ] ) )
						{
							$seq = $method[ 'replace' ];
						}
						else
						{
							trigger_error( "Invalid method replace sequence", E_ERROR );
						}

						$default        = element( 0, $value );
						$result[ $key ] = trim( element( $seq, $value, $default ) );
					}
					elseif ( key( $method ) == 'combine' )
					{
						$result[ $key ] = ( is_array( $value ) && count( $value ) == 1 ? current( $value ) : $value );
					}
				}
			}
			else
			{
				$result[ $key ] = $value;
			}
		}

		return $result;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'array_filter_value' ) )
{
	/**
	 * Recursive Filter Array Value
	 *
	 * Remove element by the value of array
	 *
	 * @param   array $array Array Source
	 * @param   mixed $value
	 * @param   int   $limit
	 *
	 * @return  array
	 */
	function array_filter_value( &$array, $value, $limit = 0 )
	{
		if ( is_array( $value ) )
		{
			foreach ( $value as $remove )
			{
				$array = array_filter_value( $array, $remove, $limit );
			}

			return $array;
		}

		$result = [ ];
		$count  = 0;

		foreach ( $array as $key => $value )
		{
			if ( $count > 0 and $count == $limit )
			{
				return $result;
			}
			if ( ! is_array( $value ) )
			{
				if ( $key != $value )
				{
					$result[ $key ] = $value;
					$count++;
				}
			}
			else
			{
				$sub = array_filter_value( $value, $value, $limit );
				if ( count( $sub ) > 0 )
				{
					if ( $key != $value )
					{
						$result[ $key ] = $sub;
						$count += count( $sub );
					}
				}
			}
		}

		return $result;
	}
}
// ------------------------------------------------------------------------


if ( ! function_exists( 'array_assign_recursive' ) )
{
	/**
	 * Reasign Element
	 *
	 * Reasign key of multidimensional array
	 *
	 * Sample Data
	 * $data = Array([title] => Array ([value] => 1) [address] => Array([value] => 1 [logo] => Array([value] => 1)
	 * [country] => Array([value] => 1))
	 *
	 * Sample to use
	 * print_r ( reassign_key_element ( 'value',$data ) );
	 *
	 * Sample Output
	 * Array([title] => 1 [address] => Array([0] => 1 [logo] => 1 [country] => 1)
	 *
	 * @param   string $element Element key of array
	 * @param   arary  $array   Array Source
	 *
	 * @return  array
	 */
	function array_assign_recursive( $element, &$array, $limit = 0 )
	{
		if ( is_array( $element ) )
		{
			foreach ( $element as $remove )
			{
				$array = array_assign_recursive( $remove, $array, $limit );
			}

			return $array;
		}
		$new_array = [ ];
		$count     = 0;
		foreach ( $array as $key => $value )
		{
			if ( $count > 0 and $count == $limit )
			{
				return $new_array;
			}
			if ( count( $value ) == 1 )
			{
				if ( is_array( $value ) )
				{
					if ( array_key_exists( $element, $value ) )
					{
						$new_array[ $key ] = $value[ $element ];
						$count++;
					}
				}
			}
			else
			{
				$sub = array_assign_recursive( $element, $value, $limit );
				if ( count( $sub ) > 0 )
				{
					if ( array_key_exists( $element, $value ) )
					{
						array_unshift( $sub, $value[ $element ] );
						$new_array[ $key ] = $sub;
						$count += count( $sub );
					}
					else
					{
						$new_array[ $key ] = $sub;
						$count += count( $sub );
					}
				}
			}
		}

		return $new_array;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'array_search_recursive' ) )
{
	/**
	 * Array Search Recursive
	 *
	 * Search key or value in multi-dimensional array
	 *
	 * @param   mixed $needle   Needle to search
	 * @param   array $haystack Array Source
	 * @param   bool  $return   Return Key or Bool
	 *
	 * @return  bool
	 */
	function array_search_recursive( $needle, $haystack, $return = TRUE )
	{
		foreach ( $haystack as $key => $value )
		{
			if ( $needle == $value )
			{
				if ( $return == TRUE )
				{
					return $key;
				}
				else
				{
					return TRUE;
				}
			}
			elseif ( is_array( $value ) )
			{
				return array_search_recursive( $needle, $value );
			}
		}

		return FALSE;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'array_unique_recursive' ) )
{
	/**
	 * Array Unique Recursive
	 *
	 * @param   array $array Array Source
	 *
	 * @return  array
	 */
	function array_unique_recursive( $array )
	{
		$serialized = array_map( 'serialize', $array );
		$unique     = array_unique( $serialized );

		return array_intersect_key( $array, $unique );
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'is_multidimensional' ) )
{
	/**
	 * Is Multidimensional
	 *
	 * @param   array $array Array Source
	 *
	 * @return  bool
	 */
	function is_multidimensional( $array )
	{
		if ( count( $array ) != count( $array, COUNT_RECURSIVE ) )
		{
			return TRUE;
		}

		return FALSE;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'array_keys_recursive' ) )
{
	/**
	 * Array Keys Recursive
	 *
	 * @param array $array Array Source
	 *
	 * @return array
	 */
	function array_keys_recursive( $array, $total = 0, $depth = 0 )
	{
		$result = [ ];
		for ( $i = 0; $i < count( $array ); $i++ )
		{
			$total = ( $total == 0 ? count( $array ) - 1 : $total );
			$depth = ( $depth == 0 ? $depth + 1 : $depth );
			// Define array key
			$key = reset( $array );

			// Remove first node
			array_shift( $array );

			// Recursive Key
			$result[ $key ] = ( $depth == $total ? end( $array ) : array_keys_recursive( $array, $total, $depth + 1 ) );

			// Stop because it's allready built
			break;
		}

		return $result;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'array_flatten_recursive' ) )
{
	/**
	 * Array Flatten Recursive
	 *
	 * @param array $array Array Source
	 *
	 * @return array
	 */
	function array_flatten_recursive( array $array = [ ] )
	{
		$return = [ ];

		foreach ( $array as $key => $value )
		{
			if ( is_array( $value ) )
			{
				$return = array_merge( $return, array_flatten_recursive( $value ) );
			}
			else
			{
				$return[ $key ] = $value;
			}
		}

		return $return;
	}
}
// ------------------------------------------------------------------------

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license      http://opensource.org/licenses/MIT	MIT License
 * @link         http://codeigniter.com
 * @since        Version 1.0.0
 * @filesource
 */

/**
 * CodeIgniter Array Helpers
 *
 * @package        CodeIgniter
 * @subpackage     Helpers
 * @category       Helpers
 * @author         EllisLab Dev Team
 * @link           http://codeigniter.com/user_guide/helpers/array_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists( 'element' ) )
{
	/**
	 * Element
	 *
	 * Lets you determine whether an array index is set and whether it has a value.
	 * If the element is empty it returns NULL (or whatever you specify as the default value.)
	 *
	 * @param    string
	 * @param    array
	 * @param    mixed
	 *
	 * @return    mixed    depends on what the array contains
	 */
	function element( $item, $array, $default = NULL )
	{
		if ( is_null( $array ) )
		{
			var_dump( $item );
		}

		return array_key_exists( $item, $array ) ? $array[ $item ] : $default;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'random_element' ) )
{
	/**
	 * Random Element - Takes an array as input and returns a random element
	 *
	 * @param    array
	 *
	 * @return    mixed    depends on what the array contains
	 */
	function random_element( $array )
	{
		return is_array( $array ) ? $array[ array_rand( $array ) ] : $array;
	}
}

// --------------------------------------------------------------------

if ( ! function_exists( 'elements' ) )
{
	/**
	 * Elements
	 *
	 * Returns only the array items specified. Will return a default value if
	 * it is not set.
	 *
	 * @param    array
	 * @param    array
	 * @param    mixed
	 *
	 * @return    mixed    depends on what the array contains
	 */
	function elements( $items, $array, $default = NULL )
	{
		$return = [ ];

		is_array( $items ) OR $items = [ $items ];

		foreach ( $items as $item )
		{
			$return[ $item ] = array_key_exists( $item, $array ) ? $array[ $item ] : $default;
		}

		return $return;
	}
}