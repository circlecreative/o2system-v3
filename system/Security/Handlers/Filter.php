<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 02-Aug-16
 * Time: 9:51 PM
 */

namespace O2System\Security\Handlers;

use O2System\Core;

class Filter extends Core\Library
{
	use Core\Library\Traits\Handlers;

	public function __construct()
	{
		parent::__construct();

		$this->__locateHandlers();

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ __CLASS__ ] );
	}

	/**
	 * Clean Keys
	 *
	 * Internal method that helps to prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @param    string $string Input string
	 * @param    string $fatal  Whether to terminate script exection
	 *                          or to return FALSE if an invalid
	 *                          key is encountered
	 *
	 * @access  public
	 * @return  string|bool
	 */
	public function cleanKeys( $string, $fatal = TRUE )
	{
		if ( ! preg_match( '/^[a-z0-9:_\/|-]+$/i', $string ) )
		{
			if ( $fatal === TRUE )
			{
				return FALSE;
			}
			else
			{
				\O2System::Exception()->setStatusHeader( 503 );
				echo 'Disallowed Key Characters.';
				exit( 7 ); // EXIT_USER_INPUT
			}
		}

		// Clean UTF-8 if supported
		if ( $this->utf8->isEnabled() === TRUE )
		{
			return $this->utf8->cleanString( $string );
		}

		return $string;
	}

	// --------------------------------------------------------------------

	/**
	 * Clean Input Data
	 *
	 * Internal method that aids in escaping data and
	 * standardizing newline characters to PHP_EOL.
	 *
	 * @param    string|string[] $string Input string(s)
	 *
	 * @access  protected
	 * @return  string
	 */
	public function cleanValues( $string )
	{
		if ( is_array( $string ) )
		{
			$new_array = [ ];
			foreach ( array_keys( $string ) as $key )
			{
				$new_array[ $this->cleanKeys( $key ) ] = $this->cleanValues( $string[ $key ] );
			}

			return $new_array;
		}

		// Clean UTF-8 if supported
		if ( $this->utf8->isEnabled() === TRUE )
		{
			$string = $this->utf8->cleanString( $string );
		}

		// Remove control characters
		$string = remove_invisible_characters( $string, FALSE );

		// Standardize newlines
		return preg_replace( '/(?:\r\n|[\r\n])/', PHP_EOL, $string );
	}

	// ------------------------------------------------------------------------
}