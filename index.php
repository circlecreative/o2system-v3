<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * @package        O2System
 * @author         O2System Developer Team (http://circle-creative.com)
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://o2system.in/license.html
 * @license        http://opensource.org/licenses/MIT   MIT License
 * @link           http://o2system.in
 * @since          Version 2.0
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
    if( ! defined( 'ENVIRONMENT' ) )
    {
        define( 'ENVIRONMENT', 'development' );
    }

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
    if( ! defined( 'DIR_SYSTEM' ) )
    {
        define( 'DIR_SYSTEM', 'o2system' );
    }

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
    if( ! defined( 'DIR_APPLICATIONS' ) )
    {
        define( 'DIR_APPLICATIONS', 'applications' );
    }

/*
 *---------------------------------------------------------------
 * DEFINE ROOT PATH
 *---------------------------------------------------------------
 */
    define( 'ROOTPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR );

/*
 * --------------------------------------------------------------------
 * LOAD O2SYSTEM FRAMEWORK
 * --------------------------------------------------------------------
 */
    require_once ROOTPATH . DIR_SYSTEM . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'O2System.php';

/*
 * ------------------------------------------------------
 *  BOOT O2SYSTEM FRAMEWORK
 * ------------------------------------------------------
 */
    if( class_exists('O2System', FALSE) )
    {
        ( new O2System )->boot();
    }
