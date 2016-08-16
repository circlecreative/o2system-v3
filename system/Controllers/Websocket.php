<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 21-Jul-16
 * Time: 12:16 AM
 */

namespace O2System\Controllers;


use O2System\Core\Controller;
use O2System\Core\SPL\ArrayObject;
use O2System\Libraries\Websocket\User;

abstract class Websocket extends Controller
{
	/**
	 * Websocket Handler
	 *
	 * @type \O2System\Libraries\Websocket
	 */
	protected $handler;
	
	public function _setHandler( \O2System\Libraries\Websocket $handler )
	{
		$this->handler = $handler;
	}

	/**
	 * Do Websocket Process
	 *
	 * @param \O2System\Libraries\Websocket\User $user
	 * @param                                    $message
	 *
	 * @return mixed
	 */
	abstract public function _onProcess( User $user, $message );

	/**
	 * Server-side file.
	 * This file is an infinitive loop. Seriously.
	 * It gets the file data.txt's last-changed timestamp, checks if this is larger than the timestamp of the
	 * AJAX-submitted timestamp (time of last ajax request), and if so, it sends back a JSON with the data from
	 * data.txt (and a timestamp). If not, it waits for one seconds and then start the next while step.
	 *
	 * Note: This returns a JSON, containing the content of data.txt and the timestamp of the last data.txt change.
	 * This timestamp is used by the client's JavaScript for the next request, so THIS server-side script here only
	 * serves new content after the last file change. Sounds weird, but try it out, you'll get into it really fast!
	 */
	protected function _longPolling( User $user, ArrayObject $account )
	{
		if ( method_exists( $this, '_getLastPollingData' ) )
		{
			// set php runtime to unlimited
			set_time_limit( 0 );

			$data = $this->_getLastPollingData( $account );

			// main loop
			while ( TRUE )
			{
				// PHP caches file data, like requesting the size of a file, by default. clearstatcache() clears that cache
				clearstatcache();

				if ( $data )
				{
					if ( is_string( $data ) OR is_numeric( $data ) )
					{
						$this->handler->sendMessage( $user, $data );
					}
					elseif ( is_object( $data ) OR is_array( $data ) )
					{
						$this->handler->sendMessage( $user, json_encode( $data ) );
					}

					// leave this loop step
					break;
				}
				else
				{
					// wait for 1 sec (not very sexy as this blocks the PHP/Apache process, but that's how it goes)
					sleep( 1 );
					continue;
				}
			}
		}
	}
}