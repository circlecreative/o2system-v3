<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 20-Jul-16
 * Time: 10:30 PM
 */

namespace O2System\Libraries;


use O2System\Core\SPL\ArrayObject;
use O2System\Libraries\Websocket\User;

class Websocket
{
	/**
	 * PHP Socket Resource
	 *
	 * @access  protected
	 * @type    resource
	 */
	protected $_socket_resource;

	/**
	 * Lists of Sockets Objects
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_sockets = [ ]; //create an array of socket objects

	/**
	 * Lists of Users Objects
	 *
	 * @access  protected
	 * @type array
	 */
	protected $_users = [ ]; //create an array of users objects to handle discussions with users

	/**
	 * Lists of Accounts Objects
	 *
	 * @access  protected
	 * @type array
	 */
	protected $_accounts = [ ]; //create an array of users objects to handle discussions with users

	/**
	 * Debug Mode
	 *
	 * @access  public
	 * @type    bool
	 */
	public $debug_mode = FALSE;

	/**
	 * Websocket constructor.
	 *
	 * @param $ip_address
	 * @param $port
	 */
	public function __construct( $ip_address, $port = 9000 )
	{
		// error_reporting(E_ALL);
		set_time_limit( 0 );
		ob_implicit_flush();

		if ( strpos( $ip_address, ':' ) !== FALSE )
		{
			$x_ip_address = explode( ':', $ip_address );
			$ip_address   = $x_ip_address[ 0 ];
			$port         = $x_ip_address[ 1 ];
		}

		$this->_socket_resource = socket_create( AF_INET, SOCK_STREAM, SOL_TCP ) or die( "socket_create() failed" );
		socket_set_option( $this->_socket_resource, SOL_SOCKET, SO_REUSEADDR, 1 ) or die( "socket_option() failed" );
		socket_bind( $this->_socket_resource, $ip_address, $port ) or die( "socket_bind() failed" );
		socket_listen( $this->_socket_resource, 20 ) or die( "socket_listen() failed" );
		$this->_sockets[] = $this->_socket_resource;

		$this->stdOut( $this->__asciiBanner() );
		$this->stdOut( "PHP WebSocket Server running...." );
		$this->stdOut( "Server Started : " . date( 'Y-m-d H:i:s' ) );
		$this->stdOut( "Listening on   : " . $ip_address . " port " . $port );
		$this->stdOut( "Master socket  : " . $this->_socket_resource . "\n" );
		$this->stdOut( ".... awaiting connections ..." );

		// Main Server processing loop
		while ( TRUE )  //server is always listening
		{
			$changed_sockets = $this->_sockets;
			@socket_select( $changed_sockets, $write = NULL, $except = NULL, NULL );

			$this->stdOut( "listening..." . PHP_EOL );

			foreach ( $changed_sockets as $socket )
			{
				if ( $socket == $this->_socket_resource )
				{
					$user = socket_accept( $this->_socket_resource );
					if ( $user < 0 )
					{
						$this->logStdOut( "socket_accept() failed" );
						continue;
					}
					else
					{
						$this->connect( $user );
					}
				}
				else
				{
					$bytes = @socket_recv( $socket, $buffer, 2048, 0 );
					if ( $bytes == 0 )
					{
						$this->disconnect( $socket );
					}
					else
					{
						$user = $this->getUserBySocket( $socket );

						if ( ! $user->isHandshake() )
						{
							$this->stdOut( "Handshaking $user" );
							$this->_doHandshake( $user, $buffer );
						}
						else
						{
							$this->process( $user, $this->__frameDecode( $buffer ) );
						}
					}
				}
			} //foreach socket
		} //main loop
	}

	private function __asciiBanner()
	{
		$banner = "               _    ____             _        _   \n";
		$banner .= " __      _____| |__/ ___|  ___   ___| | _____| |_\n ";
		$banner .= "\ \ /\ / / _ \ '_ \___ \ / _ \ / __| |/ / _ \ __|\n";
		$banner .= "  \ V  V /  __/ |_) |__) | (_) | (__|   <  __/ |_ \n";
		$banner .= "   \_/\_/ \___|_.__/____/ \___/ \___|_|\_\___|\__|\n";

		return $banner;

	}

	public function process( User $user, $message )
	{
		if ( class_exists( $class_name = \O2System::$config[ 'namespace' ] . 'Controllers\Websocket' ) )
		{
			$controller = new $class_name();
			$controller->_setHandler( $this );

			$controller_message = $controller->_onProcess( $user, $message );
			$message            = empty( $controller_message ) ? $message : $controller_message;
		}

		/* Extend and modify this method to suit your needs */
		/* Basic usage is to echo incoming messages back to client */
		$this->sendMessage( $user, $message );
	}


	public function sendPong( User $user )
	{
		$header_bytes[ 0 ] = 138; // 1xA Pong frame (FIN + opcode)
		$message           = implode( array_map( "chr", $header_bytes ) );

		$this->sendMessage( $user, $message );
	}

	/**
	 * Encode a text for sending to clients via ws://
	 *
	 * @param $message
	 * WebSocket frame
	 *
	 * +-+-+-+-+-------+-+-------------+-------------------------------+
	 * 0                   1                   2                   3
	 * 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1
	 * +-+-+-+-+-------+-+-------------+-------------------------------+
	 * |F|R|R|R| opcode|M| Payload len |    Extended payload length    |
	 * |I|S|S|S|  (4)  |A|     (7)     |             (16/64)           |
	 * |N|V|V|V|       |S|             |   (if payload len==126/127)   |
	 * | |1|2|3|       |K|             |                               |
	 * +-+-+-+-+-------+-+-------------+ - - - - - - - - - - - - - - - +
	 * |     Extended payload length continued, if payload len == 127  |
	 * + - - - - - - - - - - - - - - - +-------------------------------+
	 * |                               |Masking-key, if MASK set to 1  |
	 * +-------------------------------+-------------------------------+
	 * | Masking-key (continued)       |          Payload Data         |
	 * +-------------------------------- - - - - - - - - - - - - - - - +
	 * :                     Payload Data continued ...                :
	 * + - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +
	 * |                     Payload Data continued ...                |
	 * +---------------------------------------------------------------+
	 */
	final private function __frameEncode( $message )
	{
		$length = strlen( $message );

		$bytesHeader      = [ ];
		$bytesHeader[ 0 ] = 129; // 0x1 text frame (FIN + opcode)

		if ( $length <= 125 )
		{
			$bytesHeader[ 1 ] = $length;
		}
		else if ( $length >= 126 && $length <= 65535 )
		{
			$bytesHeader[ 1 ] = 126;
			$bytesHeader[ 2 ] = ( $length >> 8 ) & 255;
			$bytesHeader[ 3 ] = ( $length ) & 255;
		}
		else
		{
			$bytesHeader[ 1 ] = 127;
			$bytesHeader[ 2 ] = ( $length >> 56 ) & 255;
			$bytesHeader[ 3 ] = ( $length >> 48 ) & 255;
			$bytesHeader[ 4 ] = ( $length >> 40 ) & 255;
			$bytesHeader[ 5 ] = ( $length >> 32 ) & 255;
			$bytesHeader[ 6 ] = ( $length >> 24 ) & 255;
			$bytesHeader[ 7 ] = ( $length >> 16 ) & 255;
			$bytesHeader[ 8 ] = ( $length >> 8 ) & 255;
			$bytesHeader[ 9 ] = ( $length ) & 255;
		}

		//apply chr against bytesHeader , then prepend to message
		$str = implode( array_map( "chr", $bytesHeader ) ) . $message;

		return $str;
	}

	/**
	 * frame_decode (decode data frame)  a received payload (websockets)
	 *
	 * @param $payload (Refer to: https://tools.ietf.org/html/rfc6455#section-5 )
	 */
	final private function __frameDecode( $payload )
	{
		if ( ! isset( $payload ) )
		{
			return NULL;
		}  //empty data return nothing

		$length = ord( $payload[ 1 ] ) & 127;

		if ( $length == 126 )
		{
			$masks = substr( $payload, 4, 4 );
			$data  = substr( $payload, 8 );
		}
		elseif ( $length == 127 )
		{
			$masks = substr( $payload, 10, 4 );
			$data  = substr( $payload, 14 );
		}
		else
		{
			$masks = substr( $payload, 2, 4 );
			$data  = substr( $payload, 6 );
		}

		for ( $i = 0; $i < strlen( $masks ); ++$i )
		{
			$this->stdOut( "header[" . $i . "] =" . ord( $masks[ $i ] ) . " \n" );
		}
		//$this->say(" data:$data \n");

		//did we just get a PING frame
		if ( strlen( $masks ) == 4 && strlen( $data ) == 0 )
		{
			return "ping";
		}

		$text = '';
		for ( $i = 0; $i < strlen( $data ); ++$i )
		{
			$text .= $data[ $i ] ^ $masks[ $i % 4 ];
		}

		return $text;
	}  //end of frame_decode unmask(Received from client)


	/**
	 * Send Message to User
	 *
	 * @param $user
	 * @param $message
	 */
	final public function sendMessage( $user, $message )
	{
		$message = $this->__frameEncode( $message );

		if ( $user instanceof User )
		{
			socket_write( $user->getSocket(), $message );

			$this->stdOut( "Send message to {$user->getId()}: " . $message . " (" . strlen( $message ) . " bytes) \n" );
		}
		elseif ( is_resource( $user ) )
		{
			socket_write( $user, $message );

			$this->stdOut( "Send message to " . array_search( $user, $this->_users ) . ": " . $message . " (" . strlen( $message ) . " bytes) \n" );
		}
	}

	/**
	 * Connect Socket
	 *
	 * @param $socket
	 */
	public function connect( $socket )
	{
		$user                             = new User( uniqid(), $socket );
		$this->_users[ $user->getId() ]   = $user;
		$this->_sockets[ $user->getId() ] = $socket;

		$this->stdOut( "User #{$user->getId()} connected on socket #$socket" );
		$this->stdOut( "Connected users: " . count( $this->_users ) );
	}

	public function disconnect( $socket )
	{
		$index = array_search( $socket, $this->_sockets );
		socket_close( $socket );

		unset( $this->_users[ $index ] );

		$this->stdOut( "User #$index disconnected on socket #$socket" );
		$this->stdOut( "Connected users: " . count( $this->_users ) );
	}

	private function __calculatingSecurityKey( $security_key, $magic_string )
	{
		$this->logStdOut( "\n Calculating sec-key: [" . $security_key . "] \n MagicString:" . $magic_string . "\n" );

		return base64_encode( sha1( $security_key . $magic_string, TRUE ) );
	}

	/**
	 * Do Handshake
	 *
	 * @param $user
	 * @param $buffer
	 *
	 * @return bool
	 */
	protected function _doHandshake( User $user, $buffer )
	{
		$this->stdOut( "\nWebSocket Requesting handshake..." );

		list( $resource, $host, $origin, $websocket_key, $websocket_version, $last_8_bytes ) = $this->_getHeaders( $buffer );

		$magic_string = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";

		//Calculate Accept = base64_encode( SHA1( key1 +attach magic_string ))
		$accept = $this->__calculatingSecurityKey( $websocket_key, $magic_string );

		/*
		 * Respond only when protocol specified in request header
		 * "Sec-WebSocket-Protocol: chat" . "\r\n" .
		 */
		$upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"WebSocket-Location: ws://" . $host . $resource . "\r\n" .
			"Sec-WebSocket-Accept: $accept" .
			"\r\n\r\n";

		socket_write( $user->getSocket(), $upgrade );

		$this->stdOut( "Issuing websocket Upgrade \n" );
		$user->doHandshake();

		$this->stdOut( "Done handshaking... Connected Users: " . count( $this->_users ) );

		return $user->isHandshake();
	}

	/**
	 * Get request Headers
	 *
	 * @param $request
	 *
	 * @return array
	 */
	protected function _getHeaders( $request )
	{
		$resource = $host = $origin = NULL;

		if ( preg_match( "/GET (.*) HTTP/", $request, $match ) )
		{
			$resource = $match[ 1 ];
		}
		if ( preg_match( "/Host: (.*)\r\n/", $request, $match ) )
		{
			$host = $match[ 1 ];
		}
		if ( preg_match( "/Origin: (.*)\r\n/", $request, $match ) )
		{
			$origin = $match[ 1 ];
		}
		if ( preg_match( "/Sec-WebSocket-Key: (.*)\r\n/", $request, $match ) )
		{
			$this->stdOut( "WebSocket-Key: " . $websocket_key = $match[ 1 ] );
		}
		if ( preg_match( "/Sec-WebSocket-Version: (.*)\r\n/", $request, $match ) )
		{
			$this->stdOut( "WebSocket-Version: " . $websocket_version = $match[ 1 ] );
		}
		if ( $match = substr( $request, -8 ) )
		{
			$this->logStdOut( "Last 8 bytes: " . $last_8_bytes = $match );
		}

		return [ $resource, $host, $origin, $websocket_key, $websocket_version, $last_8_bytes ];
	}

	/**
	 * Get User By Socket
	 *
	 * @param $socket
	 *
	 * @return User
	 */
	public function getUserBySocket( $socket )
	{
		$index = array_search( $socket, $this->_sockets );

		return $this->_users[ $index ];
	}

	public function setUserAccount( User $user, ArrayObject $account )
	{
		$account->socket                   = $this->_sockets[ $user->getId() ];
		$this->_accounts[ $user->getId() ] = $account;
	}

	public function getAccountBySocket( $socket )
	{
		$index = array_search( $socket, $this->_sockets );

		return $this->_accounts[ $index ];
	}

	/**
	 * Send Server Output
	 *
	 * @param string $message
	 */
	public function stdOut( $message = '' )
	{
		echo $message . PHP_EOL;
	}

	/**
	 * Log Server Output
	 *
	 * @param string $log_message
	 */
	public function logStdOut( $log_message = '' )
	{
		if ( $this->debug_mode )
		{
			echo $log_message . PHP_EOL;
		}
	}
}