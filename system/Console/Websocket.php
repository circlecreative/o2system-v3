<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 19-Jul-16
 * Time: 7:38 PM
 */

namespace O2System\Console;

use O2System\Core\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Websocket extends Command
{
	/**
	 * Console Command Controller Name
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $name = 'websocket';

	/**
	 * Console Controller Description
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $description = 'Websocket daemon console';

	/**
	 * Console Controller Help Arguments Options
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $actionCommands = [
		'open' => [
			'description' => 'open websocket port',
			'options'     => [
				'ip'   => [
					'mode'        => InputOption::VALUE_OPTIONAL,
					'shortcut'    => '-ip',
					'description' => 'example: -ip 127.0.0.1',
					'default'     => 'AUTO',
				],
				'port' => [
					'mode'        => InputOption::VALUE_OPTIONAL,
					'shortcut'    => '-p',
					'description' => 'example: -p 8000',
					'default'     => '8000',
				],
			],
		],
	];

	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$action = $input->getArgument( 'action' );

		switch ( strtoupper( $action ) )
		{
			case 'OPEN':

				$ip   = $input->getOption( 'ip' );
				$port = $input->getOption( 'port' );

				if ( empty( $ip ) OR $ip === 'AUTO' )
				{
					$hostname = gethostname();
					$ip       = gethostbyname( $hostname );
				}
				elseif ( strpos( $ip, ':' ) !== FALSE )
				{
					$x_ip = explode( ',', $ip );
					$ip   = $x_ip[ 0 ];
					$port = $x_ip[ 1 ];
				}

				$websocket = new \O2System\Libraries\Websocket( $ip, $port );

				break;
		}
	}
}