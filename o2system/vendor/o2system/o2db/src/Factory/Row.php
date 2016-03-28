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

namespace O2System\DB\Factory
{

	use IteratorAggregate;
	use ArrayAccess;
	use Countable;
	use Serializable;

	/**
	 * Row Object Class
	 *
	 * @category    Database Class
	 * @author      O2System Developer Team
	 * @link        http://o2system.in/features/o2db/metadata/result
	 */
	class Row implements IteratorAggregate, ArrayAccess, Countable, Serializable
	{
		protected $_fields     = array();
		protected $_num_fields = 0;

		public function __construct( $array = array() )
		{
			if ( ! empty( $array ) )
			{
				$this->_fields = $array;
				$this->_num_fields = count( $array );
			}
		}

		public function fields_list()
		{
			return array_keys( $this->_fields );
		}

		public function num_fields()
		{
			return $this->count();
		}

		public function __get( $field )
		{
			return $this->offsetGet( $field );
		}

		public function getIterator()
		{
			return new \ArrayIterator( $this->_fields );
		}

		public function offsetSet( $field, $value )
		{
			if ( $this->_is_json( $value ) )
			{
				$value = new Row\Metadata( json_decode( $value, TRUE ) );
			}
			elseif ( $this->_is_serialize( $value ) )
			{
				$value = new Row\Metadata( unserialize( $value ) );
			}

			$this->_fields[ $field ] = $value;
		}

		public function offsetExists( $field )
		{
			return isset( $this->_fields[ $field ] );
		}

		public function offsetUnset( $field )
		{
			unset( $this->_fields[ $field ] );
		}

		public function offsetGet( $field )
		{
			return isset( $this->_fields[ $field ] ) ? $this->_fields[ $field ] : NULL;
		}

		public function count()
		{
			if ( $this->_num_fields == 0 )
			{
				$this->_num_fields = count( $this->fields_list() );
			}

			return $this->_num_fields;
		}

		public function serialize()
		{
			return serialize( $this->_fields );
		}

		public function unserialize( $fields )
		{
			$this->_fields = unserialize( $fields );
		}

		public function __toString()
		{
			return json_encode( $this->_fields );
		}

		public function __set( $name, $value )
		{
			$this->offsetSet( $name, $value );
		}

		// ------------------------------------------------------------------------

		protected function _is_serialize( $string )
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
			$end = '';

			if ( ! isset( $string[ 0 ] ) ) return FALSE;

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

		// ------------------------------------------------------------------------

		protected function _is_json( $string )
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

		// ------------------------------------------------------------------------

		public function __toArray()
		{
			return $this->_fields;
		}
	}
}

// ------------------------------------------------------------------------

namespace O2System\DB\Factory\Row
{
	class Metadata extends \ArrayObject
	{
		public function __construct( $data = array() )
		{
			parent::__construct( [ ], \ArrayObject::ARRAY_AS_PROPS );

			if ( ! empty( $data ) )
			{
				foreach ( $data as $key => $value )
				{
					$this->__set( $key, $value );
				}
			}
		}

		public function __set( $index, $value )
		{
			if ( is_array( $value ) )
			{
				$value = new Metadata( $value );
			}

			$this->offsetSet( $index, $value );
		}
	}
}