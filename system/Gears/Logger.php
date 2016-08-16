<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 10-Aug-16
 * Time: 4:55 PM
 */

namespace O2System\Gears;


class Logger
{
	/**
	 * Constants of Logger Types
	 *
	 * @access  public
	 * @type    integer
	 */
	const DISABLED  = 0;
	const DEBUG     = 1;
	const INFO      = 2;
	const NOTICE    = 3;
	const WARNING   = 4;
	const ALERT     = 5;
	const ERROR     = 6;
	const EMERGENCY = 7;
	const CRITICAL  = 8;
	const ALL       = 9;

	/**
	 * Class config
	 *
	 * @access protected
	 *
	 * @type array
	 */
	protected $config = [
		'path'        => NULL,
		'threshold'   => Logger::ALL,
		'date_format' => 'Y-m-d H:i:s',
	];

	/**
	 * List of logging levels
	 *
	 * @access protected
	 * @type array
	 */
	protected $_levels = [
		0 => 'DISABLED',
		1 => 'DEBUG',
		2 => 'INFO',
		3 => 'NOTICE',
		4 => 'WARNING',
		5 => 'ALERT',
		6 => 'ERROR',
		7 => 'EMERGENCY',
		8 => 'CRITICAL',
		9 => 'ALL',
	];

	// --------------------------------------------------------------------

	/**
	 * Class Initialize
	 *
	 * @throws \Exception
	 */
	public function __construct( array $config = [ ] )
	{
		$this->config = array_merge( $this->config, $config );

		if ( ! is_dir( $this->config[ 'path' ] ) )
		{
			if ( ! mkdir( $this->config[ 'path' ], 0775, TRUE ) )
			{
				throw new \Exception( "Logger: Logs path '" . $this->config[ 'path' ] . "' is not a directory, doesn't exist or cannot be created." );
			}
		}
		elseif ( ! is_writable( $this->config[ 'path' ] ) )
		{
			throw new \Exception( "Logger: Logs path '" . $this->config[ 'path' ] . "' is not writable by the PHP process." );
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Interesting events.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function info( $message )
	{
		return $this->write( Logger::INFO, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function error( $message )
	{
		return $this->write( Logger::ERROR, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function debug( $message )
	{
		return $this->write( Logger::DEBUG, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function notice( $message )
	{
		return $this->write( Logger::NOTICE, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function warning( $message )
	{
		return $this->write( Logger::WARNING, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function alert( $message )
	{
		return $this->write( Logger::ALERT, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function emergency( $message )
	{
		return $this->write( Logger::EMERGENCY, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function critical( $message )
	{
		return $this->write( Logger::CRITICAL, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Write logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 *
	 * @return bool
	 */
	public function write( $level, $message )
	{
		if ( $this->config[ 'threshold' ] == 0 )
		{
			return FALSE;
		}

		if ( is_array( $this->config[ 'threshold' ] ) )
		{
			if ( ! in_array( $level, $this->config[ 'threshold' ] ) )
			{
				return FALSE;
			}
		}
		elseif ( $this->config[ 'threshold' ] !== Logger::ALL )
		{
			if ( ! is_string( $level ) && $level > $this->config[ 'threshold' ] )
			{
				return FALSE;
			}
		}

		if ( is_numeric( $level ) )
		{
			$level = $this->_levels[ $level ];
		}
		else
		{
			$level = strtoupper( $level );
		}

		$filepath = $this->config[ 'path' ] . 'log-' . date( 'd-m-Y' ) . '.log';
		$log      = '';

		if ( ! is_file( $filepath ) )
		{
			$newfile = TRUE;
		}

		if ( ! $fp = @fopen( $filepath, 'ab' ) )
		{
			return FALSE;
		}

		$log .= $level . ' - ' . date( 'r' ) . ' --> ' . $message . "\n";

		flock( $fp, LOCK_EX );

		for ( $written = 0, $length = strlen( $log ); $written < $length; $written += $result )
		{
			if ( ( $result = fwrite( $fp, substr( $log, $written ) ) ) === FALSE )
			{
				break;
			}
		}

		flock( $fp, LOCK_UN );
		fclose( $fp );

		if ( isset( $newfile ) AND $newfile === TRUE )
		{
			chmod( $filepath, 0664 );
		}

		return is_int( $result );
	}
}