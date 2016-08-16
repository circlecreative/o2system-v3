<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 3:20 AM
 */

namespace O2System\Core\Library;


use O2System\Core\Collectors\Config;
use O2System\Core\Collectors\Errors;

abstract class Handler
{
	/**
	 * Use Config Collectors
	 */
	use Config;

	/**
	 * Use Errors Collectors
	 */
	use Errors;

	// ------------------------------------------------------------------------

	/**
	 * Set Library
	 *
	 * Set Handler Library Instance
	 *
	 * @param Base $library Handler Library Instance
	 *
	 * @access  public
	 */
	public function setLibrary( $library )
	{
		if ( property_exists( $this, 'library' ) )
		{
			$this->library =& $library;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic __get
	 *
	 * Handles reading of the parent handler or library's properties
	 *
	 * @access      public
	 * @static      static class method
	 * @final       this method can't be overwritten
	 *
	 * @param   string $property property name
	 *
	 * @return mixed
	 */
	public function __get( $property )
	{
		if ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}
		elseif ( isset( $this->library ) AND $this->library instanceof Base )
		{
			return $this->library->{$property};
		}
	}
}