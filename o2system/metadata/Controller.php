<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
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
 * @author         Circle Creative Developer Team
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Metadata;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\ArrayObject;

class Controller extends ArrayObject
{
	public function __construct( $controller = array() )
	{
		$std_objects = array(
			'parameter'         => NULL,
			'class'             => NULL,
			'namespace'         => NULL,
			'realpath'          => NULL,
			'reflection'        => NULL,
			'public_methods'    => array(),
			'protected_methods' => array(),
			'params'            => array(),
			'method'            => 'index',
		);

		if ( is_string( $controller ) AND file_exists( $controller ) )
		{
			$path_info = pathinfo( $controller );
			$class_name = prepare_class_name( $path_info[ 'filename' ] );

			$class_namespace = \O2System::Load()->getNamespace( $path_info[ 'dirname' ] );
			$directory = \O2System::Load()->getNamespacePath( $class_namespace, $path_info[ 'dirname' ] );

			$x_class_namespace = explode( '\\', $class_namespace );
			$x_class_namespace = array_filter( $x_class_namespace );

			if ( end( $x_class_namespace ) === $class_name )
			{
				array_pop( $x_class_namespace );
				$class_namespace = implode( '\\', $x_class_namespace ) . '\\';
			}

			$controller = array(
				'parameter' => strtolower( $path_info[ 'filename' ] ),
				'class'     => $class_namespace . $class_name,
				'namespace' => $class_namespace,
				'realpath'  => $directory . $path_info[ 'filename' ] . '.php',
			);
		}

		parent::__construct( array_merge( $std_objects, $controller ) );

		if ( $this->offsetExists( 'realpath' ) )
		{
			$this->__fetchMethods();
		}
	}

	private function __fetchMethods()
	{
		if ( class_exists( $this->class ) )
		{
			$this->reflection = new \ReflectionClass( $this->class );

			foreach ( $this->reflection->getMethods( \ReflectionMethod::IS_PUBLIC ) as $public_method )
			{
				if ( preg_match( '[^_]', $public_method->name ) OR $public_method->name === 'instance' ) continue;

				$this->public_methods[ $public_method->name ] = $public_method;

				if ( $public_method->name === 'index' )
				{
					$this->method = 'index';
				}
			}

			foreach ( $this->reflection->getMethods( \ReflectionMethod::IS_PROTECTED ) as $protected_method )
			{
				$this->protected_methods[ $protected_method->name ] = $protected_method;

				if ( $protected_method->name === '_route' )
				{
					if ( isset( $this->method ) )
					{
						$this->params[] = $this->method;
					}

					$this->method = '_route';
				}
			}
		}
	}

	public function isPublicMethod( $method )
	{
		if ( array_key_exists( $method, $this->public_methods ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	public function isProtectedMethod( $method )
	{
		$method = '_' . ltrim( $method, '_' );

		if ( array_key_exists( $method, $this->protected_methods ) )
		{
			return TRUE;
		}

		return FALSE;
	}
}