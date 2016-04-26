<?php
/**
 * O2CURL
 *
 * Lightweight HTTP Request Libraries for PHP 5.4+
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
 * @package     o2curl
 * @author      O2System Developer Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2curl/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2curl.html
 */
// ------------------------------------------------------------------------

namespace O2System\CURL\Interfaces;

// ------------------------------------------------------------------------

/**
 * CURL HTTP Method Registry
 *
 * http://www.iana.org/assignments/http-methods/http-methods.xhtml
 *
 * @package          o2curl
 * @subpackage       interfaces
 * @category         interfaces
 * @version          1.0
 * @author           O2System Developer Team
 * @copyright        Copyright (c) 2005 - 2014
 * @license          http://circle-creative.com/products/o2curl/license.html
 * @link             http://www.iana.org/assignments/http-methods/http-methods.xhtml
 */
interface Method
{
    // RFC7231
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const CONNECT = 'CONNECT';
    const OPTIONS = 'OPTIONS';
    const TRACE = 'TRACE';

    // RFC3253
    const BASELINE = 'BASELINE';

    // RFC2068
    const LINK = 'LINK';
    const UNLINK = 'UNLINK';

    // RFC3253
    const MERGE = 'MERGE';
    const BASELINECONTROL = 'BASELINE-CONTROL';
    const MKACTIVITY = 'MKACTIVITY';
    const VERSIONCONTROL = 'VERSION-CONTROL';
    const REPORT = 'REPORT';
    const CHECKOUT = 'CHECKOUT';
    const CHECKIN = 'CHECKIN';
    const UNCHECKOUT = 'UNCHECKOUT';
    const MKWORKSPACE = 'MKWORKSPACE';
    const UPDATE = 'UPDATE';
    const LABEL = 'LABEL';

    // RFC3648
    const ORDERPATCH = 'ORDERPATCH';

    // RFC3744
    const ACL = 'ACL';

    // RFC4437
    const MKREDIRECTREF = 'MKREDIRECTREF';
    const UPDATEREDIRECTREF = 'UPDATEREDIRECTREF';

    // RFC4791
    const MKCALENDAR = 'MKCALENDAR';

    // RFC4918
    const PROPFIND = 'PROPFIND';
    const LOCK = 'LOCK';
    const UNLOCK = 'UNLOCK';
    const PROPPATCH = 'PROPPATCH';
    const MKCOL = 'MKCOL';
    const COPY = 'COPY';
    const MOVE = 'MOVE';

    // RFC5323
    const SEARCH = 'SEARCH';

    // RFC5789
    const PATCH = 'PATCH';

    // RFC5842
    const BIND = 'BIND';
    const UNBIND = 'UNBIND';
    const REBIND = 'REBIND';
}
