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
 * Template Metadata Driver Class
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

class O2_Template_Metadata extends CI_Driver 
{
	/**
     * Charset Type
     * @access public	 	 	 	 	 	 
     */
    public $charset = 'UTF-8';

	/**
     * Metadata Variables
     * @access public	 	 	 	 	 	 
     */
	public $vars = array();

	/**
     * Title Separator
     * @access public	 	 	 	 	 	 
     */
	public $separator = '-';

	/**
     * Browser Title
     * @access public	 	 	 	 	 	 
     */
	public $browser_title = '';

	/**
     * Page Title
     * @access public	 	 	 	 	 	 
     */
	public $page_title = '';

	/**
     * Valid Metadata
     * @access private	 	 	 	 	 	 
     */
	private $_valid_metadata = array(
            'abstract',
            'author',
            'category',
            'classification',
            'copyright',
            'coverage',
            'description',
            'distribution',
            'doc-class',
            'doc-rights',
            'doc-type',
            'downloadoptions',
            'expires',
            'designer',
            'directory',
            'generator',
            'googlebot',
            'identifier-url',
            'keywords',
            'language',
            'mssmarttagspreventparsing',
            'name',
            'owner',
            'progid',
            'rating',
            'refresh',
            'reply-to',
            'resource-type',
            'revisit-after',
            'robots',
            'summary',
            'title',
            'topic',
            'url'
        );

	/**
     * Valid Meta HTTP Equiv
     * @access private	 	 	 	 	 	 
     */
	private $_valid_http_equiv = array(
            'cache-control',
            'content-language',
            'content-type',
            'date',
            'expires',
            'last-modified',
            'location',
            'refresh',
            'set-cookie',
            'window-target',
            'pragma',
            'page-enter',
            'page-exit',
            'x-ua-compatible'
        );

    // ------------------------------------------------------------------------

	/**
     * Set Metadata Variables
     *
     * @access public	 	 	 	 	 	 
     * @return void
     */
	public function set($vars = array(), $overide = FALSE)
	{
		foreach($vars as $tag => $content)
		{
			if(in_array($tag, $this->_valid_metadata))
			{
				if($overide === TRUE OR empty ($this->vars[$tag]))
				{
					$this->vars[$tag] = $content;
				}
				else if($overide === 'implode')
				{
					$separator = ($tag === 'title' ? ' '.$this->separator.' ' : ', ');
					$this->vars[$tag] = implode($separator,array($this->vars[$tag], $content));
				}				
			}
		}
	}

    // ------------------------------------------------------------------------

	/**
     * Set Metadata HTTP Equiv
     *
     * @access public	 	 
     * @return void	 	 	 	 
     */
	public function http_equiv($vars = array(), $overide = FALSE)
	{
		foreach($vars as $tag => $content)
		{
			if(in_array($tag, $this->_valid_http_equiv))
			{
				if($overide === TRUE OR ! isset ($this->vars[$tag]))
				{
					$this->vars[$tag] = $content;
				}
				else if($overide === 'implode')
				{
					$this->vars[$tag] = implode(', ',array($this->vars[$tag], $content));
				}				
			}
		}
	}

	/**
     * Set Browser and Page Title
     *
     * @access public	 	
     * @return void 	 	 	 	 
     */
	public function title($title, $overide = FALSE)
	{
		if($title == '') return;

		$this->page_title($title, $overide);
		$this->browser_title($title, $overide);
		$this->set(array('title' => $title), $overide);		
	}

    // ------------------------------------------------------------------------

	/**
     * Set Browser Title
     *
     * @access public	 	 	 	 	 	 
     * @return void
     */
	public function browser_title($title, $overide = FALSE)
	{
		if($title == '') return;

		if($overide === TRUE OR $this->browser_title == '')
		{
			$this->browser_title = $title;
		}
		else if($overide === 'implode')
		{
			$this->browser_title = implode(' '.$this->separator.' ',array($this->browser_title, $title));
		}
	}

    // ------------------------------------------------------------------------

	/**
     * Set Page Title
     *
     * @access public	
     * @return void 	 	 	 	 	 
     */
	public function page_title($title, $overide = FALSE)
	{
		if($title == '') return;
		
		if($overide === TRUE OR $this->page_title == '')
		{
			$this->page_title = $title;
		}
		else if($overide === 'implode')
		{
			$this->page_title = implode(' '.$this->separator.' ',array($this->page_title, $title));
		}		
	}

    // ------------------------------------------------------------------------

	/**
     * Render Metadata
     *
     * @access public	
     * @return string 	 	 	 	 	 
     */
	public function render()
	{
        $O2 =& get_instance();

		$O2->load->vars('browser_title', $this->browser_title);
		$O2->load->vars('page_title', $this->page_title);

		$output = '';

		if (count($this->vars) > 0)
        {
            $output.= "<meta charset=\"" . $this->charset . "\"/> \n";
            foreach ($this->vars as $tag => $content)
            {
            	$content = (is_array($content) ? implode(',', $content) : $content);
                $output.= "<meta name=\"$tag\" content=\"$content\"/> \n";
            }
        }

        return $output;
	}
}

/* End of file Template_Metadata.php */
/* Location: ./system/libraries/Template/drivers/Template_Metadata.php */