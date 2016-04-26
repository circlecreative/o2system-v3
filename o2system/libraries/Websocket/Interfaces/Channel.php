<?php

namespace O2System\Libraries\Websocket\Interfaces;

/**
 * WebSocket Server Application
 *
 * @author Nico Kaiser <nico@kaiser.me>
 */
abstract class Channel
{
	protected static $instances = array();

	/**
	 * Singleton
	 */
	protected function __construct() { }

	final private function __clone() { }

	final public static function getInstance()
	{
		$calledClassName = get_called_class();
		if ( ! isset( self::$instances[ $calledClassName ] ) )
		{
			self::$instances[ $calledClassName ] = new $calledClassName();
		}

		return self::$instances[ $calledClassName ];
	}

	abstract public function on_connect( $connection );

	abstract public function on_disconnect( $connection );

	abstract public function on_data( $data, $client );

	// Common methods:

	protected function _decodeData( $data )
	{
		$decodedData = json_decode( $data, TRUE );

		if ( $decodedData === NULL )
		{
			return FALSE;
		}

		if ( isset( $decodedData[ 'action' ], $decodedData[ 'data' ] ) === FALSE )
		{
			return FALSE;
		}

		return $decodedData;
	}

	protected function _encodeData( $action, $data )
	{
		if ( empty( $action ) )
		{
			return FALSE;
		}

		$payload = array(
			'action' => $action,
			'data'   => $data,
		);

		return json_encode( $payload );
	}

	public function set_info($serverInfo)
	{
		if(is_array($serverInfo))
		{
			$this->_serverInfo = $serverInfo;
			return true;
		}
		return false;
	}
}