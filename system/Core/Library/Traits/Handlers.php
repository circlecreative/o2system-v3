<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 5:13 AM
 */

namespace O2System\Core\Library\Traits;

trait Handlers
{
	/**
	 * List of library valid handlers
	 *
	 * @access  protected
	 *
	 * @type    array   handler classes list
	 */
	private $validHandlers = [ ];

	/**
	 * List of library loaded handlers
	 *
	 * @access  protected
	 *
	 * @type    array   handler classes list
	 */
	protected $loadedHandlers = [ ];

	// ------------------------------------------------------------------------

	/**
	 * Locate Handlers
	 *
	 * @access private
	 */
	private function __locateHandlers()
	{
		$filepath = ( new \ReflectionClass( get_class( $this ) ) )->getFileName();
		$filename = pathinfo( $filepath, PATHINFO_FILENAME );
		$basepath = dirname( $filepath ) . DIRECTORY_SEPARATOR;

		$paths = [
			$basepath . $filename . DIRECTORY_SEPARATOR . 'Handlers' . DIRECTORY_SEPARATOR,
			$basepath . 'Handlers' . DIRECTORY_SEPARATOR,
			$basepath . $filename . DIRECTORY_SEPARATOR,
			$basepath . strtolower( $filename ) . DIRECTORY_SEPARATOR . 'handlers' . DIRECTORY_SEPARATOR,
			$basepath . 'handlers' . DIRECTORY_SEPARATOR,
			$basepath . strtolower( $filename ) . DIRECTORY_SEPARATOR,
		];

		foreach ( $paths as $path )
		{
			if ( is_dir( $path ) )
			{
				// Define valid handlers
				foreach ( glob( $path . '*.php' ) as $filepath )
				{
					if ( is_file( $filepath ) )
					{
						$this->validHandlers[ strtolower( pathinfo( $filepath, PATHINFO_FILENAME ) ) ] = $filepath;
					}
				}
				break;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Valid Handler
	 *
	 * Validate if handler is a valid handler
	 *
	 * @param $handler
	 *
	 * @return bool
	 */
	public function isValidHandler( $handler )
	{
		return (bool) array_key_exists( $handler, $this->validHandlers );
	}

	// ------------------------------------------------------------------------

	/**
	 * Load Handler
	 *
	 * @param $handler
	 *
	 * @return bool|Handler
	 */
	protected function _loadHandler( $handler )
	{
		if ( array_key_exists( $handler, $this->loadedHandlers ) )
		{
			return $this->loadedHandlers[ $handler ];
		}
		elseif ( is_file( $filepath = $this->validHandlers[ $handler ] ) )
		{
			$parent_namespace  = get_parent_class( $this );
			$current_namespace = get_called_class();
			$class_name        = pathinfo( $filepath, PATHINFO_FILENAME );

			$classes = [
				$current_namespace . '\\' . $class_name,
				$current_namespace . '\\Handlers\\' . $class_name,
				$parent_namespace . '\\' . $class_name,
				$parent_namespace . '\\Handlers\\' . $class_name,
			];

			foreach ( $classes as $class )
			{
				if ( class_exists( $class ) )
				{
					$this->loadedHandlers[ $handler ] = new $class();

					if ( method_exists( $this->loadedHandlers[ $handler ], 'setLibrary' ) )
					{
						if ( property_exists( $this->loadedHandlers[ $handler ], 'library' ) )
						{
							$this->loadedHandlers[ $handler ]->setLibrary( $this );
						}
					}

					if ( method_exists( $this, 'getConfig' ) AND method_exists( $this->loadedHandlers[ $handler ], 'setConfig' ) )
					{
						if ( FALSE !== ( $config = $this->getConfig( $handler ) ) )
						{
							$this->loadedHandlers[ $handler ]->setConfig( $config );
						}
					}

					return $this->loadedHandlers[ $handler ];
				}
			}
		}

		return NULL;
	}
}