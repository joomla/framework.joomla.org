<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatusCli\Command;

use Joomla\StatusCli\Application;

/**
 * CLI Command to run the package test suites and generate reports
 *
 * @since  1.0
 */
class RunTests
{
	/**
	 * Application object
	 *
	 * @var    Application
	 * @since  1.0
	 */
	private $app;

	/**
	 * Class constructor
	 *
	 * @param   Application  $app  Application object
	 *
	 * @since   1.0
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Execute the command
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// Need to debug - Serialization of 'Closure' is not allowed
		return;

		// Use a DirectoryIterator object to loop over each package
		$iterator = new \DirectoryIterator(JPATH_ROOT . '/vendor/joomla');

		/* @type  $directory  \DirectoryIterator */
		foreach ($iterator as $directory)
		{
			if (!$directory->isDot() && $directory->isDir())
			{
				$this->app->out('Processing the ' . $directory->getFilename() . ' package.');

				// Check if a test config exists for the package
				if (file_exists(JPATH_TESTS . '/phpunit.' . $directory->getFilename() . '.xml'))
				{
					$command = new \PHPUnit_TextUI_Command;

					$options = [
						'--configuration=' . JPATH_TESTS . '/phpunit.' . $directory->getFilename() . '.xml'
					];

					$returnVal = $command->run($options, false);
				}
				else
				{
					$this->app->out('No test config exists for the ' . $directory->getFilename() . ' package.');
				}
			}
		}
	}
}
