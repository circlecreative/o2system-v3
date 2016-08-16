<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 1:07 AM
 */

namespace O2System\Core\HTTP;


class Protocol
{
	/**
	 * Protocol version
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * List of valid protocol versions
	 *
	 * @var array
	 */
	protected $validProtocolVersions = [ '1.0', '1.1', '2' ];

	/**
	 * Sets the HTTP protocol version.
	 *
	 * @param string $version
	 *
	 * @return Protocol
	 */
	public function setVersion( $version )
	{
		if ( ! is_numeric( $version ) )
		{
			$version = substr( $version, strpos( $version, '/' ) + 1 );
		}

		if ( ! in_array( $version, $this->validProtocolVersions ) )
		{
			throw new \InvalidArgumentException( 'Invalid HTTP Protocol Version. Must be one of: ' . implode( ', ', $this->validProtocolVersions ) );
		}

		$this->version = $version;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the HTTP Protocol Version.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	//--------------------------------------------------------------------
}