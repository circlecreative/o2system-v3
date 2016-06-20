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

/**
 * Trail Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/access/trail.html
 */
class Trail extends DriverInterface
{
	public function log( array $log )
	{
		if ( \O2System::Useragent()->isRobot() ) return FALSE;

		$id_user_account = NULL;

		if ( $this->_library->user->isLogin() )
		{
			$id_user_account = $this->_library->user->account( 'id' );
		}

		// Try to close old session trail
		if ( isset( $_SESSION[ 'trail' ] ) )
		{
			if ( $trail = $this->_library->model->getTrail( $_SESSION[ 'trail' ] ) )
			{
				$session_end = now();
				$session_duration = $session_end - $trail[ 'session_start' ];

				$this->_library->model->updateTrail( array(
					                                      'session_end'      => $session_end,
					                                      'session_duration' => $session_duration,
				                                      ), $_SESSION[ 'trail' ] );
			}
		}

		// Create new session trail
		$session_referer = NULL;

		if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) )
		{
			$session_referer = \O2System::Input()->server( 'HTTP_REFERER', TRUE );
		}
		elseif ( isset( $_SERVER[ 'REDIRECT_URL' ] ) )
		{
			$session_referer = \O2System::Input()->server( 'REDIRECT_URL', TRUE );
		}

		$session_data = array(
			'id_user_account' => $id_user_account,
			'user_ip'         => \O2System::Input()->ipAddress(),
			'user_agent'      => \O2System::Input()->userAgent(),
			'session_id'      => \O2System::Session()->session_id,
			'session_url'     => current_url(),
		);

		$trail = $this->_library->model->getTrail( $session_data );

		if ( $trail === FALSE )
		{
			$_SESSION[ 'trail' ] = $session_data;

			return $this->_library->model->insertTrail( array(
				                                             'id_user_account'  => $id_user_account,
				                                             'user_ip'          => \O2System::Input()->ipAddress(),
				                                             'user_agent'       => \O2System::Input()->userAgent(),
				                                             'session_id'       => \O2System::Session()->session_id,
				                                             'session_referer'  => $session_referer,
				                                             'session_url'      => current_url(),
				                                             'session_start'    => now(),
				                                             'session_metadata' => json_encode( $log ),
			                                             ) );
		}
		else
		{
			$session_end = now();
			$session_duration = $session_end - $trail[ 'session_start' ];
			$session_data = isset( $_SESSION[ 'trail' ] ) ? $_SESSION[ 'trail' ] : $session_data;

			unset( $_SESSION[ 'trail' ] );

			return $this->_library->model->updateTrail( array(
				                                             'session_end'      => $session_end,
				                                             'session_duration' => $session_duration,
				                                             'session_metadata' => json_encode( $log ),
			                                             ), $session_data );
		}
	}

	public function getLog()
	{
		if ( $this->_library->user->isLogin() )
		{
			$conditions[ 'id_user_account' ] = $this->_library->user->account( 'id' );
		}

		$conditions[ 'session_url' ] = current_url();

		return $this->_library->model->getTrail( $conditions );
	}

	public function getLogs()
	{
		if ( $this->_library->user->isLogin() )
		{
			$conditions[ 'id_user_account' ] = $this->_library->user->account( 'id' );
		}

		$conditions[ 'session_url' ] = current_url();

		return $this->_library->model->getTrails( $conditions );
	}
}