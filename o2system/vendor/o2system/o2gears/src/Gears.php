<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/11/2016
 * Time: 10:11 PM
 */

namespace O2System
{
	use O2System\Gears\Console;
	use O2System\Gears\Trace;

	class Gears
	{
		protected static $_lines = array();

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
			ini_set('memory_limit', '512M');

			$vars = static::__prepareOutput( $vars );
			$vars = htmlentities( $vars );
			$vars = htmlspecialchars( htmlspecialchars_decode( $vars, ENT_QUOTES ), ENT_QUOTES, 'UTF-8' );

			$trace = new Trace();

			ob_start();

			// Load print out template
			include __DIR__ . '/views/screen.php';
			$output = ob_get_contents();
			ob_end_clean();

			echo $output;

			if ( $halt === TRUE ) die;
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
			ini_set('memory_limit', '512M');
			$vars = static::__prepareOutput( $vars );
			$vars = htmlentities( $vars );
			$vars = htmlspecialchars( htmlspecialchars_decode( $vars, ENT_QUOTES ), ENT_QUOTES, 'UTF-8' );

			echo '<pre>' . $vars . '</pre>';

			if ( $halt === TRUE ) die;
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
				static::$_lines = array();
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
				$vars = implode( PHP_EOL, static::$_lines );
				static::$_lines = array();
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
				$vars = print_r( $vars, TRUE );
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
}

namespace O2System\Gears
{

	use O2System\Glob\Interfaces\ExceptionInterface;

	class Exception extends ExceptionInterface
	{
	}

	// ------------------------------------------------------------------------

	/**
	 * Console Class
	 *
	 * This class is to gear up PHP Developer to send output to browser console
	 *
	 * @package        O2System
	 * @subpackage     core/gears
	 * @category       core class
	 * @author         Circle Creative Dev Team
	 * @link           http://o2system.center/wiki/#GearsConsole
	 */
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

					echo 'var object' . preg_replace( '~[^A-Z|0-9]~i', "_", $title ) . ' = \'' . str_replace( "'", "\'",
					                                                                                          $object ) . '\';' . PHP_EOL;
					echo 'var val' . preg_replace( '~[^A-Z|0-9]~i', "_",
					                               $title ) . ' = eval("(" + object' . preg_replace( '~[^A-Z|0-9]~i', "_",
					                                                                                 $title ) . ' + ")" );' . PHP_EOL;
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

	// ------------------------------------------------------------------------

	/**
	 * Benchmark Class
	 *
	 * This class enables you to mark points and calculate the time difference
	 * between them.  Memory consumption can also be displayed.
	 *
	 * @package        O2System
	 * @subpackage     core/gears
	 * @category       core class
	 * @author         Circle Creative Dev Team
	 * @link           http://o2system.center/wiki/#GearsBenchmark
	 */
	class Benchmark
	{
		protected $_start_time;
		protected $_start_memory;
		protected $_start_cpu;

		/**
		 * List of all benchmark markers
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_marker = array();

		/**
		 * List of all benchmark time elapsed markers
		 *
		 * @access  protected
		 * @type    array
		 */
		protected $_elapsed = array();

		// ------------------------------------------------------------------------

		public function __construct()
		{
			/*
			 *--------------------------------------------------------------
			 * Define the start time of the application, used for profiling.
			 *--------------------------------------------------------------
			 */
			$this->_start_time = microtime( TRUE );


			/*
			 *-----------------------------------------------------------------------------
			 * Define the memory usage at the start of the application, used for profiling.
			 *-----------------------------------------------------------------------------
			 */
			$this->_start_memory = memory_get_usage( TRUE );

			/*
			 *-----------------------------------------------------------------------------
			 * Define the cpu usage at the start of the application, used for profiling.
			 *-----------------------------------------------------------------------------
			 */
			$this->_start_cpu = $this->_get_cpu_usage();
		}

		/**
		 * Start
		 * Benchmark start timer marker
		 *
		 * @access  public
		 *
		 * @property-write  $_marker
		 *
		 * @param    string $marker marker name
		 */
		public function start( $marker = 'total_execution' )
		{
			$this->_marker[ $marker ] = array(
				'time'   => $this->_start_time,
				'memory' => $this->_start_memory,
				'cpu'    => $this->_start_cpu,
			);
		}
		// ------------------------------------------------------------------------

		/**
		 * Elapsed
		 * Benchmark elapsed timer marker
		 *
		 * @access    public
		 *
		 * @property-write  $_elapsed
		 *
		 * @method  $this->stop()
		 *
		 * @param    string $marker marker name
		 *
		 * @return    int   time of elapsed time
		 */
		public function elapsed_time( $marker = 'total_execution' )
		{
			if ( empty( $this->_elapsed[ $marker ] ) )
			{
				$this->stop( $marker );
			}

			return $this->_elapsed[ $marker ][ 'time' ] . ' seconds';
		}
		// ------------------------------------------------------------------------

		/**
		 * Stop
		 * Benchmark stop timer marker
		 *
		 * @access    public
		 *
		 * @property-write  $_elapsed
		 *
		 * @param   string  $marker   marker name
		 * @param   int     $decimals time number format decimals
		 */
		public function stop( $marker = 'total_execution', $decimals = 4 )
		{
			$this->_elapsed[ $marker ] = array(
				'time'   => number_format( ( time() + microtime( TRUE ) ) - $this->_marker[ $marker ][ 'time' ],
				                           $decimals ),
				'memory' => ( memory_get_usage( TRUE ) - $this->_marker[ $marker ][ 'memory' ] ),
				'cpu'    => $this->_get_cpu_usage(),
			);
		}
		// ------------------------------------------------------------------------

		/**
		 * Memory Usage
		 * Benchmark Memory Usage
		 *
		 * @access    public
		 *
		 * @property-read  $_elapsed
		 *
		 * @param   string $marker marker name
		 *
		 * @return  string  memory usage in MB
		 */
		public function memory_usage( $marker = 'total_execution' )
		{
			if ( empty( $this->_elapsed[ $marker ] ) )
			{
				$this->stop( $marker );
			}

			$memory = $this->_elapsed[ $marker ][ 'memory' ];

			return round( $memory / 1024 / 1024, 2 ) . ' MB';
		}

		// ------------------------------------------------------------------------

		public function cpu_usage( $marker = 'total_execution' )
		{
			if ( empty( $this->_elapsed[ $marker ] ) )
			{
				$this->stop( $marker );
			}

			return $this->_elapsed[ $marker ][ 'cpu' ];
		}

		/**
		 * Memory Peak Usage
		 *
		 * @access    public
		 *
		 * @return  string  memory usage in MB
		 */
		public function memory_peak_usage()
		{
			return round( memory_get_peak_usage( TRUE ) / 1024 / 1024, 2 ) . ' MB';
		}
		// ------------------------------------------------------------------------

		/**
		 * CPU Usage
		 *
		 * @access    protected
		 *
		 * @return  int
		 */
		protected function _get_cpu_usage()
		{
			if ( stristr( PHP_OS, 'win' ) )
			{
				if ( class_exists( 'COM', FALSE ) )
				{
					$wmi = new \COM( "Winmgmts://" );
					$server = $wmi->execquery( "SELECT LoadPercentage FROM Win32_Processor" );

					$cpu_num = 0;
					$usage_total = 0;

					if ( ! empty( $server ) )
					{
						$time_start = microtime( TRUE );
						foreach ( $server as $cpu )
						{
							$cpu_num++;
							$usage_total += $cpu->loadpercentage;

							$time_end = microtime( TRUE );
							$time_execution = round( $time_end - $time_start );

							if ( $time_execution > 5 )
							{
								break;
							}
						}

						$cpu_usage = round( $usage_total / $cpu_num );
					}
					else
					{
						$cpu_usage = 0;
					}
				}
				else
				{
					$cpu_usage = 0;
				}
			}
			else
			{
				$sys_load = sys_getloadavg();
				$cpu_usage = $sys_load[ 0 ];
			}

			return (int) $cpu_usage . ' hertz';
		}

		// ------------------------------------------------------------------------

		/**
		 * Get Elapsed
		 * Get all Benchmark elapsed markers
		 *
		 * @access    public
		 *
		 * @return  array   list of elapsed markers
		 */
		public function elapsed( $marker = 'total_execution' )
		{
			$elapsed = new \stdClass;
			$elapsed->time = $this->elapsed_time( $marker );
			$elapsed->memory = $this->memory_usage( $marker );
			$elapsed->memory_peak = $this->memory_peak_usage();
			$elapsed->cpu = $this->cpu_usage( $marker );

			return $elapsed;
		}
	}

	/**
	 * Debug Class
	 *
	 * This class is to gear up PHP Developer for manual debugging line by line
	 *
	 * @package        O2System
	 * @subpackage     core/gears
	 * @category       core class
	 * @author         Circle Creative Dev Team
	 * @link           http://o2system.center/wiki/#GearsDebug
	 *
	 * @static         static class
	 */
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
		private static $_chronology = array();

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
			static::$_chronology = array();
			static::$_chronology[] = static::__where_call( __CLASS__ . '::start()' );
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
		private static function __where_call( $call )
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
			$trace = static::__where_call( __CLASS__ . '::line()' );

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
			$trace = static::__where_call( __CLASS__ . '::marker()' );
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
			static::$_chronology[] = static::__where_call( __CLASS__ . '::stop()' );
			$chronology = static::$_chronology;
			static::$_chronology = array();

			Output::screen( $chronology, $halt );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Logging Class
	 *
	 * @package        O2System
	 * @subpackage     core/gears
	 * @category       core class
	 * @author         Circle Creative Dev Team
	 * @link           http://o2system.center/wiki/#GearsLogging
	 *
	 * @static         static class
	 */
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
		protected $_config = array(
			'path'        => NULL,
			'threshold'   => Logger::ALL,
			'date_format' => 'Y-m-d H:i:s',
		);

		/**
		 * List of logging levels
		 *
		 * @access protected
		 * @type array
		 */
		protected $_levels = array(
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
		);

		// --------------------------------------------------------------------

		/**
		 * Class Initialize
		 *
		 * @throws \Exception
		 */
		public function __construct( array $config = array() )
		{
			$this->_config = array_merge( $this->_config, $config );

			if ( ! is_dir( $this->_config[ 'path' ] ) )
			{
				if ( ! mkdir( $this->_config[ 'path' ], 0775, TRUE ) )
				{
					throw new \Exception( "Logger: Logs path '" . $this->_config[ 'path' ] . "' is not a directory, doesn't exist or cannot be created." );
				}
			}
			elseif ( ! is_writable( $this->_config[ 'path' ] ) )
			{
				throw new \Exception( "Logger: Logs path '" . $this->_config[ 'path' ] . "' is not writable by the PHP process." );
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
			if ( $this->_config[ 'threshold' ] == 0 )
			{
				return FALSE;
			}

			if ( is_array( $this->_config[ 'threshold' ] ) )
			{
				if ( ! in_array( $level, $this->_config[ 'threshold' ] ) )
				{
					return FALSE;
				}
			}
			elseif ( $this->_config[ 'threshold' ] !== Logger::ALL )
			{
				if ( ! is_string( $level ) && $level > $this->_config[ 'threshold' ] )
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

			$filepath = $this->_config[ 'path' ] . 'log-' . date( 'd-m-Y' ) . '.log';
			$log = '';

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

	// ------------------------------------------------------------------------

	/**
	 * Tracer Class
	 *
	 * This class is to gear up PHP Developer for Backtrace the PHP Process
	 *
	 * @package        O2System
	 * @subpackage     core/gears
	 * @category       core class
	 * @author         Circle Creative Dev Team
	 * @link           http://o2system.center/wiki/#GearsTracer
	 */
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
		private $_chronology = array();
		/**
		 * Class Name
		 *
		 * @access  protected
		 * @type    string name of called class
		 */
		private $_benchmark = array();

		// ------------------------------------------------------------------------

		/**
		 * Class Constructor
		 *
		 * @access public
		 *
		 * @param string $flag tracer option
		 */
		public function __construct( $trace = array(), $flag = Trace::PROVIDE_OBJECT )
		{
			$this->_benchmark = array(
				'time'   => time() + microtime(),
				'memory' => memory_get_usage(),
			);

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
			$this->__generate_chronology();
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
		private function __generate_chronology()
		{
			foreach ( $this->_trace as $trace )
			{
				if ( in_array( $trace[ 'function' ], [ 'showException', 'showError', 'showPhpError', 'shutdown' ] ) OR
					( isset( $trace[ 'class' ] ) AND $trace[ 'class' ] === 'O2System\Gears\Tracer' )
				)
				{
					continue;
				}

				$line = new TraceChronology();

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

				if ( ! empty( $trace[ 'args' ] ) AND $line->call !== 'print_out()' ) $line->args = $trace[ 'args' ];

				if ( ! isset( $trace[ 'file' ] ) )
				{
					$current_trace = current( $this->_trace );
					$line->file = @$current_trace[ 'file' ];
					$line->line = @$current_trace[ 'line' ];
				}
				else
				{
					$line->file = @$trace[ 'file' ];
					$line->line = @$trace[ 'line' ];
				}

				$line->time = ( time() + microtime() ) - $this->_benchmark[ 'time' ];
				$line->memory = memory_get_usage() - $this->_benchmark[ 'memory' ];

				$this->_chronology[] = $line;

				if ( in_array( $trace[ 'function' ], [ 'print_out', 'print_line' ] ) ) break;
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
				$this->_chronology = array();
			}

			return $chronology;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Chronology
	 *
	 * @package        O2System
	 * @subpackage     core/gears
	 * @category       core class
	 * @author         Circle Creative Dev Team
	 * @link           http://o2system.center/wiki/#GearsDebug
	 */
	class TraceChronology
	{
		public $call;
		public $type;
		public $line;
		public $time;
		public $memory;
		public $args;
	}

	// ------------------------------------------------------------------------

	class Profiler
	{

	}

	// ------------------------------------------------------------------------

	class TestUnit
	{

	}

	// ------------------------------------------------------------------------

	class Trackback
	{

	}
}