<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12-Jul-16
 * Time: 1:29 PM
 */

namespace O2System\Libraries\OAuth;

class Consumer
{
	public $key;
	public $secret;

	function __construct( $key, $secret, $callback_url = NULL )
	{
		$this->key          = $key;
		$this->secret       = $secret;
		$this->callback_url = $callback_url;
	}

	function __toString()
	{
		return "OAuthConsumer[key=$this->key,secret=$this->secret]";
	}
}