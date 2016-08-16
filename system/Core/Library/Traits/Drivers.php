<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 5:13 AM
 */

namespace O2System\Core\Library\Traits;

trait Drivers
{
	/**
	 * List of library valid drivers
	 *
	 * @access  protected
	 *
	 * @type    array   driver classes list
	 */
	private $validDrivers = [ ];

	/**
	 * List of library loaded drivers
	 *
	 * @access  protected
	 *
	 * @type    array   driver classes list
	 */
	protected $loadedDrivers = [ ];

	// ------------------------------------------------------------------------

	/**
	 * Locate Drivers
	 *
	 * @access private
	 */
	private function __locateDrivers()
	{
		$filepath = ( new \ReflectionClass( get_class( $this ) ) )->getFileName();
		$filename = pathinfo( $filepath, PATHINFO_FILENAME );
		$basepath = dirname( $filepath ) . DIRECTORY_SEPARATOR;

		$paths = [
			$basepath . $filename . DIRECTORY_SEPARATOR . 'Drivers' . DIRECTORY_SEPARATOR,
			$basepath . 'Drivers' . DIRECTORY_SEPARATOR,
			$basepath . $filename . DIRECTORY_SEPARATOR,
			$basepath . strtolower( $filename ) . DIRECTORY_SEPARATOR . 'drivers' . DIRECTORY_SEPARATOR,
			$basepath . 'drivers' . DIRECTORY_SEPARATOR,
			$basepath . strtolower( $filename ) . DIRECTORY_SEPARATOR,
		];

		foreach ( $paths as $path )
		{
			if ( is_dir( $path ) )
			{
				// Define valid drivers
				foreach ( glob( $path . '*.php' ) as $filepath )
				{
					if ( is_file( $filepath ) )
					{
						$this->validDrivers[ strtolower( pathinfo( $filepath, PATHINFO_FILENAME ) ) ] = $filepath;
					}
				}
				break;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Valid Driver
	 *
	 * Validate if driver is a valid driver
	 *
	 * @param $driver
	 *
	 * @return bool
	 */
	public function isValidDriver( $driver )
	{
		return (bool) array_key_exists( $driver, $this->validDrivers );
	}

	// ------------------------------------------------------------------------

	/**
	 * Load Driver
	 *
	 * @param $driver
	 *
	 * @return bool|Driver
	 */
	protected function _loadDriver( $driver )
	{
		if ( array_key_exists( $driver, $this->loadedDrivers ) )
		{
			return $this->loadedDrivers[ $driver ];
		}
		elseif ( is_file( $filepath = $this->validDrivers[ $driver ] ) )
		{
			$parent_namespace  = get_parent_class( $this );
			$current_namespace = get_called_class();

			$classes = [
				$current_namespace . '\\' . studlycapcase( $driver ),
				$current_namespace . '\\Drivers\\' . studlycapcase( $driver ),
				$parent_namespace . '\\' . studlycapcase( $driver ),
				$parent_namespace . '\\Drivers\\' . studlycapcase( $driver ),
			];

			foreach ( $classes as $class )
			{
				if ( class_exists( $class ) )
				{
					$this->loadedDrivers[ $driver ] = new $class();

					if ( method_exists( $this->loadedDrivers[ $driver ], 'setLibrary' ) )
					{
						if ( property_exists( $this->loadedDrivers[ $driver ], 'library' ) )
						{
							$this->loadedDrivers[ $driver ]->setLibrary( $this );
						}
					}

					if ( method_exists( $this, 'getConfig' ) AND method_exists( $this->loadedDrivers[ $driver ], 'setConfig' ) )
					{
						if ( FALSE !== ( $config = $this->getConfig( $driver ) ) )
						{
							$this->loadedDrivers[ $driver ]->setConfig( $config );
						}
					}

					return $this->loadedDrivers[ $driver ];
				}
			}
		}

		return FALSE;
	}
}