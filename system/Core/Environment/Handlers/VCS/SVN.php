<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 1:03 AM
 */

namespace O2System\Core\Environment\Handlers\VCS;

use O2System\Core\Environment\Interfaces\VCSHandler;

class SVN implements VCSHandler
{
	public function requestEventListener()
	{
		if ( isset( $_REQUEST[ 'VCS' ] ) )
		{
			$method = '_' . camelcase( $_REQUEST[ 'VCS' ] );

			if ( method_exists( $this, $method ) )
			{
				call_user_func( [ $this, $method ] );
			}
		}
	}

	protected function _svnUp()
	{
		$stdout = [ ];
		$svn    = exec( 'svn up --username www-data --password readonly --force', $stdout );
		if ( count( $stdout ) > 0 )
		{
			echo 'SVN Update : <br>';
			foreach ( $stdout as $o )
			{
				echo $o . '<br>';
			}
		}
		unset( $stdout );
		exit( 'Performing SVN UP' );
	}

	protected function _svnCleanUp()
	{
		$stdout = [ ];
		$svn    = exec( 'svn cleanup --username www-data --password readonly --force', $stdout );
		if ( count( $stdout ) > 0 )
		{
			echo 'SVN Cleanup : <br>';
			foreach ( $stdout as $o )
			{
				echo $o . '<br>';
			}
		}
		unset( $stdout );
		exit( 'Performing SVN CLEANUP' );
	}

	protected function _svnInfo()
	{
		$stdout = [ ];
		$svn    = exec( 'svn info', $stdout );
		if ( count( $stdout ) > 0 )
		{
			echo 'SVN Info : <br>';
			foreach ( $stdout as $o )
			{
				echo $o . '<br>';
			}
		}
		unset( $stdout );
		exit( 'Performing SVN INFO' );
	}

	protected function _svnStatus()
	{
		$stdout = [ ];
		$svn    = exec( 'svn status --username www-data --password readonly --force', $stdout );
		if ( count( $stdout ) > 0 )
		{
			echo 'SVN Status : <br>';
			foreach ( $stdout as $o )
			{
				echo $o . '<br>';
			}
		}
		unset( $stdout );
		exit( 'Performing SVN STATUS' );
	}
}