<?php
/**
 * O2Apps
 *
 * Application Engine under O2System Framework for PHP 5.2.4 or newer
 *
 * This content is released under PT. Lingkar Kreasi (Circle Creative) License
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative).
 *
 * Required
 * License Serial Number
 * This software cannot be used without any license serial number from PT. Lingkar Kreasi (Circle Creative) License
 * which is provided for single domain, multiple domain or white labeling (OEM).
 *
 * Permitted
 *  1. Private Use
 *     You may use and modify the software without distributing it. 
 *  2. Commercial Use
 *     This software and derivatives may be used for commercial purposes.
 *  3. Non-Commercial Use
 *     This software and derivatives may be used for non-commercial purposes.
 *  4. Distribution
 *     You may distribute this software as long you have license serial number from PT. Lingkar Kreasi (Circle Creative),
 *     which is provided for single domain, multiple domain or white labeling (OEM)
 *  5. Modification
 *     This software may be modified without warranty from from PT. Lingkar Kreasi (Circle Creative) and 
 *     PT. Lingkar Kreasi (Circle Creative) cannot be held liable for damages.
 *  6. Sublicensing
 *     You may grant a sublicense to modify and distribute this software to your clients / customers
 *     as long you have license serial number from PT. Lingkar Kreasi (Circle Creative),
 *     which is provided for single domain, multiple domain or white labeling (OEM).
 *  7. Use Trademark
 *     You may use the names, logos, or trademarks of O2Apps for promotion purposes.
 *     
 * Forbidden
 * Hold Liable
 * Software is provided without warranty of any kind if has been modified or added
 * and PT. Lingkar Kreasi (Circle Creative) cannot be held liable for damages.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND IF HAS BEEN MODIFIED OR ADDED.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package		O2System
 * @author		Steeven Andrian Salim
 * @copyright	Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license		http://circle-creative.com/products/o2apps/license.html
 * @link		http://circle-creative.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Images Controller
 *
 * @package       Apps
 * @subpackage    Controllers
 * @category      Default Controller
 * 
 * @version       1.0 Build 24.09.2014
 * @author        Steeven Andrian Salim
 * @copyright     Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative)
 * @link          http://www.circle-creative.com/products/o2apps/user-guide/controllers/images.html
 */
// ------------------------------------------------------------------------
class App_Images extends App_Controller 
{
	private $_request;

	public function __construct()
	{
		parent::__construct();

		$this->_request = new stdClass;
	}

	public function _remap()
	{
		$params = func_get_args();

		//print_out($params);
		
		// Define Image Request App Path
		if(in_array($params[0], $this->system->apps))
		{
			$this->_request->app = $params[0];
			array_shift($params);
		}
		else
		{
			$this->_request->app_path = $this->uri->active->app->name;
		}

		//print_out($params);

		// Define Image Method Request
		if(method_exists($this, '_'.$params[0]))
		{
			$this->_request->method = '_'.$params[0];
			array_shift($params);
		}

		// Define Image Size Request
		if(strpos($params[0], 'x') !== false)
		{
			$size = explode('x', $params[0]);
			$this->_request->size = array(
				'width' => $size[0],
				'height' => $size[1]
			);

			array_shift($params);
		}

		// Define Image Filename Request
		if(preg_match('/(\.gif|\.jpg|\.jpeg|\.png|\.bmp)$/', end($params)))
		{
			$this->_request->filename = $params[0];
			array_pop($params);
		}
		else
		{
			$this->_request->filename = 'no-image.jpg';
		}

		// Define Image Path Request
		if(!empty($params))
		{
			$this->_request->path =  implode('/', $params).'/';
			$this->_request->dir = APPSPATH.$this->_request->app.'/upload/images/'.$this->_request->path;
		}
		else
		{
			$this->_request->path = '';
			$this->_request->dir = APPSPATH.$this->_request->app.'/upload/images/';
		}

		$this->{$this->_request->method}();
	}

	/**
     * Alias for thumbnail request
     * @access private
     */ 
	protected function _thumb()
	{
		$this->_thumbnail();
	}

	protected function _thumbnail()
	{

	}

	protected function _large()
	{

	}

	protected function _avatar()
	{
		print_out('show_avatar');
	}
}