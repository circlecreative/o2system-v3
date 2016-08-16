<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 4:05 PM
 */

namespace O2System\Core\View\Handlers;


use O2System\Core\Library\Handler;
use O2System\Exception\ViewThemeException;

class Theme extends Handler
{
	/**
	 * Active Theme
	 *
	 * @type \O2System\Registry\Metadata\Theme
	 */
	public $active = FALSE;

	public function initialize()
	{
		if ( isset( $this->config[ 'default' ] ) )
		{
			$this->set( $this->config[ 'default' ] );
		}

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	public function set( $theme )
	{
		if ( is_string( $theme ) )
		{
			if ( $this->isExists( $theme ) )
			{
				if ( ! empty( $this->active ) )
				{
					\O2System::$view->removePath( $this->active->realpath );
				}

				$this->_loadPackage( $theme );
			}
		}
		elseif ( is_bool( $theme ) AND $theme === FALSE )
		{
			$this->active = FALSE;
		}

		return $this;
	}

	public function isExists( $theme )
	{
		foreach ( \O2System::$load->getThemesDirectories( TRUE ) as $path )
		{
			if ( is_dir( $path . $theme ) )
			{
				return TRUE;
				break;
			}
		}

		return FALSE;
	}

	protected function _loadPackage( $theme )
	{
		foreach ( \O2System::$load->getThemesDirectories( TRUE ) as $path )
		{
			if ( is_dir( $theme_path = $path . $theme . DIRECTORY_SEPARATOR ) )
			{
				// Locate theme properties
				if ( ! is_file( $theme_path . 'theme.properties' ) )
				{
					throw new ViewThemeException( 'E_VIEW_THEME_PROPERTIES_NOT_FOUND', 108, [ $theme_path . 'theme.properties' ] );
				}

				// Read theme properties
				$properties = json_decode( file_get_contents( $theme_path . 'theme.properties' ), TRUE );

				if ( json_last_error() !== JSON_ERROR_NONE )
				{
					throw new ViewThemeException( 'E_VIEW_THEME_PROPERTIES_INVALID', 109, [ $theme_path . 'theme.properties' ] );
				}
				else
				{
					$active = new \O2System\Registry\Metadata\Theme( $properties );
					$active[ 'parameter' ] = $theme;
					$active[ 'realpath' ]  = $theme_path;
					$active[ 'url' ]       = path_to_url( $theme_path ) . '/';

					\O2System::$view->addPath( $theme_path );

					if ( is_file( $theme_path . 'theme.settings' ) )
					{
						$settings = json_decode( file_get_contents( $theme_path . 'theme.settings' ), TRUE );

						if ( json_last_error() === JSON_ERROR_NONE )
						{
							$active[ 'settings' ] = $settings;
						}
						else
						{
							throw new ViewThemeException( 'E_VIEW_THEME_SETTINGS_INVALID', 110, [ $theme_path . 'theme.settings' ] );
						}
					}

					foreach ( \O2System::$view->parser->getExtensions() as $extension )
					{
						if ( \O2System::$view->parser->isParsePHP() === FALSE AND $extension === '.php' )
						{
							continue;
						}

						if ( is_file( $layout = $theme_path . 'theme' . $extension ) )
						{
							$active[ 'layout' ] = $layout;

							// Read Partials
							if ( is_dir( $theme_path . 'partials' . DIRECTORY_SEPARATOR ) )
							{
								$files = scandir( $theme_path . 'partials' . DIRECTORY_SEPARATOR );

								$active[ 'partials' ] = [ ];

								foreach ( $files as $file )
								{
									if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' AND \O2System::$view->parser->isParsePHP() === FALSE )
									{
										continue;
									}

									if ( is_file( $theme_path . 'partials' . DIRECTORY_SEPARATOR . $file ) )
									{
										$active[ 'partials' ][ pathinfo( $file, PATHINFO_FILENAME ) ] = $theme_path . 'partials' . DIRECTORY_SEPARATOR . $file;
									}
								}
							}

							break;
						}
					}
				}

				if ( empty( $active[ 'layout' ] ) )
				{
					throw new ViewThemeException( 'E_VIEW_THEME_UNDEFINED_DEFAULT_LAYOUT', 111, [ $theme_path ] );
				}

				\O2System::$active[ 'theme' ] = $active;

				return TRUE;
				break;
			}
		}

		if ( empty( $this->active ) )
		{
			throw new ViewThemeException( 'E_VIEW_THEME_PACKAGE_NOT_FOUND', 112, [ $theme_path ] );
		}
	}

	public function isLayoutExists( $layout = 'theme', $theme = NULL )
	{
		if ( isset( $theme ) )
		{
			foreach ( \O2System::$view->getPaths( 'themes' ) as $path )
			{
				if ( is_dir( $layout_path = $path . $theme . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR ) )
				{
					foreach ( \O2System::$view->parser->getExtensions() as $extension )
					{
						if ( \O2System::$view->parser->isParsePHP() === FALSE AND $extension === '.php' )
						{
							continue;
						}
						elseif ( is_file( $layout_path . $layout . $extension ) )
						{
							return TRUE;
							break;
						}
					}

					break;
				}
			}
		}
		elseif ( isset( $this->active ) )
		{
			foreach ( \O2System::$view->parser->getExtensions() as $extension )
			{
				if ( \O2System::$view->parser->isParsePHP() === FALSE AND $extension === '.php' )
				{
					continue;
				}
				elseif ( is_file( $this->active->realpath . 'layouts' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR . $layout . $extension ) )
				{
					return TRUE;
					break;
				}
			}
		}

		return FALSE;
	}

	public function setLayout( $layout )
	{
		$layout_path = $this->active->realpath . 'layouts' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR;

		if ( $this->isLayoutExists( $layout ) )
		{
			foreach ( \O2System::$view->parser->getExtensions() as $extension )
			{
				if ( \O2System::$view->parser->isParsePHP() === FALSE AND $extension === '.php' )
				{
					continue;
				}
				elseif ( is_file( $layout_path . $layout . $extension ) )
				{
					$this->active[ 'layout' ] = $layout_path . $layout . $extension;
					\O2System::$view->addPath( $layout_path );

					// Load Layout Settings
					if ( is_file( $layout_path . $layout . '.settings' ) )
					{
						$settings = json_decode( file_get_contents( $layout_path . $layout . '.settings' ), TRUE );

						if ( json_last_error() === JSON_ERROR_NONE )
						{
							$this->active[ 'settings' ] = $settings;
						}
						else
						{
							throw new ViewThemeException( 'E_VIEW_THEME_LAYOUT_SETTINGS_INVALID', 113, [ $layout_path . $layout . '.settings' ] );
						}
					}

					// Load Layout Partials
					if ( is_dir( $layout_path . 'partials' . DIRECTORY_SEPARATOR ) )
					{
						$files = scandir( $layout_path . 'partials' . DIRECTORY_SEPARATOR );

						$this->active[ 'partials' ] = [ ];

						foreach ( $files as $file )
						{
							if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' AND \O2System::$view->parser->isParsePHP() === FALSE )
							{
								continue;
							}

							if ( is_file( $layout_path . 'partials' . DIRECTORY_SEPARATOR . $file ) )
							{
								$this->active[ 'partials' ][ pathinfo( $file, PATHINFO_FILENAME ) ] = $layout_path . 'partials' . DIRECTORY_SEPARATOR . $file;
							}
						}
					}

					return TRUE;

					break;
				}
			}
		}

		throw new ViewThemeException( 'E_VIEW_THEME_LAYOUT_NOT_EXISTS', 114, [ $layout_path ] );
	}
}