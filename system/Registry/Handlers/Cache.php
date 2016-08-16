<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 27-Jul-16
 * Time: 5:52 PM
 */

namespace O2System\Registry\Handlers;


use O2System\Core\Console\Command;
use O2System\Core\SPL\ArrayAccess;
use O2System\Core\SPL\ArrayObject;
use O2System\Registry\Metadata;

class Cache
{
	/**
	 * Registry Cache Key
	 *
	 * @access  protected
	 * @var     string
	 */
	protected $key;

	/**
	 * Registry Cache Lifetime
	 *
	 * @access  protected
	 * @var     bool|int
	 */
	protected $lifetime = FALSE;

	/**
	 * List of Package Types
	 *
	 * @access  protected
	 * @type    array
	 */
	public $packageTypes = [
		'app'       => 'apps',
		'module'    => 'modules',
		'component' => 'components',
		'plugin'    => 'plugins',
		'widget'    => 'widgets',
		'language'  => 'languages',
	];

	/**
	 * O2System Console Command
	 *
	 * @var Command
	 */
	protected $command;

	// ------------------------------------------------------------------------

	/**
	 * Set Registry Cache Key
	 *
	 * @param $key
	 *
	 * @access  public
	 * @return  Cache
	 */
	public function setKey( $key )
	{
		$this->key = $key . ':' . md5( ROOTPATH );

		return $this;
	}

	public function setPackageTypes( ArrayObject $packageTypes )
	{
		$this->packageTypes = array_merge( $this->packageTypes, $packageTypes->getArrayCopy() );
	}

	public function setLifetime( $lifetime )
	{
		$this->lifetime = $lifetime;
	}

	public function setCommand( Command $command )
	{
		$this->command = $command;
	}

	/**
	 * Update
	 *
	 * Perform registry cache update
	 *
	 * @access  public
	 * @return  bool
	 */
	public function update()
	{
		if ( $cache = $this->fetchProperties() )
		{
			$this->stopDebugLine( 'REGISTRY_FETCH_SAVING_REGISTRIES_CACHE', [ ], 'FINISHED' );

			\O2System::$registry->exchangeArray( $cache );

			return $this->save( $cache );
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Load Registry From Cache
	 *
	 * @access  public
	 * @return mixed
	 */
	public function load()
	{
		if ( $cache = \O2System::$cache->registry->get( $this->key ) )
		{
			return $cache;
		}
		else
		{
			$registries = $this->fetchProperties();

			if ( $this->save( $registries ) )
			{
				return $this->load();
			}
		}

		return FALSE;
	}

	/**
	 * Save Registry to Cache
	 *
	 * @param   array $registries
	 *
	 * @access  public
	 * @return  bool
	 */
	public function save( array $registries )
	{
		if ( ! empty( $registries ) )
		{
			\O2System::$cache->registry->save( $this->key, $registries, $this->lifetime );

			return (bool) \O2System::$cache->registry->isExists( $this->key );
		}

		return FALSE;
	}

	/**
	 * Destroy Registry Cache
	 *
	 * @access  public
	 * @return  bool
	 */
	public function destroy()
	{
		return (bool) \O2System::$cache->registry->delete( $this->key );
	}

	/**
	 * Fetch properties
	 *
	 * @return  array
	 */
	protected function fetchProperties()
	{
		$directory = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( ROOTPATH ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$propertiesIterator = new \RegexIterator( $directory, '/^.+\.properties$/i', \RecursiveRegexIterator::GET_MATCH );

		foreach ( $propertiesIterator as $files )
		{
			foreach ( $files as $file )
			{
				$paths = array_filter( explode( DIRECTORY_SEPARATOR, str_replace( [ APPSPATH, SYSTEMPATH ], '', $file ) ) );
				array_pop( $paths );
				$properties[ implode( '/', $paths ) ] = $file;
			}
		}

		$registries = [ ];

		if ( ! empty( $properties ) )
		{
			// Sort the properties by keys
			ksort( $properties );

			$this->startDebugLine( 'REGISTRY_FETCH_REGISTRIES_COUNT_PROPERTIES', [ count( $properties ) ], 'FETCHING' );

			foreach ( $properties as $property )
			{
				$path_info = pathinfo( $property );

				$metadata = file_get_contents( $property );

				$this->startDebugLine( 'REGISTRY_FETCH_REGISTRIES_DECODING_PROPERTIES', [ str_replace( ROOTPATH, '', $property ) ], 'DECODE' );

				$metadata = json_decode( $metadata, TRUE );

				if ( json_last_error() === JSON_ERROR_NONE )
				{
					// Filter realpath
					if ( $path_info[ 'dirname' ] === substr( APPSPATH, 0, -1 ) )
					{
						$path            = NULL;
						$parameter       = NULL;
						$segments        = NULL;
						$parent_segments = NULL;
						$checksum        = md5( $path_info[ 'dirname' ] );
						$code            = strtoupper( substr( $checksum, 2, 7 ) );
					}
					else
					{
						$path = str_replace( [ ROOTPATH, DIR_SYSTEM, DIR_APPLICATIONS ], '', $path_info[ 'dirname' ] );
						$path = trim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

						$x_path = explode( DIRECTORY_SEPARATOR, strtolower( $path ) );
						$x_path = array_diff( $x_path, $this->packageTypes );
						$x_path = array_filter( $x_path );

						$parameter = end( $x_path );

						$parent_segments = implode( '/', array_slice( $x_path, 0, 1 ) );

						if ( $parent_segments === $parameter )
						{
							$parent_segments = NULL;
						}

						$segments = implode( '/', $x_path );
						$checksum = md5( $segments );
						$code     = strtoupper( substr( $checksum, 2, 7 ) );
					}

					$metadata = array_merge(
						[
							'type'            => strtoupper( $path_info[ 'filename' ] ),
							'path'            => $path,
							'segments'        => strtolower( $segments ),
							'parent_segments' => strtolower( $parent_segments ),
							'parameter'       => strtolower( $parameter ),
							'checksum'        => $checksum,
							'code'            => $code,
						], (array) $metadata );

					if ( ! in_array( $path_info[ 'filename' ], [ 'language', 'theme', 'widget' ] ) )
					{
						if ( array_key_exists( 'namespace', $metadata ) === FALSE )
						{
							$metadata[ 'namespace' ] = 'Applications\\' . prepare_namespace( $segments );
							$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_DECODING_PROPERTIES_NAMESPACE_ERROR', [ $path_info[ 'filename' ] ], 'DECODE', TRUE );
						}
						else
						{
							$metadata[ 'namespace' ] = rtrim( $metadata[ 'namespace' ], '\\' ) . '\\';
						}
					}

					//$this->__writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_DECODING_PROPERTIES_SUCCESS', [ $path_info[ 'filename' ] ] );

					if ( is_file( $setting_filepath = $path_info[ 'dirname' ] . DIRECTORY_SEPARATOR . $path_info[ 'filename' ] . '.settings' ) )
					{
						$settings = file_get_contents( $setting_filepath );

						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_DECODING_PROPERTIES_SETTINGS', [ str_replace( ROOTPATH, '', $setting_filepath ) ], 'DECODE_SETTINGS' );

						$settings = json_decode( $settings, TRUE );

						if ( json_last_error() === JSON_ERROR_NONE )
						{
							$metadata[ 'settings' ] = new Metadata\Setting( $settings );

							$this->stopDebugLine( 'REGISTRY_FETCH_REGISTRIES_DECODING_PROPERTIES_SETTINGS_SUCCESS', [ str_replace( ROOTPATH, '', $setting_filepath ) ], 'DECODE_SETTINGS' );
						}
						else
						{
							$this->stopDebugLine( 'REGISTRY_FETCH_REGISTRIES_DECODING_PROPERTIES_SETTINGS_ERROR', [ str_replace( ROOTPATH, '', $setting_filepath ), 'DECODE_SETTINGS', FALSE ] );
						}
					}

					if ( $path_info[ 'filename' ] === 'widget' )
					{
						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_BUILD_PROPERTIES_OBJECT', [ 'widgets:' . $metadata[ 'segments' ] ], 'BUILD' );

						$registries[ 'widgets' ][ $metadata[ 'segments' ] ] = new Metadata\Widget( $metadata );

						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_REGISTER_PROPERTIES_OBJECT', [ 'widgets:' . $metadata[ 'segments' ] ], 'REGISTER' );
					}
					elseif ( $path_info[ 'filename' ] === 'language' )
					{
						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_BUILD_PROPERTIES_OBJECT', [ 'languages:' . $metadata[ 'parameter' ] ], 'BUILD' );

						$registries[ 'languages' ][ $metadata[ 'parameter' ] ] = new Metadata\Language( $metadata );

						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_REGISTER_PROPERTIES_OBJECT', [ 'languages:' . $metadata[ 'parameter' ] ], 'REGISTER' );
					}
					elseif ( $path_info[ 'filename' ] === 'theme' )
					{
						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_BUILD_PROPERTIES_OBJECT', [ 'themes:' . $metadata[ 'segments' ] ], 'BUILD' );

						$registries[ 'themes' ][ $metadata[ 'segments' ] ] = new Metadata\Theme( $metadata );

						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_REGISTER_PROPERTIES_OBJECT', [ 'themes:' . $metadata[ 'segments' ] ], 'REGISTER' );
					}
					else
					{
						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_BUILD_PROPERTIES_OBJECT', [ 'modules:' . $metadata[ 'segments' ] ], 'BUILD' );

						$registries[ 'modules' ][ $metadata[ 'segments' ] ] = new Metadata\Module( $metadata );

						$this->writeDebugLine( 'REGISTRY_FETCH_REGISTRIES_REGISTER_PROPERTIES_OBJECT', [ 'modules:' . $metadata[ 'segments' ] ], 'REGISTER' );
					}

					$this->stopDebugLine( 'REGISTRY_FETCH_REGISTRIES_DECODING_PROPERTIES_SUCCESS', [ str_replace( ROOTPATH, '', $property ) ], 'DECODE' );
				}
				else
				{
					$this->stopDebugLine( 'REGISTRY_FETCH_REGISTRIES_DECODING_PROPERTIES_ERROR', [ str_replace( ROOTPATH, '', $property ) ], 'FETCHING', TRUE );
				}
			}
		}

		return $registries;
	}

	// ------------------------------------------------------------------------

	public function startDebugLine( $message, $vars = [ ], $prefix = 'START' )
	{
		if ( isset( $this->command ) )
		{
			$this->command->startDebugLine( $this, $message, $vars, $prefix );
		}
	}

	public function writeDebugLine( $message, $vars = [ ], $prefix = 'PROCESS', $error = FALSE )
	{
		if ( isset( $this->command ) )
		{
			$this->command->writeDebugLine( $this, $message, $vars, $prefix, $error );
		}
	}

	public function stopDebugLine( $message, $vars = [ ], $prefix = 'STOP', $error = TRUE )
	{
		if ( isset( $this->command ) )
		{
			$this->command->stopDebugLine( $this, $message, $vars, $prefix, $error );
		}
	}
}