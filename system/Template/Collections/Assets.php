<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/14/2016
 * Time: 11:37 PM
 */

namespace O2System\Template\Collections;

use MatthiasMullie\Minify;
use O2System\Bootstrap\Components\Tag;
use O2System\Core\Library\Collections;
use O2System\Template;

class Assets extends Collections
{
	/**
	 * Library Instance
	 *
	 * @access  protected
	 * @type    Template
	 */
	protected $library;

	protected $collections = [
		'fonts'   => [ ],
		'icons'   => [ ],
		'core'    => [ ],
		'plugins' => [ ],
		'module'  => [ ],
		'theme'   => [ ],
		'custom'  => [ ],
		'inline'  => [ ],
	];

	// ------------------------------------------------------------------------

	public function addAssets( array $assets, $group = 'custom' )
	{
		if ( isset( $this->collections[ $group ] ) )
		{
			if ( is_string( key( $assets ) ) )
			{
				$this->collections[ $group ] = array_merge( $this->collections[ $group ], $assets );
			}
			elseif ( is_numeric( key( $assets ) ) )
			{
				if ( empty( $this->collections[ $group ][ 'css' ] ) )
				{
					$this->collections[ $group ][ 'css' ] = $assets;
				}
				else
				{
					$this->collections[ $group ][ 'css' ] = array_unique( array_merge( $this->collections[ $group ][ 'css' ], $assets ) );
				}

				if ( empty( $this->collections[ $group ][ 'js' ] ) )
				{
					$this->collections[ $group ][ 'js' ] = $assets;
				}
				else
				{
					$this->collections[ $group ][ 'js' ] = array_unique( array_merge( $this->collections[ $group ][ 'js' ], $assets ) );
				}
			}
		}

		return $this;
	}

	public function addAsset( $asset, $group = 'custom' )
	{
		if ( isset( $this->collections[ $group ] ) )
		{
			if ( ! in_array( $asset, $this->collections[ $group ] ) )
			{
				$ext = pathinfo( $asset, PATHINFO_EXTENSION );

				if ( empty( $ext ) )
				{
					$this->collections[ $group ][ 'css' ][ $asset ] = $asset;
					$this->collections[ $group ][ 'js' ][ $asset ]  = $asset;
				}
				else
				{
					$this->collections[ $group ][ $ext ][ $asset ] = $asset;
				}
			}
		}

		return $this;
	}

	public function addCss( $css, $attr = NULL, $group = 'custom' )
	{
		if ( is_string( $attr ) )
		{
			$group = $attr;
			$attr  = [
				'media' => 'all',
				'rel'   => 'stylesheet',
				'type'  => 'text/css',
			];
		}
		elseif ( is_array( $attr ) )
		{
			$attr = array_merge(
				[
					'media' => 'all',
					'rel'   => 'stylesheet',
					'type'  => 'text/css',
				], $attr );
		}

		if ( isset( $this->collections[ $group ][ 'css' ] ) )
		{
			if ( in_array( $css, $this->collections[ $group ][ 'css' ] ) )
			{
				return $this;
			}
		}

		if ( is_file( $css ) )
		{
			$attr[ 'realpath' ]                                                          = $css;
			$this->collections[ $group ][ 'css' ][ pathinfo( $css, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
		}
		elseif ( strpos( $css, '://' ) !== FALSE )
		{
			$attr[ 'src' ]                                                               = $css;
			$this->collections[ $group ][ 'css' ][ pathinfo( $css, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
		}
		elseif ( $group === 'inline' )
		{
			$this->collections[ 'inline' ][ 'css' ][ md5( $css ) ] = $css;
		}
		else
		{
			$this->collections[ $group ][ 'css' ][ $css ] = $css;
		}

		return $this;
	}

	public function addJs( $js, $attr = [ ], $group = 'custom' )
	{
		if ( is_string( $attr ) )
		{
			$group = $attr;
			$attr  = [
				'type'  => 'text/javascript',
				'defer' => 'defer',
			];
		}
		elseif ( is_array( $attr ) )
		{
			$attr = array_merge(
				$attr, [
				'type'  => 'text/javascript',
				'defer' => 'defer',
			] );
		}

		if ( isset( $this->collections[ $group ][ 'js' ] ) )
		{
			if ( in_array( $js, $this->collections[ $group ][ 'js' ] ) )
			{
				return $this;
			}
		}

		if ( is_file( $js ) )
		{
			$attr[ 'realpath' ]                                                        = $js;
			$this->collections[ $group ][ 'js' ][ pathinfo( $js, PATHINFO_FILENAME ) ] = new Tag( 'script', $attr );
		}
		elseif ( strpos( $js, '://' ) !== FALSE )
		{
			$attr[ 'src' ]                                                             = $js;
			$this->collections[ $group ][ 'js' ][ pathinfo( $js, PATHINFO_FILENAME ) ] = new Tag( 'script', $attr );
		}
		elseif ( $group === 'inline' )
		{
			$this->collections[ 'inline' ][ 'js' ][ md5( $js ) ] = $js;
		}
		else
		{
			$this->collections[ $group ][ 'js' ][ $js ] = $js;
		}

		return $this;
	}

	public function addFonts( $fonts )
	{
		$this->addAssets( $fonts, 'fonts' );
	}

	public function addFont( $font, $attr = [ ] )
	{
		$this->addCss( $font, $attr, 'fonts' );
	}

	public function addIcons( $icons )
	{
		foreach ( $icons as $icon )
		{
			$this->addIcon( $icon );
		}
	}

	public function addIcon( $icon, $attr = [ ] )
	{
		$attr = array_merge(
			[
				'rel'  => 'shortcut-icon',
				'type' => 'image/x-icon',
			], $attr );

		if ( isset( $this->collections[ 'icons' ][ 'css' ] ) )
		{
			if ( ! in_array( $icon, $this->collections[ 'icons' ][ 'css' ] ) )
			{
				if ( is_file( $icon ) )
				{
					$attr[ 'realpath' ] = $icon;

					$this->collections[ 'icons' ][ pathinfo( $icon, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
				}
				elseif ( strpos( $icon, '://' ) !== FALSE )
				{
					$attr[ 'src' ] = $icon;

					$this->collections[ 'icons' ][ pathinfo( $icon, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
				}
				else
				{
					$this->collections[ 'icons' ][] = $icon;
				}
			}
		}

		return $this;
	}

	public function addPackages( array $packages, $group = 'plugins' )
	{
		foreach ( $packages as $package )
		{
			$this->addPackage( $package, $group );
		}
	}

	public function addPackage( $package, $group = 'plugins' )
	{
		$this->collections[ $group ][ 'packages' ][] = $package;
	}

	private function __inlineCss( $collection )
	{
		$this->collections[ 'css' ][ 'inline' ] = new Tag(
			'style', implode( PHP_EOL, $collection ), [
			'media' => 'all',
			'rel'   => 'stylesheet',
			'type'  => 'text/css',
		] );
	}

	private function __inlineJs( $collection )
	{
		$this->collections[ 'js' ][ 'inline' ] = new Tag(
			'script', implode( PHP_EOL, $collection ), [
			'type' => 'text/javascript',
		] );
	}

	private function __fetchCss( $collection )
	{
		foreach ( $collection as $key => $css )
		{
			if ( $css instanceof Tag )
			{
				$this->collections[ 'css' ][ $key ] = $css;
			}
			elseif ( strpos( $css, '://' ) !== FALSE )
			{
				$key = is_numeric( $key ) ? pathinfo( $css, PATHINFO_FILENAME ) : $key;

				$this->collections[ 'css' ][ $key ] = new Tag(
					'link', [
					'media' => 'all',
					'rel'   => 'stylesheet',
					'type'  => 'text/css',
					'href'  => $css,
				] );
			}
			else
			{
				$filepath = $this->__loadCss( $css );

				if ( $filepath )
				{
					$key = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;

					$this->collections[ 'css' ][ $key ] = new Tag(
						'link', [
						'media'    => 'all',
						'rel'      => 'stylesheet',
						'type'     => 'text/css',
						'realpath' => $filepath,
						'href'     => path_to_url( $filepath ),
					] );
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
				$this->collections[ 'js' ][ $key ] = $js;
			}
			elseif ( strpos( $js, '://' ) !== FALSE )
			{
				$key = is_numeric( $key ) ? pathinfo( $js, PATHINFO_FILENAME ) : $key;

				$this->collections[ 'js' ][ $key ] = new Tag(
					'script', [
					'type'  => 'text/javascript',
					'defer' => 'defer',
					'src'   => $js,
				] );
			}
			else
			{
				$filepath = $this->__loadJs( $js );

				if ( $filepath )
				{
					$key = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;

					$this->collections[ 'js' ][ $key ] = new Tag(
						'script', [
						'type'     => 'text/javascript',
						'defer'    => 'defer',
						'realpath' => $filepath,
						'src'      => path_to_url( $filepath ),
					] );
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
				$this->collections[ 'css' ][ $key ] = $font;
			}
			elseif ( strpos( $font, '://' ) !== FALSE )
			{
				$key = is_numeric( $key ) ? pathinfo( $font, PATHINFO_FILENAME ) : $key;

				$this->collections[ 'css' ][ $key ] = new Tag(
					'link', [
					'media' => 'all',
					'rel'   => 'stylesheet',
					'type'  => 'text/css',
					'href'  => $font,
				] );
			}
			else
			{
				$filepath = $this->__loadFont( $font );

				if ( $filepath )
				{
					$key = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;

					$this->collections[ 'css' ][ $key ] = new Tag(
						'link', [
						'media'    => 'all',
						'rel'      => 'stylesheet',
						'type'     => 'text/css',
						'realpath' => $filepath,
						'href'     => path_to_url( $filepath ),
					] );
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

						$key                                  = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;
						$this->collections[ 'icons' ][ $key ] = new Tag( 'link', $icon );
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
			foreach ( $this->library->paths as $path )
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
			$this->collections[ 'css' ][ $css ] = new Tag(
				'link', [
				'media'    => 'all',
				'rel'      => 'stylesheet',
				'type'     => 'text/css',
				'realpath' => $filepath,
				'href'     => path_to_url( $filepath ),
			] );

			return TRUE;
		}

		return FALSE;
	}

	private function __loadJs( $js, $js_path = NULL )
	{
		if ( is_null( $js_path ) )
		{
			foreach ( $this->library->paths as $path )
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
			$this->collections[ 'js' ][ $js ] = new Tag(
				'script', [
				'type'     => 'text/javascript',
				'defer'    => 'defer',
				'realpath' => $filepath,
				'src'      => path_to_url( $filepath ),
			] );
		}

		return FALSE;
	}

	private function __loadFont( $font, $font_path = NULL )
	{
		if ( is_null( $font_path ) )
		{
			foreach ( $this->library->paths as $path )
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
			foreach ( $this->library->paths as $path )
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
			foreach ( $this->library->paths as $path )
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
		if ( empty( $this->collections ) )
		{
			$this->render();
		}

		$properties_map = [
			'header' => 'css',
			'footer' => 'js',
		];

		if ( isset( $properties_map[ $property ] ) )
		{
			return $this->collections[ $properties_map[ $property ] ];
		}

		return $this->collections[ $property ];
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
		$this->collections = new Template\Collections\Assets\Compiler();
		foreach ( $this->collections as $group => $collection )
		{
			if ( $group === 'inline' )
			{
				foreach ( $collection as $type => $asset )
				{
					if ( ! $this->collections->offsetExists( $type ) )
					{
						$this->collections[ $type ] = new Template\Collections\Assets\Compiler();
					}

					$this->{'__inline' . ucfirst( $type )}( $asset );
				}
			}
			else
			{
				foreach ( $collection as $type => $asset )
				{
					if ( ! $this->collections->offsetExists( $type ) )
					{
						$this->collections[ $type ] = new Template\Collections\Assets\Compiler();
					}

					$this->{'__fetch' . ucfirst( $type )}( $asset );
				}
			}
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}
}