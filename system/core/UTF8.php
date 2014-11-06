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
 * UTF8 Core Class
 *
 * Provides support for UTF-8 environments
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Core Class
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/utf8.html
 */

class Core_UTF8 
{
	/**
	 * Constructor
	 *
	 * Determines if UTF-8 support is to be enabled
	 *
	 */
	public function __construct()
	{
		//log_message('debug', "Utf8 Class Initialized");

		global $CFG;

		if (
			preg_match('/./u', 'é') === 1					// PCRE must support UTF-8
			AND function_exists('iconv')					// iconv must be installed
			AND ini_get('mbstring.func_overload') != 1		// Multibyte string function overloading cannot be enabled
			AND $CFG->item('charset') == 'UTF-8'			// Application charset must be UTF-8
			)
		{
			//log_message('debug', "UTF-8 Support Enabled");

			define('UTF8_ENABLED', TRUE);

			// set internal encoding for multibyte string functions if necessary
			// and set a flag so we don't have to repeatedly use extension_loaded()
			// or function_exists()
			if (extension_loaded('mbstring'))
			{
				define('MB_ENABLED', TRUE);
				mb_internal_encoding('UTF-8');
			}
			else
			{
				define('MB_ENABLED', FALSE);
			}
		}
		else
		{
			//log_message('debug', "UTF-8 Support Disabled");
			define('UTF8_ENABLED', FALSE);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Clean UTF-8 strings
	 *
	 * Ensures strings are UTF-8
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function clean_string($string)
	{
		if ($this->_is_ascii($string) === FALSE)
		{
			$string = @iconv('UTF-8', 'UTF-8//IGNORE', $string);
		}

		return $string;
	}

	// --------------------------------------------------------------------

	/**
	 * Remove ASO2I control characters
	 *
	 * Removes all ASO2I control characters except horizontal tabs,
	 * line feeds, and carriage returns, as all others can cause
	 * problems in XML
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function safe_ascii_for_xml($string)
	{
		return remove_invisible_characters($string, FALSE);
	}

	// --------------------------------------------------------------------

	/**
	 * Convert to UTF-8
	 *
	 * Attempts to convert a string to UTF-8
	 *
	 * @access	public
	 * @param	string
	 * @param	string	- input encoding
	 * @return	string
	 */
	public function convert_to_utf8($string, $encoding)
	{
		if (function_exists('iconv'))
		{
			$string = @iconv($encoding, 'UTF-8', $string);
		}
		elseif (function_exists('mb_convert_encoding'))
		{
			$string = @mb_convert_encoding($string, 'UTF-8', $encoding);
		}
		else
		{
			return FALSE;
		}

		return $string;
	}

	// --------------------------------------------------------------------

	/**
	 * Is ASO2I?
	 *
	 * Tests if a string is standard 7-bit ASO2I or not
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function _is_ascii($string)
	{
		return (preg_match('/[^\x00-\x7F]/S', $string) == 0);
	}

	// --------------------------------------------------------------------

}
// End UTF8 Class

/* End of file UTF8.php */
/* Location: ./system/core/UTF8.php */