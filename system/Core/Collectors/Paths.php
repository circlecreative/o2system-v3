<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 7:20 AM
 */

namespace O2System\Core\Collectors;


trait Paths
{
	/**
	 * List of Paths
	 *
	 * @type array
	 */
	protected $paths = [ ];

	/**
	 * Sub Path
	 *
	 * @type string|null
	 */
	protected $subPath = NULL;

	public function setSubPath( $subPath )
	{
		$this->subPath = $subPath;

		return $this;
	}

	public function setPaths( array $paths )
	{
		$this->paths = [ ];
		$this->addPaths( $paths );

		return $this;
	}

	public function addPaths( array $paths )
	{
		foreach ( $paths as $path )
		{
			$this->addPath( $path );
		}

		return $this;
	}

	public function addPath( $path )
	{
		if ( is_dir( $path ) AND ! in_array( $path, $this->paths ) )
		{
			$path = rtrim( str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $path ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
			array_unshift( $this->paths, $path );
		}

		return $this;
	}

	public function removePath( $path )
	{
		if ( FALSE !== ( $key = array_search( $path, $this->paths ) ) )
		{
			unset( $this->paths[ $key ] );
		}

		return $this;
	}

	public function getPaths( $subPath = NULL, $reverse = FALSE )
	{
		$subPath = isset( $subPath ) ? $subPath : $this->subPath;

		if ( empty( $subPath ) )
		{
			return ( $reverse === TRUE ) ? array_reverse( $this->paths ) : $this->paths;
		}
		else
		{
			$replacedSubPaths = [ ];
			foreach ( $this->paths as $path )
			{
				foreach ( [ strtolower( $subPath ), ucfirst( $subPath ) ] as $subPath )
				{
					$subPath = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $subPath );

					if ( is_dir( $path . $subPath ) AND ! in_array( $path . $subPath, $replacedSubPaths ) )
					{
						$replacedSubPaths[] = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $path . $subPath ) . DIRECTORY_SEPARATOR;
						break;
					}
				}
			}

			return ( $reverse === TRUE ) ? array_reverse( $replacedSubPaths ) : $replacedSubPaths;
		}
	}
}