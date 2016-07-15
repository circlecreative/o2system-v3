<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 30-Jun-16
 * Time: 11:44 AM
 */

namespace O2System\Metadata;


use O2System\Glob\ArrayObject;

class Domain extends ArrayObject
{
	public function __construct( $domain = NULL )
	{
		if ( isset( $domain ) )
		{
			if ( is_array( $domain ) )
			{
				parent::__construct( $domain );
			}
			elseif ( is_string( $domain ) )
			{
				$domain = parse_domain( $domain );
				parent::__construct( $domain );
			}
		}
		elseif ( is_cli() === FALSE )
		{
			$domain = parse_domain()->__toArray();
			parent::__construct( $domain );
		}
		else
		{
			$domain = parse_domain( \O2System::$config[ 'base_url' ] )->__toArray();
			parent::__construct( $domain );
		}
	}

	public function url( $uri = NULL, $query = [ ], $www = FALSE )
	{
		if ( is_bool( $query ) )
		{
			$www   = $query;
			$query = [ ];
		}

		$this->www = $www;

		return $this->__prepUrl( $uri, $query );
	}

	public function subdomainUrl( $level = 'AUTO', $uri = NULL, $query = [ ], $www = FALSE )
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

		$this->www = $www;

		return $this->__prepUrl( $uri, $query, $type );
	}

	public function originUrl( $uri = NULL, $query = [ ], $www = FALSE )
	{
		if ( is_bool( $query ) )
		{
			$www   = $query;
			$query = [ ];
		}

		$this->www = $www;

		return $this->__prepUrl( $uri, $query, 'origin' );
	}

	public function hostUrl( $uri = NULL, $query = [ ], $www = FALSE )
	{
		if ( is_bool( $query ) )
		{
			$www   = $query;
			$query = [ ];
		}

		$this->www = $www;

		return $this->__prepUrl( $uri, $query, 'host' );
	}

	private function __prepUrl( $uri = NULL, $query = [ ], $type = 'domain' )
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
		$domain = $this->www === TRUE ? 'www.' . $domain : $domain;

		// Add Port
		$domain = $this->port == 80 ? $domain : $domain . ':' . $this->port;

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

	public function isHttps()
	{
		return (bool) $this->scheme === 'https://' ? TRUE : FALSE;
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
}