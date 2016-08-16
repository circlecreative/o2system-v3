<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/13/2016
 * Time: 7:21 AM
 */

namespace O2System\Template\Drivers;


use O2System\Core\Library\Driver;
use O2System\Core\SPL\ArrayObject;

class Cache extends Driver
{
	/**
	 * Library Instance
	 *
	 * @access  protected
	 * @type    Template
	 */
	protected $library;

	protected $handler;

	public function get( $key )
	{
		if ( is_file( $filename = $this->config[ 'path' ] . $key ) )
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
		if ( is_file( $filename = $this->config[ 'path' ] . $id ) )
		{
			if ( $this->config[ 'lifetime' ] > 0 && time() > filemtime( $filename ) + $this->config[ 'lifetime' ] )
			{
				unlink( $filename );

				return FALSE;
			}

			$info             = new ArrayObject();
			$info->size       = filesize( $filename );
			$info->realpath   = $filename;
			$info->url        = path_to_url( $filename );
			$info->is_expired = FALSE;

			return $info;
		}

		return FALSE;
	}
}