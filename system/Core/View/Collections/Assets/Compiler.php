<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 06-Aug-16
 * Time: 9:03 AM
 */

namespace O2System\Core\View\Collections\Assets;

use MatthiasMullie\Minify;
use O2System\Core\HTML\Tag;
use O2System\Core\SPL\ArrayObject;

class Compiler extends ArrayObject
{
	private $cacheFilename;
	private $extension;
	private $isCombined = FALSE;

	public function setCacheFilename( $filename )
	{
		$this->cacheFilename = $filename;

		return $this;
	}

	public function setExtension( $extension )
	{
		$this->extension = $extension;

		return $this;
	}

	public function setCombined( $combined )
	{
		$this->isCombined = (bool) $combined;

		return $this;
	}

	public function render()
	{
		if ( $this->isEmpty() === FALSE )
		{
			if ( $this->isCombined === FALSE )
			{
				return implode( PHP_EOL, $this->getArrayCopy() );
			}
			else
			{
				$assets = $this->getArrayCopy();

				if ( $this->extension === 'Js' )
				{
					$filename = $this->cacheFilename . '.js';

					if ( $cache = \O2System::$view->cache->info( $filename ) )
					{
						$script = new Tag(
							'script', [
							'type'  => 'text/javascript',
							'defer' => 'defer',
							'src'   => path_to_url( $cache->realpath ),
						] );

						$this->offsetSet( 'js', $script );
					}
					else
					{
						$minify = new Minify\JS();

						foreach ( $assets as $asset )
						{
							if ( $realpath = $asset->getAttribute( 'realpath' ) )
							{
								$minify->add( $realpath );
							}
							elseif ( $href = $asset->getAttribute( 'href' ) )
							{
								$data = file_get_contents( $href );

								if ( ! empty( $data ) )
								{
									$minify->add( $data );
								}
							}
						}

						if ( $minify->minify( $realpath = \O2System::$view->getConfig( 'cache', 'path' ) . $filename ) )
						{
							if ( $cache = \O2System::$view->cache->info( $filename ) )
							{
								$script = new Tag(
									'script', [
									'type'  => 'text/javascript',
									'defer' => 'defer',
									'src'   => path_to_url( $cache->realpath ),
								] );

								$this->offsetSet( 'js', $script );
							}
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
}