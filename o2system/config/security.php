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
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

return array(
	/**
	 * List of sanitize filename strings
	 *
	 * @var    array
	 */
	'filename_bad_characters' => array(
		'../', '<!--', '-->', '<', '>',
		"'", '"', '&', '$', '#',
		'{', '}', '[', ']', '=',
		';', '?', '%20', '%22',
		'%3c',        // <
		'%253c',    // <
		'%3e',        // >
		'%0e',        // >
		'%28',        // (
		'%29',        // )
		'%2528',    // (
		'%26',        // &
		'%24',        // $
		'%3f',        // ?
		'%3b',        // ;
		'%3d'        // =
	),

	// ------------------------------------------------------------------------

	/**
	 * List of never allowed strings
	 *
	 * @var    array
	 */
	'never_allowed_strings'   => array(
		'document.cookie' => '[removed]',
		'document.write'  => '[removed]',
		'.parentNode'     => '[removed]',
		'.innerHTML'      => '[removed]',
		'-moz-binding'    => '[removed]',
		'<!--'            => '&lt;!--',
		'-->'             => '--&gt;',
		'<![CDATA['       => '&lt;![CDATA[',
		'<comment>'       => '&lt;comment&gt;',
	),

	// ------------------------------------------------------------------------

	/**
	 * List of never allowed regex replacements
	 *
	 * @var    array
	 */
	'never_allowed_regex'     => array(
		'javascript\s*:',
		'(document|(document\.)?window)\.(location|on\w*)',
		'expression\s*(\(|&\#40;)', // CSS and IE
		'vbscript\s*:', // IE, surprise!
		'wscript\s*:', // IE
		'jscript\s*:', // IE
		'vbs\s*:', // IE
		'Redirect\s+30\d',
		"([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?",
	),

	// ------------------------------------------------------------------------

	/**
	 * List of naughty html tags
	 *
	 * @var    array
	 */
	'naughty_tags'            => array(
		'alert', 'prompt', 'confirm', 'applet', 'audio', 'basefont', 'base', 'behavior', 'bgsound',
		'blink', 'body', 'embed', 'expression', 'form', 'frameset', 'frame', 'head', 'html', 'ilayer',
		'iframe', 'input', 'button', 'select', 'isindex', 'layer', 'link', 'meta', 'keygen', 'object',
		'plaintext', 'style', 'script', 'textarea', 'title', 'math', 'video', 'svg', 'xml', 'xss',
	),

	// ------------------------------------------------------------------------

	/**
	 * List of evil html tags attributes
	 *
	 * @var    array
	 */
	'evil_attributes'         => array(
		'on\w+', 'style', 'xmlns', 'formaction', 'form', 'xlink:href', 'FSCommand', 'seekSegmentTime',
	),

	// ------------------------------------------------------------------------

	/**
	 * List of sql commands
	 *
	 * @var    array
	 */
	'sql_injection_commands'  => array(
		'union',
		'sql',
		'mysql',
		'database',
		'cookie',
		'coockie',
		'select',
		'from',
		'where',
		'benchmark',
		'concat',
		'table',
		'into',
		'by',
		'values',
		'exec',
		'shell',
		'truncate',
		'wget',
		'/**/',
	),

	// ------------------------------------------------------------------------

	/**
	 * List of proxy detection keys
	 *
	 * @var    array
	 */
	'proxy_detection_keys'    => array(
		'HTTP_VIA',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_FORWARDED',
		'HTTP_CLIENT_IP',
		'HTTP_FORWARDED_FOR_IP',
		'VIA',
		'X_FORWARDED_FOR',
		'FORWARDED_FOR',
		'X_FORWARDED',
		'FORWARDED',
		'CLIENT_IP',
		'FORWARDED_FOR_IP',
		'HTTP_PROXY_CONNECTION',
		'HTTP_PC_REMOTE_ADDR',
		'HTTP_X_IMFORWARDS',
		'HTTP_XROXY_CONNECTION',
	),

	// ------------------------------------------------------------------------

	/**
	 * List of DNSBL Spam Lookup
	 *
	 * @var    array
	 */
	'dnsbl_spam_lookup'       => array(
		'dnsbl.solid.net',
		'dnsbl-1.uceprotect.net',
		'dnsbl-2.uceprotect.net',
		'dnsbl-3.uceprotect.net',
		'dnsbl.dronebl.org',
		'dnsbl.sorbs.net',
		'zen.spamhaus.org',
	),
);