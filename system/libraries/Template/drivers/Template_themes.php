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
 * Template Themes Driver
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

class O2_Template_Themes extends CI_Driver
{
    /**
     * Theme Default Parameter
     *
     * @var string
     * @access public
     */
    public $default;

    /**
     * Theme Active Registry
     *
     * @var object - array
     * @access protected
     */
    public $active;

    /**
     * Theme Configuration
     *
     * @var object
     * @access private
     */
    private $_config;

    /**
     * Installed Themes
     *
     * @var object - array
     * @access private
     */
    private $_installed;

    /**
     * Themes Valid List
     *
     * @var array
     * @access private
     */
    private $_valid_themes;

    /**
     * Theme Active RAW Registry
     *
     * @var object - array
     * @access private
     */
    private $_registry;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        global $O2SYSTEM, $URI, $CFG;

        // Load Installed Themes Registry
        $this->_installed = $O2SYSTEM->{$URI->request->app->parameter}->themes;

        // Define Valid Themes List
        $this->_valid_themes = array_keys( get_object_vars($this->_installed) );

        // Define Theme Config
        $this->_config = $CFG->item('template');
        $this->_config = new O2_System_Registry($this->_config['theme']);

        // Define Default Theme
        if(! empty($this->_config->default))
        {
            $this->default = $this->_config->default;
        }
        else
        {
            $this->default = reset($this->_valid_themes);
        }
    }

    /**
     * Theme Loader
     *
     * @params $theme   string  Theme parameter
     * @access public
     * @return chaining
     */
    public function load($theme)
    {
        $theme = ($theme === 'AUTO' ? $this->default : $theme);

        global $URI;

        // Register Theme Registry
        $this->_registry = $this->_installed->{$theme};

        //print_out($this->_registry);

        // Define Active Theme Registry
        $this->active = new O2_System_Theme_Registry();
        $this->active->parameter = $theme;
        $this->active->path = $this->_registry->path;
        $this->active->URL = $URI->get_app_url('themes/'.$theme);

        // Register Screenshot
        if(isset($this->_registry->files['screenshot']))
        {
            $this->active->screenshot = $this->_registry->files['screenshot'];
        }

        // Register Layout
        if(isset($this->_registry->files['layout']))
        {
            $this->active->layout = $this->_registry->files['layout'];
        }
        else
        {
            $error_messages = 'Unable to locate theme default layout';
            log_message('error', $error_messages);
            show_error($error_messages);
        }

        // Register Blocks
        if(isset($this->_registry->blocks->files))
        {
            $this->active->blocks = $this->_registry->blocks->files;
        }

        // Register Pages
        if(isset($this->_registry->pages->files))
        {
            $this->active->pages = $this->_registry->pages->files;
        }

        // Register Details
        if(isset($this->_registry->files['details']))
        {
            $json_file = file_get_contents($this->_registry->files['details']);
            $this->active->details = json_decode($json_file);
        }
        else
        {
            $error_messages = 'Unable to load the requested theme: details.json';
            log_message('error', $error_messages);
            show_error($error_messages);
        }

        // Register Settings
        if(isset($this->_registry->files['settings']))
        {
            $json_file = file_get_contents($this->_registry->files['settings']);
            $this->active->settings = json_decode($json_file);
        }
        else
        {
            $error_messages = 'Unable to load the requested theme: settings.json';
            log_message('error', $error_messages);
            show_error($error_messages);
        }

        // Register Modules
        if(isset($this->_registry->modules))
        {
            foreach($this->_registry->modules as $module_parameter => $module_registry)
            {
                if(isset($module_registry->files))
                {
                    $this->active->modules[$module_parameter] = $module_registry->files;
                }
            }
        }

        return $this;
    }

    /**
     * Set Theme Layout
     *
     * @params $layout  string  Layout parameter, pack name or page name
     * @access public
     * @return void
     */
    public function set_layout($layout)
    {
        $found = FALSE;

        // Find from root theme folder
        if(isset($this->_registry->files[$layout]))
        {
            $this->active->layout = $this->_registry->files[$layout];
            $found = TRUE;
        }

        // Find from theme pages
        if(isset($this->_registry->pages->files[$layout]))
        {
            $this->active->layout = $this->_registry->pages->files[$layout];
            $found = TRUE;
        }

        // Find from theme layouts
        if(isset($this->_registry->layouts))
        {
            if (strrpos($layout, '/') !== FALSE)
            {
                $x_layout = preg_split('[/]', $layout, -1, PREG_SPLIT_NO_EMPTY);

                if(isset($this->_registry->layouts->{$x_layout[0]}))
                {
                    $pack_request = $x_layout[0];
                    array_shift($x_layout);
                }

                if(isset($pack_request))
                {
                    $layout = end($x_layout);
                    if(in_array($layout, array_keys($this->_registry->layouts->{$pack_request}->files)))
                    {
                        $this->active->layout = $this->_registry->layouts->{$pack_request}->files[$layout];
                        $found = TRUE;
                    }
                }
            }
            else
            {
                if (isset($this->_registry->layouts->{$layout}))
                {
                    if(in_array('layout', array_keys($this->_registry->layouts->{$layout}->files)))
                    {
                        $this->active->layout = $this->_registry->layouts->{$layout}->files['layout'];
                        $found = TRUE;
                    }
                }
            }
        }

        if($found === FALSE)
        {
            $error_messages = 'Unable to load the requested layout file: ' . $layout;
            log_message('error', $error_messages);
            show_error($error_messages);
        }
    }
}

/* End of file Template_themes.php */
/* Location: ./system/libraries/Template/drivers/Template_themes.php */