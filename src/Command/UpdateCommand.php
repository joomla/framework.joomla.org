<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Update command
 */
class UpdateCommand extends AbstractCommand
{
	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());

		$symfonyStyle->title('Update Server');
		$symfonyStyle->comment('Updating server to git HEAD');

		// Pull from remote repo
		$this->runCommand('cd ' . JPATH_ROOT . ' && git pull 2>&1');

		$symfonyStyle->comment('Updating Composer resources');

		// Run Composer install
		$this->runCommand('cd ' . JPATH_ROOT . ' && composer install --no-dev -o 2>&1');

		// Run Phinx Migrations
		$this->runCommand('cd ' . JPATH_ROOT . ' && vendor/bin/phinx migrate 2>&1');

		// Reset the Twig cache
		$this->getApplication()->getCommand('twig:reset-cache')->execute();

		// Reset the router cache
		$this->getApplication()->getCommand('router:cache')->execute();

		$symfonyStyle->success('Update complete');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('update:server');
		$this->setDescription('Update the server to the current git HEAD');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command updates the server to the current git HEAD

<info>php %command.full_name% %command.name%</info>
EOF
		);
	}

	/**
	 * Execute a command on the server.
	 *
	 * @param   string  $command  The command to execute.
	 *
	 * @return  string  Return data from the command
	 *
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
