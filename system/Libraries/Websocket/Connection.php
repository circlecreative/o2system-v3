<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 2/26/2016
 * Time: 10:33 AM
 */

namespace O2System\Libraries\Websocket;

class Connection
{
	/**
	 * Server Resource Object
	 *
	 * @type Server
	 */
	protected $_server;

	/**
	 * Connection Socket
	 *
	 * @type resource
	 */
	public $socket;

	/**
	 * Connection ID
	 *
	 * @type    string
	 */
	public $id_connection = NULL;

	/**
	 * Connection IP Address
	 *
	 * @type    string
	 */
	public $ip_address;

	/**
	 * Connection Port
	 *
	 * @type    int
	 */
	public $port;

	/**
	 * Connection Channel
	 *
	 * @type    string
	 */
	public $channel;

	/**
	 * Connection Handshake Status
	 *
	 * @type bool
	 */
	protected $_is_handshaked = FALSE;

	/**
	 * Connection Data Status
	 *
	 * @type bool
	 */
	protected $_is_waiting_data = FALSE;

	/**
	 * Connection Buffering Data
	 *
	 * @type string
	 */
	protected $_buffering_data = '';

	public function __construct( Server $server, $stream_socket )
	{
		$this->_server = $server;
		$this->socket  = $stream_socket;

		// set some client-information:
		$socket_name      = stream_socket_get_name( $stream_socket, TRUE );
		$x_socket_name    = explode( ':', $socket_name );
		$this->ip_address = $x_socket_name[ 0 ];
		$this->port       = $x_socket_name[ 1 ];

		$this->id_connection = md5( $this->ip_address . $this->port . spl_object_hash( $this ) );

		$this->sendMessage( 'Connected' );
	}

	protected function _handshake( $data )
	{
		$this->sendMessage( 'Performing handshake' );
		$lines = preg_split( "/\r\n/", $data );

		// check for valid http-header:
		if ( ! preg_match( '/\AGET (\S+) HTTP\/1.1\z/', $lines[ 0 ], $matches ) )
		{
			$this->sendMessage( 'Invalid request: ' . $lines[ 0 ] );
			$this->sendHttpResponse( 400 );
			stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );

			return FALSE;
		}

		// check for valid channel:
		$channel       = $matches[ 1 ];
		$this->channel = $this->_server->getChannel( substr( $channel, 1 ) );

		if ( $this->channel === FALSE )
		{
			$this->sendMessage( 'Invalid channel: ' . $channel );
			$this->sendHttpResponse( 404 );
			stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
			$this->_server->removeErrorConnection( $this );

			return FALSE;
		}
		// generate headers array:
		$headers = [ ];
		foreach ( $lines as $line )
		{
			$line = chop( $line );
			if ( preg_match( '/\A(\S+): (.*)\z/', $line, $matches ) )
			{
				$headers[ $matches[ 1 ] ] = $matches[ 2 ];
			}
		}

		// check for supported websocket version:
		if ( ! isset( $headers[ 'Sec-WebSocket-Version' ] ) OR $headers[ 'Sec-WebSocket-Version' ] < 6 )
		{
			$this->sendMessage( 'Unsupported websocket version.' );
			$this->sendHttpResponse( 501 );
			stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
			$this->_server->removeErrorConnection( $this );

			return FALSE;
		}

		// check origin:
		if ( $this->_server->validate_origin === TRUE )
		{
			$origin = ( isset( $headers[ 'Sec-WebSocket-Origin' ] ) ) ? $headers[ 'Sec-WebSocket-Origin' ] : FALSE;
			$origin = ( isset( $headers[ 'Origin' ] ) ) ? $headers[ 'Origin' ] : $origin;

			if ( $origin === FALSE )
			{
				$this->sendMessage( 'No origin provided.' );
				$this->sendHttpResponse( 401 );
				stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
				$this->_server->removeErrorConnection( $this );

				return FALSE;
			}

			if ( empty( $origin ) )
			{
				$this->sendMessage( 'Empty origin provided.' );
				$this->sendHttpResponse( 401 );
				stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
				$this->_server->removeErrorConnection( $this );

				return FALSE;
			}

			if ( $this->_server->isAllowedOrigin( $origin ) === FALSE )
			{
				$this->sendMessage( 'Invalid origin provided.' );
				$this->sendHttpResponse( 401 );
				stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
				$this->_server->removeErrorConnection( $this );

				return FALSE;
			}
		}

		// do handyshake: (hybi-10)
		$secKey    = $headers[ 'Sec-WebSocket-Key' ];
		$secAccept = base64_encode( pack( 'H*', sha1( $secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11' ) ) );
		$response  = 'HTTP/1.1 101 Switching Protocols' . PHP_EOL;
		$response .= 'Upgrade: websocket' . PHP_EOL;
		$response .= 'Connection: Upgrade' . PHP_EOL;
		$response .= 'Sec-WebSocket-Accept: ' . $secAccept . PHP_EOL;

		if ( isset( $headers[ 'Sec-WebSocket-Protocol' ] ) AND ! empty( $headers[ 'Sec-WebSocket-Protocol' ] ) )
		{
			$response .= 'Sec-WebSocket-Protocol: " . substr( $channel, 1 ) . "' . PHP_EOL;
		}

		$response .= '' . PHP_EOL;
		if ( FALSE === ( $this->_server->writeBuffer( $this->socket, $response ) ) )
		{
			return FALSE;
		}

		$this->_is_handshaked = TRUE;
		$this->_server->sendMessage( 'Handshake sent' );
		$this->channel->onConnect( $this );

		// trigger status application:
		if ( $this->_server->getChannel( 'status' ) !== FALSE )
		{
			$this->_server->getChannel( 'status' )->clientConnected( $this->ip_address, $this->port );
		}

		return TRUE;
	}

	public function sendHttpResponse( $code = 400 )
	{
		$header = 'HTTP/1.1 ';

		switch ( $code )
		{
			case 400:
				$header .= '400 Bad request';
				break;

			case 401:
				$header .= '401 Unauthorized';
				break;

			case 403:
				$header .= '403 Forbidden';
				break;

			case 404:
				$header .= '404 Not Found';
				break;

			case 501:
				$header .= '501 Not Implemented';
				break;
		}

		$header .= '' . PHP_EOL;
		$this->_server->writeBuffer( $this->socket, $header );
	}

	public function onData( $data )
	{
		if ( $this->_is_handshaked )
		{
			return $this->_handle( $data );
		}
		else
		{
			$this->_handshake( $data );
		}
	}

	protected function _handle( $data )
	{
		if ( $this->_is_waiting_data === TRUE )
		{
			$data                   = $this->_buffering_data . $data;
			$this->_buffering_data  = '';
			$this->_is_waiting_data = FALSE;
		}

		$decoded_data = $this->_hybi10Decode( $data );

		if ( $decoded_data === FALSE )
		{
			$this->_is_waiting_data = TRUE;
			$this->_buffering_data .= $data;

			return FALSE;
		}
		else
		{
			$this->_buffering_data  = '';
			$this->_is_waiting_data = FALSE;
		}

		// trigger status application:
		if ( $this->_server->getChannel( 'status' ) !== FALSE )
		{
			$this->_server->getChannel( 'status' )->clientActivity( $this->port );
		}

		if ( ! isset( $decoded_data[ 'type' ] ) )
		{
			$this->sendHttpResponse( 401 );
			stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
			$this->_server->removeErrorConnection( $this );

			return FALSE;
		}

		switch ( $decoded_data[ 'type' ] )
		{
			case 'text':
				$this->channel->onData( $decoded_data[ 'payload' ], $this );
				break;

			case 'binary':
				if ( method_exists( $this->channel, 'onBinaryData' ) )
				{
					$this->channel->onBinaryData( $decoded_data[ 'payload' ], $this );
				}
				else
				{
					$this->close( 1003 );
				}
				break;

			case 'ping':
				$this->send( $decoded_data[ 'payload' ], 'pong', FALSE );
				$this->_server->sendMessage( 'Ping? Pong!' );
				break;

			case 'pong':
				// server currently not sending pings, so no pong should be received.
				break;

			case 'close':
				$this->close();
				$this->_server->sendMessage( 'Disconnected' );
				break;
		}

		return TRUE;
	}

	public function send( $payload, $type = 'text', $masked = FALSE )
	{
		$encodedData = $this->_hybi10Encode( $payload, $type, $masked );

		if ( ! $this->_server->writeBuffer( $this->socket, $encodedData ) )
		{
			$this->_server->removeClosedConnection( $this );

			return FALSE;
		}

		return TRUE;
	}

	public function close( $statusCode = 1000 )
	{
		$payload      = str_split( sprintf( '%016b', $statusCode ), 8 );
		$payload[ 0 ] = chr( bindec( $payload[ 0 ] ) );
		$payload[ 1 ] = chr( bindec( $payload[ 1 ] ) );
		$payload      = implode( '', $payload );
		switch ( $statusCode )
		{
			case 1000:
				$payload .= 'normal closure';
				break;

			case 1001:
				$payload .= 'going away';
				break;

			case 1002:
				$payload .= 'protocol error';
				break;

			case 1003:
				$payload .= 'unknown data (opcode)';
				break;

			case 1004:
				$payload .= 'frame too large';
				break;

			case 1007:
				$payload .= 'utf8 expected';
				break;

			case 1008:
				$payload .= 'message violates server policy';
				break;
		}

		if ( $this->send( $payload, 'close', FALSE ) === FALSE )
		{
			return FALSE;
		}

		if ( $this->channel )
		{
			$this->channel->onDisconnect( $this );
		}

		stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
		$this->_server->removeClosedConnection( $this );
	}

	public function onDisconnect()
	{
		$this->sendMessage( 'Disconnected', 'info' );
		$this->close( 1000 );
	}

	public function sendMessage( $message, $type = 'info' )
	{
		$this->_server->sendMessage( '[client ' . $this->ip . ':' . $this->port . '] ' . $message, $type );
	}

	protected function _hybi10Encode( $payload, $type = 'text', $masked = TRUE )
	{
		$frame_headers  = [ ];
		$frame          = '';
		$payload_length = strlen( $payload );

		switch ( $type )
		{
			case 'text':
				// first byte indicates FIN, Text-Frame (10000001):
				$frame_headers[ 0 ] = 129;
				break;

			case 'close':
				// first byte indicates FIN, Close Frame(10001000):
				$frame_headers[ 0 ] = 136;
				break;

			case 'ping':
				// first byte indicates FIN, Ping frame (10001001):
				$frame_headers[ 0 ] = 137;
				break;

			case 'pong':
				// first byte indicates FIN, Pong frame (10001010):
				$frame_headers[ 0 ] = 138;
				break;
		}

		// set mask and payload length (using 1, 3 or 9 bytes)
		if ( $payload_length > 65535 )
		{
			$payload_bin_length = str_split( sprintf( '%064b', $payload_length ), 8 );
			$frame_headers[ 1 ] = ( $masked === TRUE ) ? 255 : 127;
			for ( $i = 0; $i < 8; $i++ )
			{
				$frame_headers[ $i + 2 ] = bindec( $payload_bin_length[ $i ] );
			}
			// most significant bit MUST be 0 (close connection if frame too big)
			if ( $frame_headers[ 2 ] > 127 )
			{
				$this->close( 1004 );

				return FALSE;
			}
		}
		elseif ( $payload_length > 125 )
		{
			$payload_bin_length = str_split( sprintf( '%016b', $payload_length ), 8 );
			$frame_headers[ 1 ] = ( $masked === TRUE ) ? 254 : 126;
			$frame_headers[ 2 ] = bindec( $payload_bin_length[ 0 ] );
			$frame_headers[ 3 ] = bindec( $payload_bin_length[ 1 ] );
		}
		else
		{
			$frame_headers[ 1 ] = ( $masked === TRUE ) ? $payload_length + 128 : $payload_length;
		}
		// convert frame-head to string:
		foreach ( array_keys( $frame_headers ) as $i )
		{
			$frame_headers[ $i ] = chr( $frame_headers[ $i ] );
		}
		if ( $masked === TRUE )
		{
			// generate a random mask:
			$mask = [ ];
			for ( $i = 0; $i < 4; $i++ )
			{
				$mask[ $i ] = chr( rand( 0, 255 ) );
			}

			$frame_headers = array_merge( $frame_headers, $mask );
		}

		$frame = implode( '', $frame_headers );

		// append payload to frame:
		for ( $i = 0; $i < $payload_length; $i++ )
		{
			$frame .= ( $masked === TRUE ) ? $payload[ $i ] ^ $mask[ $i % 4 ] : $payload[ $i ];
		}

		return $frame;
	}

	protected function _hybi10Decode( $data )
	{
		$payload_length   = '';
		$payload_mask     = '';
		$payload_unmasked = '';
		$decoded_data     = [ ];

		// estimate frame type:
		$first_byte_binary  = sprintf( '%08b', ord( $data[ 0 ] ) );
		$second_byte_binary = sprintf( '%08b', ord( $data[ 1 ] ) );
		$opcode             = bindec( substr( $first_byte_binary, 4, 4 ) );
		$is_masked          = ( $second_byte_binary[ 0 ] == '1' ) ? TRUE : FALSE;
		$payload_length     = ord( $data[ 1 ] ) & 127;

		// close connection if unmasked frame is received:
		if ( $is_masked === FALSE )
		{
			$this->close( 1002 );
		}

		switch ( $opcode )
		{
			// text frame:
			case 1:
				$decoded_data[ 'type' ] = 'text';
				break;

			case 2:
				$decoded_data[ 'type' ] = 'binary';
				break;

			// connection close frame:
			case 8:
				$decoded_data[ 'type' ] = 'close';
				break;

			// ping frame:
			case 9:
				$decoded_data[ 'type' ] = 'ping';
				break;

			// pong frame:
			case 10:
				$decoded_data[ 'type' ] = 'pong';
				break;

			default:
				// Close connection on unknown opcode:
				$this->close( 1003 );
				break;
		}

		if ( $payload_length === 126 )
		{
			$payload_mask   = substr( $data, 4, 4 );
			$payload_offset = 8;
			$data_length    = bindec( sprintf( '%08b', ord( $data[ 2 ] ) ) . sprintf( '%08b', ord( $data[ 3 ] ) ) ) + $payload_offset;
		}
		elseif ( $payload_length === 127 )
		{
			$payload_mask   = substr( $data, 10, 4 );
			$payload_offset = 14;
			$temp           = '';

			for ( $i = 0; $i < 8; $i++ )
			{
				$temp .= sprintf( '%08b', ord( $data[ $i + 2 ] ) );
			}

			$data_length = bindec( $temp ) + $payload_offset;
			unset( $temp );
		}
		else
		{
			$payload_mask   = substr( $data, 2, 4 );
			$payload_offset = 6;
			$data_length    = $payload_length + $payload_offset;
		}

		/**
		 * We have to check for large frames here. socket_recv cuts at 1024 bytes
		 * so if websocket-frame is > 1024 bytes we have to wait until whole
		 * data is transferd.
		 */
		if ( strlen( $data ) < $data_length )
		{
			return FALSE;
		}

		if ( $is_masked === TRUE )
		{
			for ( $i = $payload_offset; $i < $data_length; $i++ )
			{
				$j = $i - $payload_offset;
				if ( isset( $data[ $i ] ) )
				{
					$payload_unmasked .= $data[ $i ] ^ $payload_mask[ $j % 4 ];
				}
			}
			$decoded_data[ 'payload' ] = $payload_unmasked;
		}
		else
		{
			$payload_offset            = $payload_offset - 4;
			$decoded_data[ 'payload' ] = substr( $data, $payload_offset );
		}

		return $decoded_data;
	}
}