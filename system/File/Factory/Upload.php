<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 11/5/2015
 * Time: 12:44 PM
 */

namespace O2System\File\Factory;

use Upload\Storage\FileSystem;
use Upload\Validation\Extension;
use Upload\Validation\Mimetype;
use Upload\Validation\Size;

/**
 * File Uploading Class
 *
 * @package        O2System
 * @subpackage     Libraries
 * @category       Uploads
 * @author         Circle Creative Dev Team
 * @link           http://codeigniter.com/wiki/#Upload
 */
class Upload
{
	protected $_config = [
		'max_increment_filename' => 100,
	];
	protected $_info   = [ ];
	protected $_errors = [ ];

	// ------------------------------------------------------------------------

	public function __construct( $config = [ ] )
	{
		if ( ! class_exists( 'finfo' ) )
		{
			throw new \Exception( 'Upload: The fileinfo extension must be loaded.' );
		}

		$default_config = \O2System::$config[ 'upload' ];


		if ( isset( $default_config ) )
		{
			if ( isset( $default_config[ 'path' ] ) AND isset( $config[ 'path' ] ) )
			{
				$config[ 'path' ] = str_replace( '/', DIRECTORY_SEPARATOR, $config[ 'path' ] );

				if ( is_dir( $config[ 'path' ] ) )
				{
					$this->_config[ 'path' ] = trim( $config[ 'path' ], DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
				}
				elseif ( is_dir( $default_config[ 'path' ] . trim( $config[ 'path' ], DIRECTORY_SEPARATOR ) ) )
				{
					$this->_config[ 'path' ] = $default_config[ 'path' ] . trim( $config[ 'path' ], DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
				}
				else
				{
					$this->_config[ 'path' ] = rtrim( $default_config[ 'path' ], DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
				}

				unset( $default_config[ 'path' ], $config[ 'path' ] );
			}

			$this->_config = array_merge_recursive( $this->_config, $config, $default_config );
		}
		else
		{
			$this->_config = array_merge_recursive( $this->_config, $config );
		}

		if ( isset( $this->_config[ 'allowed_mimes' ] ) )
		{
			$this->setAllowedMimes( $this->_config[ 'allowed_mimes' ] );
		}

		if ( isset( $this->_config[ 'allowed_extensions' ] ) )
		{
			$this->setAllowedExtensions( $this->_config[ 'allowed_extensions' ] );
		}
	}

	// ------------------------------------------------------------------------

	public function setPath( $path )
	{
		$this->_config[ 'path' ] = $path . DIRECTORY_SEPARATOR;
	}

	// ------------------------------------------------------------------------

	public function setAllowedMimes( $mimes )
	{
		if ( is_string( $mimes ) )
		{
			$mimes                            = explode( ',', $mimes );
			$this->_config[ 'allowed_mimes' ] = array_map( 'trim', $mimes );
		}
	}

	// ------------------------------------------------------------------------

	public function setAllowedExtensions( $extensions )
	{
		if ( is_string( $extensions ) )
		{
			$extensions                            = explode( ',', $extensions );
			$this->_config[ 'allowed_extensions' ] = array_map( 'trim', $extensions );
		}
	}

	// ------------------------------------------------------------------------

	public function setMinSize( $size, $unit = 'M' )
	{
		$this->_config[ 'min_size' ] = (int) $size . $unit;
	}

	// ------------------------------------------------------------------------

	public function setMaxSize( $size, $unit = 'M' )
	{
		$this->_config[ 'max_size' ] = (int) $size . $unit;
	}

	// ------------------------------------------------------------------------

	public function setMinWidth( $width )
	{
		$this->_config[ 'min_width' ] = (int) $width;
	}

	// ------------------------------------------------------------------------

	public function setMinHeight( $height )
	{
		$this->_config[ 'min_height' ] = (int) $height;
	}

	// ------------------------------------------------------------------------

	public function setMaxWidth( $width )
	{
		$this->_config[ 'max_width' ] = (int) $width;
	}

	// ------------------------------------------------------------------------

	public function setMaxHeight( $height )
	{
		$this->_config[ 'max_height' ] = (int) $height;
	}

	// ------------------------------------------------------------------------

	public function setFilename( $filename, $delimiter = '-' )
	{
		$filename = strtolower( trim( $filename ) );
		$filename = preg_replace( '/[^\w-]/', '', $filename );;

		if ( $delimiter === '-' )
		{
			$filename = preg_replace( '/[ _]+/', '-', $filename );
		}
		elseif ( $delimiter === '_' )
		{
			$filename = preg_replace( '/[ -]+/', '_', $filename );
		}

		$this->_config[ 'filename' ] = $filename;
	}

	// ------------------------------------------------------------------------

	public function setMaxIncrementFilename( $increment )
	{
		$this->_config[ 'max_increment_filename' ] = (int) $increment;
	}

	// ------------------------------------------------------------------------

	public function doUpload( $field )
	{
		if ( isset( $_FILES[ $field ] ) )
		{
			$files = $_FILES;

			if ( is_array( $files[ $field ][ 'name' ] ) )
			{
				foreach ( $files[ $field ][ 'name' ] as $key => $value )
				{
					if ( $value === '' )
					{
						continue;
					}

					$_FILES[ md5( $field . $key ) ] = [
						'name'     => $value,
						'type'     => $files[ $field ][ 'type' ][ $key ],
						'tmp_name' => $files[ $field ][ 'tmp_name' ][ $key ],
						'error'    => $files[ $field ][ 'error' ][ $key ],
						'size'     => $files[ $field ][ 'size' ][ $key ],
					];

					if ( $this->_uploadFile( md5( $field . $key ) ) )
					{
						$info[ $value ] = $this->getInfo();
					}
					else
					{
						$errors[ $value ] = $this->getErrors();
					}
				}

				if ( isset( $info ) AND empty( $errors ) )
				{
					$this->_info = $info;

					return TRUE;
				}
				elseif ( isset( $errors ) )
				{
					if ( ! empty( $info ) )
					{
						$this->_info = $info;
					}

					$this->_errors = $errors;

					return FALSE;
				}
			}
			else
			{
				return $this->_uploadFile( $field );
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	protected function _uploadFile( $field )
	{
		if ( isset( $this->_config[ 'path' ] ) )
		{
			if ( ! is_dir( $this->_config[ 'path' ] ) )
			{
				mkdir( $this->_config[ 'path' ], 0777, TRUE );
			}

			$storage = new FileSystem( rtrim( $this->_config[ 'path' ], DIRECTORY_SEPARATOR ) );
		}
		else
		{
			throw new \InvalidArgumentException( 'Upload: Undefined config upload path' );
		}

		$file = new \Upload\File( $field, $storage );

		// Set Filename
		if ( isset( $this->_config[ 'unique_filename' ] ) )
		{
			$this->_config[ 'filename' ] = uniqid();
		}

		if ( isset( $this->_config[ 'filename' ] ) )
		{
			$filename = strtolower( trim( $this->_config[ 'filename' ] ) );
		}
		else
		{
			$filename = strtolower( trim( $file->getName() ) );
		}

		$filename = preg_replace( '/[^\w-]/', '', $filename );
		$filename = preg_replace( '/[ _]+/', '-', $filename );

		if ( is_file( $this->_config[ 'path' ] . $filename . '.' . $file->getExtension() ) )
		{
			if ( isset( $this->_config[ 'overwrite' ] ) AND $this->_config[ 'overwrite' ] === TRUE )
			{
				unlink( $this->_config[ 'path' ] . $filename . '.' . $file->getExtension() );
				$file->setName( $filename );
			}
			else
			{
				for ( $i = 1; $i < $this->_config[ 'max_increment_filename' ]; $i++ )
				{
					if ( ! is_file( $this->_config[ 'path' ] . $filename . '-' . $i . '.' . $file->getExtension() ) )
					{
						$filename = $filename . '-' . $i;
						$file->setName( $filename );
						break;
					}
				}
			}
		}
		else
		{
			$file->setName( $filename );
		}

		// Validation Mime Types
		if ( isset( $this->_config[ 'allowed_mimes' ] ) )
		{
			$validations[] = new Mimetype( $this->_config[ 'allowed_mimes' ] );
		}

		// Validation Extensions
		if ( isset( $this->_config[ 'allowed_extensions' ] ) )
		{
			$validations[] = new Extension( $this->_config[ 'allowed_extensions' ] );
		}

		// Validation Size
		if ( isset( $this->_config[ 'max_size' ] ) AND isset( $this->_config[ 'min_size' ] ) )
		{
			$validations[] = new Size( $this->_config[ 'max_size' ], $this->_config[ 'min_size' ] );
		}
		elseif ( isset( $this->_config[ 'max_size' ] ) )
		{
			$validations[] = new Size( $this->_config[ 'max_size' ] );
		}

		if ( ! empty( $validations ) )
		{
			$file->addValidations( $validations );
		}

		$path = rtrim( $this->_config[ 'path' ], DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		$this->_info = new \ArrayObject(
			[
				'path'      => $path,
				'filename'  => $file->getNameWithExtension(),
				'extension' => $file->getExtension(),
				'mime'      => $file->getMimetype(),
				'size'      => $file->getSize(),
				'url'       => $this->_pathToUrl( $path . $filename . '.' . $file->getExtension() ),
			], \ArrayObject::ARRAY_AS_PROPS );

		if ( $dimension = getimagesize( $file->getRealPath() ) )
		{
			$this->_info[ 'dimension' ] = new \ArrayObject(
				[
					'width'     => $dimension[ 0 ],
					'height'    => $dimension[ 1 ],
					'attribute' => $dimension[ 3 ],
				], \ArrayObject::ARRAY_AS_PROPS );
		}

		if ( isset( $this->_config[ 'min_width' ] ) )
		{
			if ( isset( $dimension ) )
			{
				if ( $dimension[ 0 ] < $this->_config[ 'min_width' ] )
				{
					$this->_errors[] = 'Image width must larger than ' . $this->_config[ 'min_width' ];

					return FALSE;
				}
			}
		}

		if ( isset( $this->_config[ 'min_height' ] ) )
		{
			if ( isset( $dimension ) )
			{
				if ( $dimension[ 1 ] < $this->_config[ 'min_height' ] )
				{
					$this->_errors[] = 'Image height must larger than ' . $this->_config[ 'min_height' ];

					return FALSE;
				}
			}
		}

		if ( isset( $this->_config[ 'max_width' ] ) )
		{
			if ( isset( $dimension ) )
			{
				if ( $dimension[ 1 ] > $this->_config[ 'max_width' ] )
				{
					$this->_errors[] = 'Image width must less than ' . $this->_config[ 'max_width' ];

					return FALSE;
				}
			}
		}

		if ( isset( $this->_config[ 'max_height' ] ) )
		{
			if ( isset( $dimension ) )
			{
				if ( $dimension[ 1 ] > $this->_config[ 'max_height' ] )
				{
					$this->_errors[] = 'Image height must less than ' . $this->_config[ 'max_height' ];

					return FALSE;
				}
			}
		}

		// Try to upload file
		try
		{
			$file->upload();

			return TRUE;
		}
		catch ( \Exception $e )
		{
			$this->_errors = $file->getErrors();
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function getInfo()
	{
		return $this->_info;
	}

	// ------------------------------------------------------------------------

	public function getErrors()
	{
		return $this->_errors;
	}

	// ------------------------------------------------------------------------

	protected function _pathToUrl( $path )
	{
		$base_url = isset( $_SERVER[ 'REQUEST_SCHEME' ] ) ? $_SERVER[ 'REQUEST_SCHEME' ] : 'http';
		$base_url .= '://' . $_SERVER[ 'SERVER_NAME' ];

		// Add server port if needed
		$base_url .= $_SERVER[ 'SERVER_PORT' ] !== '80' ? ':' . $_SERVER[ 'SERVER_PORT' ] : '';

		// Add base path
		$base_url .= dirname( $_SERVER[ 'SCRIPT_NAME' ] );
		$base_url = str_replace( DIRECTORY_SEPARATOR, '/', $base_url );
		$base_url = trim( $base_url, '/' ) . '/';

		// Vendor directory
		$base_dir = explode( 'vendor' . DIRECTORY_SEPARATOR . 'o2system', __DIR__ );
		$base_dir = str_replace( [ 'o2system', '/' ], [ '', DIRECTORY_SEPARATOR ], $base_dir[ 0 ] );
		$base_dir = trim( $base_dir, DIRECTORY_SEPARATOR );

		$path = str_replace( [ $base_dir, DIRECTORY_SEPARATOR ], [ '', '/' ], $path );
		$path = trim( $path, '/' );

		return trim( $base_url . $path, '/' );
	}
}