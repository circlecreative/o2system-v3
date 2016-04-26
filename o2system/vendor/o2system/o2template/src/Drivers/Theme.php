<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Template\Drivers;

// ------------------------------------------------------------------------

use O2System\Glob\Interfaces\DriverInterface;
use O2System\Template\Exception;

/**
 * Template Themes Driver
 *
 * @package          Template
 * @subpackage       Library
 * @category         Driver
 * @version          1.0 Build 11.09.2012
 * @author           Steeven Andrian Salim
 * @copyright        Copyright (c) 2005 - 2014
 * @license          http://www.circle-creative.com/products/o2system/license.html
 * @link             http://www.circle-creative.com
 */
class Theme extends DriverInterface
{
	public $active   = FALSE;
	public $layout;
	public $partials = array(
		'theme'  => array(),
		'custom' => array(),
	);

	public function set( $theme )
	{
		if ( is_string( $theme ) )
		{
			if ( $this->exists( $theme ) )
			{
				$this->_load_package( $theme );
			}
		}
		elseif ( is_bool( $theme ) AND $theme === FALSE )
		{
			$this->active = FALSE;
		}

		return $this;
	}

	/**
	 * Load
	 *
	 * @param $theme
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function _load_package( $theme )
	{
		foreach ( $this->_library->_paths as $path )
		{
			if ( is_dir( $theme_path = $path . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR ) )
			{
				// Locate theme properties
				if ( ! is_file( $theme_path . 'theme.properties' ) )
				{
					throw new ThemeInvalidPropertiesException( 'TEMPLATE_THEMEUNABLETOLOCATEPROPERTIES', 12002, [ $theme_path . 'theme.properties' ] );
				}

				// Read theme properties
				$properties = json_decode( file_get_contents( $theme_path . 'theme.properties' ), TRUE );

				if ( json_last_error() !== JSON_ERROR_NONE )
				{
					throw new ThemeInvalidPropertiesException( 'TEMPLATE_THEMEUNABLETOPARSEPROPERTIES', 12003, [ $theme_path . 'theme.properties' ] );
				}
				else
				{
					$this->active = new \ArrayObject( $properties, \ArrayObject::ARRAY_AS_PROPS );
					$this->active[ 'parameter' ] = $theme;
					$this->active[ 'realpath' ] = $theme_path;
					$this->active[ 'url' ] = path_to_url( $theme_path ) . '/';

					$this->_library->add_path( $theme_path );

					if ( is_file( $theme_path . 'theme.settings' ) )
					{
						$settings = json_decode( file_get_contents( $theme_path . 'theme.settings' ), TRUE );

						if ( json_last_error() === JSON_ERROR_NONE )
						{
							$this->active[ 'settings' ] = $settings;
						}
						else
						{
							throw new ThemeInvalidSettingsException( 'TEMPLATE_THEMEUNABLETOPARSESETTINGS', 12004, [ $theme_path . 'theme.settings' ] );
						}
					}

					$extension = isset( $this->active[ 'settings' ][ 'extension' ] ) ? $this->active[ 'settings' ][ 'extension' ] : 'tpl';

					if ( is_file( $layout = $theme_path . 'theme.' . $extension ) )
					{
						$this->active[ 'layout' ] = $layout;

						// Read Partials
						if ( is_dir( $theme_path . 'partials' . DIRECTORY_SEPARATOR ) )
						{
							$files = scandir( $theme_path . 'partials' . DIRECTORY_SEPARATOR );
							$this->active[ 'partials' ] = array();

							foreach ( $files as $file )
							{
								if ( is_file( $theme_path . 'partials' . DIRECTORY_SEPARATOR . $file ) )
								{
									$this->active[ 'partials' ][ pathinfo( $file, PATHINFO_FILENAME ) ] = $theme_path . 'partials' . DIRECTORY_SEPARATOR . $file;
								}
							}
						}
					}
					else
					{
						$this->setError( 'Unable to read theme layout: ' . $theme_path . 'theme.tpl' );
					}
				}

				return TRUE;
				break;
			}
		}

		throw new ThemeException( 'TEMPLATE_THEMEUNABLETOLOCATEPACKAGE', 12001, [ $theme_path ] );
	}

	/**
	 * Theme Checker
	 *
	 * @params  string  $theme  Theme Name
	 *
	 * @access  public
	 * @return  bool
	 */
	public function exists( $theme )
	{
		foreach ( $this->_library->_paths as $path )
		{
			if ( is_dir( $path . 'themes' . DIRECTORY_SEPARATOR . $theme ) )
			{
				return TRUE;
				break;
			}
		}

		return FALSE;
	}

	public function layout_exists( $layout )
	{
		if ( isset( $this->active->realpath ) )
		{
			if ( is_dir( $this->active->realpath . 'layouts' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR ) )
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Set Layout
	 *
	 * @params  string  $filename   Layout Name
	 * @params  string  $extension  Layout Extension
	 *
	 * @access  public
	 * @return  bool
	 */
	public function set_layout( $layout = NULL )
	{
		if ( isset( $layout ) )
		{
			if ( $this->layout_exists( $layout ) )
			{
				$layout_path = $this->active->realpath . 'layouts' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR;

				$extension = isset( $this->active[ 'settings' ][ 'extension' ] ) ? $this->active[ 'settings' ][ 'extension' ] : 'tpl';

				if ( is_file( $layout_path . $layout . '.' . $extension ) )
				{
					$this->active[ 'layout' ] = $layout_path . $layout . '.' . $extension;

					$this->_library->add_path( $layout_path );

					// Load Layout Settings
					if ( is_file( $layout_path . $layout . '.settings' ) )
					{
						$settings = json_decode( file_get_contents( $layout_path . $layout . '.settings' ), TRUE );

						if ( json_last_error() === JSON_ERROR_NONE )
						{
							$this->active[ 'settings' ] = $settings;
						}
					}
					else
					{
						throw new ThemeInvalidSettingsException( 'TEMPLATE_THEMEUNABLETOPARSESETTINGS', 12004, [ $layout_path . $layout . '.settings' ] );
					}
				}

				// Load Layout Partials
				if ( is_dir( $layout_path . 'partials' . DIRECTORY_SEPARATOR ) )
				{
					$files = scandir( $layout_path . 'partials' . DIRECTORY_SEPARATOR );
					$this->active[ 'partials' ] = array();

					foreach ( $files as $file )
					{
						if ( is_file( $layout_path . 'partials' . DIRECTORY_SEPARATOR . $file ) )
						{
							$this->active[ 'partials' ][ pathinfo( $file, PATHINFO_FILENAME ) ] = $layout_path . 'partials' . DIRECTORY_SEPARATOR . $file;
						}
					}
				}

				return TRUE;
			}
		}

		return $this->_library->setError( 'Unable to load requested layout: ' . $layout );
	}
}

class ThemeException extends Exception
{
	public function __construct( $message, $code, $args = array() )
	{
		$this->_args = $args;
		parent::__construct( $message, $code );
	}
}

class ThemeInvalidPropertiesException extends Exception
{
	public function __construct( $message, $code, $args = array() )
	{
		$this->_args = $args;
		parent::__construct( $message, $code );
	}
}

class ThemeInvalidSettingsException extends Exception
{
	public function __construct( $message, $code, $args = array() )
	{
		$this->_args = $args;
		parent::__construct( $message, $code );
	}
}