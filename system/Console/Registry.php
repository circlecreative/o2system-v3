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

use O2System\Core\Console\Command;
use Symfony\Component\Console\Helper\Table;
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
	 * Console Controller Name
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $name = 'registry';

	/**
	 * Console Controller Description
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $description = 'Registry management console';

	/**
	 * Console Controller Help Arguments Options
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $_help_arguments_options = [
		'flush' => 'flush applications registry',
		'fetch' => 'fetch applications registry',
		'info'  => 'applications registry info',
	];

	/**
	 * Console Controller Help Arguments Options
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $actionCommands = [
		'fetch'  => [
			'description' => 'Fetch applications registry',
		],
		'update' => [
			'description' => 'Update applications registry',
		],
		'flush'  => [
			'description' => 'Flush applications registry',
		],
		'info'   => [
			'description' => 'Applications registry info',
		],
	];

	protected function _executeFetch()
	{
		if ( \O2System::$registry->count() > 0 )
		{
			$this->writeLine( 'REGISTRY_FETCH_REGISTRIES' );

			$handler = \O2System::$registry->getCacheHandler();
			$handler->setCommand( $this );

			if ( $handler->update() === FALSE )
			{
				$this->writeLine( 'REGISTRY_FETCH_REGISTRIES_STRUCTURE', NULL, 'ERROR' );
			}
		}
		else
		{
			$this->writeLine( 'REGISTRY_FETCH_REGISTRIES', NULL, 'ERROR' );
		}
	}

	protected function _executeFlush()
	{
		if ( \O2System::$registry->count() > 0 )
		{
			$helper = $this->getHelper( 'question' );

			$question = new ConfirmationQuestion( '(Y/N) ' . \O2System::$language->line( 'REGISTRY_FLUSH_REGISTRIES_CONFIRM' ) . ' ', FALSE );

			if ( $helper->ask( $this->input, $this->output, $question ) )
			{
				$this->startDebugLine( $this, 'REGISTRY_FLUSH_REGISTRIES_START' );

				foreach ( \O2System::$registry as $key => $value )
				{
					$this->writeDebugLine( $this, 'REGISTRY_FLUSH_REGISTRIES_KEY', [ $key ], 'REMOVE' );
					sleep( 1 );
				}

				if ( \O2System::$registry->destroy() )
				{
					$this->stopDebugLine( $this, 'REGISTRY_FLUSH_REGISTRIES_SUCCESS', [ ], 'FINISHED' );
				}
				else
				{
					$this->stopDebugLine( 'REGISTRY_FLUSH_REGISTRIES_ERROR', 'FINISHED', FALSE );
				}
			}
			else
			{
				$this->writeLine( 'REGISTRY_FLUSH_REGISTRIES_CANCEL', NULL, 'ERROR' );
			}
		}
		else
		{
			$this->writeLine( 'REGISTRY_CURRENTLY_EMPTY_REGISTRIES', NULL, 'ERROR' );
		}
	}

	protected function _executeUpdate()
	{
		if ( \O2System::$registry->count() > 0 )
		{
			$helper = $this->getHelper( 'question' );

			$question = new ConfirmationQuestion( '(Y/N) ' . \O2System::$language->line( 'REGISTRY_UPDATE_REGISTRIES_CONFIRM' ) . ' ', FALSE );

			if ( $helper->ask( $this->input, $this->output, $question ) )
			{
				// Flushing Registries
				$this->startDebugLine( $this, 'REGISTRY_UPDATE_REGISTRIES_START' );

				foreach ( \O2System::$registry as $key => $value )
				{
					$this->writeDebugLine( $this, 'REGISTRY_FLUSH_REGISTRIES_KEY', [ $key ], 'REMOVE' );
					sleep( 1 );
				}

				if ( \O2System::$registry->destroy() )
				{
					$this->stopDebugLine( $this, 'REGISTRY_FLUSH_REGISTRIES_SUCCESS', [ ], 'FINISHED' );
				}
				else
				{
					$this->stopDebugLine( $this, 'REGISTRY_FLUSH_REGISTRIES_ERROR', [ ], 'FINISHED', FALSE );
				}

				$handler = \O2System::$registry->getCacheHandler();
				$handler->setCommand( $this );

				if ( $handler->update() )
				{
					$this->stopDebugLine( $this, 'REGISTRY_UPDATE_REGISTRIES_SUCCESS', [ ], 'FINISHED' );
				}
				else
				{
					$this->stopDebugLine( $this, 'REGISTRY_UPDATE_REGISTRIES_ERROR', [ ], 'FINISHED', FALSE );
				}
			}
			else
			{
				$this->writeLine( 'REGISTRY_UPDATE_REGISTRIES_CANCEL', NULL, 'ERROR' );
			}
		}
		else
		{
			$this->writeLine( 'REGISTRY_CURRENTLY_EMPTY_REGISTRIES', NULL, 'ERROR' );
		}
	}

	protected function _executeInfo()
	{
		$this->writeLine( 'REGISTRY_CHECK_REGISTRIES' );

		if ( \O2System::$registry->count() > 0 )
		{
			$rows = [ ];
			foreach ( \O2System::$registry->info() as $key => $amount )
			{
				$rows[] = [ $key, $amount ];
			}

			$table = new Table( $this->output );
			$table->setHeaders( [ 'Key', 'Num' ] );
			$table->setRows( $rows );
			$table->render();
		}
		else
		{
			$this->writeLine( 'REGISTRY_CURRENTLY_EMPTY_REGISTRIES', NULL, 'ERROR' );
		}
	}
}