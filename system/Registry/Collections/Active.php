<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 27-Jul-16
 * Time: 5:53 PM
 */

namespace O2System\Registry\Collections;

use O2System\Core\SPL\ArrayObject;

/**
 * Class Active
 *
 * @package O2System\Registry
 */
class Active extends ArrayObject
{
	/**
	 * Active constructor.
	 *
	 * @param array $active
	 */
	public function __construct( array $active = [ ] )
	{
		parent::__construct( $active );

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}
}