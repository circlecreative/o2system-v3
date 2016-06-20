<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/15/2015
 * Time: 12:05 PM
 */

namespace O2System
{

	use O2System\Image\Exception;

	class Image
	{
		const QUADRANT_TOP    = 'T';
		const QUADRANT_BOTTOM = 'B';
		const QUADRANT_CENTER = 'C';
		const QUADRANT_LEFT   = 'L';
		const QUADRANT_RIGHT  = 'R';

		const ROTATE_CW       = 90;
		const ROTATE_CCW      = -90;
		const FLIP_HORIZONTAL = 'hor';
		const FLIP_VERTICAL   = 'ver';

		const DIMENSION_WIDTH  = 'WIDTH';
		const DIMENSION_HEIGHT = 'HEIGHT';
		const DIMENSION_AUTO   = 'AUTO';

		protected $_valid_drivers = array(
			'gd'          => 'GD',
			'gd2'         => 'GD2',
			'imagemagick' => 'ImageMagick',
			'netpbm'      => 'NetPBM',
		);

		protected $_config = array(
			'driver'           => 'gd2',
			'file_permissions' => 0664,
			'quality'          => 90,
			'maintain_ratio'   => TRUE,
		);

		protected $_source;
		protected $_cache;

		protected $_errors;

		/**
		 * CodeIgniter Image Libraries Resource
		 *
		 * @access  protected
		 * @type    \CodeIgniter\Image
		 */
		protected $_handle;

		public function __construct( array $config = array() )
		{
			$this->_config = array_merge( $this->_config, $config );

			if ( ! array_key_exists( $this->_config[ 'driver' ], $this->_valid_drivers ) )
			{
				throw new Exception( 'Invalid Image Library Driver' );
			}

			if ( isset( $this->_config[ 'source' ] ) )
			{
				$this->setSource( $this->_config[ 'source' ] );
			}

			$this->_handle = new \CodeIgniter\Image();
		}

		protected function _setCache($cache )
		{
			$cache = $this->_source->dirname . DIRECTORY_SEPARATOR . $cache;

			if ( is_file( $cache ) )
			{
				$this->_cache = new \ArrayObject( pathinfo( $cache ), \ArrayObject::ARRAY_AS_PROPS );
				$this->_cache[ 'realpath' ] = $cache;

				if ( $dimension = getimagesize( $cache ) )
				{
					$this->_cache[ 'mime' ] = $dimension[ 'mime' ];
					$this->_cache[ 'dimension' ] = new \ArrayObject( array(
						                                                 'width'     => $dimension[ 0 ],
						                                                 'height'    => $dimension[ 1 ],
						                                                 'attribute' => $dimension[ 3 ],
					                                                 ), \ArrayObject::ARRAY_AS_PROPS );

					return TRUE;
				}
			}

			throw new Exception( 'Invalid cache image: ' . $cache );
		}

		public function setSource($source, $target = NULL )
		{
			if ( is_file( $source ) )
			{
				$this->_config[ 'source' ] = $source;
				$this->_source = new \ArrayObject( pathinfo( $source ), \ArrayObject::ARRAY_AS_PROPS );
				$this->_source[ 'realpath' ] = $source;

				if ( $dimension = getimagesize( $source ) )
				{
					$this->_source[ 'mime' ] = $dimension[ 'mime' ];
					$this->_source[ 'dimension' ] = new \ArrayObject( array(
						                                                  'width'     => $dimension[ 0 ],
						                                                  'height'    => $dimension[ 1 ],
						                                                  'attribute' => $dimension[ 3 ],
					                                                  ), \ArrayObject::ARRAY_AS_PROPS );

				}

				if ( isset( $target ) )
				{
					$this->setTarget( $target );
				}

				return $this;
			}

			throw new Exception( 'Invalid source image: ' . $source );
		}

		public function setTarget($target )
		{
			$filenames = array(
				$this->_source->dirname . DIRECTORY_SEPARATOR . pathinfo( $target, PATHINFO_FILENAME ) . '.' . $this->_source->extension,
				pathinfo( $target, PATHINFO_DIRNAME ) . DIRECTORY_SEPARATOR . pathinfo( $target, PATHINFO_FILENAME ) . '.' . $this->_source->extension,
			);

			foreach ( $filenames as $filename )
			{
				$save_path = pathinfo( $filename, PATHINFO_DIRNAME );

				if ( ! is_dir( $save_path ) )
				{
					if ( ! mkdir( $save_path, 0775, TRUE ) )
					{
						throw new Exception( "Configured target path '" . $save_path . "' is not a directory, doesn't exist or cannot be created." );
					}
				}
				elseif ( ! is_writable( $save_path ) )
				{
					throw new Exception( "Configured target path '" . $save_path . "' is not writable by the PHP process." );
				}
				else
				{
					$this->_config[ 'target' ] = $filename;
				}
			}


			return $this;
		}

		public function setWatermark(array $watermark )
		{
			$this->_config[ 'watermark' ] = $watermark;
		}

		public function setWatermarkText($watermark, $font = NULL, $size = 12, $color = 'ffffff', $shadow_color = '', $shadow_distance = 3 )
		{
			$this->setWatermark( array(
				                      'text' => $watermark,
				                      'font' => array(
					                      'path'   => $font,
					                      'size'   => $size,
					                      'color'  => ltrim( $color, '#' ),
					                      'shadow' => array(
						                      'color'    => ltrim( $shadow_color, '#' ),
						                      'distance' => $shadow_distance,
					                      ),
				                      ),
			                      ) );
		}

		public function setWatermarkImage($watermark, $opacity = 50, $x_transparency = 4, $y_transparency = 4 )
		{
			if ( is_file( $watermark ) )
			{
				$this->setWatermark( array(
					                      'image'        => $watermark,
					                      'opacity'      => $opacity,
					                      'transparency' => [ 'x' => $x_transparency, 'y' => $y_transparency ],
				                      ) );
			}
		}

		public function resize( $size, $dimension = Image::DIMENSION_AUTO )
		{
			if ( strpos( $size, '%' ) )
			{
				$this->_config[ 'size' ] = $this->_calculateSizePercent( $size, $dimension );
			}
			else
			{
				$this->_config[ 'size' ] = $this->_calculateSize( $size, $dimension );
			}

			return $this;
		}

		protected function adaptive( $size, $quadrant = Image::QUADRANT_CENTER )
		{
			$this->_config[ 'maintain_ratio' ] = FALSE;

			$this->resize( $size );

			$max_height = ( intval( $this->_config[ 'size' ][ 'height' ] ) > $this->_source->dimension->height ) ? $this->_source->dimension->height : $this->_config[ 'size' ][ 'height' ];
			$max_width = ( intval( $this->_config[ 'size' ][ 'width' ] ) > $this->_source->dimension->width ) ? $this->_source->dimension->width : $this->_config[ 'size' ][ 'width' ];

			if ( $this->_source->dimension->width > $max_width )
			{
				// Image is landscape
				switch ( $quadrant )
				{
					case 'L':
						$x_axis = 0;
						break;
					case 'R':
						$x_axis = intval( ( $this->_source->dimension->width - $max_width ) );
						break;
					case 'C':
					default:
						$x_axis = intval( ( $this->_source->dimension->width - $max_width ) / 2 );
						break;
				}
			}
			elseif ( $this->_source->dimension->height > $max_height )
			{
				// Image is portrait
				switch ( $quadrant )
				{
					case 'T':
						$y_axis = 0;
						break;
					case 'B':
						$y_axis = intval( ( $this->_source->dimension->height - $max_height ) );
						break;
					case 'C':
					default:
						$y_axis = intval( ( $this->_source->dimension->height - $max_height ) / 2 );
						break;
				}
			}

			$this->_config[ 'size' ] = array(
				'width'  => $max_width,
				'height' => $max_height,
			);

			if ( isset( $x_axis ) )
			{
				$this->_config[ 'crop' ][ 'x_axis' ] = $x_axis;
			}

			if ( isset( $y_axis ) )
			{
				$this->_config[ 'crop' ][ 'y_axis' ] = $y_axis;
			}

			return $this;
		}

		protected function crop( $size, $x_axis, $y_axis = NULL )
		{
			$this->resize( $size );

			if ( isset( $x_axis ) )
			{
				$this->_config[ 'crop' ][ 'x_axis' ] = $x_axis;
			}

			if ( isset( $y_axis ) )
			{
				$this->_config[ 'crop' ][ 'y_axis' ] = $y_axis;
			}

			return $this;
		}

		/**
		 * Rotate
		 *
		 * Rotate image either n degrees, clockwise or counter-clockwise
		 *
		 * @param string|int $direction
		 *
		 * @return  \O2System\Image
		 */
		public function rotate( $direction = 'CW' )
		{
			if ( $direction == 'CW' )
			{
				$this->_config[ 'rotation' ] = 90;
			}
			elseif ( $direction == 'CCW' )
			{
				$this->_config[ 'rotation' ] = -90;
			}
			elseif ( is_numeric( $direction ) )
			{
				$this->_config[ 'rotation' ] = $direction;
			}

			return $this;
		}

		/**
		 * Calculate Size Percent
		 *
		 * Calculates new dimensions based on percentage of source dimension
		 *
		 * @param   int $percent Resize Percent
		 *
		 * @access  protected
		 * @return  array
		 */
		protected function _calculateSizePercent($percent, $dimension )
		{
			$percent = intval( rtrim( $percent, '%' ) );

			return $this->_calculateSize( array(
				                               ceil( ( $this->_source->dimension->width * $percent ) / 100 ), // width
				                               ceil( ( $this->_source->dimension->height * $percent ) / 100 ) // height

			                               ), $dimension );
		}

		protected function _calculateSize($size, $dimension )
		{
			if ( is_array( $size ) )
			{
				list( $width, $height ) = $size;
			}
			else
			{
				$width = $size;
				$height = 0;
			}

			if ( $dimension === Image::DIMENSION_WIDTH )
			{
				if ( $width > 0 AND $height > 0 )
				{
					$dimension = ( ( ( $this->_source->dimension->height / $this->_source->dimension->width ) - ( $height / $width ) ) < 0 )
						? Image::DIMENSION_WIDTH : Image::DIMENSION_HEIGHT;
				}
				else
				{
					$dimension = ( $height === 0 ) ? Image::DIMENSION_WIDTH : Image::DIMENSION_HEIGHT;
				}
			}

			switch ( $dimension )
			{
				default:
				case 'WIDTH':
					$height = (int) ceil( $width * $this->_source->dimension->height / $this->_source->dimension->width );
					break;

				case 'HEIGHT':
					$width = (int) ceil( $this->_source->dimension->width * $height / $this->_source->dimension->height );
					break;
			}

			return [ 'width' => $width, 'height' => $height ];
		}

		public function render()
		{
			// Set Image Library
			$ci_config[ 'image_library' ] = $this->_config[ 'driver' ];

			// Set Image Library Path
			if ( in_array( $this->_config[ 'driver' ], [ 'imagemagick', 'netpbm' ] ) )
			{
				if ( empty( $this->_config[ 'library_path' ] ) )
				{
					$lib_name = $this->_valid_drivers[ $this->_config[ 'driver' ] ];
					throw new Exception( 'The ' . $lib_name . 'Library need library path to be set.' );
				}
				else
				{
					$ci_config[ 'library_path' ] = $this->_config[ 'library_path' ];
				}
			}

			// Set Maintain Ratio
			$ci_config[ 'maintain_ratio' ] = $this->_config[ 'maintain_ratio' ];

			// Set Source Image
			$ci_config[ 'source_image' ] = $this->_config[ 'source' ];

			if ( ! empty( $this->_config[ 'crop' ] ) )
			{
				$crop_config = $ci_config;

				if ( empty( $this->_config[ 'size' ] ) )
				{
					throw new Exception( 'Undefined Crop Target Size' );
				}

				$crop_config[ 'width' ] = $this->_config[ 'size' ][ 'width' ];
				$crop_config[ 'height' ] = $this->_config[ 'size' ][ 'height' ];

				$crop_config[ 'x_axis' ] = $this->_config[ 'crop' ][ 'x_axis' ];

				if ( isset( $this->_config[ 'crop' ][ 'y_axis' ] ) )
				{
					$crop_config[ 'y_axis' ] = $this->_config[ 'crop' ][ 'y_axis' ];
				}

				$crop_config[ 'new_image' ] = $this->_source->filename . '-temp.' . $this->_source->extension;

				$this->_handle->initialize( $crop_config );

				if ( $this->_handle->crop() )
				{
					// Set cache
					$this->_setCache( $crop_config[ 'new_image' ] );
					$this->_handle->clear();
				}
				else
				{
					$this->_errors = $this->_handle->displayErrors();

					return FALSE;
				}
			}
			elseif ( ! empty( $this->_config[ 'size' ] ) )
			{
				$resize_config = $ci_config;
				$resize_config[ 'width' ] = $this->_config[ 'size' ][ 'width' ];
				$resize_config[ 'height' ] = $this->_config[ 'size' ][ 'height' ];
				$resize_config[ 'new_image' ] = $this->_source->filename . '-temp.' . $this->_source->extension;

				$this->_handle->initialize( $resize_config );

				if ( $this->_handle->resize() )
				{
					// Set cache
					$this->_setCache( $resize_config[ 'new_image' ] );
					$this->_handle->clear();
				}
				else
				{
					$this->_errors = $this->_handle->displayErrors();

					return FALSE;
				}
			}

			if ( isset( $this->_config[ 'rotation' ] ) )
			{
				if ( isset( $this->_cache ) )
				{
					$rotate_config[ 'source' ] = $this->_cache->realpath;
				}
				else
				{
					$rotate_config[ 'source' ] = $this->_config[ 'source' ];
				}

				$rotate_config[ 'rotation_angle' ] = $this->_config[ 'rotation' ];
				$rotate_config[ 'new_image' ] = $this->_source->filename . '-temp.' . $this->_source->extension;

				$this->_handle->initialize( $rotate_config );

				if ( $this->_handle->rotate() )
				{
					// Set cache
					$this->_cache = $rotate_config[ 'new_image' ];
					$this->_handle->clear();
				}
				else
				{
					$this->_errors = $this->_handle->displayErrors();

					return FALSE;
				}
			}

			if ( isset( $this->_config[ 'watermark' ] ) )
			{
				if ( isset( $this->_cache ) )
				{
					$watermark_config[ 'source_image' ] = $this->_cache->realpath;
				}
				else
				{
					$watermark_config[ 'source_image' ] = $this->_config[ 'source' ];
				}

				if ( isset( $this->_config[ 'watermark' ][ 'text' ] ) )
				{
					$watermark_config[ 'wm_type' ] = 'text';
					$watermark_config[ 'wm_text' ] = $this->_config[ 'watermark' ][ 'text' ];

					if ( isset( $this->_config[ 'watermark' ][ 'font' ][ 'path' ] ) )
					{
						if ( is_file( $this->_config[ 'watermark' ][ 'font' ][ 'path' ] ) )
						{
							$watermark_config[ 'wm_font_path' ] = $this->_config[ 'watermark' ][ 'font' ][ 'path' ];
						}
					}

					$watermark_config[ 'wm_font_size' ] = $this->_config[ 'watermark' ][ 'font' ][ 'size' ];
					$watermark_config[ 'wm_font_color' ] = $this->_config[ 'watermark' ][ 'font' ][ 'color' ];
					$watermark_config[ 'wm_shadow_color' ] = $this->_config[ 'watermark' ][ 'font' ][ 'shadow' ][ 'color' ];
					$watermark_config[ 'wm_shadow_distance' ] = $this->_config[ 'watermark' ][ 'font' ][ 'shadow' ][ 'distance' ];
				}
				elseif ( isset( $this->_config[ 'watermark' ][ 'image' ] ) )
				{
					$watermark_config[ 'wm_overlay_path' ] = $this->_config[ 'watermark' ][ 'image' ];
					$watermark_config[ 'wm_opacity' ] = $this->_config[ 'watermark' ][ 'opacity' ];
					$watermark_config[ 'wm_x_transp' ] = $this->_config[ 'watermark' ][ 'transparency' ][ 'x' ];
					$watermark_config[ 'wm_y_transp' ] = $this->_config[ 'watermark' ][ 'transparency' ][ 'y' ];
				}

				$watermark_config[ 'new_image' ] = $this->_source->filename . '-temp.' . $this->_source->extension;

				$this->_handle->initialize( $watermark_config );

				if ( $this->_handle->watermark() )
				{
					// Set cache
					$this->_setCache( $watermark_config[ 'new_image' ] );
					$this->_handle->clear();
				}
				else
				{
					$this->_errors = $this->_handle->displayErrors();

					return FALSE;
				}
			}

			if ( ! empty( $this->_cache ) )
			{
				// Set Target Image
				if ( isset( $this->_config[ 'target' ] ) )
				{
					$this->save( $this->_config[ 'target' ] );
				}

				return TRUE;
			}

			return FALSE;
		}

		public function save( $target )
		{
			if ( empty( $this->_cache ) )
			{
				if ( $this->render() === FALSE )
				{
					return FALSE;
				}

				if ( isset( $this->_cache ) )
				{
					$this->setTarget( $target );

					$image = fopen( $this->_config[ 'target' ], 'w' );
					fwrite( $image, file_get_contents( $this->_cache->realpath ) );
					fclose( $image );

					unlink( $this->_cache->realpath );

					return TRUE;
				}
			}

			return FALSE;
		}

		public function string()
		{
			if ( empty( $this->_cache ) )
			{
				if ( $this->render() === FALSE )
				{
					return FALSE;
				}

				if ( isset( $this->_cache ) )
				{
					$string = NULL;
					ob_start();
					readfile( $this->_cache->realpath );
					$string = ob_get_contents();
					ob_end_clean();

					echo $string;
					die;

					return $string;
				}
			}

			return FALSE;
		}

		public function show()
		{
			if ( empty( $this->_cache ) )
			{
				if ( $this->render() === FALSE )
				{
					return FALSE;
				}

				if ( isset( $this->_cache ) )
				{
					header( 'Content-Disposition: filename=' . $this->_cache->basename . ';' );
					header( 'Content-Type: ' . $this->_cache->mime );
					header( 'Content-Transfer-Encoding: binary' );
					header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', time() ) . ' GMT' );

					echo file_get_contents( $this->_cache->realpath );
					unlink( $this->_cache->realpath );

					return TRUE;
				}
			}

			throw new Exception( 'Unable to render image from source' . $this->_source->realpath );
		}

		public function getErrors()
		{
			return $this->_errors;
		}
	}
}

namespace O2System\Image
{

	use O2System\Glob\Interfaces\ExceptionInterface;

	class Exception extends ExceptionInterface
	{
	}
}

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license      http://opensource.org/licenses/MIT	MIT License
 * @link         http://codeigniter.com
 * @since        Version 1.0.0
 * @filesource
 */
namespace CodeIgniter
{
	/**
	 * Image Manipulation class
	 *
	 * @package        CodeIgniter
	 * @subpackage     Libraries
	 * @category       Image_lib
	 * @author         EllisLab Dev Team
	 * @link           http://codeigniter.com/user_guide/libraries/image_lib.html
	 */
	class Image
	{
		/**
		 * PHP extension/library to use for image manipulation
		 * Can be: imagemagick, netpbm, gd, gd2
		 *
		 * @var string
		 */
		public $image_library = 'gd2';

		/**
		 * Path to the graphic library (if applicable)
		 *
		 * @var string
		 */
		public $library_path = '';

		/**
		 * Whether to send to browser or write to disk
		 *
		 * @var bool
		 */
		public $dynamic_output = FALSE;

		/**
		 * Path to original image
		 *
		 * @var string
		 */
		public $source_image = '';

		/**
		 * Path to the modified image
		 *
		 * @var string
		 */
		public $new_image = '';

		/**
		 * Image width
		 *
		 * @var int
		 */
		public $width = '';

		/**
		 * Image height
		 *
		 * @var int
		 */
		public $height = '';

		/**
		 * Quality percentage of new image
		 *
		 * @var int
		 */
		public $quality = 90;

		/**
		 * Whether to create a thumbnail
		 *
		 * @var bool
		 */
		public $create_thumb = FALSE;

		/**
		 * String to add to thumbnail version of image
		 *
		 * @var string
		 */
		public $thumb_marker = '_thumb';

		/**
		 * Whether to maintain aspect ratio when resizing or use hard values
		 *
		 * @var bool
		 */
		public $maintain_ratio = TRUE;

		/**
		 * auto, height, or width.  Determines what to use as the master dimension
		 *
		 * @var string
		 */
		public $master_dim = 'auto';

		/**
		 * Angle at to rotate image
		 *
		 * @var string
		 */
		public $rotation_angle = '';

		/**
		 * X Coordinate for manipulation of the current image
		 *
		 * @var int
		 */
		public $x_axis = '';

		/**
		 * Y Coordinate for manipulation of the current image
		 *
		 * @var int
		 */
		public $y_axis = '';

		// --------------------------------------------------------------------------
		// Watermark Vars
		// --------------------------------------------------------------------------

		/**
		 * Watermark text if graphic is not used
		 *
		 * @var string
		 */
		public $wm_text = '';

		/**
		 * Type of watermarking.  Options:  text/overlay
		 *
		 * @var string
		 */
		public $wm_type = 'text';

		/**
		 * Default transparency for watermark
		 *
		 * @var int
		 */
		public $wm_x_transp = 4;

		/**
		 * Default transparency for watermark
		 *
		 * @var int
		 */
		public $wm_y_transp = 4;

		/**
		 * Watermark image path
		 *
		 * @var string
		 */
		public $wm_overlay_path = '';

		/**
		 * TT font
		 *
		 * @var string
		 */
		public $wm_font_path = '';

		/**
		 * Font size (different versions of GD will either use points or pixels)
		 *
		 * @var int
		 */
		public $wm_font_size = 17;

		/**
		 * Vertical alignment:   T M B
		 *
		 * @var string
		 */
		public $wm_vrt_alignment = 'B';

		/**
		 * Horizontal alignment: L R C
		 *
		 * @var string
		 */
		public $wm_hor_alignment = 'C';

		/**
		 * Padding around text
		 *
		 * @var int
		 */
		public $wm_padding = 0;

		/**
		 * Lets you push text to the right
		 *
		 * @var int
		 */
		public $wm_hor_offset = 0;

		/**
		 * Lets you push text down
		 *
		 * @var int
		 */
		public $wm_vrt_offset = 0;

		/**
		 * Text color
		 *
		 * @var string
		 */
		protected $wm_font_color = '#ffffff';

		/**
		 * Dropshadow color
		 *
		 * @var string
		 */
		protected $wm_shadow_color = '';

		/**
		 * Dropshadow distance
		 *
		 * @var int
		 */
		public $wm_shadow_distance = 2;

		/**
		 * Image opacity: 1 - 100  Only works with image
		 *
		 * @var int
		 */
		public $wm_opacity = 50;

		// --------------------------------------------------------------------------
		// Private Vars
		// --------------------------------------------------------------------------

		/**
		 * Source image folder
		 *
		 * @var string
		 */
		public $source_folder = '';

		/**
		 * Destination image folder
		 *
		 * @var string
		 */
		public $dest_folder = '';

		/**
		 * Image mime-type
		 *
		 * @var string
		 */
		public $mime_type = '';

		/**
		 * Original image width
		 *
		 * @var int
		 */
		public $orig_width = '';

		/**
		 * Original image height
		 *
		 * @var int
		 */
		public $orig_height = '';

		/**
		 * Image format
		 *
		 * @var string
		 */
		public $image_type = '';

		/**
		 * Size of current image
		 *
		 * @var string
		 */
		public $size_str = '';

		/**
		 * Full path to source image
		 *
		 * @var string
		 */
		public $full_src_path = '';

		/**
		 * Full path to destination image
		 *
		 * @var string
		 */
		public $full_dst_path = '';

		/**
		 * File permissions
		 *
		 * @var    int
		 */
		public $file_permissions = 0644;

		/**
		 * Name of function to create image
		 *
		 * @var string
		 */
		public $create_fnc = 'imagecreatetruecolor';

		/**
		 * Name of function to copy image
		 *
		 * @var string
		 */
		public $copy_fnc = 'imagecopyresampled';

		/**
		 * Error messages
		 *
		 * @var array
		 */
		public $error_msg = array();

		/**
		 * Whether to have a drop shadow on watermark
		 *
		 * @var bool
		 */
		protected $wm_use_drop_shadow = FALSE;

		/**
		 * Whether to use truetype fonts
		 *
		 * @var bool
		 */
		public $wm_use_truetype = FALSE;

		/**
		 * Initialize Image Library
		 *
		 * @param    array $props
		 *
		 * @return    void
		 */
		public function __construct( $props = array() )
		{
			if ( count( $props ) > 0 )
			{
				$this->initialize( $props );
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Initialize image properties
		 *
		 * Resets values in case this class is used in a loop
		 *
		 * @return    void
		 */
		public function clear()
		{
			$props = array( 'thumb_marker', 'library_path', 'source_image', 'new_image', 'width', 'height', 'rotation_angle', 'x_axis', 'y_axis', 'wm_text', 'wm_overlay_path', 'wm_font_path', 'wm_shadow_color', 'source_folder', 'dest_folder', 'mime_type', 'orig_width', 'orig_height', 'image_type', 'size_str', 'full_src_path', 'full_dst_path' );

			foreach ( $props as $val )
			{
				$this->$val = '';
			}

			$this->image_library = 'gd2';
			$this->dynamic_output = FALSE;
			$this->quality = 90;
			$this->create_thumb = FALSE;
			$this->thumb_marker = '-thumb';
			$this->maintain_ratio = TRUE;
			$this->master_dim = 'auto';
			$this->wm_type = 'text';
			$this->wm_x_transp = 4;
			$this->wm_y_transp = 4;
			$this->wm_font_size = 17;
			$this->wm_vrt_alignment = 'B';
			$this->wm_hor_alignment = 'C';
			$this->wm_padding = 0;
			$this->wm_hor_offset = 0;
			$this->wm_vrt_offset = 0;
			$this->wm_font_color = '#ffffff';
			$this->wm_shadow_distance = 2;
			$this->wm_opacity = 50;
			$this->create_fnc = 'imagecreatetruecolor';
			$this->copy_fnc = 'imagecopyresampled';
			$this->error_msg = array();
			$this->wm_use_drop_shadow = FALSE;
			$this->wm_use_truetype = FALSE;
		}

		// --------------------------------------------------------------------

		/**
		 * initialize image preferences
		 *
		 * @param    array
		 *
		 * @return    bool
		 */
		public function initialize( $props = array() )
		{
			// Convert array elements into class variables
			if ( count( $props ) > 0 )
			{
				foreach ( $props as $key => $val )
				{
					if ( property_exists( $this, $key ) )
					{
						if ( in_array( $key, array( 'wm_font_color', 'wm_shadow_color' ) ) )
						{
							if ( preg_match( '/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i', $val, $matches ) )
							{
								/* $matches[1] contains our hex color value, but it might be
								 * both in the full 6-length format or the shortened 3-length
								 * value.
								 * We'll later need the full version, so we keep it if it's
								 * already there and if not - we'll convert to it. We can
								 * access string characters by their index as in an array,
								 * so we'll do that and use concatenation to form the final
								 * value:
								 */
								$val = ( strlen( $matches[ 1 ] ) === 6 )
									? '#' . $matches[ 1 ]
									: '#' . $matches[ 1 ][ 0 ] . $matches[ 1 ][ 0 ] . $matches[ 1 ][ 1 ] . $matches[ 1 ][ 1 ] . $matches[ 1 ][ 2 ] . $matches[ 1 ][ 2 ];
							}
							else
							{
								continue;
							}
						}

						$this->$key = $val;
					}
				}
			}

			// Is there a source image? If not, there's no reason to continue
			if ( $this->source_image === '' )
			{
				$this->setError( 'IMAGE_SOURCE_IMAGE_REQUIRED' );

				return FALSE;
			}

			/* Is getimagesize() available?
			 *
			 * We use it to determine the image properties (width/height).
			 * Note: We need to figure out how to determine image
			 * properties using ImageMagick and NetPBM
			 */
			if ( ! function_exists( 'getimagesize' ) )
			{
				$this->setError( 'IMAGE_GD_REQUIRED_FOR_PROPERTIES' );

				return FALSE;
			}

			$this->image_library = strtolower( $this->image_library );

			/* Set the full server path
			 *
			 * The source image may or may not contain a path.
			 * Either way, we'll try use realpath to generate the
			 * full server path in order to more reliably read it.
			 */
			if ( ( $full_source_path = realpath( $this->source_image ) ) !== FALSE )
			{
				$full_source_path = str_replace( '\\', '/', $full_source_path );
			}
			else
			{
				$full_source_path = $this->source_image;
			}

			$x = explode( '/', $full_source_path );
			$this->source_image = end( $x );
			$this->source_folder = str_replace( $this->source_image, '', $full_source_path );

			// Set the Image Properties
			if ( ! $this->getImageProperties( $this->source_folder . $this->source_image ) )
			{
				return FALSE;
			}

			/*
			 * Assign the "new" image name/path
			 *
			 * If the user has set a "new_image" name it means
			 * we are making a copy of the source image. If not
			 * it means we are altering the original. We'll
			 * set the destination filename and path accordingly.
			 */
			if ( $this->new_image === '' )
			{
				$this->dest_image = $this->source_image;
				$this->dest_folder = $this->source_folder;
			}
			elseif ( strpos( $this->new_image, '/' ) === FALSE )
			{
				$this->dest_folder = $this->source_folder;
				$this->dest_image = $this->new_image;
			}
			else
			{
				if ( strpos( $this->new_image, '/' ) === FALSE && strpos( $this->new_image, '\\' ) === FALSE )
				{
					$full_dest_path = str_replace( '\\', '/', realpath( $this->new_image ) );
				}
				else
				{
					$full_dest_path = $this->new_image;
				}

				// Is there a file name?
				if ( ! preg_match( '#\.(jpg|jpeg|gif|png)$#i', $full_dest_path ) )
				{
					$this->dest_folder = $full_dest_path . '/';
					$this->dest_image = $this->source_image;
				}
				else
				{
					$x = explode( '/', $full_dest_path );
					$this->dest_image = end( $x );
					$this->dest_folder = str_replace( $this->dest_image, '', $full_dest_path );
				}
			}

			/* Compile the finalized filenames/paths
			 *
			 * We'll create two master strings containing the
			 * full server path to the source image and the
			 * full server path to the destination image.
			 * We'll also split the destination image name
			 * so we can insert the thumbnail marker if needed.
			 */
			if ( $this->create_thumb === FALSE OR $this->thumb_marker === '' )
			{
				$this->thumb_marker = '';
			}

			$xp = $this->explodeName( $this->dest_image );

			$filename = $xp[ 'name' ];
			$file_ext = $xp[ 'ext' ];

			$this->full_src_path = $this->source_folder . $this->source_image;
			$this->full_dst_path = $this->dest_folder . $filename . $this->thumb_marker . $file_ext;

			/* Should we maintain image proportions?
			 *
			 * When creating thumbs or copies, the target width/height
			 * might not be in correct proportion with the source
			 * image's width/height. We'll recalculate it here.
			 */
			if ( $this->maintain_ratio === TRUE && ( $this->width !== 0 OR $this->height !== 0 ) )
			{
				$this->imageReproportion();
			}

			/* Was a width and height specified?
			 *
			 * If the destination width/height was not submitted we
			 * will use the values from the actual file
			 */
			if ( $this->width === '' )
			{
				$this->width = $this->orig_width;
			}

			if ( $this->height === '' )
			{
				$this->height = $this->orig_height;
			}

			// Set the quality
			$this->quality = trim( str_replace( '%', '', $this->quality ) );

			if ( $this->quality === '' OR $this->quality === 0 OR ! ctype_digit( $this->quality ) )
			{
				$this->quality = 90;
			}

			// Set the x/y coordinates
			is_numeric( $this->x_axis ) OR $this->x_axis = 0;
			is_numeric( $this->y_axis ) OR $this->y_axis = 0;

			// Watermark-related Stuff...
			if ( $this->wm_overlay_path !== '' )
			{
				$this->wm_overlay_path = str_replace( '\\', '/', realpath( $this->wm_overlay_path ) );
			}

			if ( $this->wm_shadow_color !== '' )
			{
				$this->wm_use_drop_shadow = TRUE;
			}
			elseif ( $this->wm_use_drop_shadow === TRUE && $this->wm_shadow_color === '' )
			{
				$this->wm_use_drop_shadow = FALSE;
			}

			if ( $this->wm_font_path !== '' )
			{
				$this->wm_use_truetype = TRUE;
			}

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Resize
		 *
		 * This is a wrapper function that chooses the proper
		 * resize function based on the protocol specified
		 *
		 * @return    bool
		 */
		public function resize()
		{
			$protocol = ( $this->image_library === 'gd2' ) ? 'image_process_gd' : 'image_process_' . $this->image_library;

			return $this->$protocol( 'resize' );
		}

		// --------------------------------------------------------------------

		/**
		 * Image Crop
		 *
		 * This is a wrapper function that chooses the proper
		 * cropping function based on the protocol specified
		 *
		 * @return    bool
		 */
		public function crop()
		{
			$protocol = ( $this->image_library === 'gd2' ) ? 'image_process_gd' : 'image_process_' . $this->image_library;

			return $this->$protocol( 'crop' );
		}

		// --------------------------------------------------------------------

		/**
		 * Image Rotate
		 *
		 * This is a wrapper function that chooses the proper
		 * rotation function based on the protocol specified
		 *
		 * @return    bool
		 */
		public function rotate()
		{
			// Allowed rotation values
			$degs = array( 90, 180, 270, 'vrt', 'hor' );

			if ( $this->rotation_angle === '' OR ! in_array( $this->rotation_angle, $degs ) )
			{
				$this->setError( 'IMAGE_ROTATION_ANGLE_REQUIRED' );

				return FALSE;
			}

			// Reassign the width and height
			if ( $this->rotation_angle === 90 OR $this->rotation_angle === 270 )
			{
				$this->width = $this->orig_height;
				$this->height = $this->orig_width;
			}
			else
			{
				$this->width = $this->orig_width;
				$this->height = $this->orig_height;
			}

			// Choose resizing function
			if ( $this->image_library === 'imagemagick' OR $this->image_library === 'netpbm' )
			{
				$protocol = 'image_process_' . $this->image_library;

				return $this->$protocol( 'rotate' );
			}

			return ( $this->rotation_angle === 'hor' OR $this->rotation_angle === 'vrt' )
				? $this->imageMirrorGd()
				: $this->imageRotateGd();
		}

		// --------------------------------------------------------------------

		/**
		 * Image Process Using GD/GD2
		 *
		 * This function will resize or crop
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function imageProcessGd($action = 'resize' )
		{
			$v2_override = FALSE;

			// If the target width/height match the source, AND if the new file name is not equal to the old file name
			// we'll simply make a copy of the original with the new name... assuming dynamic rendering is off.
			if ( $this->dynamic_output === FALSE && $this->orig_width === $this->width && $this->orig_height === $this->height )
			{
				if ( $this->source_image !== $this->new_image && @copy( $this->full_src_path, $this->full_dst_path ) )
				{
					chmod( $this->full_dst_path, $this->file_permissions );
				}

				return TRUE;
			}

			// Let's set up our values based on the action
			if ( $action === 'crop' )
			{
				// Reassign the source width/height if cropping
				$this->orig_width = $this->width;
				$this->orig_height = $this->height;

				// GD 2.0 has a cropping bug so we'll test for it
				if ( $this->gdVersion() !== FALSE )
				{
					$gd_version = str_replace( '0', '', $this->gdVersion() );
					$v2_override = ( $gd_version == 2 );
				}
			}
			else
			{
				// If resizing the x/y axis must be zero
				$this->x_axis = 0;
				$this->y_axis = 0;
			}

			//  Create the image handle
			if ( ! ( $src_img = $this->imageCreateGd() ) )
			{
				return FALSE;
			}

			/* Create the image
			 *
			 * Old conditional which users report cause problems with shared GD libs who report themselves as "2.0 or greater"
			 * it appears that this is no longer the issue that it was in 2004, so we've removed it, retaining it in the comment
			 * below should that ever prove inaccurate.
			 *
			 * if ($this->image_library === 'gd2' && function_exists('imagecreatetruecolor') && $v2_override === FALSE)
			 */
			if ( $this->image_library === 'gd2' && function_exists( 'imagecreatetruecolor' ) )
			{
				$create = 'imagecreatetruecolor';
				$copy = 'imagecopyresampled';
			}
			else
			{
				$create = 'imagecreate';
				$copy = 'imagecopyresized';
			}

			$dst_img = $create( $this->width, $this->height );

			if ( $this->image_type === 3 ) // png we can actually preserve transparency
			{
				imagealphablending( $dst_img, FALSE );
				imagesavealpha( $dst_img, TRUE );
			}

			$copy( $dst_img, $src_img, 0, 0, $this->x_axis, $this->y_axis, $this->width, $this->height, $this->orig_width, $this->orig_height );

			// Show the image
			if ( $this->dynamic_output === TRUE )
			{
				$this->imageDisplayGd( $dst_img );
			}
			elseif ( ! $this->imageSaveGd( $dst_img ) ) // Or save it
			{
				return FALSE;
			}

			// Kill the file handles
			imagedestroy( $dst_img );
			imagedestroy( $src_img );

			chmod( $this->full_dst_path, $this->file_permissions );

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Process Using ImageMagick
		 *
		 * This function will resize, crop or rotate
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function imageProcessImagemagick($action = 'resize' )
		{
			//  Do we have a vaild library path?
			if ( $this->library_path === '' )
			{
				$this->setError( 'IMAGE_LIBRARY_PATH_INVALID' );

				return FALSE;
			}

			if ( ! preg_match( '/convert$/i', $this->library_path ) )
			{
				$this->library_path = rtrim( $this->library_path, '/' ) . '/convert';
			}

			// Execute the command
			$cmd = $this->library_path . ' -quality ' . $this->quality;

			if ( $action === 'crop' )
			{
				$cmd .= ' -crop ' . $this->width . 'x' . $this->height . '+' . $this->x_axis . '+' . $this->y_axis . ' "' . $this->full_src_path . '" "' . $this->full_dst_path . '" 2>&1';
			}
			elseif ( $action === 'rotate' )
			{
				$angle = ( $this->rotation_angle === 'hor' OR $this->rotation_angle === 'vrt' )
					? '-flop' : '-rotate ' . $this->rotation_angle;

				$cmd .= ' ' . $angle . ' "' . $this->full_src_path . '" "' . $this->full_dst_path . '" 2>&1';
			}
			else // Resize
			{
				if ( $this->maintain_ratio === TRUE )
				{
					$cmd .= ' -resize ' . $this->width . 'x' . $this->height . ' "' . $this->full_src_path . '" "' . $this->full_dst_path . '" 2>&1';
				}
				else
				{
					$cmd .= ' -resize ' . $this->width . 'x' . $this->height . '\! "' . $this->full_src_path . '" "' . $this->full_dst_path . '" 2>&1';
				}
			}

			$retval = 1;
			// exec() might be disabled
			if ( functionUsable( 'exec' ) )
			{
				@exec( $cmd, $output, $retval );
			}

			// Did it work?
			if ( $retval > 0 )
			{
				$this->setError( 'IMAGE_PROCESS_FAILED' );

				return FALSE;
			}

			chmod( $this->full_dst_path, $this->file_permissions );

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Process Using NetPBM
		 *
		 * This function will resize, crop or rotate
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function imageProcessNetpbm($action = 'resize' )
		{
			if ( $this->library_path === '' )
			{
				$this->setError( 'IMAGE_LIBRARY_PATH_INVALID' );

				return FALSE;
			}

			// Build the resizing command
			switch ( $this->image_type )
			{
				case 1 :
					$cmd_in = 'giftopnm';
					$cmd_out = 'ppmtogif';
					break;
				case 2 :
					$cmd_in = 'jpegtopnm';
					$cmd_out = 'ppmtojpeg';
					break;
				case 3 :
					$cmd_in = 'pngtopnm';
					$cmd_out = 'ppmtopng';
					break;
			}

			if ( $action === 'crop' )
			{
				$cmd_inner = 'pnmcut -left ' . $this->x_axis . ' -top ' . $this->y_axis . ' -width ' . $this->width . ' -height ' . $this->height;
			}
			elseif ( $action === 'rotate' )
			{
				switch ( $this->rotation_angle )
				{
					case 90:
						$angle = 'r270';
						break;
					case 180:
						$angle = 'r180';
						break;
					case 270:
						$angle = 'r90';
						break;
					case 'vrt':
						$angle = 'tb';
						break;
					case 'hor':
						$angle = 'lr';
						break;
				}

				$cmd_inner = 'pnmflip -' . $angle . ' ';
			}
			else // Resize
			{
				$cmd_inner = 'pnmscale -xysize ' . $this->width . ' ' . $this->height;
			}

			$cmd = $this->library_path . $cmd_in . ' ' . $this->full_src_path . ' | ' . $cmd_inner . ' | ' . $cmd_out . ' > ' . $this->dest_folder . 'netpbm.tmp';

			$retval = 1;
			// exec() might be disabled
			if ( functionUsable( 'exec' ) )
			{
				@exec( $cmd, $output, $retval );
			}

			// Did it work?
			if ( $retval > 0 )
			{
				$this->setError( 'IMAGE_PROCESS_FAILED' );

				return FALSE;
			}

			// With NetPBM we have to create a temporary image.
			// If you try manipulating the original it fails so
			// we have to rename the temp file.
			copy( $this->dest_folder . 'netpbm.tmp', $this->full_dst_path );
			unlink( $this->dest_folder . 'netpbm.tmp' );
			chmod( $this->full_dst_path, $this->file_permissions );

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Rotate Using GD
		 *
		 * @return    bool
		 */
		public function imageRotateGd()
		{
			// Create the image handle
			if ( ! ( $src_img = $this->imageCreateGd() ) )
			{
				return FALSE;
			}

			// Set the background color
			// This won't work with transparent PNG files so we are
			// going to have to figure out how to determine the color
			// of the alpha channel in a future release.

			$white = imagecolorallocate( $src_img, 255, 255, 255 );

			// Rotate it!
			$dst_img = imagerotate( $src_img, $this->rotation_angle, $white );

			// Show the image
			if ( $this->dynamic_output === TRUE )
			{
				$this->imageDisplayGd( $dst_img );
			}
			elseif ( ! $this->imageSaveGd( $dst_img ) ) // ... or save it
			{
				return FALSE;
			}

			// Kill the file handles
			imagedestroy( $dst_img );
			imagedestroy( $src_img );

			chmod( $this->full_dst_path, $this->file_permissions );

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Create Mirror Image using GD
		 *
		 * This function will flip horizontal or vertical
		 *
		 * @return    bool
		 */
		public function imageMirrorGd()
		{
			if ( ! $src_img = $this->imageCreateGd() )
			{
				return FALSE;
			}

			$width = $this->orig_width;
			$height = $this->orig_height;

			if ( $this->rotation_angle === 'hor' )
			{
				for ( $i = 0; $i < $height; $i++, $left = 0, $right = $width - 1 )
				{
					while ( $left < $right )
					{
						$cl = imagecolorat( $src_img, $left, $i );
						$cr = imagecolorat( $src_img, $right, $i );

						imagesetpixel( $src_img, $left, $i, $cr );
						imagesetpixel( $src_img, $right, $i, $cl );

						$left++;
						$right--;
					}
				}
			}
			else
			{
				for ( $i = 0; $i < $width; $i++, $top = 0, $bot = $height - 1 )
				{
					while ( $top < $bot )
					{
						$ct = imagecolorat( $src_img, $i, $top );
						$cb = imagecolorat( $src_img, $i, $bot );

						imagesetpixel( $src_img, $i, $top, $cb );
						imagesetpixel( $src_img, $i, $bot, $ct );

						$top++;
						$bot--;
					}
				}
			}

			// Show the image
			if ( $this->dynamic_output === TRUE )
			{
				$this->imageDisplayGd( $src_img );
			}
			elseif ( ! $this->imageSaveGd( $src_img ) ) // ... or save it
			{
				return FALSE;
			}

			// Kill the file handles
			imagedestroy( $src_img );

			chmod( $this->full_dst_path, $this->file_permissions );

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Watermark
		 *
		 * This is a wrapper function that chooses the type
		 * of watermarking based on the specified preference.
		 *
		 * @return    bool
		 */
		public function watermark()
		{
			return ( $this->wm_type === 'overlay' ) ? $this->overlayWatermark() : $this->textWatermark();
		}

		// --------------------------------------------------------------------

		/**
		 * Watermark - Graphic Version
		 *
		 * @return    bool
		 */
		public function overlayWatermark()
		{
			if ( ! function_exists( 'imagecolortransparent' ) )
			{
				$this->setError( 'IMAGE_GD_REQUIRED' );

				return FALSE;
			}

			// Fetch source image properties
			$this->getImageProperties();

			// Fetch watermark image properties
			$props = $this->getImageProperties( $this->wm_overlay_path, TRUE );
			$wm_img_type = $props[ 'image_type' ];
			$wm_width = $props[ 'width' ];
			$wm_height = $props[ 'height' ];

			// Create two image resources
			$wm_img = $this->imageCreateGd( $this->wm_overlay_path, $wm_img_type );
			$src_img = $this->imageCreateGd( $this->full_src_path );

			// Reverse the offset if necessary
			// When the image is positioned at the bottom
			// we don't want the vertical offset to push it
			// further down. We want the reverse, so we'll
			// invert the offset. Same with the horizontal
			// offset when the image is at the right

			$this->wm_vrt_alignment = strtoupper( $this->wm_vrt_alignment[ 0 ] );
			$this->wm_hor_alignment = strtoupper( $this->wm_hor_alignment[ 0 ] );

			if ( $this->wm_vrt_alignment === 'B' )
			{
				$this->wm_vrt_offset = $this->wm_vrt_offset * -1;
			}

			if ( $this->wm_hor_alignment === 'R' )
			{
				$this->wm_hor_offset = $this->wm_hor_offset * -1;
			}

			// Set the base x and y axis values
			$x_axis = $this->wm_hor_offset + $this->wm_padding;
			$y_axis = $this->wm_vrt_offset + $this->wm_padding;

			// Set the vertical position
			if ( $this->wm_vrt_alignment === 'M' )
			{
				$y_axis += ( $this->orig_height / 2 ) - ( $wm_height / 2 );
			}
			elseif ( $this->wm_vrt_alignment === 'B' )
			{
				$y_axis += $this->orig_height - $wm_height;
			}

			// Set the horizontal position
			if ( $this->wm_hor_alignment === 'C' )
			{
				$x_axis += ( $this->orig_width / 2 ) - ( $wm_width / 2 );
			}
			elseif ( $this->wm_hor_alignment === 'R' )
			{
				$x_axis += $this->orig_width - $wm_width;
			}

			//  Build the finalized image
			if ( $wm_img_type === 3 && function_exists( 'imagealphablending' ) )
			{
				@imagealphablending( $src_img, TRUE );
			}

			// Set RGB values for text and shadow
			$rgba = imagecolorat( $wm_img, $this->wm_x_transp, $this->wm_y_transp );
			$alpha = ( $rgba & 0x7F000000 ) >> 24;

			// make a best guess as to whether we're dealing with an image with alpha transparency or no/binary transparency
			if ( $alpha > 0 )
			{
				// copy the image directly, the image's alpha transparency being the sole determinant of blending
				imagecopy( $src_img, $wm_img, $x_axis, $y_axis, 0, 0, $wm_width, $wm_height );
			}
			else
			{
				// set our RGB value from above to be transparent and merge the images with the specified opacity
				imagecolortransparent( $wm_img, imagecolorat( $wm_img, $this->wm_x_transp, $this->wm_y_transp ) );
				imagecopymerge( $src_img, $wm_img, $x_axis, $y_axis, 0, 0, $wm_width, $wm_height, $this->wm_opacity );
			}

			// We can preserve transparency for PNG images
			if ( $this->image_type === 3 )
			{
				imagealphablending( $src_img, FALSE );
				imagesavealpha( $src_img, TRUE );
			}

			// Output the image
			if ( $this->dynamic_output === TRUE )
			{
				$this->imageDisplayGd( $src_img );
			}
			elseif ( ! $this->imageSaveGd( $src_img ) ) // ... or save it
			{
				return FALSE;
			}

			imagedestroy( $src_img );
			imagedestroy( $wm_img );

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Watermark - Text Version
		 *
		 * @return    bool
		 */
		public function textWatermark()
		{
			if ( ! ( $src_img = $this->imageCreateGd() ) )
			{
				return FALSE;
			}

			if ( $this->wm_use_truetype === TRUE && ! is_file( $this->wm_font_path ) )
			{
				$this->setError( 'IMAGE_MISSING_FONT' );

				return FALSE;
			}

			// Fetch source image properties
			$this->getImageProperties();

			// Reverse the vertical offset
			// When the image is positioned at the bottom
			// we don't want the vertical offset to push it
			// further down. We want the reverse, so we'll
			// invert the offset. Note: The horizontal
			// offset flips itself automatically

			if ( $this->wm_vrt_alignment === 'B' )
			{
				$this->wm_vrt_offset = $this->wm_vrt_offset * -1;
			}

			if ( $this->wm_hor_alignment === 'R' )
			{
				$this->wm_hor_offset = $this->wm_hor_offset * -1;
			}

			// Set font width and height
			// These are calculated differently depending on
			// whether we are using the true type font or not
			if ( $this->wm_use_truetype === TRUE )
			{
				if ( empty( $this->wm_font_size ) )
				{
					$this->wm_font_size = 17;
				}

				if ( function_exists( 'imagettfbbox' ) )
				{
					$temp = imagettfbbox( $this->wm_font_size, 0, $this->wm_font_path, $this->wm_text );
					$temp = $temp[ 2 ] - $temp[ 0 ];

					$fontwidth = $temp / strlen( $this->wm_text );
				}
				else
				{
					$fontwidth = $this->wm_font_size - ( $this->wm_font_size / 4 );
				}

				$fontheight = $this->wm_font_size;
				$this->wm_vrt_offset += $this->wm_font_size;
			}
			else
			{
				$fontwidth = imagefontwidth( $this->wm_font_size );
				$fontheight = imagefontheight( $this->wm_font_size );
			}

			// Set base X and Y axis values
			$x_axis = $this->wm_hor_offset + $this->wm_padding;
			$y_axis = $this->wm_vrt_offset + $this->wm_padding;

			if ( $this->wm_use_drop_shadow === FALSE )
			{
				$this->wm_shadow_distance = 0;
			}

			$this->wm_vrt_alignment = strtoupper( $this->wm_vrt_alignment[ 0 ] );
			$this->wm_hor_alignment = strtoupper( $this->wm_hor_alignment[ 0 ] );

			// Set vertical alignment
			if ( $this->wm_vrt_alignment === 'M' )
			{
				$y_axis += ( $this->orig_height / 2 ) + ( $fontheight / 2 );
			}
			elseif ( $this->wm_vrt_alignment === 'B' )
			{
				$y_axis += $this->orig_height - $fontheight - $this->wm_shadow_distance - ( $fontheight / 2 );
			}

			// Set horizontal alignment
			if ( $this->wm_hor_alignment === 'R' )
			{
				$x_axis += $this->orig_width - ( $fontwidth * strlen( $this->wm_text ) ) - $this->wm_shadow_distance;
			}
			elseif ( $this->wm_hor_alignment === 'C' )
			{
				$x_axis += floor( ( $this->orig_width - ( $fontwidth * strlen( $this->wm_text ) ) ) / 2 );
			}

			if ( $this->wm_use_drop_shadow )
			{
				// Offset from text
				$x_shad = $x_axis + $this->wm_shadow_distance;
				$y_shad = $y_axis + $this->wm_shadow_distance;

				/* Set RGB values for shadow
				 *
				 * First character is #, so we don't really need it.
				 * Get the rest of the string and split it into 2-length
				 * hex values:
				 */
				$drp_color = str_split( substr( $this->wm_shadow_color, 1, 6 ), 2 );
				$drp_color = imagecolorclosest( $src_img, hexdec( $drp_color[ 0 ] ), hexdec( $drp_color[ 1 ] ), hexdec( $drp_color[ 2 ] ) );

				// Add the shadow to the source image
				if ( $this->wm_use_truetype )
				{
					imagettftext( $src_img, $this->wm_font_size, 0, $x_shad, $y_shad, $drp_color, $this->wm_font_path, $this->wm_text );
				}
				else
				{
					imagestring( $src_img, $this->wm_font_size, $x_shad, $y_shad, $this->wm_text, $drp_color );
				}
			}

			/* Set RGB values for text
			 *
			 * First character is #, so we don't really need it.
			 * Get the rest of the string and split it into 2-length
			 * hex values:
			 */
			$txt_color = str_split( substr( $this->wm_font_color, 1, 6 ), 2 );
			$txt_color = imagecolorclosest( $src_img, hexdec( $txt_color[ 0 ] ), hexdec( $txt_color[ 1 ] ), hexdec( $txt_color[ 2 ] ) );

			// Add the text to the source image
			if ( $this->wm_use_truetype )
			{
				imagettftext( $src_img, $this->wm_font_size, 0, $x_axis, $y_axis, $txt_color, $this->wm_font_path, $this->wm_text );
			}
			else
			{
				imagestring( $src_img, $this->wm_font_size, $x_axis, $y_axis, $this->wm_text, $txt_color );
			}

			// We can preserve transparency for PNG images
			if ( $this->image_type === 3 )
			{
				imagealphablending( $src_img, FALSE );
				imagesavealpha( $src_img, TRUE );
			}

			// Output the final image
			if ( $this->dynamic_output === TRUE )
			{
				$this->imageDisplayGd( $src_img );
			}
			else
			{
				$this->imageSaveGd( $src_img );
			}

			imagedestroy( $src_img );

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Create Image - GD
		 *
		 * This simply creates an image resource handle
		 * based on the type of image being processed
		 *
		 * @param    string
		 * @param    string
		 *
		 * @return    resource
		 */
		public function imageCreateGd($path = '', $image_type = '' )
		{
			if ( $path === '' )
			{
				$path = $this->full_src_path;
			}

			if ( $image_type === '' )
			{
				$image_type = $this->image_type;
			}

			switch ( $image_type )
			{
				case 1 :
					if ( ! function_exists( 'imagecreatefromgif' ) )
					{
						$this->setError( array( 'IMAGE_UNSUPPORTED_IMAGECREATE', 'IMAGE_GIF_NOT_SUPPORTED' ) );

						return FALSE;
					}

					return imagecreatefromgif( $path );
				case 2 :
					if ( ! function_exists( 'imagecreatefromjpeg' ) )
					{
						$this->setError( array( 'IMAGE_UNSUPPORTED_IMAGECREATE', 'IMAGE_JPG_NOT_SUPPORTED' ) );

						return FALSE;
					}

					return imagecreatefromjpeg( $path );
				case 3 :
					if ( ! function_exists( 'imagecreatefrompng' ) )
					{
						$this->setError( array( 'IMAGE_UNSUPPORTED_IMAGECREATE', 'IMAGE_PNG_NOT_SUPPORTED' ) );

						return FALSE;
					}

					return imagecreatefrompng( $path );
				default:
					$this->setError( array( 'IMAGE_UNSUPPORTED_IMAGECREATE' ) );

					return FALSE;
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Write image file to disk - GD
		 *
		 * Takes an image resource as input and writes the file
		 * to the specified destination
		 *
		 * @param    resource
		 *
		 * @return    bool
		 */
		public function imageSaveGd($resource )
		{
			switch ( $this->image_type )
			{
				case 1:
					if ( ! function_exists( 'imagegif' ) )
					{
						$this->setError( array( 'IMAGE_UNSUPPORTED_IMAGECREATE', 'IMAGE_GIF_NOT_SUPPORTED' ) );

						return FALSE;
					}

					if ( ! @imagegif( $resource, $this->full_dst_path ) )
					{
						$this->setError( 'IMAGE_SAVE_FAILED' );

						return FALSE;
					}
					break;
				case 2:
					if ( ! function_exists( 'imagejpeg' ) )
					{
						$this->setError( array( 'IMAGE_UNSUPPORTED_IMAGECREATE', 'IMAGE_JPG_NOT_SUPPORTED' ) );

						return FALSE;
					}

					if ( ! @imagejpeg( $resource, $this->full_dst_path, $this->quality ) )
					{
						$this->setError( 'IMAGE_SAVE_FAILED' );

						return FALSE;
					}
					break;
				case 3:
					if ( ! function_exists( 'imagepng' ) )
					{
						$this->setError( array( 'IMAGE_UNSUPPORTED_IMAGECREATE', 'IMAGE_PNG_NOT_SUPPORTED' ) );

						return FALSE;
					}

					if ( ! @imagepng( $resource, $this->full_dst_path ) )
					{
						$this->setError( 'IMAGE_SAVE_FAILED' );

						return FALSE;
					}
					break;
				default:
					$this->setError( array( 'IMAGE_UNSUPPORTED_IMAGECREATE' ) );

					return FALSE;
					break;
			}

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Dynamically outputs an image
		 *
		 * @param    resource
		 *
		 * @return    void
		 */
		public function imageDisplayGd($resource )
		{
			header( 'Content-Disposition: filename=' . $this->source_image . ';' );
			header( 'Content-Type: ' . $this->mime_type );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', time() ) . ' GMT' );

			switch ( $this->image_type )
			{
				case 1    :
					imagegif( $resource );
					break;
				case 2    :
					imagejpeg( $resource, NULL, $this->quality );
					break;
				case 3    :
					imagepng( $resource );
					break;
				default:
					echo 'Unable to display the image';
					break;
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Re-proportion Image Width/Height
		 *
		 * When creating thumbs, the desired width/height
		 * can end up warping the image due to an incorrect
		 * ratio between the full-sized image and the thumb.
		 *
		 * This function lets us re-proportion the width/height
		 * if users choose to maintain the aspect ratio when resizing.
		 *
		 * @return    void
		 */
		public function imageReproportion()
		{
			if ( ( $this->width === 0 && $this->height === 0 ) OR $this->orig_width === 0 OR $this->orig_height === 0
				OR ( ! ctype_digit( (string) $this->width ) && ! ctype_digit( (string) $this->height ) )
				OR ! ctype_digit( (string) $this->orig_width ) OR ! ctype_digit( (string) $this->orig_height )
			)
			{
				return;
			}

			// Sanitize
			$this->width = (int) $this->width;
			$this->height = (int) $this->height;

			if ( $this->master_dim !== 'width' && $this->master_dim !== 'height' )
			{
				if ( $this->width > 0 && $this->height > 0 )
				{
					$this->master_dim = ( ( ( $this->orig_height / $this->orig_width ) - ( $this->height / $this->width ) ) < 0 )
						? 'width' : 'height';
				}
				else
				{
					$this->master_dim = ( $this->height === 0 ) ? 'width' : 'height';
				}
			}
			elseif ( ( $this->master_dim === 'width' && $this->width === 0 )
				OR ( $this->master_dim === 'height' && $this->height === 0 )
			)
			{
				return;
			}

			if ( $this->master_dim === 'width' )
			{
				$this->height = (int) ceil( $this->width * $this->orig_height / $this->orig_width );
			}
			else
			{
				$this->width = (int) ceil( $this->orig_width * $this->height / $this->orig_height );
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Get image properties
		 *
		 * A helper function that gets info about the file
		 *
		 * @param    string
		 * @param    bool
		 *
		 * @return    mixed
		 */
		public function getImageProperties($path = '', $return = FALSE )
		{
			// For now we require GD but we should
			// find a way to determine this using IM or NetPBM

			if ( $path === '' )
			{
				$path = $this->full_src_path;
			}

			if ( ! is_file( $path ) )
			{
				$this->setError( 'IMAGE_INVALID_PATH' );

				return FALSE;
			}

			$vals = getimagesize( $path );
			$types = array( 1 => 'gif', 2 => 'jpeg', 3 => 'png' );
			$mime = ( isset( $types[ $vals[ 2 ] ] ) ) ? 'image/' . $types[ $vals[ 2 ] ] : 'image/jpg';

			if ( $return === TRUE )
			{
				return array(
					'width'      => $vals[ 0 ],
					'height'     => $vals[ 1 ],
					'image_type' => $vals[ 2 ],
					'size_str'   => $vals[ 3 ],
					'mime_type'  => $mime,
				);
			}

			$this->orig_width = $vals[ 0 ];
			$this->orig_height = $vals[ 1 ];
			$this->image_type = $vals[ 2 ];
			$this->size_str = $vals[ 3 ];
			$this->mime_type = $mime;

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Size calculator
		 *
		 * This function takes a known width x height and
		 * recalculates it to a new size. Only one
		 * new variable needs to be known
		 *
		 *    $props = array(
		 *            'width'        => $width,
		 *            'height'    => $height,
		 *            'new_width'    => 40,
		 *            'new_height'    => ''
		 *        );
		 *
		 * @param    array
		 *
		 * @return    array
		 */
		public function sizeCalculator($vals )
		{
			if ( ! is_array( $vals ) )
			{
				return;
			}

			$allowed = array( 'new_width', 'new_height', 'width', 'height' );

			foreach ( $allowed as $item )
			{
				if ( empty( $vals[ $item ] ) )
				{
					$vals[ $item ] = 0;
				}
			}

			if ( $vals[ 'width' ] === 0 OR $vals[ 'height' ] === 0 )
			{
				return $vals;
			}

			if ( $vals[ 'new_width' ] === 0 )
			{
				$vals[ 'new_width' ] = ceil( $vals[ 'width' ] * $vals[ 'new_height' ] / $vals[ 'height' ] );
			}
			elseif ( $vals[ 'new_height' ] === 0 )
			{
				$vals[ 'new_height' ] = ceil( $vals[ 'new_width' ] * $vals[ 'height' ] / $vals[ 'width' ] );
			}

			return $vals;
		}

		// --------------------------------------------------------------------

		/**
		 * Explode source_image
		 *
		 * This is a helper function that extracts the extension
		 * from the source_image.  This function lets us deal with
		 * source_images with multiple periods, like: my.cool.jpg
		 * It returns an associative array with two elements:
		 * $array['ext']  = '.jpg';
		 * $array['name'] = 'my.cool';
		 *
		 * @param    array
		 *
		 * @return    array
		 */
		public function explodeName($source_image )
		{
			$ext = strrchr( $source_image, '.' );
			$name = ( $ext === FALSE ) ? $source_image : substr( $source_image, 0, -strlen( $ext ) );

			return array( 'ext' => $ext, 'name' => $name );
		}

		// --------------------------------------------------------------------

		/**
		 * Is GD Installed?
		 *
		 * @return    bool
		 */
		public function gdLoaded()
		{
			if ( ! extension_loaded( 'gd' ) )
			{
				/* As it is stated in the PHP manual, dl() is not always available
				 * and even if so - it could generate an E_WARNING message on failure
				 */
				return ( function_exists( 'dl' ) && @dl( 'gd.so' ) );
			}

			return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Get GD version
		 *
		 * @return    mixed
		 */
		public function gdVersion()
		{
			if ( function_exists( 'gd_info' ) )
			{
				$gd_version = @gd_info();

				return preg_replace( '/\D/', '', $gd_version[ 'GD Version' ] );
			}

			return FALSE;
		}

		// --------------------------------------------------------------------

		/**
		 * Set error message
		 *
		 * @param    string
		 *
		 * @return    void
		 */
		public function setError($errors )
		{
			if ( class_exists( 'O2System', FALSE ) )
			{
				\O2System::$language->load( 'image' );

				if ( is_array( $errors ) )
				{
					foreach ( $errors as $error )
					{
						$this->error_msg[] = \O2System::$language->line( $error );
					}
				}
				else
				{
					$this->error_msg[] = \O2System::$language->line( $errors );
				}
			}
			else
			{
				$error_messages = array(
					'IMAGE_SOURCE_IMAGE_REQUIRED'   => 'You must specify a source image in your preferences.',
					'IMAGE_GD_REQUIRED'             => 'The GD image library is required for this feature.',
					'IMAGE_GD_REQUIRED_FOR_PROPS'   => 'Your server must support the GD image library in order to determine the image properties.',
					'IMAGE_UNSUPPORTED_IMAGECREATE' => 'Your server does not support the GD function required to process this type of image.',
					'IMAGE_GIF_NOT_SUPPORTED'       => 'GIF images are often not supported due to licensing restrictions.  You may have to use JPG or PNG images instead.',
					'IMAGE_JPG_NOT_SUPPORTED'       => 'JPG images are not supported.',
					'IMAGE_PNG_NOT_SUPPORTED'       => 'PNG images are not supported.',
					'IMAGE_JPG_OR_PNG_REQUIRED'     => 'The image resize protocol specified in your preferences only works with JPEG or PNG image types.',
					'IMAGE_COPY_ERROR'              => 'An error was encountered while attempting to replace the file.  Please make sure your file directory is writable.',
					'IMAGE_ROTATE_UNSUPPORTED'      => 'Image rotation does not appear to be supported by your server.',
					'IMAGE_LIBRARY_PATH_INVALID'    => 'The path to your image library is not correct.  Please set the correct path in your image preferences.',
					'IMAGE_PROCESS_FAILED'          => 'Image processing failed.  Please verify that your server supports the chosen protocol and that the path to your image library is correct.',
					'IMAGE_ROTATION_ANGLE_REQUIRED' => 'An angle of rotation is required to rotate the image.',
					'IMAGE_WRITING_FAILED_GIF'      => 'GIF image.',
					'IMAGE_INVALID_PATH'            => 'The path to the image is not correct.',
					'IMAGE_COPY_FAILED'             => 'The image copy routine failed.',
					'IMAGE_MISSING_FONT'            => 'Unable to find a font to use.',
					'IMAGE_SAVE_FAILED'             => 'Unable to save the image.  Please make sure the image and file directory are writable.',
				);

				if ( is_array( $errors ) )
				{
					foreach ( $errors as $error )
					{
						$this->error_msg[] = $error_messages[ $error ];
					}
				}
				else
				{
					$this->error_msg[] = $error_messages[ $errors ];
				}
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Show error messages
		 *
		 * @param    string
		 * @param    string
		 *
		 * @return    string
		 */
		public function displayErrors()
		{
			return $this->error_msg;
		}
	}
}