<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 4:07 PM
 */

namespace O2System\Core\View\Collections;


use O2System\Core\Collectors\Config;
use O2System\Core\HTML\Tag;
use O2System\Core\SPL\ArrayObject;

class Assets
{
	use Config;

	protected $storage = [
		'core'         => [ ],
		'plugins'      => [ ],
		'fonts'        => [ ],
		'favicons'     => [ ],
		'theme'        => [ ],
		'applications' => [ ],
		'module'       => [ ],
		'custom'       => [ ],
		'inline'       => [ ],
	];

	/**
	 * Assets Compiles
	 *
	 * @type ArrayObject
	 */
	protected $compiles;

	public function initialize()
	{
		if ( ! empty( $this->config[ 'autoload' ] ) )
		{
			$this->add( $this->config[ 'autoload' ], 'core' );
		}
	}

	public function add( $assets, $group = 'applications' )
	{
		if ( is_array( $assets ) )
		{
			foreach ( $assets as $key => $asset )
			{
				if ( is_string( $key ) )
				{
					if ( array_key_exists( $key, $this->storage ) )
					{
						if ( $key === 'fonts' )
						{
							$this->addFont( $asset );
						}
						elseif ( $key === 'favicons' )
						{
							$this->addFavicon( $asset );
						}
						else
						{
							$this->add( $asset, $key );
						}
					}
					elseif ( $key === 'packages' )
					{
						$this->addPackage( $asset, $group );
					}
					else
					{
						$this->add( $asset, $group );
					}
				}
				else
				{
					$this->add( $asset, $group );
				}
			}
		}
		elseif ( array_key_exists( $group, $this->storage ) )
		{
			if ( ! in_array( $assets, $this->storage[ $group ] ) )
			{
				foreach ( \O2System::$load->getAssetsDirectories() as $path )
				{
					if ( strpos( $assets, '://' ) === FALSE )
					{
						if ( is_file( $assets ) )
						{
							$pathinfo = pathinfo( $assets );

							$this->storage[ $group ][ $pathinfo[ 'extension' ] ][ $pathinfo[ 'filename' ] ] = $assets;
						}
						else
						{
							foreach ( [ 'css', 'js' ] as $extension )
							{
								if ( is_file( $filepath = $path . $extension . DIRECTORY_SEPARATOR . $assets . '.' . $extension ) )
								{
									$this->storage[ $group ][ $extension ][ $assets ] = $filepath;
								}
							}
						}
					}
					elseif ( strpos( $assets, '://' ) !== FALSE )
					{
						$pathinfo = pathinfo( $assets );

						$this->storage[ $group ][ $pathinfo[ 'extension' ] ][ $pathinfo[ 'filename' ] ] = $assets;
					}
				}
			}
		}

		return $this;
	}

	public function addFont( $fonts )
	{
		if ( is_array( $fonts ) )
		{
			foreach ( $fonts as $font )
			{
				$this->addFont( $font );
			}
		}
		else
		{
			foreach ( \O2System::$load->getAssetsDirectories() as $path )
			{
				if ( is_file( $filepath = $path . 'fonts' . DIRECTORY_SEPARATOR . $fonts . DIRECTORY_SEPARATOR . $fonts . '.css' ) )
				{
					$this->storage[ 'fonts' ][ $fonts ] = $filepath;
				}
			}
		}

		return $this;
	}

	public function addFavicon( $favicon, array $attr = [ ] )
	{
		$attr = array_merge(
			[
				'rel'  => 'shortcut-icon',
				'type' => 'image/x-icon',
			], $attr );

		if ( isset( $this->storage[ 'icons' ][ 'css' ] ) )
		{
			if ( ! in_array( $favicon, $this->storage[ 'icons' ][ 'css' ] ) )
			{
				if ( is_file( $favicon ) )
				{
					$attr[ 'realpath' ] = $favicon;

					$this->storage[ 'icons' ][ pathinfo( $favicon, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
				}
				elseif ( strpos( $favicon, '://' ) !== FALSE )
				{
					$attr[ 'src' ] = $favicon;

					$this->storage[ 'icons' ][ pathinfo( $favicon, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
				}
				else
				{
					$this->storage[ 'icons' ][] = $favicon;
				}
			}
		}

		return $this;
	}

	public function addPackage( $packages, $group = 'plugins' )
	{
		if ( is_array( $packages ) )
		{
			foreach ( $packages as $package )
			{
				if ( is_array( $package ) )
				{
					$this->_addPackagePack( $package, $group );
				}
				else
				{
					$this->addPackage( $package, $group );
				}
			}
		}
		else
		{
			foreach ( \O2System::$load->getAssetsDirectories() as $path )
			{
				foreach ( [ 'css', 'js' ] as $extension )
				{
					if ( is_file( $filepath = $path . 'packages' . DIRECTORY_SEPARATOR . $packages . '.' . $extension ) )
					{
						$this->storage[ $group ][ $extension ][ $packages ] = $filepath;
					}
				}
			}
		}

		return $this;
	}

	protected function _addPackagePack( $pack, $group = 'plugins' )
	{
		extract( $pack );

		if ( isset( $package ) )
		{
			foreach ( \O2System::$load->getAssetsDirectories() as $path )
			{
				$path = $path . 'packages' . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR;

				foreach ( [ 'css', 'js' ] as $extension )
				{
					if ( is_file( $filepath = $path . $package . '.' . $extension ) )
					{
						$this->storage[ $group ][ $extension ][ $package ] = $filepath;
					}

					if ( isset( $theme ) )
					{
						if ( is_dir( $theme_path = $path . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR ) )
						{
							if ( is_file( $theme_filepath = $theme_path . $theme . '.' . $extension ) )
							{
								$this->storage[ $group ][ $extension ][ $package . '-theme-' . $theme ] = $theme_filepath;
							}
						}
						elseif ( is_dir( $theme_path = $path . 'themes' . DIRECTORY_SEPARATOR ) )
						{
							if ( is_file( $theme_filepath = $theme_path . $theme . '.' . $extension ) )
							{
								$this->storage[ $group ][ $extension ][ $package . '-theme-' . $theme ] = $theme_filepath;
							}
						}
					}

					if ( isset( $plugins ) )
					{
						$plugins = is_string( $plugins ) ? [ $plugins ] : $plugins;

						foreach ( $plugins as $plugin )
						{
							if ( is_dir( $plugins_path = $path . 'plugins' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR ) )
							{
								if ( is_file( $plugin_filepath = $plugins_path . $plugin . '.' . $extension ) )
								{
									$this->storage[ $group ][ $extension ][ $package . '-plugin-' . $plugin ] = $plugin_filepath;
								}
							}
							elseif ( is_dir( $plugins_path = $path . 'plugins' . DIRECTORY_SEPARATOR ) )
							{
								if ( is_file( $plugin_filepath = $plugins_path . $plugin . '.' . $extension ) )
								{
									$this->storage[ $group ][ $extension ][ $package . '-plugin-' . $plugin ] = $plugin_filepath;
								}
							}
						}
					}

					if ( isset( $languages ) )
					{
						$languages = is_string( $languages ) ? [ $languages ] : $languages;

						foreach ( $languages as $language )
						{
							if ( is_dir( $language_sub_path = $path . 'languages' . DIRECTORY_SEPARATOR . \O2System::$language->active . DIRECTORY_SEPARATOR ) )
							{
								$language_path = $language_sub_path;
							}
							elseif ( is_dir( $language_root_path = $path . 'languages' . DIRECTORY_SEPARATOR ) )
							{
								$language_path = $language_root_path;
							}

							$language_filename = $language . '-' . \O2System::$language->active . '.' . $extension;

							if ( is_file( $language_filepath = $language_path . $language_filename ) )
							{
								$this->storage[ $group ][ $extension ][ $package . '-language-' . $language ] = $language_filepath;
							}
						}
					}
				}
			}
		}
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

		if ( isset( $this->storage[ $group ][ 'css' ] ) )
		{
			if ( in_array( $css, $this->storage[ $group ][ 'css' ] ) )
			{
				return $this;
			}
		}

		if ( is_file( $css ) )
		{
			$attr[ 'realpath' ] = $css;

			$this->storage[ $group ][ 'css' ][ pathinfo( $css, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
		}
		elseif ( strpos( $css, '://' ) !== FALSE )
		{
			$attr[ 'src' ] = $css;

			$this->storage[ $group ][ 'css' ][ pathinfo( $css, PATHINFO_FILENAME ) ] = new Tag( 'link', $attr );
		}
		elseif ( $group === 'inline' )
		{
			$this->storage[ 'inline' ][ 'css' ][ md5( $css ) ] = $css;
		}
		else
		{
			$this->storage[ $group ][ 'css' ][ $css ] = $css;
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

		if ( isset( $this->storage[ $group ][ 'js' ] ) )
		{
			if ( in_array( $js, $this->storage[ $group ][ 'js' ] ) )
			{
				return $this;
			}
		}

		if ( is_file( $js ) )
		{
			$attr[ 'realpath' ] = $js;

			$this->storage[ $group ][ 'js' ][ pathinfo( $js, PATHINFO_FILENAME ) ] = new Tag( 'script', $attr );
		}
		elseif ( strpos( $js, '://' ) !== FALSE )
		{
			$attr[ 'src' ] = $js;

			$this->storage[ $group ][ 'js' ][ pathinfo( $js, PATHINFO_FILENAME ) ] = new Tag( 'script', $attr );
		}
		elseif ( $group === 'inline' )
		{
			$this->storage[ 'inline' ][ 'js' ][ md5( $js ) ] = $js;
		}
		else
		{
			$this->storage[ $group ][ 'js' ][ $js ] = $js;
		}

		return $this;
	}

	public function __get( $property )
	{
		if ( empty( $this->compiles ) )
		{
			$this->render();
		}

		return $this->compiles->offsetGet( $property );
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
		$this->compiles = new ArrayObject(
			[
				'header' => new Assets\Compiler(),
				'footer' => new Assets\Compiler(),
				'css'    => new Assets\Compiler(),
				'js'     => new Assets\Compiler(),
			] );

		$this->compiles->css->setCombined( $this->config[ 'combine' ] )->setExtension( 'Css' );
		$this->compiles->js->setCombined( $this->config[ 'combine' ] )->setExtension( 'Js' );

		foreach ( $this->storage as $group => $collection )
		{
			if ( $group === 'fonts' )
			{
				if ( $this->compiles->offsetExists( $group ) === FALSE )
				{
					$this->compiles[ $group ] = new Assets\Compiler();
				}

				foreach ( $collection as $item => $filepath )
				{
					$this->compiles->header[ underscore( $item ) ] = $this->compiles[ $group ][ underscore( $item ) ] = new Tag(
						'link', [
						'media'    => 'all',
						'rel'      => 'stylesheet',
						'type'     => 'text/css',
						'realpath' => ( strpos( $filepath, '://' ) === FALSE ? path_to_url( $filepath ) : '' ),
						'href'     => ( strpos( $filepath, '://' ) === FALSE ? path_to_url( $filepath ) : $filepath ),
					] );
				}
			}
			elseif ( $group === 'favicons' )
			{
				if ( $this->compiles->offsetExists( $group ) === FALSE )
				{
					$this->compiles[ $group ] = new Assets\Compiler();
				}
			}
			elseif ( $group === 'inline' )
			{
				if ( $this->compiles->offsetExists( $group ) === FALSE )
				{
					$this->compiles[ $group ] = new Assets\Compiler();
				}
			}
			else
			{
				foreach ( $collection as $extension => $files )
				{
					foreach ( $files as $item => $filepath )
					{
						if ( $extension === 'css' )
						{
							$this->compiles->header[ underscore( $item ) ] = $this->compiles[ $extension ][ underscore( $item ) ] = new Tag(
								'link', [
								'media'    => 'all',
								'rel'      => 'stylesheet',
								'type'     => 'text/css',
								'realpath' => ( strpos( $filepath, '://' ) === FALSE ? path_to_url( $filepath ) : '' ),
								'href'     => ( strpos( $filepath, '://' ) === FALSE ? path_to_url( $filepath ) : $filepath ),
							] );
						}
						elseif ( $extension === 'js' )
						{
							$this->compiles->footer[ underscore( $item ) ] = $this->compiles[ $extension ][ underscore( $item ) ] = new Tag(
								'script', [
								'type'     => 'text/javascript',
								'defer'    => 'defer',
								'realpath' => ( strpos( $filepath, '://' ) === FALSE ? path_to_url( $filepath ) : '' ),
								'src'      => ( strpos( $filepath, '://' ) === FALSE ? path_to_url( $filepath ) : $filepath ),
							] );
						}
					}
				}
			}
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}

	private function __inlineCss( $collection )
	{
		$this->storage[ 'css' ][ 'inline' ] = new Tag(
			'style', implode( PHP_EOL, $collection ), [
			'media' => 'all',
			'rel'   => 'stylesheet',
			'type'  => 'text/css',
		] );
	}

	private function __inlineJs( $collection )
	{
		$this->storage[ 'js' ][ 'inline' ] = new Tag(
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
				$this->storage[ 'css' ][ $key ] = $css;
			}
			elseif ( strpos( $css, '://' ) !== FALSE )
			{
				$key = is_numeric( $key ) ? pathinfo( $css, PATHINFO_FILENAME ) : $key;

				$this->storage[ 'css' ][ $key ] = new Tag(
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

					$this->storage[ 'css' ][ $key ] = new Tag(
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
			$this->storage[ 'css' ][ $css ] = new Tag(
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

				$this->storage[ 'js' ][ $key ] = new Tag(
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

					$this->storage[ 'js' ][ $key ] = new Tag(
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
			$this->storage[ 'js' ][ $js ] = new Tag(
				'script', [
				'type'     => 'text/javascript',
				'defer'    => 'defer',
				'realpath' => $filepath,
				'src'      => path_to_url( $filepath ),
			] );
		}

		return FALSE;
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

				$this->storage[ 'css' ][ $key ] = new Tag(
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

					$this->storage[ 'css' ][ $key ] = new Tag(
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

						$key                              = is_numeric( $key ) ? pathinfo( $filepath, PATHINFO_FILENAME ) : $key;
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
}