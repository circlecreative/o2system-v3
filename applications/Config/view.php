<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * @author         O2System Framework Developer Team
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://www.o2system.io/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://www.o2system.io
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );
/*
|-------------------------------------------------------------------------------------------------------
| View Parser
|
| $config[ 'parser' ][ 'driver' ]      The view parser driver to use: standard, smarty, twig, dwoo
| $config[ 'parser' ][ 'php' ]         Enable parser to parse PHP codes.
| $config[ 'parser' ][ 'markdown' ]    Enable parser to parse Markdown codes.
| $config[ 'parser' ][ 'shortcode' ]   Enable parser to parse Wordpress Like shortcodes.
|-------------------------------------------------------------------------------------------------------
*/
$view[ 'parser' ] = [
	'engine' => 'moustache',
	'cache'  => '',
	'parse'  => [
		'php'        => TRUE,
		'short_tags' => TRUE,
		'markdown'   => FALSE,
		'bbcode'     => FALSE,
		'shortcodes' => FALSE,
		'string'     => [
			'allowed_php_scripts'   => FALSE, // bool
			'allowed_php_functions' => TRUE, // bool | [ function_name, ... ]
			'allowed_php_constants' => TRUE, // bool | [ CONSTANT_NAME, ... ]
			'allowed_php_globals'   => TRUE, // bool
		],
	],
];

/*
|-------------------------------------------------------------------------------------------------------
| View Theme
|
| This determines which set of template theme files should be used.
| Leave this BLANK unless you would like to set the template theme.
|-------------------------------------------------------------------------------------------------------
*/
$view[ 'theme' ][ 'default' ] = 'default';

/*
|-------------------------------------------------------------------------------------------------------
| View Assets
|
| This determines which set of template theme files should be used.
| Leave this BLANK unless you would like to set the template theme.
|-------------------------------------------------------------------------------------------------------
*/
$view[ 'assets' ][ 'combine' ]  = FALSE;
$view[ 'assets' ][ 'autoload' ] = [
	'js'       => [
		'jquery',
		'system',
		'classie',
		'wow',
		'holder',
		'form-plugin',
		'https://js.pusher.com/3.1/pusher.min.js',
		'yukbisnis',
		'moment',
	],
	'css'      => [ 'utilities', 'fonts', 'animate', 'yubi', 'form' ],
	'fonts'    => [ 'yukbisnis', 'font-awesome', 'myriad-pro' ],
	'packages' => [
		'bootstrap',
		'pace',
		'toastr',
		'slimscroll',
		'fancybox',
	],
];

$view[ 'meta' ][ 'charset' ]         = 'UTF8';
$view[ 'meta' ][ 'title_separator' ] = '-';

/**
 * Compress HTML Output using zLib Compression
 */
$view[ 'output' ][ 'compress' ] = FALSE;

/**
 * Beautify HTML Code
 */
$view[ 'output' ][ 'beautify' ] = FALSE;

/**
 * Removes extra characters (usually unnecessary spaces)
 * from your output for faster page load speeds.
 * Makes your outputted HTML source code less readable.
 */
$view[ 'output' ][ 'minify' ]   = TRUE;

/**
 * Add profiler information at your HTML source code
 */
$view[ 'output' ][ 'profiler' ] = TRUE;

/**
 * Default cache lifetime of your output.
 * If you want to set by controller please set this to zero
 *
 * Note: zero|FALSE means no caching output
 */
$view[ 'output' ][ 'cache' ][ 'lifetime' ] = 0;