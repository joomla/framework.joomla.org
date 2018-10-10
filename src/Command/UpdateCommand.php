<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Update command
 */
class UpdateCommand extends Command
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'update:server';

	/**
	 * Executes the current command.
	 *
	 * @param   InputInterface   $input   The command input.
	 * @param   OutputInterface  $output  The command output.
	 *
	 * @return  integer|null  null or 0 if everything went fine, or an error code
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Update Server');
		$symfonyStyle->comment('Updating server to git HEAD');

		// Pull from remote repo
		try
		{
			(new Process('git pull', JPATH_ROOT))->mustRun();
		}
		catch (ProcessFailedException $e)
		{
			$this->getApplication()->getLogger()->error('Could not execute `git pull`', ['exception' => $e]);

			$symfonyStyle->error('Error running `git pull`: ' . $e->getMessage());

			return 1;
		}

		$symfonyStyle->comment('Updating Composer resources');

		// Run Composer install
		try
		{
			(new Process('composer install --no-dev -o -a', JPATH_ROOT))->mustRun();
		}
		catch (ProcessFailedException $e)
		{
			$this->getApplication()->getLogger()->error('Could not update Composer resources', ['exception' => $e]);

			$symfonyStyle->error('Error updating Composer resources: ' . $e->getMessage());

			return 1;
		}

		$symfonyStyle->comment('Running database migrations');

		// Run Phinx Migrations
		try
		{
			(new Process('vendor/bin/phinx migrate', JPATH_ROOT))->mustRun();
		}
		catch (ProcessFailedException $e)
		{
			$this->getApplication()->getLogger()->error('Could not run database migrations', ['exception' => $e]);

			$symfonyStyle->error('Error running database migrations: ' . $e->getMessage());

			return 1;
		}

		// Reset the Twig cache
		$this->getApplication()->find('twig:reset-cache')->run(
			new ArrayInput(
				[
					'command' => 'twig:reset-cache',
				]
			),
			$output
		);

		$symfonyStyle->success('Update complete');

		return 0;
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->setDescription('Update the server to the current git HEAD');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command updates the server to the current git HEAD

<info>php %command.full_name%</info>
EOF
		);
	}
}
