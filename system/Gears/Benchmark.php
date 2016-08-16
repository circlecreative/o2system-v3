<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 10-Aug-16
 * Time: 4:54 PM
 */

namespace O2System\Gears;


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
	protected $_marker = [ ];

	/**
	 * List of all benchmark time elapsed markers
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_elapsed = [ ];

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
		$this->_start_cpu = $this->_getProcessorUsage();
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
		$this->_marker[ $marker ] = [
			'time'   => $this->_start_time,
			'memory' => $this->_start_memory,
			'cpu'    => $this->_start_cpu,
		];
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
	public function elapsedTime( $marker = 'total_execution' )
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
		$this->_elapsed[ $marker ] = [
			'time'   => number_format(
				( time() + microtime( TRUE ) ) - $this->_marker[ $marker ][ 'time' ],
				$decimals
			),
			'memory' => ( memory_get_usage( TRUE ) - $this->_marker[ $marker ][ 'memory' ] ),
			'cpu'    => $this->_getProcessorUsage(),
		];
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
	public function memoryUsage( $marker = 'total_execution', $getUsage = FALSE )
	{
		if ( $getUsage === FALSE AND isset( $marker ) )
		{
			if ( empty( $this->_elapsed[ $marker ] ) )
			{
				$this->stop( $marker );
			}

			$memory = $this->_elapsed[ $marker ][ 'memory' ];
		}
		else
		{
			$memory = memory_get_usage( TRUE );
		}

		return round( $memory / 1024 / 1024, 2 ) . ' MB';
	}

	// ------------------------------------------------------------------------

	public function processorUsage( $marker = 'total_execution' )
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
	public function memoryPeakUsage()
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
	protected function _getProcessorUsage()
	{
		if ( stristr( PHP_OS, 'win' ) )
		{
			if ( class_exists( 'COM', FALSE ) )
			{
				$wmi    = new \COM( "Winmgmts://" );
				$server = $wmi->execquery( "SELECT LoadPercentage FROM Win32_Processor" );

				$cpu_num     = 0;
				$usage_total = 0;

				if ( ! empty( $server ) )
				{
					$time_start = microtime( TRUE );
					foreach ( $server as $cpu )
					{
						$cpu_num++;
						$usage_total += $cpu->loadpercentage;

						$time_end       = microtime( TRUE );
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
			$sys_load  = sys_getloadavg();
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
		$elapsed              = new \stdClass;
		$elapsed->time        = $this->elapsedTime( $marker );
		$elapsed->memory      = $this->memoryUsage( $marker );
		$elapsed->memory_peak = $this->memoryPeakUsage();
		$elapsed->processor   = $this->processorUsage( $marker );

		return $elapsed;
	}
}