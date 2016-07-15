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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://o2system.center
 * @since          Version 3.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\Console;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Console Registry
 *
 * This class contains functions that enable config files to be managed
 *
 * @package        O2System
 * @subpackage     core
 * @category       Core Library Class
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/console/registry.html
 */
class Registry extends Command
{
	/**
	 * Configure
	 *
	 * Configure console application
	 *
	 * @access  protected
	 */
	protected function configure()
	{
		$this->setName( 'registry' )
			->setDescription( 'Registry manager console commands' )
			->addArgument(
				'action',
				InputArgument::REQUIRED,
				'Avaliable action: ' . PHP_EOL .
				'1. flush : Flushing your applications registry.' . PHP_EOL .
				'2. fetch : Fetch your applications registry.' . PHP_EOL .
				'3. info  : Your applications registry info metadata.'
			);
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
		$action = $input->getArgument( 'action' );

		switch ( $action )
		{
			case 'flush':

				if ( count( \O2System::$registry ) > 0 )
				{
					$helper = $this->getHelper( 'question' );

					$question = new ConfirmationQuestion( ' ::: [ CONFIRM ] ' . \O2System::$language->line( 'REGISTRY_CONFIRM_FLUSHREGISTRIES' ), FALSE );

					if ( $helper->ask( $input, $output, $question ) )
					{
						$output->writeln( PHP_EOL . ' ::: [ INFO ] ' . \O2System::$language->line( 'REGISTRY_INFO_STARTFLUSHINGREGISTRIES' ) . '...' . PHP_EOL );

						foreach ( \O2System::$registry as $key => $value )
						{
							$output->writeln( ' ::: [ INFO ] ' . str_replace( '%s', $key, \O2System::$language->line( 'REGISTRY_INFO_FLUSHREGISTRIES' ) ) );
							sleep( 1 );
						}

						if ( \O2System::$registry->destroy() )
						{
							$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ SUCCESS ] ' . \O2System::$language->line( 'REGISTRY_SUCCESS_FLUSHREGISTRIES' ) );
						}
						else
						{
							$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ SUCCESS ] ' . \O2System::$language->line( 'REGISTRY_ERROR_CREATEREGISTRIES' ) );
						}
					}
				}
				else
				{
					$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ WARNING ] ' . \O2System::$language->line( 'REGISTRY_WARNING_EMPTYREGISTRIES' ) );
				}

				break;

			case 'fetch':

				if ( count( \O2System::$registry ) > 0 )
				{
					$output->writeln( PHP_EOL . ' ::: [ INFO ] ' . \O2System::$language->line( 'REGISTRY_INFO_FETCHGREGISTRIESTRUCTURE' ) . ': ' . ROOTPATH );

					if ( \O2System::$registry->fetch() )
					{
						$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ SUCCESS ] ' . \O2System::$language->line( 'REGISTRY_SUCCESS_CREATEREGISTRIES' ) );
					}
				}
				else
				{
					$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ ERROR ] ' . \O2System::$language->line( 'REGISTRY_WARNING_NOTEMPTYREGISTRIES' ) );
				}

				break;

			case 'update':

				$helper   = $this->getHelper( 'question' );
				$question = new ConfirmationQuestion( ' ::: [ CONFIRM ] ' . \O2System::$language->line( 'REGISTRY_CONFIRM_UPDATEREGISTRIES' ) . ' ', FALSE );

				if ( $helper->ask( $input, $output, $question ) )
				{
					$output->writeln( PHP_EOL . ' ::: [ INFO ] ' . \O2System::$language->line( 'REGISTRY_INFO_FLUSHREGISTRIESSTART' ) . '...' );
					$output->writeln( PHP_EOL . ' ::: [ INFO ] ' . \O2System::$language->line( 'REGISTRY_INFO_FLUSHREGISTRIESOLD' ) . '...' );
					$output->writeln( PHP_EOL . ' ::: [ INFO ] ' . \O2System::$language->line( 'REGISTRY_INFO_FETCHGREGISTRIESTRUCTURE' ) . ': ' . ROOTPATH );

					$num_registry = count( \O2System::$registry );

					if ( $num_registry > 0 )
					{
						if ( \O2System::$registry->destroy() )
						{
							if ( \O2System::$registry->fetch() )
							{
								$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ SUCCESS ] ' . \O2System::$language->line( 'REGISTRY_SUCCESS_UPDATEREGISTRIES' ) );
							}
							else
							{
								$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ ERROR ] ' . \O2System::$language->line( 'REGISTRY_ERROR_UPDATEREGISTRIES' ) );
							}
						}
						else
						{
							$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ ERROR ] ' . \O2System::$language->line( 'REGISTRY_ERROR_UPDATEREGISTRIES' ) );
						}
					}
					else
					{

						$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ WARNING ] ' . \O2System::$language->line( 'REGISTRY_WARNING_EMPTYREGISTRIES' ) );

						$output->writeln( PHP_EOL . ' ::: [ INFO ] ' . \O2System::$language->line( 'REGISTRY_INFO_FETCHGREGISTRIESTRUCTURE' ) . ': ' . ROOTPATH );

						if ( \O2System::$registry->fetch() )
						{
							$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ SUCCESS ] ' . \O2System::$language->line( 'REGISTRY_SUCCESS_CREATEREGISTRIES' ) );
						}
						else
						{
							$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ ERROR ] ' . \O2System::$language->line( 'REGISTRY_ERROR_CREATEREGISTRIES' ) );
						}
					}
				}

				break;

			case 'info':

				$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ INFO ] ' . \O2System::$language->line( 'REGISTRY_INFO_CHECKREGISTRIES' ) . '...' );

				if ( count( \O2System::$registry ) > 0 )
				{
					$output->writeln( print_r( \O2System::$registry->info(), TRUE ) );
				}
				else
				{
					$output->writeln( PHP_EOL . PHP_EOL . ' ::: [ WARNING ] ' . \O2System::$language->line( 'REGISTRY_WARNING_EMPTYREGISTRIES' ) );
				}

				break;
		}
	}
}