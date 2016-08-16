<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 5/9/2016
 * Time: 11:44 AM
 */

namespace O2System\Registry\Metadata;


use O2System\Core\SPL\ArrayObject;

class Page extends ArrayObject
{
	public function __construct( $page )
	{
		$pathinfo = pathinfo( $page );

		parent::__construct(
			[
				'realpath'  => $page,
				'directory' => $directory = $pathinfo[ 'dirname' ] . DIRECTORY_SEPARATOR,
				'filename'  => $filename = $pathinfo[ 'filename' ],
				'basename'  => $pathinfo[ 'basename' ],
				'extension' => $pathinfo[ 'extension' ],
				'vars'      => $this->__fetchVars( $directory, $filename ),
				'settings'  => $this->__fetchSettings( $directory, $filename ),
			] );
	}

	private function __fetchVars( $directory, $filename )
	{
		$filepaths = [
			$directory . $filename . '.vars',
			$directory . 'pages.vars',
			dirname( $directory ) . DIRECTORY_SEPARATOR . 'pages.vars',
		];

		foreach ( $filepaths as $filepath )
		{
			if ( is_file( $filepath ) )
			{
				$vars = file_get_contents( $filepath );
				$vars = json_decode( $vars, TRUE );

				if ( isset( $vars[ $filename ] ) )
				{
					return $vars[ $filename ];
				}

				return $vars;
			}
		}

		return [ ];
	}

	private function __fetchSettings( $directory, $filename )
	{
		$filepaths = [
			$directory . $filename . '.settings',
			$directory . 'pages.settings',
			dirname( $directory ) . DIRECTORY_SEPARATOR . 'pages.settings',
		];

		foreach ( $filepaths as $filepath )
		{
			if ( is_file( $filepath ) )
			{
				$settings = file_get_contents( $filepath );
				$settings = json_decode( $settings, TRUE );

				if ( isset( $settings[ $filename ] ) )
				{
					return new Setting( $settings[ $filename ] );
				}

				return new Setting( $settings );
			}
		}

		return new Setting();
	}
}

