<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 11:22 PM
 */

namespace O2System\Core;

use O2System\Core;

/**
 * Class Logger
 *
 * @package O2System\Glob
 */
class Logger extends \O2System\Gears\Logger
{
	/**
	 * Logger constructor.
	 *
	 * @param array $config
	 */
	public function __construct()
	{
		parent::__construct( \O2System::$config[ 'log' ]->getArrayCopy() );
	}

	/**
	 * Interesting events.
	 *
	 * @param string $message Log message
	 * @param array  $args    Log sprintf args
	 *
	 * @return bool
	 */
	public function info( $message, array $args = [ ] )
	{
		return $this->write( Logger::INFO, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message Log message
	 * @param array  $args    Log sprintf args
	 *
	 * @return bool
	 */
	public function error( $message, array $args = [ ] )
	{
		return $this->write( Logger::ERROR, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Detailed debug information.
	 *
	 * @param string $message Log message
	 * @param array  $args    Log sprintf args
	 *
	 * @return bool
	 */
	public function debug( $message, array $args = [ ] )
	{
		return $this->write( Logger::DEBUG, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Normal but significant events.
	 *
	 * @param string $message Log message
	 * @param array  $args    Log sprintf args
	 *
	 * @return bool
	 */
	public function notice( $message, array $args = [ ] )
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
	 * @param string $message Log message
	 * @param array  $args    Log sprintf args
	 *
	 * @return bool
	 */
	public function warning( $message, array $args = [ ] )
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
	 * @param string $message Log message
	 * @param array  $args    Log sprintf args
	 *
	 * @return bool
	 */
	public function alert( $message, array $args = [ ] )
	{
		return $this->write( Logger::ALERT, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * System is unusable.
	 *
	 * @param string $message Log message
	 * @param array  $args    Log sprintf args
	 *
	 * @return bool
	 */
	public function emergency( $message, array $args = [ ] )
	{
		return $this->write( Logger::EMERGENCY, $message );
	}

	// --------------------------------------------------------------------

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message Log message
	 * @param array  $args    Log sprintf args
	 *
	 * @return bool
	 */
	public function critical( $message, array $args = [ ] )
	{
		return $this->write( Logger::CRITICAL, $message );
	}

	protected function __parseMessage( $message, array $args = [ ] )
	{
		if ( isset( \O2System::$language ) AND \O2System::$language instanceof Core\Language )
		{
			return \O2System::$language->line( $message, $args );
		}

		return $message;
	}
}