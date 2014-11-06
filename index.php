<?php
/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 *
 */
	define('ENVIRONMENT', 'development');

	define('BASEAPP', 'site');
/*
 *---------------------------------------------------------------
 * APPLICATION PATH
 *---------------------------------------------------------------
 */
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (realpath('system') !== FALSE)
	{
		$system_path = realpath('system').'/';
	}

	// ensure there's a trailing slash
	$base_path = pathinfo(__FILE__, PATHINFO_DIRNAME).'/';
	$system_path = rtrim($system_path, '/').'/';

	define('BASEPATH', str_replace("\\", "/", $base_path));

	// Path to the system folder
	define('SYSPATH', str_replace("\\", "/", $system_path));

	// Path to the Apps folder
	define('APPSPATH', BASEPATH.'apps/');
/*
 * --------------------------------------------------------------------
 * LOAD O2SYSTEM
 * --------------------------------------------------------------------
 */
require_once SYSPATH.'core/O2System.php';

/* End of file index.php */
/* Location: ./index.php */