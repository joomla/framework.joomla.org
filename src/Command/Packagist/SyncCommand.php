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
use Joomla\FrameworkWebsite\
{
	CommandInterface, PackageAware
};
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Http\Http;
use Joomla\Input\Input;

/**
 * Help command
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class SyncCommand extends AbstractController implements CommandInterface
{
	use PackageAware;

	/**
	 * The HTTP driver
	 *
	 * @var    Http
	 * @since  1.0
	 */
	private $http;

	/**
	 * The package model
	 *
	 * @var    PackageModel
	 * @since  1.0
	 */
	private $packageModel;

	/**
	 * Instantiate the controller.
	 *
	 * @param   Http                 $http          The HTTP driver.
	 * @param   PackageModel         $packageModel  The package model.
	 * @param   Input                $input         The input object.
	 * @param   AbstractApplication  $app           The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(Http $http, PackageModel $packageModel, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->http         = $http;
		$this->packageModel = $packageModel;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Sync Release Data with Packagist');

		foreach (array_keys((array) $this->getPackages()->get('packages')) as $packageName)
		{
			$this->getApplication()->out(
				sprintf('Processing <info>%s</info> package', $packageName)
			);

			$url = "https://packagist.org/packages/joomla/$packageName.json";

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
					if ($this->packageModel->hasRelease($packageName, $versionData->version))
					{
						continue;
					}

					// Add the release
					$this->packageModel->addRelease($packageName, $versionData->version);

					$this->getApplication()->out(
						sprintf('Added <info>%1$s</info> package at version <info>%2$s</info>', $packageName, $versionData->version)
					);
				}
			}
			catch (\RuntimeException $exception)
			{
				$message = "Could not fetch release data for $packageName from Packagist";

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
	 *
	 * @since   1.0
	 */
	public function getDescription() : string
	{
		return 'Synchronizes release data with Packagist.';
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
		return 'Packagist Sync';
	}

	/**
	 * Check if a version string represents a stable version
	 *
	 * @param   string  $version  Version string to check
	 *
	 * @return  bool
	 *
	 * @since   1.0
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
