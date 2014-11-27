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

/**
 * System Config
 *
 * This class contains functions that enable config files to be managed based on CodeIgniter Concept by EllisLab, Inc.
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/config.html
 */

class O2_Config
{
	/**
	 * Config Items Registry
	 *
	 * @var object
	 */
	public $items;

	/**
	 * Constructor
	 *
	 * Sets the $config data from the primary config.php file as a class variable
	 *
	 * @access   public
	 * @param   string	the config file name
	 * @param   boolean  if configuration values should be loaded into their own section
	 * @param   boolean  true if errors should just return false, false if an error message should be displayed
	 * @return  boolean  if the file was successfully loaded or not
	 */
	public function __construct()
	{
		global $O2SYSTEM, $URI;

		$this->items = new O2_System_Config_Registry;

		$default_items =& get_config();

		foreach($default_items as $item => $data)
		{
			$this->set_item($item, $data);
		}

		if(isset($O2SYSTEM->app) AND !empty($O2SYSTEM->app->config->files))
		{
			foreach($O2SYSTEM->app->config->files as $filename => $filepath)
			{
				$this->load($filename, $filepath);
			}
		}

		if(! empty($URI->request->app->parameter))
		{
			$requested_app = $URI->request->app->parameter;

			if(! empty($O2SYSTEM->{ $requested_app }->config->files))
			{
				foreach($O2SYSTEM->{ $requested_app }->config->files as $filename => $filepath)
				{
					$this->load($filename, $filepath);
				}
			}
		}

		log_message('debug', "Config Class Initialized");
	}

	// ------------------------------------------------------------------------

	/**
	 * Load Config File
	 *
	 * @access	public
	 * @param	string	the config file name
	 * @param   boolean  if configuration values should be loaded into their own section
	 * @param   boolean  true if errors should just return false, false if an error message should be displayed
	 * @return	boolean	if the file was loaded correctly
	 */
	public function load($filename, $filepath)
	{
		if(file_exists($filepath))
		{
			require_once($filepath);

			if(isset($$filename))
			{
				$this->set_item($filename, $$filename);
				unset($$filename);
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch a config file item
	 *
	 *
	 * @access	public
	 * @param	string	the config item name
	 * @param	string	the index name
	 * @param	bool
	 * @return	string
	 */
	public function item($item, $index = '')
	{
		if ($index == '')
		{
			if ( ! isset($this->items->{$item}))
			{
				return FALSE;
			}

			$item = $this->items->{$item};
		}
		else
		{
			if ( ! isset($this->items->{$index}))
			{
				return FALSE;
			}

			if ( ! isset($this->items->{$index}[$item]))
			{
				return FALSE;
			}

			$item = $this->items->{$index}[$item];
		}

		return $item;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set a config file item
	 *
	 * @access	public
	 * @param	string	the config item key
	 * @param	string	the config item value
	 * @return	void
	 */
	public function set_item($item, $value)
	{
		$combined = array('cookie', 'session', 'csrf');

		if($item == 'encryption_key' AND $value == '')
		{
			$value = ucfirst( substr ( md5 ( 'O2System' ), 7, 7) );
		}

		foreach($combined as $combine)
		{
			if(preg_match('[^'.$combine.'_(\w)]', $item))
			{
				if(empty($this->items->$combine))
				{
					$this->items->$combine = new stdClass;
				}

				$this->items->$combine->{ str_replace($combine.'_', '', $item) } = $value;
			}
			else
			{
				$this->items->{$item} = $value;
			}
		}
	}

	// ------------------------------------------------------------------------
}

/* End of file Config.php */
/* Location: ./system/core/Config.php */