<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 19-Jul-16
 * Time: 7:40 PM
 */

namespace O2System\Core\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
	/**
	 * Console Controller Name
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $name;

	/**
	 * Console Controller Description
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $description;

	/**
	 * Console Controller Help Arguments Options
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $configurations = [ ];

	protected $input;
	protected $action;
	protected $output;
	protected $debugFormatter;

	/**
	 * Configure Controller
	 *
	 * @access  protected
	 * @throws  \O2System\Exception
	 */
	protected function configure()
	{
		if ( isset( $this->name ) )
		{
			$this->setName( $this->name );
			$this->setDescription( $this->description );

			if ( ! empty( $this->actionCommands ) )
			{
				$arguments_descriptions = '';
				$options                = [ ];

				$i_action = 1;
				foreach ( $this->actionCommands as $command => $action )
				{
					$arguments_descriptions .= $i_action . '. ' . $command . ' [ ' . ucfirst( $action[ 'description' ] ) . ' ]' . PHP_EOL;

					if ( isset( $action[ 'options' ] ) )
					{
						foreach ( $action[ 'options' ] as $option => $setting )
						{
							$setting[ 'shortcut' ]    = empty( $setting[ 'shortcut' ] ) ? NULL : $setting[ 'shortcut' ];
							$setting[ 'mode' ]        = isset( $setting[ 'mode' ] ) ? $setting[ 'mode' ] : InputArgument::OPTIONAL;
							$setting[ 'description' ] = empty( $setting[ 'description' ] ) ? NULL : ucfirst( $setting[ 'description' ] );
							$setting[ 'default' ]     = empty( $setting[ 'default' ] ) ? NULL : $setting[ 'default' ];

							if ( isset( $setting[ 'values' ] ) AND is_array( $setting[ 'values' ] ) )
							{
								$setting[ 'description' ] = empty( $setting[ 'description' ] ) ? NULL : ucfirst( $setting[ 'description' ] );

								if ( $setting[ 'mode' ] == InputArgument::OPTIONAL )
								{
									$setting[ 'description' ] .= PHP_EOL . 'Optional Values: ';
								}
								elseif ( $setting[ 'mode' ] == InputArgument::REQUIRED )
								{
									$setting[ 'description' ] .= PHP_EOL . 'Listed Values: ';
								}

								foreach ( $setting[ 'values' ] as $value => $description )
								{
									$setting[ 'description' ] .= '[' . ucfirst( $description ) . ': "' . $value . '"]';
								}
							}

							$options[ $option ] = $setting;
						}
					}
				}

				$this->addArgument( 'action', InputArgument::REQUIRED, $arguments_descriptions );

				ksort( $options );

				if ( count( $options ) > 0 )
				{
					foreach ( $options as $option => $setting )
					{
						$this->addOption( $option, $setting[ 'shortcut' ], $setting[ 'mode' ], $setting[ 'description' ], $setting[ 'default' ] );
					}
				}
			}
		}
	}

	/**
	 * Execute
	 *
	 * Execute console command
	 *
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$this->input  =& $input;
		$this->output =& $output;
		$this->action = studlycapcase( $input->getArgument( 'action' ) );

		if ( method_exists( $this, $method = '_execute' . $this->action ) )
		{
			return call_user_func( [ $this, $method ] );
		}
	}

	public function printOut( $output, $halt = TRUE )
	{
		echo PHP_EOL . str_repeat( '-', strlen( $output ) / 2 ) . ' DEBUG OUTPUT ' . str_repeat( '-', strlen( $output ) / 2 ) . PHP_EOL . PHP_EOL;
		print_r( $output );
		echo PHP_EOL . PHP_EOL . str_repeat( '-', strlen( $output ) / 2 ) . ' DEBUG OUTPUT ' . str_repeat( '-', strlen( $output ) / 2 ) . PHP_EOL;

		if ( $halt === TRUE )
		{
			die;
		}
	}

	public function writeLine( $line, $header = NULL, $type = 'INFO', $vars = [ ] )
	{
		$formatter = $this->getHelper( 'formatter' );

		if ( is_string( $line ) )
		{
			$message = \O2System::$language->line( $line . '_MESSAGE_' . strtoupper( $type ), $vars );
			$message = empty( $message ) ? \O2System::$language->line( $line, $vars ) : $message;

			if ( is_null( $header ) )
			{
				$header = \O2System::$language->line( $line . '_HEADER' );
			}
			else
			{
				$header = \O2System::$language->line( $header );
			}

			if ( empty( $header ) )
			{
				$message = PHP_EOL . $formatter->formatBlock( [ $message ], strtolower( $type ), TRUE );
			}
			else
			{
				$message = PHP_EOL . $formatter->formatBlock( [ $header, $message ], strtolower( $type ), TRUE );
			}
		}
		elseif ( is_array( $line ) )
		{
			$lines = '';
			foreach ( $line as $key => $value )
			{
				$lines .= str_pad( $key, 15 ) . ' : ' . $value . PHP_EOL;
			}

			if ( isset( $header ) )
			{
				$header  = \O2System::$language->line( $header );
				$message = $formatter->formatBlock( [ $header, $lines ], strtolower( $type ), TRUE );
			}
			else
			{
				$message = $lines;
			}
		}

		$this->output->writeln( $message );
		time_nanosleep( 0, 200000000 );
	}

	public function startDebugLine( $id, $line, array $vars = [ ], $prefix = 'STARTED' )
	{
		if ( empty( $this->debugFormatter ) )
		{
			$this->debugFormatter = $this->getHelper( 'debug_formatter' );
		}

		$message = \O2System::$language->line( $line, $vars );

		$this->output->writeln(
			$this->debugFormatter->start(
				spl_object_hash( $id ),
				$message,
				$prefix
			) );
	}

	public function writeDebugLine( $id, $line, array $vars = [ ], $prefix = 'OUT', $error = FALSE )
	{
		$message = \O2System::$language->line( $line, $vars );

		if ( $error === FALSE )
		{
			$this->output->writeln(
				$this->debugFormatter->start(
					spl_object_hash( $id ),
					$message,
					$prefix
				) );
		}
		else
		{
			$this->output->writeln(
				$this->debugFormatter->stop(
					spl_object_hash( $id ),
					$message,
					FALSE,
					$prefix
				) );
		}
	}

	public function stopDebugLine( $id, $line, array $vars = [ ], $prefix = 'OUT', $error = TRUE )
	{
		$message = \O2System::$language->line( $line, $vars );

		$this->output->writeln(
			$this->debugFormatter->stop(
				spl_object_hash( $id ),
				$message,
				$error,
				$prefix
			) );
	}
}