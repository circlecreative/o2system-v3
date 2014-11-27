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
 * Template Navigations Driver Class
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

class O2_Template_navigations extends CI_Driver 
{
	var $data = array();
	var $active = '';
    var $properties = array();

    /**
     * Set Data
     *
     * @access	public
     * @param array
     * @return	void
     */
    public function set_data($data)
    { 
        $this->data = (empty($data) ? array('Home', app_url()) : $data);
    }
    // --------------------------------------------------------------------

    public function set_properties($properties)
    {
        $this->properties = $properties;
    }

	/**
     * Render Output
     *
     * @access	public
     * @return	void
     */    
    public function render()
    {
    	$output = new stdClass; 

        if(count($this->data) > 0)
        {
            $output -> {'raw'} = $this->data;
            
            foreach($this->data as $position => $navigation)
            {
                // Navigation Data
                $navigation_data = $this->render_html($navigation, $position) . "\n";

                // Navigation Output
                $position = str_replace('-','_', $position);
                $output -> {$position} = $navigation_data;  
            }

            return $output;
        }

        return false;
    }    

    /**
     * Generate Array Navigation into HTML list
     *
     * @access	public
     * @param array
     * @return	void
     */
    public function render_html($navigations, $position, $sub = false)
    {  
        // Empty nothing to proceed
        if(empty($navigations)) return false;

    	$O2 =& get_instance();
    	$O2->load->helper('html');

        $prop_position = ($sub === true ? 'sub-'.$position : $position);

        $default_properties = array('id' => 'nav-'.$prop_position, 'class' => 'nav-'.$prop_position);
        
        $properties = element($position, $this->properties);
        $properties = element(($sub === true ? 'child' : 'parent'), $properties, $properties);

        $properties = (empty($properties) ? $default_properties : $properties);

        $output = html('ul', $properties);

        //print_out($navigations);

        foreach($navigations as $nav)
        {
            if(empty($nav->parameter)) continue;
            
            // List Open
            $output.= html('li', array('class' => (isset($nav->parameter) && in_array($nav->parameter,$CI->uri->segments) ? 'active' : '')));

            // Nav Link
            $output.= html('a', array(
                'class' => (isset($nav->parameter) && in_array($nav->parameter,$CI->uri->segments) ? 'active' : ''),
                'href' => (isset($nav->URL) ? $nav->URL : ''),
                'title' => $nav->title,
            ));

            $output.= $nav->title;

            $output.= html('/a');

            // Check if nested
            if(isset($nav->sub_nav))
            {
                $output.= $this->render_html($nav->sub_nav, $position, true);
            }  

            $output.= html('/li');
        }

        $output.= html('/ul');

        return $output;
    }
}

/* End of file Template_Navigations.php */
/* Location: ./system/libraries/Template/drivers/Template_Navigations.php */