<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 27-Jul-16
 * Time: 5:52 PM
 */

namespace O2System\Registry\Handlers;


use O2System\Registry\Interfaces\Handler;
use O2System\Registry\Metadata;

class Database extends Handler
{
	/**
	 * Set Registry Database Table
	 *
	 * @access  protected
	 * @var     string
	 */
	protected $key;

	/**
	 * Set Registry Cache Key
	 *
	 * @param $key
	 *
	 * @access  public
	 * @return  Database
	 */
	public function setKey( $key )
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Load Registry From Cache
	 *
	 * @access  public
	 * @return  array|bool
	 */
	public function load()
	{
		$result = \O2System::$db->get( $this->key );

		if ( $result->count() > 0 )
		{
			foreach ( $result as $row )
			{
				$metadata = [
					'name'            => $row->name,
					'version'         => $row->version,
					'type'            => $row->type,
					'realpath'        => str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $row->realpath ),
					'segments'        => $row->segments,
					'parent_segments' => $row->parent_segments,
					'parameter'       => $row->parameter,
					'checksum'        => $row->checksum,
					'code'            => $row->code,
					'namespace'       => $row->namespace,
				];

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
					$results[ 'widgets' ][ $row->segments ] = new Metadata\Widget( $metadata );
				}
				elseif ( $row->type === 'LANGUAGE' )
				{
					$results[ 'languages' ][ $row->parameter ] = new Metadata\Language( $metadata );
				}
				elseif ( $row->type === 'THEME' )
				{
					$results[ 'themes' ][ $row->segments ] = new Metadata\Theme( $metadata );
				}
				else
				{
					$results[ 'modules' ][ $row->segments ] = new Metadata\Module( $metadata );
				}
			}

			return $results;
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
			foreach ( $registries as $offset => $registry )
			{
				foreach ( $registry as $key => $value )
				{
					$settings        = isset( $value->settings ) ? $value->settings : NULL;
					$checksum        = $value->checksum;
					$code            = $value->code;
					$parameter       = $value->parameter;
					$name            = $value->name;
					$realpath        = $value->realpath;
					$namespace       = isset( $value->namespace ) ? $value->namespace : NULL;
					$segments        = $value->segments;
					$parent_segments = $value->parent_segments;
					$type            = $value->type;

					unset( $value->checksum, $value->code, $value->parameter, $value->name, $value->realpath, $value->segments, $value->parent_segments, $value->type );

					if ( isset( $value->namespace ) )
					{
						unset( $value->namespace );
					}

					$result = \O2System::$db->getWhere( $this->key, [ 'key' => $key ] );

					if ( $result->count() == 0 )
					{
						\O2System::$db->insert(
							$this->key, [
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
							'metadata'        => $value,
							'settings'        => $settings,
						] );
					}
					else
					{
						\O2System::$db->where( 'key', $key )->update(
							$this->key, [
							'checksum'        => $checksum,
							'code'            => $code,
							'parameter'       => $parameter,
							'name'            => $name,
							'realpath'        => $realpath,
							'namespace'       => $namespace,
							'segments'        => $segments,
							'parent_segments' => $parent_segments,
							'type'            => $type,
							'metadata'        => $value,
							'settings'        => $settings,
						] );
					}
				}
			}

			return TRUE;
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
		return (bool) \O2System::$db->truncate( $this->key );
	}
}