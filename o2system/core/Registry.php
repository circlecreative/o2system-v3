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

use O2System\Glob\ArrayObject;

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
final class Registry extends ArrayObject
{
	protected static $_driver;

	/**
	 * List of Modular Types
	 *
	 * @access  protected
	 * @type    array
	 */
	public static $package_types = array(
		'app'       => 'apps',
		'module'    => 'modules',
		'component' => 'components',
		'plugin'    => 'plugins',
		'widget'    => 'widgets',
		'theme'     => 'themes',
		'language'  => 'languages',
	);

	// ------------------------------------------------------------------------

	/**
	 * Class Constructor
	 *
	 * @access  public
	 *
	 * @param   \O2System\Cache $cache
	 */
	public function __construct()
	{
		\O2System::$language->load( 'registry' );

		$config = \O2System::$config->load( 'registry', TRUE );

		if ( $config[ 'driver' ] === 'database' )
		{
			static::$_driver = new RegistryDatabaseHandler;
		}
		else
		{
			static::$_driver = new RegistryCacheHandler;
		}

		if ( isset( $config[ 'package_types' ] ) AND is_array( $config[ 'package_types' ] ) )
		{
			static::$package_types = array_merge( static::$package_types, $config[ 'package_types' ] );
		}

		if ( $this->__load_registries() === FALSE )
		{
			if ( ! defined( 'CONSOLE_PATH' ) )
			{
				throw new RegistryException( 'REGISTRY_CURRENTLYEMPTY' );
			}
		}
		else
		{
			// Register Modular Namespace
			foreach ( $this->getArrayCopy() as $key => $packages )
			{
				foreach ( $packages as $package )
				{
					if ( isset( $package->namespace ) )
					{
						\O2System::Load()->addNamespace( $package->namespace, ROOTPATH . $package->realpath );
					}

					if ( is_dir( ROOTPATH . $package->realpath . 'languages' ) )
					{
						\O2System::$language->addPath(  ROOTPATH . $package->realpath );
					}
				}
			}
		}

		\O2System::Log( 'info', 'Registry Class Initialized' );
	}

	// ------------------------------------------------------------------------

	private function __load_registries()
	{
		if ( $registries = static::$_driver->loadRegistries() )
		{
			parent::__construct( $registries );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Update
	 *
	 * Perform registry cache update
	 *
	 * @access  public
	 * @return  bool
	 */
	public function fetch()
	{
		if ( $cache = $this->_fetch_properties() )
		{
			echo PHP_EOL . '::: [ INFO ] Create registries cache for: ' . ROOTPATH;
			echo PHP_EOL . '::: [ INFO ] Saving registries cache for: ' . ROOTPATH;
			parent::__construct( $cache );

			return static::$_driver->saveRegistries( $cache );
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

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
		return static::$_driver->destroyRegistries();
	}

	// ------------------------------------------------------------------------

	public function info()
	{
		$cache = $this->getArrayCopy();

		foreach ( $cache as $key => $value )
		{
			$info[ $key ] = count( $value );

			if ( is_cli() )
			{
				echo '::: ' . $key . ': ' . count( $value ) . ' registries' . PHP_EOL;
			}
		}

		if ( ! is_cli() )
		{
			print_dump( $info );
		}
	}

	/**
	 * Fetch properties
	 *
	 * @uses-by Registry::__construct
	 *
	 * @access  protected
	 * @return  bool
	 * @throws  \Exception
	 */
	protected function _fetch_properties()
	{
		$directory = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( ROOTPATH ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$propertiesIterator = new \RegexIterator( $directory, '/^.+\.properties/i', \RecursiveRegexIterator::GET_MATCH );

		foreach ( $propertiesIterator as $files )
		{
			foreach ( $files as $file )
			{
				$paths = array_filter( explode( DIRECTORY_SEPARATOR, str_replace( [ APPSPATH, SYSTEMPATH ], '', $file ) ) );
				array_pop( $paths );
				$properties[ implode( '/', $paths ) ] = $file;
			}
		}

		if ( ! empty( $properties ) )
		{
			// Sort the properties by keys
			ksort( $properties );

			$this->__send_message( 'REGISTRY_INFO_COUNTFETCHINGPROPERTIES', 'INFO', array( count( $properties ) ) );

			foreach ( $properties as $property )
			{
				$path_info = pathinfo( $property );

				$this->__send_message( 'REGISTRY_INFO_DIRECTORYFETCHINGPROPERTIES', 'INFO', array( str_replace( ROOTPATH, '', $property ) ) );

				$metadata = file_get_contents( $property );

				$this->__send_message( 'REGISTRY_INFO_DECODINGPROPERTIES', 'INFO', array( str_replace( ROOTPATH, '', $property ) ) );

				$metadata = json_decode( $metadata, TRUE );

				if ( json_last_error() === JSON_ERROR_NONE )
				{
					// Filter realpath
					if ( $path_info[ 'dirname' ] === substr( APPSPATH, 0, -1 ) )
					{
						$realpath = DIR_APPLICATIONS . DIRECTORY_SEPARATOR;
						$parameter = NULL;
						$segments = NULL;
						$parent_segments = NULL;
						$checksum = md5( $path_info[ 'dirname' ] );
						$code = strtoupper( substr( $checksum, 2, 7 ) );
					}
					else
					{
						$realpath = str_replace( ROOTPATH, '', $path_info[ 'dirname' ] ) . DIRECTORY_SEPARATOR;

						$x_realpath = explode( DIRECTORY_SEPARATOR, str_replace( [ APPSPATH, SYSTEMPATH ], '', $path_info[ 'dirname' ] ) );
						$x_realpath = array_diff( $x_realpath, array_values( static::$package_types ) );

						$parameter = end( $x_realpath );
						$parent_segments = implode( '/', array_slice( $x_realpath, 0, 1 ) );

						if ( $parent_segments === $parameter )
						{
							$parent_segments = NULL;
						}

						$segments = implode( '/', $x_realpath );
						$checksum = md5( $segments );
						$code = strtoupper( substr( $checksum, 2, 7 ) );
					}

					$metadata = array_merge( array(
						                         'type'            => strtoupper( $path_info[ 'filename' ] ),
						                         'realpath'        => $realpath,
						                         'segments'        => $segments,
						                         'parent_segments' => $parent_segments,
						                         'parameter'       => $parameter,
						                         'checksum'        => $checksum,
						                         'code'            => $code,
					                         ), (array) $metadata );

					if ( ! in_array( $path_info[ 'filename' ], array( 'language', 'theme', 'widget' ) ) )
					{
						if ( array_key_exists( 'namespace', $metadata ) === FALSE )
						{
							$this->__send_message( 'REGISTRY_ERROR_FETCHINGUNDEFINEDNAMESPACE', 'ERROR', array( $path_info[ 'filename' ] ) );
						}
					}

					$this->__send_message( 'REGISTRY_SUCCESS_FETCHINGDECODINGPROPERTIES', 'SUCCESS', array( $path_info[ 'filename' ] ) );

					if ( is_file( $setting_filepath = $path_info[ 'dirname' ] . DIRECTORY_SEPARATOR . $path_info[ 'filename' ] . '.settings' ) )
					{
						$this->__send_message( 'REGISTRY_INFO_FETCHINGSETTINGSPROPERTIES', 'INFO', array( str_replace( ROOTPATH, '', $setting_filepath ) ) );

						$settings = file_get_contents( $setting_filepath );

						$this->__send_message( 'REGISTRY_INFO_DECODINGSETTINGSPROPERTIES', 'INFO', array( str_replace( ROOTPATH, '', $setting_filepath ) ) );

						$settings = json_decode( $settings, TRUE );

						if ( json_last_error() === JSON_ERROR_NONE )
						{
							$metadata[ 'settings' ] = new \O2System\Metadata\Setting( $settings );

							$this->__send_message( 'REGISTRY_SUCCESS_DECODINGSETTINGSPROPERTIES', 'SUCCESS', array( str_replace( ROOTPATH, '', $setting_filepath ) ) );
						}
						else
						{
							$this->__send_message( 'REGISTRY_WARNING_DECODINGSETTINGSPROPERTIES', 'WARNING', array( str_replace( ROOTPATH, '', $setting_filepath ) ) );
						}
					}

					if ( $path_info[ 'filename' ] === 'widget' )
					{
						$cache[ 'widgets' ][ $metadata[ 'segments' ] ] = new \O2System\Metadata\Widget( $metadata );
					}
					elseif ( $path_info[ 'filename' ] === 'language' )
					{
						$cache[ 'languages' ][ $metadata[ 'parameter' ] ] = new \O2System\Metadata\Language( $metadata );
					}
					elseif ( $path_info[ 'filename' ] === 'theme' )
					{
						$cache[ 'themes' ][ $metadata[ 'segments' ] ] = new \O2System\Metadata\Theme( $metadata );
					}
					else
					{
						$cache[ 'modules' ][ $metadata[ 'segments' ] ] = new \O2System\Metadata\Module( $metadata );
					}

					$this->__send_message( 'REGISTRY_INFO_REGISTERINGMANIFEST', 'INFO', array( str_replace( ROOTPATH, '', $property ) ) );
				}
				else
				{
					$this->__send_message( 'REGISTRY_ERROR_DECODINGMANIFEST', 'ERROR', array( str_replace( ROOTPATH, '', $property ) ) );
				}
			}

			return $cache;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function get_type( $type )
	{
		if ( $this->offsetExists( 'modules' ) )
		{
			$storage = $this->offsetGet( 'modules' );
			$results = array();

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
	public function find( $index, $offset )
	{
		$index = str_replace( [ DIRECTORY_SEPARATOR, '-' ], [ '/', '_' ], strtolower( $index ) );

		if ( $this->offsetExists( $offset ) )
		{
			$registries = $this->offsetGet( $offset );

			if ( array_key_exists( $index, $registries ) )
			{
				return $registries[ $index ];
			}
		}

		return FALSE;
	}

	/**
	 * Find Parents
	 *
	 * @param \O2System\Glob\Factory\Registry $module
	 *
	 * @return array
	 */
	public function find_parents( \ArrayObject $module )
	{
		$parents = array();

		if ( isset( $module->parent_segments ) )
		{
			$parent_segments = explode( '/', $module->parent_segments );
			$num_parent_segments = count( $parent_segments );

			for ( $i = 0; $i < $num_parent_segments; $i++ )
			{
				$slice_paths = array_slice( $parent_segments, 0, ( $num_parent_segments - $i ) );
				$offset = implode( '/', $slice_paths );

				if ( $parent = $this->find( $offset, 'modules' ) )
				{
					$parents[] = $parent;
				}
			}
		}

		return $parents;
	}

	private function __send_message( $message, $type = 'INFO', $vars = array() )
	{
		$message = \O2System::$language->line( $message );

		if ( ! empty( $vars ) )
		{
			array_unshift( $vars, $message );

			$message = call_user_func_array( 'sprintf', $vars );
		}

		if ( defined( 'CONSOLE_PATH' ) )
		{
			echo ' ::: [ ' . strtoupper( $type ) . ' ] ' . $message . PHP_EOL;
			time_nanosleep( 0, 200000000 );
		}
	}
}

class RegistryCacheHandler
{
	protected $_key;

	public function __construct()
	{
		$this->_key = 'o2system_registry:' . md5( ROOTPATH );
	}

	public function loadRegistries()
	{
		if ( $cache = \O2System::Cache()->get( $this->_key ) )
		{
			return $cache;
		}

		return FALSE;
	}

	public function saveRegistries( array $registries )
	{
		if ( ! empty( $registries ) )
		{
			\O2System::Cache()->save( $this->_key, $registries, FALSE );

			// Since sometime some cache engine returning nothing
			// We must check from __get_cache()
			return (bool) $this->loadRegistries();
		}

		return FALSE;
	}

	public function destroyRegistries()
	{
		return \O2System::Cache()->delete( $this->_key );
	}
}

class RegistryDatabaseHandler
{
	public $db;

	public function __construct()
	{
		$this->db = \O2System::DB();

		if( empty( $this->db ) )
		{
			$this->db = \O2system::Load()->database();
		}
	}

	public function loadRegistries()
	{
		$result = $this->db->get( 'sys_registries' );

		if ( $result->num_rows() > 0 )
		{
			foreach ( $result as $row )
			{
				$metadata = array(
					'name'            => $row->name,
					'version'         => $row->version,
					'type'            => $row->type,
					'realpath'        => $row->realpath,
					'segments'        => $row->segments,
					'parent_segments' => $row->parent_segments,
					'parameter'       => $row->parameter,
					'checksum'        => $row->checksum,
					'code'            => $row->code,
					'namespace'       => $row->namespace,
				);

				if ( ! empty( $row->metadata ) )
				{
					$metadata = array_merge( $metadata, (array) $row->metadata );
				}

				if ( ! empty( $row->settings ) )
				{
					$metadata[ 'settings' ] = (array) $row->settings;
				}

				if ( $row->type === 'WIDGET' )
				{
					$results[ 'widgets' ][ $row->segments ] = new \O2System\Metadata\Widget( $metadata );
				}
				elseif ( $row->type === 'LANGUAGE' )
				{
					$results[ 'languages' ][ $row->parameter ] = new \O2System\Metadata\Language( $metadata );
				}
				elseif ( $row->type === 'THEME' )
				{
					$results[ 'themes' ][ $row->segments ] = new \O2System\Metadata\Theme( $metadata );
				}
				else
				{
					$results[ 'modules' ][ $row->segments ] = new \O2System\Metadata\Module( $metadata );
				}
			}

			return $results;
		}

		return FALSE;
	}

	public function saveRegistries( array $registries )
	{
		if ( ! empty( $registries ) )
		{
			foreach ( $registries as $offset => $registry )
			{
				foreach ( $registry as $key => $value )
				{
					$settings = isset( $value->settings ) ? json_encode( $value->settings ) : NULL;
					$checksum = $value->checksum;
					$code = $value->code;
					$parameter = $value->parameter;
					$name = $value->name;
					$realpath = $value->realpath;
					$namespace = isset( $value->namespace ) ? $value->namespace : NULL;
					$segments = $value->segments;
					$parent_segments = $value->parent_segments;
					$type = $value->type;

					unset( $value->checksum, $value->code, $value->parameter, $value->name, $value->realpath, $value->segments, $value->parent_segments, $value->type );

					if ( isset( $value->namespace ) )
					{
						unset( $value->namespace );
					}

					$result = $this->db->get_where( 'sys_registries', [ 'key' => $key ] );

					if ( $result->num_rows() == 0 )
					{
						$return = $this->db->insert( 'sys_registries', array(
							'key'             => $key,
							'checksum'        => $checksum,
							'code'            => $code,
							'parameter'       => $parameter,
							'name'            => $name,
							'realpath'        => $realpath,
							'namespace'       => $namespace,
							'segments'        => $segments,
							'parent_segments' => $parent_segments,
							'type'            => $type,
							'metadata'        => json_encode( $value ),
							'settings'        => $settings,
						) );
					}
					else
					{
						$return = $this->db->where( 'key', $key )->update( 'sys_registries', array(
							'checksum'        => $checksum,
							'code'            => $code,
							'parameter'       => $parameter,
							'name'            => $name,
							'realpath'        => $realpath,
							'namespace'       => $namespace,
							'segments'        => $segments,
							'parent_segments' => $parent_segments,
							'type'            => $type,
							'metadata'        => json_encode( $value ),
							'settings'        => $settings,
						) );
					}
				}
			}

			return $return;
		}

		return FALSE;
	}

	public function destroyRegistries()
	{
		return TRUE;
	}
}

class RegistryException extends Exception
{

}