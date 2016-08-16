<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 28-Jul-16
 * Time: 2:18 AM
 */

namespace O2System\Session\Interfaces;


use O2System\Session\Config;

abstract class HandlerInterface implements \SessionHandlerInterface
{
	/**
	 * Platform Name
	 *
	 * @access  protected
	 * @var string
	 */
	protected $platform;

	/**
	 * Handler Configuration
	 *
	 * @var \O2System\Session\Config
	 */
	protected $config;

	/**
	 * Key Prefix
	 *
	 * @var string
	 */
	protected $prefixKey = 'o2system_session:';

	/**
	 * Lock key
	 *
	 * @var    string
	 */
	protected $lockKey;

	/**
	 * The Data fingerprint.
	 *
	 * @var bool
	 */
	protected $fingerprint;

	/**
	 * Lock placeholder.
	 *
	 * @var mixed
	 */
	protected $isLocked = FALSE;

	/**
	 * Current session ID
	 *
	 * @var type
	 */
	protected $sessionId;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param Config $config
	 */
	public function __construct( Config $config )
	{
		$this->config     = $config;
		$config[ 'name' ] = isset( $config[ 'name' ] ) ? $config[ 'name' ] : 'o2system_session';

		$is_match_ip = isset( $config[ 'match' ]->ip ) ? $config[ 'match' ]->ip : FALSE;

		$this->prefixKey = $config[ 'name' ] . ':' . ( $is_match_ip ? $_SERVER[ 'REMOTE_ADDR' ] . ':' : '' );
	}

	//--------------------------------------------------------------------

	/**
	 * Get Current Platform
	 *
	 * @return string
	 */
	public function getPlatform()
	{
		return $this->platform;
	}

	/**
	 * Internal method to force removal of a cookie by the client
	 * when session_destroy() is called.
	 *
	 * @return bool
	 */
	protected function _destroyCookie()
	{
		return setcookie(
			$this->config[ 'name' ],
			NULL,
			1,
			$this->config[ 'cookie' ]->path,
			$this->config[ 'cookie' ]->domain,
			$this->config[ 'cookie' ]->secure,
			TRUE
		);
	}

	//--------------------------------------------------------------------

	/**
	 * A dummy method allowing drivers with no locking functionality
	 * (databases other than PostgreSQL and MySQL) to act as if they
	 * do acquire a lock.
	 *
	 * @param string $session_id
	 *
	 * @return bool
	 */
	protected function _lockSession( $session_id )
	{
		$this->isLocked = TRUE;

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Releases the lock, if any.
	 *
	 * @return bool
	 */
	protected function _lockRelease()
	{
		$this->isLocked = FALSE;

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Is Supported
	 *
	 * Determines if the driver is supported on this system.
	 *
	 * @access  public
	 * @return  boolean
	 */
	abstract public function isSupported();
}