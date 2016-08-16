<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 28-Jul-16
 * Time: 12:29 AM
 */

namespace O2System\Core\Request\Collections;

use O2System\Core\SPL\ArrayAccess;

class Headers extends ArrayAccess
{
	public function __construct()
	{
		// In Apache, you can simply call apache_request_headers()
		if ( function_exists( 'apache_request_headers' ) )
		{
			$this->storage = apache_request_headers();
		}

		$this->storage[ 'Content-Type' ] = isset( $_SERVER[ 'CONTENT_TYPE' ] ) ? $_SERVER[ 'CONTENT_TYPE' ] : @getenv( 'CONTENT_TYPE' );

		foreach ( $_SERVER as $key => $val )
		{
			if ( sscanf( $key, 'HTTP_%s', $header ) === 1 )
			{
				// take SOME_HEADER and turn it into Some-Header
				$header = str_replace( '_', ' ', strtolower( $header ) );
				$header = str_replace( ' ', '-', ucwords( $header ) );

				$this->storage[ $header ] = \O2System::$request->getServer( $key );
			}
		}
	}

	protected function __prepareOffset( $offset )
	{
		// take SOME_HEADER and turn it into Some-Header
		$header = str_replace( [ '-', '_' ], ' ', strtolower( $offset ) );
		$header = str_replace( ' ', '-', ucwords( $header ) );

		return $header;
	}

	public function offsetExists( $offset )
	{
		$offset = $this->__prepareOffset( $offset );

		return parent::offsetExists( $offset );
	}

	public function offsetGet( $offset )
	{
		$offset = $this->__prepareOffset( $offset );

		return parent::offsetGet( $offset );
	}

	public function offsetFilter($offset, $filter = NULL)
	{
		if ( $this->offsetExists( $offset ) )
		{
			$var = $this->offsetGet( $offset );

			if ( is_array( $var ) AND is_array( $filter ) )
			{
				return filter_var_array( $offset, $filter );
			}

			if ( isset( $filter ) )
			{
				return filter_var( $var, $filter );
			}

			return $var;
		}

		return NULL;
	}
}
