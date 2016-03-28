<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/12/2016
 * Time: 12:32 PM
 */

namespace O2System\Glob\Interfaces;


abstract class ModelInterface
{
	protected static $_instance;

	/**
	 * Model Table
	 *
	 * @access  public
	 * @type    string
	 */
	public $table = NULL;

	/**
	 * Model Table Fields
	 *
	 * @access  public
	 * @type    array
	 */
	public $fields = array();

	/**
	 * Model Table Primary Key
	 *
	 * @access  public
	 * @type    string
	 */
	public $primary_key = 'id';

	/**
	 * Model Table Primary Keys
	 *
	 * @access  public
	 * @type    array
	 */
	public $primary_keys = array();

	/**
	 * List of library valid sub models
	 *
	 * @access  protected
	 *
	 * @type    array   driver classes list
	 */
	protected $_valid_sub_models = array();

	/**
	 * List of Before Process Methods
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_before_process = array();

	/**
	 * List of After Process Methods
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_after_process = array();

	// ------------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		if ( class_exists( 'O2System', FALSE ) )
		{
			$this->db = \O2System::Load()->database();
		}

		// Fetch Sub-Models
		$this->_fetchSubModels();

		// Fetch Before and After Process
		$this->_fetchProcessMethods();
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Drivers Path
	 *
	 * @access  protected
	 * @return  string
	 */
	final protected function _fetchSubModels()
	{
		$reflection = new \ReflectionClass( get_called_class() );
		$filepath = $reflection->getFileName();

		if ( strpos( dirname( $filepath ), 'core' ) !== FALSE )
		{
			$sub_model_path = str_replace( 'core', 'models', dirname( $filepath ) ) . DIRECTORY_SEPARATOR;
		}
		elseif ( strpos( dirname( $filepath ), 'models' ) !== FALSE )
		{
			$sub_model_path = dirname( $filepath ) . DIRECTORY_SEPARATOR . strtolower( pathinfo( $filepath, PATHINFO_FILENAME ) ) . DIRECTORY_SEPARATOR;
		}

		foreach ( glob( $sub_model_path . '*.php' ) as $filepath )
		{
			$this->_valid_sub_models[ strtolower( pathinfo( $filepath, PATHINFO_FILENAME ) ) ] = $filepath;
		}
	}

	// ------------------------------------------------------------------------

	final protected function _fetchProcessMethods()
	{
		// Build the reflection to determine validation methods
		$reflection = new \ReflectionClass( get_called_class() );

		foreach ( $reflection->getMethods() as $method )
		{
			if ( strpos( $method->name, '_before_process_' ) !== FALSE )
			{
				$this->_before_process[] = $method->name;
			}
			elseif ( strpos( $method->name, '_after_process_' ) !== FALSE )
			{
				$this->_after_process[] = $method->name;
			}
		}
	}

	// ------------------------------------------------------------------------

	final public static function instance()
	{
		if ( ! isset( static::$_instance ) )
		{
			static::$_instance = new static;
		}

		return static::$_instance;
	}

	// ------------------------------------------------------------------------

	public function __get( $property )
	{
		if ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}
		elseif ( array_key_exists( $property, $this->_valid_sub_models ) )
		{
			// Try to load the sub model
			return $this->_loadSubModel( $property );
		}
		elseif ( class_exists( 'O2System', FALSE ) )
		{
			return \O2System::instance()->__get( $property );
		}

		return \O2System\Glob::instance()->__get( $property );
	}

	// ------------------------------------------------------------------------

	/**
	 * Load driver
	 *
	 * Separate load_driver call to support explicit driver load by library or user
	 *
	 * @param   string $driver driver class name (lowercase)
	 *
	 * @return    object    Driver class
	 */
	final protected function _loadSubModel( $model )
	{
		if ( is_file( $this->_valid_sub_models[ $model ] ) )
		{
			require_once( $this->_valid_sub_models[ $model ] );

			$class_name = '\\' . get_called_class() . '\\' . ucfirst( $model );
			$class_name = str_replace( '\Core\\Model', '\Models', $class_name );

			if ( class_exists( $class_name, FALSE ) )
			{
				$this->{$model} = new $class_name();
			}
		}

		return $this->{$model};
	}

	// ------------------------------------------------------------------------

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @access  private
	 * @return  void
	 */
	private function __wakeup()
	{

	}

	// ------------------------------------------------------------------------

	public function __call( $method, $args = array() )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( array( $this, $method ), $args );
		}
		elseif ( class_exists( 'O2System', FALSE ) )
		{
			return \O2System::instance()->__call( $method, $args );
		}

		return \O2System\Glob::instance()->__call( $method, $args );

	}

	// ------------------------------------------------------------------------

	final public static function __callStatic( $method, $args = array() )
	{
		return static::instance()->__call( $method, $args );
	}
}