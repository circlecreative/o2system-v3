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

namespace O2System\Registry\Metadata;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\SPL\ArrayObject;

class Controller extends ArrayObject
{
	public function __construct( $controller = [ ] )
	{
		$objects = [
			'parameter'        => NULL,
			'class'            => NULL,
			'namespace'        => NULL,
			'directory'        => NULL,
			'reflection'       => NULL,
			'publicMethods'    => [ ],
			'protectedMethods' => [ ],
			'params'           => [ ],
			'method'           => 'index',
		];

		if ( is_file( $controller ) )
		{
			$className          = prepare_class_name( pathinfo( $controller, PATHINFO_FILENAME ) );
			$namespaceDirectory = str_replace( [ 'Controllers', 'controllers' ], '--controller--', dirname( $controller ) );

			$xNamespaceDirectory = explode( '--controller--', $namespaceDirectory );
			$namespaceDirectory  = $xNamespaceDirectory[ 0 ];
			array_shift( $xNamespaceDirectory );
			array_unshift( $xNamespaceDirectory, 'Controllers' );

			$xNamespaceDirectory = array_filter( $xNamespaceDirectory );

			$classNamespace = \O2System::$load->getNamespace( $namespaceDirectory );
			$classNamespace = count( $xNamespaceDirectory ) == 0 ? $classNamespace : ( $classNamespace . prepare_class_name( implode( '\\', $xNamespaceDirectory ) ) . '\\' );

			$controller = [
				'parameter' => strtolower( $className ),
				'class'     => $classNamespace . $className,
				'namespace' => $classNamespace,
				'directory' => realpath( $controller ),
			];
		}

		if ( isset( $controller ) )
		{
			parent::__construct( array_merge( $objects, $controller ) );
		}
		else
		{
			parent::__construct( array_merge( $objects ) );
		}

		if ( class_exists( $this->class ) )
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
				if ( preg_match( '[^_]', $public_method->name ) OR $public_method->name === 'instance' )
				{
					continue;
				}

				$this->publicMethods[ $public_method->name ] = $public_method;

				if ( $public_method->name === 'index' )
				{
					$this->method = 'index';
				}
			}

			foreach ( $this->reflection->getMethods( \ReflectionMethod::IS_PROTECTED ) as $protected_method )
			{
				$this->protectedMethods[ $protected_method->name ] = $protected_method;

				if ( $protected_method->name === '_route' )
				{
					if ( isset( $this->method ) )
					{
						$this->params[ 0 ] = $this->isPublicMethod( $this->method ) ? $this->method : '_' . $this->method;
					}

					$this->method = '_route';
				}
			}
		}
	}

	public function isPublicMethod( $method )
	{
		$method = camelcase( $method );

		if ( array_key_exists( $method, $this->publicMethods ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	public function isProtectedMethod( $method )
	{
		$method = '_' . camelcase( ltrim( $method, '_' ) );

		if ( array_key_exists( $method, $this->protectedMethods ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	public function getCalledClass()
	{
		return get_class_name( $this->class );
	}

	public function getNamespace()
	{
		$namespaces = $this->getNamespaces( TRUE );

		return implode( '\\', $namespaces ) . '\\';
	}

	public function getNamespaces( $filter = FALSE )
	{
		$namespaces = explode( '\\', $this->namespace );

		if ( $filter )
		{
			$namespaces = array_filter(
				$namespaces, function ( $namespace )
			{
				if ( $namespace !== 'Controllers' )
				{
					return trim( $namespace );
				}
			} );
		}
		else
		{
			$namespaces = array_map( 'trim', $namespaces );
		}

		return $namespaces;
	}
}