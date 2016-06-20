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

/**
 * Email
 *
 * Porting from CodeIgniter Email Libraries Class
 *
 * @package     O2System
 * @subpackage  Libraries
 * @category    Library Class
 * @author      Circle Creative Dev Team
 * @link        http://circle-creative.com/products/o2system-codeigniter/user-guide/libraries/email.html
 */
class Email
{
	protected $_config = array();

	/**
	 * PHPMailer Handler
	 *
	 * @type
	 */
	protected $_php_mailer;

	protected $_errors = array();

	public function __construct( $config = array() )
	{
		if ( ! class_exists( 'PHPMailer' ) )
		{
			throw new \Exception( 'The PHPMailer must be loaded to use this email class.' );
		}

		if ( empty( $config ) )
		{
			$config = \O2System::$config->load( 'email', TRUE );
		}

		$this->_config[ 'charset' ] = \O2System::$config[ 'charset' ];

		if ( isset( $config[ 'from' ] ) )
		{
			if ( is_array( $config[ 'from' ] ) )
			{
				if ( isset( $config[ 'from' ][ 'email' ] ) )
				{
					$this->_config[ 'from' ] = $config[ 'from' ][ 'email' ];
				}

				if ( isset( $config[ 'from' ][ 'name' ] ) )
				{
					$this->_config[ 'from_name' ] = $config[ 'from' ][ 'name' ];
				}

				if ( isset( $config[ 'from' ][ 'return_path' ] ) )
				{
					$this->_config[ 'return_path' ] = $config[ 'from' ][ 'return_path' ];
				}
			}
			else
			{
				$this->_config[ 'from' ] = $this->_config[ 'from_name' ] = $config[ 'from' ];
			}

			unset( $config[ 'from' ] );
		}
		else
		{
			$this->_config[ 'from' ] = 'no-reply@' . str_replace( 'www.', '', \O2System::$active[ 'domain' ] );
			$this->_config[ 'from_name' ] = 'no-reply';
		}

		if ( isset( $config[ 'protocol' ] ) )
		{
			if ( is_string( $config[ 'protocol' ] ) )
			{
				$protocol = $config[ 'protocol' ];
				$settings = array();
			}
			else
			{
				$protocol = key( $config[ 'protocol' ] );
				$settings = $config[ 'protocol' ][ $protocol ];
			}

			$this->_config[ 'protocol' ] = [ $protocol, $settings ];

			unset( $config[ 'protocol' ] );
		}

		$this->_php_mailer = new \PHPMailer;

		if ( isset( $config ) AND is_array( $config ) )
		{
			$this->_config = array_merge_recursive( $this->_config, $config );
		}

		foreach ( $this->_config as $key => $value )
		{
			if ( is_string( $value ) )
			{
				$value = [ $value ];
			}

			if ( method_exists( $this, strtolower( $key ) ) )
			{
				call_user_func_array( array( $this, strtolower( $key ) ), $value );
			}
			elseif ( method_exists( $this, 'set_' . strtolower( $key ) ) )
			{
				call_user_func_array( array( $this, 'set_' . strtolower( $key ) ), $value );
			}
		}
	}

	public function setHost($host )
	{
		$this->_php_mailer->Host = $host;

		return $this;
	}

	public function setCharset($charset )
	{
		$this->_php_mailer->CharSet = $charset;

		return $this;
	}

	public function setContentType($type )
	{
		if ( ! in_array( $type, [ 'html', 'plain', 'text' ] ) )
		{
			throw new \BadMethodCallException( 'Email: Invalid Email Content Type' );
		}

		if ( $type === 'html' )
		{
			$this->_php_mailer->isHTML( TRUE );
		}

		return $this;
	}

	public function setProtocol()
	{
		$args = func_get_args();

		if ( ! in_array( $args[ 0 ], [ 'mail', 'sendmail', 'smtp' ] ) )
		{
			throw new \BadMethodCallException( 'Email: Invalid Email Protocol' );
		}
		else
		{
			$this->_php_mailer->Mailer = $args[ 0 ];
		}

		if ( $args[ 0 ] === 'sendmail' )
		{
			if ( ! empty( $args[ 1 ] ) )
			{
				$this->_php_mailer->Sendmail( $args[ 1 ] );
			}
		}
		elseif ( $args[ 0 ] === 'smtp' )
		{
			$this->_php_mailer->isSMTP();

			if ( ! empty( $args[ 1 ] ) )
			{
				if ( isset( $args[ 1 ][ 'username' ] ) AND isset( $args[ 1 ][ 'password' ] ) )
				{
					$this->_php_mailer->Username = $args[ 1 ][ 'username' ];
					$this->_php_mailer->Password = $args[ 1 ][ 'password' ];
					$this->_php_mailer->SMTPAuth = TRUE;
				}

				if ( isset( $args[ 1 ][ 'port' ] ) )
				{
					$this->_php_mailer->Port = $args[ 1 ][ 'port' ];
				}

				if ( isset( $args[ 1 ][ 'host' ] ) )
				{
					$this->_php_mailer->Host = $args[ 1 ][ 'host' ];
					$this->setHost( $args[ 1 ][ 'host' ] );
				}
			}
		}

		return $this;
	}

	public function subject( $subject )
	{
		$this->_php_mailer->Subject = $subject;

		return $this;
	}

	public function message( $body, $vars = array() )
	{
		if ( \O2System::instance()->__isset( 'view' ) )
		{
			$body = \O2System::View()->load( $body, (array)$vars, TRUE );
		}
		elseif ( is_file( $body ) )
		{
			$body = file_get_contents( $body );
			$body = htmlspecialchars_decode( $body );

			if ( count( $vars ) > 0 )
			{
				extract( $vars );

				/*
				 * Buffer the output
				 *
				 * We buffer the output for two reasons:
				 * 1. Speed. You get a significant speed boost.
				 * 2. So that the final rendered template can be post-processed by
				 *	the output class. Why do we need post processing? For one thing,
				 *	in order to show the elapsed page load time. Unless we can
				 *	intercept the content right before it's sent to the browser and
				 *	then stop the timer it won't be accurate.
				 */
				ob_start();

				// If the PHP installation does not support short tags we'll
				// do a little string replacement, changing the short tags
				// to standard PHP echo statements.
				if ( ! ini_get( 'short_open_tag' ) AND functionUsable( 'eval' ) )
				{
					echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', str_replace( '<?=', '<?php echo ', $body ) ) );
				}
				else
				{
					echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', $body ) );
				}

				$body = ob_get_contents();
				@ob_end_clean();
			}
		}

		$this->setContentType( 'html' );

		$this->_php_mailer->Body = $body;
		$this->_php_mailer->AltBody = strip_tags( $body );

		return $this;
	}

	public function altMessage($message, $vars = array() )
	{
		if ( \O2System::instance()->__isset( 'view' ) )
		{
			$message = \O2System::View()->load( $message, $vars, TRUE );
		}
		elseif ( is_file( $message ) )
		{
			$message = file_get_contents( $message );
			$message = htmlspecialchars_decode( $message );

			if ( count( $vars ) > 0 )
			{
				extract( $vars );

				/*
				 * Buffer the output
				 *
				 * We buffer the output for two reasons:
				 * 1. Speed. You get a significant speed boost.
				 * 2. So that the final rendered template can be post-processed by
				 *	the output class. Why do we need post processing? For one thing,
				 *	in order to show the elapsed page load time. Unless we can
				 *	intercept the content right before it's sent to the browser and
				 *	then stop the timer it won't be accurate.
				 */
				ob_start();

				// If the PHP installation does not support short tags we'll
				// do a little string replacement, changing the short tags
				// to standard PHP echo statements.
				if ( ! ini_get( 'short_open_tag' ) AND functionUsable( 'eval' ) )
				{
					echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', str_replace( '<?=', '<?php echo ', $message ) ) );
				}
				else
				{
					echo eval( '?>' . preg_replace( '/;*\s*\?>/', '; ?>', $message ) );
				}

				$message = ob_get_contents();
				@ob_end_clean();
			}
		}

		$this->_php_mailer->AltBody = strip_tags( $message );

		return $this;
	}

	public function from( $address, $name = NULL, $return_path = NULL )
	{
		$name = isset( $name ) ? $name : @$this->_config[ 'from_name' ];

		$this->_php_mailer->setFrom( $address, $name );

		if ( isset( $return_path ) )
		{
			$this->returnPath( $return_path );
		}

		return $this;
	}

	public function fromName($name )
	{
		$this->_php_mailer->FromName = $name;

		return $this;
	}

	public function returnPath($return_path )
	{
		$this->_php_mailer->ReturnPath = $return_path;

		return $this;
	}

	public function replyTo($address, $name )
	{
		$this->_php_mailer->addReplyTo( $address, $name );

		return $this;
	}

	public function to( $address, $name = NULL )
	{
		if ( is_array( $address ) )
		{
			foreach ( $address as $email => $name )
			{
				if ( is_numeric( $email ) )
				{
					$this->_php_mailer->addAddress( $name );
				}
				else
				{
					$this->_php_mailer->addAddress( $email, $name );
				}
			}
		}
		elseif ( is_string( $address ) )
		{
			if ( strpos( $address, ',' ) )
			{
				$address = explode( ',', $address );
				$address = array_map( 'trim', $address );

				foreach ( $address as $email )
				{
					$this->_php_mailer->addAddress( $email );
				}
			}
			else
			{
				$this->_php_mailer->addAddress( $address, $name );
			}
		}

		return $this;
	}

	public function cc( $address, $name = NULL )
	{
		if ( is_array( $address ) )
		{
			foreach ( $address as $email => $name )
			{
				if ( is_numeric( $email ) )
				{
					$this->_php_mailer->addCC( $name );
				}
				else
				{
					$this->_php_mailer->addCC( $email, $name );
				}
			}
		}
		elseif ( is_string( $address ) )
		{
			if ( strpos( $address, ',' ) )
			{
				$address = explode( ',', $address );
				$address = array_map( 'trim', $address );

				foreach ( $address as $email )
				{
					$this->_php_mailer->addCC( $email );
				}
			}
			else
			{
				$this->_php_mailer->addCC( $address, $name );
			}
		}

		return $this;
	}

	public function bcc( $address, $name )
	{
		if ( is_array( $address ) )
		{
			foreach ( $address as $email => $name )
			{
				if ( is_numeric( $email ) )
				{
					$this->_php_mailer->addBCC( $name );
				}
				else
				{
					$this->_php_mailer->addBCC( $email, $name );
				}
			}
		}
		elseif ( is_string( $address ) )
		{
			if ( strpos( $address, ',' ) )
			{
				$address = explode( ',', $address );
				$address = array_map( 'trim', $address );

				foreach ( $address as $email )
				{
					$this->_php_mailer->addBCC( $email );
				}
			}
			else
			{
				$this->_php_mailer->addBCC( $address, $name );
			}
		}

		return $this;
	}

	public function attach( $attachment, $name = NULL )
	{
		if ( is_array( $attachment ) )
		{
			foreach ( $attachment as $file => $name )
			{
				if ( is_numeric( $file ) )
				{
					$this->attach( $file );
				}
				else
				{
					$this->attach( $file, $name );
				}
			}
		}
		else
		{
			if ( is_file( $attachment ) )
			{
				$this->_php_mailer->addAttachment( $attachment, $name );
			}
		}

		return $this;
	}

	public function getBody()
	{
		return $this->_php_mailer->Body;
	}

	public function getAltBody()
	{
		return $this->_php_mailer->AltBody;
	}

	public function send()
	{
		try
		{
			if ( $this->_php_mailer->send() )
			{
				return TRUE;
			}
			else
			{
				$this->_errors[] = $this->_php_mailer->ErrorInfo;
			}
		}
		catch ( \phpmailerException $e )
		{
			$this->_errors[] = $e->getMessage();
		}
		catch ( \Exception $e )
		{
			$this->_errors[] = $e->getMessage();
		}

		return FALSE;
	}

	public function getErrors()
	{
		return $this->_errors;
	}
}
