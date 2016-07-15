<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/20/2016
 * Time: 2:03 AM
 */

namespace O2System\Models\System;


class Registry
{
	public function insert()
	{

		$settings        = isset( $value->settings ) ? json_encode( $value->settings ) : NULL;
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

		$result = $this->db->select( 'id' )->getWhere( 'sys_registries', [ 'key' => $parent_segments ] );

		$id_parent = 0;
		if ( $result->numRows() > 0 )
		{
			$id_parent = $result->first()->id;
		}

		$registry = [
			'id_parent'       => $id_parent,
			'key'             => $key,
			'checksum'        => $checksum,
			'code'            => $code,
			'parameter'       => $parameter,
			'name'            => $name,
			'realpath'        => $realpath,
			'namespace'       => $namespace,
			'segments'        => $segments,
			'parent_segments' => $parent_segments,
			'metadata'        => json_encode( $value ),
			'type'            => $type,
			'settings'        => $settings,
		];

		$registries[] = $registry;

		$this->db->insert( 'sys_registries', $registry );
	}
}