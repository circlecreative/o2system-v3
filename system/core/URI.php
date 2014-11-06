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
 * System URI
 *
 * Parses URIs and determines routing
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/uri.html
 */

class Core_URI
{
	public $segments = array();
	public $raw_segments = array();
	public $string = NULL;
	public $active = NULL;
	public $controllers = array();
	public $methods = array();

	public function __construct()
	{
		$this->set_uri();
	}

	public function base_url()
	{
		if (isset($_SERVER['HTTP_HOST']))
		{
		    $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
		    $base_url .= '://' . $_SERVER['HTTP_HOST'];
		    $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
		}
		else
		{
		    $base_url= 'http://localhost/';
		}

		return $base_url;
	}

	public function set_uri($request = '', $override = FALSE)
	{
		/*if($request == '')
		{
			// Set URI String
			if(! empty($_GET['request']))
			{
				$this->string = $this->_sanitize_segment($_GET['request']);
				unset($_GET['request']);
			}
		}
		else
		{
			$this->string = $this->_sanitize_segment($request);
			unset($request);
		}*/

		if($request == '' AND $override === FALSE)
		{
			$this->_fetch_uri_string();
		}
		elseif($override === TRUE)
		{
			$this->_set_uri_string($request);
		}

		// If the URI contains only a slash we'll kill it
		$this->string = ($this->string == '/') ? '' : $this->string;

		//print_code($this->string);

		// Set RAW Segments
		$this->raw_segments = preg_split('[/]', $this->string, -1, PREG_SPLIT_NO_EMPTY);
		$this->_reindex_segments($this->raw_segments);

		// Set Active Object
		$this->active = new URI_Active;

		$this->controllers = array();

		// Set Core Controller
		$registry = new stdClass;
		$registry->name = 'core';
		$registry->class = 'Core_Controller';
		$registry->path = SYSPATH.'core/';
		$registry->filepath = $registry->path.'Controller'.__EXT__;

		$this->controllers['core'] = $registry;

		// Set Root App Controller
		$registry = new stdClass;
		$registry->name = 'app';
		$registry->class = 'App_Controller';
		$registry->path = BASEPATH.'apps/core/';
		$registry->filepath = $registry->path.$registry->class.__EXT__;

		$this->controllers['app'] = $registry;

		// Validate Request
		$this->_validate_segments();
	}

	/**
	 * Get the URI String
	 *
	 * @access	private
	 * @return	string
	 */
	function _fetch_uri_string()
	{
		global $CFG;

		// Is the request coming from the command line?
		if (php_sapi_name() == 'cli' or defined('STDIN'))
		{
			$this->_set_uri_string($this->_parse_cli_args());
			return;
		}

		// Let's try the REQUEST_URI first, this will work in most situations
		if ($uri = $this->_detect_uri())
		{
			$this->_set_uri_string($uri);
			return;
		}

		// Is there a PATH_INFO variable?
		// Note: some servers seem to have trouble with getenv() so we'll test it two ways
		$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
		if (trim($path, '/') != '' && $path != "/".SELF)
		{
			$this->_set_uri_string($path);
			return;
		}

		// No PATH_INFO?... What about QUERY_STRING?
		$path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
		if (trim($path, '/') != '')
		{
			$this->_set_uri_string($path);
			return;
		}

		// As a last ditch effort lets try using the $_GET array
		if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '')
		{
			$this->_set_uri_string(key($_GET));
			return;
		}

		// We've exhausted all our options...
		$this->string = '';
		return;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the URI String
	 *
	 * @access	public
	 * @param 	string
	 * @return	string
	 */
	function _set_uri_string($string)
	{
		$this->string = $this->_sanitize_segment($string);
	}

	// --------------------------------------------------------------------

	/**
	 * Detects the URI
	 *
	 * This function will detect the URI automatically and fix the query string
	 * if necessary.
	 *
	 * @access	private
	 * @return	string
	 */
	private function _detect_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME']))
		{
			return '';
		}

		$uri = $_SERVER['REQUEST_URI'];
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (strncmp($uri, '?/', 2) === 0)
		{
			$uri = substr($uri, 2);
		}
		$parts = preg_split('#\?#i', $uri, 2);
		$uri = $parts[0];
		if (isset($parts[1]))
		{
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		}
		else
		{
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}

		if ($uri == '/' || empty($uri))
		{
			return '/';
		}

		$uri = parse_url($uri, PHP_URL_PATH);

		// Do some final cleaning of the URI and return it
		return str_replace(array('//', '../'), '/', trim($uri, '/'));
	}

	// --------------------------------------------------------------------

	/**
	 * Parse cli arguments
	 *
	 * Take each command line argument and assume it is a URI segment.
	 *
	 * @access	private
	 * @return	string
	 */
	private function _parse_cli_args()
	{
		$args = array_slice($_SERVER['argv'], 1);

		return $args ? '/' . implode('/', $args) : '';
	}

	// --------------------------------------------------------------------

	/**
	 * Remove the suffix from the URL if needed
	 *
	 * @access	private
	 * @return	void
	 */
	function _remove_url_suffix()
	{
		if  ($this->config->item('url_suffix') != "")
		{
			$this->uri_string = preg_replace("|".preg_quote($this->config->item('url_suffix'))."$|", "", $this->uri_string);
		}
	}

	// --------------------------------------------------------------------

	public function set_active($key, $value)
	{
		$this->active->{$key} = $value;
	}

	/**
     * Sanitize Segment
     * @access private
     */    
    private function _sanitize_segment($string, $delimiter = '_')
    {
        if(! empty($string))
        {
        	// Remove URL Suffix
        	$string = str_replace(array('.html','.htm'), '', $string);

        	// Convert programatic characters to entities
			$bad	= array('$',		'(',		')',		'%28',		'%29');
			$good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');

			$string = str_replace($bad, $good, $string);

        	$string = filter_var($string, FILTER_SANITIZE_URL);

        	$non_displayables = array();
		
			// every control character except newline (dec 10)
			// carriage return (dec 13), and horizontal tab (dec 09)
			
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
			
			$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

			do
			{
				$string = preg_replace($non_displayables, '', $string, -1, $count);
			}
			while ($count);

            $patterns = array(
                '/[\s]+/',
                '/[_]+/',
                '/[-]+/',
                '/-/',
                '/[' . $delimiter . ']+/'
            );
            $replace = array(
                '-',
                '-',
                '-',
                $delimiter,
                $delimiter
            );

            $string = preg_replace($patterns, $replace, $string);
        }
            
        return trim($string);
    }

	public function is_attribute_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

		if(strpos($segments, '@') === 0)
		{
			$this->set_active('attribute', str_replace('@','',$segments));
			return true;
		}
		return false;
	}

	public function is_hash_tag_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

		if(strpos($segments, '#') === 0)
		{
			$this->active_hash_tag = str_replace('#','',$segments);
			return true;
		}
		return false;
	}

	public function is_login_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('login','qrlogin','logoff','logout','activation','forgot_password','reset_password');

		if(in_array($segments, $identifier))
		{
			return true;
		}
		return false;		
	}

	public function is_facebook_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('facebook_app','fb_app','facebook_page','fb_page');

		if(in_array($segments, $identifier))
		{
			$this->user_agent = 'facebook';
			return true;
		}
		return false;
	}

	public function is_mobile_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('m','mb','mobi','mobile');

		if(in_array($segments, $identifier))
		{
			$this->user_agent = 'mobile';
			return true;
		}
		return false;
	}

	public function is_tablet_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('tab','tablet','ipad','pad');

		if(in_array($segments, $identifier))
		{
			$this->user_agent = 'tablet';
			return true;
		}
		return false;
	}

	public function is_desktop_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('desktop','full_site');

		if(in_array($segments, $identifier))
		{
			$this->user_agent = 'desktop';
			return true;
		}
		return false;		
	}

	public function is_form_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('form','create','add_new','edit','update');

		if(in_array($segments, $identifier))
		{
			$this->set_active('request_form', $segments);
			return true;
		}

		return false;
	}	

	public function is_image_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? end($segments) : $segments);

		if(preg_match('/(\.jpg|\.png|\.bmp)$/', $segments))
		{
			return true;
		}
		return false;
	}

	public function is_file_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('attachment','attachments','file','files');

		//print_out($segments);

		if(in_array($segments, $identifier))
		{
			return true;
		}
		return false;
	}

	public function is_id_request($segments = array())
	{
		// Check Segments
		$segments = (empty($segments) ? $this->raw_segments : $segments);
        $segments = (is_array($segments) ? end($segments) : $segments);

        $x_string = explode('_',$segments);

        $id = reset($x_string);
        $title = array_slice($x_string, 1);

        if(is_numeric($id))
        {
        	$this->set_active('request_id', $id);

        	// Normalize Alias
        	if(!is_numeric($title) AND !empty($title))
        	{
        		$title = array_map('ucfirst', $title);
        		$title = implode(' ', $title);

        		$this->set_active('request_title', $title);
        	}

        	return $id;
        }

        return false;
	}

	public function is_pages_request($segments = array())
	{
		
        return false;
	}    

    private function _validate_segments()
    {
		$segments = $this->raw_segments;

		if($this->is_app_request())
        {
        	$key = array_search($this->active->app->name, $segments);
        	unset($segments[$key]);
        }		

        if($this->is_lang_request($segments))
        {
        	$key = array_search($this->active->lang, $segments);
        	unset($segments[$key]);
        }		

        $this->segments = array();

        foreach($segments as $key => $segment)
        {
        	if(
				$this->is_attribute_request($segment) === false AND
				$this->is_hash_tag_request($segment) === false AND
				$this->is_mobile_request($segment) === false AND
				$this->is_tablet_request($segment) === false AND
				$this->is_desktop_request($segment) === false AND
				$this->is_facebook_request($segment) === false
            )
            {
            	// Module Request
            	if($this->is_module_request($segment))
            	{
            		$this->segments[] = $segment;
            	}

            	// Controller Request
            	if($this->is_controller_request($segment))
            	{
            		$this->segments[] = $segment;
            	}

            	// Method Request
            	if($this->is_method_request($segment))
            	{
            		$this->segments[] = $segment;
            	}

            	// ID Request
            	else if($ID = $this->is_id_request($segment))
            	{
            		$this->segments[] = $ID;
            	}

            	// Static Content Request
            	elseif($alias = $this->is_pages_request($segment))
            	{
            		array_unshift($this->segments,'pages','index');
            		$this->segments[] = $alias;
            	}

            	// Login Request
            	elseif($this->is_login_request($segment))
            	{
            		array_unshift($this->segments,'login');
            		$this->segments[] = $segment;
            	}

            	// Other Request
            	else
            	{
            		// Image or File Request
            		if($this->is_image_request($this->raw_segments) OR $this->is_file_request($this->raw_segments))
            		{
            			$this->segments[] = $raw_segment;
            		}
            		else
            		{
            			$this->segments[] = $segment;
            		}
            	}
            }
        }

        $this->segments = array_unique($this->segments);
        $this->_reindex_segments($this->segments);

        $this->set_active('params', array_slice($this->segments, 2));
        
        return $this->segments;
    }

    public function is_app_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        global $PATH;

        if(in_array($segments, $PATH->apps))
        {
        	$registry = new stdClass;
        	$registry->name = $segments;
        	$registry->class =  get_class_name($registry->name . '_Controller');
        	$registry->path = $PATH->{$segments}->path;
        	$registry->filepath = $registry->path . 'core/' . $registry->class . __EXT__;

            $this->set_active('app', $registry);

            $this->controllers[$registry->name] = $registry;

            return true; 
        }
        else
        {
        	$registry = new stdClass;
        	$registry->name = BASEAPP;
        	$registry->class =  get_class_name($registry->name . '_Controller');
        	$registry->path = $PATH->{BASEAPP}->path;
        	$registry->filepath = $registry->path . 'core/' . $registry->class . __EXT__;

            $this->set_active('app', $registry);

            $this->controllers[$registry->name] = $registry;

            return false;
        }
	}

	public function is_module_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        if(empty($this->active->app))
        {
        	$this->active->app = BASEAPP;
        }

        global $PATH;

        $modules = get_object_vars($PATH->{ $this->active->app->name }->modules);
        $modules_names = array_keys($modules);

        if(in_array($segments, $modules_names))
        {
        	$registry = new stdClass;
        	$registry->name = $segments;
        	$registry->class =  get_class_name($this->active->app->name .'_'. $registry->name);

        	$registry->path = $modules[$segments]->path.'controllers/';
        	$registry->filepath = $registry->path . $segments . __EXT__;

            $this->set_active('module', $registry);

            $this->controllers['modules'][$registry->name] = $registry;

            return true; 
        }
        else
        {
            return false;
        }
	}

	public function is_controller_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        if(! empty($this->active->module->path))
        {
        	$controller_path = $this->active->module->path;

        	if(is_dir($controller_path . $segments))
        	{
        		$controller_path = $controller_path . $segments . '/';
        	}

        	if(! empty($this->active->controller->path))
        	{
        		$controller_path = $this->active->controller->path;

        		if(is_dir($controller_path . $segments))
	        	{
	        		$controller_path = $this->active->controller->path . $segments . '/';
	        	}
        	}

        	if(is_file($controller_path .$segments . __EXT__))
    		{
    			$registry = new stdClass;
        		$registry->name = $segments;
        		$registry->class = get_class_name($this->active->app->name .'_'. $registry->name);
        		$registry->path = $controller_path;
        		$registry->filepath = $controller_path . $segments . __EXT__;

    			$this->set_active('controller', $registry);

    			if(! isset($this->controllers['modules'][$registry->name]))
    			{
    				$this->controllers['modules'][$registry->name] = $registry;
    			}

    			return true;
    		}
        }
        else
        {
        	global $PATH;
        	
        	if(isset($PATH->{ 'app' }->controllers->files[$segments]))
    		{
    			if(file_exists($PATH->{ 'app' }->controllers->files[$segments]))
    			{
    				$registry = new stdClass;
	        		$registry->name = $segments;
	        		$registry->class = get_class_name('App_'.$registry->name);
	        		$registry->path = $PATH->{ 'app' }->controllers->files[$segments];
	        		$registry->filepath = $PATH->{ 'app' }->controllers->files[$segments];

	    			$this->set_active('controller', $registry);

	    			$this->controllers['standard'][ strtolower($registry->class) ] = $registry;
    			}
    		}

    		if(isset($PATH->{ $this->active->app->name }->controllers->files[$segments]))
    		{
    			if(file_exists($PATH->{ $this->active->app->name }->controllers->files[$segments]))
    			{
    				$registry = new stdClass;
    				$registry->name = $segments;
    				$registry->class = get_class_name($this->active->app->name.'_'.$registry->name);
	        		$registry->path = $PATH->{ $this->active->app->name }->controllers->files[$segments];
	        		$registry->filepath = $PATH->{ $this->active->app->name }->controllers->files[$segments];

	    			$this->set_active('controller', $registry);

	    			$this->controllers['standard'][ strtolower($registry->class) ] = $registry;

	    			if(empty($this->active->controller))
	    			{
	    				$this->set_active('controller', $registry);
	    			}
    			}
    		}

    		if(!empty($this->active->controller))
    		{
    			return TRUE;
    		}
        }

        return FALSE;
	}

	public function is_method_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        if(!empty($this->controllers))
        {
        	$class_name = '';

        	foreach($this->controllers as $app => $registry)
        	{
        		if(is_object($registry))
        		{
        			require_once($registry->filepath);
        			$class_name = $registry->class;
        		}
        		elseif(is_array($registry))
        		{
        			foreach($registry as $module => $sub_registry)
        			{
        				require_once($sub_registry->filepath);
        				$class_name = $sub_registry->class;
        			}
        		}
        	}

        	$RC = new ReflectionClass($class_name); 
    		$MTD = $RC->getMethods(ReflectionMethod::IS_PUBLIC);

    		$this->methods = array();

    		foreach($MTD as $method)
    		{
    			if(! in_array($method, $this->methods)) $this->methods[] = $method->name;
    		}

    		if(!empty($this->methods) AND in_array($segments, $this->methods))
    		{
    			$this->set_active('method', $segments);
    			return TRUE;
    		}
        }

        return FALSE;
	}

	public function is_lang_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        $lang = array('en','id');

		if(in_array($segments, $lang))
		{
			$this->set_active('lang', $segments);

			return true;
		}
		return false;
	}

    // --------------------------------------------------------------------
	/**
	 * Re-index Segments
	 *
	 * This function re-indexes the $this->segment array so that it
	 * starts at 1 rather than 0.  Doing so makes it simpler to
	 * use functions like $this->uri->segment(n) since there is
	 * a 1:1 relationship between the segment array and the actual segments.
	 *
	 * @access	private
	 * @return	void
	 */
	private function _reindex_segments(&$segments)
	{
		$segments = array_unique($segments);
		array_unshift($segments, NULL);
		unset($segments[0]);
	}

	public function segment($index)
	{
		return isset($this->segments[$index]) ? $this->segments[$index] : FALSE;
	}
}

class URI_Active 
{
	public $app;
	public $lang;
	public $module;
	public $controller;
	public $method;
	public $params;
}