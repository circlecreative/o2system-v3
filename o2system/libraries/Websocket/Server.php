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
	 * Server Stream Socket
	 *
	 * @type    resource
	 */
	protected $_stream_socket;
	
	/**
	 * Server Stream Context
	 *
	 * @type    resource
	 */
	protected $_stream_context;
	
	/**
	 * Server Stream SSL
	 *
	 * @type bool
	 */
	protected $_stream_ssl = FALSE;
	
	/**
	 * Server Certificate
	 *
	 * @type array
	 */
	protected $_certificate = NULL;
	
	protected $_sockets     = array();
	protected $_ips         = array();
	protected $_channels    = array();
	protected $_connections = array();
	protected $_requests    = array();
	
	// server settings:
	public $validate_origin  = TRUE;
	protected $_allowed_origins = array();
	protected $_max_connections = 5;
	protected $_max_clients     = 30;
	protected $_max_requests    = 50;
	
	
	public function __construct( $host = 'localhost', $port = 8000, $openssl = FALSE )
	{
		ob_implicit_flush( TRUE );
		$this->_stream_ssl = $openssl;
		$this->_create_socket( $host, $port );
	}
	
	/**
	 * Set whether the client origin should be checked on new connections.
	 *
	 * @param bool $validate
	 *
	 * @return bool True if value could validated and set successfully.
	 */
	public function set_validate_origin( $validate )
	{
		if ( is_bool( $validate ) )
		{
			$this->validate_origin = $validate;
		}
		
		return $this;
	}
	
	/**
	 * Sets how many clients are allowed to connect to server until no more
	 * connections are accepted.
	 *
	 * @param in $max Max. total connections to server.
	 *
	 * @return bool True if value could be set.
	 */
	public function set_max_clients( $limit )
	{
		if ( is_int( $limit ) )
		{
			$this->_max_clients = (int) $limit;
		}
		
		return $this;
	}
	
	/**
	 * Sets value for the max. connection per ip to this server.
	 *
	 * @param int $limit Connection limit for an ip.
	 *
	 * @return bool True if value could be set.
	 */
	public function set_max_connections( $limit )
	{
		if ( is_int( $limit ) )
		{
			$this->_max_connections = $limit;
		}
		
		return $this;
	}
	
	/**
	 * Sets how many requests a client is allowed to do per minute.
	 *
	 * @param int $limit Requets/Min limit (per client).
	 *
	 * @return bool True if value could be set.
	 */
	public function set_max_requests( $limit )
	{
		if ( is_int( $limit ) )
		{
			$this->_max_requests = $limit;
		}
		
		return $this;
	}
	
	/**
	 * Adds a new channel object to the channel storage.
	 *
	 * @param string $key     Name of channel.
	 * @param object $channel The channel object.
	 */
	public function register_channel( $key, Channel $channel )
	{
		$this->_channels[ $key ] = $channel;
		
		// status is kind of a system-app, needs some special cases:
		if ( $key === 'status' )
		{
			$info = array(
				'max_clients'     => $this->_max_clients,
				'max_connections' => $this->_max_connections,
				'max_requests'    => $this->_max_requests,
			);
			
			$this->_channels[ $key ]->set_info( $info );
		}
	}
	
	/**
	 * Create a socket on given host/port
	 *
	 * @param string $host The host/bind address to use
	 * @param int    $port The actual port to bind on
	 */
	protected function _create_socket( $host, $port )
	{
		$protocol = ( $this->_stream_ssl === TRUE ) ? 'tls://' : 'tcp://';
		$url = $protocol . $host . ':' . $port;
		$this->_stream_context = stream_context_create();
		
		if ( $this->_stream_ssl === TRUE )
		{
			$this->_set_stream_ssl();
		}
		if ( ! $this->_stream_socket = stream_socket_server( $url, $errno, $err, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $this->_stream_context ) )
		{
			die( 'Error creating socket: ' . $err );
		}
		
		$this->_sockets[] = $this->_stream_socket;
	}
	
	protected function _set_stream_ssl()
	{
		// Generate PEM file
		if ( ! file_exists( $filepath = \O2System::$config[ 'upload' ] . 'files' . DIRECTORY_SEPARATOR . 'server.pem' ) )
		{
			$csr = array(
				"countryName"            => "DE",
				"stateOrProvinceName"    => "none",
				"localityName"           => "none",
				"organizationName"       => "none",
				"organizationalUnitName" => "none",
				"commonName"             => "foo.lh",
				"emailAddress"           => "baz@foo.lh",
			);

			$pkey = openssl_pkey_new();
			$cert = openssl_csr_new( $csr, $pkey );
			$cert = openssl_csr_sign( $cert, NULL, $pkey, 365 );

			$pem = array();
			openssl_x509_export( $cert, $pem[ 0 ] );
			openssl_pkey_export( $pkey, $pem[ 1 ], 'o2system' );
			$pem = implode( $pem );

			file_put_contents( $filepath, $pem );
		}
		
		// apply ssl context:
		stream_context_set_option( $this->_stream_context, 'ssl', 'local_cert', $filepath );
		stream_context_set_option( $this->_stream_context, 'ssl', 'passphrase', 'o2system' );
		stream_context_set_option( $this->_stream_context, 'ssl', 'allow_self_signed', TRUE );
		stream_context_set_option( $this->_stream_context, 'ssl', 'verify_peer', FALSE );
	}

	protected function _read_buffer( $stream )
	{
		if ( $this->_stream_ssl === TRUE )
		{
			$buffer = fread( $stream, 8192 );
			// extremely strange chrome behavior: first frame with ssl only contains 1 byte?!
			if ( strlen( $buffer ) === 1 )
			{
				$buffer .= fread( $stream, 8192 );
			}
			
			return $buffer;
		}
		else
		{
			$buffer = '';
			$size = 8192;
			$metadata[ 'unread_bytes' ] = 0;
			
			while ( $metadata[ 'unread_bytes' ] > 0 )
			{
				if ( feof( $stream ) )
				{
					return FALSE;
				}
				
				$result = fread( $stream, $size );
				
				if ( $result === FALSE || feof( $stream ) )
				{
					return FALSE;
				}
				
				$buffer .= $result;
				$metadata = stream_get_meta_data( $stream );
				$size = ( $metadata[ 'unread_bytes' ] > $size ) ? $size : $metadata[ 'unread_bytes' ];
			}
			
			return $buffer;
		}
	}

	public function write_buffer( $stream, $string )
	{
		$length = strlen( $string );
		
		for ( $written = 0; $written < $length; $written += $fwrite )
		{
			$fwrite = @fwrite( $stream, substr( $string, $written ) );
			
			if ( $fwrite === FALSE )
			{
				return FALSE;
			}
			elseif ( $fwrite === 0 )
			{
				return FALSE;
			}
		}
		
		return $written;
	}
	
	/**
	 * Main server method. Listens for connections, handles connectes/disconnectes, e.g.
	 */
	public function run()
	{
		while ( TRUE )
		{
			$changed_sockets = $this->_sockets;
			@stream_select( $changed_sockets, $write = NULL, $except = NULL, 0, 5000 );
			
			foreach ( $changed_sockets as $socket )
			{
				if ( $socket == $this->_stream_socket )
				{
					if ( ( $stream_socket = stream_socket_accept( $this->_stream_socket ) ) === FALSE )
					{
						$this->send_message( 'Socket error: ' . socket_strerror( socket_last_error( $stream_socket ) ) );
						continue;
					}
					else
					{
						$connection = new Connection( $this, $stream_socket );
						$this->_connections[ (int) $stream_socket ] = $connection;
						$this->_sockets[] = $stream_socket;
						
						if ( count( $this->_connections ) > $this->_max_clients )
						{
							$connection->on_disconnect();
							
							if ( $this->get_channel( 'status' ) !== FALSE )
							{
								$this->get_channel( 'status' )->statusMsg( 'Attention: Client Limit Reached!', 'warning' );
							}

							continue;
						}
						
						$this->_store_ip( $connection->ip_address );

						if ( $this->is_reached_max_connections( $connection->ip_address ) === FALSE )
						{
							$connection->on_disconnect();
							
							if ( $this->get_channel( 'status' ) !== FALSE )
							{
								$this->get_channel( 'status' )->send_status( 'Connection/Ip limit for ip ' . $connection->ip_address . ' was reached!', 'warning' );
							}
							continue;
						}
					}
				}
				else
				{
					$connection = $this->_connections[ (int) $socket ];

					if ( $connection instanceof Connection )
					{
						unset( $this->_connections[ (int) $socket ] );
						continue;
					}

					$data = $this->_read_buffer( $socket );
					$bytes = strlen( $data );
					
					if ( $bytes === 0 )
					{
						$connection->on_disconnect();
						continue;
					}
					elseif ( $data === FALSE )
					{
						$this->remove_error_connection( $connection );
						continue;
					}
					elseif ( $connection->waitingForData === FALSE && $this->is_reached_max_requests( $connection->conn_id ) === FALSE )
					{
						$connection->on_disconnect();
					}
					else
					{
						$connection->on_data( $data );
					}
				}
			}
		}
	}
	
	/**
	 * Returns a server channel.
	 *
	 * @param string $key Name of channel.
	 *
	 * @return object The channel object.
	 */
	public function get_channel( $key )
	{
		if ( empty( $key ) )
		{
			return FALSE;
		}
		if ( array_key_exists( $key, $this->_channels ) )
		{
			return $this->_channels[ $key ];
		}
		
		return FALSE;
	}
	
	
	/**
	 * Echos a message to standard output.
	 *
	 * @param string $message Message to display.
	 * @param string $type    Type of message.
	 */
	public function log( $message, $type = 'info' )
	{
		echo date( 'Y-m-d H:i:s' ) . ' [' . ( $type ? $type : 'error' ) . '] ' . $message . PHP_EOL;
	}
	
	/**
	 * Removes a client from client storage.
	 *
	 * @param Object $connection Client object.
	 */
	public function remove_closed_connection( $connection )
	{
		$this->_remove_ip( $connection->ip_address );

		if ( isset( $this->_requests[ $connection->conn_id ] ) )
		{
			unset( $this->_requests[ $connection->conn_id ] );
		}
		unset( $this->_connections[ (int) $connection->socket ] );
		
		// trigger status channel:
		if ( $this->get_channel( 'status' ) !== FALSE )
		{
			$this->get_channel( 'status' )->client_disconnect( $connection->ip_address, $connection->port );
		}

		if( $index = array_search( $connection->socket, $this->_sockets ))
		{
			unset( $this->_sockets[ $index ] );
		}

		unset( $connection );
	}
	
	/**
	 * Removes a client and all references in case of timeout/error.
	 *
	 * @param object $connection The connection object to remove.
	 */
	public function remove_error_connection( Connection $connection )
	{
		// remove reference in connection channel:
		if ( isset( $connection->channel ) )
		{
			$connection->channel->on_disconnect( $connection );
		}

		$this->_remove_ip( $connection->ip_address );

		if ( isset( $this->_requests[ $connection->conn_id ] ) )
		{
			unset( $this->_requests[ $connection->conn_id ] );
		}
		unset( $this->_connections[ (int) $connection->socket ] );

		// trigger status channel:
		if ( $this->get_channel( 'status' ) !== FALSE )
		{
			$this->get_channel( 'status' )->client_disconnect( $connection->ip_address, $connection->port );
		}

		if( $index = array_search( $connection->socket, $this->_sockets ))
		{
			unset( $this->_sockets[ $index ] );
		}

		unset( $connection );
	}
	
	/**
	 * Checks if the submitted origin (part of websocket handshake) is allowed
	 * to connect. Allowed origins can be set at server startup.
	 *
	 * @param string $domain The origin-domain from websocket handshake.
	 *
	 * @return bool If domain is allowed to connect method returns true.
	 */
	public function is_allowed_origin( $domain )
	{
		return (bool) isset( $this->_allowed_origins[ $domain ] );
	}
	
	/**
	 * Adds a new ip to ip storage.
	 *
	 * @param string $ip An ip address.
	 */
	protected function _store_ip( $ip )
	{
		if ( isset( $this->_ips[ $ip ] ) )
		{
			$this->_ips[ $ip ]++;
		}
		else
		{
			$this->_ips[ $ip ] = 1;
		}
	}
	
	/**
	 * Removes an ip from ip storage.
	 *
	 * @param string $ip An ip address.
	 *
	 * @return bool True if ip could be removed.
	 */
	protected function _remove_ip( $ip )
	{
		if ( ! isset( $this->_ips[ $ip ] ) )
		{
			return FALSE;
		}
		if ( $this->_ips[ $ip ] === 1 )
		{
			unset( $this->_ips[ $ip ] );
			
			return TRUE;
		}
		$this->_ips[ $ip ]--;
		
		return TRUE;
	}
	
	/**
	 * Checks if an ip has reached the maximum connection limit.
	 *
	 * @param string $ip An ip address.
	 *
	 * @return bool False if ip has reached max. connection limit. True if connection is allowed.
	 */
	public function is_reached_max_connections( $ip )
	{
		if ( empty( $ip ) )
		{
			return FALSE;
		}
		if ( ! isset ( $this->_ips[ $ip ] ) )
		{
			return TRUE;
		}
		
		return ( $this->_ips[ $ip ] > $this->_max_connections ) ? FALSE : TRUE;
	}
	
	/**
	 * Checkes if a client has reached its max. requests per minute limit.
	 *
	 * @param string $conn_id A client id. (unique client identifier)
	 *
	 * @return bool True if limit is not yet reached. False if request limit is reached.
	 */
	public function is_reached_max_requests( $conn_id )
	{
		// no data in storage - no danger:
		if ( ! isset( $this->_requests[ $conn_id ] ) )
		{
			$this->_requests[ $conn_id ] = array(
				'lastRequest'   => time(),
				'totalRequests' => 1,
			);
			
			return TRUE;
		}
		
		// time since last request > 1min - no danger:
		if ( time() - $this->_requests[ $conn_id ][ 'lastRequest' ] > 60 )
		{
			$this->_requests[ $conn_id ] = array(
				'lastRequest'   => time(),
				'totalRequests' => 1,
			);
			
			return TRUE;
		}
		
		// did requests in last minute - check limits:
		if ( $this->_requests[ $conn_id ][ 'totalRequests' ] > $this->_max_requests )
		{
			return FALSE;
		}
		
		$this->_requests[ $conn_id ][ 'totalRequests' ]++;
		
		return TRUE;
	}
	
	/**
	 * Adds a domain to the allowed origin storage.
	 *
	 * @param sting $domain A domain name from which connections to server are allowed.
	 *
	 * @return bool True if domain was added to storage.
	 */
	public function set_origin( $domain )
	{
		$domain = str_replace( 'http://', '', $domain );
		$domain = str_replace( 'www.', '', $domain );
		$domain = ( strpos( $domain, '/' ) !== FALSE ) ? substr( $domain, 0, strpos( $domain, '/' ) ) : $domain;
		
		if ( ! empty( $domain ) )
		{
			$this->_allowed_origins[ $domain ] = TRUE;
		}
		
		return $this;
	}

	public function send_message( $message, $type = 'INFO', $vars = array() )
	{
		$message = \O2System::$language->line( $message );

		if ( ! empty( $vars ) )
		{
			array_unshift( $vars, $message );

			$message = call_user_func_array( 'sprintf', $vars );
		}

		if ( defined( 'CONSOLE_PATH' ) )
		{
			echo ' ::: [ ' . strtoupper( $type ) . ' ] ' . $message . PHP_EOL;
			time_nanosleep( 0, 200000000 );
		}
	}
}