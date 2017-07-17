<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Packagist;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\CommandInterface;
use Joomla\FrameworkWebsite\Model\{
	PackageModel, ReleaseModel
};
use Joomla\Http\Http;
use Joomla\Input\Input;

/**
 * Command to synchronize the release listing with Packagist
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 */
class SyncCommand extends AbstractController implements CommandInterface
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
	 * Instantiate the controller.
	 *
	 * @param   Http                 $http          The HTTP driver.
	 * @param   PackageModel         $packageModel  The package model.
	 * @param   ReleaseModel         $releaseModel  The package model.
	 * @param   Input                $input         The input object.
	 * @param   AbstractApplication  $app           The application object.
	 */
	public function __construct(
		Http $http,
		PackageModel $packageModel,
		ReleaseModel $releaseModel,
		Input $input = null,
		AbstractApplication $app = null
	)
	{
		parent::__construct($input, $app);

		$this->http         = $http;
		$this->packageModel = $packageModel;
		$this->releaseModel = $releaseModel;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Sync Release Data with Packagist');

		foreach ($this->packageModel->getPackages() as $package)
		{
			$this->getApplication()->out(
				sprintf('Processing <info>%s</info> package', $package->display)
			);

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

					$this->getApplication()->out(
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

				$this->getApplication()->out("<error>$message</error>");
			}
		}

		$this->getApplication()->out('<info>Update completed.</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 */
	public function getDescription() : string
	{
		return 'Synchronizes release data with Packagist.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle() : string
	{
		return 'Packagist Sync';
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
