<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.2.4 or newer
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
 * @package        Applications
 * @author         YukBisnis Developer Team
 * @copyright      Copyright (c) 2005 - 2014, PT. YukBisnis Indonesia
 * @license        http://www.yukbisnis.com/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://www.yukbisnis.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

/*
|--------------------------------------------------------------------------
| Base Host Domain
|--------------------------------------------------------------------------
|
| Base Host Domain to your application
|
|   example.com
|
| If you're working with multiple domains routing, you must set the base
| host domain.
*/
$config[ 'domain' ] = '';

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your application root. Typically this will be your base URL,
| WITH a trailing slash:
|
|   http://example.com/
|
| If this is not set then O2System will try guess the protocol, domain
| and path to your installation. However, you should always configure this
| explicitly and never rely on auto-guessing, especially in production
| environments.
|
*/
$config[ 'base_url' ] = '';

/*
|--------------------------------------------------------------------------
| Index File
|--------------------------------------------------------------------------
|
| Typically this will be your index.php file, unless you've renamed it to
| something else. If you are using mod_rewrite to remove the page set this
| variable so that it is blank.
|
*/
$config[ 'index_page' ] = '';

/*
|-------------------------------------------------------------------------------------------------------
| Default Encryption Key
|-------------------------------------------------------------------------------------------------------
*/
$config[ 'encryption_key' ] = 'O2Syst3m';

/*
|-------------------------------------------------------------------------------------------------------
| Default Language
|-------------------------------------------------------------------------------------------------------
|
| This determines which set of language files should be used. Make sure
| there is an available translation if you intend to use something other
| than english.
|
*/
$config[ 'language' ] = 'en';
$config[ 'currency' ] = 'USD';
$config[ 'weight' ]   = 'Gr';
/*
|-------------------------------------------------------------------------------------------------------
| Default Character Set
|-------------------------------------------------------------------------------------------------------
|
| This determines which character set is used by default in various methods
| that require a character set to be provided.
|
*/
$config[ 'charset' ] = 'UTF-8';

/*
|-------------------------------------------------------------------------------------------------------
| Master Time Reference
|-------------------------------------------------------------------------------------------------------
|
| Options are 'local' or 'gmt'.  This pref tells the system whether to use
| your server's local time as the master 'now' reference, or convert it to
| GMT.  See the 'date helper' page of the user guide for information
| regarding date handling.
|
*/
$config[ 'timezone' ] = 'Asia/Jakarta';
$config[ 'time_reference' ] = 'local';

/*
|-------------------------------------------------------------------------------------------------------
| Reverse Proxy IPs
|-------------------------------------------------------------------------------------------------------
|
| If your server is behind a reverse proxy, you must whitelist the proxy IP
| addresses from which O2System should trust the HTTP_X_FORWARDED_FOR
| header in order to properly identify the visitor's IP address.
| Comma-delimited, e.g. '10.0.1.200,10.0.1.201'
|
*/
$config[ 'proxy_ips' ] = '';

/*
|-------------------------------------------------------------------------------------------------------
| Debug IPs
|-------------------------------------------------------------------------------------------------------
|
| Allowed debug ip
|
*/
$config[ 'debug_ips' ] = '';

/*
|-------------------------------------------------------------------------------------------------------
| Enable/Disable System Hooks
|-------------------------------------------------------------------------------------------------------
|
| If you would like to use the 'hooks' feature you must enable it by
| setting this variable to TRUE (boolean).  See the user guide for details.
|
*/
$config[ 'enable_hooks' ] = FALSE;

/*
|-------------------------------------------------------------------------------------------------------
| Applications Namespace
|-------------------------------------------------------------------------------------------------------
|
| This item allows you to set the filename/classname prefix when extending
| native libraries.  For more information please see the user guide:
|
| http://o2system.in/user-guide/general/class-namespace.html
|
*/
$config[ 'namespace' ] = 'Applications\\';

/*
|-------------------------------------------------------------------------------------------------------
| Allowed GET
|-------------------------------------------------------------------------------------------------------
|
| Determines allowing GET array from URL
|
*/
$config[ 'input' ][ 'allowed_get' ] = TRUE;

/*
|-------------------------------------------------------------------------------------------------------
| Global XSS Filtering
|-------------------------------------------------------------------------------------------------------
|
| Determines whether the XSS filter is always active when GET, POST or
| COOKIE data is encountered
|
*/
$config[ 'input' ][ 'xss_filtering' ] = TRUE;

/*
|-------------------------------------------------------------------------------------------------------
| Standardize newlines
|-------------------------------------------------------------------------------------------------------
|
| Determines whether to standardize newline characters in input data,
| meaning to replace \r\n, \r, \n occurences with the PHP_EOL value.
|
| This is particularly useful for portability between UNIX-based OSes,
| (usually \n) and Windows (\r\n).
|
*/
$config[ 'input' ][ 'standardize_newlines' ] = TRUE;

/*
|-------------------------------------------------------------------------------------------------------
| URI PROTOCOL
|-------------------------------------------------------------------------------------------------------
|
| This item determines which server global should be used to retrieve the
| URI string.  The default setting of 'AUTO' works for most servers.
| If your links do not seem to work, try one of the other delicious flavors:
|
| 'AUTO'			Default - auto detects
| 'PATH_INFO'		Uses the PATH_INFO
| 'QUERY_STRING'	Uses the QUERY_STRING
| 'REQUEST_URI'		Uses the REQUEST_URI
| 'ORIG_PATH_INFO'	Uses the ORIG_PATH_INFO
|
*/
$config[ 'URI' ][ 'protocol' ] = 'AUTO';

/*
|-------------------------------------------------------------------------------------------------------
| URL suffix
|-------------------------------------------------------------------------------------------------------
|
| This option allows you to add a suffix to all URLs generated by O2System.
| For more information please see the user guide:
|
| http://application.com/user_guide/general/urls.html
*/

$config[ 'URI' ][ 'suffix' ] = '';

/*
|-------------------------------------------------------------------------------------------------------
| Allowed URL Characters
|-------------------------------------------------------------------------------------------------------
|
| This lets you specify with a regular expression which characters are permitted
| within your URLs.  When someone tries to submit a URL with disallowed
| characters they will get a warning message.
|
| As a security measure you are STRONGLY encouraged to restrict URLs to
| as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-
|
| Leave blank to allow all characters -- but only if you are insane.
|
| DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
|
*/
$config[ 'URI' ][ 'permitted_chars' ] = 'a-z 0-9~%.:_\-@';

/*
|-------------------------------------------------------------------------------------------------------
| Directory and File Permissions
|-------------------------------------------------------------------------------------------------------
|
| The file and directory permissions to be applied on newly created directory or files.
|
| IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
|            integer notation (i.e. 0700, 0644, etc.)
*/
$config[ 'permissions' ][ 'folder' ] = 0755;
$config[ 'permissions' ][ 'file' ] = 0755;

/*
|-------------------------------------------------------------------------------------------------------
| Error Logging Threshold
|-------------------------------------------------------------------------------------------------------
|
| If you have enabled error logging, you can set an error threshold to
| determine what gets logged. Threshold options are:
| You can enable error logging by setting a threshold over zero. The
| threshold determines what gets logged. Threshold options are:
|
    L_DISABLED  = 0;
    L_DEBUG     = 1;
    L_INFO      = 2;
    L_NOTICE    = 3;
    L_WARNING   = 4;
    L_ALERT     = 5;
    L_ERROR     = 6;
    L_EMERGENCY = 7;
    L_CRITICAL  = 8;
    L_ALL       = 9;
|
| For a live site you'll usually only enable Errors (1) to be logged otherwise
| your log files will fill up very fast.
|
*/
$config[ 'log' ][ 'threshold' ] = O2System::L_ALL;

/*
|-------------------------------------------------------------------------------------------------------
| Error Logging Directory Path
|-------------------------------------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| application/logs/ folder. Use a full server path with trailing slash.
|
*/
$config[ 'log' ][ 'path' ] = APPSPATH . 'logs/';

/*
|-------------------------------------------------------------------------------------------------------
| Date Format for Logs
|-------------------------------------------------------------------------------------------------------
|
| Each item that is logged has an associated date. You can use PHP date
| codes to set your own date formatting
|
*/
$config[ 'log' ][ 'date_format' ] = 'Y-m-d H:i:s';

/*
|-------------------------------------------------------------------------------------------------------
| Cache Configuration
|
| $config[ 'cache' ][ 'storage' ]       The view cache storage driver setting
| $config[ 'cache' ][ 'files' ]         By default the path would be set to APPSPATH.'cache/'
| $config[ 'cache' ][ 'compiler' ]      Some driver need this path to be set.
| $config[ 'cache' ][ 'lifetime' ]      The cache lifetime
|-------------------------------------------------------------------------------------------------------
*/

$config[ 'cache' ][ 'storage' ] = array(
	'driver'   => 'redis',
	'socket'   => 'tcp', // 'tcp' or 'unix'
	'host'     => '127.0.0.1',
	'port'     => 6379,
	'password' => NULL, // (optional)
	'timeout'  => 5,
);

$config[ 'cache' ][ 'failover' ] = array(
	'driver' => 'files',
	'path'   => APPSPATH . 'cache' . DIRECTORY_SEPARATOR,
);

$config[ 'cache' ][ 'lifetime' ] = 7200;

/*
|-------------------------------------------------------------------------------------------------------
| Session Variables
|-------------------------------------------------------------------------------------------------------
|
| $config[ 'session' ][ 'name' ]            The session cookie name, must contain only [0-9a-z_-] characters
| $config[ 'session' ][ 'lifetime' ]        The number of SECONDS you want the session to last.
|	                                        Setting to 0 (zero) means expire when the browser is closed.
|
| $config[ 'session' ][ 'update_time' ]     How many seconds between session regenerating the session ID.
|
| $config[ 'session' ][ 'match_ip' ]        Whether to match the user's IP address when reading the session data.
|
| $config[ 'session' ][ 'regenerate' ]      Whether to destroy session data associated with the old session ID
|	                                        when auto-regenerating the session ID. When set to FALSE, the data
|	                                        will be later deleted by the garbage collector.
|-------------------------------------------------------------------------------------------------------
*/
$config[ 'session' ][ 'driver' ] = 'redis';
$config[ 'session' ][ 'name' ] = 'o2_session';
$config[ 'session' ][ 'save_path' ] = $config[ 'cache' ][ 'storage' ];
$config[ 'session' ][ 'lifetime' ] = 3600;
$config[ 'session' ][ 'regenerate_time' ] = 300;
$config[ 'session' ][ 'regenerate_id' ] = FALSE;
$config[ 'session' ][ 'match_ip' ] = TRUE;

/*
|-------------------------------------------------------------------------------------------------------
| Cookie Related Variables
|-------------------------------------------------------------------------------------------------------
|
| $config[ 'cookie' ][ 'lifetime' ]     The number of SECONDS you want the cookie to last.
| $config[ 'cookie' ][ 'prefix' ]       Set a cookie name prefix if you need to avoid collisions
| $config[ 'cookie' ][ 'domain' ]       Set to .your-domain.com for site-wide cookies
| $config[ 'cookie' ][ 'path' ]         Typically will be a forward slash
| $config[ 'cookie' ][ 'secure' ]       Cookie will only be set if a secure HTTPS connection exists.
| $config[ 'cookie' ][ 'httponly' ]     Cookie will only be accessible via HTTP(S) (no javascript)
|
| Note: These settings (with the exception of 'cookie_prefix' and 'cookie_httponly')
|       will also affect sessions.
|
*/
$config[ 'cookie' ][ 'prefix' ] = 'o2_';
$config[ 'cookie' ][ 'lifetime' ] = 7200;
$config[ 'cookie' ][ 'domain' ] = '.' . isset( $_SERVER[ 'HTTP_HOST' ] ) ? @$_SERVER[ 'HTTP_HOST' ] : @$_SERVER[ 'SERVER_NAME' ];
$config[ 'cookie' ][ 'path' ] = '/';
$config[ 'cookie' ][ 'secure' ] = FALSE;
$config[ 'cookie' ][ 'httponly' ] = TRUE;

/*
|-------------------------------------------------------------------------------------------------------
| Cross Site Request Forgery
|-------------------------------------------------------------------------------------------------------
| Enables a CSRF cookie token to be set. When set to TRUE, token will be
| checked on a submitted form. If you are accepting user data, it is strongly
| recommended CSRF protection be enabled.
|
| $config[ 'csrf' ][ 'protection' ]     Enable or disabled CSRF protection
| $config[ 'csrf' ][ 'token_name' ]     CSRF token name
| $config[ 'csrf' ][ 'cookie_name' ]    CSRF cookie name
| $config[ 'csrf' ][ 'lifetime' ]       The number of SECONDS you want the CSRF token to expire.
| $config[ 'csrf' ][ 'regenerate' ]     Regenerate CSRF token on every submission
| $config[ 'csrf' ][ 'exclude_uris' ]   Array of URIs which ignore CSRF checks
|
*/
$config[ 'csrf' ][ 'protection' ] = FALSE;
$config[ 'csrf' ][ 'token_name' ] = 'o2_csrf';
$config[ 'csrf' ][ 'cookie_name' ] = 'csrf';
$config[ 'csrf' ][ 'lifetime' ] = 7200;
$config[ 'csrf' ][ 'regenerate' ] = TRUE;
$config[ 'csrf' ][ 'exclude_uris' ] = array();

$config[ 'upload' ][ 'path' ] = APPSPATH . 'upload' . DIRECTORY_SEPARATOR;