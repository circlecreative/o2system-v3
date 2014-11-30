<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

/**
 * Session
 *
 * Session Core Class
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/session.html
 */

class O2_Session 
{
	private $_name;
	private $_apc_support = FALSE;
	private $_config;
	private $system_data;

	public $userdata;
	public $ip_address = FALSE;
	public $request = FALSE;
	public $headers = FALSE;

    /**
     * Class constructor
     *
     * @return  void
     */
	public function __construct()
	{
		$this->_apc_support = @extension_loaded('apc');

		// Set Config
		global $CFG, $URI;
		$this->_config = new O2_System_Config_Registry( $CFG->item('session') );
		$this->_config->encryption_key = $CFG->item('encryption_key');

		$this->_config->cookie = new O2_System_Config_Registry( $CFG->item('cookie') );

		// Set Session Name
		$this->_name = $this->_config->cookie->name.'_'.$URI->request->app->parameter;

		// Set Session IP Address
		$this->ip_address();

		// Set Session Request
		$this->request();

		// Set Session Headers
		$this->headers();

		// Run the Session routine. If a session doesn't exist we'll
		// create a new one.  If it does, we'll update it.
		if($this->read() === FALSE)
		{
			$this->create();
		}
		else
		{
			$this->update();
		}
	}

	private function initialize()
	{
		$last_activity = $this->userdata('last_activity');
        $ip_address = $this->userdata('ip_address');
        $user_agent = $this->userdata('user_agent');

        if(!empty($last_activity) AND (($last_activity + $this->_config->expired) < now() OR $last_activity > $this->request->time)) 
        {
            // Expired - destroy
            //log_message('debug', 'Session: Expired');
            $destroy = TRUE;
        } 
        elseif($this->_config->match_ip === TRUE AND !empty($ip_address) AND $ip_address !== $this->request->ip_address) 
        {
            // IP doesn't match - destroy
            //log_message('debug', 'Session: IP address mismatch');
            $destroy = TRUE;
        } 
        elseif ($this->_config->match_useragent === TRUE && !empty($user_agent) AND $user_agent !== trim(substr($this->request->user_agent(), 0, 50))) 
        {
            // Agent doesn't match - destroy
            //log_message('debug', 'Session: User Agent string mismatch');
            $destroy = TRUE;
        }

        if(! isset($destroy)) 
        {
        	if($this->request->time > ($this->userdata('last_activity') + $this->_config->updated)) 
	        {
	            $this->update();
	        }

            return;
        }
        else
        {
        	$this->destroy();
        }

		$this->create();
	}

	public function create()
	{
		$session_id = '';

		while (strlen($session_id) < 32)
		{
			$session_id .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		$session_id .= $this->ip_address;

		$this->system_data = new O2_System_Registry;
		@$this->system_data->session->name = $this->_name;
		@$this->system_data->session->id = md5(uniqid($session_id, TRUE));
		@$this->system_data->session->start = $this->request->time;
		@$this->system_data->session->end = $this->request->time;
		@$this->system_data->user->name = 'Guest';
		@$this->system_data->user->id = '0';
		@$this->system_data->user->ip_address = $this->ip_address;
		@$this->system_data->user->agent = substr($this->request->agent, 0, 120);
		@$this->system_data->security->csrf_token = $this->request->csrf_token;

		$this->write();
	}

	public function read()
	{
		global $IN;

		// Load Session From Native Browser COOKIE
		if($_COOKIE_SESSION = $IN->cookie($this->_config->cookie->name) AND empty($this->system_data))
		{
			$this->system_data = new O2_System_Registry( json_decode($_COOKIE_SESSION, TRUE) );
		}

		if($_COOKIE_SESSION = $IN->cookie($this->_name) AND empty($this->userdata))
		{
			$this->userdata = new O2_System_Registry( json_decode($_COOKIE_SESSION, TRUE) );
		}

		// Load Session From Native PHP Session
		if($this->_config->use_native === TRUE)
		{
			if(! isset($_SESSION))
			{
				session_start();
				session_name($this->_config->cookie->name);
			}

			if(! empty($_SESSION))
			{
				$_NATIVE_SESSION = $_SESSION[$this->_config->cookie->name];
				$this->system_data = $_SESSION['system_data'];
				$this->userdata = $_SESSION[$this->_name];
			}
		}

		// Load Session From Native PHP APC Cache
		if($this->_apc_support === TRUE AND $this->_config->use_apc === TRUE)
		{
			$_APC_SESSION = apc_fetch($this->_config->cookie->name);
			$_APC_SESSION = new O2_System_Registry( json_decode($_APC_SESSION, TRUE) );

			$this->system_data = $_APC_SESSION->system_data;
			$this->userdata = $_APC_SESSION->{$this->_name};
		}

		// Load Session From Database


		if(empty($this->system_data))
		{
			return FALSE;
		}

		$system_data = $this->system_data;

		// Is the session current?
		if((@$system_data->session->end + $this->_config->expired) < $this->request->time)
		{
			$this->destroy();
			return FALSE;
		}

		// Does the IP Match?
		if ($this->_config->match_ip == TRUE AND $system_data->user->ip_address != $this->ip_address)
		{
			$this->destroy();
			return FALSE;
		}

		// Does the User Agent Match?
		if ($this->_config->match_useragent == TRUE AND trim($system_data->user->agent) != trim(substr($this->request->agent, 0, 120)))
		{
			$this->destroy();
			return FALSE;
		}

		// Session is valid!
		return TRUE;
	}

	public function update()
	{
		global $IN;

		// Load Session From Native Browser COOKIE
		if($_OLD_SESSION = $IN->cookie($this->_config->cookie->name))
		{
			$old_system_data = new O2_System_Registry( json_decode($_OLD_SESSION, TRUE) );
		}

		$new_session_id = '';

		while (strlen($new_session_id) < 32)
		{
			$new_session_id.= mt_rand(0, mt_getrandmax());
		}

		$this->system_data = new O2_System_Registry;
		@$this->system_data->session->name = $this->_name;
		@$this->system_data->session->id = md5(uniqid($new_session_id, TRUE));
		@$this->system_data->session->start = $old_system_data->session->end;
		@$this->system_data->session->end = $this->request->time;
		@$this->system_data->user->name = 'Guest';
		@$this->system_data->user->id = '0';
		@$this->system_data->user->ip_address = $this->ip_address;
		@$this->system_data->user->agent = substr($this->request->agent, 0, 120);
		@$this->system_data->security->csrf_token = $this->request->csrf_token;

		$this->write();
	}

	public function set_userdata($items = array(), $data = NULL)
	{
		if (is_string($items))
		{
			$items = array($items => $data);
		}

		if (count($items) > 0)
		{
			foreach($items as $key => $value)
			{
				@$this->userdata->{$key} = $value;
			}
		}

		$this->write();
	}

	public function userdata($item = '')
	{
		if($item == '') return (empty($this->userdata) ? NULL : $this->userdata);

		return (empty($this->userdata->{$item}) ? NULL : $this->userdata->{$item});
	}

	private function write()
	{
        $system_data = $this->system_data;
        $system_data = json_encode($system_data);

		$userdata = json_encode($this->userdata);

		// Write Session to COOKIE
		if ($this->_config->cookie->encrypt == TRUE)
		{
			$ENC =& load_class('Encrypt', 'libraries', 'CI');
			$system_data = $ENC->encode($system_data);
			$userdata = $ENC->encode($userdata);
		}
		else
		{
			// if encryption is not used, we provide an md5 hash to prevent userside tampering
			$userdata = $userdata.md5($userdata.$this->_config->encryption_key);
		}

		$expire = ($this->_config->expired_on_close === TRUE) ? 0 : $this->_config->expired + time();

		// Set system cookie
		setcookie(
			$this->_config->cookie->name,
			$system_data,
			$expire,
			$this->_config->cookie->path,
			$this->_config->cookie->domain,
			$this->request->secure
		);

		// Set userdata cookie
		setcookie(
			$this->_name,
			$userdata,
			$expire,
			$this->_config->cookie->path,
			$this->_config->cookie->domain,
			$this->request->secure
		);

		// Set CSRF cookie
		setcookie(
			$this->_config->cookie->name.'_csrf',
			$this->request->csrf_token,
			$expire,
			$this->_config->cookie->path,
			$this->_config->cookie->domain,
			$this->request->secure
		);

		$this->cookie = $_COOKIE;

		// Write Session to PHP Session
		if($this->_config->use_native === TRUE)
		{
			$_SESSION['system_data'] = $system_data;
			$_SESSION[$this->_name] = $userdata;

			$this->native_session = $_SESSION;
		}

		// Write Session to APC
		if($this->_apc_support === TRUE AND $this->_config->use_apc === TRUE)
		{
			$_APC_SESSION['system_data'] = $system_data;
			$_APC_SESSION[$this->_name] = $userdata;

			apc_store($this->_config->cookie->name, json_encode($_APC_SESSION), $this->_config->expired);

			$this->apc = apc_fetch($this->_config->cookie->name);
		}

		// Write Session to Database
	}

	public function destroy()
	{
        if (isset($_COOKIE[$this->_config->cookie->name])) 
        {
            // Clear session cookie
            $params = session_get_cookie_params();
            setcookie($this->_config->cookie->name, '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            unset($_COOKIE[$this->_config->cookie->name]);
        }

        if($this->_config->use_native === TRUE)
        {
	        if(isset($_SESSION))
	        {
	        	session_destroy();
	        	unset($_SESSION);
	        }
	    }

        if($this->_apc_support === TRUE)
        {
        	if(apc_exists($this->_config->cookie->name))
        	{
        		apc_delete($this->_config->cookie->name);
        	}
        }

        $this->create();
	}

	public function headers()
	{
		if ($this->headers !== FALSE)
		{
			return $this->headers;
		}

		global $IN;

		foreach($IN->request_headers() as $header_name => $header_data)
		{
			$this->headers[$header_name] = $IN->get_request_header($header_name, TRUE);
		}
	}

	public function request()
	{
		if ($this->request !== FALSE)
		{
			return $this->request;
		}

		$this->request = new O2_System_Registry;

		if(!empty($_SERVER))
		{
			if(isset($_SERVER['REQUEST_SCHEME']))
			{
				$this->request->scheme = $_SERVER['REQUEST_SCHEME'];

				if($this->request->scheme === 'https')
				{
					$this->request->secure = TRUE;
				}
				else
				{
					$this->request->secure = FALSE;
				}
			}

			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
			{
				$this->request->ajax = TRUE;
			}
			else
			{
				$this->request->ajax = FALSE;
			}

			$this->request->cli = (php_sapi_name() === 'cli' OR defined('STDIN'));

			if(isset($_SERVER['HTTP_USER_AGENT']))
			{
				$this->request->agent = $_SERVER['HTTP_USER_AGENT'];
			}

			if(isset($_SERVER['REQUEST_METHOD']))
			{
				$this->request->method = $_SERVER['REQUEST_METHOD'];
			}

			if(isset($_SERVER['REQUEST_URI']))
			{
				$request_uri = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);

				$this->request->uri = str_replace(array($request_uri, '.html', '.htm'), '', $_SERVER['REQUEST_URI']);
			}

			$this->request->time = time();

			// If it's not a POST request we will set the CSRF cookie
			global $IN;

			// Load Session From Native Browser COOKIE
			if($_CSRF_SESSION = $IN->cookie($this->_config->cookie->name.'_csrf'))
			{
				$this->request->csrf_token = $_CSRF_SESSION;
			}

	        if(strtoupper($this->request->method) !== 'POST' AND empty($this->request->csrf_token))
	        {
    			mt_srand();
	        	$this->request->csrf_token = md5(time() + mt_rand(0, 1999999999));
	        }
		}
	}

	/**
	* Fetch the IP Address
	*
	* @return	string
	*/
	public function ip_address()
	{
		if ($this->ip_address !== FALSE)
		{
			return $this->ip_address;
		}

		global $IN;
		$this->ip_address = $IN->ip_address();

		return $this->ip_address;
	}
}