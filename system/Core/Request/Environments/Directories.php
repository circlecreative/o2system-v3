<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 27-Jul-16
 * Time: 8:35 PM
 */

namespace O2System\Core\Request\Environments;

use O2System\Core\SPL\ArrayIterator;

class Directories extends ArrayIterator
{
	public function offsetSet( $index, $value )
	{
		parent::offsetSet( $index, str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $value ) );
	}

	public function getCurrent( $subDirectory = NULL )
	{
		return $this->current() . ( isset( $subDirectory ) ? prepare_class_name( $subDirectory ) . DIRECTORY_SEPARATOR : '' );
	}

	public function getSubDirectories( $subDirectory, $reverse = FALSE )
	{
		$directories = $this->getArrayCopy();

		$subDirectory = prepare_class_name( $subDirectory );

		foreach ( $directories as $key => $directory )
		{
			$directories[ $key ] = $directory . str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $subDirectory ) . DIRECTORY_SEPARATOR;
		}

		return $reverse === TRUE ? array_reverse( $directories ) : $directories;
	}
}