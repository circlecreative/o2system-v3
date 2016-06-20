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
	private $id;
	private $socket;
	private $handshake;
	private $pid;
	private $isConnected;

	public function __construct( $id, $socket )
	{
		$this->id          = $id;
		$this->socket      = $socket;
		$this->handshake   = FALSE;
		$this->pid         = NULL;
		$this->isConnected = TRUE;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getSocket()
	{
		return $this->socket;
	}

	public function getHandshake()
	{
		return $this->handshake;
	}

	public function getPid()
	{
		return $this->pid;
	}

	public function isConnected()
	{
		return $this->isConnected;
	}

	public function setId( $id )
	{
		$this->id = $id;
	}

	public function setSocket( $socket )
	{
		$this->socket = $socket;
	}

	public function setHandshake( $handshake )
	{
		$this->handshake = $handshake;
	}

	public function setPid( $pid )
	{
		$this->pid = $pid;
	}

	public function setIsConnected( $isConnected )
	{
		$this->isConnected = $isConnected;
	}
}