<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 11:12 PM
 */

namespace O2System\Core;


use O2System\Core;

class Environment
{
	const DEVELOPMENT = 'DEVELOPMENT';
	const TESTING     = 'TESTING';
	const PRODUCTION  = 'PRODUCTION';

	protected $stage = 'DEVELOPMENT';
	protected $vcs   = 'SVN';

	protected $debugStage       = 'DEVELOPMENT';
	protected $debugIpAddresses = [ '127.0.0.1', '::1' ];

	/**
	 * Exception Handler
	 *
	 * @type Handler
	 */
	protected $exceptionHandler;

	/**
	 * Version Control Handler
	 *
	 * @type HandlerInterface
	 */
	protected $vcsHandler;

	/**
	 * Paths Registries
	 *
	 * @type array
	 */
	protected $paths = [

	];

	/**
	 * ENV constructor.
	 */
	public function __construct()
	{
		$this->storage =& $_ENV;

		$this->stage = isset( $_ENV[ 'STAGE' ] ) ? strtoupper( $_ENV[ 'STAGE' ] ) : 'DEVELOPMENT';
		$this->vcs   = isset( $_ENV[ 'VCS' ] ) ? strtoupper( $_ENV[ 'VCS' ] ) : 'NONE';

		$this->exceptionHandler = new Core\Environment\Handlers\Exception();
		$this->exceptionHandler->registerHandler();

		$vcs_handler_class = 'O2System\Core\Environment\Handlers\VCS\\' . $this->vcs;

		if ( class_exists( $vcs_handler_class ) )
		{
			$this->vcsHandler = new $vcs_handler_class();
		}

		$this->setStage( ENVIRONMENT );
		$this->setDebugIpAddresses( \O2System::$config[ 'debug_ips' ] );
	}

	public function setStage( $stage )
	{
		$this->stage = strtoupper( $stage );
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Debug Stage
	 *
	 * @return string
	 */
	public function getDebugStage()
	{
		return $this->debugStage;
	}

	/**
	 * Set Debug Stage
	 *
	 * @param string $stage UPPERCASE stage DEVELOPMENT|TESTING|PRODUCTION
	 */
	public function setDebugStage( $stage )
	{
		switch ( strtoupper( $stage ) )
		{
			default:
			case 'DEVELOPMENT':
				error_reporting( -1 );
				ini_set( 'display_errors', 1 );
				$this->debugStage = 'DEVELOPMENT';
				break;
			case 'TESTING':
			case 'PRODUCTION':
				ini_set( 'display_errors', 0 );
				error_reporting( E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED );
				$this->debugStage = strtoupper( $stage );
				break;
		}
	}

	// ------------------------------------------------------------------------

	public function isDebugStage( $stage = 'DEVELOPMENT' )
	{
		$stage = strtoupper( $stage );

		return (bool) ( $stage === $this->debugStage );
	}

	public function getDebugIpAddresses()
	{
		return $this->debugIpAddresses;
	}

	/**
	 * Set Debug IP Addresses
	 *
	 * @param string|array $ip_address List of IP Addresses (v4)
	 */
	public function setDebugIpAddresses( $ip_address )
	{
		if ( is_string( $ip_address ) )
		{
			$ip_address = array_map( 'trim', explode( ',', $ip_address ) );
		}

		$this->debugIpAddresses = array_merge( $this->debugIpAddresses, array_filter( $ip_address ) );
		$this->debugIpAddresses = array_unique( $this->debugIpAddresses );
	}

	public function debugStageEventListener()
	{
		if ( in_array( \O2System::$request->getIpAddress(), $this->debugIpAddresses ) )
		{
			$this->setDebugStage( 'DEVELOPMENT' );

			// Register Version Control Handler
			$this->vcsHandler->requestEventListener();
		}

		call_user_func( [ $this, '_' . strtolower( $this->debugStage ) . 'StageEventRunner' ] );
	}

	public function registerLibrary( $class_name )
	{
		$reflection   = new \ReflectionClass( $class_name );
		$library_path = dirname( $reflection->getFileName() ) . DIRECTORY_SEPARATOR;

		$this->exceptionHandler->addPath( $library_path );

		\O2System::$language->addPath( $library_path );
		\O2System::$language->load( underscore( get_class_name( $class_name ) ) );
	}

	protected function _developmentStageEventRunner()
	{
		if ( isset( $_REQUEST[ 'PHP_INFO' ] ) )
		{
			echo phpinfo();
			exit( 0 );
		}
	}
}