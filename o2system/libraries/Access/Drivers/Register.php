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
use O2System\Libraries\Email;

/**
 * Register Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/access/register.html
 */
class Register extends DriverInterface
{
	/**
	 * Register User
	 *
	 * @access  public
	 *
	 * @param array $user
	 * @param bool  $buffer
	 *
	 * @return bool
	 */
	public function user( $user = array(), $buffer = FALSE )
	{
		if ( $this->_isEmailExists( $user[ 'email' ] ) === FALSE AND
			$this->_isUsernameExists( $user[ 'username' ] ) === FALSE AND
			$this->_isMsidnExists( $user[ 'msisdn' ] ) === FALSE
		)
		{
			$user[ 'email' ] = trim( $user[ 'email' ] );
			$user[ 'password' ] = $this->_library->hashPassword( $user[ 'password' ] );

			if ( $this->_library->getConfig( 'password', 'salt' ) )
			{
				$user[ 'salt' ] = $this->_library->saltPassword();
			}

			if ( $buffer === TRUE )
			{
				$user[ 'token' ] = random_string( 'numeric' );
				$this->_library->model->insertRegistrationBuffer( $user );

				$this->sendEmail( $user );
			}
			else
			{
				$this->_library->model->insertAccount( $user );
			}

			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function validate( $token )
	{
		if ( $buffer = $this->_library->model->getRegistrationBuffer( $token ) )
		{
			$expired_time = strtotime( $buffer->code_expired );

			if ( $expired_time < now() )
			{
				$buffer->code_value = random_string( 'numeric' );
				$buffer->code_expired = date( 'Y-m-d H:m:s', strtotime( '+1 day', now() ) );
				$buffer->record_create_timestamp = date( 'Y-m-d H:m:s' );
				$registration = $buffer->registration;
				$registration[ 'token' ] = $buffer->code_value;
				$this->sendEmail( $registration );

				$this->_library->model->updateRegistrationBuffer( $buffer );

				return FALSE;
			}
			else
			{
				if ( $buffer->code_type === 'EMAIL' )
				{
					$user = $buffer->metadata;
					$user->token = random_string( 'numeric' );
					$this->_library->model->insertRegistrationBuffer( (array) $user, 'MSISDN' );

					// Insert Account
					$return = $this->_library->model->insertAccount( $buffer->metadata );

					// Delete Buffer
					if ( $this->_library->model->deleteRegistrationBuffer( $buffer ) )
					{
						return $return;
					}
				}
				elseif ( $buffer->code_type === 'MSISDN' )
				{
					// Delete Buffer
					if ( $this->_library->model->deleteRegistrationBuffer( $buffer ) )
					{
						return TRUE;
					}
				}
			}

		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	public function sendEmail($registration )
	{
		$from_email = empty ( $this->_config[ 'from_email' ] ) ? NULL : $this->_config[ 'from_email' ];

		if ( empty( $from_email ) )
		{
			$from_email = 'no-reply@' . \O2System::$active[ 'domain' ];
		}

		$from_name = empty ( $this->_config[ 'from_name' ] ) ? NULL : $this->_config[ 'from_name' ];

		if ( empty( $from_name ) )
		{
			$from_name = 'Yukbisnis Indonesia';
		}

		$subject = empty ( $this->_config[ 'subject' ] ) ? NULL : $this->_config[ 'subject' ];

		if ( empty( $subject ) )
		{
			$subject = 'User Registration';
		}

		$message = empty ( $this->_config[ 'message' ] ) ? NULL : $this->_config[ 'message' ];

		if ( empty( $message ) )
		{
			$message = \O2System::View()->load( 'email/registration/register', (array)$registration, TRUE );
		}
		elseif( is_file( $message ) )
		{
			$message = \O2System::View()->parser->parse_file( $message, $registration, TRUE );
		}
		else
		{
			$message = \O2System::View()->parser->parse_string( $message, $registration, TRUE );
		}

		$email = new Email(\O2System::$config->load('email', TRUE));

		$email->setContentType('html');
		$email->from( $from_email, $from_name );
		$email->to( $registration[ 'email' ] );
		$email->subject( $subject );
		$email->message( 'email/registration/register', $registration );

		if ( $email->send() )
		{
			\O2System::Session()->set_flashdata( 'success', 'Email berhasil dikirim.' );
		}
		else
		{
			\O2System::Session()->set_flashdata( 'failed', 'Email gagal dikirim.' );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Email Exists
	 *
	 * Checking existence of user account by email
	 *
	 * @param string $email
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function _isEmailExists($email )
	{
		if ( is_object( $this->_library->model->getAccount( $email ) ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Username Exists
	 *
	 * Checking existence of user account by username
	 *
	 * @param   string $username
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function _isUsernameExists($username )
	{
		if ( is_object( $this->_library->model->getAccount( $username ) ) )
		{
			return TRUE;
		}

		if ( is_object( $this->_library->model->getRegistrationBuffer( $username ) ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Is MSIDN Exists
	 *
	 * Checking existence of user account by MSIDN
	 *
	 * @param int|string $msidn
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function _isMsidnExists($msidn )
	{
		if ( is_object( $this->_library->model->getAccount( $msidn ) ) )
		{
			return TRUE;
		}

		return FALSE;
	}
}