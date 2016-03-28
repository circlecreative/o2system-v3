<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/11/2016
 * Time: 4:01 PM
 */

namespace O2System\Glob\Exception;

/**
 * Class ExceptionSeverity
 *
 * @package O2System\Glob\Exception
 */
abstract class ExceptionSeverity
{
	/**
	 * List of severities
	 *
	 * @type array
	 */
	private static $_severities = array(
		E_ERROR           => 'ERROR',
		E_WARNING         => 'WARNING',
		E_PARSE           => 'PARSE',
		E_NOTICE          => 'NOTICE',
		E_CORE_ERROR      => 'CORE_ERROR',
		E_CORE_WARNING    => 'CORE_WARNING',
		E_COMPILE_ERROR   => 'COMPILE_ERROR',
		E_COMPILE_WARNING => 'COMPILE_WARNING',
		E_USER_ERROR      => 'USER_ERROR',
		E_USER_WARNING    => 'USER_WARNING',
		E_USER_NOTICE     => 'USER_NOTICE',
		E_STRICT          => 'RUNTIME_NOTICE',
	);

	// ------------------------------------------------------------------------

	/**
	 * Get Severity
	 *
	 * @param $severity
	 *
	 * @return string
	 */
	public static function getSeverity( $severity )
	{
		$lang_line = array_key_exists( $severity, self::$_severities ) ? self::$_severities[ $severity ] : readable( $severity );

		if ( class_exists( 'O2System', FALSE ) )
		{
			$lang_severity = \O2System::$language->line( 'EXCEPTIONSEVERITY_' . $lang_line );
		}
		else
		{
			$lang_severity = \O2System\Glob::$language->line( 'EXCEPTIONSEVERITY_' . $lang_line );
		}

		$severity = '[' . $severity . '] ' . ( empty( $lang_severity ) ? readable( $lang_line ) : $lang_severity );

		return $severity;
	}
}