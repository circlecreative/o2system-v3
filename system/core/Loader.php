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
 * System Loader
 *
 * Autoload class base on SPL Functions
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Loader
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide.html
 */

class Core_Loader
{
    /** 
     * Constructor
     * Constant contain my full path to Model, View, Controllers and Lobrary-
     * Direcories.
     *
     * @Constant MPATH,VPATH,CPATH,LPATH
     */
     
    public function __construct()
    {
        // Autoload
        $this->autoload();

        // Register SPL Functions
        spl_autoload_register(array($this, 'library'));
    }

    private function fetch_apps()
    {
    	$app_folders = dir_map(BASEPATH.'apps');

    	if(! empty($app_folders))
    	{
	    	foreach(array_keys($app_folders) as $app_folder)
	    	{
	    		if(! in_array($app_folder, array_values($this->default_paths)))
	    		{
	    			$this->paths->apps[] = $app_folder;
	    		}
	    	}
	    }

	    // Register Root App
	    if(is_dir(BASEPATH.'apps/root'))
	    {
	    	$this->register_path('root');
	    	$this->fetch_modules(BASEPATH.'apps/root/');
	    }
	    else
	    {
	    	$this->fetch_modules(BASEPATH.'apps/');
	    }

	    // Register Active App
	    $this->register_path(Router::active_app());
	    $this->paths->app = BASEPATH.'apps/'.ACTIVEAPP.'/';
	    $this->fetch_modules($this->paths->app);
    }

    private function fetch_modules($app_path)
    {
    	foreach($this->default_module_paths as $path_name => $path_folder)
    	{
    		if(is_dir($app_path . $path_folder))
    		{
    			$module_paths = dir_map($app_path . $path_folder);
    			$module_paths =  array_keys($module_paths);

    			foreach($module_paths as $module_path)
    			{
    				$this->paths->{$path_name}[$module_path] = $app_path . $path_folder . '/' . $module_path . '/';
    			}

    			$this->paths->{$path_name}['root'] = $app_path . $path_folder . '/';
    		}
    	}
    }

    private function autoload($app = 'ROOT')
    {
    	if($app === 'ROOT')
    	{
    		$paths = array(
    			BASEPATH.'apps/config/',
    			BASEPATH.'apps/root/config/',
    		);

    		foreach($paths as $path)
	    	{
	    		if(file_exists($path.'autoload'.__EXT__))
	    		{
	    			require $path.'autoload'.__EXT__;

	    			if(isset($autoload))
	    			{
		    			foreach($this->default_paths as $path_name => $path_folder)
		    			{
		    				if(! empty($autoload[$path_name]))
		    				{
		    					foreach($autoload[$path_name] as $load)
		    					{
		    						if(method_exists($this, $path_name))
		    						{
		    							$this->$path_name($load);
		    						}
		    					}
		    				}
		    			}
		    		}
	    		}
	    	}
    	}
    	else
    	{

    	}
    }

    public function register_path($new_path, $group = 'ALL')
    {
    	if($group == 'ALL')
    	{
    		foreach($this->default_paths as $path_name => $path_folder)
	    	{
	    		$path_root = BASEPATH.'apps/'.$new_path.'/';
	    		$this->paths->{$path_name}[] = $path_root.$path_folder.'/';
	    	}
    	}
    }

    public function library($class, $params = NULL)
    {
    	foreach($this->paths->library as $path)
    	{
    		$class_name = get_class_name($class);

    		if(file_exists($path.$class_name.__EXT__))
    		{
    			if(! class_exists($class_name))
    			{
	    			spl_autoload_extensions(__EXT__);
	    			set_include_path($path);
	    			spl_autoload($class_name);

	    			$registry = new stdClass;
	    			$registry->class_name = $class_name;
	    			$registry->class_path = $path;

	    			array_push($this->registry->library, $registry);
	    		}
    		}
    	}
    }

    public function controller($class = 'controller', $params = NULL)
    { 	
    	$base_controllers = array(
    		get_class_name($class),
    		get_class_name('App_'.$class),
    		get_class_name(ACTIVEAPP.'_'.$class)
    	);

    	$base_paths = $this->paths->controller;
    	array_unshift($base_paths, BASEPATH.'apps/'.ACTIVEAPP.'/core/');
    	array_unshift($base_paths, BASEPATH.'apps/core/');
    	array_unshift($base_paths, SYSPATH.'core/');

    	foreach($base_controllers as $index => $class_name)
    	{
    		foreach($base_paths as $base_path)
    		{
    			if(file_exists($base_path.$class_name.__EXT__))
    			{
	    			if(! class_exists($class_name))
					{
		    			spl_autoload_extensions(__EXT__);
		    			set_include_path($base_path);
		    			spl_autoload($class_name);

		    			$registry = new stdClass;
		    			$registry->class_name = $class_name;
		    			$registry->class_path = $base_path;

		    			array_push($this->registry->controller, $registry);
		    		}
    			}
    		}
    	}

    	global $O2;

    	$module_controllers = array(
    		get_class_name('App_'.$O2->router->active_module) => get_class_name('App_'.$O2->router->active_module),
    		get_class_name(ACTIVEAPP.'_'.$O2->router->active_module)  => get_class_name(ACTIVEAPP.'_'.$O2->router->active_module),
    		get_class_name($O2->router->active_module)  => strtolower($O2->router->active_module)		
    	);

    	//print_code($this->paths->module);

    	$module_paths = $this->paths->controller;
    	
    	foreach($this->paths('module') as $path_module)
    	{
    		if(is_dir($path_module.$O2->router->active_module))
    		{
    			array_push($module_paths, $path_module.$O2->router->active_module.'/controllers/');
    		}
    	}

    	foreach($module_controllers as $class_name => $filename)
    	{
    		foreach($module_paths as $module_path)
    		{
    			$load[] = $module_path.$filename.__EXT__;
	    		if(file_exists($module_path.$filename.__EXT__))
	    		{
	    			if(! class_exists($class_name))
	    			{
		    			require $module_path.$filename.__EXT__;

		    			$registry = new stdClass;
		    			$registry->class_name = get_class_name(ACTIVEAPP.'_'.$O2->router->active_module);
		    			$registry->class_path = $module_path;

		    			array_push($this->registry->controller, $registry);
		    		}
	    		}
	    	}
    	}

    	foreach($O2->router->segments as $slice => $segment)
    	{
    		if($segment == $O2->router->active_module) continue;

    		if(is_dir(end($module_paths).$segment.'/'))
    		{
    			$sub_module_path = end($module_paths).$segment.'/';
    		}
    		
    		if(isset($sub_module_path))
    		{
    			if(is_file($sub_module_path.$segment.__EXT__))
    			{
    				require $sub_module_path.$segment.__EXT__;

	    			$registry = new stdClass;
	    			$registry->class_name = get_class_name(ACTIVEAPP.'_'.$segment);
	    			$registry->class_path = $sub_module_path;

	    			array_push($this->registry->controller, $registry);

	    			$O2->router->set_active($registry, $slice);
    			}
    		}
    	}
    	//print_code($this->registry->controller);
    }

    public function paths($path_name = 'ALL')
    {
    	if($path_name === 'ALL')
    	{
    		return $this->paths;
    	}
    	elseif(isset($this->paths->{$path_name}))
    	{
    		return $this->paths->{$path_name};
    	}
    }
}