<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 8:31 PM
 */

namespace O2System\Core;


/**
 * Class Language
 *
 * @package O2System\Glob
 */
class Language extends SPL\ArrayObject
{
	/**
	 * Active Language
	 *
	 * @type string
	 */
	public $active;

	/**
	 * List of loaded language files
	 *
	 * @access  protected
	 *
	 * @var array
	 */
	protected $isLoaded = [ ];

	// ------------------------------------------------------------------------

	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();

		$this->active = \O2System::$config->language;

		\O2System::$load->helper( 'language' );
		$this->load( [ DIR_SYSTEM, DIR_APPLICATIONS ] );
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Active
	 *
	 * @param   string $code
	 *
	 * @access  public
	 */
	public function setActive( $code )
	{
		$this->active = $code;
	}

	// ------------------------------------------------------------------------

	/**
	 * Load a language file
	 *
	 * @access    public
	 *
	 * @param    mixed     the name of the language file to be loaded. Can be an array
	 * @param    string    the language (english, etc.)
	 * @param    bool      return loaded array of translations
	 * @param    bool      add suffix to $langfile
	 * @param    string    alternative path to look for language file
	 *
	 * @return    mixed
	 */
	public function load( $file, $ideom = NULL )
	{
		if ( is_array( $file ) )
		{
			foreach ( $file as $item )
			{
				$this->load( $item, $ideom );
			}

			return $this;
		}

		$ideom = is_null( $ideom ) ? $this->active : $ideom;

		if ( is_file( $file ) )
		{
			$this->_loadFile( $file );
		}
		else
		{
			$file = strtolower( $file );

			foreach ( \O2System::$load->getLanguagesDirectories() as $directory )
			{
				$filePaths = [
					$directory . $ideom . DIRECTORY_SEPARATOR . $file . '.ini',
					$directory . $file . '_' . $ideom . '.ini',
					$directory . $file . '-' . $ideom . '.ini',
					$directory . $file . '.ini',
				];

				foreach ( $filePaths as $filePath )
				{
					$this->_loadFile( $filePath );
				}
			}
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Load File
	 *
	 * @param $filepath
	 */
	protected function _loadFile( $filepath )
	{
		if ( is_file( $filepath ) AND ! in_array( $filepath, $this->isLoaded ) )
		{
			$lines = parse_ini_file( $filepath, TRUE, INI_SCANNER_RAW );

			if ( ! empty( $lines ) )
			{

				$this->isLoaded[ pathinfo( $filepath, PATHINFO_FILENAME ) ] = $filepath;

				foreach ( $lines as $key => $line )
				{
					$this->offsetSet( $key, $line );
				}
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch a single line of text from the language array
	 *
	 * @access    public
	 *
	 * @param    string $line the language line
	 *
	 * @return    string
	 */
	public function line( $line = '', array $args = [ ] )
	{
		$line = strtoupper( $line );

		if ( empty( $args ) )
		{
			return ( $line == '' || ! $this->offsetExists( $line ) ) ? NULL : $this->offsetGet( $line );
		}
		else
		{
			$line = ( $line == '' || ! $this->offsetExists( $line ) ) ? NULL : $this->offsetGet( $line );
			array_unshift( $args, $line );

			return @call_user_func_array( 'sprintf', $args );
		}
	}
}