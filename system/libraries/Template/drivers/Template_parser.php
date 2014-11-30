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
 * @package     O2System
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Template Parser
 *
 * @package	      Template
 * @subpackage	  Library
 * @category	  Driver
 * @version       1.0 Build 11.09.2012
 * @author	      Steeven Andrian Salim
 * @copyright     Copyright (c) 2005 - 2014 PT. Lingkar Kreasi (Circle Creative)
 * @license       http://www.circle-creative.com/products/o2system/license.html
 * @link          http://www.circle-creative.com
 */
// ------------------------------------------------------------------------

class O2_Template_Parser extends CI_Driver
{
    protected $_config;

    public function __construct()
    {
        global $CFG;
        $this->_config = $CFG->item('template');
    }

    public function parse($registry = '')
    {
        if(is_object($registry)) 
        {
            if (strtolower($this->_config['engine']) == 'smarty') 
            {
                $smarty = new Smarty();
                $smarty->setCompileDir($this->_config['cache']['compiler']);
                $smarty->setCacheDir($this->_config['cache']['path']);
                $smarty->setTemplateDir($registry->path);
                $smarty->caching = $this->_config['cache']['enable'];

                foreach ($registry->vars as $_assign_key => $_assign_value) 
                {
                    $smarty->assign($_assign_key, $_assign_value);
                }

                $output = $smarty->fetch($registry->filename);
            } 
            else 
            {
                $loader = new Twig_Loader_Filesystem($registry->path);
                $twig = new Twig_Environment($loader, array(
                    'cache' => $this->_config['cache']['path'],
                ));

                $template = $twig->loadTemplate($registry->filename);
                $output = $template->render($registry->vars);
            }
        }

        return $output;
    }
}