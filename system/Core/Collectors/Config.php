<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 7:27 AM
 */

namespace O2System\Core\Collectors;


use O2System\Core\SPL\ArrayAccess;
use O2System\Core\SPL\ArrayIterator;
use O2System\Core\SPL\ArrayObject;

trait Config
{
	/**
	 * List of Config
	 *
	 * @type array
	 */
	protected $config = [ ];

	/**
	 * Set Config
	 *
	 * @access   public
	 *
	 * @param array|string $key
	 *
	 * @return $this
	 */
	public function setConfig( $key, $value = NULL )
	{
		if ( is_array( $this->config ) )
		{
			$this->config = new \O2System\Registry\Metadata\Config( $this->config );
		}

		if ( is_array( $key ) )
		{
			$this->config->merge( $key );
		}
		elseif ( $key instanceof ArrayObject OR $key instanceof ArrayIterator OR $key instanceof ArrayAccess )
		{
			$this->config->merge( $key->getArrayCopy() );
		}
		elseif ( is_string( $key ) )
		{
			if ( array_key_exists( $key, $this->config ) )
			{
				if ( is_array( $value ) AND is_array( $this->config[ $key ] ) )
				{
					$this->config[ $key ] = array_merge( $this->config[ $key ], $value );
				}
				else
				{
					$this->config[ $key ] = $value;
				}
			}
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Add Config
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function addConfig( $key, $value )
	{
		if ( isset( $this->config[ $key ] ) )
		{
			if ( is_array( $value ) AND is_array( $this->config[ $key ] ) )
			{
				$this->config[ $key ] = array_merge( $this->config[ $key ], $value );
			}
			else
			{
				$this->config[ $key ] = $value;
			}
		}

		return $this;
	}

	/**
	 * Get Config
	 *
	 * @access  public
	 * @final   this method can't be overwritten
	 *
	 * @param string|null $key Config item index name
	 *
	 * @return array|null
	 */
	final public function getConfig( $key = NULL, $offset = NULL )
	{
		if ( isset( $key ) )
		{
			if ( isset( $this->config[ $key ] ) )
			{
				if ( isset( $offset ) )
				{
					return isset( $this->config[ $key ][ $offset ] ) ? $this->config[ $key ][ $offset ] : NULL;
				}

				return $this->config[ $key ];
			}

			return FALSE;
		}

		return $this->config;
	}
}