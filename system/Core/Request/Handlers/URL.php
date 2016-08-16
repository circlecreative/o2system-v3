<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 27-Jul-16
 * Time: 6:53 PM
 */

namespace O2System\Core\Request\Handlers;

class URL
{
	public $is_www     = FALSE;
	public $is_secure  = FALSE;
	public $string;
	public $origin;
	public $scheme;
	public $host;
	public $port       = 80;
	public $user;
	public $pass;
	public $path;
	public $query;
	public $fragment;
	public $attribute;
	public $ip_address;
	public $domain;
	public $subdomain;
	public $subdomains = [ ];
	public $tld;
	public $tlds       = [ ];

	public function __construct()
	{
		$this->domain     = $this->host = isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ];
		$this->origin     = ltrim( $this->domain, 'www.' );
		$this->is_secure  = is_https();
		$this->scheme     = is_https() ? 'https://' : 'http://';
		$this->ip_address = gethostbyname( $this->host );
		$this->query      = @$_SERVER[ 'QUERY_STRING' ];
		$this->string     = $this->scheme . $this->host . $_SERVER[ 'REQUEST_URI' ];

		if ( strpos( $_SERVER[ 'PHP_SELF' ], '/@' ) !== FALSE )
		{
			$x_php_self      = explode( '/@', $_SERVER[ 'PHP_SELF' ] );
			$this->attribute = '@' . $x_php_self[ 1 ];

			if ( strpos( $this->attribute, '/' ) !== FALSE )
			{
				$x_attribute     = explode( '/', $this->attribute );
				$this->attribute = $x_attribute[ 0 ];
			}
		}

		if ( preg_match( "/[a-zA-Z0-9]+[@][a-zA-Z0-9]+/", $_SERVER[ 'PHP_SELF' ], $user_password ) )
		{
			$x_user_password = explode( '@', $user_password[ 0 ] );
			$this->user      = $x_user_password[ 0 ];
			$this->pass      = $x_user_password[ 1 ];
		}

		$this->port = is_https() ? 443 : 80;

		if ( strpos( $this->domain, ':' ) !== FALSE )
		{
			$x_domain     = explode( ':', $this->domain );
			$this->domain = reset( $x_domain );
			$this->port   = end( $x_domain );
		}

		if ( filter_var( $this->domain, FILTER_VALIDATE_IP ) !== FALSE OR strpos( $this->domain, '.' ) === FALSE )
		{
			$x_domain = [ $this->domain ];

			$x_path = explode( '.php', $_SERVER[ 'PHP_SELF' ] );
			$x_path = explode( '/', trim( $x_path[ 0 ], '/' ) );
			array_pop( $x_path );

			$this->path = empty( $x_path ) ? NULL : implode( '/', $x_path );
		}
		else
		{
			$x_domain = explode( '.', $this->domain );
		}

		if ( count( $x_domain ) > 1 )
		{
			$this->is_www = FALSE;
			if ( $x_domain[ 0 ] === 'www' )
			{
				$this->is_www = TRUE;
				array_shift( $x_domain );
			}

			$this->tlds = [ ];
			foreach ( $x_domain as $key => $hostname )
			{
				if ( strlen( $hostname ) <= 3 AND $key >= 1 )
				{
					$this->tlds[] = $hostname;
				}
			}

			if ( empty( $this->tlds ) )
			{
				$this->tlds[] = end( $x_domain );
			}

			$this->tld = '.' . implode( '.', $this->tlds );

			$this->subdomains = array_diff( $x_domain, $this->tlds );
			$this->subdomains = count( $this->subdomains ) == 0 ? $this->tlds : $this->subdomains;

			$this->domain = end( $this->subdomains );
			array_pop( $this->subdomains );

			$this->domain = implode( '.', array_slice( $this->subdomains, 1 ) ) . '.' . $this->domain . $this->tld;
			$this->domain = ltrim( $this->domain, '.' );

			if ( count( $this->subdomains ) > 0 )
			{
				$this->subdomain = reset( $this->subdomains );
			}
		}
		else
		{
			$this->domain = $this->origin;
		}

		$ordinal_ends = [ 'th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th' ];

		foreach ( $this->subdomains as $key => $subdomain )
		{
			$ordinal_number = count( $x_domain ) - $key;

			if ( ( ( $ordinal_number % 100 ) >= 11 ) && ( ( $ordinal_number % 100 ) <= 13 ) )
			{
				$ordinal_key = $ordinal_number . 'th';
			}
			else
			{
				$ordinal_key = $ordinal_number . $ordinal_ends[ $ordinal_number % 10 ];
			}

			$this->subdomains[ $ordinal_key ] = $subdomain;

			unset( $this->subdomains[ $key ] );
		}

		foreach ( $this->tlds as $key => $tld )
		{
			$ordinal_number = count( $this->tlds ) - $key;

			if ( ( ( $ordinal_number % 100 ) >= 11 ) && ( ( $ordinal_number % 100 ) <= 13 ) )
			{
				$ordinal_key = $ordinal_number . 'th';
			}
			else
			{
				$ordinal_key = $ordinal_number . $ordinal_ends[ $ordinal_number % 10 ];
			}

			$this->tlds[ $ordinal_key ] = $tld;

			unset( $this->tlds[ $key ] );
		}

		\O2System::$log->debug( 'LOG_DEBUG_REQUEST_URL_CLASS_INITIALIZED' );
	}

	public function domain( $uri = NULL, $query = [ ], $www = FALSE )
	{
		if ( is_bool( $query ) )
		{
			$www   = $query;
			$query = [ ];
		}

		$this->is_www = $www;

		return $this->__prepareString( $uri, $query );
	}

	public function subdomain( $level = 'AUTO', $uri = NULL, $query = [ ], $www = FALSE )
	{
		$type = 'subdomain';

		if ( is_string( $level ) )
		{
			$type = $level;
		}
		else
		{
			@list( $uri, $query, $www ) = func_get_args();
		}

		if ( is_bool( $query ) )
		{
			$www   = $query;
			$query = [ ];
		}

		$this->is_www = $www;

		return $this->__prepareString( $uri, $query, $type );
	}

	public function origin( $uri = NULL, $query = [ ], $www = FALSE )
	{
		if ( is_bool( $query ) )
		{
			$www   = $query;
			$query = [ ];
		}

		$this->is_www = $www;

		return $this->__prepareString( $uri, $query, 'origin' );
	}

	public function host( $uri = NULL, $query = [ ], $www = FALSE )
	{
		if ( is_bool( $query ) )
		{
			$www   = $query;
			$query = [ ];
		}

		$this->is_www = $www;

		return $this->__prepareString( $uri, $query, 'host' );
	}

	private function __prepareString( $uri = NULL, $query = [ ], $type = 'domain' )
	{
		if ( isset( $uri ) )
		{
			$uri = is_array( $uri ) ? implode( '/', $uri ) : rtrim( $uri, '/' );
		}

		$query = empty( $query ) ? NULL : '?' . http_build_query( $query );

		if ( array_key_exists( $type, $this->subdomains ) )
		{
			$slice      = array_search( $type, array_keys( $this->subdomains ) );
			$subdomains = array_slice( $this->subdomains, $slice );

			$domain = empty( $subdomains ) ? $this->domain : implode( '.', $subdomains ) . '.' . $this->host . $this->tld;
		}
		elseif ( $type === 'domain' )
		{
			$domain = $this->domain;
		}
		elseif ( $type === 'origin' )
		{
			$domain = $this->origin;
		}
		elseif ( $type === 'host' )
		{
			$domain = $this->host . $this->tld;
		}
		elseif ( $type === 'subdomain' )
		{
			$domain = empty( $this->subdomain ) ? $this->domain : $this->subdomain . '.' . $this->domain;
		}
		else
		{
			$domain = $type . $this->domain;
		}

		// Add WWW
		$domain = $this->is_www === TRUE ? 'www.' . $domain : $domain;

		// Add Port
		$domain = in_array( $this->port, [ 80, 443 ] ) ? $domain : $domain . ':' . $this->port;

		// Add Path
		$domain = empty( $this->path ) ? $domain : $domain . '/' . $this->path;

		return $this->scheme . rtrim( $domain . '/' . $uri, '/' ) . ( empty( $query ) ? NULL : '/' . $query );
	}

	public function hasSubdomain( $subdomain = NULL )
	{
		if ( isset( $subdomain ) )
		{
			return (bool) in_array( $subdomain, $this->subdomains );
		}

		return (bool) empty( $this->subdomain ) ? FALSE : TRUE;
	}

	public function getSubdomain( $level )
	{
		if ( array_key_exists( $level, $this->subdomains ) )
		{
			return $this->subdomains[ $level ];
		}

		return FALSE;
	}

	public function isSecure()
	{
		return (bool) is_https();
	}

	public function hasTld( $tld )
	{
		return (bool) in_array( $tld, $this->tlds );
	}

	public function getTld( $level )
	{
		if ( array_key_exists( $level, $this->tlds ) )
		{
			return $this->tlds[ $level ];
		}

		return FALSE;
	}

	public function __toString()
	{
		return (string) $this->origin();
	}
}