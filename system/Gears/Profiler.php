<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 10-Aug-16
 * Time: 4:56 PM
 */

namespace O2System\Gears;


class Profiler
{
	protected        $_application_name = 'O2System-Gears-Profiler';
	protected        $_sample_size      = 1;
	protected static $_enabled          = FALSE;

	public function __construct( $application_name = NULL )
	{
		include_once __DIR__ . '/xhprof/utils/xhprof_lib.php';
		include_once __DIR__ . '/xhprof/utils/xhprof_runs.php';
	}

	public function setApplicationName( $application_name )
	{
		$this->_application_name = $application_name;

		return $this;
	}

	public function start( $application_name = NULL )
	{
		if ( isset( $application_name ) )
		{
			$this->setApplicationName( $application_name );
		}

		if ( mt_rand( 1, $this->_sample_size == 1 ) AND isset( $_GET[ 'XHPROF_ENABLED' ] ) )
		{
			xhprof_enable();

			self::$_enabled = TRUE;
		}
	}

	public function end()
	{
		if ( self::$_enabled )
		{
			$XHProfData = xhprof_disable();

			$XHProfRuns = new \XHProfRuns_Default();
			$XHProfRuns->saveRun( $XHProfData, $this->_application_name );
		}
	}
}