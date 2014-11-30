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
 * @package     O2System
 * @subpackage  system/core
 * @category    Loader
 * @author      Steeven Andrian Salim
 * @link        http://circle-creative.com/products/o2system/user-guide/core/loader.html
 */

class O2_Loader
{
    /**
     * List of paths to load views from
     *
     * @var array
     * @access protected
     */
    protected $_view_paths = array();

    /**
     * List of paths to load libraries from
     *
     * @var array
     * @access protected
     */
    protected $_library_paths = array();

    /**
     * List of paths to load driver from
     *
     * @var array
     * @access protected
     */
    protected $_drivers_paths = array();

    /**
     * List of paths to load models from
     *
     * @var array
     * @access protected
     */
    protected $_model_paths = array();

    /**
     * List of paths to load helpers from
     *
     * @var array
     * @access protected
     */
    protected $_helper_paths = array();

    /**
     * List of loaded base classes
     * Set by the controller class
     *
     * @var array
     * @access protected
     */
    protected $_base_classes = array(); // Set by the controller class

    /**
     * List of cached variables
     *
     * @var array
     * @access protected
     */
    protected $_cached_vars = array();

    /**
     * List of loaded classes
     *
     * @var array
     * @access protected
     */
    protected $_classes = array();

    /**
     * List of loaded drivers
     *
     * @var array
     * @access protected
     */
    protected $_drivers = array();

    /**
     * List of loaded composers
     *
     * @var array
     * @access protected
     */
    protected $_composers = array();

    /**
     * List of loaded files
     *
     * @var array
     * @access protected
     */
    protected $_loaded_files = array();

    /**
     * List of loaded models
     *
     * @var array
     * @access protected
     */
    protected $_models = array();

    /**
     * List of loaded helpers
     *
     * @var array
     * @access protected
     */
    protected $_helpers = array();

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

    /**
     * List of autoload class, helpers, drivers
     * which is needed by system
     *
     * @var array
     * @access protected
     */
    protected $_autoload = array(
        'helper' => array('array', 'object', 'url'),
        'drivers' => array('template')
    );

    protected $_use_template = FALSE;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Define loaded classes
        $this->_classes =& is_loaded();

        // Load Composer
        $this->_load_composer();

        // Load Autoloader
        $this->_autoloader();

        log_message('debug', "Loader Class Initialized");
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
     * @access  public
     * @return  string|bool Class object name if loaded or FALSE
     */
    public function is_loaded($class)
    {
        return array_search(strtolower($class), $this->_classes, TRUE);
    }

    // --------------------------------------------------------------------

    /**
     * Composer Autoloader
     *
     * @access private
     * @return void
     */
    private function _load_composer()
    {
        global $O2SYSTEM, $URI;

        // System Composer
        if (isset($O2SYSTEM->system->vendor->files['autoload']))
        {
            require_once $O2SYSTEM->system->vendor->files['autoload'];
            $this->_composers['system'] = TRUE;
        }

        // Apps Composer
        if (isset($O2SYSTEM->app->vendor->files['autoload']))
        {
            require_once $O2SYSTEM->app->vendor->files['autoload'];
            $this->_composers['app'] = TRUE;
        }

        // Set Module Request by Requested URI
        $app_request = $URI->request->app->parameter;
        $module_request = @$URI->request->module->parameter;

        // Request App Composer
        if (isset($O2SYSTEM->{$app_request}->vendor->files['autoload']))
        {
            require_once $O2SYSTEM->{$app_request}->vendor->files['autoload'];
            $this->_composers[$app_request] = TRUE;
        }

        // Request Module Composer
        if(! empty($module_request))
        {
            if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}))
            {
                // Load Request Module Composer
                if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}->vendor->files['autoload']))
                {
                    require_once $O2SYSTEM->{$app_request}->modules->{$module_request}->vendor->files['autoload'];
                    $this->_composers[$app_request]['modules'][$module_request] = TRUE;
                }
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Composer Loader
     *
     * Load composer from module or app.
     *
     * @param   string      $module  Module or App Name or Combined with slash
     * @return  chaining
     */
    public function composer($module)
    {
        global $O2SYSTEM, $URI;

        // Set Module Request by Requested URI
        $app_request = $URI->request->app->parameter;

        if (strrpos($composer, '/') !== FALSE)
        {
            // Is System / Apps Composer Request?
            $x_module = preg_split('[/]', $module, -1, PREG_SPLIT_NO_EMPTY);

            // Is Apps Composer Request?
            if(in_array($x_module[0], $O2SYSTEM->apps))
            {
                $app_request = $x_module[0];
                array_shift($x_module);
            }

            $app_modules = get_object_vars($O2SYSTEM->{$app_request}->modules);

            if( in_array( $x_module[0], array_keys($app_modules) ) ) {
                $module_request = $x_module[0];
                array_shift($x_module);
            }
        }
        else
        {
            $module_request = $module;
        }

        if(isset($module_request))
        {
            if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}))
            {
                // Load Request Module Composer
                if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}->vendor->files['autoload']))
                {
                    require_once $O2SYSTEM->{$app_request}->modules->{$module_request}->vendor->files['autoload'];
                    $this->_composers[$app_request]['modules'][$module_request] = TRUE;

                    return $this;
                }
            }
        }

        // Couldn't Find Autoload
        $error_messages = 'Unable to load the composer autoload: ' . $module;
        log_message('error', $error_messages);
        show_error($error_messages);
    }

    /**
     * Library Loader
     *
     * Loads and instantiates libraries.
     * Designed to be called from application controllers.
     *
     * @param   string  $library        Library name
     * @param   array   $config         Optional parameters to pass to the library class constructor
     * @param   string  $object_name    An optional object name to assign to
     * @access  public
     * @return  object
     */
    public function library($library = '', $config = NULL, $object_name = NULL)
    {
        if(! empty($config) AND !is_array($config))
        {
            $object_name = $config;
            $config = NULL;
        }

        if (is_array($library))
        {
            foreach ($library as $class)
            {
                $this->library($class, $config);
            }

            return;
        }

        if ($library == '' OR isset($this->_base_classes[$library]))
        {
            return FALSE;
        }

        if ( ! is_null($config) && ! is_array($config))
        {
            $config = NULL;
        }

        $this->_load_class($library, $config, $object_name);
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
     * @access  public
     * @return  object
     */
    public function model($model, $object_name = NULL, $db_conn = FALSE)
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

        // Set Module Request by Requested URI
        $app_request = $URI->request->app->parameter;
        $module_request = @$URI->request->module->parameter;

        // Before we proceed more further is the module that requested has model with the same name?
        if(! empty($module_request) AND $model !== $module_request)
        {
            if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}->models))
            {
                $module_models = $O2SYSTEM->{$app_request}->modules->{$module_request}->models->files;
                
                if(in_array( strtolower($module_request.'_model'), array_keys($module_models)))
                {
                    // Define Model Class Name, Parameter and Object Name
                    $class_name = prepare_class_name($module_request.'_Model');
                    $class_parameter = strtolower($class_name);

                    // Load App Core Model
                    if(! class_exists('App_Model'))
                    {
                        require_once $O2SYSTEM->app->core->files['app_model'];
                    }

                    // Load Request App Core Model
                    $app_class_core_model = prepare_class_name($URI->request->app->parameter.'_Model');

                    if(! class_exists( $app_class_core_model ))
                    {
                        require_once $O2SYSTEM->{$app_request}->core->files[ strtolower($app_class_core_model) ];
                    }

                    $module_models = $O2SYSTEM->{$app_request}->modules->{$module_request}->models->files;

                    // Load Request Module Model
                    if(isset($module_models[$class_parameter]))
                    {
                        require_once $module_models[$class_parameter];

                        $module_class_name = prepare_class_name( $app_request.'_'.$class_name );

                        if(class_exists($module_class_name))
                        {
                            $class_name = $module_class_name;
                        }
                    } 

                    if (! in_array($class_parameter, $this->_models, TRUE))
                    {
                        $O2 =& get_instance();

                        if(! isset($O2->$class_parameter))
                        {
                            // Register loaded models registry
                            $this->_models[] = $class_parameter;
                            $this->_models = array_unique($this->_models);

                            if(class_exists($class_name))
                            {
                                $O2->$class_parameter = new $class_name();
                            }
                        }
                    }
                }
            }
        }

        // Is App or Module Model Request?
        if (strrpos($model, '/') !== FALSE)
        {
            // Is App Model Request?
            $x_model = preg_split('[/]', $model, -1, PREG_SPLIT_NO_EMPTY);

            if(in_array($x_model[0], $O2SYSTEM->apps))
            {
                $app_request = $x_model[0];
                array_shift($x_model);
            }

            // Is Module Model Request?
            $app_modules = get_object_vars($O2SYSTEM->{$app_request}->modules);

            if( in_array( $x_model[0], array_keys($app_modules) ) )
            {
                $module_request = $x_model[0];
                array_shift($x_model);
            }

            // Is Now Model Request?
            if(count($x_model) == 1)
            {
                $model = end($x_model);
            }
            elseif(empty($x_model))
            {
                $model = $module_request;
            }
        }
        // Is there any module at the same app which has same name with the model that requested?
        else
        {
            // Is Module Model Request?
            $app_modules = get_object_vars($O2SYSTEM->{$URI->request->app->parameter}->modules);

            if( in_array( $model, array_keys($app_modules) ) )
            {
                $module_request = $model;
            }
        }

        // Define Model Class Name, Parameter and Object Name
        $class_name = prepare_class_name($model.'_Model');
        $class_parameter = strtolower($class_name);

        $object_name = (empty($object_name) ? $class_parameter : $object_name);

        if (in_array($object_name, $this->_models, TRUE))
        {
            return $this;
        }

        $found = FALSE;

        // System Models
        $system_models = $O2SYSTEM->system->models->files;

        if(isset($system_models[$class_parameter]))
        {
            require_once $system_models[$class_parameter];
            $found = TRUE;
        }

        // App Models
        $app_models = $O2SYSTEM->app->models->files;

        if(isset($app_models[$class_parameter]))
        {
            // Load App Core Model
            if(! class_exists('App_Model'))
            {
                require_once $O2SYSTEM->app->core->files['app_model'];
            }

            require_once $app_models[$class_parameter];
            $found = TRUE;
        }

        // Request App Models
        $request_app_models = $O2SYSTEM->{$app_request}->models->files;

        if(isset($request_app_models[$class_parameter]))
        {
            // Load App Core Model
            if(! class_exists('App_Model'))
            {
                require_once $O2SYSTEM->app->core->files['app_model'];
            }

            // Load Request App Core Model
            $app_class_core_model = prepare_class_name($app_request.'_Model');

            if(! class_exists( $app_class_core_model ))
            {
                require_once $O2SYSTEM->{$app_request}->core->files[ strtolower($app_class_core_model) ];
            }

            require_once $request_app_models[$class_parameter];
            $found = TRUE;

            $model_class_name = prepare_class_name( $app_request.'_'.$class_name );

            if(class_exists($model_class_name))
            {
                $class_name = $model_class_name;
            }
        }

        // Request Module Models
        if(! empty($module_request) AND $found === FALSE)
        {
            // Load App Core Model
            if(! class_exists('App_Model'))
            {
                require_once $O2SYSTEM->app->core->files['app_model'];
            }

            // Load Request App Core Model
            $app_class_core_model = prepare_class_name($app_request.'_Model');

            if(! class_exists( $app_class_core_model ))
            {
                require_once $O2SYSTEM->{$app_request}->core->files[ strtolower($app_class_core_model) ];
            }

            if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}))
            {
                $module_models = $O2SYSTEM->{$app_request}->modules->{$module_request}->models->files;

                // Load Request Module Model
                if(isset($module_models[$class_parameter]))
                {
                    require_once $module_models[$class_parameter];

                    $model_class_name = prepare_class_name( $app_request.'_'.$class_name );

                    if(class_exists($model_class_name))
                    {
                        $class_name = $model_class_name;
                    }

                    $found = TRUE;
                }    
            }        
        }

        if($found === TRUE)
        {
            $O2 =& get_instance();

            if(isset($O2->$object_name))
            {
                $error_messages = 'The model name you are loading is the name of a resource that is already being used: '.$object_name;
                log_message('error', $error_messages);
                show_error($error_messages);
            }

            if ($db_conn !== FALSE && ! class_exists('CI_DB', FALSE))
            {
                if ($db_conn === TRUE)
                {
                    $db_conn = '';
                }

                $this->database($db_conn, FALSE, TRUE);
            }

            // Register loaded models registry
            $this->_models[] = $object_name;
            $this->_models = array_unique($this->_models);

            if(class_exists($class_name))
            {
                $O2->$object_name = new $class_name();
                return $this;
            }
        }

        // Couldn't Find Model
        $error_messages = 'Unable to locate the model you have specified: '.$model;
        log_message('error', $error_messages);
        show_error($error_messages);
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
     * @param   mixed   $config         Database configuration options
     * @param   bool    $return         Whether to return the database object
     * @param   bool    $query_builder  Whether to enable Query Builder
     *                  (overrides the configuration setting)
     *
     * @return  object|bool Database object if $return is set to TRUE,
     *          FALSE on failure, CI_Loader instance in any other case
     */
    public function database($config = '', $return = FALSE, $query_builder = NULL)
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
            return DB($config, $query_builder);
        }

        // Initialize the db variable. Needed to prevent
        // reference errors with some configurations
        $O2->db = '';

        // Load the DB class
        $O2->db =& DB($config, $query_builder);
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
    public function view($view = '', $vars = array(), $return = FALSE)
    {
        global $O2SYSTEM, $URI;
        $O2 =& get_instance();

        if(empty($view))
        {
            if($this->_use_template === TRUE)
            {
                return $O2->template->render();
            }
            else
            {
                return;
            }
        }

        // System Views
        $system_views = $O2SYSTEM->system->views;

        // Apps Views
        $apps_views = $O2SYSTEM->app->views;

        if(in_array($view, array_keys($apps_views->files)) AND !isset($view_path))
        {
            $view_path = $apps_views->files[$view];
        }

        // App Request Views
        $app_request = $URI->request->app->parameter;

        if(isset($O2SYSTEM->{$app_request}->views) AND !isset($view_path))
        {
            $app_request_views = $O2SYSTEM->{$app_request}->views;

            if(in_array($view, array_keys($app_request_views->files)))
            {
                $view_path = $app_request_views->files[$view];
            }
        }

        // Module Request Views
        $module_request = @$URI->request->module->parameter;
        if(! empty($module_request) AND ! isset($view_path))
        {
            $module_request_views = $O2SYSTEM->{$app_request}->modules->{$module_request}->views;
            if(in_array($view, array_keys($module_request_views->files)))
            {
                $view_path = $module_request_views->files[$view];
            }

            if(isset($O2->template->themes->active->modules))
            {
                $theme_modules = $O2->template->themes->active->modules;

                if(in_array($module_request, array_keys($theme_modules)))
                {
                    if(in_array($view, array_keys($theme_modules[$module_request])))
                    {
                        $view_path = $theme_modules[$module_request][$view];
                    }
                }
            }
        }

        if(isset($view_path))
        {
            $registry = new O2_System_Registry;
            $registry->parameter = pathinfo($view_path, PATHINFO_FILENAME);
            $registry->path = pathinfo($view_path, PATHINFO_DIRNAME).'/';
            $registry->filename = pathinfo($view_path, PATHINFO_BASENAME);;
            $registry->filepath = $view_path;

            if (is_array($vars))
            {
                $this->_cached_vars = array_merge($this->_cached_vars, $vars, array('O2' => $O2));
            }
            else
            {
                $this->_cached_vars = array_merge($this->_cached_vars, array('O2' => $O2));
            }

            $registry->vars = $this->_cached_vars;

            if($return === TRUE)
            {
                return $O2->template->parser->parse($registry);
            }
            else
            {
                if($this->_use_template === TRUE)
                {
                    $O2->template->render($registry);
                }
                else
                {
                    $output = $O2->template->parser->parse($registry);
                    $O2->output->append_output($output);
                }
            }
        }
        else
        {
            $error_messages = 'Unable to load the requested view file: ' . $view;
            log_message('error', $error_messages);
            show_error($error_messages);
        }
    }

    // --------------------------------------------------------------------

    public function theme($theme = 'AUTO', $layout = 'layout')
    {
        $this->_use_template = TRUE;

        // Instantiate the class
        $O2 =& get_instance();
        $O2->template->theme($theme, $layout);

        return $this;
    }

    public function layout($layout)
    {
        $O2 =& get_instance();
        $O2->template->themes->set_layout($layout);

        return $this;
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
    public function helper($helper = array())
    {
        // Request Multiple Helpers
        if(is_array($helper))
        {
            foreach($helper as $_helper)
            {
                $this->helper($_helper);
            }

            return $this;
        }

        global $O2SYSTEM, $URI;

        // Define App and Module Request
        $app_request = $URI->request->app->parameter;
        $module_request = @$URI->request->module->parameter;

        // Is App or Module Model Request?
        if (strrpos($helper, '/') !== FALSE)
        {
            // Is App helper Request?
            $x_helper = preg_split('[/]', $helper, -1, PREG_SPLIT_NO_EMPTY);

            if(in_array($x_helper[0], $O2SYSTEM->apps))
            {
                $app_request = $x_helper[0];
                array_shift($x_helper);
            }

            // Is Module helper Request?
            $app_modules = get_object_vars($O2SYSTEM->{$app_request}->modules);

            if( in_array( $x_helper[0], array_keys($app_modules) ) )
            {
                $module_request = $x_helper[0];
                array_shift($x_helper);
            }

            // Is Now helper Request?
            if(count($x_helper) == 1)
            {
                $helper = end($x_helper);
            }
            elseif(empty($x_helper))
            {
                $helper = $module_request;
            }
        }

        $helper_parameter = $helper.'_helper';

        // System Helpers
        $system_helpers = $O2SYSTEM->system->helpers->files;
        if(in_array($helper_parameter, array_keys($system_helpers)))
        {
            include_once($system_helpers[$helper_parameter]);

            $registry = new O2_System_Registry;
            $registry->parameter = $helper_parameter;
            $registry->name = prepare_class_name($registry->parameter);
            $registry->extends = FALSE;
            $registry->path = pathinfo($system_helpers[$registry->parameter], PATHINFO_DIRNAME);
            $registry->filename = pathinfo($system_helpers[$registry->parameter], PATHINFO_BASENAME);
            $registry->filepath = $system_helpers[$registry->parameter];

            $this->_helpers[$registry->parameter] = $registry;

            log_message('debug', 'Helper loaded from system: '.$helper);
        }

        // App Helpers
        $app_helpers = $O2SYSTEM->app->helpers->files;
        if(in_array($helper_parameter, array_keys($app_helpers)))
        {
            include_once($app_helpers[$helper_parameter]);
            
            if(isset($this->_helpers[$helper_parameter]))
            {
                $registry = new O2_System_Registry;
                $registry->parameter = $helper_parameter;
                $registry->name = prepare_class_name($registry->parameter);
                $registry->path = pathinfo($app_helpers[$registry->parameter], PATHINFO_DIRNAME);
                $registry->filename = pathinfo($app_helpers[$registry->parameter], PATHINFO_BASENAME);
                $registry->filepath = $app_helpers[$registry->parameter];

                $_registry = $this->_helpers[$helper_parameter];
                $_registry->extends[] = $registry;

                $this->_helpers[$helper_parameter] = $_registry;
            }
            else
            {
                $registry = new O2_System_Registry;
                $registry->parameter = $helper_parameter;
                $registry->name = prepare_class_name($registry->parameter);
                $registry->path = pathinfo($app_helpers[$registry->parameter], PATHINFO_DIRNAME);
                $registry->filename = pathinfo($app_helpers[$registry->parameter], PATHINFO_BASENAME);
                $registry->filepath = $app_helpers[$registry->parameter];

                $this->_helpers[$helper_parameter] = $registry;
            }

            log_message('debug', 'Helper loaded from apps: '.$helper);
        }


        // Request App Helpers
        $app_request_helpers = $O2SYSTEM->{$app_request}->helpers->files;

        if(in_array($helper_parameter, array_keys($app_request_helpers)))
        {
            include_once($app_request_helpers[$helper_parameter]);

            if(isset($this->_helpers[$helper_parameter]))
            {
                $registry = new O2_System_Registry;
                $registry->parameter = $helper_parameter;
                $registry->name = prepare_class_name($registry->parameter);
                $registry->path = pathinfo($app_helpers[$registry->parameter], PATHINFO_DIRNAME);
                $registry->filename = pathinfo($app_request_helpers[$registry->parameter], PATHINFO_BASENAME);
                $registry->filepath = $app_request_helpers[$registry->parameter];

                $_registry = $this->_helpers[$helper_parameter];
                $_registry->extends[] = $registry;

                $this->_helpers[$helper_parameter] = $_registry;
            }
            else
            {
                $registry = new O2_System_Registry;
                $registry->parameter = $helper_parameter;
                $registry->name = prepare_class_name($registry->parameter);
                $registry->path = pathinfo($app_helpers[$registry->parameter], PATHINFO_DIRNAME);
                $registry->filename = pathinfo($app_helpers[$registry->parameter], PATHINFO_BASENAME);
                $registry->filepath = $app_helpers[$registry->parameter];

                $this->_helpers[$helper_parameter] = $registry;
            }

            log_message('debug', 'Helper loaded from requested app: '.$helper);
        }

        // Request Module Helpers
        if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}))
        {
            $module_request_helpers = $O2SYSTEM->{$app_request}->modules->{$module_request}->helpers->files;

            if(in_array($helper_parameter, array_keys($module_request_helpers)))
            {
                include_once($module_request_helpers[$helper_parameter]);

                if (isset($this->_helpers[$helper_parameter]))
                {
                    $registry = new O2_System_Registry;
                    $registry->parameter = $helper_parameter;
                    $registry->name = prepare_class_name($registry->parameter);
                    $registry->path = pathinfo($app_helpers[$registry->parameter], PATHINFO_DIRNAME);
                    $registry->filename = pathinfo($module_request_helpers[$registry->parameter], PATHINFO_BASENAME);
                    $registry->filepath = $module_request_helpers[$registry->parameter];

                    $_registry = $this->_helpers[$helper_parameter];
                    $_registry->extends[] = $registry;

                    $this->_helpers[$helper_parameter] = $_registry;
                }
                else
                {
                    $registry = new O2_System_Registry;
                    $registry->parameter = $helper_parameter;
                    $registry->name = prepare_class_name($registry->parameter);
                    $registry->path = pathinfo($app_helpers[$registry->parameter], PATHINFO_DIRNAME);
                    $registry->filename = pathinfo($app_helpers[$registry->parameter], PATHINFO_BASENAME);
                    $registry->filepath = $app_helpers[$registry->parameter];

                    $this->_helpers[$helper_parameter] = $registry;
                }
            }

            log_message('debug', 'Helper loaded from requested module: '.$helper);
        }

        // unable to load the helper
        if ( ! isset($this->_helpers[$helper_parameter]))
        {
            $error_messages = 'Unable to load the requested file: helpers/'.$helper.'.php';
            log_message('error', $error_messages);
            show_error($error_messages);
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
     * @param   string|string[]         $helpers    Helper name(s)
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
     * @param   string          $lang   Language name
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
     * @param   string|string[] $library        Driver name(s)
     * @param   array           $config         Optional parameters to pass to the driver
     * @param   string          $object_name    An optional object name to assign to
     *
     * @return  object|bool Object or FALSE on failure if $library is a string
     *          and $object_name is set. CI_Loader instance otherwise.
     */
    public function driver($library = '', $config = array(), $object_name = NULL)
    {
        // Handle multiple library request
        if (is_array($library))
        {
            foreach ($library as $driver)
            {
                $this->driver($driver);
            }

            return $this;
        }
        elseif (empty($library))
        {
            return FALSE;
        }

         // Request Multiple Helpers
        if(is_array($library))
        {
            foreach($library as $_library)
            {
                $this->library($_library);
            }
        }

        global $O2SYSTEM, $URI;

        // Define App and Module Request
        $app_request = $URI->request->app->parameter;
        $module_request = @$URI->request->module->parameter;

        // Is App or Module Model Request?
        if (strrpos($library, '/') !== FALSE)
        {
            // Is App library Request?
            $x_library = preg_split('[/]', $library, -1, PREG_SPLIT_NO_EMPTY);

            if(in_array($x_library[0], $O2SYSTEM->apps))
            {
                $app_request = $x_library[0];
                array_shift($x_library);
            }

            // Is Module library Request?
            $app_modules = get_object_vars($O2SYSTEM->{$app_request}->modules);

            if( in_array( $x_library[0], array_keys($app_modules) ) )
            {
                $module_request = $x_library[0];
                array_shift($x_library);
            }

            // Is Now library Request?
            if(count($x_library) == 1)
            {
                $library = end($x_library);
            }
            elseif(empty($x_library))
            {
                $library = $module_request;
            }
        }

        if ( ! class_exists('CI_Driver_Library'))
        {
            // we aren't instantiating an object here, that'll be done by the Library itself
            require SYSPATH.'libraries/Driver.php';
        }

        $driver_parameter = strtolower($library);
        $driver_class = prepare_class_name($library);

        // Temp found class
        $found = FALSE;

        // Inheritance class
        $inheritance_registry = array();

        // System Libraries
        $system_libraries = $O2SYSTEM->system->libraries;
        $system_drivers = get_object_vars($system_libraries);
        $system_drivers = array_keys($system_drivers);
        $system_drivers = array_diff($system_drivers, array('path', 'files'));

        if( in_array($driver_parameter, $system_drivers) )
        {
            $registry = $system_libraries->{$driver_parameter};
            
            if(isset($registry->drivers))
            {
                $registry->parameter = $driver_parameter;
                $registry->name = prepare_class_name('O2_'.$registry->parameter);
                $registry->filepath = @reset($registry->files);
                $registry->filename = pathinfo($registry->filepath, PATHINFO_BASENAME);
                
                unset($registry->files);

                $inheritance_registry[$driver_parameter] = $registry;
                $found = TRUE;
            }
        }

        // App Libraries
        $app_libraries = $O2SYSTEM->app->libraries;
        $app_drivers = get_object_vars($app_libraries);
        $app_drivers = array_keys($app_drivers);
        $app_drivers = array_diff($app_drivers, array('path', 'files'));

        $app_libraries = $O2SYSTEM->app->libraries->files;
        $driver_parameter = str_replace('app_', '', $library);

        $driver_parameters = array(
            'app_'.$driver_parameter,
            $driver_parameter
        );

        foreach($driver_parameters as $driver_parameter)
        {
            if( in_array($driver_parameter, $app_drivers) )
            {
                $registry = $app_libraries->{$driver_parameter};
                
                if(isset($registry->drivers))
                {
                    $registry->parameter = $driver_parameter;
                    $registry->name = prepare_class_name($registry->parameter);
                    $registry->filepath = reset($registry->files);
                    $registry->filename = pathinfo($registry->filepath, PATHINFO_BASENAME);
                    
                    unset($registry->files);

                    $inheritance_registry[$driver_parameter] = $registry;
                    $found = TRUE;
                    break;
                }
            }
        }

        // Request App Libraries
        $app_request_libraries = $O2SYSTEM->{$app_request}->libraries;
        $app_request_drivers = get_object_vars($app_request_libraries);
        $app_request_drivers = array_keys($app_request_drivers);
        $app_request_drivers = array_diff($app_request_drivers, array('path', 'files'));

        $driver_parameter = str_replace($app_request, '', $library);

        $driver_parameters = array(
            $app_request.'_'.$driver_parameter,
            $driver_parameter
        );

        foreach($driver_parameters as $driver_parameter)
        {
            if( in_array($driver_parameter, $app_request_drivers) )
            {
                $registry = $app_request_libraries->{$driver_parameter};
                
                if(isset($registry->drivers))
                {
                    $registry->parameter = $driver_parameter;
                    $registry->name = prepare_class_name($registry->parameter);
                    $registry->filepath = reset($registry->files);
                    $registry->filename = pathinfo($registry->filepath, PATHINFO_BASENAME);
                    
                    unset($registry->files);

                    $inheritance_registry[$driver_parameter] = $registry;
                    $found = TRUE;
                    break;
                }
            }
        }

        // Request Module Libraries
        if(! empty($module_request))
        {
            if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}))
            {
                $module_request_libraries = $O2SYSTEM->{$app_request}->modules->{$module_request}->libraries;
                $module_request_drivers = get_object_vars($module_request_libraries);
                $module_request_drivers = array_keys($module_request_drivers);
                $module_request_drivers = array_diff($module_request_drivers, array('path', 'files'));

                $driver_parameter = str_replace($module_request.'_', '', $library);

                $driver_parameters = array(
                    $module_request.'_'.$driver_parameter,
                    $app_request.'_'.$module_request.'_'.$driver_parameter,
                    $driver_parameter
                );

                foreach($driver_parameters as $driver_parameter)
                {
                    if( in_array($driver_parameter, $module_request_drivers) )
                    {
                        $registry = $module_request_libraries->{$driver_parameter};
                        
                        if(isset($registry->drivers))
                        {
                            $registry->parameter = $driver_parameter;
                            $registry->name = prepare_class_name($registry->parameter);
                            $registry->filepath = reset($registry->files);
                            $registry->filename = pathinfo($registry->filepath, PATHINFO_BASENAME);
                            
                            unset($registry->files);

                            $inheritance_registry[$driver_parameter] = $registry;
                            $found = TRUE;
                            break;
                        }
                    }
                }
            }
        }

        if(! empty($inheritance_registry) AND $found === TRUE)
        {
            // Class Requested Has Inheritance
            if(count($inheritance_registry) > 1)
            {
                foreach($inheritance_registry as $registry)
                {
                    if(! class_exists($registry->name))
                    {
                        include_once($registry->filepath);
                        $this->_loaded_files[] = $registry->parameter;
                        $this->_loaded_files = array_unique($this->_loaded_files);
                    }
                }

                // Initialize Class from last registry
                return $this->_init_class($registry, $config, $object_name, TRUE);
            }
            else
            {
                $registy = reset($inheritance_registry);

                @include_once($registry->filepath);

                $this->_loaded_files[] = $registry->parameter;
                $this->_loaded_files = array_unique($this->_loaded_files);

                // Initialize Class
                return $this->_init_class($registry, $config, $object_name, TRUE);
            }
        }

        $error_messages = 'Failed to load driver, non-existent class: '.$driver_class;
        log_message('error', $error_messages);
        show_error($error_messages);
    }

    // --------------------------------------------------------------------

    public function get_drivers_registry($library = NULL)
    {
        if(empty($library))
        {
            return $this->_drivers;
        }
        else
        {
            if(isset($this->_drivers[$library]))
            {
                return $this->_drivers[$library];
            }
            else
            {
                $driver_parameter = strtolower($library);
                $driver_class = prepare_class_name($library);

                // Class Names
                $class_names = array(
                    'CI_'.$driver_class,
                    'O2_'.$driver_class,
                    'App_'.$driver_class,
                    $driver_class
                );

                global $O2SYSTEM, $URI, $system_path, $apps_path;

                $driver_paths = array($system_path, $apps_path, $URI->request->app->parameter);

                $found = FALSE;
                foreach($driver_paths as $driver_path)
                {
                    if(isset( $O2SYSTEM->{$driver_path}->libraries->{ $driver_parameter } ))
                    {
                        $registry = $O2SYSTEM->{$driver_path}->libraries->{ $driver_parameter };
                        $registry->parameter = strtolower($library);

                        $found = TRUE;
                        break;
                    }
                }

                if($found === TRUE)
                {
                    foreach($class_names as $class_name)
                    {
                        if(class_exists($class_name))
                        {
                            $registry->name = $class_name;
                            $registry->prefix = str_replace($driver_class, '', $registry->name);

                            return $registry;
                        }
                    }
                }

                return FALSE;
            }
        }
    }

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
    protected function _load_class($class, $config = NULL, $object_name = NULL)
    {  
        global $O2SYSTEM, $URI;

        // Define App and Module Request
        $app_request = $URI->request->app->parameter;
        $module_request = @$URI->request->module->parameter;

        // Is App or Module Model Request?
        if (strrpos($class, '/') !== FALSE)
        {
            // Is App Class Request?
            $x_class = preg_split('[/]', $class, -1, PREG_SPLIT_NO_EMPTY);

            if(in_array($x_class[0], $O2SYSTEM->apps))
            {
                $app_request = $x_class[0];
                array_shift($x_class);
            }

            // Is Module Class Request?
            $app_modules = get_object_vars($O2SYSTEM->{$app_request}->modules);

            if( in_array( $x_class[0], array_keys($app_modules) ) )
            {
                $module_request = $x_class[0];
                array_shift($x_class);
            }

            // Is Now Class Request?
            if(count($x_class) == 1)
            {
                $class = end($x_class);
            }
            elseif(empty($x_class))
            {
                $class = $module_request;
            }
        }

        // Temp found class
        $found = FALSE;

        // Inheritance class
        $inheritance_registry = array();

        // System Classes
        $system_libraries = $O2SYSTEM->system->libraries->files;

        if( in_array($class, array_keys($system_libraries)) )
        {
            $registry = new O2_System_Class_Registry;
            $registry->parameter = $class;
            $registry->name = prepare_class_name('O2_'.$registry->parameter);
            $registry->path = pathinfo($system_libraries[$registry->parameter], PATHINFO_DIRNAME);
            $registry->filename = pathinfo($system_libraries[$registry->parameter], PATHINFO_BASENAME);
            $registry->filepath = $system_libraries[$registry->parameter];

            $inheritance_registry[$registry->parameter] = $registry;

            $found = TRUE;
        }

        // App Libraries
        $app_libraries = $O2SYSTEM->app->libraries->files;
        $class_parameter = str_replace('app_', '', $class);

        $class_parameters = array(
            'app_'.$class_parameter,
            $class_parameter
        );

        foreach($class_parameters as $class_parameter)
        {
            if( in_array($class_parameter, array_keys($app_libraries)) )
            {
                $registry = new O2_System_Class_Registry;
                $registry->parameter = $class_parameter;
                $registry->name = prepare_class_name($registry->parameter);
                $registry->path = pathinfo($app_libraries[$registry->parameter], PATHINFO_DIRNAME);
                $registry->filename = pathinfo($app_libraries[$registry->parameter], PATHINFO_BASENAME);
                $registry->filepath = $app_libraries[$registry->parameter];

                $inheritance_registry[$registry->parameter] = $registry;

                $found = TRUE;
                break;
            }
        }

        // Request App Libraries
        $app_request_libraries = $O2SYSTEM->{$app_request}->libraries->files;
        $class_parameter = str_replace($app_request, '', $class);

        $class_parameters = array(
            $app_request.'_'.$class_parameter,
            $class_parameter
        );

        foreach($class_parameters as $class_parameter)
        {
            if( in_array($class_parameter, array_keys($app_request_libraries)) )
            {
                $registry = new O2_System_Class_Registry;
                $registry->parameter = $class_parameter;
                $registry->name = prepare_class_name($registry->parameter);
                $registry->path = pathinfo($app_request_libraries[$registry->parameter], PATHINFO_DIRNAME);
                $registry->filename = pathinfo($app_request_libraries[$registry->parameter], PATHINFO_BASENAME);
                $registry->filepath = $app_request_libraries[$registry->parameter];

                $inheritance_registry[$registry->parameter] = $registry;

                $found = TRUE;
            }
        }

        // Request Module Libraries
        if(! empty($module_request))
        {
            if(isset($O2SYSTEM->{$app_request}->modules->{$module_request}))
            {
                $module_request_libraries = $O2SYSTEM->{$app_request}->modules->{$module_request}->libraries->files;
                $class_parameter = str_replace($module_request.'_', '', $class);

                $class_parameters = array(
                    $module_request.'_'.$class_parameter,
                    $app_request.'_'.$module_request.'_'.$class_parameter,
                    $class_parameter
                );

                foreach($class_parameters as $class_parameter)
                {
                    if( in_array($class_parameter, array_keys($module_request_libraries)) )
                    {
                        $registry = new O2_System_Class_Registry;
                        $registry->parameter = $class_parameter;
                        $registry->name = prepare_class_name($registry->parameter);
                        $registry->path = pathinfo($module_request_libraries[$registry->parameter], PATHINFO_DIRNAME);
                        $registry->filename = pathinfo($module_request_libraries[$registry->parameter], PATHINFO_BASENAME);
                        $registry->filepath = $module_request_libraries[$registry->parameter];

                        $inheritance_registry[$registry->parameter] = $registry;

                        $found = TRUE;
                        break;
                    }
                }
            }
        }

        if(! empty($inheritance_registry) AND $found === TRUE)
        {
            // Class Requested Has Inheritance
            if(count($inheritance_registry) > 1)
            {
                foreach($inheritance_registry as $registry)
                {
                    if(! class_exists($registry->name))
                    {
                        include_once($registry->filepath);

                        $this->_loaded_files[] = $registry->parameter;
                        $this->_loaded_files = array_unique($this->_loaded_files);
                    }
                }

                // Initialize Class from last registry
                return $this->_init_class($registry, $config, $object_name);
            }
            else
            {
                $registy = reset($inheritance_registry);

                include_once($registry->filepath);

                $this->_loaded_files[] = $registry->parameter;
                $this->_loaded_files = array_unique($this->_loaded_files);

                // Initialize Class
                return $this->_init_class($registry, $config, $object_name);
            }
        }

        // Couldn't find class
        $error_messages = 'Unable to load the requested class: '.prepare_class_name($class);
        log_message('error', $error_messages);
        show_error($error_messages);
    }

    // --------------------------------------------------------------------

    /**
     * Internal O2System Class Initialization
     *
     * @used-by O2_Loader::_load_class()
     *
     * @param   object      $registry       Class name
     * @param   string      $prefix         Class name prefix
     * @param   array       $config         Optional configuration to pass to the class constructor:
     *                                      FALSE to skip;
     *                                      NULL to search in config paths;
     *                                      array containing configuration data
     * @param   string      $object_name    Optional object name to assign to
     * @return  void
     */
     private function _init_class($registry, $config = array(), $object_name = NULL, $driver_init = FALSE)
    {
        global $CFG;

        $class_name = $registry->name;

        // Is the class name valid?
        if ( ! class_exists($registry->name, FALSE))
        {
            // Check for existing CodeIgniter Class
            $codeigniter_class_name = str_replace('O2', 'CI', $registry->name);

            if( ! class_exists($codeigniter_class_name, FALSE))
            {
                log_message('error', 'Non-existent class: '.$registry->name);
                show_error('Non-existent class: '.$registry->name);
            }
            else
            {
                $class_name = $codeigniter_class_name;
            }
        }

        // Set the variable name we will assign the class to
        // Was a custom class name supplied? If so we'll use it
        if (empty($object_name))
        {
            $object_name = $registry->parameter;
            if (isset($this->_varmap[$object_name]))
            {
                $object_name = $this->_varmap[$object_name];
            }
        }

        // Don't overwrite existing properties
        $O2 =& get_instance();
        if (isset($O2->$object_name))
        {
            if ($O2->$object_name instanceof $class_name)
            {
                log_message('debug', $class_name." has already been instantiated as '".$object_name."'. Second attempt aborted.");
                return;
            }

            // Couldn't find class
            $error_messages = "Resource '".$object_name."' already exists and is not a ".$class_name." instance.";
            log_message('error', $error_messages);
            show_error($error_messages);
        }

        // Save the class name and object name
        if($driver_init === TRUE)
        {
            $this->_drivers[$object_name] = $registry;
        }
        else 
        {
            $this->_classes[$object_name] = $class_name;
        }

        // Check for config
        if(empty($config))
        {
            $config = $CFG->item($registry->parameter);
        }

        // Instantiate the class
        $O2->$object_name = isset($config)
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
}

/* End of file Loader.php */
/* Location: ./system/core/Loader.php */