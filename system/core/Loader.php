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
 * System Loader
 *
 * Autoload class base on SPL Functions
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Loader
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2system/user-guide.html
 */

class Core_Loader
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
    protected $_varmap           = array('unit_test' => 'unit',
                                            'user_agent' => 'agent');

    /**
     * Constructor
     *
     * Sets the path to the view files and gets the initial output buffering level
     */
    public function __construct()
    {
        global $PATH;

        $this->_ob_level  = ob_get_level();
        $this->_library_paths = array(APPSPATH, BASEPATH);
        $this->_helper_paths = array(APPSPATH, BASEPATH);
        $this->_model_paths = array(APPSPATH);
        $this->_view_paths = array(APPSPATH.'views/'  => TRUE);

        log_message('debug', "Loader Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Initialize the Loader
     *
     * This method is called once in Core_Controller.
     *
     * @param   array
     * @return  object
     */
    public function initialize()
    {
        $this->_classes = array();
        $this->_loaded_files = array();
        $this->_models = array();
        $this->_base_classes =& is_loaded();

        $this->_autoloader();

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Is Loaded
     *
     * A utility function to test if a class is in the self::$_classes array.
     * This function returns the object name if the class tested for is loaded,
     * and returns FALSE if it isn't.
     *
     * It is mainly used in the form_helper -> _get_validation_object()
     *
     * @param   string  class being checked for
     * @return  mixed   class object name on the CI SuperObject or FALSE
     */
    public function is_loaded($class)
    {
        if (isset($this->_classes[$class]))
        {
            return $this->_classes[$class];
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Class Loader
     *
     * This function lets users load and instantiate classes.
     * It is designed to be called from a user's app controllers.
     *
     * @param   string  the name of the class
     * @param   mixed   the optional parameters
     * @param   string  an optional object name
     * @return  void
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
     * This function lets users load and instantiate models.
     *
     * @param   string  the name of the class
     * @param   string  name for the model
     * @param   bool    database connection
     * @return  void
     */
    public function model($model, $name = '', $db_conn = FALSE)
    {
        if (is_array($model))
        {
            foreach ($model as $babe)
            {
                $this->model($babe);
            }
            return;
        }

        if ($model == '')
        {
            return;
        }

        $path = '';

        // Is the model in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($model, '/')) !== FALSE)
        {
            // The path is in front of the last slash
            $path = substr($model, 0, $last_slash + 1);

            // And the model name behind it
            $model = substr($model, $last_slash + 1);
        }

        if ($name == '')
        {
            $name = $model;
        }

        if (in_array($name, $this->_models, TRUE))
        {
            return;
        }

        $CI =& get_instance();
        if (isset($CI->$name))
        {
            show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
        }

        $model = strtolower($model);

        foreach ($this->_model_paths as $mod_path)
        {
            if ( ! file_exists($mod_path.'models/'.$path.$model.'.php'))
            {
                continue;
            }

            if ($db_conn !== FALSE AND ! class_exists('CI_DB'))
            {
                if ($db_conn === TRUE)
                {
                    $db_conn = '';
                }

                $CI->load->database($db_conn, FALSE, TRUE);
            }

            if ( ! class_exists('CI_Model'))
            {
                load_class('Model', 'core');
            }

            require_once($mod_path.'models/'.$path.$model.'.php');

            $model = ucfirst($model);

            $CI->$name = new $model();

            $this->_models[] = $name;
            return;
        }

        // couldn't find the model
        show_error('Unable to locate the model you have specified: '.$model);
    }

    // --------------------------------------------------------------------

    /**
     * Database Loader
     *
     * @param   string  the DB credentials
     * @param   bool    whether to return the DB object
     * @param   bool    whether to enable active record (this allows us to override the config setting)
     * @return  object
     */
    public function database($params = '', $return = FALSE, $active_record = NULL)
    {
        // Grab the super object
        $CI =& get_instance();

        // Do we even need to load the database class?
        if (class_exists('CI_DB') AND $return == FALSE AND $active_record == NULL AND isset($CI->db) AND is_object($CI->db))
        {
            return FALSE;
        }

        require_once(BASEPATH.'database/DB.php');

        if ($return === TRUE)
        {
            return DB($params, $active_record);
        }

        // Initialize the db variable.  Needed to prevent
        // reference errors with some configurations
        $CI->db = '';

        // Load the DB class
        $CI->db =& DB($params, $active_record);
    }

    // --------------------------------------------------------------------

    /**
     * Load the Utilities Class
     *
     * @return  string
     */
    public function dbutil()
    {
        if ( ! class_exists('CI_DB'))
        {
            $this->database();
        }

        $CI =& get_instance();

        // for backwards compatibility, load dbforge so we can extend dbutils off it
        // this use is deprecated and strongly discouraged
        $CI->load->dbforge();

        require_once(BASEPATH.'database/DB_utility.php');
        require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_utility.php');
        $class = 'CI_DB_'.$CI->db->dbdriver.'_utility';

        $CI->dbutil = new $class();
    }

    // --------------------------------------------------------------------

    /**
     * Load the Database Forge Class
     *
     * @return  string
     */
    public function dbforge()
    {
        if ( ! class_exists('CI_DB'))
        {
            $this->database();
        }

        $CI =& get_instance();

        require_once(BASEPATH.'database/DB_forge.php');
        require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_forge.php');
        $class = 'CI_DB_'.$CI->db->dbdriver.'_forge';

        $CI->dbforge = new $class();
    }

    // --------------------------------------------------------------------

    /**
     * Load View
     *
     * This function is used to load a "view" file.  It has three parameters:
     *
     * 1. The name of the "view" file to be included.
     * 2. An associative array of data to be extracted for use in the view.
     * 3. TRUE/FALSE - whether to return the data or load it.  In
     * some cases it's advantageous to be able to return data so that
     * a developer can process it in some way.
     *
     * @param   string
     * @param   array
     * @param   bool
     * @return  void
     */
    public function view($view, $vars = array(), $return = FALSE)
    {
        return $this->_load(array('_view' => $view, '_vars' => $this->_object_to_array($vars), '_return' => $return));
    }

    // --------------------------------------------------------------------

    /**
     * Load File
     *
     * This is a generic file loader
     *
     * @param   string
     * @param   bool
     * @return  string
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
     * @param   array
     * @param   string
     * @return  void
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
     * Load Helper
     *
     * This function loads the specified helper file.
     *
     * @param   mixed
     * @return  void
     */
    public function helper($helpers = array())
    {
        foreach ($this->_prep_filename($helpers, '_helper') as $helper)
        {
            if (isset($this->_helpers[$helper]))
            {
                continue;
            }

            $ext_helper = APPSPATH.'helpers/'.config_item('subclass_prefix').$helper.'.php';

            // Is this a helper extension request?
            if (file_exists($ext_helper))
            {
                $base_helper = BASEPATH.'helpers/'.$helper.'.php';

                if ( ! file_exists($base_helper))
                {
                    show_error('Unable to load the requested file: helpers/'.$helper.'.php');
                }

                include_once($ext_helper);
                include_once($base_helper);

                $this->_helpers[$helper] = TRUE;
                log_message('debug', 'Helper loaded: '.$helper);
                continue;
            }

            // Try to load the helper
            foreach ($this->_helper_paths as $path)
            {
                if (file_exists($path.'helpers/'.$helper.'.php'))
                {
                    include_once($path.'helpers/'.$helper.'.php');

                    $this->_helpers[$helper] = TRUE;
                    log_message('debug', 'Helper loaded: '.$helper);
                    break;
                }
            }

            // unable to load the helper
            if ( ! isset($this->_helpers[$helper]))
            {
                show_error('Unable to load the requested file: helpers/'.$helper.'.php');
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Load Helpers
     *
     * This is simply an alias to the above function in case the
     * user has written the plural form of this function.
     *
     * @param   array
     * @return  void
     */
    public function helpers($helpers = array())
    {
        $this->helper($helpers);
    }

    // --------------------------------------------------------------------

    /**
     * Loads a language file
     *
     * @param   array
     * @param   string
     * @return  void
     */
    public function language($file = array(), $lang = '')
    {
        $CI =& get_instance();

        if ( ! is_array($file))
        {
            $file = array($file);
        }

        foreach ($file as $langfile)
        {
            $CI->lang->load($langfile, $lang);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Loads a config file
     *
     * @param   string
     * @param   bool
     * @param   bool
     * @return  void
     */
    public function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
    {
        $CI =& get_instance();
        $CI->config->load($file, $use_sections, $fail_gracefully);
    }

    // --------------------------------------------------------------------

    /**
     * Driver
     *
     * Loads a driver library
     *
     * @param   string  the name of the class
     * @param   mixed   the optional parameters
     * @param   string  an optional object name
     * @return  void
     */
    public function driver($library = '', $params = NULL, $object_name = NULL)
    {
        if ( ! class_exists('CI_Driver_Library'))
        {
            // we aren't instantiating an object here, that'll be done by the Library itself
            require BASEPATH.'libraries/Driver.php';
        }

        if ($library == '')
        {
            return FALSE;
        }

        // We can save the loader some time since Drivers will *always* be in a subfolder,
        // and typically identically named to the library
        if ( ! strpos($library, '/'))
        {
            $library = ucfirst($library).'/'.$library;
        }

        return $this->library($library, $params, $object_name);
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
     * Get Package Paths
     *
     * Return a list of all package paths, by default it will ignore BASEPATH.
     *
     * @param   string
     * @return  void
     */
    public function get_package_paths($include_base = FALSE)
    {
        return $include_base === TRUE ? $this->_library_paths : $this->_model_paths;
    }

    // --------------------------------------------------------------------

    /**
     * Remove Package Path
     *
     * Remove a path from the library, model, and helper path arrays if it exists
     * If no path is provided, the most recently added path is removed.
     *
     * @param   type
     * @param   bool
     * @return  type
     */
    public function remove_package_path($path = '', $remove_config_path = TRUE)
    {
        $config =& $this->_get_component('config');

        if ($path == '')
        {
            $void = array_shift($this->_library_paths);
            $void = array_shift($this->_model_paths);
            $void = array_shift($this->_helper_paths);
            $void = array_shift($this->_view_paths);
            $void = array_shift($config->_config_paths);
        }
        else
        {
            $path = rtrim($path, '/').'/';
            foreach (array('_library_paths', '_model_paths', '_helper_paths') as $var)
            {
                if (($key = array_search($path, $this->{$var})) !== FALSE)
                {
                    unset($this->{$var}[$key]);
                }
            }

            if (isset($this->_view_paths[$path.'views/']))
            {
                unset($this->_view_paths[$path.'views/']);
            }

            if (($key = array_search($path, $config->_config_paths)) !== FALSE)
            {
                unset($config->_config_paths[$key]);
            }
        }

        // make sure the application default paths are still in the array
        $this->_library_paths = array_unique(array_merge($this->_library_paths, array(APPSPATH, BASEPATH)));
        $this->_helper_paths = array_unique(array_merge($this->_helper_paths, array(APPSPATH, BASEPATH)));
        $this->_model_paths = array_unique(array_merge($this->_model_paths, array(APPSPATH)));
        $this->_view_paths = array_merge($this->_view_paths, array(APPSPATH.'views/' => TRUE));
        $config->_config_paths = array_unique(array_merge($config->_config_paths, array(APPSPATH)));
    }

    // --------------------------------------------------------------------

    /**
     * Loader
     *
     * This function is used to load views and files.
     * Variables are prefixed with _ to avoid symbol collision with
     * variables made available to view files
     *
     * @param   array
     * @return  void
     */
    protected function _load($_data)
    {
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
            $_file = ($_ext == '') ? $_view.'.php' : $_view;

            foreach ($this->_view_paths as $view_file => $cascade)
            {
                if (file_exists($view_file.$_file))
                {
                    $_path = $view_file.$_file;
                    $file_exists = TRUE;
                    break;
                }

                if ( ! $cascade)
                {
                    break;
                }
            }
        }

        if ( ! $file_exists && ! file_exists($_path))
        {
            show_error('Unable to load the requested file: '.$_file);
        }

        // This allows anything loaded using $this->load (views, files, etc.)
        // to become accessible from within the Controller and Model functions.

        $_CI =& get_instance();
        foreach (get_object_vars($_CI) as $_key => $_var)
        {
            if ( ! isset($this->$_key))
            {
                $this->$_key =& $_CI->$_key;
            }
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
        extract($this->_cached_vars);

        /*
         * Buffer the output
         *
         * We buffer the output for two reasons:
         * 1. Speed. You get a significant speed boost.
         * 2. So that the final rendered template can be
         * post-processed by the output class.  Why do we
         * need post processing?  For one thing, in order to
         * show the elapsed page load time.  Unless we
         * can intercept the content right before it's sent to
         * the browser and then stop the timer it won't be accurate.
         */
        ob_start();

        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.

        if ((bool) @ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE)
        {
            echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_path))));
        }
        else
        {
            include($_path); // include() vs include_once() allows for multiple views with the same name
        }

        log_message('debug', 'File loaded: '.$_path);

        // Return the file data if requested
        if ($_return === TRUE)
        {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        /*
         * Flush the buffer... or buff the flusher?
         *
         * In order to permit views to be nested within
         * other views, we need to flush the content back out whenever
         * we are beyond the first level of output buffering so that
         * it can be seen and included properly by the first included
         * template and any subsequent ones. Oy!
         *
         */
        if (ob_get_level() > $this->_ob_level + 1)
        {
            ob_end_flush();
        }
        else
        {
            $_CI->output->append_output(ob_get_contents());
            @ob_end_clean();
        }
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
        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace('.php', '', trim($class, '/'));

        // Was the path included with the class name?
        // We look for a slash to determine this
        $subdir = '';
        if (($last_slash = strrpos($class, '/')) !== FALSE)
        {
            // Extract the path
            $subdir = substr($class, 0, $last_slash + 1);

            // Get the filename from the path
            $class = substr($class, $last_slash + 1);
        }

        // We'll test for both lowercase and capitalized versions of the file name
        foreach (array(ucfirst($class), strtolower($class)) as $class)
        {
            $subclass = APPSPATH.'libraries/'.$subdir.config_item('subclass_prefix').$class.'.php';

            // Is this a class extension request?
            if (file_exists($subclass))
            {
                $baseclass = BASEPATH.'libraries/'.ucfirst($class).'.php';

                if ( ! file_exists($baseclass))
                {
                    log_message('error', "Unable to load the requested class: ".$class);
                    show_error("Unable to load the requested class: ".$class);
                }

                // Safety:  Was the class already loaded by a previous call?
                if (in_array($subclass, $this->_loaded_files))
                {
                    // Before we deem this to be a duplicate request, let's see
                    // if a custom object name is being supplied.  If so, we'll
                    // return a new instance of the object
                    if ( ! is_null($object_name))
                    {
                        $CI =& get_instance();
                        if ( ! isset($CI->$object_name))
                        {
                            return $this->_init_class($class, config_item('subclass_prefix'), $params, $object_name);
                        }
                    }

                    $is_duplicate = TRUE;
                    log_message('debug', $class." class already loaded. Second attempt ignored.");
                    return;
                }

                include_once($baseclass);
                include_once($subclass);
                $this->_loaded_files[] = $subclass;

                return $this->_init_class($class, config_item('subclass_prefix'), $params, $object_name);
            }

            // Lets search for the requested library file and load it.
            $is_duplicate = FALSE;
            foreach ($this->_library_paths as $path)
            {
                $filepath = $path.'libraries/'.$subdir.$class.'.php';

                // Does the file exist?  No?  Bummer...
                if ( ! file_exists($filepath))
                {
                    continue;
                }

                // Safety:  Was the class already loaded by a previous call?
                if (in_array($filepath, $this->_loaded_files))
                {
                    // Before we deem this to be a duplicate request, let's see
                    // if a custom object name is being supplied.  If so, we'll
                    // return a new instance of the object
                    if ( ! is_null($object_name))
                    {
                        $CI =& get_instance();
                        if ( ! isset($CI->$object_name))
                        {
                            return $this->_init_class($class, '', $params, $object_name);
                        }
                    }

                    $is_duplicate = TRUE;
                    log_message('debug', $class." class already loaded. Second attempt ignored.");
                    return;
                }

                include_once($filepath);
                $this->_loaded_files[] = $filepath;
                return $this->_init_class($class, '', $params, $object_name);
            }

        } // END FOREACH

        // One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
        if ($subdir == '')
        {
            $path = strtolower($class).'/'.$class;
            return $this->_load_class($path, $params);
        }

        // If we got this far we were unable to find the requested class.
        // We do not issue errors if the load call failed due to a duplicate request
        if ($is_duplicate == FALSE)
        {
            log_message('error', "Unable to load the requested class: ".$class);
            show_error("Unable to load the requested class: ".$class);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Instantiates a class
     *
     * @param   string
     * @param   string
     * @param   bool
     * @param   string  an optional object name
     * @return  null
     */
    protected function _init_class($class, $prefix = '', $config = FALSE, $object_name = NULL)
    {
        // Is there an associated config file for this class?  Note: these should always be lowercase
        if ($config === NULL)
        {
            // Fetch the config paths containing any package paths
            $config_component = $this->_get_component('config');

            if (is_array($config_component->_config_paths))
            {
                // Break on the first found file, thus package files
                // are not overridden by default paths
                foreach ($config_component->_config_paths as $path)
                {
                    // We test for both uppercase and lowercase, for servers that
                    // are case-sensitive with regard to file names. Check for environment
                    // first, global next
                    if (defined('ENVIRONMENT') AND file_exists($path .'config/'.ENVIRONMENT.'/'.strtolower($class).'.php'))
                    {
                        include($path .'config/'.ENVIRONMENT.'/'.strtolower($class).'.php');
                        break;
                    }
                    elseif (defined('ENVIRONMENT') AND file_exists($path .'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php'))
                    {
                        include($path .'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php');
                        break;
                    }
                    elseif (file_exists($path .'config/'.strtolower($class).'.php'))
                    {
                        include($path .'config/'.strtolower($class).'.php');
                        break;
                    }
                    elseif (file_exists($path .'config/'.ucfirst(strtolower($class)).'.php'))
                    {
                        include($path .'config/'.ucfirst(strtolower($class)).'.php');
                        break;
                    }
                }
            }
        }

        if ($prefix == '')
        {
            if (class_exists('CI_'.$class))
            {
                $name = 'CI_'.$class;
            }
            elseif (class_exists(config_item('subclass_prefix').$class))
            {
                $name = config_item('subclass_prefix').$class;
            }
            else
            {
                $name = $class;
            }
        }
        else
        {
            $name = $prefix.$class;
        }

        // Is the class name valid?
        if ( ! class_exists($name))
        {
            log_message('error', "Non-existent class: ".$name);
            show_error("Non-existent class: ".$class);
        }

        // Set the variable name we will assign the class to
        // Was a custom class name supplied?  If so we'll use it
        $class = strtolower($class);

        if (is_null($object_name))
        {
            $classvar = ( ! isset($this->_varmap[$class])) ? $class : $this->_varmap[$class];
        }
        else
        {
            $classvar = $object_name;
        }

        // Save the class name and object name
        $this->_classes[$class] = $classvar;

        // Instantiate the class
        $CI =& get_instance();
        if ($config !== NULL)
        {
            $CI->$classvar = new $name($config);
        }
        else
        {
            $CI->$classvar = new $name;
        }
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
            return FALSE;
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
            if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
            {
                $this->$type($autoload[$type]);
            }
        }

        // A little tweak to remain backward compatible
        // The $autoload['core'] item was deprecated
        if ( ! isset($autoload['libraries']) AND isset($autoload['core']))
        {
            $autoload['libraries'] = $autoload['core'];
        }

        // Load libraries
        if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
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
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param   object
     * @return  array
     */
    protected function _object_to_array($object)
    {
        return (is_object($object)) ? get_object_vars($object) : $object;
    }

    // --------------------------------------------------------------------

    /**
     * Get a reference to a specific library or model
     *
     * @param   string
     * @return  bool
     */
    protected function &_get_component($component)
    {
        $CI =& get_instance();
        return $CI->$component;
    }

    // --------------------------------------------------------------------

    /**
     * Prep filename
     *
     * This function preps the name of various items to make loading them more reliable.
     *
     * @param   mixed
     * @param   string
     * @return  array
     */
    protected function _prep_filename($filename, $extension)
    {
        if ( ! is_array($filename))
        {
            return array(strtolower(str_replace('.php', '', str_replace($extension, '', $filename)).$extension));
        }
        else
        {
            foreach ($filename as $key => $val)
            {
                $filename[$key] = strtolower(str_replace('.php', '', str_replace($extension, '', $val)).$extension);
            }

            return $filename;
        }
    }
}