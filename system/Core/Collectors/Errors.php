<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 7:46 AM
 */

namespace O2System\Core\Collectors;

use O2System\Core;

trait Errors
{
	/**
	 * List of Errors
	 *
	 * @type array
	 */
	protected $errors = [ ];

	public function throwError( $message, $code = 0, array $args = [ ], $class = NULL )
	{
		$isDebugMode = FALSE;

		if ( $code == 0 )
		{
			$log_message = '[ ' . get_called_class() . ' ] ' . $message;
		}
		else
		{
			$log_message = '[ ' . get_called_class() . ' ] ' . '[ ' . $code . ' ] ' . $message;
		}

		$message = \O2System::$language->line( $message, $args );

		$this->errors[] = $throw_message = empty( $message ) ? $log_message : $message;

		\O2System::$log->error( $throw_message );
		$isDebugMode = \O2System::$environment->isDebugStage();

		if ( $isDebugMode === TRUE )
		{
			$exceptionClass = isset( $class ) ? $class : get_called_class() . '\\Exception';

			if ( class_exists( $exceptionClass ) )
			{
				throw new $exceptionClass( $throw_message, $code );
			}
			else
			{
				throw new Core\Exception( $throw_message, $code );
			}
		}
	}

	public function setError( $code, $message )
	{
		if ( is_array( $code ) )
		{
			$this->errors = [ ];

			foreach ( $code as $errorCode => $errorMessage )
			{
				$this->errors[ $errorCode ] = $errorMessage;
			}
		}
		else
		{
			$this->errors[ $code ] = $message;
		}
	}

	public function addError( $code, $message )
	{
		$this->errors[ $code ] = $message;
	}

	public function getErrors( $html = FALSE )
	{
		return $this->errors;
	}
}