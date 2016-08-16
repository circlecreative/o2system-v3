<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 10-Aug-16
 * Time: 6:24 PM
 */

namespace O2System\Core\Environment\Handlers;

use O2System\Core;
use O2System\Gears\Trace;

class Exception implements Core\Environment\Interfaces\Severity
{
	use Core\Traits\Constant;
	use Core\Collectors\Paths;

	protected $obLevel = 0;

	// ------------------------------------------------------------------------

	/**
	 * ExceptionHandler constructor.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		$this->obLevel = ob_get_level();

		$this->setSubPath( 'Views' );
		$this->addPaths( [ SYSTEMPATH, APPSPATH ] );

		\O2System::$language->load( 'exception' );
	}

	// ------------------------------------------------------------------------

	/**
	 * Register Handler
	 *
	 * @access  public
	 */
	public function registerHandler()
	{
		set_exception_handler( [ $this, 'showException' ] );
		register_shutdown_function( [ $this, 'shutdown' ] );
		set_error_handler( [ $this, 'showError' ] );
	}

	// ------------------------------------------------------------------------

	/**
	 * Show Exception
	 *
	 * @param \Exception $exception
	 */
	public function showException( \Exception $exception )
	{
		if ( method_exists( $exception, 'getViewFile' ) )
		{
			$view_filename = $exception->getViewFile();
		}

		$view_filename = empty( $view_filename ) ? 'exception.php' : $view_filename;

		foreach ( $this->getPaths( 'views/exceptions' ) as $path )
		{
			$path = is_cli() ? $path . 'cli' . DIRECTORY_SEPARATOR : $path . 'html' . DIRECTORY_SEPARATOR;

			if ( is_file( $path . $view_filename ) )
			{
				$view = $path . $view_filename;
				break;
			}
		}

		if ( \O2System::$environment->isDebugStage( 'PRODUCTION' ) )
		{
			if ( \O2System::$request->uri->hasSegment( 'error' ) === FALSE )
			{
				redirect( 'error/500', 'refresh' );
			}
		}

		if ( empty( $exception->library ) )
		{
			$exception->library = [
				'name'        => 'O2System Framework',
				'description' => 'Open Source PHP Framework',
				'version'     => SYSTEM_VERSION,
			];
		}

		if ( ! isset( $exception->header ) )
		{
			\O2System::$language->load( 'exception' );
			$common_description     = \O2System::$language->line( 'E_DESCRIPTION_COMMON' );
			$exception->header      = \O2System::$language->line( 'E_HEADER_' . get_class_name( $exception ) );
			$exception->description = \O2System::$language->line( 'E_DESCRIPTION_' . get_class_name( $exception ) );
		}

		if ( empty( $exception->header ) )
		{
			$exception->header = get_class( $exception );
		}

		if ( empty( $exception->description ) )
		{
			if ( isset( $common_description ) )
			{
				$exception->description = sprintf( $common_description, get_class( $exception ) );
			}
		}

		$backtrace = new Trace( $exception->getTrace() );

		if ( ob_get_level() > $this->obLevel + 1 )
		{
			ob_end_flush();
		}

		ob_start();
		include( $view );
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

	// ------------------------------------------------------------------------

	/**
	 * Show 404
	 *
	 * @param string    $page
	 * @param bool|TRUE $log_error
	 */
	public function show404( $page = '', $log_error = TRUE )
	{
		$header = new Core\HTTP\Header();
		$header->setStatusCode( Core\HTTP\Header::STATUS_NOT_FOUND );

		$heading = $header->getStatusHeader( Core\HTTP\Header::STATUS_NOT_FOUND );
		$message = $header->getStatusDescription( Core\HTTP\Header::STATUS_NOT_FOUND );

		print_out( __METHOD__ );

		// By default we log this, but allow a dev to skip it
		if ( $log_error )
		{
			$this->logException( E_ERROR, $heading . ': ' . $page );
		}

		$paths = array_reverse( $this->_paths );

		foreach ( $paths as $path )
		{
			$path = $path . 'errors' . DIRECTORY_SEPARATOR;
			$path = is_cli() ? $path . 'cli' . DIRECTORY_SEPARATOR : $path . 'html' . DIRECTORY_SEPARATOR;

			if ( is_file( $path . 'error.php' ) )
			{
				$view = $path . 'error.php';
			}
		}

		if ( ob_get_level() > $this->obLevel + 1 )
		{
			ob_end_flush();
		}

		ob_start();
		include( $view );
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;

		exit( 4 ); // EXIT_UNKNOWN_FILE
	}

	// ------------------------------------------------------------------------

	/**
	 * Log Exception
	 *
	 * @param $severity
	 * @param $message
	 * @param $file
	 * @param $line
	 */
	public function logException( $severity, $message, $file, $line )
	{
		$messages[] = '[ ' . static::getConstant( $severity ) . ' ] ';
		$messages[] = $message;
		$messages[] = $file . ' at line ' . $line;

		\O2System::$log->error( implode( ' ', $messages ) );
	}

	// ------------------------------------------------------------------------

	/**
	 * Shutdown Handler
	 *
	 * This is the shutdown handler to simulate
	 * a complete custom exception handler.
	 *
	 * E_STRICT is purposivly neglected because such events may have
	 * been caught. Duplication or none? None is preferred for now.
	 *
	 * @link    http://insomanic.me.uk/post/229851073/php-trick-catching-fatal-errors-e-error-with-a
	 * @return    void
	 */
	public function shutdown()
	{
		/*if ( NULL !== ( $error = error_get_last() ) )
		{
			$this->showError( $error[ 'type' ], $error[ 'message' ], $error[ 'file' ], $error[ 'line' ] );
		}*/

		$last_error = error_get_last();

		if ( isset( $last_error ) &&
			( $last_error[ 'type' ] & ( E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING ) )
		)
		{
			$this->showError( $last_error[ 'type' ], $last_error[ 'message' ], $last_error[ 'file' ], $last_error[ 'line' ] );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Show Error
	 *
	 * This is the custom error handler that is declared at the (relative)
	 *  The main reason we use this is to permit
	 * PHP errors to be logged in our own log files since the user may
	 * not have access to server logs. Since this function effectively
	 * intercepts PHP errors, however, we also need to display errors
	 * based on the current error_reporting level.
	 * We do that with the use of a PHP error template.
	 *
	 * @param    int    $severity
	 * @param    string $message
	 * @param    string $filepath
	 * @param    int    $line
	 *
	 * @return    void
	 */
	public function showError( $severity, $message, $filepath, $line )
	{
		$is_error = ( ( ( E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR ) & $severity ) === $severity );

		// When an error occurred, set the status header to '500 Internal Server Error'
		// to indicate to the client something went wrong.
		// This can't be done within the $this->showPhpError method because
		// it is only called when the display_errors flag is set (which isn't usually
		// the case in a production environment) or when errors are ignored because
		// they are above the error_reporting threshold.
		if ( $is_error )
		{
			$header = new Core\HTTP\Header();
			$header->setStatusCode( Core\HTTP\Header\Status::STATUS_INTERNAL_SERVER_ERROR );
			$header->send();
		}

		// Should we ignore the error? We'll get the current error_reporting
		// level and add its bits with the severity bits to find out.
		if ( ( $severity & error_reporting() ) !== $severity )
		{
			return;
		}

		$this->logException( $severity, $message, $filepath, $line );

		// Should we display the error?
		if ( str_ireplace( [ 'off', 'none', 'no', 'false', 'null' ], '', ini_get( 'display_errors' ) ) )
		{
			$this->showPhpError( $severity, $message, $filepath, $line );
		}

		// If the error is fatal, the execution of the script should be stopped because
		// errors can't be recovered from. Halting the script conforms with PHP's
		// default error handling. See http://www.php.net/manual/en/errorfunc.constants.php
		if ( $is_error )
		{
			exit( 1 ); // EXIT_ERROR
		}

		$this->showPhpError( $severity, $message, $filepath, $line );
	}

	// ------------------------------------------------------------------------

	/**
	 * Show PHP Error
	 *
	 * @param $severity
	 * @param $message
	 * @param $filepath
	 * @param $line
	 */
	public function showPhpError( $severity, $message, $filepath, $line )
	{
		foreach ( $this->getPaths( 'views/errors' ) as $path )
		{
			$path = is_cli() ? $path . 'cli' . DIRECTORY_SEPARATOR : $path . 'html' . DIRECTORY_SEPARATOR;

			if ( is_file( $path . 'error.php' ) )
			{
				$view = $path . 'error.php';
			}
		}

		$severity = static::getConstant( $severity );

		$backtrace = new Trace( debug_backtrace() );

		if ( ob_get_level() > $this->obLevel + 1 )
		{
			ob_end_flush();
		}

		ob_start();
		include( $view );
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
}