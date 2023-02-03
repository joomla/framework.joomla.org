<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Update command
 */
class UpdateCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var  string|null
     */
    protected static $defaultName = 'update:server';

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Update Server');
        $symfonyStyle->comment('Updating server to git HEAD');
        if (!$this->getHelperSet()) {
            $symfonyStyle->error('The helper set has not been registered to the update command.');
            return 1;
        }

        /** @var ProcessHelper $processHelper */
        $processHelper = $this->getHelperSet()->get('process');

        // Pull from remote repo
        try {
            $processHelper->mustRun($output, new Process(['git', 'pull'], JPATH_ROOT));
        } catch (ProcessFailedException $e) {
            $this->getApplication()->getLogger()->error('Could not execute `git pull`', ['exception' => $e]);
            $symfonyStyle->error('Error running `git pull`: ' . $e->getMessage());
            return 1;
        }

        $symfonyStyle->comment('Updating Composer resources');

        // Run Composer install
        try {
            $processHelper->mustRun($output, new Process(['composer', 'install', '--no-dev', '-o', '-a'], JPATH_ROOT));
        } catch (ProcessFailedException $e) {
            $this->getApplication()->getLogger()->error('Could not update Composer resources', ['exception' => $e]);
            $symfonyStyle->error('Error updating Composer resources: ' . $e->getMessage());
            return 1;
        }

        $symfonyStyle->comment('Running database migrations');

        // Run Phinx Migrations
        try {
            $processHelper->mustRun($output, new Process(['vendor/bin/phinx', 'migrate'], JPATH_ROOT));
        } catch (ProcessFailedException $e) {
            $this->getApplication()->getLogger()->error('Could not run database migrations', ['exception' => $e]);
            $symfonyStyle->error('Error running database migrations: ' . $e->getMessage());
            return 1;
        }

        // Reset the Twig cache
        $this->getApplication()->getCommand('twig:reset-cache')->execute(new ArrayInput([
                    'command' => 'twig:reset-cache',
                ]), $output);
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
    }
}
