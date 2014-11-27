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
 * O2System Core Class
 *
 * Reactor class
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/system.html
 */

class O2_System
{
	/**
	 * Default system folders path names
	 *
	 * @var array
	 */
	private $_default_system_paths = array(
		'core' => 'core',
    	'config' => 'config',
    	'cache' => 'cache',
    	'log' => 'logs',
    	'language' => 'language',
    	'upload' => 'uploads',
    	'theme' => 'themes',
    	'assets' => 'assets',
    	'library' => 'libraries',
		'controller' => 'controllers',
		'model' => 'models',
		'view' => 'views',
		'helper' => 'helpers',
    );

	/**
	 * Default modules folder path names
	 *
	 * @var array
	 */
    private $_default_module_paths = array(
    	'module' => 'modules',
    	'component' => 'components',
    	'plugin' => 'plugins',
    	'widget' => 'widgets'
    );

	/**
	 * Default non fetch paths
	 *
	 * @var array
	 */
    private $_default_non_fetch_paths = array(
    	'cache', 'log', 'upload', 'assets',
    );

	/**
	 * Detected Apps Registry
	 *
	 * @var array
	 */
	public $apps;

	/**
	 * Detected Language Registry
	 *
	 * @var array
	 */
	public $lang;

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		// Fetch System Map Folders
		$this->_fetch_system();

		// Fetch Apps Map Folders
		$this->_fetch_apps();
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch directory to create directory registry map
	 *
	 * @access  private
	 * @return	array
	 */
	private function _fetch_dir($path, $depth = 0, $hidden = FALSE)
	{
		if($fp = @opendir($path))
		{
			$filedata	= array();
			$new_depth	= $depth - 1;
			$path	= rtrim($path, __DS__).__DS__;

			while( FALSE !== ( $file = readdir($fp) ) )
			{
				// Remove '.', '..', and hidden files [optional]
				if ( ! trim($file, '.') OR ($hidden == FALSE && $file[0] == '.')  OR in_array($file, $this->_default_non_fetch_paths) OR $file == 'index.html')
				{
					continue;
				}

				if ( ($depth < 1 OR $new_depth > 0) && @is_dir($path.$file))
				{
					$filedata[$file] = $this->_fetch_dir($path.$file.__DS__, $new_depth, $hidden);
				}
				else
				{
					$filedata[] = $file;
				}
			}

			closedir($fp);
			return $filedata;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch system directory to create system registry map
	 *
	 * @access  private
	 * @return	void
	 */
	private function _fetch_system()
	{
		$apps_map = $this->_fetch_dir(SYSPATH);

		$this->system = $this->_fetch_packages($apps_map, SYSPATH);
		$this->system->path = SYSPATH;

		if(!empty($this->system->language))
		{
			foreach($this->system->language as $lang_code => $lang_data)
			{
				if($lang_code != 'path') $this->lang[] = $lang_code;
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch apps directory to create apps registry map
	 *
	 * @access  private
	 * @return	void
	 */
	private function _fetch_apps()
	{
		$apps_map = $this->_fetch_dir(APPSPATH);
		$this->app = $this->_fetch_packages($apps_map, APPSPATH);

    	foreach($this->app as $apps_name => $apps_map)
    	{
    		if(! in_array($apps_name, array_values($this->_default_system_paths)))
    		{
    			$this->apps[] = $apps_name;
    			$this->{$apps_name} = $apps_map;

    			unset($this->app->{$apps_name});
    		}
    	}

    	$this->app->path = APPSPATH;

	    $this->apps = array_unique($this->apps);
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch packages registry map to register O2System map
	 *
	 * @access  private
	 * @return	void
	 */
	private function _fetch_packages($map, $path)
	{
		$new_map = new stdClass;

		foreach($map as $folder => $files)
		{
			if(is_numeric($folder))
			{
				@$new_map->path = $path;
			}
			else
			{
				@$new_map->{$folder}->path = $path.$folder.'/';
			}

			if(is_array($files))
			{
				foreach($files as $filepath => $filename)
				{
					if(!is_array($filename) AND !is_numeric($filename))
					{
						$new_map->{$folder}->files[ strtolower( pathinfo($filename, PATHINFO_FILENAME) ) ] = $new_map->{$folder}->path . $filename;
					}
					elseif(is_array($filename))
					{
						$new_map->{$folder}->{$filepath} = $this->_fetch_packages($filename, $new_map->{$folder}->path.$filepath.'/');
					}
				}
			}
			elseif(!is_numeric($folder) AND is_string($files) )
			{
				$new_map->{$folder}->filename = pathinfo($files, PATHINFO_FILENAME);
				$new_map->{$folder}->filepath = $new_map->{$folder}->path . $files;
			}
			else
			{
				$new_map->files[ pathinfo($files, PATHINFO_FILENAME) ] = $new_map->path . $files;
			}
		}

		return $new_map;
	}
}

/**
 * Active URI Standard Objects
 *
 * O2System standard uri active registry
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/registry.html
 */
class O2_System_URI_Active_Registry
{
	/**
	 * Active App Requested 
	 *
	 * @var	string
	 */
	public $app;

	/**
	 * Active Lang Requested 
	 *
	 * @var	string
	 */
	public $lang;

	/**
	 * Active Module Requested 
	 *
	 * @var	string
	 */
	public $module;

	/**
	 * Active Controller Requested 
	 *
	 * @var	string
	 */
	public $controller;

	/**
	 * Active Method Requested 
	 *
	 * @var	string
	 */
	public $method;

	/**
	 * Active Params Requested 
	 *
	 * @var	string
	 */
	public $params;
}

/**
 * Active URI Standard Objects
 *
 * O2System standard uri active registry
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/registry.html
 */
class O2_System_URI_Request_Registry
{
	/**
	 * Requested App 
	 *
	 * @var	string
	 */
	public $app = NULL;

	/**
	 * Requested Attribute 
	 *
	 * @var	string
	 */
	public $attribute = FALSE;

	/**
	 * Requested Lang
	 *
	 * @var	string
	 */
	public $lang;

	/**
	 * Requested Module
	 *
	 * @var	string
	 */
	public $module;

	/**
	 * Requested Controller
	 *
	 * @var	string
	 */
	public $controller;

	/**
	 * Requested Method
	 *
	 * @var	string
	 */
	public $method = 'index';

	/**
	 * Requested Params
	 *
	 * @var	array
	 */
	public $params = array();
}

/**
 * Active URI Standard Objects
 *
 * O2System standard uri active registry
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/registry.html
 */
class O2_System_Class_Registry
{
	/**
	 * Class Parameter Name 
	 *
	 * @var	string
	 */
	public $parameter;

	/**
	 * Class Full Name 
	 *
	 * @var	string
	 */	
	public $name;

	/**
	 * Class Folder Path
	 *
	 * @var	string
	 */	
	public $path;

	/**
	 * Class Filepath 
	 *
	 * @var	string
	 */
	public $filepath;

}

/**
 * O2System Config Registry
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/registry.html
 */
class O2_System_Config_Registry 
{
    /**
     * Class constructor
     *
     * @return  void
     */
	public function __construct($config = array())
	{
		if(! empty($config))
		{
			foreach($config as $key => $value)
			{
				if(is_array($value))
				{
					$this->{$key} = new O2_System_Config_Registry($value);
				}
				else
				{
					$this->{$key} = $value;
				}
			}
		}
	}
}

/**
 * O2System Registry
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/registry.html
 */
class O2_System_Registry 
{
    /**
     * Class constructor
     *
     * @return  void
     */
	public function __construct($config = array())
	{
		if(! empty($config))
		{
			foreach($config as $key => $value)
			{
				if(is_array($value))
				{
					$this->{$key} = new O2_System_Registry($value);
				}
				else
				{
					$this->{$key} = $value;
				}
			}
		}
	}
} 

/* End of file System.php */
/* Location: ./system/core/System.php */