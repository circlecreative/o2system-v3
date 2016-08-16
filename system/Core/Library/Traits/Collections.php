<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 5:13 AM
 */

namespace O2System\Core\Library\Traits;


trait Collections
{
	/**
	 * List of library valid collections
	 *
	 * @access  protected
	 *
	 * @type    array   collection classes list
	 */
	private $validCollections = [ ];

	/**
	 * List of library loaded collections
	 *
	 * @access  protected
	 *
	 * @type    array   collection classes list
	 */
	protected $loadedCollections = [ ];

	// ------------------------------------------------------------------------

	/**
	 * Locate Collections
	 *
	 * @access private
	 */
	private function __locateCollections()
	{
		$filepath = ( new \ReflectionClass( get_class( $this ) ) )->getFileName();
		$filename = pathinfo( $filepath, PATHINFO_FILENAME );
		$basepath = dirname( $filepath ) . DIRECTORY_SEPARATOR;

		$paths = [
			$basepath . $filename . DIRECTORY_SEPARATOR . 'Collections' . DIRECTORY_SEPARATOR,
			$basepath . 'Collections' . DIRECTORY_SEPARATOR,
			$basepath . $filename . DIRECTORY_SEPARATOR,
			$basepath . strtolower( $filename ) . DIRECTORY_SEPARATOR . 'collections' . DIRECTORY_SEPARATOR,
			$basepath . 'collections' . DIRECTORY_SEPARATOR,
			$basepath . strtolower( $filename ) . DIRECTORY_SEPARATOR,
		];

		foreach ( $paths as $path )
		{
			if ( is_dir( $path ) )
			{
				// Define valid collections
				foreach ( glob( $path . '*.php' ) as $filepath )
				{
					if ( is_file( $filepath ) )
					{
						$this->validCollections[ strtolower( pathinfo( $filepath, PATHINFO_FILENAME ) ) ] = $filepath;
					}
				}
				break;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Valid Collection
	 *
	 * Validate if collection is a valid collection
	 *
	 * @param $collection
	 *
	 * @return bool
	 */
	public function isValidCollection( $collection )
	{
		return (bool) array_key_exists( $collection, $this->validCollections );
	}

	// ------------------------------------------------------------------------

	/**
	 * Load Collection
	 *
	 * @param $collection
	 *
	 * @return bool|Collections
	 */
	protected function _loadCollection( $collection )
	{
		if ( array_key_exists( $collection, $this->loadedCollections ) )
		{
			return $this->loadedCollections[ $collection ];
		}
		elseif ( is_file( $filepath = $this->validCollections[ $collection ] ) )
		{
			$parent_namespace  = get_parent_class( $this );
			$current_namespace = get_called_class();

			$classes = [
				$current_namespace . '\\' . studlycapcase( $collection ),
				$current_namespace . '\\Collections\\' . studlycapcase( $collection ),
				$parent_namespace . '\\' . studlycapcase( $collection ),
				$parent_namespace . '\\Collections\\' . studlycapcase( $collection ),
			];

			foreach ( $classes as $class )
			{
				if ( class_exists( $class ) )
				{
					$this->loadedCollections[ $collection ] = new $class();

					if ( method_exists( $this->loadedCollections[ $collection ], 'setLibrary' ) )
					{
						if ( property_exists( $this->loadedCollections[ $collection ], 'library' ) )
						{
							$this->loadedCollections[ $collection ]->setLibrary( $this );
						}
					}

					if ( method_exists( $this, 'getConfig' ) AND method_exists( $this->loadedCollections[ $collection ], 'setConfig' ) )
					{
						if ( FALSE !== ( $config = $this->getConfig( $collection ) ) )
						{
							$this->loadedCollections[ $collection ]->setConfig( $config );
						}
					}

					return $this->loadedCollections[ $collection ];
				}
			}
		}

		return FALSE;
	}
}