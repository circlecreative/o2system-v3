<?php
/**
 * O2CMS
 *
 * Website Content Management System under O2Apps and O2System Framework for PHP 5.2.4 or newer
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
 *  4. Modification
 *     This software may be modified without warranty from from PT. Lingkar Kreasi (Circle Creative) and 
 *     PT. Lingkar Kreasi (Circle Creative) cannot be held liable for damages.
 *    
 * Forbidden
 *  1. Distribution
 *     You may not distribute this software
 *  2. Sublicensing
 *     You may not grant a sublicense to modify and distribute this software to third parties not included.
 *  3. Use Trademark
 *     While this may be implicitly true of all licenses, this license explicitly states that you may NOT use 
 *     the names, logos, or trademarks of contributors.
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

class Site_Frontpage extends Site_Controller
{
	public function __construct()
	{
		parent::__construct();

		print_line_marker('Loader Testing');

		print_line_marker('Load Model Testing', 'START', 10);

		print_line_marker('Load System Model Testing: system/models/', 'START', 15);
		$this->load->model('system');
		print_line_marker('END');

		print_line_marker('Load Root App Model Testing: apps/models/', 'START', 15);
		$this->load->model('testing');
		print_line_marker('END');

		print_line_marker('Load Active App Model Testing: apps/site/models/', 'START', 15);
		$this->load->model('settings');
		print_line_marker('END');

		print_line_marker('Test Load Active App Current Module Model: apps/site/modules/frontpage/models/', 'START', 15);
		$this->load->model('frontpage');
		print_line_marker('END');

		print_line_marker('Test Load Active App Other Module Model: apps/site/modules/pages/models/', 'START', 15);
		$this->load->model('pages');
		print_line_marker('END');

		print_line_marker('Test Load Other App Model: apps/cms/models/', 'START', 15);
		$this->load->model('cms/settings');
		print_line_marker('END');

		print_line_marker('Test Load Other App Module Model: apps/cms/modules/dashboard/models/', 'START', 15);
		$this->load->model('cms/dashboard');
		print_line_marker('END');

		print_line_marker('Load Model Testing', 'END', 10);

		print_line_marker('Load Library Testing', 'START', 10);

		print_line_marker('Test Load Standalone App Libraries', 'START', 15);
		$this->load->library('app_standalone');
		print_line_marker('END');

		print_line_marker('Test Load Standalone Active Module Class', 'START', 15);
		$this->load->library('frontpage_standalone');
		print_line_marker('END');

		print_line_marker('Test Load Inheritance Class', 'START', 15);
		$this->load->library('inheritance_class');
		print_line_marker('END');

		print_line_marker('Test Load Other App Standalone Class: apps/cms/libraries/', 'START', 15);
		$this->load->library('cms/standalone_class');
		print_line_marker('END');

		print_line_marker('Test Load Other App Module Standalone Class: apps/cms/modules/dashboard/libraries/', 'START', 15);
		$this->load->library('cms/dashboard/dashboard_standalone');
		print_line_marker('END');

		print_line_marker('Test Load Other App Module Inheritance Class: apps/cms/modules/dashboard/libraries/', 'START', 15);
		$this->load->library('cms/dashboard/inheritance_class',array(),'cms_inheritance');
		print_line_marker('END');

		print_line_marker('Load Library Testing', 'START', 10);

		print_line_marker('Loader Testing', 'END');
		print_lines('', TRUE);
	}

	public function index()
	{
		$this->load->library('user_agent');
		
		$this->_set_title('Welcome to Site Frontpage');
		$this->load->template()->view('frontpage');
	}
}