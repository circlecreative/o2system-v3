<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package		O2System
 * @author		Steeven Andrian Salim
 * @copyright	Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license		http://circle-creative.com/products/o2system/license.html
 * @license	    http://opensource.org/licenses/MIT	MIT License
 * @link		http://circle-creative.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

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

/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" folder.
 * Include the path if the folder is not in the same directory
 * as this file.
 *
 * NO TRAILING SLASH!
 */
	$system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATIONS FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "apps" folder.
 * This is the folder name where all your apps is placed.
 *
 * NO TRAILING SLASH!
 */
	$apps_path = 'apps';	

/*
 *---------------------------------------------------------------
 * APPLICATION DEFAULT
 *---------------------------------------------------------------
 *
 * You can define the default base application which is loaded at first
 * for example: for developed website with separated content management system
 * you can use site as default and the cms can be accessed by request based on URL request.
 *
 */
	define('BASEAPP', 'site');	

/*
 *---------------------------------------------------------------
 * APPLICATION PATH
 *---------------------------------------------------------------
 */
	// Path to base path folder
	$base_path = pathinfo(__FILE__, PATHINFO_DIRNAME).'/';
	define('BASEPATH', str_replace("\\", '/', $base_path));

	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	$_system_path = BASEPATH.$system_path.'/';

	// Is the system path correct?
	if ( ! is_dir($_system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

	// Path to the system folder
	define('SYSPATH', str_replace("\\", '/', $_system_path));

	// Is the apps path correct?
	$_apps_path = BASEPATH . $apps_path . '/';

	if ( ! is_dir($_apps_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your apps folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

	// Path to the Apps folder
	define('APPSPATH', $_apps_path);
/*
 * --------------------------------------------------------------------
 * LOAD O2SYSTEM
 * --------------------------------------------------------------------
 */
require_once SYSPATH.'core/O2System.php';

/* End of file index.php */
/* Location: ./index.php */