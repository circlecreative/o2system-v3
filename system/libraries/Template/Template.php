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
 * Template Driver
 *
 * @package     O2System
 * @subpackage  system/core
 * @category    Core Class
 * @author      Steeven Andrian Salim
 * @copyright     Copyright (c) 2005 - 2014 PT. Lingkar Kreasi (Circle Creative)
 * @license       http://www.circle-creative.com/products/o2system/license.html
 * @link          http://www.circle-creative.com
 */

class O2_Template extends CI_Driver_Library
{
    /**
     * Valid Drivers
     * @access protected
     */
    protected $valid_drivers = array(
        'template_assets', 
        'template_banners' , 
        'template_breadcrumbs', 
        'template_navigations',
        'template_metadata', 
        'template_partials', 
        'template_widgets',
        'template_table',
        'template_fetcher'
    );

    /**
     * Template Configuration
     * @access protected
     */
    protected $_config = array();

    /**
     * Template Data
     * @access protected
     */
    protected $_vars = array();  

    /**
     * Template Args
     * @access protected
     */
    protected $_args = array();  

    /**
     * Template Output
     * @access protected
     */
    protected $_output = '';    

    /**
     * Template Variables
     * @access public
     */    
    var $_installed;
    var $_templates;
    var $directory;
    var $_app_data = '';
    var $active = '';
    var $layout = '';
    var $path = '';
    var $data = array();
    var $details = array();
    var $settings = array();  
    var $contents = array();
    var $partials = array();

   /**
     * Template Cache
     * @access protected
     */
    protected $_cached = false;
    protected $_cached_content = '';
    protected $_cache_ttl = 0;  
    protected $_cache_file = '';
    
    // ------------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param array
     */
    public function __construct($config = array())
    {
        global $O2SYSTEM, $URI;

        $this->_path = $URI->request->app->path.'templates/';
        $this->_installed = $O2SYSTEM->{$URI->request->app->parameter}->templates;
        $this->_templates = array_keys( get_object_vars($this->_installed) ); 

        log_message('debug', 'Template Drivers Initialized');
    }
    
    // ------------------------------------------------------------------------

    /**
     * Set Template
     *
     * @access  public
     * @param   string (template parameter)
     * @param   string (template layout parameter)
     */    
    public function _set($template = 'AUTO', $layout = 'index')
    {
        if($template === 'AUTO')
        {
            $template = reset($this->_templates);
        }

        // Define Active Template
        $this->active = $template;

        // Define Active Layout
        $this->layout = $layout;

        // Define Active Directory
        $this->directory = $this->_installed->{ $this->active }->path;

        // Set Template Data
        $this->data();   

        // Load Template Assets
        // Autoload Core JS
        $this->assets->inline_js("
            var BASE_URL = '".base_url()."';
        ",'header');

        $this->assets->load_js('jquery','core');
        $this->assets->load_packages('jquery-ui','core');
        $this->assets->load_packages('bootstrap','core');

        // Load Helper JS
        $this->assets->load_js('system.init','core');

        // Template Assets
        $this->assets->load_json('template');

        // Load Template JS
        $this->assets->load_js('template.init','template');
    }
    // --------------------------------------------------------------------

    public function directory()
    {
        if(empty($this->directory))
        {
            $this->_set();
        }

        return $this->directory;
    }

    // --------------------------------------------------------------------

    /**
     * Template Data
     *
     * @access  public
     * @param   string (template parameter)
     * @param   string (array or object return value of template details and settings)
     */    
    public function data($template = '', $return = 'object')
    {    
        // Define Template
        $template = ($template == '' ? $this->active : $template);

        global $CFG;

        $this->data = new stdClass;
        $this->data->parameter = $template;
        $this->data->URL = $CFG->item('base_url') . $this->_path . $template . '/';
        $this->data->path = $this->_path . $template . '/';
        $this->data->details = $this->_get_details($template);
        $this->data->settings = $this->_get_settings($template);

        if($return === 'object')
        {
            return $this->data;
        }
        else
        {
            return object_to_array($this->data);
        }

        return false;
    }     
    // --------------------------------------------------------------------  

    /**
     * Get Template Details
     * @access  private  
     */
    private function _get_details($template = '')
    {
        // Define Template Parameter
        $template = ($template == '' ? $this->active : $template);

        // Define XML File Path
        $xml_file = $this->_path . $template . '/' . 'details.xml';

        if (file_exists($xml_file))
        {
            $xml_data = simplexml_load_file($xml_file);
            $this->details = new stdClass;
            
            foreach(get_object_vars($xml_data) as $key => $value)
            {
                if($key == '@attributes')
                {
                    foreach($value as $_key => $_value)
                    {
                        $this->details->{$_key} = $_value;
                    }

                    unset($xml_data->$key);
                }
                elseif(is_object($value))
                {
                    $this->details->$key = get_object_vars($value);
                }
                else
                {
                    $this->details->$key = $value;
                }
            }

            return $this->details;
        }
        else
        {
            show_error("Template Details XML File Not Found: " . $xml_file);
        }
    }
    // --------------------------------------------------------------------
    
    /**
     * Get Template Settings
     * @access  private  
     */
    private function _get_settings($template = '')
    {
        // Define Template Parameter
        $template = ($template == '' ? $this->active : $template);

        // Define XML File Path
        $xml_file = $this->_path . $template . '/' . 'settings.xml';

        if (file_exists($xml_file))
        {
            $xml_data = simplexml_load_file($xml_file);
            $this->settings = new stdClass;
            
            foreach(get_object_vars($xml_data) as $key => $value)
            {
                if($key == '@attributes')
                {
                    foreach($value as $_key => $_value)
                    {
                        $this->settings->{$_key} = $_value;
                    }

                    unset($xml_data->$key);
                }
                elseif(is_object($value))
                {
                    $this->settings->$key = get_object_vars($value);
                }
                elseif(in_array($key, array('positions', 'navigations')))
                {
                    $this->settings->$key = preg_split('[,]', $value, -1, PREG_SPLIT_NO_EMPTY);
                }
                else
                {
                    $this->settings->$key = $value;
                }
            }

            return $this->settings;
        }
        else
        {
            show_error("Template Settings XML File Not Found: " . $xml_file);
        }
    }   
    // --------------------------------------------------------------------   

    /**
     * Enable cache with TTL, default TTL is 60
     * @param int $ttl
     * @param mixed $identifier
     */
    public function cache($ttl = 60) 
    {
        // CodeIgniter Instance
        $CI = & get_instance();
    }
    // --------------------------------------------------------------------

    /**
     * Render the entire HTML output combining partials, layouts and views.
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function render($content = '')
    {
        // O2System Instance
        $O2 = & get_instance();

        // Load Template Data
        $O2->load->vars('template', $this->data);

        // Load Metadata
        $O2->load->vars('metadata', $this->metadata->render());

        // Load Assets
        $O2->load->vars('assets', $this->assets->render());

        // Load Navigations
        $O2->load->vars('navigations', $this->navigations->render());

        // Load Template Partials
        $this->_load_partials();

        // Load Template Contents
        $this->_load_contents();

        if($content == '')
        {
            $O2->load->vars('content', $this->contents['single']);
        }
        else
        {
            $O2->load->vars('content', $O2->load->view($content, '', TRUE));
        }

        // Template Output 
        $this->_output = $O2->load->view($this->layout,'', TRUE);

        $O2->output->set_output($this->_output);
    }

    private function _load_contents()
    {
        // O2System Instance
        $O2 =& get_instance();

        // Load directory helper
        $O2->load->helper('directory');

        if(!is_dir($this->directory . 'contents')) show_error('Template Contents not found');

        $contents_map = directory_map($this->directory . 'contents');

        foreach($contents_map as $content)
        {
            // Define partial view name
            $name = str_replace(array('.php', '.tpl'),'', $content);

            // Loaded contents
            $this->contents[$name] = $O2->load->view('contents/'.$content, '', TRUE);
        }
    }

    private function _load_partials()
    {
        // O2System Instance
        $O2 =& get_instance();

        // Load directory helper
        $O2->load->helper('directory');

        if(!is_dir($this->directory . 'partials')) show_error('Template Partials not found');

        // Add partials location to CodeIgniter View Paths
        //$O2->load->add_location($this->directory . 'partials/','view');

        $partial_map = directory_map($this->directory . 'partials');

        foreach($partial_map as $partial)
        {
            // Define partial view name
            $name = str_replace(array('.php', '.tpl'),'', $partial);

            // Loaded partials
            $this->partials[] = $name;

            // Write partials to view
            $O2->load->vars($name, $O2->load->view('partials/'.$partial, '', TRUE));
        }
    }

    private function _load_view($view, $data, $return = true)
    {
        // CodeIgniter Instance
        $O2 = & get_instance();
        $O2->load->library('parser');

        // Grab the content of the view (parsed or loaded)
        if($this->parser_enabled === TRUE)
        {
            // Parse view
            return $O2->parser->parse($view, $data, $return);
        }
        else
        {
            // Load the view only
            return $O2->load->view($view, $data, $return);
        }
    }     
}

/* End of file Template.php */
/* Location: ./system/libraries/Template/Template.php */