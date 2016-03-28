<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 2/26/2016
 * Time: 11:48 AM
 */

namespace O2System\Libraries\Websocket;


class Client
{
	protected $_host;
	protected $_port;
	protected $_path;
	protected $_origin;
	protected $_socket       = NULL;
	protected $_is_connected = FALSE;
	
	public function __construct() { }
	
	public function __destruct()
	{
		$this->disconnect();
	}
	
	public function send_data( $data, $type = 'text', $masked = TRUE )
	{
		if ( $this->_is_connected === FALSE )
		{
			trigger_error( "Not connected", E_USER_WARNING );

			return FALSE;
		}
		if ( ! is_string( $data ) )
		{
			trigger_error( "Not a string data was given.", E_USER_WARNING );

			return FALSE;
		}
		if ( strlen( $data ) == 0 )
		{
			return FALSE;
		}
		$res = @fwrite( $this->_socket, $this->_hybi10Encode( $data, $type, $masked ) );
		if ( $res === 0 || $res === FALSE )
		{
			return FALSE;
		}
		$buffer = ' ';
		while ( $buffer !== '' )
		{
			$buffer = fread( $this->_socket, 512 );// drop?
		}
		
		return TRUE;
	}
	
	public function connect( $host, $port, $path, $origin = FALSE )
	{
		$this->_host = $host;
		$this->_port = $port;
		$this->_path = $path;
		$this->_origin = $origin;
		
		$key = base64_encode( $this->_generateRandomString( 16, FALSE, TRUE ) );
		$header = "GET " . $path . " HTTP/1.1\r\n";
		$header .= "Host: " . $host . ":" . $port . "\r\n";
		$header .= "Upgrade: WebSocket\r\n";
		$header .= "Connection: Upgrade\r\n";
		$header .= "Sec-WebSocket-Key: " . $key . "\r\n";
		if ( $origin !== FALSE )
		{
			$header .= "Sec-WebSocket-Origin: " . $origin . "\r\n";
		}
		$header .= "Sec-WebSocket-Version: 13\r\n";
		
		$this->_socket = fsockopen( $host, $port, $errno, $errstr, 2 );
		socket_set_timeout( $this->_socket, 0, 10000 );
		@fwrite( $this->_socket, $header );
		$response = @fread( $this->_socket, 1500 );
		
		preg_match( '#Sec-WebSocket-Accept:\s(.*)$#mU', $response, $matches );
		
		if ( $matches )
		{
			$keyAccept = trim( $matches[ 1 ] );
			$expectedResonse = base64_encode( pack( 'H*', sha1( $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11' ) ) );
			$this->_is_connected = ( $keyAccept === $expectedResonse ) ? TRUE : FALSE;
		}
		
		return $this->_is_connected;
	}
	
	public function checkConnection()
	{
		$this->_is_connected = FALSE;
		
		// send ping:
		$data = 'ping?';
		@fwrite( $this->_socket, $this->_hybi10Encode( $data, 'ping', TRUE ) );
		$response = @fread( $this->_socket, 300 );
		if ( empty( $response ) )
		{
			return FALSE;
		}
		$response = $this->_hybi10Decode( $response );
		if ( ! is_array( $response ) )
		{
			return FALSE;
		}
		if ( ! isset( $response[ 'type' ] ) || $response[ 'type' ] !== 'pong' )
		{
			return FALSE;
		}
		$this->_is_connected = TRUE;

		return TRUE;
	}
	
	
	public function disconnect()
	{
		$this->_is_connected = FALSE;
		is_resource( $this->_socket ) and fclose( $this->_socket );
	}
	
	public function reconnect()
	{
		sleep( 10 );
		$this->_is_connected = FALSE;
		fclose( $this->_socket );
		$this->connect( $this->_host, $this->_port, $this->_path, $this->_origin );
	}
	
	private function _generateRandomString( $length = 10, $addSpaces = TRUE, $addNumbers = TRUE )
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
		$useChars = array();
		// select some random chars:    
		for ( $i = 0; $i < $length; $i++ )
		{
			$useChars[] = $characters[ mt_rand( 0, strlen( $characters ) - 1 ) ];
		}
		// add spaces and numbers:
		if ( $addSpaces === TRUE )
		{
			array_push( $useChars, ' ', ' ', ' ', ' ', ' ', ' ' );
		}
		if ( $addNumbers === TRUE )
		{
			array_push( $useChars, rand( 0, 9 ), rand( 0, 9 ), rand( 0, 9 ) );
		}
		shuffle( $useChars );
		$randomString = trim( implode( '', $useChars ) );
		$randomString = substr( $randomString, 0, $length );

		return $randomString;
	}
	
	private function _hybi10Encode( $payload, $type = 'text', $masked = TRUE )
	{
		$frameHead = array();
		$frame = '';
		$payloadLength = strlen( $payload );
		
		switch ( $type )
		{
			case 'text':
				// first byte indicates FIN, Text-Frame (10000001):
				$frameHead[ 0 ] = 129;
				break;
			
			case 'close':
				// first byte indicates FIN, Close Frame(10001000):
				$frameHead[ 0 ] = 136;
				break;
			
			case 'ping':
				// first byte indicates FIN, Ping frame (10001001):
				$frameHead[ 0 ] = 137;
				break;
			
			case 'pong':
				// first byte indicates FIN, Pong frame (10001010):
				$frameHead[ 0 ] = 138;
				break;
		}
		
		// set mask and payload length (using 1, 3 or 9 bytes) 
		if ( $payloadLength > 65535 )
		{
			$payloadLengthBin = str_split( sprintf( '%064b', $payloadLength ), 8 );
			$frameHead[ 1 ] = ( $masked === TRUE ) ? 255 : 127;
			for ( $i = 0; $i < 8; $i++ )
			{
				$frameHead[ $i + 2 ] = bindec( $payloadLengthBin[ $i ] );
			}
			// most significant bit MUST be 0 (close connection if frame too big)
			if ( $frameHead[ 2 ] > 127 )
			{
				$this->close( 1004 );

				return FALSE;
			}
		}
		elseif ( $payloadLength > 125 )
		{
			$payloadLengthBin = str_split( sprintf( '%016b', $payloadLength ), 8 );
			$frameHead[ 1 ] = ( $masked === TRUE ) ? 254 : 126;
			$frameHead[ 2 ] = bindec( $payloadLengthBin[ 0 ] );
			$frameHead[ 3 ] = bindec( $payloadLengthBin[ 1 ] );
		}
		else
		{
			$frameHead[ 1 ] = ( $masked === TRUE ) ? $payloadLength + 128 : $payloadLength;
		}
		
		// convert frame-head to string:
		foreach ( array_keys( $frameHead ) as $i )
		{
			$frameHead[ $i ] = chr( $frameHead[ $i ] );
		}
		if ( $masked === TRUE )
		{
			// generate a random mask:
			$mask = array();
			for ( $i = 0; $i < 4; $i++ )
			{
				$mask[ $i ] = chr( rand( 0, 255 ) );
			}
			
			$frameHead = array_merge( $frameHead, $mask );
		}
		$frame = implode( '', $frameHead );
		
		// append payload to frame:
		$framePayload = array();
		for ( $i = 0; $i < $payloadLength; $i++ )
		{
			$frame .= ( $masked === TRUE ) ? $payload[ $i ] ^ $mask[ $i % 4 ] : $payload[ $i ];
		}
		
		return $frame;
	}
	
	private function _hybi10Decode( $data )
	{
		$payloadLength = '';
		$mask = '';
		$unmaskedPayload = '';
		$decodedData = array();
		
		// estimate frame type:
		$firstByteBinary = sprintf( '%08b', ord( $data[ 0 ] ) );
		$secondByteBinary = sprintf( '%08b', ord( $data[ 1 ] ) );
		$opcode = bindec( substr( $firstByteBinary, 4, 4 ) );
		$isMasked = ( $secondByteBinary[ 0 ] == '1' ) ? TRUE : FALSE;
		$payloadLength = ord( $data[ 1 ] ) & 127;
		
		switch ( $opcode )
		{
			// text frame:
			case 1:
				$decodedData[ 'type' ] = 'text';
				break;
			
			case 2:
				$decodedData[ 'type' ] = 'binary';
				break;
			
			// connection close frame:
			case 8:
				$decodedData[ 'type' ] = 'close';
				break;
			
			// ping frame:
			case 9:
				$decodedData[ 'type' ] = 'ping';
				break;
			
			// pong frame:
			case 10:
				$decodedData[ 'type' ] = 'pong';
				break;
			
			default:
				return FALSE;
				break;
		}
		
		if ( $payloadLength === 126 )
		{
			$mask = substr( $data, 4, 4 );
			$payloadOffset = 8;
			$dataLength = bindec( sprintf( '%08b', ord( $data[ 2 ] ) ) . sprintf( '%08b', ord( $data[ 3 ] ) ) ) + $payloadOffset;
		}
		elseif ( $payloadLength === 127 )
		{
			$mask = substr( $data, 10, 4 );
			$payloadOffset = 14;
			$tmp = '';
			for ( $i = 0; $i < 8; $i++ )
			{
				$tmp .= sprintf( '%08b', ord( $data[ $i + 2 ] ) );
			}
			$dataLength = bindec( $tmp ) + $payloadOffset;
			unset( $tmp );
		}
		else
		{
			$mask = substr( $data, 2, 4 );
			$payloadOffset = 6;
			$dataLength = $payloadLength + $payloadOffset;
		}
		
		if ( $isMasked === TRUE )
		{
			for ( $i = $payloadOffset; $i < $dataLength; $i++ )
			{
				$j = $i - $payloadOffset;
				if ( isset( $data[ $i ] ) )
				{
					$unmaskedPayload .= $data[ $i ] ^ $mask[ $j % 4 ];
				}
			}
			$decodedData[ 'payload' ] = $unmaskedPayload;
		}
		else
		{
			$payloadOffset = $payloadOffset - 4;
			$decodedData[ 'payload' ] = substr( $data, $payloadOffset );
		}
		
		return $decodedData;
	}
}