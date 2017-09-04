<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Packagist;

use Joomla\Console\AbstractCommand;
use Joomla\FrameworkWebsite\Model\{
	PackageModel, ReleaseModel
};
use Joomla\Http\Http;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to synchronize the release listing with Packagist
 */
class SyncCommand extends AbstractCommand
{
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
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());

		$symfonyStyle->title('Sync Release Data with Packagist');

		foreach ($this->packageModel->getPackages() as $package)
		{
			$symfonyStyle->comment(sprintf('Processing <info>%s</info> package', $package->display));

			$url = "https://packagist.org/packages/joomla/{$package->package}.json";

			try
			{
				$response = $this->http->get($url);
				$data     = json_decode($response->body);

				foreach ($data->package->versions as $versionData)
				{
					// Skip non stable versions
					if (!$this->versionIsStable($versionData->version))
					{
						continue;
					}

					// Make sure this release is logged
					if ($this->releaseModel->hasRelease($package, $versionData->version))
					{
						continue;
					}

					// Add the release
					$this->releaseModel->addRelease($package, $versionData->version);

					$symfonyStyle->comment(
						sprintf('Added <info>%1$s</info> package at version <info>%2$s</info>', $package->display, $versionData->version)
					);
				}
			}
			catch (\RuntimeException $exception)
			{
				$message = "Could not fetch release data for {$package->display} from Packagist";

				$this->getApplication()->getLogger()->warning(
					$message,
					['exception' => $exception]
				);

				$symfonyStyle->error($message);
			}
		}

		$symfonyStyle->success('Update completed.');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('packagist:sync:releases');
		$this->setDescription('Synchronizes release data with Packagist');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command synchronizes the package release data with Packagist

<info>php %command.full_name% %command.name%</info>
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
	private function versionIsStable(string $version) : bool
	{
		if (strpos($version, 'dev') !== false)
		{
			return false;
		}

		if (strpos($version, 'alpha') !== false)
		{
			return false;
		}

		if (strpos($version, 'beta') !== false)
		{
			return false;
		}

		if (strpos($version, 'rc') !== false)
		{
			return false;
		}

		return true;
	}
}
