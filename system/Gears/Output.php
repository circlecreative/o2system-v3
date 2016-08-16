<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 10-Aug-16
 * Time: 5:02 PM
 */

namespace O2System\Gears;


class Output
{
	protected static $_lines = [ ];

	/**
	 * Print Out
	 *
	 * Gear up developer to echo any type of variables to browser screen
	 *
	 * @uses \O2System\Gears\Output::screen()
	 *
	 * @param mixed $vars string|array|object|integer|boolean
	 * @param bool  $halt set FALSE to disabled halt output
	 */
	public static function printScreen( $vars, $halt = TRUE )
	{
		ini_set( 'memory_limit', '512M' );

		$vars = static::__prepareOutput( $vars );
		$vars = htmlentities( $vars );
		$vars = htmlspecialchars( htmlspecialchars_decode( $vars, ENT_QUOTES ), ENT_QUOTES, 'UTF-8' );

		$trace = new Trace();

		if ( isset( $_SERVER[ 'DOCUMENT_ROOT' ] ) )
		{
			$root_dir = $_SERVER[ 'DOCUMENT_ROOT' ];
		}
		else
		{
			$root_dir = dirname( $_SERVER[ 'SCRIPT_FILENAME' ] );
		}

		$root_dir = str_replace( '/', DIRECTORY_SEPARATOR, $root_dir ) . DIRECTORY_SEPARATOR;

		$assets_url =  path_to_url( PUBLICPATH . '/assets/') . '/';
		
		ob_start();

		// Load print out template
		include SYSTEMPATH . 'Views/gears/screen.php';
		$output = ob_get_contents();
		ob_end_clean();

		echo $output;

		if ( $halt === TRUE )
		{
			die;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Print Code
	 *
	 * Gear up developer to echo output with <code> tag to browser screen
	 *
	 * @param mixed $vars string|array|object|integer|boolean
	 * @param mixed $halt set FALSE to disabled halt output
	 */
	public static function printCode( $vars, $halt = TRUE )
	{
		ini_set( 'memory_limit', '512M' );
		$vars = static::__prepareOutput( $vars );
		$vars = htmlentities( $vars );
		$vars = htmlspecialchars( htmlspecialchars_decode( $vars, ENT_QUOTES ), ENT_QUOTES, 'UTF-8' );

		echo '<pre>' . $vars . '</pre>';

		if ( $halt === TRUE )
		{
			die;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Print JSON
	 *
	 * Gear up developer to echo output with <code> tag to browser screen
	 *
	 * @param mixed $vars   string|array|object|integer|boolean
	 * @param mixed $option bool|integer JSON Encode Option
	 * @param mixed $halt   bool set FALSE to disabled halt output
	 */
	public static function printJSON( $vars, $option = NULL, $halt = TRUE )
	{
		if ( is_bool( $option ) )
		{
			$halt   = $option;
			$option = NULL;
		}

		if ( is_numeric( $option ) )
		{
			static::printScreen( json_encode( $vars, $option ), $halt );
		}
		else
		{
			static::printScreen( json_encode( $vars ), $halt );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Print Line
	 *
	 * Gear up developer to print line by line any type of variables
	 * to browser screen from anywhere in source code
	 *
	 * @uses \O2System\Gears\Output::line()
	 *
	 * @param mixed $line       string|array|object|integer|boolean
	 * @param mixed $halt       set TRUE to halt output
	 *                          set (string) FLUSH to flush previous sets of lines output
	 */
	public static function printLine( $line = '', $halt = FALSE )
	{
		if ( strtoupper( $halt ) === 'FLUSH' )
		{
			static::$_lines   = [ ];
			static::$_lines[] = $line;
		}

		if ( is_array( $line ) || is_object( $line ) )
		{
			static::$_lines[] = print_r( $line, TRUE );
		}
		else
		{
			static::$_lines[] = static::__prepareOutput( $line );
		}

		if ( $halt === TRUE OR $line === '---' )
		{
			$vars           = implode( PHP_EOL, static::$_lines );
			static::$_lines = [ ];
			static::printScreen( $vars, $halt );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Print Code
	 *
	 * Gear up developer to do var_dump output with <pre> tag to browser screen
	 *
	 * @uses \O2System\Gears\Output::dump()
	 *
	 * @param mixed $vars string|array|object|integer|boolean
	 * @param mixed $halt set FALSE to disabled halt output
	 */
	public static function printDump( $vars, $halt = TRUE )
	{
		ob_start();
		var_dump( $vars );
		$output = ob_get_contents();
		ob_end_clean();

		static::printCode( $output, $halt );
	}

	// ------------------------------------------------------------------------

	public static function printConsole( $title, $vars, $type = Console::LOG )
	{
		$vars = static::__prepareOutput( $vars );
		Console::debug( $type, $title, $vars );
	}

	// ------------------------------------------------------------------------

	private static function __prepareOutput( $vars )
	{
		if ( is_bool( $vars ) )
		{
			if ( $vars === TRUE )
			{
				$vars = '(bool) TRUE';
			}
			else
			{
				$vars = '(bool) FALSE';
			}
		}
		elseif ( is_resource( $vars ) )
		{
			$vars = '(resource) ' . get_resource_type( $vars );
		}
		elseif ( is_array( $vars ) || is_object( $vars ) )
		{
			$vars = @print_r( $vars, TRUE );
		}
		elseif ( is_int( $vars ) OR is_numeric( $vars ) )
		{
			$vars = '(int) ' . $vars;
		}
		elseif ( is_null( $vars ) )
		{
			$vars = '(null)';
		}

		$vars = trim( $vars );

		return $vars;
	}
}