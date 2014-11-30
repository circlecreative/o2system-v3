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
 * Template Library
 *
 * @package       O2System
 * @subpackage    system/core
 * @category      Core Class
 * @author        Steeven Andrian Salim
 * @copyright     Copyright (c) 2005 - 2014 PT. Lingkar Kreasi (Circle Creative)
 * @license       http://www.circle-creative.com/products/o2system/license.html
 * @link          http://www.circle-creative.com
 */

class O2_Template extends CI_Driver_Library
{
    /**
     * Constructor
     *
     * @param array
     */
    public function __construct()
    {
        log_message('debug', 'Template Drivers Initialized');
    }
    
    // ------------------------------------------------------------------------

    /**
     * Set Theme
     *
     * @param  $theme   string  Theme parameter
     * @param  $layout  string  Theme layout
     * @return void
     * @access public
     */    
    public function theme($theme = 'AUTO', $layout = 'layout')
    {
        $this->themes->load($theme)->set_layout($layout);

        // Load Theme Assets
        // Autoload Core JS
        $this->assets->inline_js("
            var BASE_URL = '".base_url()."';
        ",'header');

        $this->assets->load_js('jquery','core');
        $this->assets->load_packages('jquery-ui','core');
        $this->assets->load_packages('bootstrap','core');

        // Load Helper JS
        $this->assets->load_js('system.init','core');

        // Theme Assets
        $this->assets->load_json('theme');

        // Load Theme JS
        $this->assets->load_js('theme.init','theme');
    }
    // --------------------------------------------------------------------

    /**
     * Render the entire HTML output combining blocks, layouts and views.
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function render($view = '')
    {
        // O2System Instance
        $O2 =& get_instance();

        // Load Theme Data
        $O2->load->vars('theme', $this->themes->active);

        // Load Metadata
        $O2->load->vars('metadata', $this->metadata->render());

        // Load Assets
        $O2->load->vars('assets', $this->assets->render());

        // Load Navigations
        $O2->load->vars('navigations', $this->navigations->render());

        // Load Blocks
        $blocks = new stdClass;

        if(! empty($view))
        {
            $blocks->content = $this->parser->parse($view);
        }

        if(! empty($this->themes->active->blocks))
        {
            foreach($this->themes->active->blocks as $block_parameter => $block_path)
            {
                $block = new O2_System_Registry;
                $block->parameter = pathinfo($block_path, PATHINFO_FILENAME);
                $block->path = pathinfo($block_path, PATHINFO_DIRNAME).'/';
                $block->filename = pathinfo($block_path, PATHINFO_BASENAME);;
                $block->filepath = $block_path;
                $block->vars = $O2->load->get_vars();

                $blocks->{$block_parameter} = $this->parser->parse($block);
            }
        }

        $O2->load->vars('blocks', $blocks);

        // Theme Output
        $layout = new O2_System_Registry;
        $layout->parameter = pathinfo($this->themes->active->layout, PATHINFO_FILENAME);
        $layout->path = pathinfo($this->themes->active->layout, PATHINFO_DIRNAME).'/';
        $layout->filename = pathinfo($this->themes->active->layout, PATHINFO_BASENAME);;
        $layout->filepath = $this->themes->active->layout;
        $layout->vars = $O2->load->get_vars();

        $output = $this->parser->parse($layout);

        $O2->output->set_output($output);
    }
}

/* End of file Theme.php */
/* Location: ./system/libraries/Theme/Theme.php */