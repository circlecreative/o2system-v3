<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2014, .
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Core\Request\Handlers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * User Agent Class
 *
 * Identifies the platform, browser, robot, or mobile device of the browsing agent
 *
 * @package        CodeIgniter
 * @subpackage     Libraries
 * @category       User Agent
 * @author         EllisLab Dev Team
 * @link           http://codeigniter.com/user_guide/libraries/user_agent.html
 */
class UserAgent
{

	/**
	 * Current user-agent
	 *
	 * @var string
	 */
	public $string = NULL;

	/**
	 * Flag for if the user-agent belongs to a browser
	 *
	 * @var bool
	 */
	public $is_browser = FALSE;

	/**
	 * Flag for if the user-agent is a robot
	 *
	 * @var bool
	 */
	public $is_robot = FALSE;

	/**
	 * Flag for if the user-agent is a mobile browser
	 *
	 * @var bool
	 */
	public $is_mobile = FALSE;

	/**
	 * Languages accepted by the current user agent
	 *
	 * @var array
	 */
	public $languages = [ ];

	/**
	 * Character sets accepted by the current user agent
	 *
	 * @var array
	 */
	public $charsets = [ ];

	/**
	 * List of platforms to compare against current user agent
	 *
	 * @var array
	 */
	public static $platforms = [ ];

	/**
	 * List of browsers to compare against current user agent
	 *
	 * @var array
	 */
	public static $browsers = [ ];

	/**
	 * List of mobile browsers to compare against current user agent
	 *
	 * @var array
	 */
	public static $mobiles = [ ];

	/**
	 * List of robots to compare against current user agent
	 *
	 * @var array
	 */
	public static $robots = [ ];

	/**
	 * Current user-agent platform
	 *
	 * @var string
	 */
	public $platform = '';

	/**
	 * Current user-agent browser
	 *
	 * @var string
	 */
	public $browser = '';

	/**
	 * Current user-agent version
	 *
	 * @var string
	 */
	public $version = '';

	/**
	 * Current user-agent mobile name
	 *
	 * @var string
	 */
	public $mobile = '';

	/**
	 * Current user-agent device type
	 *
	 * @type string
	 */
	public $device = '';

	/**
	 * Current user-agent robot name
	 *
	 * @var string
	 */
	public $robot = '';

	/**
	 * HTTP Referer
	 *
	 * @var    mixed
	 */
	public $referer;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * Sets the User Agent and runs the compilation routine
	 *
	 * @return    void
	 */
	public function __construct()
	{
		if ( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) )
		{
			$this->string = trim( $_SERVER[ 'HTTP_USER_AGENT' ] );
		}

		if ( $this->string !== NULL && $this->_loadAgentFile() )
		{
			$this->_compileData();
		}

		\O2System::$log->debug( 'LOG_DEBUG_REQUEST_USER_AGENT_CLASS_INITIALIZED' );
	}

	// --------------------------------------------------------------------

	/**
	 * Compile the User Agent Data
	 *
	 * @return    bool
	 */
	protected function _loadAgentFile()
	{
		if ( ( $found = is_file( SYSTEMPATH . 'config/user_agents.php' ) ) )
		{
			include( SYSTEMPATH . 'config/user_agents.php' );
		}

		if ( is_file( SYSTEMPATH . 'config/' . strtolower( ENVIRONMENT ) . '/user_agents.php' ) )
		{
			include( SYSTEMPATH . 'config/' . strtolower( ENVIRONMENT ) . '/user_agents.php' );
			$found = TRUE;
		}

		if ( $found !== TRUE )
		{
			return FALSE;
		}

		$return = FALSE;

		if ( isset( $platforms ) )
		{
			static::$platforms = $platforms;
			unset( $platforms );
			$return = TRUE;
		}

		if ( isset( $browsers ) )
		{
			static::$browsers = $browsers;
			unset( $browsers );
			$return = TRUE;
		}

		if ( isset( $mobiles ) )
		{
			static::$mobiles = $mobiles;
			unset( $mobiles );
			$return = TRUE;
		}

		if ( isset( $robots ) )
		{
			static::$robots = $robots;
			unset( $robots );
			$return = TRUE;
		}

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Compile the User Agent Data
	 *
	 * @return    bool
	 */
	protected function _compileData()
	{
		$this->_setPlatform();

		foreach ( [ '_setRobot', '_setBrowser', '_setMobile' ] as $function )
		{
			if ( $this->$function() === TRUE )
			{
				break;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Platform
	 *
	 * @return    bool
	 */
	protected function _setPlatform()
	{
		if ( is_array( static::$platforms ) && count( static::$platforms ) > 0 )
		{
			foreach ( static::$platforms as $key => $val )
			{
				if ( preg_match( '|' . preg_quote( $key ) . '|i', $this->string ) )
				{
					$this->platform = $val;

					return TRUE;
				}
			}
		}

		$this->platform = 'Unknown Platform';

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Browser
	 *
	 * @return    bool
	 */
	protected function _setBrowser()
	{
		if ( is_array( static::$browsers ) && count( static::$browsers ) > 0 )
		{
			foreach ( static::$browsers as $key => $val )
			{
				if ( preg_match( '|' . $key . '.*?([0-9\.]+)|i', $this->string, $match ) )
				{
					$this->is_browser = TRUE;
					$this->version    = $match[ 1 ];
					$this->browser    = $val;
					$this->_setMobile();

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Robot
	 *
	 * @return    bool
	 */
	protected function _setRobot()
	{
		if ( is_array( static::$robots ) && count( static::$robots ) > 0 )
		{
			foreach ( static::$robots as $key => $val )
			{
				if ( preg_match( '|' . preg_quote( $key ) . '|i', $this->string ) )
				{
					$this->is_robot = TRUE;
					$this->robot    = $val;
					$this->_setMobile();

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mobile Device
	 *
	 * @return    bool
	 */
	protected function _setMobile()
	{
		if ( is_array( static::$mobiles ) && count( static::$mobiles ) > 0 )
		{
			foreach ( static::$mobiles as $key => $val )
			{
				if ( FALSE !== ( stripos( $this->string, $key ) ) )
				{
					$this->is_mobile = TRUE;
					$this->mobile    = $val;

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the accepted languages
	 *
	 * @return    void
	 */
	protected function _setLanguages()
	{
		if ( ( count( $this->languages ) === 0 ) && ! empty( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) )
		{
			$this->languages = explode( ',', preg_replace( '/(;\s?q=[0-9\.]+)|\s/i', '', strtolower( trim( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) ) ) );
		}

		if ( count( $this->languages ) === 0 )
		{
			$this->languages = [ 'Undefined' ];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set the accepted character sets
	 *
	 * @return    void
	 */
	protected function _setCharsets()
	{
		if ( ( count( $this->charsets ) === 0 ) && ! empty( $_SERVER[ 'HTTP_ACCEPT_CHARSET' ] ) )
		{
			$this->charsets = explode( ',', preg_replace( '/(;\s?q=.+)|\s/i', '', strtolower( trim( $_SERVER[ 'HTTP_ACCEPT_CHARSET' ] ) ) ) );
		}

		if ( count( $this->charsets ) === 0 )
		{
			$this->charsets = [ 'Undefined' ];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Is Browser
	 *
	 * @param    string $key
	 *
	 * @return    bool
	 */
	public function isBrowser( $key = NULL )
	{
		if ( ! $this->is_browser )
		{
			return FALSE;
		}

		// No need to be specific, it's a browser
		if ( $key === NULL )
		{
			return TRUE;
		}

		// Check for a specific browser
		return ( isset( static::$browsers[ $key ] ) && $this->browser === static::$browsers[ $key ] );
	}

	// --------------------------------------------------------------------

	/**
	 * Is Robot
	 *
	 * @param    string $key
	 *
	 * @return    bool
	 */
	public function isRobot( $key = NULL )
	{
		if ( ! $this->is_robot )
		{
			return FALSE;
		}

		// No need to be specific, it's a robot
		if ( $key === NULL )
		{
			return TRUE;
		}

		// Check for a specific robot
		return ( isset( static::$robots[ $key ] ) && $this->robot === static::$robots[ $key ] );
	}

	// --------------------------------------------------------------------

	/**
	 * Is Mobile
	 *
	 * @param    string $key
	 *
	 * @return    bool
	 */
	public function isMobile( $key = NULL )
	{
		if ( ! $this->is_mobile )
		{
			return FALSE;
		}

		// No need to be specific, it's a mobile
		if ( $key === NULL )
		{
			return TRUE;
		}

		// Check for a specific robot
		return ( isset( static::$mobiles[ $key ] ) && $this->mobile === static::$mobiles[ $key ] );
	}

	// --------------------------------------------------------------------

	/**
	 * Is this a referral from another site?
	 *
	 * @return    bool
	 */
	public function isReferral()
	{
		if ( ! isset( $this->referer ) )
		{
			if ( empty( $_SERVER[ 'HTTP_REFERER' ] ) )
			{
				$this->referer = FALSE;
			}
			else
			{
				$referer_host = @parse_url( $_SERVER[ 'HTTP_REFERER' ], PHP_URL_HOST );
				$own_host     = parse_url( \O2System::$config->base_url(), PHP_URL_HOST );

				$this->referer = ( $referer_host && $referer_host !== $own_host );
			}
		}

		return $this->referer;
	}

	// --------------------------------------------------------------------

	/**
	 * Agent String
	 *
	 * @return    string
	 */
	public function agentString()
	{
		return $this->string;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Platform
	 *
	 * @return    string
	 */
	public function platform()
	{
		return $this->platform;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Browser Name
	 *
	 * @return    string
	 */
	public function browser()
	{
		return $this->browser;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the Browser Version
	 *
	 * @return    string
	 */
	public function version()
	{
		return $this->version;
	}

	// --------------------------------------------------------------------

	/**
	 * Get The Robot Name
	 *
	 * @return    string
	 */
	public function robot()
	{
		return $this->robot;
	}
	// --------------------------------------------------------------------

	/**
	 * Get the Mobile Device
	 *
	 * @return    string
	 */
	public function mobile()
	{
		return $this->mobile;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the referrer
	 *
	 * @return    bool
	 */
	public function referrer()
	{
		return empty( $_SERVER[ 'HTTP_REFERER' ] ) ? '' : trim( $_SERVER[ 'HTTP_REFERER' ] );
	}

	// --------------------------------------------------------------------

	/**
	 * Get the accepted languages
	 *
	 * @return    array
	 */
	public function languages()
	{
		if ( count( $this->languages ) === 0 )
		{
			$this->_setLanguages();
		}

		return $this->languages;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the accepted Character Sets
	 *
	 * @return    array
	 */
	public function charsets()
	{
		if ( count( $this->charsets ) === 0 )
		{
			$this->_setCharsets();
		}

		return $this->charsets;
	}

	// --------------------------------------------------------------------

	/**
	 * Test for a particular language
	 *
	 * @param    string $lang
	 *
	 * @return    bool
	 */
	public function acceptLang( $lang = 'en' )
	{
		return in_array( strtolower( $lang ), $this->languages(), TRUE );
	}

	// --------------------------------------------------------------------

	/**
	 * Test for a particular character set
	 *
	 * @param    string $charset
	 *
	 * @return    bool
	 */
	public function acceptCharset( $charset = 'utf-8' )
	{
		return in_array( strtolower( $charset ), $this->charsets(), TRUE );
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a custom user-agent string
	 *
	 * @param    string $string
	 *
	 * @return    void
	 */
	public function parse( $string )
	{
		// Reset values
		$this->is_browser = FALSE;
		$this->is_robot   = FALSE;
		$this->is_mobile  = FALSE;
		$this->browser    = '';
		$this->version    = '';
		$this->mobile     = '';
		$this->robot      = '';

		// Set the new user-agent string and parse it, unless empty
		$this->string = $string;

		if ( ! empty( $string ) )
		{
			$this->_compileData();
		}
	}

}
