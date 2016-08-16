<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 10-Aug-16
 * Time: 4:55 PM
 */

namespace O2System\Gears;


class Trace
{
	/**
	 * Class Name
	 *
	 * @access  protected
	 * @type    string name of called class
	 */
	const PROVIDE_OBJECT = DEBUG_BACKTRACE_PROVIDE_OBJECT;
	/**
	 * Class Name
	 *
	 * @access  protected
	 * @type    string name of called class
	 */
	const IGNORE_ARGS = DEBUG_BACKTRACE_IGNORE_ARGS;
	/**
	 * Class Name
	 *
	 * @access  protected
	 * @type    string name of called class
	 */
	private $_trace = NULL;
	/**
	 * Class Name
	 *
	 * @access  protected
	 * @type    string name of called class
	 */
	private $_chronology = [ ];
	/**
	 * Class Name
	 *
	 * @access  protected
	 * @type    string name of called class
	 */
	private $_benchmark = [ ];

	// ------------------------------------------------------------------------

	/**
	 * Class Constructor
	 *
	 * @access public
	 *
	 * @param string $flag tracer option
	 */
	public function __construct( $trace = [ ], $flag = Trace::PROVIDE_OBJECT )
	{
		$this->_benchmark = [
			'time'   => time() + microtime(),
			'memory' => memory_get_usage(),
		];

		if ( ! empty( $trace ) )
		{
			$this->_trace = $trace;
		}
		else
		{
			$this->_trace = debug_backtrace( $flag );
		}

		// reverse array to make steps line up chronologically
		$this->_trace = array_reverse( $this->_trace );

		// Generate Lines
		$this->__generateChronology();
	}

	// ------------------------------------------------------------------------

	/**
	 * Generate Chronology Method
	 *
	 * Generate array of Backtrace Chronology
	 *
	 * @access           private
	 * @return           void
	 */
	private function __generateChronology()
	{
		foreach ( $this->_trace as $trace )
		{
			if ( in_array( $trace[ 'function' ], [ 'showException', 'showError', 'showPhpError', 'shutdown' ] ) OR
				( isset( $trace[ 'class' ] ) AND $trace[ 'class' ] === 'O2System\Gears\Tracer' )
			)
			{
				continue;
			}

			$line = new Trace\Chronology();

			if ( isset( $trace[ 'class' ] ) && isset( $trace[ 'type' ] ) )
			{
				$line->call = $trace[ 'class' ] . $trace[ 'type' ] . $trace[ 'function' ] . '()';
				$line->type = $trace[ 'type' ] === '->' ? 'non-static' : 'static';
			}
			else
			{
				$line->call = $trace[ 'function' ] . '()';
				$line->type = 'non-static';
			}

			if ( ! empty( $trace[ 'args' ] ) AND $line->call !== 'print_out()' )
			{
				$line->args = $trace[ 'args' ];
			}

			if ( ! isset( $trace[ 'file' ] ) )
			{
				$current_trace = current( $this->_trace );
				$line->file    = @$current_trace[ 'file' ];
				$line->line    = @$current_trace[ 'line' ];
			}
			else
			{
				$line->file = @$trace[ 'file' ];
				$line->line = @$trace[ 'line' ];
			}

			$line->time   = ( time() + microtime() ) - $this->_benchmark[ 'time' ];
			$line->memory = memory_get_usage() - $this->_benchmark[ 'memory' ];

			$this->_chronology[] = $line;

			if ( in_array( $trace[ 'function' ], [ 'print_out', 'print_line' ] ) )
			{
				break;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Chronology Method
	 *
	 * Backtrace chronology
	 *
	 * @access public
	 *
	 * @param   bool $reset option for resetting the chronology data
	 *
	 * @return  array
	 */
	public function chronology( $reset = TRUE )
	{
		$chronology = $this->_chronology;

		if ( $reset === TRUE )
		{
			$this->_chronology = [ ];
		}

		return $chronology;
	}
}