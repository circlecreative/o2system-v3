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

use O2System\Bootstrap\Factory\Tag;
use O2System\Glob\ArrayObject;
use O2System\Glob\Interfaces\DriverInterface;

use MatthiasMullie\Minify;
use O2System\Cache;

/**
 * Template Assets
 *
 * Manage Template Assets Library
 *
 * @package       o2system
 * @subpackage    libraries
 * @category      Library Driver
 * @author        Steeven Andrian Salim
 * @link          http://o2system.center/framework/user-guide/library/template/drivers/assets.html
 */
final class Assets extends DriverInterface
{
	private $storage;

	protected $_collections = array(
		'fonts'   => array(),
		'icons'   => array(),
		'core'    => array(),
		'plugins' => array(),
		'module'  => array(),
		'theme'   => array(),
		'custom'  => array(),
		'inline'  => array(),
	);

	// ------------------------------------------------------------------------

	public function is_collected( $asset, $group )
	{

	}

	public function add_assets( array $assets, $group = 'custom' )
	{
		if ( isset( $this->_collections[ $group ] ) )
		{
			if ( is_string( key( $assets ) ) )
			{
				$this->_collections[ $group ] = array_merge( $this->_collections[ $group ], $assets );
			}
			elseif ( is_numeric( key( $assets ) ) )
			{
				if ( empty( $this->_collections[ $group ][ 'css' ] ) )
				{
					$this->_collections[ $group ][ 'css' ] = $assets;
				}
				else
				{
					$this->_collections[ $group ][ 'css' ] = array_unique( array_merge( $this->_collections[ $group ][ 'css' ], $assets ) );
				}

				if ( empty( $this->_collections[ $group ][ 'js' ] ) )
				{
					$this->_collections[ $group ][ 'js' ] = $assets;
				}
				else
				{
					$this->_collections[ $group ][ 'js' ] = array_unique( array_merge( $this->_collections[ $group ][ 'js' ], $assets ) );
				}
			}
		}

		return $this;
	}

	public function add_asset( $asset, $group = 'custom' )
	{
		if ( isset( $this->_collections[ $group ] ) )
		{
			if ( ! in_array( $asset, $this->_collections[ $group ] ) )
			{
				$ext = pathinfo( $asset, PATHINFO_EXTENSION );

				if ( empty( $ext ) )
				{
					$this->_collections[ $group ][ 'css' ][ $asset ] = $asset;
					$this->_collections[ $group ][ 'js' ][ $asset ] = $asset;
				}
				else
				{
					$this->_collections[ $group ][ $ext ][ $asset ] = $asset;
				}
			}
		}

		return $this;
	}

	public function add_css( $css, $attr = NULL, $group = 'custom' )
	{
		if ( is_string( $attr ) )
		{
			$group = $attr;
			$attr = array(
				'media' => 'all',
				'rel'   => 'stylesheet',
				'type'  => 'text/css',
			);
		}
		elseif ( is_array( $attr ) )
		{
			$attr = array_merge( array(
				                     'media' => 'all',
				                     'rel'   => 'stylesheet',
				                     'type'  => 'text/css',
			                     ), $attr );
		}

		if ( isset( $this->_collections[ $group ][ 'css' ] ) )
		{
			if ( in_array( $css, $this->_collections[ $group ][ 'css' ] ) )
			{
				return $this;
			}
		}

		if ( is_file( $css ) )
		{
			$attr[ 'realpath' ] = $css;
			$this->_collections[ $group ][ 'css' ][ pathinfo( $css, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
		}
		elseif ( strpos( $css, '://' ) !== FALSE )
		{
			$attr[ 'src' ] = $css;
			$this->_collections[ $group ][ 'css' ][ pathinfo( $css, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
		}
		elseif ( $group === 'inline' )
		{
			$this->_collections[ 'inline' ][ 'css' ][ md5( $css ) ] = $css;
		}
		else
		{
			$this->_collections[ $group ][ 'css' ][ $css ] = $css;
		}

		return $this;
	}

	public function add_js( $js, $attr = array(), $group = 'custom' )
	{
		if ( is_string( $attr ) )
		{
			$group = $attr;
			$attr = array(
				'type'  => 'text/javascript',
				'defer' => 'defer',
			);
		}
		elseif ( is_array( $attr ) )
		{
			$attr = array_merge( $attr, array(
				'type'  => 'text/javascript',
				'defer' => 'defer',
			) );
		}

		if ( isset( $this->_collections[ $group ][ 'js' ] ) )
		{
			if ( in_array( $js, $this->_collections[ $group ][ 'js' ] ) )
			{
				return $this;
			}
		}

		if ( is_file( $js ) )
		{
			$attr[ 'realpath' ] = $js;
			$this->_collections[ $group ][ 'js' ][ pathinfo( $js, PATHINFO_FILENAME ) ] = new Tag( 'script', $attr );
		}
		elseif ( strpos( $js, '://' ) !== FALSE )
		{
			$attr[ 'src' ] = $js;
			$this->_collections[ $group ][ 'js' ][ pathinfo( $js, PATHINFO_FILENAME ) ] = new Tag( 'script', $attr );
		}
		elseif ( $group === 'inline' )
		{
			$this->_collections[ 'inline' ][ 'js' ][ md5( $js ) ] = $js;
		}
		else
		{
			$this->_collections[ $group ][ 'js' ][ $js ] = $js;
		}

		return $this;
	}

	public function add_fonts( $fonts )
	{
		$this->add_assets( $fonts );
	}

	public function add_font( $font, $attr = array() )
	{
		$this->add_css( $font, $attr, 'fonts' );
	}

	public function add_icons( $icons )
	{
		foreach ( $icons as $icon )
		{
			$this->add_icon( $icon );
		}
	}

	public function add_icon( $icon, $attr = array() )
	{
		$attr = array_merge( array(
			                     'rel'  => 'shortcut-icon',
			                     'type' => 'image/x-icon',
		                     ), $attr );

		if ( isset( $this->_collections[ 'icons' ][ 'css' ] ) )
		{
			if ( ! in_array( $icon, $this->_collections[ 'icons' ][ 'css' ] ) )
			{
				if ( is_file( $icon ) )
				{
					$attr[ 'realpath' ] = $icon;

					$this->_collections[ 'icons' ][ pathinfo( $icon, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
				}
				elseif ( strpos( $icon, '://' ) !== FALSE )
				{
					$attr[ 'src' ] = $icon;

					$this->_collections[ 'icons' ][ pathinfo( $icon, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
				}
				else
				{
					$this->_collections[ 'icons' ][] = $icon;
				}
			}
		}

		return $this;
	}

	public function add_packages( array $packages, $group = 'plugins' )
	{
		foreach ( $packages as $package )
		{
			$this->add_package( $package, $group );
		}
	}

	public function add_package( $package, $group = 'plugins' )
	{
		$this->_collections[ $group ]['packages'][] = $package;
	}

	private function __inlineCss( $collection )
	{
		$this->storage[ 'css' ][ 'inline' ] = new Tag( 'style', implode( PHP_EOL, $collection ), array(
			'media' => 'all',
			'rel'   => 'stylesheet',
			'type'  => 'text/css',
		) );
	}

	private function __inlineJs( $collection )
	{
		$this->storage[ 'js' ][ 'inline' ] = new Tag( 'script', implode( PHP_EOL, $collection ), array(
			'type'  => 'text/javascript',
		) );
	}

	private function __fetchCss( $collection )
	{
		foreach ( $collection as $key => $css )
		{
			if ( $css instanceof Tag )
			{
				$this->storage[ 'css' ][ $key ] = $css;
			}
			elseif ( strpos( $css, '://' ) !== FALSE )
			{
				$key = is_numeric( $key ) ? pathinfo( $css, PATHINFO_FILENAME ) : $key;

				$this->storage[ 'css' ][ $key ] = new Tag( 'link', array(
					'media' => 'all',
					'rel'   => 'stylesheet',
					'type'  => 'text/css',
					'href'  => $css,
				) );
			}
			else
			{
				$filepath = $this->__loadCss( $css );

				if ( $filepath )
				{
					$key = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;

					$this->storage[ 'css' ][ $key ] = new Tag( 'link', array(
						'media'    => 'all',
						'rel'      => 'stylesheet',
						'type'     => 'text/css',
						'realpath' => $filepath,
						'href'     => path_to_url( $filepath ),
					) );
				}
			}
		}
	}

	private function __fetchJs( $collection )
	{
		foreach ( $collection as $key => $js )
		{
			if ( $js instanceof Tag )
			{
				$this->storage[ 'js' ][ $key ] = $js;
			}
			elseif ( strpos( $js, '://' ) !== FALSE )
			{
				$key = is_numeric( $key ) ? pathinfo( $js, PATHINFO_FILENAME ) : $key;

				$this->storage[ 'js' ][ $key ] = new Tag( 'script', array(
					'type'  => 'text/javascript',
					'defer' => 'defer',
					'src'   => $js,
				) );
			}
			else
			{
				$filepath = $this->__loadJs( $js );

				if ( $filepath )
				{
					$key = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;

					$this->storage[ 'js' ][ $key ] = new Tag( 'script', array(
						'type'     => 'text/javascript',
						'defer'    => 'defer',
						'realpath' => $filepath,
						'src'      => path_to_url( $filepath ),
					) );
				}
			}
		}
	}

	private function __fetchFonts( $collection )
	{
		foreach ( $collection as $key => $font )
		{
			if ( $font instanceof Tag )
			{
				$this->storage[ 'css' ][ $key ] = $font;
			}
			elseif ( strpos( $font, '://' ) !== FALSE )
			{
				$key = is_numeric( $key ) ? pathinfo( $font, PATHINFO_FILENAME ) : $key;

				$this->storage[ 'css' ][ $key ] = new Tag( 'link', array(
					'media' => 'all',
					'rel'   => 'stylesheet',
					'type'  => 'text/css',
					'href'  => $font,
				) );
			}
			else
			{
				$filepath = $this->__loadFont( $font );

				if ( $filepath )
				{
					$key = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;

					$this->storage[ 'css' ][ $key ] = new Tag( 'link', array(
						'media'    => 'all',
						'rel'      => 'stylesheet',
						'type'     => 'text/css',
						'realpath' => $filepath,
						'href'     => path_to_url( $filepath ),
					) );
				}
			}
		}
	}

	private function __fetchIcons( $collection )
	{
		foreach ( $collection as $key => $icon )
		{
			if ( is_array( $icon ) )
			{
				if ( isset( $icon[ 'src' ] ) )
				{
					$filepath = $this->__loadIcon( $icon[ 'src' ] );

					if ( $filepath )
					{
						$icon[ 'src' ] = path_to_url( $filepath );

						$key = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;
						$this->storage[ 'icons' ][ $key ] = new Tag( 'link', $icon );
					}
				}
			}
			elseif ( is_string( $icon ) )
			{
				$filepath = $this->__loadIcon( $icon );
			}
		}
	}


	private function __fetchPackages( $collection )
	{
		foreach ( $collection as $key => $package )
		{
			if ( is_string( $package ) )
			{
				$this->__loadPackage( $package );
			}
			else
			{
				$package_path = $this->__loadPackage( $package[ 'package' ] );

				// Load Package Theme
				if ( array_key_exists( 'theme', $package ) )
				{
					$this->__loadPackageTheme( $package[ 'theme' ], $package_path );
				}

				// Load Package Plugin
				if ( array_key_exists( 'plugins', $package ) )
				{
					$this->__loadPackagePlugins( $package[ 'plugins' ], $package_path );
				}
			}
		}
	}

	private function __loadCss( $css, $css_path = NULL )
	{
		if ( is_null( $css_path ) )
		{
			foreach ( $this->_library->_paths as $path )
			{
				if ( is_dir( $css_path = $path . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR ) )
				{
					if ( is_file( $css_path . $css . '.css' ) )
					{
						return $css_path . $css . '.css';
						break;
					}
				}
			}
		}

		if ( is_file( $filepath = $css_path . $css . '.css' ) )
		{
			$this->storage[ 'css' ][ $css ] = new Tag( 'link', array(
				'media'    => 'all',
				'rel'      => 'stylesheet',
				'type'     => 'text/css',
				'realpath' => $filepath,
				'href'     => path_to_url( $filepath ),
			) );

			return TRUE;
		}

		return FALSE;
	}

	private function __loadJs( $js, $js_path = NULL )
	{
		if ( is_null( $js_path ) )
		{
			foreach ( $this->_library->_paths as $path )
			{
				if ( is_dir( $js_path = $path . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR ) )
				{
					if ( is_file( $js_path . $js . '.js' ) )
					{
						return $js_path . $js . '.js';
						break;
					}
				}
			}
		}

		if ( is_file( $filepath = $js_path . $js . '.js' ) )
		{
			$this->storage[ 'js' ][ $js ] = new Tag( 'script', array(
				'type'     => 'text/javascript',
				'defer'    => 'defer',
				'realpath' => $filepath,
				'src'      => path_to_url( $filepath ),
			) );
		}

		return FALSE;
	}

	private function __loadFont( $font, $font_path = NULL )
	{
		if ( is_null( $font_path ) )
		{
			foreach ( $this->_library->_paths as $path )
			{
				if ( is_dir( $font_path = $path . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $font . DIRECTORY_SEPARATOR ) )
				{
					if ( is_file( $font_path . $font . '.css' ) )
					{
						return $font_path . $font . '.css';
						break;
					}
				}
			}
		}

		if ( is_file( $filepath = $font_path . $font . DIRECTORY_SEPARATOR . $font . '.css' ) )
		{
			return $filepath;
		}

		return FALSE;
	}

	private function __loadIcon( $icon, $icon_path = NULL )
	{
		if ( is_null( $icon_path ) )
		{
			foreach ( $this->_library->_paths as $path )
			{
				if ( is_dir( $icon_path = $path . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR ) )
				{
					if ( is_file( $icon_path . $icon ) )
					{
						return $icon_path . $icon;
						break;
					}
				}
			}
		}

		if ( is_file( $filepath = $icon_path . $icon ) )
		{
			return $filepath;
		}

		return FALSE;
	}

	private function __loadPackage( $package, $package_path = NULL )
	{
		if ( is_null( $package_path ) )
		{
			foreach ( $this->_library->_paths as $path )
			{
				if ( is_dir( $package_path = $path . 'assets' . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR ) )
				{
					$this->__loadCSS( $package, $package_path );
					$this->__loadJS( $package, $package_path );

					return $package_path;

					break;
				}
			}
		}
		else
		{
			if ( is_dir( $package_path ) )
			{
				$this->__loadCSS( $package, $package_path );
				$this->__loadJS( $package, $package_path );

				return $package_path;
			}
		}

		return FALSE;
	}

	private function __loadPackageTheme( $theme, $package_path )
	{
		if ( is_dir( $theme_path = $package_path . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR ) )
		{
			return $this->__loadCSS( $theme, $theme_path );
		}

		return FALSE;
	}

	private function __loadPackagePlugins( $plugins, $package_path )
	{
		if ( is_array( $plugins ) )
		{
			foreach ( $plugins as $plugin )
			{
				$this->__loadPackagePlugins( $plugin, $package_path );
			}
		}
		elseif ( is_string( $plugins ) )
		{
			if ( is_dir( $plugin_path = $package_path . 'plugins' . DIRECTORY_SEPARATOR . $plugins . DIRECTORY_SEPARATOR ) )
			{
				$this->__loadCSS( $plugins, $plugin_path );

				return $this->__loadJS( $plugins, $plugin_path );
			}
			elseif ( is_file( $plugin_path = $package_path . $plugins . '.js' ) )
			{
				return $this->__loadJS( $plugins, $plugin_path );
			}

			return FALSE;
		}
	}


	public function __get( $property )
	{
		if ( empty( $this->storage ) )
		{
			$this->render();
		}

		$properties_map = array(
			'header' => 'css',
			'footer' => 'js',
		);

		if ( isset( $properties_map[ $property ] ) )
		{
			return $this->storage[ $properties_map[ $property ] ];
		}

		return $this->storage[ $property ];
	}

	/**
	 * Render Assets
	 *
	 * @access  public
	 * @final   This can't be overwritten
	 *
	 * @return string
	 */
	public function render()
	{
		$this->storage = new \O2System\Template\Collections\Assets();

		foreach ( $this->_collections as $group => $collection )
		{
			if ( $group === 'inline' )
			{
				foreach ( $collection as $type => $asset )
				{
					if ( ! $this->storage->offsetExists( $type ) )
					{
						$this->storage[ $type ] = new \O2System\Template\Collections\Assets();
					}

					$this->{'__inline' . ucfirst( $type )}( $asset );
				}
			}
			else
			{
				foreach ( $collection as $type => $asset )
				{
					if ( ! $this->storage->offsetExists( $type ) )
					{
						$this->storage[ $type ] = new \O2System\Template\Collections\Assets();
					}

					$this->{'__fetch' . ucfirst( $type )}( $asset );
				}
			}
		}

		return $this->storage->render();
	}

	public function __toString()
	{
		return $this->render();
	}
}