<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 11-Aug-16
 * Time: 1:00 AM
 */

namespace O2System\Core\Console;


class Application
{
	protected $appName;
	protected $appVersion;
	protected $appPath;
	protected $validControllers = [ ];

	public function setAppName( $appName )
	{
		$this->appName = $appName;
	}

	public function setAppVersion( $appVersion )
	{
		$this->appVersion = $appVersion;
	}

	public function setAppPath( $appPath )
	{
		if ( is_dir( $appPath ) )
		{
			$this->appPath = rtrim( $appPath, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

			// Define valid collections
			foreach ( glob( $this->appPath . '*.php' ) as $filepath )
			{
				if ( is_file( $filepath ) )
				{
					$class_name = str_replace( [ ROOTPATH, '.php' ], '', $filepath );

					$this->validControllers[ '\\' . prepare_class_name( $class_name ) ] = $filepath;
				}
			}
		}
	}

	public function start()
	{
		$app = new \Symfony\Component\Console\Application( $this->appName, $this->appVersion );

		foreach ( $this->validControllers as $class => $filepath )
		{
			require_once( $filepath );

			if ( class_exists( $class ) )
			{
				$app->add( new $class() );
			}
		}

		$app->run();
	}
}