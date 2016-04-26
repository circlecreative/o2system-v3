<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/9/2016
 * Time: 4:06 AM
 */

namespace O2System\Glob\Interfaces;


class DriverInterface
{
	/**
	 * Driver Class Configuration
	 *
	 * @access protected
	 */
	protected $_config = array();

	/**
	 * Instance of the library class
	 *
	 * @type object
	 */
	protected $_library;

	/**
	 * Array of methods and properties for the parent class(es)
	 *
	 * @static
	 * @var    array
	 */
	protected static $_reflections = array();

	protected $_errors = array();

	// ------------------------------------------------------------------------

	public function setLibrary( $library )
	{
		$this->_library =& $library;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Override
	 * Handles reading of the parent driver or library's properties
	 *
	 * @access      public
	 * @static      static class method
	 * @final       this method can't be overwritten
	 *
	 * @param   string $property property name
	 *
	 * @return mixed
	 */
	public function __get( $property )
	{
		if ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}

		return $this->_library->__get( $property );
	}
	// --------------------------------------------------------------------

	/**
	 * Set Driver Config
	 *
	 * @access   public
	 * @final    this method can't be overwritten
	 *
	 * @param array $config
	 *
	 * @return $this
	 */
	final public function setConfig( array $config )
	{
		$this->_config = $config;

		if ( method_exists( $this, 'initialize' ) )
		{
			$this->initialize();
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Driver Config Item
	 *
	 * @access  public
	 * @final   this method can't be overwritten
	 *
	 * @param string|null $item Config item index name
	 *
	 * @return array|null
	 */
	final public function getConfig( $item = NULL, $offset = NULL )
	{
		if( empty( $item ) )
		{
			return $this->_config;
		}
		elseif( isset( $item ) )
		{
			if( isset( $offset ) )
			{
				if( isset( $this->_config[ $item ][ $offset ] ) )
				{
					return $this->_config[ $item ][ $offset ];
				}
			}
			elseif( isset( $this->_config[ $item ] ) )
			{
				return $this->_config[ $item ];
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Throw Error
	 *
	 * @param   string $error Error Message
	 * @param   int    $code  Error Code
	 *
	 * @access  public
	 * @return  bool
	 */
	final public function setError( $error, $code = 0, array $args = array() )
	{
		if ( class_exists( 'O2System', FALSE ) )
		{
			$lang_error = \O2System::$language->line( $error );
		}
		else
		{
			$lang_error = \O2System\Glob::$language->line( $error );
		}

		$error = empty( $lang_error ) ? $error : $lang_error;

		if ( ! empty( $args ) )
		{
			$args = array_values( $args );
			array_unshift( $args, $error );

			$error = call_user_func_array( 'sprintf', $args );
		}

		$error = empty( $error ) ? '(null)' : $error;

		$code = $code == 0 ? ( count( $this->_errors ) + 1 ) : $code;

		$this->_errors[ $code ] = $error;

		return FALSE;
	}
}