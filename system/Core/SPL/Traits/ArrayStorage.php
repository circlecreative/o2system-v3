<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 6:58 PM
 */

namespace O2System\Core\SPL\Traits;


/**
 * Class ArrayStorage
 *
 * @package O2System\Core\SPL\Traits
 */
trait ArrayStorage
{
	/**
	 * isEmpty
	 *
	 * Validate ArrayObject is empty
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		return (bool) ( $this->count() == 0 ) ? TRUE : FALSE;
	}

	public function countValues()
	{
		return array_count_values( $this->getArrayCopy() );
	}

	public function exchangeStorage( array $array )
	{
		if ( method_exists( $this, 'exchangeArray' ) )
		{
			$this->exchangeArray( $array );

			return;
		}

		parent::__construct( $array );
	}

	/**
	 * Clear Storage
	 *
	 * Clear array iterator storage
	 */
	public function clearStorage()
	{
		if ( method_exists( $this, 'exchangeArray' ) )
		{
			$this->exchangeArray( [ ] );

			return;
		}

		parent::__construct( [ ] );
	}

	/**
	 * In Storage
	 *
	 * Checks if a value exists in storage
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public function inStorage( $value )
	{
		return (bool) in_array( $value, $this->getArrayCopy() );
	}
}