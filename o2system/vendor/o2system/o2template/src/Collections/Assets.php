<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/14/2016
 * Time: 11:37 PM
 */

namespace O2System\Template\Collections;

use O2System\Bootstrap\Factory\Tag;
use O2System\Glob\ArrayObject;
use MatthiasMullie\Minify;

class Assets extends ArrayObject
{
	public function render()
	{
		if ( $this->__isEmpty() === FALSE )
		{
			if ( \O2System::View()->getConfig( 'assets', 'combine' ) === TRUE )
			{
				$assets = $this->getArrayCopy();

				foreach ( $assets as $type => $asset )
				{
					switch ( $type )
					{
						case 'css':

							if ( $theme = \O2System::View()->theme->active )
							{
								$filename = $theme->parameter . '-style.css';
							}
							else
							{
								$filename = 'style.css';
							}

							if ( $cache = \O2System::View()->cache->info( $filename ) )
							{
								$style = new Tag( 'link', array(
									'media' => 'all',
									'rel'   => 'stylesheet',
									'href'  => path_to_url( $cache->realpath ),
								) );

								$this->offsetSet( 'css', $style );
							}
							else
							{
								$minify = new Minify\CSS();

								foreach ( $asset as $file )
								{
									if ( $realpath = $file->get_attribute( 'realpath' ) )
									{
										$minify->add( $realpath );
									}
									elseif ( $href = $file->get_attribute( 'href' ) )
									{
										$data = file_get_contents( $href );

										if ( ! empty( $data ) )
										{
											$minify->add( $data );
										}
									}
								}

								if ( $minify->minify( $realpath = \O2System::View()->getConfig( 'cache', 'path' ) . $filename ) )
								{
									if ( $cache = \O2System::View()->cache->info( $filename ) )
									{
										$style = new Tag( 'link', array(
											'media' => 'all',
											'rel'   => 'stylesheet',
											'type'  => 'text/css',
											'href'  => path_to_url( $cache->realpath ),
										) );

										$this->offsetSet( 'css', $style );
									}
								}
							}

							break;
						case 'js':

							if ( $theme = \O2System::View()->theme->active )
							{
								$filename = $theme->parameter . '-script.js';
							}
							else
							{
								$filename = 'script.js';
							}

							if ( $cache = \O2System::View()->cache->info( $filename ) )
							{
								$script = new Tag( 'script', array(
									'type'  => 'text/javascript',
									'defer' => 'defer',
									'src'   => path_to_url( $cache->realpath ),
								) );

								$this->offsetSet( 'js', $script );
							}
							else
							{
								$minify = new Minify\JS();

								foreach ( $asset as $file )
								{
									if ( $realpath = $file->get_attribute( 'realpath' ) )
									{
										$minify->add( $realpath );
									}
									elseif ( $src = $file->get_attribute( 'src' ) )
									{
										$data = file_get_contents( $src );

										if ( ! empty( $data ) )
										{
											$minify->add( $data );
										}
									}
								}

								if ( $minify->minify( $realpath = \O2System::View()->getConfig( 'cache', 'path' ) . $filename ) )
								{
									if ( $cache = \O2System::View()->cache->info( $filename ) )
									{
										$script = new Tag( 'script', array(
											'type'  => 'text/javascript',
											'defer' => 'defer',
											'src'   => path_to_url( $cache->realpath ),
										) );

										$this->offsetSet( 'js', $script );
									}
								}
							}

							break;
					}
				}
			}

			return implode( PHP_EOL, $this->getArrayCopy() );
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}
}