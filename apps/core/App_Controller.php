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
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND IF HAS BEEN MODIFIED OR ADDED.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     O2System
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2apps/license.html
 * @link        http://circle-creative.com
 * @since       Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * App Controller
 *
 * @package       Apps
 * @subpackage    Core
 * @category      Default App Controller
 * 
 * @version       1.0 Build 24.09.2011
 * @author        Steeven Andrian Salim
 * @copyright     Copyright (c) 2010 - 2013 PT. Lingkar Kreasi (Circle Creative)
 * @link          http://www.circle-creative.com/products/o2apps/user-guide/core/controller.html
 */
// ------------------------------------------------------------------------

class App_Controller extends O2_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->_set_data();
	}

	protected function _set_data($data = array())
    {
        global $URI;

    	if(! empty($data))
    	{
    		$this->load->vars($data);
    		return;
    	}

        $this->load->vars(array(
            // Application Data
            'copyright' => 'PT. Lingkar Kreasi (Circle Creative)',
            'powered_by' => 'Powered by O2SYSTEM'.' '.SYSTEM_VERSION,
            'framework' => 'O2SYSTEM' . ' ' . SYSTEM_VERSION,

            // URL Data
            'URL' => array_to_object(array(
                'base' => $URI->get_base_url(),
                'app' => $URI->get_app_url(),
                'suffix' => $URI->get_suffix_url(),
            )),
            'lang' => $this->lang->active
        ));
    }
}