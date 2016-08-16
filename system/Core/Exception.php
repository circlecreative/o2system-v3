<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 8:25 PM
 */

namespace O2System\Core;


use O2System\Core;

class Exception extends \Exception
{
	/**
	 * Previous Exception
	 *
	 * @type \Exception|null
	 */
	protected $previous = NULL;

	/**
	 * Exception Header
	 *
	 * @type string|null
	 */
	public $header = NULL;

	/**
	 * Exception Description
	 *
	 * @type string|null
	 */
	public $description = NULL;

	/**
	 * Exception Arguments
	 *
	 * @type string|null
	 */
	protected $args = NULL;

	/**
	 * Exception View File
	 *
	 * @type string|null
	 */
	protected $viewFile = NULL;

	/**
	 * Exception View Path
	 *
	 * @type string|null
	 */
	protected $viewPath = NULL;

	// ------------------------------------------------------------------------

	public function __construct( $message, $code = 0, array $args = [ ] )
	{
		\O2System::$language->load( 'exception' );
		$lang_message      = \O2System::$language->line( $message, $args );
		$this->header      = \O2System::$language->line( 'E_HEADER_' . get_class_name( $this ) );
		$this->description = \O2System::$language->line( 'E_DESCRIPTION_' . get_class_name( $this ) );

		if ( empty( $this->header ) )
		{
			$this->header = get_class( $this );
		}

		$message = empty( $lang_message ) ? $message : $lang_message;

		if ( isset( $this->args ) )
		{
			$args = array_values( $this->args );
			array_unshift( $args, $message );

			$message = call_user_func_array( 'sprintf', $args );
		}

		$message = empty( $message ) ? '(null)' : $message;

		parent::__construct( $message, $code );

		$this->_setPath();
	}

	// ------------------------------------------------------------------------

	public function setPrevious( \Exception $exception )
	{
		$this->previous = $exception;
	}

	protected function _setPath( $path = NULL )
	{
		if ( empty( $path ) )
		{
			$filepath = ( new \ReflectionClass( get_called_class() ) )->getFileName();

			$path = dirname( $filepath ) . DIRECTORY_SEPARATOR;

			foreach ( [ 'Views', 'views' ] as $view_path )
			{
				if ( is_dir( $path . $view_path ) )
				{
					$this->viewPath = $path . $view_path . DIRECTORY_SEPARATOR;
					break;
				}
			}
		}
	}

	public function getViewPath()
	{
		return $this->viewPath;
	}

	public function getViewFile()
	{
		return $this->viewFile;
	}
}