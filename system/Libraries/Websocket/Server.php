<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     O2System
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2014, .
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Libraries\Websocket;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * Websocket Server Class
 *
 * @author  Sann-Remy Chea
 *
 * @package O2System\Libraries\Websocket
 */
class Server
{

	/**
	 * The address of the server
	 *
	 * @var String
	 */
	private $__address;
	/**
	 * The port for the master socket
	 *
	 * @var int
	 */
	private $__port;
	/**
	 * The master socket
	 *
	 * @var Resource
	 */
	private $__master;
	/**
	 * The array of sockets (1 socket = 1 client)
	 *
	 * @var Array of resource
	 */
	private $__sockets;
	/**
	 * The array of connected clients
	 *
	 * @var Array of clients
	 */
	private $__clients;
	/**
	 * If true, the server will print messages to the terminal
	 *
	 * @var Boolean
	 */
	private $__verbose_mode;

	/**
	 * Server constructor
	 *
	 * @param $address The address IP or hostname of the server (default: 127.0.0.1).
	 * @param $port    The port for the master socket (default: 5001)
	 */
	public function __construct( $address = '127.0.0.1', $port = 5001, $verbose_mode = FALSE )
	{
		$this->__console( "Server starting..." );
		$this->__address      = $address;
		$this->__port         = $port;
		$this->__verbose_mode = $verbose_mode;
		// socket creation
		$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
		socket_set_option( $socket, SOL_SOCKET, SO_REUSEADDR, 1 );
		if ( ! is_resource( $socket ) )
		{
			$this->__console( "socket_create() failed: " . socket_strerror( socket_last_error() ), TRUE );
		}
		if ( ! socket_bind( $socket, $this->__address, $this->__port ) )
		{
			$this->__console( "socket_bind() failed: " . socket_strerror( socket_last_error() ), TRUE );
		}
		if ( ! socket_listen( $socket, 20 ) )
		{
			$this->__console( "socket_listen() failed: " . socket_strerror( socket_last_error() ), TRUE );
		}
		$this->__master  = $socket;
		$this->__sockets = [ $socket ];
		$this->__console( "Server started on {$this->__address}:{$this->__port}" );
	}

	/**
	 * Create a client object with its associated socket
	 *
	 * @param   resource $socket
	 */
	private function __connect( $socket )
	{
		$this->__console( "Creating client..." );
		$client            = new Client( uniqid(), $socket );
		$this->__clients[] = $client;
		$this->__sockets[] = $socket;
		$this->__console( "Client #{$client->getId()} is successfully created!" );
	}

	/**
	 * Do the handshaking between client and server
	 *
	 * @param   Client $client
	 * @param   string $headers
	 *
	 * @return bool
	 */
	private function __handshake( Client $client, $headers )
	{
		$this->__console( "Getting client WebSocket version..." );
		if ( preg_match( "/Sec-WebSocket-Version: (.*)\r\n/", $headers, $match ) )
		{
			$version = $match[ 1 ];
		}
		else
		{
			$this->__console( "The client doesn't support WebSocket" );

			return FALSE;
		}
		$this->__console( "Client WebSocket version is {$version}, (required: 13)" );
		if ( $version == 13 )
		{
			// Extract header variables
			$this->__console( "Getting headers..." );
			if ( preg_match( "/GET (.*) HTTP/", $headers, $match ) )
			{
				$root = $match[ 1 ];
			}
			if ( preg_match( "/Host: (.*)\r\n/", $headers, $match ) )
			{
				$host = $match[ 1 ];
			}
			if ( preg_match( "/Origin: (.*)\r\n/", $headers, $match ) )
			{
				$origin = $match[ 1 ];
			}
			if ( preg_match( "/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match ) )
			{
				$key = $match[ 1 ];
			}
			$this->__console( "Client headers are:" );
			$this->__console( "\t- Root: " . $root );
			$this->__console( "\t- Host: " . $host );
			$this->__console( "\t- Origin: " . $origin );
			$this->__console( "\t- Sec-WebSocket-Key: " . $key );
			$this->__console( "Generating Sec-WebSocket-Accept key..." );
			$accept_key = $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
			$accept_key = base64_encode( sha1( $accept_key, TRUE ) );
			$upgrade   = "HTTP/1.1 101 Switching Protocols\r\n" .
				"Upgrade: websocket\r\n" .
				"Connection: Upgrade\r\n" .
				"Sec-WebSocket-Accept: $accept_key" .
				"\r\n\r\n";
			$this->__console( "Sending this response to the client #{$client->getId()}:\r\n" . $upgrade );
			socket_write( $client->getSocket(), $upgrade );
			$client->setHandshake( TRUE );
			$this->__console( "Handshake is successfully done!" );

			return TRUE;
		}
		else
		{
			$this->__console( "WebSocket version 13 required (the client supports version {$version})" );

			return FALSE;
		}
	}

	/**
	 * Disconnect a client and close the connection
	 *
	 * @param   Client $client
	 */
	private function __disconnect( Client $client )
	{
		$this->__console( "Disconnecting client #{$client->getId()}" );
		$client->setIsConnected( FALSE );
		$i = array_search( $client, $this->__clients );
		$j = array_search( $client->getSocket(), $this->__sockets );
		if ( $j >= 0 )
		{
			if ( $client->getSocket() )
			{
				array_splice( $this->__sockets, $j, 1 );
				socket_shutdown( $client->getSocket(), 2 );
				socket_close( $client->getSocket() );
				$this->__console( "Socket closed" );
			}
		}
		if ( $i >= 0 )
		{
			array_splice( $this->__clients, $i, 1 );
		}
		$this->__console( "Client #{$client->getId()} disconnected" );
	}

	/**
	 * Get the client associated with the socket
	 *
	 * @param $socket
	 *
	 * @return mixed A client object if found, if not false
	 */
	private function __getClientBySocket( $socket )
	{
		foreach ( $this->__clients as $client )
		{
			if ( $client->getSocket() == $socket )
			{
				$this->__console( "Client found" );

				return $client;
			}
		}

		return FALSE;
	}

	/**
	 * Do an action
	 *
	 * @param $client
	 * @param $action
	 */
	private function __action( $client, $action )
	{
		$action = $this->__unmask( $action );
		$this->__console( "Performing action: " . $action );
		if ( $action == "exit" || $action == "quit" )
		{
			$this->__console( "Killing a child process" );
			posix_kill( $client->getPid(), SIGTERM );
			$this->__console( "Process {$client->getPid()} is killed!" );
		}
	}

	/**
	 * Run the server
	 */
	public function run()
	{
		$this->__console( "Start running..." );

		while ( TRUE )
		{
			$changed_sockets = $this->__sockets;
			if ( $changed_sockets )
			{
				@socket_select( $changed_sockets, $write = NULL, $except = NULL, 1 );
				foreach ( $changed_sockets as $socket )
				{
					if ( $socket == $this->__master )
					{
						if ( ( $accepted_socket = socket_accept( $this->__master ) ) < 0 )
						{
							$this->__console( "Socket error: " . socket_strerror( socket_last_error( $accepted_socket ) ) );
						}
						else
						{
							$this->__connect( $accepted_socket );
						}
					}
					else
					{
						$this->__console( "Finding the socket that associated to the client..." );
						$client = $this->__getClientBySocket( $socket );
						if ( $client )
						{
							$this->__console( "Receiving data from the client" );
							$data = NULL;
							while ( $bytes = @socket_recv( $socket, $r_data, 2048, MSG_DONTWAIT ) )
							{
								$data .= $r_data;
							}
							if ( ! $client->getHandshake() )
							{
								$this->__console( "Doing the handshake" );
								if ( $this->__handshake( $client, $data ) )
								{
									$this->__startProcess( $client );
								}
								else
								{
									$this->__disconnect( $client );
								}
							}
							elseif ( $bytes === 0 )
							{
								$this->__disconnect( $client );
							}
							else
							{
								// When received data from client
								$this->__action( $client, $data );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Start a child process for pushing data
	 *
	 * @param Client $client
	 */
	private function __startProcess( Client $client )
	{
		$this->__console( "Start a client process" );
		$pid = pcntl_fork();
		if ( $pid == -1 )
		{
			die( 'could not fork' );
		}
		elseif ( $pid )
		{ // process
			$client->setPid( $pid );
		}
		else
		{
			// we are the child
			while ( TRUE )
			{
				// check if the client is connected
				if ( ! $client->isConnected() )
				{
					break;
				}
				// push something to the client
				$seconds = rand( 2, 5 );
				$this->__send( $client, "I am waiting {$seconds} seconds" );
				sleep( $seconds );
			}
		}
	}

	/**
	 * Send a text to client
	 *
	 * @param $client
	 * @param $text
	 */
	private function __send( $client, $text )
	{
		$this->__console( "Send '" . $text . "' to client #{$client->getId()}" );
		$text = $this->encode( $text );
		if ( socket_write( $client->getSocket(), $text, strlen( $text ) ) === FALSE )
		{
			$this->__console( "Unable to write to client #{$client->getId()}'s socket" );
			$this->__disconnect( $client );
		}
	}

	/**
	 * Encode a text for sending to clients via ws://
	 *
	 * @param   string $message
	 * @param   string $type
	 *
	 * @return string
	 */
	public function encode( $message, $type = 'text' )
	{
		switch ( $type )
		{
			case 'continuous':
				$b1 = 0;
				break;
			case 'text':
				$b1 = 1;
				break;
			case 'binary':
				$b1 = 2;
				break;
			case 'close':
				$b1 = 8;
				break;
			case 'ping':
				$b1 = 9;
				break;
			case 'pong':
				$b1 = 10;
				break;
		}
		$b1 += 128;
		$length       = strlen( $message );
		$length_field = "";
		if ( $length < 126 )
		{
			$b2 = $length;
		}
		elseif ( $length <= 65536 )
		{
			$b2         = 126;
			$hex_length = dechex( $length );
			//$this->stdout("Hex Length: $hex_length");
			if ( strlen( $hex_length ) % 2 == 1 )
			{
				$hex_length = '0' . $hex_length;
			}
			$n = strlen( $hex_length ) - 2;
			for ( $i = $n; $i >= 0; $i = $i - 2 )
			{
				$length_field = chr( hexdec( substr( $hex_length, $i, 2 ) ) ) . $length_field;
			}
			while ( strlen( $length_field ) < 2 )
			{
				$length_field = chr( 0 ) . $length_field;
			}
		}
		else
		{
			$b2         = 127;
			$hex_length = dechex( $length );
			if ( strlen( $hex_length ) % 2 == 1 )
			{
				$hex_length = '0' . $hex_length;
			}
			$n = strlen( $hex_length ) - 2;
			for ( $i = $n; $i >= 0; $i = $i - 2 )
			{
				$length_field = chr( hexdec( substr( $hex_length, $i, 2 ) ) ) . $length_field;
			}
			while ( strlen( $length_field ) < 8 )
			{
				$length_field = chr( 0 ) . $length_field;
			}
		}

		return chr( $b1 ) . chr( $b2 ) . $length_field . $message;
	}

	/**
	 * Unmask a received payload
	 *
	 * @param $payload
	 *
	 * @return string
	 */
	private function __unmask( $payload )
	{
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
		$text = '';
		for ( $i = 0; $i < strlen( $data ); ++$i )
		{
			$text .= $data[ $i ] ^ $masks[ $i % 4 ];
		}

		return $text;
	}

	/**
	 * Print a text to the terminal
	 *
	 * @param   string $text the text to display
	 * @param   bool   $exit if true, the process will exit
	 */
	private function __console( $text, $exit = FALSE )
	{
		$text = date( '[Y-m-d H:i:s] ' ) . $text . PHP_EOL;

		if ( $exit )
		{
			die( $text );
		}
		if ( $this->__verbose_mode )
		{
			echo $text;
		}
	}
}