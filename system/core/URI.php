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
class O2_URI
{
	/**
	 * Class Configuration
	 *
	 * @var	array
	 */
	protected $_config = array();

	/**
	 * List of requested URI segments
	 *
	 * Starts at 1 instead of 0.
	 *
	 * @var	array
	 */
	public $segments = array();

	/**
	 * List of requested URI RAW segments
	 *
	 * Raw requested segments
	 *
	 * @var	array
	 */
	public $raw_segments = array();

	/**
	 * String of requested URI segments
	 *
	 * @var	string
	 */
	public $string = NULL;

	/**
	 * Base URL
	 *
	 * @var	string
	 */
	public $base_url = NULL;

	/**
	 * App URL
	 *
	 * @var	string
	 */
	public $app_url = NULL;

	/**
	 * System URL
	 *
	 * @var	string
	 */
	public $system_url = NULL;

	/**
	 * Special request registry
	 *
	 * @var	object
	 */
	public $request = NULL;	

	/**
	 * List of requested controllers
	 *
	 * @var	array
	 */
	public $controllers = array();

	/**
	 * List of requested controller methods
	 *
	 * @var	array
	 */	
	public $methods = array();

	/**
	 * Permitted URI chars
	 *
	 * PCRE character group allowed in URI segments
	 *
	 * @var	string
	 */
	protected $_permitted_chars;	

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		// Set URI Configuration
		$this->_config = config_item('URI');

		// Set Base URL
		$this->get_base_url();

		// Set System URL
		$this->get_system_url();

		// Set URI Request
		$this->_set();

		// Set App URL
		$this->get_app_url();

		log_message('debug', "URI Class Initialized");
	}

	/**
	 * Get the Base URL
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_base_url()
	{
		if (isset($_SERVER['HTTP_HOST']))
		{
		    $this->base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
		    $this->base_url .= '://' . $_SERVER['HTTP_HOST'];
		    $this->base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
		}
		else
		{
		    $this->base_url= 'http://localhost/';
		}

		return $this->base_url;
	}

	/**
	 * Get the System URL
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_system_url()
	{
		global $system_path;
		$this->system_url = $this->base_url.$system_path.'/';

		return $this->system_url;
	}		

	/**
	 * Get the Active App URL
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_app_url($uri = NULL, $suffix = '/')
	{
		global $apps_path;
		$app_uri = array($apps_path);

		if(isset($this->request->app->parameter))
		{
			$app_uri[] = $this->request->app->parameter;
		}

		if(is_array($uri))
		{
			$app_uri = array_merge($app_uri, $uri);
		}
		else
		{
			$app_uri[] = $uri;
		}

		$app_uri = implode('/', $app_uri);

		return $this->base_url . $app_uri . $suffix;
	}	

	/**
	 * Get the Suffix URL
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_suffix_url()
	{
		return $this->_config['suffix'];
	}	

	/**
	 * Set the URI Registry
	 *
	 * @access	public
	 * @return	void
	 */
	public function _set($request = '', $override = FALSE)
	{
		if(empty($request) AND $override === FALSE)
		{
			$this->_fetch_uri_string();
		}
		elseif($override === TRUE)
		{
			$this->_set_uri_string($request);
		}

		// Set Active Object
		$this->request = new O2_System_URI_Request_Registry;

		// Set Controllers Registry
		$this->controllers = array();

		// Set Core Controller
		$registry = new O2_System_Class_Registry;
		$registry->parameter = 'core';
		$registry->name = 'O2_Controller';
		$registry->path = SYSPATH.'core/';
		$registry->filepath = $registry->path.'Controller'.__EXT__;

		$this->controllers['core'] = $registry;

		// Set Root App Controller
		$registry = new O2_System_Class_Registry;
		$registry->parameter = 'app';
		$registry->name = 'App_Controller';
		$registry->path = BASEPATH.'apps/core/';
		$registry->filepath = $registry->path.$registry->name.__EXT__;

		$this->controllers['app'] = $registry;

		// Validate Request
		$this->_validate_segments();
	}

	/**
	 * Fetch the URI String
	 *
	 * @access	private
	 * @return	string
	 */
	private function _fetch_uri_string()
	{
		// If query strings are enabled, we don't need to parse any segments.
		// However, they don't make sense under CLI.
		$this->_permitted_chars = $this->_config['permitted_chars'];

		// If it's a CLI request, ignore the configuration
		if (is_cli() OR ($protocol = strtoupper($this->_config['protocol'])) === 'CLI')
		{
			$this->_set_uri_string($this->_parse_argv());
		}
		elseif ($protocol === 'AUTO')
		{
			// Is there a PATH_INFO variable? This should be the easiest solution.
			if (isset($_SERVER['PATH_INFO']))
			{
				$this->_set_uri_string($_SERVER['PATH_INFO']);
			}
			// No PATH_INFO? Let's try REQUST_URI or QUERY_STRING then
			elseif (($uri = $this->_parse_request_uri()) !== '' OR ($uri = $this->_parse_query_string()) !== '')
			{
				$this->_set_uri_string($uri);
			}
			// As a last ditch effor, let's try using the $_GET array
			elseif (is_array($_GET) && count($_GET) === 1 && trim(key($_GET), '/') !== '')
			{
				$this->_set_uri_string(key($_GET));
			}
		}
		elseif (method_exists($this, ($method = '_parse_'.strtolower($protocol))))
		{
			$this->_set_uri_string($this->$method());
		}
		else
		{
			$uri = isset($_SERVER[$protocol]) ? $_SERVER[$protocol] : @getenv($protocol);
			$this->_set_uri_string($uri);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set URI String
	 *
	 * @param 	string	$string
	 * @return	void
	 */
	protected function _set_uri_string($string)
	{
		// Filter out control characters and trim slashes
		$this->string = trim(remove_invisible_characters($string, FALSE), '/');

		// If the URI contains only a slash we'll kill it
		$this->string = ($this->string == '/') ? '' : $this->string;

		if ($this->string !== '')
		{
			// Remove the URL suffix, if present
			if ($suffix = (string) $this->_config['suffix'] !== '')
			{
				$slen = strlen($suffix);

				if (substr($this->string, -$slen) === $suffix)
				{
					$this->string = substr($this->string, 0, -$slen);
				}
			}

			// Set RAW Segments
			$segments = preg_split('[/]', $this->string, -1, PREG_SPLIT_NO_EMPTY);

			$this->raw_segments[0] = NULL;
			// Populate the segments array
			foreach ($segments as $segment)
			{
				// Filter segments for security
				$segment = trim($this->filter_segment($segment));

				if ($segment !== '')
				{
					$this->raw_segments[] = $segment;
				}
			}

			unset($this->raw_segments[0]);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Parse REQUEST_URI
	 *
	 * Will parse REQUEST_URI and automatically detect the URI from it,
	 * while fixing the query string if necessary.
	 *
	 * Borrowed from CodeIgniter 3.0-dev URI Class
	 * 
	 * @author	    EllisLab Dev Team
	 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
	 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
	 * @license	    http://opensource.org/licenses/MIT	MIT License
	 * @link	    http://codeigniter.com
	 * 
	 * @return	string
	 */
	protected function _parse_request_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']))
		{
			return '';
		}

		$uri = parse_url($_SERVER['REQUEST_URI']);
		$query = isset($uri['query']) ? $uri['query'] : '';
		$uri = isset($uri['path']) ? rawurldecode($uri['path']) : '';

		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0)
		{
			$query = explode('?', $query, 2);
			$uri = rawurldecode($query[0]);
			$_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
		}
		else
		{
			$_SERVER['QUERY_STRING'] = $query;
		}

		parse_str($_SERVER['QUERY_STRING'], $_GET);

		if ($uri === '/' OR $uri === '')
		{
			return '/';
		}

		// Do some final cleaning of the URI and return it
		return $this->_remove_relative_directory($uri);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse QUERY_STRING
	 *
	 * Will parse QUERY_STRING and automatically detect the URI from it.
	 *
 	 * Borrowed from CodeIgniter 3.0-dev URI Class
	 * 
	 * @author	    EllisLab Dev Team
	 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
	 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
	 * @license	    http://opensource.org/licenses/MIT	MIT License
	 * @link	    http://codeigniter.com
	 *
	 * @return	string
	 */
	protected function _parse_query_string()
	{
		$uri = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');

		if (trim($uri, '/') === '')
		{
			return '';
		}
		elseif (strncmp($uri, '/', 1) === 0)
		{
			$uri = explode('?', $uri, 2);
			$_SERVER['QUERY_STRING'] = isset($uri[1]) ? $uri[1] : '';
			$uri = rawurldecode($uri[0]);
		}

		parse_str($_SERVER['QUERY_STRING'], $_GET);

		return $this->_remove_relative_directory($uri);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse CLI arguments
	 *
	 * Take each command line argument and assume it is a URI segment.
	 *
	 * Borrowed from CodeIgniter 3.0-dev URI Class
	 * 
	 * @author	    EllisLab Dev Team
	 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
	 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
	 * @license	    http://opensource.org/licenses/MIT	MIT License
	 * @link	    http://codeigniter.com
	 *
	 * @return	string
	 */
	protected function _parse_argv()
	{
		$args = array_slice($_SERVER['argv'], 1);
		return $args ? implode('/', $args) : '';
	}	

	// --------------------------------------------------------------------

	/**
	 * Remove relative directory (../) and multi slashes (///)
	 *
	 * Do some final cleaning of the URI and return it, currently only used in self::_parse_request_uri()
  	 *
	 * Borrowed from CodeIgniter 3.0-dev URI Class
	 * 
	 * @author	    EllisLab Dev Team
	 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
	 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
	 * @license	    http://opensource.org/licenses/MIT	MIT License
	 * @link	    http://codeigniter.com
	 *
	 * @param	string	$url
	 * @return	string
	 */
	protected function _remove_relative_directory($uri)
	{
		$uris = array();
		$tok = strtok($uri, '/');
		while ($tok !== FALSE)
		{
			if (( ! empty($tok) OR $tok === '0') && $tok !== '..')
			{
				$uris[] = $tok;
			}
			$tok = strtok('/');
		}

		return implode('/', $uris);
	}

	// --------------------------------------------------------------------

	/**
	 * Filter Segment
	 *
	 * Filters segments for malicious characters.
 	 *
	 * Borrowed from CodeIgniter 3.0-dev URI Class
	 * 
	 * @author	    EllisLab Dev Team
	 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
	 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
	 * @license	    http://opensource.org/licenses/MIT	MIT License
	 * @link	    http://codeigniter.com
	 *
	 * @param	string	$string
	 * @return	string
	 */
	public function filter_segment($string)
	{
		$charset = config_item('charset');

		if ( ! empty($string) && ! empty($this->_permitted_chars) && ! preg_match('/^['.$this->_permitted_chars.']+$/i'.(strtoupper($charset) === 'UTF-8' ? 'u' : ''), $string))
		{
			show_error('The URI you submitted has disallowed characters.', 400);
		}

		// Convert programatic characters to entities and return
		return str_replace(
			array('$',     '(',     ')',     '%28',   '%29','-', $this->_config['suffix']),	// Bad
			array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;','_',''),	// Good
			$string
		);
	}

	// --------------------------------------------------------------------	

	/**
	 * Set URI Request Registry
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _set_request($key, $value)
	{
		$this->request->{$key} = $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URI Segment
	 *
 	 * Borrowed from CodeIgniter 3.0-dev URI Class
	 * 
	 * @author	    EllisLab Dev Team
	 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
	 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
	 * @license	    http://opensource.org/licenses/MIT	MIT License
	 * @link	    http://codeigniter.com
	 *
	 * @see		CI_URI::$segments
	 * @param	int 	$index Index
	 * @param	mixed	$no_result	What to return if the segment index is not found
	 * @return	mixed
	 */
	public function segment($index, $no_result = NULL)
	{
		return isset($this->segments[$index]) ? $this->segments[$index] : $no_result;
	}
	// --------------------------------------------------------------------

	/**
	 * Total number of segments
 	 *
 	 * Borrowed from CodeIgniter 3.0-dev URI Class
	 * 
	 * @author	    EllisLab Dev Team
	 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
	 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
	 * @license	    http://opensource.org/licenses/MIT	MIT License
	 * @link	    http://codeigniter.com
	 *
	 * @return	int
	 */
	public function total_segments()
	{
		return count($this->segments);
	}

	// --------------------------------------------------------------------

	/**
	 * Validate requested URI Segments
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
    private function _validate_segments($segments = array())
    {
		$segments = (empty($segments) ? $this->raw_segments : $segments);

		// Determine App Request
		if($this->is_app_request())
        {
        	$key = array_search($this->request->app->parameter, $segments);
        	unset($segments[$key]);
        }		

        // Determine Lang Request
        if($this->is_lang_request($segments))
        {
        	$key = array_search($this->request->lang, $segments);
        	unset($segments[$key]);
        }		

        $this->segments[0] = NULL;

        foreach($segments as $key => $segment)
        {
        	if(
				$this->is_attribute_request($segment) === FALSE AND
				$this->is_mobile_request($segment) === FALSE AND
				$this->is_tablet_request($segment) === FALSE AND
				$this->is_desktop_request($segment) === FALSE AND
				$this->is_facebook_request($segment) === FALSE
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

            	// Page Request
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
            			$this->segments = $this->raw_segments;
            		}
            		else
            		{
            			$this->segments[] = $segment;
            		}
            	}
            }
        }

        $this->segments = array_unique($this->segments);
        
        unset($this->segments[0]);

        $diff_segments = array_diff($this->segments, array('index', '_remap', @$this->request->method->name));

        $this->_set_request('params', array_slice($diff_segments, 2));
        
        return $this->segments;
    }

	/**
	 * Determine is the requested segments is application request
	 *
	 * @access	private
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
    private function is_app_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        global $O2SYSTEM;

        if(in_array($segments, $O2SYSTEM->apps))
        {
        	$registry = new O2_System_Class_Registry;
        	$registry->parameter = $segments;
        	$registry->name =  prepare_class_name($registry->parameter . '_Controller');
        	$registry->path = $O2SYSTEM->{$segments}->path;
        	$registry->filepath = $registry->path . 'core/' . $registry->name . __EXT__;

            $this->_set_request('app', $registry);

            $this->controllers[$registry->parameter] = $registry;

            return TRUE; 
        }
        else
        {
        	$registry = new O2_System_Class_Registry;
        	$registry->parameter = BASEAPP;
        	$registry->name =  prepare_class_name($registry->parameter . '_Controller');
        	$registry->path = $O2SYSTEM->{BASEAPP}->path;
        	$registry->filepath = $registry->path . 'core/' . $registry->name . __EXT__;

            $this->_set_request('app', $registry);

            $this->controllers[$registry->parameter] = $registry;

            return FALSE;
        }
	}

	/**
	 * Determine is the requested segments is module request
	 *
	 * @access	private
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	private function is_module_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        if(empty($this->request->app))
        {
        	$this->request->app = BASEAPP;
        }

        global $O2SYSTEM;

        $modules = get_object_vars($O2SYSTEM->{ $this->request->app->parameter }->modules);
        $modules_names = array_keys($modules);

        if(in_array($segments, $modules_names))
        {
        	$registry = new O2_System_Class_Registry;
        	$registry->parameter = $segments;
        	$registry->name =  prepare_class_name($this->request->app->parameter .'_'. $registry->parameter);

        	$registry->path = $modules[$segments]->path;
        	$registry->filepath = $registry->path . 'controllers/' . $segments . __EXT__;

            $this->_set_request('module', $registry);

            $this->controllers['modules'][$registry->parameter] = $registry;

            return TRUE; 
        }
        else
        {
            return FALSE;
        }
	}

	/**
	 * Determine is the requested segments is controller request
	 *
	 * @access	private
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	private function is_controller_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        if(! empty($this->request->module->path))
        {
        	$controller_path = $this->request->module->path.'controllers/';

        	if(is_dir($controller_path . $segments))
        	{
        		$controller_path = $controller_path . $segments . '/';
        	}

        	if(! empty($this->request->controller->path))
        	{
        		$controller_path = $this->request->controller->path;

        		if(is_dir($controller_path . $segments))
	        	{
	        		$controller_path = $this->request->controller->path . $segments . '/';
	        	}
        	}

        	if(is_file($controller_path .$segments . __EXT__))
    		{
    			$registry = new O2_System_Class_Registry;
        		$registry->parameter = $segments;
        		$registry->name = prepare_class_name($this->request->app->parameter .'_'. $registry->parameter);
        		$registry->path = $controller_path;
        		$registry->filepath = $controller_path . $segments . __EXT__;

    			$this->_set_request('controller', $registry);

    			if(! isset($this->controllers['modules'][$registry->parameter]))
    			{
    				$this->controllers['modules'][$registry->parameter] = $registry;
    			}

    			return TRUE;
    		}
        }
        else
        {
        	global $O2SYSTEM;
        	
        	if(isset($O2SYSTEM->{ 'app' }->controllers->files[$segments]))
    		{
    			if(file_exists($O2SYSTEM->{ 'app' }->controllers->files[$segments]))
    			{
    				$registry = new O2_System_Class_Registry;
	        		$registry->parameter = $segments;
	        		$registry->name = prepare_class_name('App_'.$registry->parameter);
	        		$registry->path = $O2SYSTEM->{ 'app' }->controllers->files[$segments];
	        		$registry->filepath = $O2SYSTEM->{ 'app' }->controllers->files[$segments];

	    			$this->_set_request('controller', $registry);

	    			$this->controllers['standard'][ strtolower($registry->name) ] = $registry;
    			}
    		}

    		if(isset($O2SYSTEM->{ $this->request->app->parameter }->controllers->files[$segments]))
    		{
    			if(file_exists($O2SYSTEM->{ $this->request->app->parameter }->controllers->files[$segments]))
    			{
    				$registry = new stdClass;
    				$registry->parameter = $segments;
    				$registry->name = prepare_class_name($this->request->app->parameter.'_'.$registry->parameter);
	        		$registry->path = $O2SYSTEM->{ $this->request->app->parameter }->controllers->files[$segments];
	        		$registry->filepath = $O2SYSTEM->{ $this->request->app->parameter }->controllers->files[$segments];

	    			$this->_set_request('controller', $registry);

	    			$this->controllers['standard'][ strtolower($registry->name) ] = $registry;

	    			if(empty($this->request->controller))
	    			{
	    				$this->_set_request('controller', $registry);
	    			}
    			}
    		}

    		if(!empty($this->request->controller))
    		{
    			return TRUE;
    		}
        }

        return FALSE;
	}

	/**
	 * Determine is the requested segments is active controller method request
	 *
	 * @access	private
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	private function is_method_request($segments = array())
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
        			$class_name = $registry->name;
        		}
        		elseif(is_array($registry))
        		{
        			foreach($registry as $module => $sub_registry)
        			{
        				require_once($sub_registry->filepath);
        				$class_name = $sub_registry->name;
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
    			$this->_set_request('method', $segments);
    			return TRUE;
    		}
        }

        return FALSE;
	}

	/**
	 * Determine is the requested segments is language request
	 *
	 * @access	private
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	private function is_lang_request($segments = array())
	{
		global $O2SYSTEM;

		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

		if(in_array($segments, $O2SYSTEM->lang))
		{
			$this->_set_request('lang', $segments);

			return TRUE;
		}
		else
		{
			$this->_set_request('lang', config_item('lang'));
		}

		return FALSE;
	}

    // --------------------------------------------------------------------

    /**
	 * Determine is the requested segments is attribute request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	public function is_attribute_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

		if (strpos($segments, '@') !== FALSE)
		{
			$this->_set_request('attribute', str_replace('@','',$segments));
			return TRUE;
		}
		return FALSE;
	}

    // --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is login controller request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	public function is_login_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('login','qrlogin','logoff','logout','activation','forgot_password','reset_password');

		if(in_array($segments, $identifier))
		{
			return TRUE;
		}
		return FALSE;		
	}

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is facebook app request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
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
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is mobile request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
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
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is tablet request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
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
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is desktop request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
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
			return TRUE;
		}
		return FALSE;		
	}

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is form request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	public function is_form_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('form','create','add_new','edit','update');

		if(in_array($segments, $identifier))
		{
			$this->_set_request('request_form', $segments);
			return TRUE;
		}

		return FALSE;
	}	

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is image request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	public function is_image_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? end($segments) : $segments);

		if(preg_match('/(\.jpg|\.png|\.bmp)$/', $segments))
		{
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is file request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	public function is_file_request($segments = array())
	{
		// Check Segments
		$segments = ( empty($segments) ? $this->raw_segments : $segments );
        $segments = (is_array($segments) ? reset($segments) : $segments);

        // Define Identifier		
		$identifier = array('attachment','attachments','file','files');

		if(in_array($segments, $identifier))
		{
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is have ID request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
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
        	$this->_set_request('request_id', $id);

        	// Normalize Alias
        	if(!is_numeric($title) AND !empty($title))
        	{
        		$title = array_map('ucfirst', $title);
        		$title = implode(' ', $title);

        		$this->_set_request('request_title', $title);
        	}

        	return $id;
        }

        return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Determine is the requested segments is page request
	 *
	 * @access	public
	 * @param   $segments Array of URI segments
	 * @return	boolean
	 */
	public function is_pages_request($segments = array())
	{
        return FALSE;
	} 

	// --------------------------------------------------------------------
}

/* End of file URI.php */
/* Location: ./system/core/URI.php */