<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/12/2016
 * Time: 12:32 PM
 */

namespace O2System\Core;

abstract class Model
{
	/**
	 * Model Instance
	 *
	 * @type Model
	 */
	protected static $instance;

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
	public $fields = [ ];

	/**
	 * Model Table Primary Key
	 *
	 * @access  public
	 * @type    string
	 */
	public $primaryKey = 'id';

	/**
	 * Model Table Primary Keys
	 *
	 * @access  public
	 * @type    array
	 */
	public $primaryKeys = [ ];

	/**
	 * List of library valid sub models
	 *
	 * @access  protected
	 *
	 * @type    array   driver classes list
	 */
	protected $validSubModels = [ ];

	/**
	 * List of Before Process Methods
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $beforeProcess = [ ];

	/**
	 * List of After Process Methods
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $afterProcess = [ ];

	// ------------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		static::$instance =& $this;

		// Fetch Sub-Models
		$this->_fetchSubModels();

		// Fetch Before and After Process
		$this->_fetchProcessMethods();

		\O2System::$log->debug( 'LOG_DEBUG_CLASS_INITIALIZED', [ get_called_class() ] );
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

		// Define called model class filepath
		$filePath = $reflection->getFileName();

		// Define filename for used as subdirectory name
		$filename = pathinfo( $filePath, PATHINFO_FILENAME );

		// Get model class directory name
		$dirName = dirname( $filePath ) . DIRECTORY_SEPARATOR;

		if ( $filename === 'Model' )
		{
			// Change model class directory name to parent directory name
			$dirName = dirname( $dirName ) . DIRECTORY_SEPARATOR;

			// Since we're working in multiple os, we must validate folder name based on sensitive folder naming
			foreach ( [ 'Models', 'models' ] as $subDirName )
			{
				if ( is_dir( $dirName . $subDirName . DIRECTORY_SEPARATOR ) )
				{
					$dirName = $dirName . $subDirName . DIRECTORY_SEPARATOR;
					break;
				}
			}
		}

		// Since we're working in multiple os, we must validate folder name based on sensitive folder naming
		foreach ( [ strtolower( $filename ), prepare_class_name( $filename ) ] as $subDirName )
		{
			if ( is_dir( $dirName . $subDirName . DIRECTORY_SEPARATOR ) )
			{
				$subModelPath = $dirName . $subDirName . DIRECTORY_SEPARATOR;
				break;
			}
		}

		$subModelPath = isset( $subModelPath ) ? $subModelPath : $dirName;

		\O2System::$load->addNamespace( $reflection->name, $subModelPath );

		foreach ( glob( $subModelPath . '*.php' ) as $filepath )
		{
			$this->validSubModels[ strtolower( pathinfo( $filepath, PATHINFO_FILENAME ) ) ] = $filepath;
		}
	}

	// ------------------------------------------------------------------------

	final protected function _fetchProcessMethods()
	{
		// Build the reflection to determine validation methods
		$reflection = new \ReflectionClass( get_called_class() );

		foreach ( $reflection->getMethods() as $method )
		{
			if ( $method->name !== '_beforeProcess' AND strpos( $method->name, '_beforeProcess' ) !== FALSE )
			{
				$this->beforeProcess[] = $method->name;
			}
			elseif ( $method->name !== '_afterProcess' AND strpos( $method->name, '_afterProcess' ) !== FALSE )
			{
				$this->afterProcess[] = $method->name;
			}
		}
	}

	// ------------------------------------------------------------------------

	final public static function __callStatic( $method, $args = [ ] )
	{
		return static::instance()->__call( $method, $args );
	}

	// ------------------------------------------------------------------------

	public function __call( $method, $args = [ ] )
	{
		if ( method_exists( $this, camelcase( $method ) ) )
		{
			return call_user_func_array( [ $this, camelcase( $method ) ], $args );
		}
		elseif ( method_exists( $this, 'get' . studlycapcase( $method ) ) )
		{
			return call_user_func_array( [ $this, 'get' . studlycapcase( $method ) ], $args );
		}

		return \O2System::instance()->__call( $method, $args );
	}

	// ------------------------------------------------------------------------

	final public static function instance()
	{
		if ( is_null( static::$instance ) )
		{
			static::$instance = new static;
		}

		return static::$instance;
	}

	// ------------------------------------------------------------------------

	public function __get( $property )
	{
		if ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}
		elseif ( array_key_exists( $property, $this->validSubModels ) )
		{
			// Try to load the sub model
			return $this->_loadSubModel( $property );
		}

		return \O2System::instance()->__get( $property );
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
		if ( is_file( $this->validSubModels[ $model ] ) )
		{
			//require_once( $this->validSubModels[ $model ] );

			$class_name = '\\' . get_called_class() . '\\' . ucfirst( $model );
			$class_name = str_replace( '\Core\\Model', '\Models', $class_name );

			if ( class_exists( $class_name ) )
			{
				$this->{$model} = new $class_name();
			}
		}

		return $this->{$model};
	}

	// ------------------------------------------------------------------------

	/**
	 * Insert Data
	 *
	 * Method to input data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   string $table Table Name
	 * @param   array  $data  Array of Input Data
	 *
	 * @return mixed
	 */
	final public function insert( $row, $table = NULL, $return = 'lastInsertId' )
	{
		$table = isset( $table ) ? $table : $this->table;

		$row = $this->_beforeProcess( $row, $table );

		if ( FALSE !== ( $return = $this->db->insert( $table, $row, $return ) ) )
		{
			$report = $this->_afterProcess();

			if ( empty( $report ) )
			{
				return $return;
			}

			return $report;
		}

		return FALSE;
	}

	/**
	 * Before Process
	 *
	 * Process row data before insert or update
	 *
	 * @param $row
	 * @param $table
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function _beforeProcess( $row, $table )
	{
		if ( ! empty( $this->beforeProcess ) )
		{
			foreach ( $this->beforeProcess as $processMethod )
			{
				$row = $this->{$processMethod}( $row, $table );
			}
		}

		return $row;
	}
	// ------------------------------------------------------------------------

	/**
	 * After Process
	 *
	 * Runs all after process method actions
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function _afterProcess()
	{
		$report = [ ];

		if ( ! empty( $this->afterProcess ) )
		{
			foreach ( $this->afterProcess as $processMethod )
			{
				$report[ $processMethod ] = $this->{$processMethod}();
			}
		}

		return $report;
	}
	// ------------------------------------------------------------------------

	/**
	 * Update Data
	 *
	 * Method to update data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   string $table Table Name
	 * @param   array  $row   Array of Update Data
	 *
	 * @return mixed
	 */
	final public function update( $row = [ ], $table = NULL, $return = 'affectedRows' )
	{
		$table      = isset( $table ) ? $table : $this->table;
		$primaryKey = isset( $this->primaryKey ) ? $this->primaryKey : 'id';

		if ( empty( $this->primaryKeys ) )
		{
			$this->db->where( $primaryKey, $row[ $primaryKey ] );
		}
		else
		{
			foreach ( $this->primaryKeys as $primaryKey )
			{
				$this->db->where( $primaryKey, $row[ $primaryKey ] );
			}
		}

		// Reset Primary Keys
		$this->primaryKey  = 'id';
		$this->primaryKeys = [ ];

		$row = $this->_beforeProcess( $row, $table );

		if ( FALSE !== ( $return = $this->db->update( $table, $row, [], $return ) ) )
		{
			$report = $this->_afterProcess();

			if ( empty( $report ) )
			{
				return $return;
			}

			return $report;
		}

		return FALSE;
	}
	// ------------------------------------------------------------------------

	/**
	 * Update Data
	 *
	 * Method to update data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   string $table Table Name
	 * @param   array  $row   Array of Update Data
	 *
	 * @return mixed
	 */
	final public function softDelete( $id, $table = NULL, $return = 'BOOL' )
	{
		$table      = isset( $table ) ? $table : $this->table;
		$primaryKey = isset( $this->primaryKey ) ? $this->primaryKey : 'id';

		$row[ 'record_status' ] = 'DELETE';

		if ( empty( $this->primaryKeys ) )
		{
			$this->db->where( $primaryKey, $id );
			$row[ $primaryKey ] = $id;
		}
		elseif ( is_array( $id ) )
		{
			foreach ( $this->primaryKeys as $primaryKey )
			{
				$this->db->where( $primaryKey, $row[ $primaryKey ] );
				$row[ $primaryKey ] = $id[ $primaryKey ];
			}
		}
		else
		{
			foreach ( $this->primaryKeys as $primaryKey )
			{
				$this->db->where( $primaryKey, $row[ $primaryKey ] );
			}

			$row[ reset( $this->primaryKeys ) ] = $id;
		}

		// Reset Primary Keys
		$this->primaryKey  = 'id';
		$this->primaryKeys = [ ];

		$row = $this->_beforeProcess( $row, $table );

		if ( FALSE !== ( $return = $this->db->update( $table, $row, [], $return ) ) )
		{
			$report = $this->_afterProcess();

			if ( empty( $report ) )
			{
				return $return;
			}

			return $report;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete Data
	 *
	 * Method to delete data with all childs and file
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   string $table Table Name
	 * @param   int    $id    Data ID
	 * @param   bool   $force Force Delete
	 *
	 * @return mixed
	 */
	public function delete( $id, $table = NULL, $force = FALSE, $return = 'BOOL' )
	{
		if ( ( isset( $table ) AND is_bool( $table ) ) OR ! isset( $table ) )
		{
			$table = $this->table;
		}

		if ( isset( $this->_adjacency_enabled ) )
		{
			if ( $this->hasChildren( $id, $table ) )
			{
				if ( $force === TRUE )
				{
					if ( $childrens = $this->getChildren( $id, $table ) )
					{
						foreach ( $childrens as $children )
						{
							$report[ $children->id ] = $this->delete( $children->id, $force, $table );
						}
					}
				}
			}
		}

		// Recursive Search File
		$fields = [ 'file', 'document', 'image', 'picture', 'cover', 'avatar', 'photo', 'video' ];

		foreach ( $fields as $field )
		{
			if ( $this->db->fieldExists( $field, $table ) )
			{
				$primaryKey = isset( $this->primaryKey ) ? $this->primaryKey : 'id';

				if ( empty( $this->primaryKeys ) )
				{
					$this->db->where( $primaryKey, $id );
				}
				elseif ( is_array( $id ) )
				{
					foreach ( $this->primaryKeys as $primaryKey )
					{
						$this->db->where( $primaryKey, $id[ $primaryKey ] );
					}
				}
				else
				{
					$this->db->where( reset( $this->primaryKeys ), $id );
				}

				$result = $this->db->select( $field )->limit( 1 )->get( $table );

				if ( $result->numRows() > 0 )
				{
					if ( ! empty( $result->first()->{$field} ) )
					{
						$directory = new \RecursiveDirectoryIterator( APPSPATH );
						$iterator  = new \RecursiveIteratorIterator( $directory );
						$results   = new \RegexIterator( $iterator, '/' . $result->first()->{$field} . '/i', \RecursiveRegexIterator::GET_MATCH );

						foreach ( $results as $file )
						{
							if ( is_array( $file ) )
							{
								foreach ( $file as $filepath )
								{
									@unlink( $filepath );
								}
							}
						}
					}
				}
			}
		}

		$primaryKey = isset( $this->primaryKey ) ? $this->primaryKey : 'id';

		if ( empty( $this->primaryKeys ) )
		{
			$this->db->where( $primaryKey, $id );
		}
		elseif ( is_array( $id ) )
		{
			foreach ( $this->primaryKeys as $primaryKey )
			{
				$this->db->where( $primaryKey, $id[ $primaryKey ] );
			}
		}
		else
		{
			$this->db->where( reset( $this->primaryKeys ), $id );
		}

		// Reset Primary Keys
		$this->primaryKey  = 'id';
		$this->primaryKeys = [ ];

		if ( FALSE !== ( $return = $this->db->delete( $table, $return ) ) )
		{
			$report = $this->_afterProcess();

			if ( empty( $report ) )
			{
				return $return;
			}

			return $report;
		}

		return FALSE;
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
}