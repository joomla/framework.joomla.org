<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Package;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\FrameworkWebsite\Helper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Github\Github;
use Joomla\Http\Exception\UnexpectedResponseException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to synchronize the pull requests for packages
 */
class SyncPullsCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var  string|null
     */
    protected static $defaultName = 'package:sync:pulls';

    /**
     * The github object
     *
     * @var  Github
     */
    private $github;

    /**
     * The helper object
     *
     * @var  Helper
     */
    private $helper;

    /**
     * The database object
     *
     * @var  DatabaseInterface
     */
    private $database;

    /**
     * Instantiate the command.
     *
     * @param   Github             $github  The github object.
     * @param   Helper             $helper  The helper.
     * @param   DatabaseInterface  $database  The database object
     */
    public function __construct(Github $github, Helper $helper, DatabaseInterface $database)
    {
        $this->github   = $github;
        $this->helper   = $helper;
        $this->database = $database;
        parent::__construct();
    }

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
        $symfonyStyle->title('Sync Package Pulls Data');
        $packages   = $this->helper->getPackages()->extract('packages')->toArray();

        foreach ($packages as $name => $package) {
            $symfonyStyle->write('Retrieving PRs for ' . $name);
            try {
                $packageName = $name;
                if (isset($package['repo'])) {
                    $packageName = $package['repo'];
                    $symfonyStyle->write(' (' . $packageName . ')');
                }
                $symfonyStyle->write(': ');
                $pulls     = $this->github->pulls->getList('joomla-framework', $packageName);
                $pullcount = count($pulls);
            } catch (UnexpectedResponseException $exception) {
                $symfonyStyle->error($exception->getMessage());
                $pullcount = 0;
            }
            $query = $this->database->getQuery(true)
                ->update('#__packages')
                ->set($this->database->quoteName('pullcount') . ' = :pullcount')
                ->where($this->database->quoteName('package') . ' = :packagename');
            $query->bind('pullcount', $pullcount, ParameterType::INTEGER)
                ->bind('packagename', $packageName, ParameterType::STRING);
            $this->database->setQuery($query)->execute();
            $symfonyStyle->writeln($pullcount);
        }

        $symfonyStyle->success('Pull data synchronized.');

        return 0;
    }

    /**
     * Configures the current command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('Synchronizes pull request data about Framework packages to the database');
    }
}
