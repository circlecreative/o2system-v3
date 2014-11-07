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
 * System Path
 *
 * Path Registry
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/path.html
 */

class Core_Path
{
	private $default_paths = array(
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

    private $default_module_paths = array(
    	'module' => 'modules',
    	'component' => 'components',
    	'plugin' => 'plugins',
    	'widget' => 'widgets'
    );

    private $skip_fetchs = array(
    	'cache', 'log', 'upload',
    );

	public $apps;

	public function __construct()
	{
		// Fetch System
		$this->_fetch_system();

		// Fetch Apps
		$this->_fetch_apps();
	}

	public function _fetch_dir($path, $depth = 0, $hidden = FALSE)
	{
		if($fp = @opendir($path))
		{
			$filedata	= array();
			$new_depth	= $depth - 1;
			$path	= rtrim($path, __DS__).__DS__;

			while( FALSE !== ( $file = readdir($fp) ) )
			{
				// Remove '.', '..', and hidden files [optional]
				if ( ! trim($file, '.') OR ($hidden == FALSE && $file[0] == '.'))
				{
					continue;
				}

				if ( ($depth < 1 OR $new_depth > 0) && @is_dir($path.$file) && !in_array($file, $this->skip_fetchs))
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

	public function _fetch_system()
	{
		$apps_map = $this->_fetch_dir(SYSPATH);

		$this->system = $this->_fetch_packages($apps_map, SYSPATH);
		$this->system->path = SYSPATH;
	}

	public function _fetch_packages($map, $path)
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
					if(!is_array($filename) AND !is_numeric($filename) AND $filename != 'index.html')
					{
						$new_map->{$folder}->files[ strtolower( pathinfo($filename, PATHINFO_FILENAME) ) ] = $new_map->{$folder}->path . $filename;
					}
					elseif(is_array($filename))
					{
						$new_map->{$folder}->{$filepath} = $this->_fetch_packages($filename, $new_map->{$folder}->path.$filepath.'/');
					}
				}
			}
			elseif(!is_numeric($folder) AND is_string($files) AND $files != 'index.html')
			{
				$new_map->{$folder}->filename = pathinfo($files, PATHINFO_FILENAME);
				$new_map->{$folder}->filepath = $new_map->{$folder}->path . $files;
			}
			elseif($files != 'index.html')
			{
				$new_map->files[ pathinfo($files, PATHINFO_FILENAME) ] = $new_map->path . $files;
			}
		}

		return $new_map;
	}

	public function _fetch_apps()
	{
		$apps_map = $this->_fetch_dir(APPSPATH);
		$this->app = $this->_fetch_packages($apps_map, APPSPATH);

    	foreach($this->app as $apps_name => $apps_map)
    	{
    		if(! in_array($apps_name, array_values($this->default_paths)))
    		{
    			$this->apps[] = $apps_name;
    			$this->{$apps_name} = $apps_map;

    			unset($this->app->{$apps_name});
    		}
    	}

    	$this->app->path = APPSPATH;

	    $this->apps = array_unique($this->apps);
	}
}