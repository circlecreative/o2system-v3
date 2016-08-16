<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 10-Aug-16
 * Time: 4:55 PM
 */

namespace O2System\Gears;


class Debug
{
	/**
	 * List of Debug Chronology
	 *
	 * @access  private
	 * @static
	 *
	 * @type    array
	 */
	private static $_chronology = [ ];

	// ------------------------------------------------------------------------

	/**
	 * Start
	 *
	 * Start Debug Process
	 *
	 * @access  public
	 * @static  static method
	 */
	public static function start()
	{
		static::$_chronology   = [ ];
		static::$_chronology[] = static::__whereCall( __CLASS__ . '::start()' );
	}

	// ------------------------------------------------------------------------

	/**
	 * Where Call Method
	 *
	 * Finding where the call is made
	 *
	 * @access          private
	 *
	 * @param   $call   String Call Method
	 *
	 * @return          Tracer Object
	 */
	private static function __whereCall( $call )
	{
		$tracer = new Tracer();

		foreach ( $tracer->chronology() as $trace )
		{
			if ( $trace->call === $call )
			{
				return $trace;
				break;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Line
	 *
	 * Add debug line
	 *
	 * @access           public
	 *
	 * @param   $vars    Mixed type variables of data
	 *                   $export  Export vars option
	 *
	 * @return           void
	 */
	public static function line( $vars, $export = FALSE )
	{
		$trace = static::__whereCall( __CLASS__ . '::line()' );

		if ( $export === TRUE )
		{
			$trace->data = var_export( $vars, TRUE );
		}
		else
		{
			$trace->data = Output::prepare_data( $vars );
		}

		static::$_chronology[] = $trace;
	}

	// ------------------------------------------------------------------------

	/**
	 * Line
	 *
	 * Add debug line
	 *
	 * @access           public
	 *
	 * @param   $vars    Mixed type variables of data
	 *                   $export  Export vars option
	 *
	 * @return           void
	 */
	public static function marker()
	{
		$trace                 = static::__whereCall( __CLASS__ . '::marker()' );
		static::$_chronology[] = $trace;
	}

	// ------------------------------------------------------------------------

	/**
	 * Stop
	 *
	 * Stop Debug Process
	 *
	 * @access          public
	 *
	 * @param   $halt   Boolean option for halt the Debug Process or not
	 *
	 * @return          void
	 */
	public static function stop( $halt = TRUE )
	{
		static::$_chronology[] = static::__whereCall( __CLASS__ . '::stop()' );
		$chronology            = static::$_chronology;
		static::$_chronology   = [ ];

		Output::screen( $chronology, $halt );
	}
}