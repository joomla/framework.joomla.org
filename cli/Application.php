<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatusCli;

use Joomla\Application\AbstractCliApplication;
use Joomla\Application\Cli\Output\Processor\ColorProcessor;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

use Joomla\Status\Service\ConfigurationProvider;
use Joomla\Status\Service\DatabaseProvider;
use Joomla\StatusCli\Command\Install;
use Joomla\StatusCli\Command\ParseComposer;
use Joomla\StatusCli\Command\RunTests;

use Joomla\StatusCli\Command\UpdateServer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * CLI application for the Framework Status Application
 *
 * @since  1.0
 */
class Application extends AbstractCliApplication implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Class constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$container = (new Container)
			->registerServiceProvider(new ConfigurationProvider)
			->registerServiceProvider(new DatabaseProvider);

		$this->setContainer($container);

		parent::__construct();

		// Set up the output processor
		$this->getOutput()->setProcessor(new ColorProcessor);
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function doExecute()
	{
		// If --install option provided, run the install routine to set up the database
		if ($this->input->getBool('install', false))
		{
			(new Install($this))->execute();
		}
		// If --updateserver option provided, run the update routine
		elseif ($this->input->getBool('updateserver', false))
		{
			(new UpdateServer($this))->execute();
		}
		// Otherwise execute the normal routine
		else
		{
			$packages = (new ParseComposer($this))->execute();
			(new RunTests($this, $packages))->execute();
		}

		$this->out('Finished!');
	}

	/**
	 * Custom initialisation method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function initialise()
	{
		$logger = new Logger('Joomla-Framework-Status');
		$logger->pushHandler(new StreamHandler(JPATH_ROOT . '/logs/cron.log'));

		$this->setLogger($logger);
	}

	/**
	 * Execute a command on the server.
	 *
	 * @param   string  $command  The command to execute.
	 *
	 * @return  string  Return data from the command
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function runCommand($command)
	{
		$lastLine = system($command, $status);

		if ($status)
		{
			// Command exited with a status != 0
			if ($lastLine)
			{
				$this->out($lastLine);

				throw new \RuntimeException($lastLine);
			}

			$this->out('An unknown error occurred');

			throw new \RuntimeException('An unknown error occurred');
		}

		return $lastLine;
	}
}
