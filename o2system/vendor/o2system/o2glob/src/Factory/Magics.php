<?php
/**
 * O2Glob
 *
 * Singleton Global Class Libraries for PHP 5.4 or newer
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2glob/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\Glob\Factory;

// ------------------------------------------------------------------------

use O2System\Glob\Helpers\Inflector;

/**
 * Magics Trait Class
 *
 * This class is works to utilize Basics and Statics Trait Class
 * to has additional magics functionality methods
 *
 * @package        O2Glob
 * @category       Factory Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/standalone/o2glob/user-guide/magics.html
 */
trait Magics
{
	/**
	 * Reflection of Called Class
	 *
	 * @access  protected
	 * @static
	 *
	 * @type    object  reflection object class of called class
	 */
	protected static $_reflection;

	/**
	 * Instance of Called Class
	 *
	 * @access  protected
	 * @static
	 *
	 * @type     object object of called class
	 */
	protected static $_instance;

	/**
	 * Registry Property of Called Class
	 *
	 * @access  protected
	 * @static
	 *
	 * @type    object|array    property registry of called class
	 */
	protected static $_registry;

	/**
	 * List of Public Called Class Methods
	 *
	 * @access  protected
	 * @static
	 *
	 * @type     array
	 */
	protected static $_methods;

	/**
	 * List of Public Called Class Methods Maps
	 *
	 * @access  protected
	 * @static
	 *
	 * @type     array
	 */
	protected static $_methods_maps = array(
		'load' => 'loader',
	);

	/**
	 * List of Public Called Class Properties
	 *
	 * @access  protected
	 * @static
	 *
	 * @type     array
	 */
	protected static $_properties;

	/**
	 * List of Public Called Class Properties
	 *
	 * @access  protected
	 * @static
	 *
	 * @type     array
	 */
	protected static $_properties_maps = array(
		'registry' => '_registry',
		'load'     => 'loader',
	);

	// ------------------------------------------------------------------------

	/**
	 * Reflection
	 * This method is used to reflect the called class
	 *
	 * @access   protected
	 * @final    this method can't be overwritten
	 *
	 * @uses     \ReflectionClass()
	 * @uses     \ReflectionMethod()
	 * @uses     \ReflectionProperty()
	 *
	 * @used_by  all \O2System\Glob Classes
	 *
	 * @property-write  array $_methods
	 * @property-write  array $_properties
	 */
	final protected static function _reflection( $called_class = NULL )
	{
		$called_class = isset( $called_class ) ? $called_class : get_called_class();

		static::$_reflection = new \ReflectionClass( $called_class );

		$methods = array(
			'public'    => \ReflectionMethod::IS_PUBLIC,
			'protected' => \ReflectionMethod::IS_PROTECTED,
			'private'   => \ReflectionMethod::IS_PRIVATE,
			'static'    => \ReflectionMethod::IS_STATIC,
		);

		foreach ( $methods as $method => $reflect )
		{
			$reflection = static::$_reflection->getMethods( $reflect );

			if ( ! empty( $reflection ) )
			{
				foreach ( $reflection as $object )
				{
					static::$_methods[ $method ][] = $object->name;
				}
			}
		}

		$properties = array(
			'public'    => \ReflectionProperty::IS_PUBLIC,
			'protected' => \ReflectionProperty::IS_PROTECTED,
			'private'   => \ReflectionProperty::IS_PRIVATE,
			'static'    => \ReflectionProperty::IS_STATIC,
		);

		foreach ( $properties as $property => $reflect )
		{
			$reflection = static::$_reflection->getProperties( $reflect );

			if ( ! empty( $reflection ) )
			{
				foreach ( $reflection as $object )
				{
					static::$_properties[ $property ][] = $object->name;
				}
			}
		}

		static::$_registry = new Registry();
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Magic Method
	 * Magic method to write called class properties
	 *
	 * @access      public
	 * @final       this method can't be overwritten
	 *
	 * @param   string $name  property name
	 * @param   mixed  $value property value
	 */
	final public function __set( $name, $value )
	{
		if ( method_exists( $this, '__setOverride' ) )
		{
			$this->__setOverride( $name, $value );
		}
		else
		{
			$this->{$name} = $value;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Method Map
	 * Set method index map
	 *
	 * @access      public
	 * @final       this method can't be overwritten
	 *
	 * @param   string $name Origin map index name
	 * @param   mixed  $map  New map index name
	 */
	final public function __set_method_map( $name, $map )
	{
		static::$_methods_maps[ $name ] = $map;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Property Map
	 * Set property index map
	 *
	 * @access      public
	 * @final       this method can't be overwritten
	 *
	 * @param   string $name Origin map index name
	 * @param   mixed  $map  New map index name
	 */
	final public function __set_property_map( $name, $map )
	{
		static::$_property_maps[ $name ] = $map;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set registry
	 * This method is used for write called class registry properties
	 *
	 * @access      public
	 * @final       this method can't be overwritten
	 *
	 * @param string $name  registry property name
	 * @param mixed  $value registry property value
	 */
	final public function __set_registry( $name, $value )
	{
		static::$_registry[ $name ] = $value;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get
	 * Magic method used as called class property getter
	 *
	 * @access      public
	 * @static      static class method
	 *
	 * @param   string $property property name
	 *
	 * @return mixed
	 */
	final public function &__get( $property )
	{
		$property = strtolower( $property );
		$property = isset( static::$_properties_maps[ $property ] ) ? static::$_properties_maps[ $property ] : $property;

		$prop[ 0 ] = NULL;

		if ( property_exists( $this, $property ) )
		{
			if ( isset( $this->{$property} ) )
			{
				$prop[ 0 ] = $this->{$property};
			}
			elseif ( isset( static::${$property} ) )
			{
				$prop[ 0 ] = static::${$property};
			}
		}
		elseif ( isset( static::$_properties[ 'public' ] ) AND
			in_array( $property, static::$_properties[ 'public' ] )
		)
		{
			$prop[ 0 ] = $this->{$property};
		}
		elseif ( isset( static::$_registry[ $property ] ) )
		{
			$prop[ 0 ] = static::$_registry[ $property ];
		}
		elseif ( method_exists( $this, '__getOverride' ) )
		{
			$prop[ 0 ] = $this->__getOverride( $property );
		}

		return $prop[ 0 ];
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Property
	 * Magic method used as called class property getter with custom returning value conversion
	 *
	 * @access    private
	 * @final     this method can't be overwritten
	 *
	 * @method    static ::$_instance->__get()
	 *
	 * @param   $property   string of property name
	 * @param   $args       array of parameters
	 *
	 * @return mixed
	 */
	final static private function __getProperty( $property, $args = array() )
	{
		if ( empty( $args ) )
		{
			return static::$_instance->__get( $property );
		}
		else
		{
			$entry = static::$_instance->__get( $property );
			@list( $index, $action, $params ) = $args;

			// if the entry is string nothing else to be proceed
			if ( is_string( $entry ) ) return $entry;

			if ( is_array( $entry ) )
			{
				$data = $entry;

				if ( isset( $entry[ $index ] ) )
				{
					$data = $entry[ $index ];
				}
				else
				{
					@list( $action, $params ) = $args;
				}
			}
			elseif ( is_object( $entry ) )
			{
				$data = $entry;

				if ( isset( $entry->{$index} ) )
				{
					$data = $entry->{$index};
				}
				else
				{
					@list( $action, $params ) = $args;
				}
			}

			// if the data is string or there is no action nothing to be proceed
			if ( is_string( $data ) OR empty( $action ) ) return $data;

			if ( in_array( $action, array( 'array', 'object', 'keys' ) ) )
			{
				switch ( $action )
				{
					default:
					case 'object';
						if ( is_array( $data ) )
						{
							return (object) $data;
						}

						return $data;
						break;
					case 'array';
						if ( is_object( $data ) )
						{
							return get_object_vars( $data );
						}

						return $data;
						break;
					case 'keys';
						if ( is_object( $data ) )
						{
							$data = get_object_vars( $data );
						}

						return array_keys( $data );
						break;
				}
			}
			elseif ( in_array( strtolower( $action ), array( 'json', 'serialize' ) ) )
			{
				switch ( $action )
				{
					default:
					case 'json':
						return json_encode( $data );
						break;
					case 'serialize';
						return serialize( $data );
						break;
				}
			}
			elseif ( isset( $data->{$action} ) )
			{
				return $data->{$action};
			}
			elseif ( isset( $data[ $action ] ) )
			{
				return $data[ $action ];
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Call
	 * Magic method caller
	 *
	 * @access  public
	 * @final   this method can't be overwritten directly, to overwrite this method create __callOverride($method,
	 *          $args = array())
	 *
	 * @param   $method   string of method name or property name
	 * @param   $args     array of parameters
	 *
	 * @return mixed
	 */
	public function __call( $method, $args = array() )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		else
		{
			return static::__callStatic( $method, $args );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Call Static
	 * Magic method to bewitch class methods or properties call
	 *
	 * Methods: methods can be called as static method or non-static method
	 * Properties: properties can be called as a method with custom returning value conversion
	 *
	 * @access      public
	 * @final       this method can't be overwritten
	 *
	 * @method      _init() called class init method
	 *              _getProperty() called class method
	 *
	 * @property-read   static::$_instance
	 * @property-read   static::$_reflection
	 * @property-read   static::$_registry
	 *
	 * @param   string $method method or property name
	 * @param   array  $args   array of parameters
	 *
	 * @return mixed
	 */
	final public static function __callStatic( $method, $args = array() )
	{
		$method = strtolower( $method );
		$method = isset( static::$_methods_maps[ $method ] ) ? static::$_methods_maps[ $method ] : $method;

		// check if the called class has been initialized and reflected
		if ( empty( static::$_instance ) AND empty( static::$_reflection ) )
		{
			$class = get_called_class();
			call_user_func( $class . '::initialize' );
		}

		// check whether the called method is a to call class registry properties
		if ( $method === 'registry' )
		{
			return static::$_registry;
		}
		elseif ( isset( static::$_registry[ $method ] ) )
		{
			if ( empty( $args ) )
			{
				return static::$_registry->__get( $method );
			}

			return static::$_registry->__call( $method, $args );
		}
		elseif ( isset( static::$_instance->{$method} ) )
		{
			return static::$_instance->{$method};
		}

		// check whether is a public non static method
		elseif ( isset( static::$_methods[ 'public' ] ) AND (
				in_array( $non_static_method = $method, static::$_methods[ 'public' ] ) OR
				in_array( $non_static_method = '_' . $method, static::$_methods[ 'public' ] ) OR
				in_array( $non_static_method = str_replace( '_', '', $method ), static::$_methods[ 'public' ] )
			)
		)
		{
			if ( is_array( $args ) )
			{
				return call_user_func_array( array( static::$_instance, $non_static_method ), $args );
			}
			else
			{
				return static::$_instance->{$non_static_method}( $args );
			}

		}

		// check whether is to call class properties
		elseif ( isset( static::$_properties[ 'public' ] ) AND in_array( $method, static::$_properties[ 'public' ] ) OR
			isset( static::$_properties[ 'static' ] ) AND in_array( $method, static::$_properties[ 'static' ] )
		)
		{
			if ( empty( $args ) )
			{
				return static::$_instance->__get( $method );
			}

			return static::__getProperty( $method, $args );
		}

		throw new \BadMethodCallException( 'Undefined class method: ' . get_class( static::$_instance ) . '::' . $method );
	}
}
