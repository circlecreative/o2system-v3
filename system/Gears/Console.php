<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 10-Aug-16
 * Time: 4:54 PM
 */

namespace O2System\Gears;


class Console
{
	/**
	 * Constants of Console Types
	 *
	 * @access  public
	 * @type     integer
	 */
	const LOG     = 1;
	const INFO    = 2;
	const WARNING = 3;
	const ERROR   = 4;

	// ------------------------------------------------------------------------

	/**
	 * Log
	 *
	 * Send output to browser log console
	 *
	 * @access  public
	 * @static  static class method
	 *
	 * @param   string $title string of output title
	 * @param   mixed  $vars  mixed type variables of data
	 */
	public static function log( $title, $vars )
	{
		static::debug( static::LOG, $title, $vars );
	}
	// ------------------------------------------------------------------------

	/**
	 * Debug
	 *
	 * Send output to browser debug console
	 *
	 * @access  public
	 * @static  static class method
	 *
	 * @param   int    $type  console type
	 * @param   string $title string of output title
	 * @param   mixed  $vars  mixed type variables of data
	 */
	public static function debug( $type, $title, $vars )
	{
		echo '<script type="text/javascript">' . PHP_EOL;
		switch ( $type )
		{
			default:
			case 1:
				echo 'console.log("' . $title . '");' . PHP_EOL;
				break;
			case 2:
				echo 'console.info("' . $title . '");' . PHP_EOL;
				break;
			case 3:
				echo 'console.warn("' . $title . '");' . PHP_EOL;
				break;
			case 4:
				echo 'console.error("' . $title . '");' . PHP_EOL;
				break;
		}

		if ( ! empty( $vars ) )
		{
			if ( is_object( $vars ) || is_array( $vars ) )
			{
				$object = json_encode( $vars );

				echo 'var object' . preg_replace( '~[^A-Z|0-9]~i', "_", $title ) . ' = \'' . str_replace(
						"'", "\'",
						$object
					) . '\';' . PHP_EOL;
				echo 'var val' . preg_replace(
						'~[^A-Z|0-9]~i', "_",
						$title
					) . ' = eval("(" + object' . preg_replace(
						'~[^A-Z|0-9]~i', "_",
						$title
					) . ' + ")" );' . PHP_EOL;
				switch ( $type )
				{
					default:
					case 1:
						echo 'console.debug(val' . preg_replace( '~[^A-Z|0-9]~i', "_", $title ) . ');' . PHP_EOL;
						break;
					case 2:
						echo 'console.info(val' . preg_replace( '~[^A-Z|0-9]~i', "_", $title ) . ');' . PHP_EOL;
						break;
					case 3:
						echo 'console.warn(val' . preg_replace( '~[^A-Z|0-9]~i', "_", $title ) . ');' . PHP_EOL;
						break;
					case 4:
						echo 'console.error(val' . preg_replace( '~[^A-Z|0-9]~i', "_", $title ) . ');' . PHP_EOL;
						break;
				}
			}
			else
			{
				switch ( $type )
				{
					default:
					case 1:
						echo 'console.debug("' . str_replace( '"', '\\"', $vars ) . '");' . PHP_EOL;
						break;
					case 2:
						echo 'console.info("' . str_replace( '"', '\\"', $vars ) . '");' . PHP_EOL;
						break;
					case 3:
						echo 'console.warn("' . str_replace( '"', '\\"', $vars ) . '");' . PHP_EOL;
						break;
					case 4:
						echo 'console.error("' . str_replace( '"', '\\"', $vars ) . '");' . PHP_EOL;
						break;
				}
			}
		}
		echo '</script>' . PHP_EOL;
	}
	// ------------------------------------------------------------------------

	/**
	 * Info
	 *
	 * Send output to browser info console
	 *
	 * @access  public
	 * @static  static class method
	 *
	 * @param   string $title string of output title
	 * @param   mixed  $vars  mixed type variables of data
	 */
	public static function info( $title, $vars )
	{
		static::debug( static::INFO, $title, $vars );
	}
	// ------------------------------------------------------------------------

	/**
	 * Warning
	 *
	 * Send output to browser warning console
	 *
	 * @access  public
	 * @static  static class method
	 *
	 * @param   string $title string of output title
	 * @param   mixed  $vars  mixed type variables of data
	 */
	public static function warning( $title, $vars )
	{
		static::debug( static::WARNING, $title, $vars );
	}
	// ------------------------------------------------------------------------

	/**
	 * Error
	 *
	 * Send output to browser error console
	 *
	 * @access  public
	 * @static  static class method
	 *
	 * @param   string $title string of output title
	 * @param   mixed  $vars  mixed type variables of data
	 */
	public static function error( $title, $vars )
	{
		static::debug( static::ERROR, $title, $vars );
	}
}