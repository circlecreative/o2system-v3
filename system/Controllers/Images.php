<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 13/08/2016
 * Time: 19:22
 */

namespace O2System\Controllers;


use O2System\Core\Controller;
use O2System\File\Factory\Image;

class Images extends Controller
{
	protected $imageDefault = 'image.jpg';

	protected function _route( $subDirectory, array $args = [ ] )
	{
		$directory = 'images' . DIRECTORY_SEPARATOR . $subDirectory . DIRECTORY_SEPARATOR;
		$directory = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $directory );

		// Set Image Filename
		$filename = end( $args );
		array_pop( $args );

		// Set Image Size
		list( $width, $height ) = explode( 'x', end( $args ) );
		array_pop( $args );

		$directory = $directory . ( empty( $args ) ? '' : implode( DIRECTORY_SEPARATOR, $args ) . DIRECTORY_SEPARATOR );

		$source = \O2System::$config[ 'upload' ][ 'path' ] . $directory . $filename;

		print_out( $source );

		if ( ! is_file( $source ) )
		{
			$source = \O2System::$config[ 'upload' ][ 'path' ] . 'images' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $this->imageDefault;
		}

		$image = new Image(
			[
				'source'         => $source,
				'size'           => [
					'width'  => $width,
					'height' => $height,
				],
				'maintain_ratio' => TRUE,
			] );

		$image->show( TRUE );
	}
}