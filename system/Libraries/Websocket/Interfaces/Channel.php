<?php

namespace O2System\Libraries\Websocket\Interfaces;

/**
 * WebSocket Server Application
 *
 * @author Nico Kaiser <nico@kaiser.me>
 */
abstract class Channel
{
	protected static $instances    = [ ];
	protected        $_server_info = NULL;

	/**
	 * Singleton
	 */
	protected function __construct()
	{
	}

	final private function __clone()
	{
	}

	final public static function instance()
	{
		$called_class = get_called_class();

		if ( ! isset( self::$instances[ $called_class ] ) )
		{
			self::$instances[ $called_class ] = new $called_class();
		}

		return self::$instances[ $called_class ];
	}

	abstract public function onConnect( $connection );

	abstract public function onDisconnect( $connection );

	abstract public function onData( $data, $client );

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

		$payload = [
			'action' => $action,
			'data'   => $data,
		];

		return json_encode( $payload );
	}

	public function setServerInfo( $server_info )
	{
		if ( is_array( $server_info ) )
		{
			$this->_server_info = $server_info;

			return TRUE;
		}

		return FALSE;
	}
}