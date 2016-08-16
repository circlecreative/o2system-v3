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

namespace O2System\DB\Metadata\Result;

// ------------------------------------------------------------------------

use ArrayAccess;
use Countable;
use IteratorAggregate;
use O2System\Core\SPL\ArrayIterator;
use O2System\Core\SPL\ArrayObject;
use O2System\DB\Metadata\Result\Row\SimpleJSONField;
use O2System\DB\Metadata\Result\Row\SimpleSerializeField;

/**
 * Row Object Class
 *
 * @category    Database Class
 * @author      O2System Developer Team
 * @link        http://o2system.in/features/o2db/metadata/result
 */
class Row implements IteratorAggregate, ArrayAccess, Countable
{
	/**
	 * List of result row fields
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_fields = [ ];

	/**
	 * Num of result row fields
	 *
	 * @type int
	 */
	protected $_num_fields = 0;

	// ------------------------------------------------------------------------

	/**
	 * Row constructor.
	 *
	 * @param array $array
	 */
	public function __construct( $array = [ ] )
	{
		if ( ! empty( $array ) )
		{
			$this->_fields     = $array;
			$this->_num_fields = count( $array );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch Fields Into
	 *
	 * @param string $class
	 * @param array  $args
	 *
	 * @return \O2System\Core\SPL\ArrayObject|object
	 */
	public function fetchFieldsInto( $class = 'O2System\Core\SPL\ArrayObject', array $args = [ ] )
	{
		if ( class_exists( $class ) AND $class !== 'O2System\Core\SPL\ArrayObject' )
		{
			$reflection  = new \ReflectionClass( $class );
			$constructor = $reflection->getConstructor();

			$object = is_null( $constructor ) ? $reflection->newInstance() : $reflection->newInstanceArgs( $args );

			foreach ( $this->_fields as $key => $value )
			{
				$object->__set( $key, $value );
			}

			return $object;
		}

		return new ArrayObject( $this->_fields );
	}

	// ------------------------------------------------------------------------

	/**
	 * Num Fields
	 *
	 * Num of row fields ( Row::count() alias )
	 *
	 * @return int
	 */
	public function numFields()
	{
		return $this->count();
	}

	// ------------------------------------------------------------------------

	/**
	 * Count
	 *
	 * Num of row fields
	 *
	 * @return int
	 */
	public function count()
	{
		if ( $this->_num_fields == 0 )
		{
			$this->_num_fields = count( $this->keys() );
		}

		return $this->_num_fields;
	}

	// ------------------------------------------------------------------------

	/**
	 * Result Fields
	 *
	 * @param string $type Array|Object|Class Name
	 *
	 * @return array|Row|mixed
	 */
	public function fields( $type = 'array' )
	{
		if ( class_exists( $type ) )
		{
			return $this->fetchFieldsInto( $type );
		}
		elseif ( $type === 'array' )
		{
			return $this->__toArray();
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Keys
	 *
	 * Return row fields keys
	 *
	 * @return array
	 */
	public function keys()
	{
		return array_keys( $this->_fields );
	}

	/**
	 * Values
	 *
	 * Return row fields values
	 *
	 * @return array
	 */
	public function values()
	{
		return array_values( $this->_fields );
	}

	/**
	 * Magic method __get
	 *
	 * @param $field
	 *
	 * @return mixed|null
	 */
	public function __get( $field )
	{
		return $this->offsetGet( $field );
	}

	/**
	 * Magic method __set to set variable into row fields
	 *
	 * @param string $field Field name
	 * @param mixed  $value Field value
	 */
	public function __set( $field, $value )
	{
		$this->offsetSet( $field, $value );
	}

	/**
	 * Offset Get
	 *
	 * Get single field value
	 *
	 * @param mixed $field
	 *
	 * @return mixed|null
	 */
	public function offsetGet( $field )
	{
		return isset( $this->_fields[ $field ] ) ? $this->_fields[ $field ] : NULL;
	}

	/**
	 * Offset Set
	 *
	 * Assign offset value into Row::$_fields[offset] = value
	 *
	 * @param string $field Field name
	 * @param mixed  $value Field value
	 */
	public function offsetSet( $field, $value )
	{
		if ( $this->_isJson( $value ) )
		{
			$value = new SimpleJSONField( json_decode( $value, TRUE ) );
		}
		elseif ( $this->_isSerialize( $value ) )
		{
			$value = new SimpleSerializeField( unserialize( $value ) );
		}

		$this->_fields[ $field ] = $value;
	}

	/**
	 * Is JSON
	 *
	 * Validate if field value is JSON format
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	protected function _isJson( $string )
	{
		// make sure provided input is of type string
		if ( ! is_string( $string ) )
		{
			return FALSE;
		}

		// trim white spaces
		$string = trim( $string );

		// get first character
		$first_char = substr( $string, 0, 1 );

		// get last character
		$last_char = substr( $string, -1 );

		// check if there is a first and last character
		if ( ! $first_char || ! $last_char )
		{
			return FALSE;
		}

		// make sure first character is either { or [
		if ( $first_char !== '{' && $first_char !== '[' )
		{
			return FALSE;
		}

		// make sure last character is either } or ]
		if ( $last_char !== '}' && $last_char !== ']' )
		{
			return FALSE;
		}

		// let's leave the rest to PHP.
		// try to decode string
		json_decode( $string );

		// check if error occurred
		if ( json_last_error() === JSON_ERROR_NONE )
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Is Serialize
	 *
	 * Validate if field value is PHP serialize format
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	protected function _isSerialize( $string )
	{
		// Bit of a give away this one
		if ( ! is_string( $string ) )
		{
			return FALSE;
		}

		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ( $string === 'b:0;' )
		{
			return TRUE;
		}

		$length = strlen( $string );
		$end    = '';

		if ( ! isset( $string[ 0 ] ) )
		{
			return FALSE;
		}

		switch ( $string[ 0 ] )
		{
			case 's':
				if ( @$string[ $length - 2 ] !== '"' )
				{
					return FALSE;
				}
			case 'b':
			case 'i':
			case 'd':
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a':
			case 'O':
				$end .= '}';

				if ( @$string[ 1 ] !== ':' )
				{
					return FALSE;
				}

				switch ( $string[ 2 ] )
				{
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
						break;

					default:
						return FALSE;
				}
			case 'N':
				$end .= ';';

				if ( $string[ $length - 1 ] !== $end[ 0 ] )
				{
					return FALSE;
				}
				break;

			default:
				return FALSE;
		}

		return (bool) unserialize( $string );
	}

	/**
	 * Get Iterator
	 *
	 * Get external array iterator
	 *
	 * @return \O2System\Core\SPL\ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator( $this->_fields );
	}

	/**
	 * Offset Exists
	 *
	 * Validate whether field exists or not
	 *
	 * @param mixed $field
	 *
	 * @return bool
	 */
	public function offsetExists( $field )
	{
		return isset( $this->_fields[ $field ] );
	}

	/**
	 * Offset Unset
	 *
	 * Unset specified row field
	 *
	 * @param mixed $field
	 */
	public function offsetUnset( $field )
	{
		unset( $this->_fields[ $field ] );
	}

	/**
	 * __toArray
	 *
	 * Convert each rows into array
	 *
	 * @return array
	 */
	public function __toArray()
	{
		return (array) $this->_fields;
	}

	// --------------------------------------------------------------------

	/**
	 * __toSerialize
	 *
	 * Convert rows into PHP serialize array
	 *
	 * @see http://php.net/manual/en/function.serialize.php
	 *
	 * @param int $options JSON encode options, default JSON_PRETTY_PRINT
	 * @param int $depth   Maximum depth of JSON encode. Must be greater than zero.
	 *
	 * @return string
	 */
	public function __toSerialize()
	{
		return serialize( $this->__toArray() );
	}

	// --------------------------------------------------------------------

	/**
	 * __toJSON
	 *
	 * @see http://php.net/manual/en/function.json-encode.php
	 *
	 * @param int $options JSON encode options, default JSON_PRETTY_PRINT
	 * @param int $depth   Maximum depth of JSON encode. Must be greater than zero.
	 *
	 * @return string
	 */
	public function __toJSON( $options = JSON_PRETTY_PRINT, $depth = 512 )
	{
		$depth = $depth == 0 ? 512 : $depth;

		return call_user_func_array( 'json_encode', [ $this->_fields, $options, $depth ] );
	}

	// --------------------------------------------------------------------

	/**
	 * __toString
	 *
	 * Convert result rows into JSON String
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) json_encode( $this->_fields );
	}
}