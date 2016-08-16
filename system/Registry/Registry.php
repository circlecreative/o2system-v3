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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\SPL\ArrayObject;

/**
 * Registry Class
 *
 * O2System core registry class
 *
 * @package     O2System
 * @subpackage  core
 * @category    Core Class
 * @author      Circle Creative Dev Team
 * @link        http://circle-creative.com/products/o2system-codeigniter/user-guide/core/registry.html
 *
 * @final       This class can't be overwritten
 */
final class Registry extends Core\SPL\ArrayObject
{
	/**
	 * Registry Cache Handler
	 *
	 * @access  protected
	 * @var Registry\Handlers\Cache
	 */
	protected static $cache;

	// ------------------------------------------------------------------------

	/**
	 * Registry constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		\O2System::$language->load( 'registry' );

		$config = \O2System::$config->load( 'registry', TRUE );

		static::$cache = new Registry\Handlers\Cache();
		static::$cache->setKey( $config[ 'key' ] );
		static::$cache->setPackageTypes( $config[ 'package_types' ] );
		static::$cache->setLifetime( $config[ 'lifetime' ] );

		if ( $cache = static::$cache->load() )
		{
			$this->exchangeStorage( $cache );

			// Register Modular Namespace
			foreach ( $this->getArrayCopy() as $key => $packages )
			{
				foreach ( $packages as $package )
				{
					if ( isset( $package->namespace ) )
					{
						\O2System::$load->addNamespace( $package->namespace, $package->getDirectory() );
						\O2System::$load->addAssetsDirectory( $package->namespace, $package->getDirectory() );
						\O2System::$load->addThemesDirectory( $package->namespace, $package->getDirectory() );
					}
				}
			}
		}

		// Integrated to $_SERVER
		$this->offsetSet( 'server', new Registry\Collections\Server() );

		// Integrated to $GLOBALS
		$this->offsetSet( 'globals', new Registry\Collections\Globals() );

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Cache Handler
	 *
	 * @return \O2System\Registry\Handlers\Cache
	 */
	public function getCacheHandler()
	{
		return static::$cache;
	}

	public function getPackageTypes()
	{
		return static::$cache->packageTypes;
	}

	/**
	 * Destroy
	 *
	 * Perform registry cache destroy
	 *
	 * @access  public
	 * @return  bool
	 */
	public function destroy()
	{
		$this->clearStorage();

		return static::$cache->destroy();
	}

	// ------------------------------------------------------------------------

	public function info()
	{
		$cache = $this->getArrayCopy();

		$info = [ ];

		foreach ( $cache as $key => $value )
		{
			$info[ $key ] = count( $value );
		}

		return $info;
	}

	public function getType( $type )
	{
		if ( $this->offsetExists( 'modules' ) )
		{
			$storage = $this->offsetGet( 'modules' );
			$results = [ ];

			foreach ( $storage as $registry )
			{
				if ( $registry->type === strtoupper( $type ) )
				{
					$results[] = $registry;
				}
			}

			return $results;
		}

		return FALSE;
	}

	public function getPackageType( $type )
	{
		$type = strtolower( $type );

		if ( array_key_exists( $type, static::$cache->packageTypes ) )
		{
			return static::$packageTypes[ $type ];
		}

		return NULL;
	}

	/**
	 * Find
	 *
	 * Find index inside offset
	 *
	 * @param   string $index
	 * @param   string $offset
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function find( $index, $offset = 'ALL' )
	{
		if ( $offset === 'ALL' )
		{
			$keys = $this->getArrayKeys();

			foreach ( $keys as $offset )
			{
				if ( $this->offsetExists( $offset ) )
				{
					$registries = $this->offsetGet( $offset );

					if ( isset( $registries[ $index ] ) )
					{
						return $registries[ $index ];
						break;
					}
				}
			}
		}
		elseif ( $this->offsetExists( $offset ) )
		{
			$registries = $this->offsetGet( $offset );

			if ( isset( $registries[ $index ] ) )
			{
				return $registries[ $index ];
			}
		}

		return FALSE;
	}

	/**
	 * Find Parents
	 *
	 * @param ArrayObject $module
	 *
	 * @return array
	 */
	public function getParents( ArrayObject $module )
	{
		$parents = [ ];

		if ( isset( $module->parent_segments ) )
		{
			$parent_segments     = explode( '/', $module->parent_segments );
			$num_parent_segments = count( $parent_segments );

			for ( $i = 0; $i < $num_parent_segments; $i++ )
			{
				$slice_paths = array_slice( $parent_segments, 0, ( $num_parent_segments - $i ) );
				$offset      = implode( '/', $slice_paths );

				if ( $parent = $this->find( $offset, 'modules' ) )
				{
					$parents[] = $parent;
				}
			}
		}

		return $parents;
	}


}