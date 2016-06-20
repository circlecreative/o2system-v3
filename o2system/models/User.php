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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://o2system.center
 * @since          Version 3.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\Models;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Model;

/**
 * User
 *
 * Base User Modeling Class
 *
 * @package        O2System
 * @subpackage     models
 * @category       Core Library Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/models/user.html
 */
class User extends Model
{
	/**
	 * User Database Table
	 *
	 * @access  public
	 * @type    string
	 */
	public $table;

	/**
	 * User Single Sign On Database Table
	 *
	 * @access  public
	 * @type    string
	 */
	public $table_sso;

	/**
	 * User Database Primary Key
	 *
	 * @access  public
	 * @type    string
	 */
	public $primary_key = 'id';

	// ------------------------------------------------------------------------

	/**
	 * Get User Account
	 *
	 * @param   string|int $username Username|Email|MSIDN|ID
	 * @param   string     $return   Return type array|object
	 *
	 * @access  public
	 * @return  bool
	 */
	public function getAccount( $username )
	{
		if ( isset( $username ) )
		{
			if ( filter_var( $username, FILTER_VALIDATE_INT ) )
			{
				$this->db->where( $this->primary_key, $username );
			}
			elseif ( filter_var( $username, FILTER_VALIDATE_EMAIL ) )
			{
				$this->db->where( 'email', $username );
			}
			elseif ( is_string( $username ) )
			{
				$this->db->where( 'username', $username );
			}

			$result = $this->db->get( $this->table );

			if ( $result->numRows() > 0 )
			{
				$row = $result->first();
				$result->free();

				return $row;
			}
		}

		return FALSE;
	}
	// ------------------------------------------------------------------------

	/**
	 * Insert User Account
	 *
	 * @param   array $account User Account Data
	 *
	 * @access  public
	 * @return  bool
	 */
	public function insertAccount( array $account )
	{
		$this->insert(
			[
				'username' => strtolower( $account[ 'username' ] ),
				'email'    => strtolower( $account[ 'email' ] ),
				'password' => $account[ 'password' ],
			] );

		$account[ 'id' ] = $this->db->lastInsertId();

		return $account;
	}
	// ------------------------------------------------------------------------

	/**
	 * Update User Account
	 *
	 * @param array $account
	 *
	 * @access  public
	 * @return  bool
	 */
	public function updateAccount( array $account )
	{
		return $this->update( $account );
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete User Account
	 *
	 * @param $id
	 *
	 * @access  public
	 * @return  bool
	 */
	public function deleteAccount( $id )
	{
		return $this->delete( $id );
	}

	// ------------------------------------------------------------------------

	/**
	 * Get User SSO
	 *
	 * @param   string      $token      User SSO Token
	 * @param   string|null $ip_address User IP Address
	 *
	 * @return bool
	 */
	public function getSso( $token, $ip_address = NULL )
	{
		if ( isset( $ip_address ) )
		{
			$this->db->where( 'ip', strval( $ip_address ) );
		}

		if ( is_int( $token ) )
		{
			$query = $this->db->getWhere( $this->table_sso, [ 'id_user_account' => intval( $token ) ], 1 );
		}
		else
		{
			$query = $this->db->getWhere( $this->table_sso, [ 'hash' => $token ], 1 );
		}

		if ( $query->numRows() > 0 )
		{
			return $query->first();
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Insert User SSO
	 *
	 * @param   array $sso SSO Data
	 *
	 * @access  public
	 * @return  bool
	 */
	public function insertSso( $sso )
	{
		return $this->db->insert( $this->table_sso, $sso );
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete User SO
	 *
	 * @param   string $token SSO Token
	 *
	 * @access  public
	 * @return  bool
	 */
	public function deleteSso( $token )
	{
		return $this->db->where( 'token', $token )->delete( $this->table_sso );
	}
}