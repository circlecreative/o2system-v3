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

namespace O2System\Libraries;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Exception;

/**
 * Class Encryption
 *
 * Provides two-way keyed encryption via PHP's MCrypt and/or OpenSSL extensions.
 *
 * @author  Andrey Andreev
 * @package O2System\Libraries
 */
class Encryption
{
	/**
	 * Encryption cipher
	 *
	 * @var string
	 */
	protected $_cipher = 'aes-128';

	/**
	 * Cipher mode
	 *
	 * @var string
	 */
	protected $_mode = 'cbc';

	/**
	 * Cipher handle
	 *
	 * @var mixed
	 */
	protected $_handle;

	/**
	 * Encryption key
	 *
	 * @var string
	 */
	protected $_key;

	/**
	 * PHP extension to be used
	 *
	 * @var string
	 */
	protected $_driver;

	/**
	 * List of usable drivers (PHP extensions)
	 *
	 * @var array
	 */
	protected $_drivers = [ ];

	/**
	 * List of available modes
	 *
	 * @var array
	 */
	protected $_modes = [
		'mcrypt'  => [
			'cbc'    => 'cbc',
			'ecb'    => 'ecb',
			'ofb'    => 'nofb',
			'ofb8'   => 'ofb',
			'cfb'    => 'ncfb',
			'cfb8'   => 'cfb',
			'ctr'    => 'ctr',
			'stream' => 'stream',
		],
		'openssl' => [
			'cbc'    => 'cbc',
			'ecb'    => 'ecb',
			'ofb'    => 'ofb',
			'cfb'    => 'cfb',
			'cfb8'   => 'cfb8',
			'ctr'    => 'ctr',
			'stream' => '',
			'xts'    => 'xts',
		],
	];

	/**
	 * List of supported HMAC algorightms
	 *
	 * name => digest size pairs
	 *
	 * @var array
	 */
	protected $_digests = [
		'sha224' => 28,
		'sha256' => 32,
		'sha384' => 48,
		'sha512' => 64,
	];

	/**
	 * mbstring.func_override flag
	 *
	 * @var bool
	 */
	protected static $func_override;

	// --------------------------------------------------------------------

	/**
	 * Encryption constructor.
	 *
	 * @param array $params
	 */
	public function __construct( array $params = [ ] )
	{
		$this->_drivers = [
			'mcrypt'  => defined( 'MCRYPT_DEV_URANDOM' ),
			// While OpenSSL is available for PHP 5.3.0, an IV parameter
			// for the encrypt/decrypt functions is only available since 5.3.3
			'openssl' => ( is_php( '5.3.3' ) && extension_loaded( 'openssl' ) ),
		];

		if ( ! $this->_drivers[ 'mcrypt' ] && ! $this->_drivers[ 'openssl' ] )
		{
			throw new Exception( 'E_ENCRYPTION_UNABLE_TO_FIND_DRIVER', 115 );
		}

		isset( self::$func_override ) OR self::$func_override = ( extension_loaded( 'mbstring' ) && ini_get( 'mbstring.func_override' ) );
		$this->initialize( $params );

		if ( ! isset( $this->_key ) && self::strlen( $key = \O2System::$config[ 'encryption_key' ] ) > 0 )
		{
			$this->_key = $key;
		}

		\O2System::$log->debug( 'Encryption Class Initialized' );
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize
	 *
	 * @param   array $params Configuration parameters
	 *
	 * @return  Encryption
	 */
	public function initialize( array $params )
	{
		if ( ! empty( $params[ 'driver' ] ) )
		{
			if ( isset( $this->_drivers[ $params[ 'driver' ] ] ) )
			{
				if ( $this->_drivers[ $params[ 'driver' ] ] )
				{
					$this->_driver = $params[ 'driver' ];
				}
				else
				{
					\O2System::$log->error( 'LOG_ERROR_ENCRYPTION_DRIVER_NOT_AVAILABLE', [ $params[ 'driver' ] ] );
				}
			}
			else
			{
				\O2System::$log->error( 'LOG_ERROR_ENCRYPTION_DRIVER_UNKNOWN_CONFIGURED', [ $params[ 'driver' ] ] );
			}
		}

		if ( empty( $this->_driver ) )
		{
			$this->_driver = ( $this->_drivers[ 'openssl' ] === TRUE )
				? 'openssl'
				: 'mcrypt';

			\O2System::$log->debug( 'LOG_DEBUG_ENCRYPTION_DRIVER_AUTO_CONFIGURED' );
		}

		empty( $params[ 'cipher' ] ) && $params[ 'cipher' ] = $this->_cipher;
		empty( $params[ 'key' ] ) OR $this->_key = $params[ 'key' ];

		$this->{'_' . camelcase( $this->_driver . 'Initialize' )}( $params );

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize MCrypt
	 *
	 * @param   array $params Configuration parameters
	 *
	 * @return  void
	 */
	protected function _mcryptInitialize( $params )
	{
		if ( ! empty( $params[ 'cipher' ] ) )
		{
			$params[ 'cipher' ] = strtolower( $params[ 'cipher' ] );
			$this->_cipherAlias( $params[ 'cipher' ] );

			if ( ! in_array( $params[ 'cipher' ], mcrypt_list_algorithms(), TRUE ) )
			{
				\O2System::$log->error( 'LOG_ERROR_ENCRYPTION_MCRYPT_CIPHER_NOT_AVAILABLE', [ strtoupper( $params[ 'cipher' ] ) ] );
			}
			else
			{
				$this->_cipher = $params[ 'cipher' ];
			}
		}

		if ( ! empty( $params[ 'mode' ] ) )
		{
			$params[ 'mode' ] = strtolower( $params[ 'mode' ] );
			if ( ! isset( $this->_modes[ 'mcrypt' ][ $params[ 'mode' ] ] ) )
			{
				\O2System::$log->error( 'LOG_ERROR_ENCRYPTION_MCRYPT_MODE_NOT_AVAILABLE', [ strtoupper( $params[ 'mode' ] ) ] );
			}
			else
			{
				$this->_mode = $this->_modes[ 'mcrypt' ][ $params[ 'mode' ] ];
			}
		}

		if ( isset( $this->_cipher, $this->_mode ) )
		{
			if ( is_resource( $this->_handle )
				&& ( strtolower( mcrypt_enc_get_algorithms_name( $this->_handle ) ) !== $this->_cipher
					OR strtolower( mcrypt_enc_get_modes_name( $this->_handle ) ) !== $this->_mode )
			)
			{
				mcrypt_module_close( $this->_handle );
			}

			if ( $this->_handle = mcrypt_module_open( $this->_cipher, '', $this->_mode, '' ) )
			{
				\O2System::$log->debug( 'LOG_DEBUG_ENCRYPTION_MCRYPT_INITIALIZED', [ strtoupper( $this->_cipher ), strtoupper( $this->_mode ) ] );
			}
			else
			{
				\O2System::$log->error( 'LOG_ERROR_ENCRYPTION_MCRYPT_UNABLE_TO_INITIALIZE' . [ strtoupper( $this->_cipher ), strtoupper( $this->_mode ) ] );
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize OpenSSL
	 *
	 * @param   array $params Configuration parameters
	 *
	 * @return  void
	 */
	protected function _opensslInitialize( $params )
	{
		if ( ! empty( $params[ 'cipher' ] ) )
		{
			$params[ 'cipher' ] = strtolower( $params[ 'cipher' ] );
			$this->_cipherAlias( $params[ 'cipher' ] );
			$this->_cipher = $params[ 'cipher' ];
		}

		if ( ! empty( $params[ 'mode' ] ) )
		{
			$params[ 'mode' ] = strtolower( $params[ 'mode' ] );
			if ( ! isset( $this->_modes[ 'openssl' ][ $params[ 'mode' ] ] ) )
			{
				\O2System::$log->error( 'LOG_ERROR_ENCRYPTION_OPENSSL_MODE_NOT_AVAILABLE', [ strtoupper( $params[ 'mode' ] ) ] );
			}
			else
			{
				$this->_mode = $this->_modes[ 'openssl' ][ $params[ 'mode' ] ];
			}
		}

		if ( isset( $this->_cipher, $this->_mode ) )
		{
			// This is mostly for the stream mode, which doesn't get suffixed in OpenSSL
			$handle = empty( $this->_mode )
				? $this->_cipher
				: $this->_cipher . '-' . $this->_mode;

			if ( ! in_array( $handle, openssl_get_cipher_methods(), TRUE ) )
			{
				$this->_handle = NULL;
				\O2System::$log->error( 'LOG_ERROR_ENCRYPTION_OPENSSL_UNABLE_TO_INITIALIZE', [ strtoupper( $handle ) ] );
			}
			else
			{
				$this->_handle = $handle;
				\O2System::$log->debug( 'LOG_DEBUG_ENCRYPTION_OPENSSL_INITIALIZE', [ strtoupper( $handle ) ] );
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Create a random key
	 *
	 * @param   int $length Output length
	 *
	 * @return  string
	 */
	public function createKey( $length )
	{
		return ( $this->_driver === 'mcrypt' )
			? mcrypt_create_iv( $length, MCRYPT_DEV_URANDOM )
			: openssl_random_pseudo_bytes( $length );
	}

	// --------------------------------------------------------------------

	/**
	 * Encrypt
	 *
	 * @param   string $data   Input data
	 * @param   array  $params Input parameters
	 *
	 * @return  string
	 */
	public function encrypt( $data, array $params = NULL )
	{
		if ( ( $params = $this->_getParams( $params ) ) === FALSE )
		{
			return FALSE;
		}

		isset( $params[ 'key' ] ) OR $params[ 'key' ] = $this->hkdf( $this->_key, 'sha512', NULL, self::strlen( $this->_key ), 'encryption' );

		if ( ( $data = $this->{'_' . camelcase( $this->_driver . '_encrypt' )}( $data, $params ) ) === FALSE )
		{
			return FALSE;
		}

		$params[ 'base64' ] && $data = $this->base64Encode( $data );

		if ( isset( $params[ 'hmac_digest' ] ) )
		{
			isset( $params[ 'hmac_key' ] ) OR $params[ 'hmac_key' ] = $this->hkdf( $this->_key, 'sha512', NULL, NULL, 'authentication' );

			return hash_hmac( $params[ 'hmac_digest' ], $data, $params[ 'hmac_key' ], ! $params[ 'base64' ] ) . $data;
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Encrypt via MCrypt
	 *
	 * @param   string $data   Input data
	 * @param   array  $params Input parameters
	 *
	 * @return  string
	 */
	protected function _mcryptEncrypt( $data, $params )
	{
		if ( ! is_resource( $params[ 'handle' ] ) )
		{
			return FALSE;
		}

		// The greater-than-1 comparison is mostly a work-around for a bug,
		// where 1 is returned for ARCFour instead of 0.
		$iv = ( ( $iv_size = mcrypt_enc_get_iv_size( $params[ 'handle' ] ) ) > 1 )
			? mcrypt_create_iv( $iv_size, MCRYPT_DEV_URANDOM )
			: NULL;

		if ( mcrypt_generic_init( $params[ 'handle' ], $params[ 'key' ], $iv ) < 0 )
		{
			if ( $params[ 'handle' ] !== $this->_handle )
			{
				mcrypt_module_close( $params[ 'handle' ] );
			}

			return FALSE;
		}

		// Use PKCS#7 padding in order to ensure compatibility with OpenSSL
		// and other implementations outside of PHP.
		if ( in_array( strtolower( mcrypt_enc_get_modes_name( $params[ 'handle' ] ) ), [ 'cbc', 'ecb' ], TRUE ) )
		{
			$block_size = mcrypt_enc_get_block_size( $params[ 'handle' ] );
			$pad        = $block_size - ( self::strlen( $data ) % $block_size );
			$data .= str_repeat( chr( $pad ), $pad );
		}

		// Work-around for yet another strange behavior in MCrypt.
		//
		// When encrypting in ECB mode, the IV is ignored. Yet
		// mcrypt_enc_get_iv_size() returns a value larger than 0
		// even if ECB is used AND mcrypt_generic_init() complains
		// if you don't pass an IV with length equal to the said
		// return value.
		//
		// This probably would've been fine (even though still wasteful),
		// but OpenSSL isn't that dumb and we need to make the process
		// portable, so ...
		$data = ( mcrypt_enc_get_modes_name( $params[ 'handle' ] ) !== 'ECB' )
			? $iv . mcrypt_generic( $params[ 'handle' ], $data )
			: mcrypt_generic( $params[ 'handle' ], $data );

		mcrypt_generic_deinit( $params[ 'handle' ] );
		if ( $params[ 'handle' ] !== $this->_handle )
		{
			mcrypt_module_close( $params[ 'handle' ] );
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Encrypt via OpenSSL
	 *
	 * @param   string $data   Input data
	 * @param   array  $params Input parameters
	 *
	 * @return  string
	 */
	protected function _opensslEncrypt( $data, $params )
	{
		if ( empty( $params[ 'handle' ] ) )
		{
			return FALSE;
		}

		$iv = ( $iv_size = openssl_cipher_iv_length( $params[ 'handle' ] ) )
			? openssl_random_pseudo_bytes( $iv_size )
			: NULL;

		$data = openssl_encrypt(
			$data,
			$params[ 'handle' ],
			$params[ 'key' ],
			1, // DO NOT TOUCH!
			$iv
		);

		if ( $data === FALSE )
		{
			return FALSE;
		}

		return $iv . $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt
	 *
	 * @param   string $data   Encrypted data
	 * @param   array  $params Input parameters
	 *
	 * @return  string
	 */
	public function decrypt( $data, array $params = NULL )
	{
		if ( ( $params = $this->_getParams( $params ) ) === FALSE )
		{
			return FALSE;
		}

		if ( isset( $params[ 'hmac_digest' ] ) )
		{
			// This might look illogical, but it is done during encryption as well ...
			// The 'base64' value is effectively an inverted "raw data" parameter
			$digest_size = ( $params[ 'base64' ] )
				? $this->_digests[ $params[ 'hmac_digest' ] ] * 2
				: $this->_digests[ $params[ 'hmac_digest' ] ];

			if ( self::strlen( $data ) <= $digest_size )
			{
				return FALSE;
			}

			$hmac_input = self::substr( $data, 0, $digest_size );
			$data       = self::substr( $data, $digest_size );

			isset( $params[ 'hmac_key' ] ) OR $params[ 'hmac_key' ] = $this->hkdf( $this->_key, 'sha512', NULL, NULL, 'authentication' );
			$hmac_check = hash_hmac( $params[ 'hmac_digest' ], $data, $params[ 'hmac_key' ], ! $params[ 'base64' ] );

			// Time-attack-safe comparison
			$diff = 0;
			for ( $i = 0; $i < $digest_size; $i++ )
			{
				$diff |= ord( $hmac_input[ $i ] ) ^ ord( $hmac_check[ $i ] );
			}

			if ( $diff !== 0 )
			{
				return FALSE;
			}
		}

		if ( $params[ 'base64' ] )
		{
			$data = $this->base64Decode( $data );
		}

		isset( $params[ 'key' ] ) OR $params[ 'key' ] = $this->hkdf( $this->_key, 'sha512', NULL, self::strlen( $this->_key ), 'encryption' );

		return $this->{'_' . camelcase( $this->_driver . '_decrypt' )}( $data, $params );
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt via MCrypt
	 *
	 * @param   string $data   Encrypted data
	 * @param   array  $params Input parameters
	 *
	 * @return  string
	 */
	protected function _mcryptDecrypt( $data, $params )
	{
		if ( ! is_resource( $params[ 'handle' ] ) )
		{
			return FALSE;
		}

		// The greater-than-1 comparison is mostly a work-around for a bug,
		// where 1 is returned for ARCFour instead of 0.
		if ( ( $iv_size = mcrypt_enc_get_iv_size( $params[ 'handle' ] ) ) > 1 )
		{
			if ( mcrypt_enc_get_modes_name( $params[ 'handle' ] ) !== 'ECB' )
			{
				$iv   = self::substr( $data, 0, $iv_size );
				$data = self::substr( $data, $iv_size );
			}
			else
			{
				// MCrypt is dumb and this is ignored, only size matters
				$iv = str_repeat( "\x0", $iv_size );
			}
		}
		else
		{
			$iv = NULL;
		}

		if ( mcrypt_generic_init( $params[ 'handle' ], $params[ 'key' ], $iv ) < 0 )
		{
			if ( $params[ 'handle' ] !== $this->_handle )
			{
				mcrypt_module_close( $params[ 'handle' ] );
			}

			return FALSE;
		}

		$data = mdecrypt_generic( $params[ 'handle' ], $data );
		// Remove PKCS#7 padding, if necessary
		if ( in_array( strtolower( mcrypt_enc_get_modes_name( $params[ 'handle' ] ) ), [ 'cbc', 'ecb' ], TRUE ) )
		{
			$data = self::substr( $data, 0, -ord( $data[ self::strlen( $data ) - 1 ] ) );
		}

		mcrypt_generic_deinit( $params[ 'handle' ] );
		if ( $params[ 'handle' ] !== $this->_handle )
		{
			mcrypt_module_close( $params[ 'handle' ] );
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt via OpenSSL
	 *
	 * @param   string $data   Encrypted data
	 * @param   array  $params Input parameters
	 *
	 * @return  string
	 */
	protected function _opensslDecrypt( $data, $params )
	{
		if ( $iv_size = openssl_cipher_iv_length( $params[ 'handle' ] ) )
		{
			$iv   = self::substr( $data, 0, $iv_size );
			$data = self::substr( $data, $iv_size );
		}
		else
		{
			$iv = NULL;
		}

		return empty( $params[ 'handle' ] )
			? FALSE
			: openssl_decrypt(
				$data,
				$params[ 'handle' ],
				$params[ 'key' ],
				1, // DO NOT TOUCH!
				$iv
			);
	}

	// --------------------------------------------------------------------

	/**
	 * Get params
	 *
	 * @param   array $params Input parameters
	 *
	 * @return  array
	 */
	protected function _getParams( $params )
	{
		if ( empty( $params ) )
		{
			return isset( $this->_cipher, $this->_mode, $this->_key, $this->_handle )
				? [
					'handle'      => $this->_handle,
					'cipher'      => $this->_cipher,
					'mode'        => $this->_mode,
					'key'         => NULL,
					'base64'      => TRUE,
					'hmac_digest' => 'sha512',
					'hmac_key'    => NULL,
				]
				: FALSE;
		}
		elseif ( ! isset( $params[ 'cipher' ], $params[ 'mode' ], $params[ 'key' ] ) )
		{
			return FALSE;
		}

		if ( isset( $params[ 'mode' ] ) )
		{
			$params[ 'mode' ] = strtolower( $params[ 'mode' ] );
			if ( ! isset( $this->_modes[ $this->_driver ][ $params[ 'mode' ] ] ) )
			{
				return FALSE;
			}
			else
			{
				$params[ 'mode' ] = $this->_modes[ $this->_driver ][ $params[ 'mode' ] ];
			}
		}

		if ( isset( $params[ 'hmac' ] ) && $params[ 'hmac' ] === FALSE )
		{
			$params[ 'hmac_digest' ] = $params[ 'hmac_key' ] = NULL;
		}
		else
		{
			if ( ! isset( $params[ 'hmac_key' ] ) )
			{
				return FALSE;
			}
			elseif ( isset( $params[ 'hmac_digest' ] ) )
			{
				$params[ 'hmac_digest' ] = strtolower( $params[ 'hmac_digest' ] );
				if ( ! isset( $this->_digests[ $params[ 'hmac_digest' ] ] ) )
				{
					return FALSE;
				}
			}
			else
			{
				$params[ 'hmac_digest' ] = 'sha512';
			}
		}

		$params = [
			'handle'      => NULL,
			'cipher'      => $params[ 'cipher' ],
			'mode'        => $params[ 'mode' ],
			'key'         => $params[ 'key' ],
			'base64'      => isset( $params[ 'raw_data' ] ) ? ! $params[ 'raw_data' ] : FALSE,
			'hmac_digest' => $params[ 'hmac_digest' ],
			'hmac_key'    => $params[ 'hmac_key' ],
		];

		$this->_cipherAlias( $params[ 'cipher' ] );
		$params[ 'handle' ] = ( $params[ 'cipher' ] !== $this->_cipher OR $params[ 'mode' ] !== $this->_mode )
			? $this->{'_' . $this->_driver . '_get_handle'}( $params[ 'cipher' ], $params[ 'mode' ] )
			: $this->_handle;

		return $params;
	}

	// --------------------------------------------------------------------

	/**
	 * Get MCrypt handle
	 *
	 * @param   string $cipher Cipher name
	 * @param   string $mode   Encryption mode
	 *
	 * @return  resource
	 */
	protected function _mcryptGetHandle( $cipher, $mode )
	{
		return mcrypt_module_open( $cipher, '', $mode, '' );
	}

	// --------------------------------------------------------------------

	/**
	 * Get OpenSSL handle
	 *
	 * @param   string $cipher Cipher name
	 * @param   string $mode   Encryption mode
	 *
	 * @return  string
	 */
	protected function _opensslGetHandle( $cipher, $mode )
	{
		// OpenSSL methods aren't suffixed with '-stream' for this mode
		return ( $mode === 'stream' )
			? $cipher
			: $cipher . '-' . $mode;
	}

	// --------------------------------------------------------------------

	/**
	 * Cipher alias
	 *
	 * Tries to translate cipher names between MCrypt and OpenSSL's "dialects".
	 *
	 * @param   string $cipher Cipher name
	 *
	 * @return  void
	 */
	protected function _cipherAlias( &$cipher )
	{
		static $dictionary;

		if ( empty( $dictionary ) )
		{
			$dictionary = [
				'mcrypt'  => [
					'aes-128'   => 'rijndael-128',
					'aes-192'   => 'rijndael-128',
					'aes-256'   => 'rijndael-128',
					'des3-ede3' => 'tripledes',
					'bf'        => 'blowfish',
					'cast5'     => 'cast-128',
					'rc4'       => 'arcfour',
					'rc4-40'    => 'arcfour',
				],
				'openssl' => [
					'rijndael-128' => 'aes-128',
					'tripledes'    => 'des-ede3',
					'blowfish'     => 'bf',
					'cast-128'     => 'cast5',
					'arcfour'      => 'rc4-40',
					'rc4'          => 'rc4-40',
				],
			];

			// Notes:
			//
			// - Rijndael-128 is, at the same time all three of AES-128,
			//   AES-192 and AES-256. The only difference between them is
			//   the key size. Rijndael-192, Rijndael-256 on the other hand
			//   also have different block sizes and are NOT AES-compatible.
			//
			// - Blowfish is said to be supporting key sizes between
			//   4 and 56 bytes, but it appears that between MCrypt and
			//   OpenSSL, only those of 16 and more bytes are compatible.
			//   Also, don't know what MCrypt's 'blowfish-compat' is.
			//
			// - CAST-128/CAST5 produces a longer cipher when encrypted via
			//   OpenSSL, but (strangely enough) can be decrypted by either
			//   extension anyway.
			//   Also, it appears that OpenSSL uses 16 rounds regardless of
			//   the key size, while RFC2144 says that for key sizes lower
			//   than 11 bytes, only 12 rounds should be used. This makes
			//   it portable only with keys of between 11 and 16 bytes.
			//
			// - RC4 (ARCFour) has a strange implementation under OpenSSL.
			//   Its 'rc4-40' cipher method seems to work flawlessly, yet
			//   there's another one, 'rc4' that only works with a 16-byte key.
			//
			// - DES is compatible, but doesn't need an alias.
			//
			// Other seemingly matching ciphers between MCrypt, OpenSSL:
			//
			// - RC2 is NOT compatible and only an obscure forum post
			//   confirms that it is MCrypt's fault.
		}

		if ( isset( $dictionary[ $this->_driver ][ $cipher ] ) )
		{
			$cipher = $dictionary[ $this->_driver ][ $cipher ];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * HKDF
	 *
	 * @link    https://tools.ietf.org/rfc/rfc5869.txt
	 *
	 * @param   $key    Input key
	 * @param   $digest A SHA-2 hashing algorithm
	 * @param   $salt   Optional salt
	 * @param   $length Output length (defaults to the selected digest size)
	 * @param   $info   Optional context/application-specific info
	 *
	 * @return  string  A pseudo-random key
	 */
	public function hkdf( $key, $digest = 'sha512', $salt = NULL, $length = NULL, $info = '' )
	{
		if ( ! isset( $this->_digests[ $digest ] ) )
		{
			return FALSE;
		}

		if ( empty( $length ) OR ! is_int( $length ) )
		{
			$length = $this->_digests[ $digest ];
		}
		elseif ( $length > ( 255 * $this->_digests[ $digest ] ) )
		{
			return FALSE;
		}

		self::strlen( $salt ) OR $salt = str_repeat( "\0", $this->_digests[ $digest ] );

		$prk = hash_hmac( $digest, $key, $salt, TRUE );
		$key = '';
		for ( $key_block = '', $block_index = 1; self::strlen( $key ) < $length; $block_index++ )
		{
			$key_block = hash_hmac( $digest, $key_block . $info . chr( $block_index ), $prk, TRUE );
			$key .= $key_block;
		}

		return self::substr( $key, 0, $length );
	}

	// --------------------------------------------------------------------

	/**
	 * __get() magic
	 *
	 * @param   string $key Property name
	 *
	 * @return  mixed
	 */
	public function __get( $key )
	{
		// Because aliases
		if ( $key === 'mode' )
		{
			return array_search( $this->_mode, $this->_modes[ $this->_driver ], TRUE );
		}
		elseif ( in_array( $key, [ 'cipher', 'driver', 'drivers', 'digests' ], TRUE ) )
		{
			return $this->{'_' . $key};
		}

		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe strlen()
	 *
	 * @param   string $str
	 *
	 * @return  integer
	 */
	protected static function strlen( $str )
	{
		return ( self::$func_override )
			? mb_strlen( $str, '8bit' )
			: strlen( $str );
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe substr()
	 *
	 * @param   string $str
	 * @param   int    $start
	 * @param   int    $length
	 *
	 * @return  string
	 */
	protected static function substr( $str, $start, $length = NULL )
	{
		if ( self::$func_override )
		{
			// mb_substr($str, $start, null, '8bit') returns an empty
			// string on PHP 5.3
			isset( $length ) OR $length = ( $start >= 0 ? self::strlen( $str ) - $start : -$start );

			return mb_substr( $str, $start, $length, '8bit' );
		}

		return isset( $length )
			? substr( $str, $start, $length )
			: substr( $str, $start );
	}

	/**
	 * Encode a string with URL-safe Base64.
	 *
	 * @param string $input The string you want encoded
	 *
	 * @return string The base64 encode of what you passed in
	 */
	public function base64Encode( $string )
	{
		return str_replace( '=', '', strtr( base64_encode( $string ), '+/', '-_' ) );
	}

	/**
	 * Decode a string with URL-safe Base64.
	 *
	 * @param string $input A Base64 encoded string
	 *
	 * @return string A decoded string
	 */
	public function base64Decode( $string )
	{
		$remainder = strlen( $string ) % 4;

		if ( $remainder )
		{
			$length = 4 - $remainder;
			$string .= str_repeat( '=', $length );
		}

		return base64_decode( strtr( $string, '-_', '+/' ) );
	}
}
