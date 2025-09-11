<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Packagist;

use Joomla\Console\Command\AbstractCommand;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Model\ReleaseModel;
use Joomla\Http\Http;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to synchronize the release listing with Packagist
 */
class SyncCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var  string|null
     */
    protected static $defaultName = 'packagist:sync:releases';

    /**
     * The HTTP driver
     *
     * @var  Http
     */
    private $http;

    /**
     * The package model
     *
     * @var  PackageModel
     */
    private $packageModel;

    /**
     * The release model object.
     *
     * @var  ReleaseModel
     */
    private $releaseModel;

    /**
     * Instantiate the command.
     *
     * @param   Http          $http          The HTTP driver.
     * @param   PackageModel  $packageModel  The package model.
     * @param   ReleaseModel  $releaseModel  The package model.
     */
    public function __construct(Http $http, PackageModel $packageModel, ReleaseModel $releaseModel)
    {
        $this->http         = $http;
        $this->packageModel = $packageModel;
        $this->releaseModel = $releaseModel;
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
        $symfonyStyle->title('Sync Release Data with Packagist');
        $updateReleases  = $input->getOption('update');
        $addedReleases   = 0;
        $updatedReleases = 0;
        foreach ($this->packageModel->getPackages() as $package) {
            $symfonyStyle->comment(sprintf('Processing <info>%s</info> package', $package->display));
            $url = "https://packagist.org/packages/joomla/{$package->package}.json";
            try {
                $response = $this->http->get($url);
                $data     = json_decode((string) $response->getBody());
                if (!isset($data->package)) {
                    var_dump($data);
                } else {
                    foreach ($data->package->versions as $versionData) {
                        // Skip non stable versions
                        if (!$this->versionIsStable($versionData->version)) {
                            continue;
                        }

                        // Make sure this release is logged, or update if specified
                        if ($this->releaseModel->hasRelease($package, $versionData->version)) {
                            if (!$updateReleases) {
                                continue;
                            }

                            $record = $this->releaseModel->getRelease($package, $versionData->version);
                            $this->releaseModel->updateRelease($record->id, $package, $versionData->version, new \DateTime($versionData->time));
                            $updatedReleases++;
                            $symfonyStyle->comment(sprintf('Updated <info>%1$s</info> package at version <info>%2$s</info>', $package->display, $versionData->version));
                        } else {
                            // Add the release
                            $this->releaseModel->addRelease($package, $versionData->version, new \DateTime($versionData->time));
                            $addedReleases++;
                            $symfonyStyle->comment(sprintf('Added <info>%1$s</info> package at version <info>%2$s</info>', $package->display, $versionData->version));
                        }
                    }
                }
            } catch (\RuntimeException $exception) {
                $message = "Could not fetch release data for {$package->display} from Packagist";
                $this->getApplication()->getLogger()->warning($message, ['exception' => $exception]);
                $symfonyStyle->error($message);
            }
        }

        $symfonyStyle->success(sprintf('Update completed; %1$d releases added and %2$d releases updated.', $addedReleases, $updatedReleases));
        return 0;
    }

    /**
     * Configures the current command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('Synchronizes release data with Packagist');
        $this->addOption('update', null, InputOption::VALUE_NONE, 'Flag indicating existing releases should be updated');
        $this->setHelp(
            <<<'EOF'
The <info>%command.name%</info> command synchronizes the package release data with Packagist

<info>php %command.full_name%</info>

By default this command will only add new releases to the database. To update existing release
data, you can pass the <info>--update</info> option.

<info>php %command.full_name% --update</info>
EOF
        );
    }

    /**
     * Check if a version string represents a stable version
     *
     * @param   string  $version  Version string to check
     *
     * @return  boolean
     */
    private function versionIsStable(string $version): bool
    {
        if (strpos($version, 'dev') !== false) {
            return false;
        }

        if (strpos($version, 'alpha') !== false) {
            return false;
        }

        if (strpos($version, 'beta') !== false) {
            return false;
        }

        if (strpos($version, 'rc') !== false) {
            return false;
        }

        return true;
    }
}
