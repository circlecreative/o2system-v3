<?php
/**
 * O2System
 * 
 * Application development framework for PHP 5.1.6 or newer
 *
 * @package		O2System
 * @author		Steeven Andrian Salim (www.steevenz.com)
 * @copyright	Copyright (c) 2011, Circle Creative - Unlimited Digital Solutions.
 * @license		http://www.o2system.net/license.html
 * @link		http://www.o2system.net | http://www.circle-creative.com
 * @filesource
 */
// ------------------------------------------------------------------------
/**
 * Developer Functions
 *
 * Loads the core functions.
 *
 * @package		Developer
 * @category	Developer Functions
 * @author		Steeven Andrian Salim (www.steevenz.com)
 * @copyright	Copyright (c) 2011, Circle Creative - Unlimited Digital Solutions.
 * @license		http://www.o2system.net/license.html
 * @link		http://www.o2system.net | http://www.circle-creative.com
 */
// ------------------------------------------------------------------------

// Re-Write PHP Settings
ini_set('display_errors','on');
error_reporting(E_ALL);

// Define Developer Path
define('DEVPATH',dirname(__FILE__).'/');

/*
 * --------------------------------------------------------------------
 * Load Developer Core Libraries
 * --------------------------------------------------------------------
 */
require_once DEVPATH.'libraries/Developer.php';
//require_once DEVPATH.'libraries/Kint/Kint.php';

/*
 * --------------------------------------------------------------------
 * Start Developer
 * --------------------------------------------------------------------
 */
$DEV = $developer = new Developer();

/*
 * --------------------------------------------------------------------
 * Load Developer Core Helpers
 * --------------------------------------------------------------------
 */
require_once DEVPATH.'helpers/core_helper.php';
     


// ------------------------------------------------------------------------
/* End of file Developer.php */
/* Location: ./system/Core/Developer.php */