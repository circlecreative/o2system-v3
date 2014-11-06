<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * O2System
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		O2System
 * @author		Steeven Andrian Salim
 * @copyright	Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license		http://circle-creative.com/products/o2system/license.html
 * @link		http://circle-creative.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

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

class Core_Config
{
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
		$this->items = new stdClass;

		global $PATH;
		global $URI;

		$default_items =& get_config();
		foreach($default_items as $item => $data)
		{
			$this->set_item($item, $data);
		}

		if(isset($PATH->app) AND !empty($PATH->app->config->files))
		{
			foreach($PATH->app->config->files as $filename => $filepath)
			{
				$this->load($filename, $filepath);
			}
		}

		if(! empty($URI->active->app->name))
		{
			$active_app = $URI->active->app->name;

			if(! empty($PATH->{ $active_app }->config->files))
			{
				foreach($PATH->{ $active_app }->config->files as $filename => $filepath)
				{
					$this->load($filename, $filepath);
				}
			}
		}
	}

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
}