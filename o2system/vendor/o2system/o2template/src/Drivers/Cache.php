<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/13/2016
 * Time: 7:21 AM
 */

namespace O2System\Template\Drivers;


use O2System\Glob\ArrayObject;
use O2System\Glob\Interfaces\DriverInterface;

class Cache extends DriverInterface
{
	protected $_handler;

	public function get( $id )
	{
		if ( is_file( $filename = $this->_config[ 'path' ] . $id ) )
		{
			return file_get_contents( $filename );
		}

		return FALSE;
	}

	public function save( $id )
	{

	}

	public function info( $id )
	{
		if ( is_file( $filename = $this->_config[ 'path' ] . $id ) )
		{
			if ( $this->_config[ 'lifetime' ] > 0 && time() > filemtime( $filename ) + $this->_config[ 'lifetime' ] )
			{
				unlink( $filename );

				return FALSE;
			}

			$info = new ArrayObject();
			$info->size = filesize( $filename );
			$info->realpath = $filename;
			$info->url = path_to_url( $filename );
			$info->is_expired = FALSE;

			return $info;
		}

		return FALSE;
	}
}