<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatusCli;

use Joomla\Application\AbstractCliApplication;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

use Joomla\Status\Service\ConfigurationProvider;
use Joomla\Status\Service\DatabaseProvider;
use Joomla\Status\Service\TwigRendererProvider;

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
			->registerServiceProvider(new DatabaseProvider)
			->registerServiceProvider(new TwigRendererProvider($this));

		$this->setContainer($container);

		// Set error reporting based on config
		$errorReporting = (int) $container->get('config')->get('errorReporting', 0);
		error_reporting($errorReporting);

		parent::__construct(null, $container->get('config'));
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
			(new Command\Install($this))->execute();
		}
		// If --updateserver option provided, run the update routine
		elseif ($this->input->getBool('updateserver', false))
		{
			(new Command\UpdateServer($this))->execute();
		}
		// If --resettwig option provided, reset the Twig cache
		elseif ($this->input->getBool('resettwig', false))
		{
			(new Command\ResetTwigCache($this))->execute();
		}
		// Otherwise execute the normal routine
		else
		{
			(new Command\ParseComposer($this))->execute();
			(new Command\RunTests($this))->execute();
		}

		$this->out('Finished!');
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
