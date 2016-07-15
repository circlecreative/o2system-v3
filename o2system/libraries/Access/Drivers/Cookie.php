<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
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

namespace O2System\Libraries\Access\Drivers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\Interfaces\DriverInterface;
use O2System\Libraries\Access\Metadata\Credentials;
use O2System\Model;
use O2System\Glob\ArrayObject;

/**
 * Cookie Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/access/login.html
 */
class Cookie extends DriverInterface
{
	private $storage = [ ];

	public function getStorage()
	{
		return $this->storage;
	}

	public function setRemember( $credentials, $domain = NULL )
	{
		$domain   = '.' . ( empty( $domain ) ? parse_domain()->domain : ltrim( $domain, '.' ) );
		$lifetime = time() + $this->_library->getConfig( 'remember', 'lifetime' );

		delete_cookie( 'remember' );

		set_cookie(
			'remember',
			$credentials,
			$lifetime,
			$domain
		);

		$this->storage[ 'remember' ] = new ArrayObject(
			[
				'name'     => \O2System::$config[ 'cookie' ][ 'prefix' ] . 'remember',
				'value'    => $credentials,
				'expire'   => $lifetime,
				'domain'   => $domain,
				'path'     => '/',
				'secure'   => \O2System::$config[ 'cookie' ][ 'secure' ],
				'httponly' => \O2System::$config[ 'cookie' ][ 'httponly' ],
			] );
	}

	public function setSso( $credentials, $domain = NULL )
	{
		$domain   = '.' . ( empty( $domain ) ? parse_domain()->domain : ltrim( $domain, '.' ) );
		$lifetime = time() + $this->_library->getConfig( 'sso', 'lifetime' );

		delete_cookie( 'sso' );

		set_cookie(
			'sso',
			$credentials,
			$lifetime,
			$domain
		);

		$this->storage[ 'sso' ] = new ArrayObject(
			[
				'name'     => \O2System::$config[ 'cookie' ][ 'prefix' ] . 'sso',
				'value'    => $credentials,
				'expire'   => $lifetime,
				'domain'   => $domain,
				'path'     => '/',
				'secure'   => \O2System::$config[ 'cookie' ][ 'secure' ],
				'httponly' => \O2System::$config[ 'cookie' ][ 'httponly' ],
			] );
	}

	public function getSso()
	{
		$sso = get_cookie( 'sso' );

		if ( ! empty( $sso ) )
		{
			$sso = $this->_library->encryption->decrypt( $sso );
			$sso = is_serialized( $sso ) ? unserialize( $sso ) : $sso;

			if ( isset( $sso[ 'signature' ] ) )
			{
				if ( $cache = \O2System::Cache()->get( 'sso-' . $sso[ 'signature' ] ) )
				{
					if ( $cache[ 'signature' ] === $sso[ 'signature' ] )
					{
						$sso[ 'credentials' ][ 'signature' ] = $sso[ 'signature' ];

						return $this->storage[ 'sso' ] = new Credentials( $sso[ 'credentials' ] );
					}
				}
			}
		}

		delete_cookie( 'sso' );

		return FALSE;
	}

	public function getRemember()
	{
		$remember = get_cookie( 'remember' );

		if ( ! empty( $remember ) )
		{
			$remember = $this->_library->encryption->decrypt( $remember );
			$remember = is_serialized( $remember ) ? unserialize( $remember ) : $remember;

			if ( isset( $remember[ 'signature' ] ) )
			{
				if ( $cache = \O2System::Cache()->get( 'remember-' . $remember[ 'signature' ] ) )
				{
					if ( $cache[ 'signature' ] === $remember[ 'signature' ] )
					{
						$remember[ 'credentials' ][ 'signature' ] = $remember[ 'signature' ];

						return $this->storage[ 'remember' ] = new Credentials( $remember[ 'credentials' ] );
					}
				}
			}
		}

		delete_cookie( 'remember' );

		return FALSE;
	}

	public function destroy()
	{
		foreach ( [ 'remember', 'sso' ] as $cookie_name )
		{
			delete_cookie( $cookie_name );
		}
	}
}