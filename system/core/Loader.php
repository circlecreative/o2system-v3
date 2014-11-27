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
 * @package     O2System
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

defined('SYSPATH') OR exit('No direct script access allowed');

/**
 * System Loader
 *
 * Loads framework components. Base on CodeIgniter 3.0-dev loader concept by EllisLab, Inc
 * Some functions at this class is still borrowed from the original code, with some re-coding
 * and renaming variables or constant
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Loader
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide/core/loader.html
 */

class O2_Loader
{
    // All these are set automatically. Don't mess with them.
    /**
     * Nesting level of the output buffering mechanism
     *
     * @var int
     * @access protected
     */
    protected $_ob_level;

    /**
     * List of paths to load views from
     *
     * @var array
     * @access protected
     */
    protected $_view_paths       = array();

    /**
     * List of paths to load libraries from
     *
     * @var array
     * @access protected
     */
    protected $_library_paths    = array();

    /**
     * List of paths to load driver from
     *
     * @var array
     * @access protected
     */
    protected $_drivers_paths    = array();

    /**
     * List of paths to load models from
     *
     * @var array
     * @access protected
     */
    protected $_model_paths      = array();

    /**
     * List of paths to load helpers from
     *
     * @var array
     * @access protected
     */
    protected $_helper_paths     = array();

    /**
     * List of loaded base classes
     * Set by the controller class
     *
     * @var array
     * @access protected
     */
    protected $_base_classes        = array(); // Set by the controller class

    /**
     * List of cached variables
     *
     * @var array
     * @access protected
     */
    protected $_cached_vars      = array();

    /**
     * List of loaded classes
     *
     * @var array
     * @access protected
     */
    protected $_classes          = array();

    /**
     * List of loaded drivers
     *
     * @var array
     * @access protected
     */
    protected $_drivers          = array();

    /**
     * List of loaded files
     *
     * @var array
     * @access protected
     */
    protected $_loaded_files     = array();

    /**
     * List of loaded models
     *
     * @var array
     * @access protected
     */
    protected $_models           = array();

    /**
     * List of loaded helpers
     *
     * @var array
     * @access protected
     */
    protected $_helpers          = array();

    /**
     * List of class name mappings
     *
     * @var array
     * @access protected
     */
    protected $_varmap = array(
        'unit_test' => 'unit',
        'user_agent' => 'agent'
    );

    protected $_autoload = array(
        'helper' => array('array', 'object', 'url'),
        'driver' => array('template')
    );

    protected $_use_template = FALSE;

    /**
     * Constructor
     *
     * Sets the path to the view files and gets the initial output buffering level
     */
    public function __construct()
    {
        $this->_ob_level  = ob_get_level();
        $this->_classes =& is_loaded();

        // Set Paths Registry
        $this->_set_paths_registry();

        // Load Template Driver
        $this->driver('template');

        log_message('debug', "Loader Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Set Paths Registry
     *
     * @access   private
     * @return   void
     */
    private function _set_paths_registry()
    {
        global $O2SYSTEM;
        global $URI;

        // Set Default Paths
        $this->_library_paths = array(SYSPATH.'libraries/', APPSPATH.'libraries/');
        $this->_helper_paths = array(SYSPATH.'helpers/', APPSPATH.'helpers/');
        $this->_model_paths = array(SYSPATH.'models/', APPSPATH.'models/');
        $this->_view_paths = array(APPSPATH.'views/');


        if(! empty($O2SYSTEM->apps))
        {
            // Register Active App Path
            array_push($this->_library_paths, $URI->request->app->path.'libraries/');
            array_push($this->_helper_paths, $URI->request->app->path.'helpers/');
            array_push($this->_model_paths, $URI->request->app->path.'models/');
            array_push($this->_view_paths, $URI->request->app->path.'views/');

            // Register Active Module Path
            if(isset($URI->request->module))
            {
                array_push($this->_library_paths, $URI->request->module->path.'libraries/');
                array_push($this->_helper_paths, $URI->request->module->path.'helpers/');
                array_push($this->_model_paths, $URI->request->module->path.'models/');
                array_push($this->_view_paths, $URI->request->module->path.'views/');  
            }          

            // Register Active Theme Views Path
            array_push($this->_view_paths, $URI->request->app->path.'templates/{template_name}/');

            if(isset($URI->request->module))
            {
                // Register Active Theme Override Views Path
                array_push($this->_view_paths, $URI->request->app->path.'templates/{template_name}/views/'.$URI->request->module->name.'/');
            }
        }
    }

    /**
     * Initializer
     *
     * @todo    Figure out a way to move this to the constructor
     *          without breaking *package_path*() methods.
     * @uses    O2_Loader::_autoloader()
     * @used-by O2_Controller::__construct()
     * @return  void
     */
    public function initialize()
    {
        $this->_autoloader();

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Is Loaded
     *
     * A utility method to test if a class is in the self::$_classes array.
     *
     * @used-by Mainly used by Form Helper function _get_validation_object().
     *
     * @param   string      $class  Class name to check for
     * @return  string|bool Class object name if loaded or FALSE
     */
    public function is_loaded($class)
    {
        return array_search(strtolower($class), $this->_classes, TRUE);
    }

    // --------------------------------------------------------------------

    /**
     * Library Loader
     *
     * Loads and instantiates libraries.
     * Designed to be called from application controllers.
     *
     * @param   string  $library        Library name
     * @param   array   $params         Optional parameters to pass to the library class constructor
     * @param   string  $object_name    An optional object name to assign to
     * @return  object
     */
    public function library($library = '', $params = NULL, $object_name = NULL)
    {
        if (is_array($library))
        {
            foreach ($library as $class)
            {
                $this->library($class, $params);
            }

            return;
        }

        if ($library == '' OR isset($this->_base_classes[$library]))
        {
            return FALSE;
        }

        if ( ! is_null($params) && ! is_array($params))
        {
            $params = NULL;
        }

        $this->_load_class($library, $params, $object_name);
    }

    // --------------------------------------------------------------------

    /**
     * Model Loader
     *
     * Loads and instantiates models.
     *
     * @param   string  $model      Model name
     * @param   string  $name       An optional object name to assign to
     * @param   bool    $db_conn    An optional database connection configuration to initialize
     * @return  object
     */
    public function model($model, $object_name = '', $db_conn = FALSE)
    {
        if (empty($model))
        {
            return $this;
        }
        elseif (is_array($model))
        {
            foreach ($model as $key => $value)
            {
                is_int($key) ? $this->model($value, '', $db_conn) : $this->model($key, $value, $db_conn);
            }

            return $this;
        }

        global $O2SYSTEM, $URI;

        // Load Core O2System Model
        if ( ! class_exists('O2_Model'))
        {
            load_class('Model', 'core');
        }

        // O2System Instance
        $O2 =& get_instance();

        // Is App or Module Model Request?
        if (strrpos($model, '/') !== FALSE)
        {
            $x_models = preg_split('[/]', $model, -1, PREG_SPLIT_NO_EMPTY);

            // Check App Model Request
            $app_request = FALSE;
            if(in_array($x_models[0], $O2SYSTEM->apps))
            {
                $app_request = $x_models[0];

                // Load App Core Model
                $app_class_model = prepare_class_name($app_request.'_Model');

                if(! class_exists( $app_class_model ))
                {
                    require_once(APPSPATH.$app_request.'/'.'core/'.$app_class_model.__EXT__);
                }

                array_shift($x_models);
            }

            if($app_request !== FALSE)
            {
                $app_models = $O2SYSTEM->{$app_request}->models;

                if(in_array($x_models[0].'_model', array_keys($app_models->files)))
                {
                    $class_name =  prepare_class_name($app_request.'_'.$x_models[0].'_Model');
                    $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);

                    require_once(APPSPATH.$app_request.'/models/'.$x_models[0].'_model'.__EXT__);

                    $O2->{ $object_name } = new $class_name();
                    $this->_models[] = $object_name;

                    return;
                }

                $app_modules = get_object_vars($O2SYSTEM->{$app_request}->modules);

                if(in_array($x_models[0], array_keys($app_modules)))
                {
                    $module_request = $x_models[0];
                    array_shift($x_models);

                    if(empty($x_models))
                    {
                        $model_request = $module_request.'_model';
                    }

                    $class_name =  prepare_class_name($app_request.'_'.$model_request);
                    $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);
                    $filename = strtolower($model_request);

                    require_once(APPSPATH.$app_request.'/modules/'.$module_request.'/models/'.$filename.__EXT__);

                    $O2->{ $object_name } = new $class_name();
                    $this->_models[] = $object_name;

                    return;
                }
            }
            // Active App Module Model Request
            elseif($app_request === FALSE)
            {
                $app_modules = get_object_vars($O2SYSTEM->{$URI->request->app->parameter}->modules);

                if(in_array($x_models[0], array_keys($app_modules)))
                {
                    // Load App Core Model
                    $app_class_model = prepare_class_name($URI->request->app->parameter.'_Model');

                    if(! class_exists( $app_class_model ))
                    {
                        require_once(APPSPATH.$URI->request->app->parameter.'/'.'core/'.$app_class_model.__EXT__);
                    }

                    $add_paths = array($URI->request->app->parameter, 'modules', $x_models[0], 'models');
                    array_shift($x_models);

                    if(empty($x_models))
                    {
                        $model = end($add_paths);
                    }

                    $class_name =  prepare_class_name($URI->request->app->parameter.'_'.$model.'_Model');
                    $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);

                    require_once(APPSPATH.$URI->request->app->parameter.'/'.'modules/'.$URI->request->module->name.'/models/'.$model.'_model'.__EXT__);

                    $O2->{ $object_name } = new $class_name();
                    $this->_models[] = $object_name;

                    return;
                }
                else
                {
                    $app_models = $O2SYSTEM->{$URI->request->app->parameter}->models;

                    if(in_array($x_models[0].'_model', array_keys($app_models->files)))
                    {
                        $model = $x_models[0].'_model';
                    }
                }

                // Active App Module Request
                $app_modules = get_object_vars($O2SYSTEM->{$URI->request->app->parameter}->modules);

                if($model == $URI->request->module->name)
                {
                    $class_name =  prepare_class_name($URI->request->app->parameter.'_'.$model.'_Model');
                    $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);

                    require_once(APPSPATH.$URI->request->app->parameter.'/'.'modules/'.$URI->request->module->name.'/models/'.$model.'_model'.__EXT__);

                    $O2->{ $object_name } = new $class_name();
                    $this->_models[] = $object_name;

                    return;
                }
            }
        }
        else
        {
            // System Model Request
            $system_models = $O2SYSTEM->system->models;
            if(in_array($model.'_model', array_keys($system_models->files)))
            {
                $class_name =  prepare_class_name($model.'_Model');
                $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);

                require_once(SYSPATH.'models/'.$model.'_model'.__EXT__);

                $O2->{ $object_name } = new $class_name();
                $this->_models[] = $object_name;

                return;
            }

            // Root App Model Request
            $app_models = $O2SYSTEM->app->models;
            if(in_array($model.'_model', array_keys($app_models->files)))
            {
                $class_name =  prepare_class_name($model.'_Model');
                $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);

                require_once(APPSPATH.'models/'.$model.'_model'.__EXT__);

                $O2->{ $object_name } = new $class_name();
                $this->_models[] = $object_name;

                return;
            }

            // Active App Module Request
            $app_modules = get_object_vars($O2SYSTEM->{$URI->request->app->parameter}->modules);

            if($model == $URI->request->module->name)
            {
                // Load App Core Model
                $app_class_model = prepare_class_name($URI->request->app->parameter.'_Model');
                if(! class_exists( $app_class_model ))
                {
                    require_once(APPSPATH.$URI->request->app->parameter.'/'.'core/'.$app_class_model.__EXT__);
                }

                $class_name =  prepare_class_name($URI->request->app->parameter.'_'.$model.'_Model');
                $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);

                require_once(APPSPATH.$URI->request->app->parameter.'/'.'modules/'.$URI->request->module->name.'/models/'.$model.'_model'.__EXT__);

                $O2->{ $object_name } = new $class_name();
                $this->_models[] = $object_name;

                return;
            }
            elseif(in_array($model, array_keys($app_modules)))
            {
                // Load App Core Model
                $app_class_model = prepare_class_name($URI->request->app->parameter.'_Model');

                if(! class_exists( $app_class_model ))
                {
                    require_once(APPSPATH.$URI->request->app->parameter.'/'.'core/'.$app_class_model.__EXT__);
                }

                $class_name =  prepare_class_name($URI->request->app->parameter.'_'.$model.'_Model');
                $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);

                require_once(APPSPATH.$URI->request->app->parameter.'/'.'modules/'.$model.'/models/'.$model.'_model'.__EXT__);

                $O2->{ $object_name } = new $class_name();
                $this->_models[] = $object_name;

                return;
            }
            else
            {
                // Active App Model Request
                $app_models = $O2SYSTEM->{ $URI->request->app->parameter }->models;
                if(in_array($model.'_model', array_keys($app_models->files)))
                {
                    // Load Active App Core Model
                    $app_class_model = prepare_class_name($URI->request->app->parameter.'_Model');

                    if(! class_exists( $app_class_model ))
                    {
                        require_once(APPSPATH.$URI->request->app->parameter.'/'.'core/'.$app_class_model.__EXT__);
                    }

                    $class_name =  prepare_class_name($URI->request->app->parameter.'_'.$model.'_Model');
                    $object_name = ($object_name == '' ? strtolower($class_name) : $object_name);

                    require_once(APPSPATH.$URI->request->app->parameter.'/models/'.$model.'_model'.__EXT__);

                    $O2->{ $object_name } = new $class_name();
                    $this->_models[] = $object_name;

                    return;
                }
            }
        }

        // couldn't find the model
        show_error('Unable to locate the model you have specified: '.$model);
    }

    // --------------------------------------------------------------------

    /**
     * Database Loader
     *
     * Borrowed from CodeIgniter 3.0-dev
     *
     * @author      EllisLab Dev Team
     * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
     * @copyright   Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @link        http://codeigniter.com
     * @since       Version 2.0.0
     *
     * @param   mixed   $params     Database configuration options
     * @param   bool    $return     Whether to return the database object
     * @param   bool    $query_builder  Whether to enable Query Builder
     *                  (overrides the configuration setting)
     *
     * @return  object|bool Database object if $return is set to TRUE,
     *                  FALSE on failure, CI_Loader instance in any other case
     */
    public function database($params = '', $return = FALSE, $query_builder = NULL)
    {
        // Grab the super object
        $O2 =& get_instance();

        // Do we even need to load the database class?
        if ($return === FALSE && $query_builder === NULL && isset($O2->db) && is_object($O2->db) && ! empty($O2->db->conn_id))
        {
            return FALSE;
        }

        require_once(SYSPATH.'database/DB.php');

        if ($return === TRUE)
        {
            return DB($params, $query_builder);
        }

        // Initialize the db variable. Needed to prevent
        // reference errors with some configurations
        $O2->db = '';

        // Load the DB class
        $O2->db =& DB($params, $query_builder);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Load the Database Utilities Class
     *
     * Borrowed from CodeIgniter 3.0-dev
     *
     * @author      EllisLab Dev Team
     * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
     * @copyright   Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @link        http://codeigniter.com
     * @since       Version 2.0.0
     *
     * @param   object  $db Database object
     * @param   bool    $return Whether to return the DB Utilities class object or not
     * @return  object
     */
    public function dbutil($db = NULL, $return = FALSE)
    {
        $O2 =& get_instance();

        if ( ! is_object($db) OR ! ($db instanceof CI_DB))
        {
            class_exists('CI_DB', FALSE) OR $this->database();
            $db =& $O2->db;
        }

        require_once(SYSPATH.'database/DB_utility.php');
        require_once(SYSPATH.'database/drivers/'.$db->dbdriver.'/'.$db->dbdriver.'_utility.php');
        $class = 'CI_DB_'.$db->dbdriver.'_utility';

        if ($return === TRUE)
        {
            return new $class($db);
        }

        $O2->dbutil = new $class($db);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Load the Database Forge Class
     *
     * Borrowed from CodeIgniter 3.0-dev
     *
     * @author      EllisLab Dev Team
     * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
     * @copyright   Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @link        http://codeigniter.com
     * @since       Version 2.0.0
     *
     * @param   object  $db Database object
     * @param   bool    $return Whether to return the DB Forge class object or not
     * @return  object
     */
    public function dbforge($db = NULL, $return = FALSE)
    {
        $O2 =& get_instance();
        if ( ! is_object($db) OR ! ($db instanceof CI_DB))
        {
            class_exists('CI_DB', FALSE) OR $this->database();
            $db =& $O2->db;
        }

        require_once(SYSPATH.'database/DB_forge.php');
        require_once(SYSPATH.'database/drivers/'.$db->dbdriver.'/'.$db->dbdriver.'_forge.php');

        if ( ! empty($db->subdriver))
        {
            $driver_path = SYSPATH.'database/drivers/'.$db->dbdriver.'/subdrivers/'.$db->dbdriver.'_'.$db->subdriver.'_forge.php';
            if (file_exists($driver_path))
            {
                require_once($driver_path);
                $class = 'CI_DB_'.$db->dbdriver.'_'.$db->subdriver.'_forge';
            }
        }
        else
        {
            $class = 'CI_DB_'.$db->dbdriver.'_forge';
        }

        if ($return === TRUE)
        {
            return new $class($db);
        }

        $O2->dbforge = new $class($db);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * View Loader
     *
     * Loads "view" files.
     *
     * @param   string  $view   View name
     * @param   array   $vars   An associative array of data
     *              to be extracted for use in the view
     * @param   bool    $return Whether to return the view output
     *              or leave it to the Output class
     * @return  object|string
     */
    public function view($view, $vars = array(), $return = FALSE)
    {
        return $this->_load(array('_view' => $view, '_vars' => $this->_object_to_array($vars), '_return' => $return));
    }

    // --------------------------------------------------------------------

    /**
     * Generic File Loader
     *
     * @param   string  $path   File path
     * @param   bool    $return Whether to return the file output
     * @return  object|string
     */
    public function file($path, $return = FALSE)
    {
        return $this->_load(array('_path' => $path, '_return' => $return));
    }

    // --------------------------------------------------------------------

    /**
     * Set Variables
     *
     * Once variables are set they become available within
     * the controller class and its "view" files.
     *
     * @param   array|object|string $vars
     *                  An associative array or object containing values
     *                  to be set, or a value's name if string
     * @param   string  $val    Value to set, only used if $vars is a string
     * @return  object
     */
    public function vars($vars = array(), $val = '')
    {
        if ($val != '' AND is_string($vars))
        {
            $vars = array($vars => $val);
        }

        $vars = $this->_object_to_array($vars);

        if (is_array($vars) AND count($vars) > 0)
        {
            foreach ($vars as $key => $val)
            {
                $this->_cached_vars[$key] = $val;
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Get Variables
     *
     * Retrieves all loaded variables.
     *
     * @return  array
     */
    public function get_vars()
    {
        return $this->_cached_vars;
    }

    // --------------------------------------------------------------------

    /**
     * Get Variable
     *
     * Check if a variable is set and retrieve it.
     *
     * @param   array
     * @return  void
     */
    public function get_var($key)
    {
        return isset($this->_cached_vars[$key]) ? $this->_cached_vars[$key] : NULL;
    }

    // --------------------------------------------------------------------    

    /**
     * Clear Cached Variables
     *
     * Clears the cached variables.
     *
     * @return  object
     */
    public function clear_vars()
    {
        $this->_cached_vars = array();
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Helper Loader
     *
     * @param   string|string[] $helpers    Helper name(s)
     * @return  object
     */
    public function helper($helpers = array())
    {
        foreach ($this->_prep_filename($helpers, '_helper') as $helper)
        {
            if (isset($this->_helpers[$helper]))
            {
                continue;
            }

            // Try to load the helper
            foreach ($this->_helper_paths as $path)
            {
                if (file_exists($path.$helper.__EXT__))
                {
                    include_once($path.$helper.__EXT__);

                    $this->_helpers[$helper] = TRUE;
                    log_message('debug', 'Helper loaded: '.$helper);
                    break;
                }
            }

            // unable to load the helper
            if ( ! isset($this->_helpers[$helper]))
            {
                show_error('Unable to load the requested file: helpers/'.$helper.__EXT__);
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Helpers Loader
     *
     * An alias for the helper() method in case the developer has
     * written the plural form of it.
     *
     * @uses    CI_Loader::helper()
     * @param   string|string[] $helpers    Helper name(s)
     * @return  object
     */
    public function helpers($helpers = array())
    {
        return $this->helper($helpers);
    }

    // --------------------------------------------------------------------

    /**
     * Language Loader
     *
     * Loads language files.
     *
     * @param   string|string[] $files  List of language file names to load
     * @param   string      Language name
     * @return  object
     */
    public function lang($files, $lang = '')
    {
        get_instance()->lang->load($files, $lang);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Config Loader
     *
     * Loads a config file (an alias for CI_Config::load()).
     *
     * @uses    CI_Config::load()
     * @param   string  $file               Configuration file name
     * @param   bool    $use_sections       Whether configuration values should be loaded into their own section
     * @param   bool    $fail_gracefully    Whether to just return FALSE or display an error message
     * @return  bool    TRUE if the file was loaded correctly or FALSE on failure
     */
    public function config($file, $use_sections = FALSE, $fail_gracefully = FALSE)
    {
        return get_instance()->config->load($file, $use_sections, $fail_gracefully);
    }

    // --------------------------------------------------------------------

    /**
     * Driver Loader
     *
     * Loads a driver library.
     *
     * @param   string|string[] $library    Driver name(s)
     * @param   array       $params         Optional parameters to pass to the driver
     * @param   string      $object_name    An optional object name to assign to
     *
     * @return  object|bool Object or FALSE on failure if $library is a string
     *              and $object_name is set. CI_Loader instance otherwise.
     */
    public function driver($library = '', $params = NULL, $object_name = NULL)
    {
        $library = strtolower($library);

        if ( ! class_exists('O2_Driver_Library'))
        {
            // we aren't instantiating an object here, that'll be done by the Library itself
            require SYSPATH.'libraries/Driver.php';
        }

        if ($library == '')
        {
            return FALSE;
        }

        $pack_name = prepare_class_name($library);

        // Class Names
        $class_names = array(
            'CI_'.$pack_name,
            'O2_'.$pack_name,
            'App_'.$pack_name,
        );

        // Check if Driver pack is exists
        foreach($this->_library_paths as $path)
        {
            if(is_dir($path.$pack_name) AND is_file($path.$pack_name.'/'.$pack_name.__EXT__))
            {
                include_once($path.$pack_name.'/'.$pack_name.__EXT__);

                foreach($class_names as $class_name)
                {
                    if(class_exists($class_name))
                    {
                        if(in_array($library, $this->_loaded_files))
                        {
                            $O2 =& get_instance();
                            if(! isset($O2->$library))
                            {
                                return $this->_init_class($library, $class_name, $params, $object_name);
                            }
                        }
                        else
                        {
                            $this->_loaded_files[] = $library;
                            return $this->_init_class($library, $class_name, $params, $object_name);
                        }
                    }
                }
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Add Package Path
     *
     * Prepends a parent path to the library, model, helper, and config path arrays
     *
     * @param   string
     * @param   boolean
     * @return  void
     */
    public function add_package_path($path, $view_cascade=TRUE)
    {
        $path = rtrim($path, '/').'/';

        array_unshift($this->_library_paths, $path);
        array_unshift($this->_model_paths, $path);
        array_unshift($this->_helper_paths, $path);

        $this->_view_paths = array($path.'views/' => $view_cascade) + $this->_view_paths;

        // Add config file path
        $config =& $this->_get_component('config');
        array_unshift($config->_config_paths, $path);
    }

    // --------------------------------------------------------------------

    /**
     * Get Drivers Paths
     *
     * Return a list of all package paths, by default it will ignore SYSPATH.
     *
     * @param   string
     * @return  void
     */
    public function get_drivers_paths($include_base = FALSE)
    {
        global $O2SYSTEM, $URI;

        $drivers_paths = new O2_System_Registry;

        // Register System Driver Paths
        foreach($O2SYSTEM->system->libraries as $driver_name => $driver_registry)
        {
            if(! in_array($driver_name, array('path', 'files', 'Smarty', 'javascript')))
            {
                $drivers_paths->{$driver_name} = $driver_registry;
            }
        }

        // Register Root App Driver Paths
        if(!empty($O2SYSTEM->app->libraries))
        {
            foreach($O2SYSTEM->app->libraries as $driver_name => $driver_registry)
            {
                if(! in_array($driver_name, array('path', 'files', 'Smarty', 'javascript')))
                {
                    $drivers_paths->{$driver_name} = $driver_registry;
                }
            }
        }

        // Register Active App Driver Paths
        if(!empty($O2SYSTEM->{ $URI->request->app->parameter }->libraries))
        {
            foreach($O2SYSTEM->{ $URI->request->app->parameter }->libraries as $driver_name => $driver_registry)
            {
                if(! in_array($driver_name, array('path', 'files', 'Smarty', 'javascript')))
                {
                    $drivers_paths->{$driver_name} = $driver_registry;
                }
            }
        }

        return $drivers_paths;
    }

    // --------------------------------------------------------------------

    /**
     * Internal O2System Data Loader
     *
     * Used to load views and files.
     *
     * Variables are prefixed with _ (UNDERSCORE) to avoid symbol collision with
     * variables made available to view files.
     *
     * @used-by O2_Loader::view()
     * @used-by O2_Loader::file()
     * @param   array   $_ci_data   Data to load
     * @return  object
     */
    protected function _load($_data)
    {
        global $URI;

        $O2 =& get_instance();
        //print_out($_data);
        // Set the default data variables
        foreach (array('_view', '_vars', '_path', '_return') as $_val)
        {
            $$_val = ( ! isset($_data[$_val])) ? FALSE : $_data[$_val];
        }

        $file_exists = FALSE;

        // Set the path to the requested file
        if ($_path != '')
        {
            $_x = explode('/', $_path);
            $_file = end($_x);
        }
        else
        {
            $_ext = pathinfo($_view, PATHINFO_EXTENSION);
            $_file = ($_ext == '') ? $_view.'.tpl' : $_view;

            foreach ($this->_view_paths as $view_file)
            {
                if($this->_use_template === TRUE)
                {
                    $view_file = str_replace('{template_name}', $O2->template->active, $view_file);
                }

                if (file_exists($view_file.$_file))
                {
                    $_path = $view_file;
                    $file_exists = TRUE;
                    break;
                }
            }
        }

        if ( ! $file_exists && ! file_exists($_path))
        {
            log_message('error','Unable to load the requested view file: '.$view_file);
        }

        /*
         * Extract and cache variables
         *
         * You can either set variables using the dedicated $this->load_vars()
         * function or via the second parameter of this function. We'll merge
         * the two types and cache them so that views that are embedded within
         * other views can have access to these variables.
         */
        if (is_array($_vars))
        {
            $this->_cached_vars = array_merge($this->_cached_vars, $_vars);
        }
        
        require_once SYSPATH . 'libraries/Smarty/Smarty.class.php';

        $smarty = new Smarty();
        $smarty->setCompileDir(APPSPATH . 'cache/smarty/');
        $smarty->setCacheDir(APPSPATH . 'cache/smarty/');
        $smarty->setTemplateDir($_path);
        $smarty->caching = 0;

        if(!empty($this->_cached_vars))
        {
            foreach($this->_cached_vars as $_assign_key => $_assign_value)
            {
                $smarty->assign($_assign_key, $_assign_value);
            }
        }

        // This allows anything loaded using $this->load (views, files, etc.)
        // to become accessible from within the Controller and Model functions.
        
        $smarty->assign('O2', $O2);

        if ($_return === TRUE)
        {
            return $smarty->fetch($_file);
        }
        else
        {
            if($this->_use_template === TRUE)
            {
                $O2->template->render($_file);
            }
            else
            {
                $O2->output->append_output( $smarty->fetch($_file));
            }
        }
    }

    public function template($template = 'AUTO', $layout = 'index')
    {
        $this->_use_template = TRUE;

        // Instantiate the class
        $O2 =& get_instance();
        $O2->template->_set($template, $layout);

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Load class
     *
     * This function loads the requested class.
     *
     * @param   string  the item that is being loaded
     * @param   mixed   any additional parameters
     * @param   string  an optional object name
     * @return  void
     */
    protected function _load_class($class, $params = NULL, $object_name = NULL)
    {  
        global $O2SYSTEM, $URI;

        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace(__EXT__, '', trim($class, '/'));

        // Define library paths
        $_library_paths = $this->_library_paths;

        // Is App or Module Model Request?
        if (strrpos($class, '/') !== FALSE)
        {
            $x_class = preg_split('[/]', $class, -1, PREG_SPLIT_NO_EMPTY);

            // Check App Class Request
            if(in_array($x_class[0], $O2SYSTEM->apps))
            {
                $app_request = $x_class[0];

                $_library_paths = array_slice($_library_paths, 0, 2);
                array_push($_library_paths, APPSPATH.$app_request.'/libraries/');

                array_shift($x_class);
            }

            // Check App Module Request
            if(isset($app_request))
            {
                $app_modules = get_object_vars($O2SYSTEM->{$app_request}->modules);

                if(in_array($x_class[0], array_keys($app_modules)))
                {
                    $module_request = $x_class[0];

                    array_push($_library_paths, APPSPATH.$app_request.'/modules/'.$module_request.'/libraries/');

                    array_shift($x_class);
                }
            }

            $class = end($x_class);
        }

        // Define class filename
        $filename = prepare_class_name($class).__EXT__;

        // Prepare Class Name
        $parent_class_names = array(
            prepare_class_name($class),            
            prepare_class_name('O2_'.$class),
            prepare_class_name('CI_'.$class),
        );

        $child_class_names = array(
            prepare_class_name('App_'.$class),
            prepare_class_name($URI->request->app->parameter.'_'.$class),
            prepare_class_name($URI->request->module->parameter.'_'.$class),
        );

        //print_lines($child_class_names);

        if(isset($app_request))
        {
            $child_class_names = array_slice($child_class_names, 0, 1);
            array_push($child_class_names, prepare_class_name($app_request.'_'.$class));
        }

        if(isset($module_request))
        {
            $child_class_names = array_slice($child_class_names, 0, 2);
            
            array_push($child_class_names, prepare_class_name($app_request.'_'.$module_request.'_'.$class));
            array_push($child_class_names, prepare_class_name($module_request.'_'.$class));
        }
        
        $parent_found = FALSE;
        foreach($_library_paths as $filepath)
        {
            foreach($parent_class_names as $parent_class_name)
            {
                $_filepath = $filepath.$filename;

                if(file_exists($_filepath))
                {
                    if(! in_array( strtolower($parent_class_name), $this->_loaded_files))
                    {
                        include_once($_filepath);
                    }

                    if(class_exists($parent_class_name))
                    {
                        // Add into loaded files registry
                        $this->_loaded_files[] = strtolower($parent_class_name);
                        $this->_loaded_files = array_unique($this->_loaded_files);

                        $parent_found = $parent_class_name;
                        break;
                    }
                }
            }

            // Parent class has been found
            if($parent_found !== FALSE)
            {
                $class_name = $parent_found;

                // Check for Child classes
                $child_found = FALSE;

                $_child_library_paths = array_slice($_library_paths, 1);

                foreach($_child_library_paths as $filepath)
                {
                    foreach($child_class_names as $child_class_name)
                    {
                        $_filepath = $filepath.$filename;

                        if(file_exists($_filepath))
                        {
                            if(! in_array( strtolower($child_class_name), $this->_loaded_files))
                            {
                                include_once($_filepath);
                            }
                        }
                        elseif(file_exists($filepath.prepare_class_name($child_class_name).__EXT__))
                        {
                            if(! in_array( strtolower($child_class_name), $this->_loaded_files))
                            {
                                include_once($filepath.prepare_class_name($child_class_name).__EXT__);
                            }
                        }

                        if(class_exists($child_class_name))
                        {
                            // Add into loaded files registry
                            $this->_loaded_files[] = strtolower($child_class_name);
                            $this->_loaded_files = array_unique($this->_loaded_files);

                            $child_found[] = $child_class_name;
                            $child_found = array_unique($child_found);
                        }
                    }
                }

                if($child_found !== FALSE)
                {
                    $class_name = end($child_found);
                }

                //print_lines($child_found);

                return $this->_init_class($class, $class_name, $params, $object_name);

                break;
            }
        }

        log_message('error', "Unable to load the requested class: ".prepare_class_name($class));
        show_error("Unable to load the requested class: ".prepare_class_name($class));
    }

    // --------------------------------------------------------------------

    /**
     * Internal O2System Class Instantiator
     *
     * @used-by O2_Loader::_load_class()
     *
     * @param   string      $class      Class name
     * @param   string      $prefix     Class name prefix
     * @param   array|null|bool $config     Optional configuration to pass to the class constructor:
     *                      FALSE to skip;
     *                      NULL to search in config paths;
     *                      array containing configuration data
     * @param   string      $object_name    Optional object name to assign to
     * @return  void
     */
    protected function _init_class($class, $class_name, $config = FALSE, $object_name = NULL)
    {
        global $CFG;

        if($config === FALSE)
        {
            if(isset($CFG->$class))
            {
                $config = $CFG->$class;
            }
        }

        // Set the variable name we will assign the class to
        // Was a custom class name supplied? If so we'll use it
        if (empty($object_name))
        {
            $object_name = strtolower($class);
            if (isset($this->_varmap[$object_name]))
            {
                $object_name = $this->_varmap[$object_name];
            }
        }

        // Don't overwrite existing properties
        $O2 =& get_instance();
        
        if (isset($O2->$object_name))
        {
            if ($O2->$object_name instanceof $class)
            {
                log_message('debug', $class." has already been instantiated as '".$object_name."'. Second attempt aborted.");
                return;
            }

            show_error("Resource '".$object_name."' already exists and is not a ".$class." instance.");
        }

        // Save the class name and object name
        $this->_classes[$object_name] = strtolower($class);

        // Instantiate the class
        $O2->$object_name = ! empty($config)
            ? new $class_name($config)
            : new $class_name();
    }

    // --------------------------------------------------------------------

    /**
     * Autoloader
     *
     * The config/autoload.php file contains an array that permits sub-systems,
     * libraries, and helpers to be loaded automatically.
     *
     * @param   array
     * @return  void
     */
    private function _autoloader()
    {
        global $CFG;

        $autoload = $CFG->item('autoload');

        if (empty($autoload))
        {
            $autoload = $this->_autoload;
        }
        else
        {
            $autoload = array_merge_recursive($autoload, $this->_autoload);
        }

        // Autoload packages
        if (isset($autoload['packages']))
        {
            foreach ($autoload['packages'] as $package_path)
            {
                $this->add_package_path($package_path);
            }
        }

        // Autoload helpers and languages
        foreach (array('helper', 'language') as $type)
        {
            if (isset($autoload[$type]) && count($autoload[$type]) > 0)
            {
                $this->$type($autoload[$type]);
            }
        }

        // Autoload drivers
        if (isset($autoload['drivers']))
        {
            foreach ($autoload['drivers'] as $item)
            {
                $this->driver($item);
            }
        }

        // A little tweak to remain backward compatible
        // The $autoload['core'] item was deprecated
        if ( ! isset($autoload['libraries']) AND isset($autoload['core']))
        {
            $autoload['libraries'] = $autoload['core'];
        }

        // Load libraries
        if (isset($autoload['libraries']) && count($autoload['libraries']) > 0)
        {
            // Load the database driver.
            if (in_array('database', $autoload['libraries']))
            {
                $this->database();
                $autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
            }

            // Load all other libraries
            foreach ($autoload['libraries'] as $item)
            {
                $this->library($item);
            }
        }

        // Autoload models
        if (isset($autoload['model']))
        {
            $this->model($autoload['model']);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Object to Array translator
     *
     * Takes an object as input and converts the class variables to
     * an associative array with key/value pairs.
     *
     * @param   object  $object Object data to translate
     * @return  array
     */
    protected function _object_to_array($object)
    {
        return (is_object($object)) ? get_object_vars($object) : $object;
    }

    // --------------------------------------------------------------------

    /**
     * O2 Component getter
     *
     * Get a reference to a specific library or model.
     *
     * @param   string  $component  Component name
     * @return  bool
     */
    protected function &_get_component($component)
    {
        $O2 =& get_instance();
        return $O2->$component;
    }

    // --------------------------------------------------------------------

    // --------------------------------------------------------------------

    /**
     * Prep filename
     *
     * This function prepares filenames of various items to
     * make their loading more reliable.
     *
     * @param   string|string[] $filename   Filename(s)
     * @param   string      $extension  Filename extension
     * @return  array
     */
    protected function _prep_filename($filename, $extension)
    {
        if ( ! is_array($filename))
        {
            return array(strtolower(str_replace(__EXT__, '', str_replace($extension, '', $filename)).$extension));
        }
        else
        {
            foreach ($filename as $key => $val)
            {
                $filename[$key] = strtolower(str_replace(__EXT__, '', str_replace($extension, '', $val)).$extension);
            }

            return $filename;
        }
    }
}

/* End of file Loader.php */
/* Location: ./system/core/Loader.php */