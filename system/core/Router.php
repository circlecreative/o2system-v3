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
 * System Router
 *
 * Parses URIs and determines routing
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/router.html
 */

class Core_Router
{
	private $_config;
	public $default_controller;
	public $error_404_controller;

	public function __construct()
	{
		global $CFG;
		$this->_config = $CFG->item('routes');

		// Route Mapping
		$this->_set_routing();

		// Parse any custom routing that may exist
		$this->_parse_routes();
	}	

	/**
	 * Set the route mapping
	 *
	 * This function determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @access	private
	 * @return	void
	 */
	private function _set_routing()
	{
		// Set the default controller so we can display it in the event
		// the URI doesn't correlated to a valid controller.
		$this->default_controller = ( ! isset($this->_config['default_controller']) OR $this->_config['default_controller'] == '') ? FALSE : strtolower($this->_config['default_controller']);
		$this->error_404_controller = ( ! isset($this->_config['404_override']) OR $this->_config['404_override'] == '') ? 'error/404' : strtolower($this->_config['404_override']);

		if(! isset($this->_config['default_controller']))
		{
			exit('Unable to locate default_controller');
		}

		global $URI;

		if( $URI->string == '' OR 
			$URI->string == $URI->active->lang OR 
			$URI->string == $URI->active->app->name OR
			$URI->string == $URI->active->app->name.'/'.$URI->active->lang
		  )
		{
			$URI->set_uri($URI->active->app->name.'/'.$this->default_controller, TRUE);	
		}

		if(empty($URI->active->method))
		{
			if($URI->is_method_request($URI->segment(3)) === FALSE AND $URI->is_method_request('_remap') === FALSE AND $URI->is_method_request('index') === TRUE)
			{
				$route_uri_string = str_replace($URI->active->controller->name, $URI->active->controller->name.'/index', $URI->string);
				$URI->set_uri($URI->active->app->name.'/'.$route_uri_string, TRUE);
			}
			elseif($URI->is_method_request('_remap') === TRUE)
			{
				$route_uri_string = str_replace($URI->active->controller->name, $URI->active->controller->name.'/_remap', $URI->string);
				$URI->set_uri($URI->active->app->name.'/'.$route_uri_string, TRUE);
			}
			elseif($URI->is_method_request('_remap') === FALSE)
			{
				$this->_error_routes();
			}
		}

		if(empty($URI->active->module) AND empty($URI->active->controller))
		{
			$this->_error_routes();
		}
	}

	private function _error_routes()
	{
		global $URI;
		
		$error_segments = preg_split('[/]', $this->error_404_controller, -1, PREG_SPLIT_NO_EMPTY);
		$error_code = end($error_segments);
		
		array_pop($error_segments);

		$error_segments = array_merge($error_segments, array('index', $error_code));

		$URI->set_uri($URI->active->app->name.'/'.implode('/',$error_segments), TRUE);
	}

	/**
	 *  Parse Routes
	 *
	 * This function matches any routes that may exist in
	 * the config/routes.php file against the URI to
	 * determine if the class/method need to be remapped.
	 *
	 * @access	private
	 * @return	void
	 */
	private function _parse_routes()
	{
		global $URI;

		// Is there a literal match?  If so we're done
		if (isset($this->_config[$URI->string]))
		{
			return $this->_set_request(explode('/', $this->_config[$URI->string]));
		}

		// Loop through the route array looking for wild-cards
		foreach($this->_config as $key => $val)
		{
			// Convert wild-cards to RegEx
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
			//print_code('#^'.$key.'$#');

			// Does the RegEx match?
			if (preg_match('#'.$key.'$#', $URI->string))
			{
				//print_code('match');
				// Do we have a back-reference?
				if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
				{
					$val = preg_replace('#'.$key.'$#', $val, $URI->string);
				}

				$URI->set_uri($URI->active->app->name.'/'.$val);
			}
		}
	}

	// --------------------------------------------------------------------
}