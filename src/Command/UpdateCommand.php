<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\CommandInterface;

/**
 * Update command
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class UpdateCommand extends AbstractController implements CommandInterface
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Update Server');

		$this->getApplication()->out('<info>Updating server to git HEAD</info>');

		// Pull from remote repo
		$this->runCommand('cd ' . JPATH_ROOT . ' && git pull 2>&1');

		$this->getApplication()->out('<info>Updating Composer resources</info>');

		// Run Composer install
		$this->runCommand('cd ' . JPATH_ROOT . ' && composer install --no-dev -o 2>&1');

		// Write the current build to a local file
		$this->getApplication()->out('<info>Writing build info</info>');

		$path = JPATH_ROOT . '/current_SHA';

		// Get the build information
		$sha = trim($this->runCommand('cd ' . JPATH_ROOT . ' && git rev-parse --short HEAD 2>&1'));

		if (!file_put_contents($path, $sha))
		{
			throw new \RuntimeException('Can not write to path: ' . $path);
		}

		$this->getApplication()->out('<info>Update complete</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getDescription() : string
	{
		return 'Update the server to the current git HEAD.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle() : string
	{
		return 'Update Server';
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
	private function runCommand(string $command) : string
	{
		$lastLine = system($command, $status);

		if ($status)
		{
			// Command exited with a status != 0
			if ($lastLine)
			{
				throw new \RuntimeException($lastLine);
			}

			throw new \RuntimeException('An unknown error occurred');
		}

		return $lastLine;
	}
}
