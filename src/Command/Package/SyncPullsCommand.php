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
     * The number of pull requests to fetch per request, this is the maximum supported by the GitHub API
     *
     * @var  integer
     */
    private const PULLS_PER_PAGE = 100;

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

        $hasErrors = false;

        foreach ($packages as $name => $package) {
            $symfonyStyle->write('Retrieving PRs for ' . $name);
            try {
                $repoName = $name;
                if (isset($package['repo'])) {
                    $repoName = $package['repo'];
                    $symfonyStyle->write(' (' . $repoName . ')');
                }
                $symfonyStyle->write(': ');
                $pullcount = $this->countOpenPulls($repoName);
            } catch (UnexpectedResponseException $exception) {
                // Keep the stored count, an API error must not reset it
                $symfonyStyle->error($exception->getMessage());
                $hasErrors = true;

                continue;
            }
            $query = $this->database->getQuery(true)
                ->update('#__packages')
                ->set($this->database->quoteName('pullcount') . ' = :pullcount')
                ->where($this->database->quoteName('package') . ' = :packagename');
            $query->bind('pullcount', $pullcount, ParameterType::INTEGER)
                ->bind('packagename', $name, ParameterType::STRING);
            $this->database->setQuery($query)->execute();
            $symfonyStyle->writeln($pullcount);
        }

        if ($hasErrors) {
            $symfonyStyle->error('Pull data synchronized with errors, the data of some packages could not be updated.');

            return 1;
        }

        $symfonyStyle->success('Pull data synchronized.');

        return 0;
    }

    /**
     * Count the open pull requests of a repository
     *
     * The GitHub API returns paginated results, so all pages have to be fetched to get the full count.
     *
     * @param   string  $repoName  The repository name
     *
     * @return  integer
     *
     * @throws  UnexpectedResponseException
     */
    private function countOpenPulls(string $repoName): int
    {
        $count = 0;
        $page  = 1;

        do {
            $pulls  = $this->github->pulls->getList('joomla-framework', $repoName, 'open', $page, self::PULLS_PER_PAGE);
            $count += \count($pulls);
            $page++;
        } while (\count($pulls) === self::PULLS_PER_PAGE);

        return $count;
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
