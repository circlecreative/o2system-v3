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
 * Object Helpers
 *
 * @package        O2System
 * @subpackage     helpers
 * @category       Helpers
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/helpers/object.html
 */
// ------------------------------------------------------------------------

if ( ! function_exists( 'object_element' ) )
{
	/**
	 * Object Element
	 *
	 * Lets you determine whether an object element is set and whether it has a value.
	 * If the element is empty it returns FALSE (or whatever you specify as the default value.)
	 *
	 * @param   string
	 * @param   array
	 * @param   mixed
	 *
	 * @return  mixed   depends on what the array contains
	 */
	function object_element( $item, $object, $default = FALSE )
	{
		if ( is_object( $object ) )
		{
			$obj = get_object_vars( $object );
			if ( element( $item, $obj ) )
			{
				return $object->$item;
			}
		}

		return $default;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'object_to_array' ) )
{
	/**
	 * Object to Array
	 *
	 * Convert multidimensional object into multidimensional array
	 *
	 * @access    public
	 *
	 * @param    object
	 *
	 * @return    array depends on what the object contains
	 */
	function object_to_array( $object )
	{
		$array = [ ];

		if ( is_object( $object ) )
		{
			$object = (array) $object;
		}

		if ( is_array( $object ) )
		{
			foreach ( $object as $keys => $value )
			{
				$keys           = preg_replace( "/^\\0(.*)\\0/", "", $keys );
				$keys           = str_replace( '@', '', $keys );
				$array[ $keys ] = object_to_array( $value );
			}
		}
		else
		{
			$array = $object;
		}

		return $array;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists( 'remove_object' ) )
{
	/**
	 * Remove Object
	 *
	 * Remove element with the value from array
	 *
	 * @element    string or array
	 * @object     source object
	 * @limit      numeric
	 * @return    new object
	 */
	function remove_object( $element, &$object, $limit = 0 )
	{
		if ( is_array( $element ) )
		{
			foreach ( $element as $remove )
			{
				$array = remove_object( $remove, $object, $limit );
			}

			return $array;
		}

		$new_object = new stdClass;
		$count      = 0;
		foreach ( $object as $key => $value )
		{
			if ( $count > 0 and $count == $limit )
			{
				return $new_object;
			}
			if ( ! is_array( $value ) )
			{
				if ( $key != $element )
				{
					$new_object->{$key} = $value;
					$count++;
				}
			}
			else
			{
				$sub = remove_object( $element, $value, $limit );
				if ( count( $sub ) > 0 )
				{
					if ( $key != $element )
					{
						$new_object->{$key} = $sub;
						$count += count( $sub );
					}
				}
			}
		}

		return $new_object;
	}
}
// ------------------------------------------------------------------------