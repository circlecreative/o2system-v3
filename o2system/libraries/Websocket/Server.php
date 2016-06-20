<?php
/**
 * O2System
 *
 * An open source channel development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Libraries\Websocket;

use O2System\Libraries\Websocket\Interfaces\Channel;

defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

class Server
{

	/**
	 * The address of the server
	 *
	 * @var String
	 */
	private $address;
	/**
	 * The port for the master socket
	 *
	 * @var int
	 */
	private $port;
	/**
	 * The master socket
	 *
	 * @var Resource
	 */
	private $master;
	/**
	 * The array of sockets (1 socket = 1 client)
	 *
	 * @var Array of resource
	 */
	private $sockets;
	/**
	 * The array of connected clients
	 *
	 * @var Array of clients
	 */
	private $clients;
	/**
	 * If true, the server will print messages to the terminal
	 *
	 * @var Boolean
	 */
	private $verboseMode;

	/**
	 * Server constructor
	 *
	 * @param $address The address IP or hostname of the server (default: 127.0.0.1).
	 * @param $port    The port for the master socket (default: 5001)
	 */
	public function __construct( $address = '127.0.0.1', $port = 5001, $verboseMode = FALSE )
	{
		$this->console( "Server starting..." );
		$this->address     = $address;
		$this->port        = $port;
		$this->verboseMode = $verboseMode;
		// socket creation
		$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
		socket_set_option( $socket, SOL_SOCKET, SO_REUSEADDR, 1 );
		if ( ! is_resource( $socket ) )
		{
			$this->console( "socket_create() failed: " . socket_strerror( socket_last_error() ), TRUE );
		}
		if ( ! socket_bind( $socket, $this->address, $this->port ) )
		{
			$this->console( "socket_bind() failed: " . socket_strerror( socket_last_error() ), TRUE );
		}
		if ( ! socket_listen( $socket, 20 ) )
		{
			$this->console( "socket_listen() failed: " . socket_strerror( socket_last_error() ), TRUE );
		}
		$this->master  = $socket;
		$this->sockets = [ $socket ];
		$this->console( "Server started on {$this->address}:{$this->port}" );
	}

	/**
	 * Create a client object with its associated socket
	 *
	 * @param $socket
	 */
	private function connect( $socket )
	{
		$this->console( "Creating client..." );
		$client          = new Client( uniqid(), $socket );
		$this->clients[] = $client;
		$this->sockets[] = $socket;
		$this->console( "Client #{$client->getId()} is successfully created!" );
	}

	/**
	 * Do the handshaking between client and server
	 *
	 * @param $client
	 * @param $headers
	 */
	private function handshake( $client, $headers )
	{
		$this->console( "Getting client WebSocket version..." );
		if ( preg_match( "/Sec-WebSocket-Version: (.*)\r\n/", $headers, $match ) )
		{
			$version = $match[ 1 ];
		}
		else
		{
			$this->console( "The client doesn't support WebSocket" );

			return FALSE;
		}
		$this->console( "Client WebSocket version is {$version}, (required: 13)" );
		if ( $version == 13 )
		{
			// Extract header variables
			$this->console( "Getting headers..." );
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
			$this->console( "Client headers are:" );
			$this->console( "\t- Root: " . $root );
			$this->console( "\t- Host: " . $host );
			$this->console( "\t- Origin: " . $origin );
			$this->console( "\t- Sec-WebSocket-Key: " . $key );
			$this->console( "Generating Sec-WebSocket-Accept key..." );
			$acceptKey = $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
			$acceptKey = base64_encode( sha1( $acceptKey, TRUE ) );
			$upgrade   = "HTTP/1.1 101 Switching Protocols\r\n" .
				"Upgrade: websocket\r\n" .
				"Connection: Upgrade\r\n" .
				"Sec-WebSocket-Accept: $acceptKey" .
				"\r\n\r\n";
			$this->console( "Sending this response to the client #{$client->getId()}:\r\n" . $upgrade );
			socket_write( $client->getSocket(), $upgrade );
			$client->setHandshake( TRUE );
			$this->console( "Handshake is successfully done!" );

			return TRUE;
		}
		else
		{
			$this->console( "WebSocket version 13 required (the client supports version {$version})" );

			return FALSE;
		}
	}

	/**
	 * Disconnect a client and close the connection
	 *
	 * @param $socket
	 */
	private function disconnect( $client )
	{
		$this->console( "Disconnecting client #{$client->getId()}" );
		$client->setIsConnected( FALSE );
		$i = array_search( $client, $this->clients );
		$j = array_search( $client->getSocket(), $this->sockets );
		if ( $j >= 0 )
		{
			if ( $client->getSocket() )
			{
				array_splice( $this->sockets, $j, 1 );
				socket_shutdown( $client->getSocket(), 2 );
				socket_close( $client->getSocket() );
				$this->console( "Socket closed" );
			}
		}
		if ( $i >= 0 )
		{
			array_splice( $this->clients, $i, 1 );
		}
		$this->console( "Client #{$client->getId()} disconnected" );
	}

	/**
	 * Get the client associated with the socket
	 *
	 * @param $socket
	 *
	 * @return A client object if found, if not false
	 */
	private function getClientBySocket( $socket )
	{
		foreach ( $this->clients as $client )
		{
			if ( $client->getSocket() == $socket )
			{
				$this->console( "Client found" );

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
	private function action( $client, $action )
	{
		$action = $this->unmask( $action );
		$this->console( "Performing action: " . $action );
		if ( $action == "exit" || $action == "quit" )
		{
			$this->console( "Killing a child process" );
			posix_kill( $client->getPid(), SIGTERM );
			$this->console( "Process {$client->getPid()} is killed!" );
		}
	}

	/**
	 * Run the server
	 */
	public function run()
	{
		$this->console( "Start running..." );

		while ( TRUE )
		{
			$changed_sockets = $this->sockets;
			if ( $changed_sockets )
			{
				@socket_select( $changed_sockets, $write = NULL, $except = NULL, 1 );
				foreach ( $changed_sockets as $socket )
				{
					if ( $socket == $this->master )
					{
						if ( ( $acceptedSocket = socket_accept( $this->master ) ) < 0 )
						{
							$this->console( "Socket error: " . socket_strerror( socket_last_error( $acceptedSocket ) ) );
						}
						else
						{
							$this->connect( $acceptedSocket );
						}
					}
					else
					{
						$this->console( "Finding the socket that associated to the client..." );
						$client = $this->getClientBySocket( $socket );
						if ( $client )
						{
							$this->console( "Receiving data from the client" );
							$data = NULL;
							while ( $bytes = @socket_recv( $socket, $r_data, 2048, MSG_DONTWAIT ) )
							{
								$data .= $r_data;
							}
							if ( ! $client->getHandshake() )
							{
								$this->console( "Doing the handshake" );
								if ( $this->handshake( $client, $data ) )
								{
									$this->startProcess( $client );
								}
								else
								{
									$this->disconnect( $client );
								}
							}
							elseif ( $bytes === 0 )
							{
								$this->disconnect( $client );
							}
							else
							{
								// When received data from client
								$this->action( $client, $data );
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
	 * @param unknown_type $client
	 */
	private function startProcess( $client )
	{
		$this->console( "Start a client process" );
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
				$this->send( $client, "I am waiting {$seconds} seconds" );
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
	private function send( $client, $text )
	{
		$this->console( "Send '" . $text . "' to client #{$client->getId()}" );
		$text = $this->encode( $text );
		if ( socket_write( $client->getSocket(), $text, strlen( $text ) ) === FALSE )
		{
			$this->console( "Unable to write to client #{$client->getId()}'s socket" );
			$this->disconnect( $client );
		}
	}

	/**
	 * Encode a text for sending to clients via ws://
	 *
	 * @param $text
	 * @param $messageType
	 */
	function encode( $message, $messageType = 'text' )
	{
		switch ( $messageType )
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
		$length      = strlen( $message );
		$lengthField = "";
		if ( $length < 126 )
		{
			$b2 = $length;
		}
		elseif ( $length <= 65536 )
		{
			$b2        = 126;
			$hexLength = dechex( $length );
			//$this->stdout("Hex Length: $hexLength");
			if ( strlen( $hexLength ) % 2 == 1 )
			{
				$hexLength = '0' . $hexLength;
			}
			$n = strlen( $hexLength ) - 2;
			for ( $i = $n; $i >= 0; $i = $i - 2 )
			{
				$lengthField = chr( hexdec( substr( $hexLength, $i, 2 ) ) ) . $lengthField;
			}
			while ( strlen( $lengthField ) < 2 )
			{
				$lengthField = chr( 0 ) . $lengthField;
			}
		}
		else
		{
			$b2        = 127;
			$hexLength = dechex( $length );
			if ( strlen( $hexLength ) % 2 == 1 )
			{
				$hexLength = '0' . $hexLength;
			}
			$n = strlen( $hexLength ) - 2;
			for ( $i = $n; $i >= 0; $i = $i - 2 )
			{
				$lengthField = chr( hexdec( substr( $hexLength, $i, 2 ) ) ) . $lengthField;
			}
			while ( strlen( $lengthField ) < 8 )
			{
				$lengthField = chr( 0 ) . $lengthField;
			}
		}

		return chr( $b1 ) . chr( $b2 ) . $lengthField . $message;
	}

	/**
	 * Unmask a received payload
	 *
	 * @param $buffer
	 */
	private function unmask( $payload )
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
	 * @param $text the text to display
	 * @param $exit if true, the process will exit
	 */
	private function console( $text, $exit = FALSE )
	{
		$text = date( '[Y-m-d H:i:s] ' ) . $text . "\r\n";
		if ( $exit )
		{
			die( $text );
		}
		if ( $this->verboseMode )
		{
			echo $text;
		}
	}
}