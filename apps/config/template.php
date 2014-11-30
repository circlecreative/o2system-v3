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

defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Template
| -------------------------------------------------------------------------
| This file lets you define template driver configuration
|
|	http://circle-creative.com/products/o2system/user-guide/drivers/template.html
|
*/

$template['theme']['enable'] = TRUE;
$template['theme']['default'] = 'example';

$template['cache']['enable'] = FALSE;
$template['cache']['path'] = APPSPATH.'cache/template/';
$template['cache']['compiler'] = APPSPATH.'cache/template/compiler/';
$template['cache']['expired'] = 7200;

$template['engine'] = 'smarty';
$template['parse_markdown'] = TRUE;

/* End of file template.php */
/* Location: ./apps/config/template.php */