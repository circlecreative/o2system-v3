<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 5/9/2016
 * Time: 11:44 AM
 */

namespace O2System\Metadata;


use O2System\Glob\ArrayObject;

class Page extends ArrayObject
{
	public function __construct( $page )
	{
		$pathinfo = pathinfo( $page );

		parent::__construct( array(
			                     'realpath'  => $page,
			                     'directory' => $directory = $pathinfo[ 'dirname' ] . DIRECTORY_SEPARATOR,
			                     'filename'  => $filename = $pathinfo[ 'filename' ],
			                     'basename'  => $pathinfo[ 'basename' ],
			                     'extension' => $pathinfo[ 'extension' ],
			                     'vars'      => $this->__fetchVars( $directory, $filename ),
		                     ) );
	}

	private function __fetchVars( $directory, $filename )
	{
		if ( is_file( $filepath = $directory . 'pages.properties' ) )
		{
			$properties = file_get_contents( $filepath );
			$properties = json_decode( $properties, TRUE );

			if ( isset( $properties[ $filename ] ) )
			{
				return $properties[ $filename ];
			}
		}

		return array();
	}
}

