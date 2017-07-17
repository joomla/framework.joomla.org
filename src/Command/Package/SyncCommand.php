<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Package;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\{
	CommandInterface, Helper
};
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Input\Input;

/**
 * Command to get download counts from Packagist
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 */
class SyncCommand extends AbstractController implements CommandInterface
{
	/**
	 * The helper object
	 *
	 * @var  Helper
	 */
	private $helper;

	/**
	 * The package model
	 *
	 * @var  PackageModel
	 */
	private $packageModel;

	/**
	 * Instantiate the controller.
	 *
	 * @param   Helper               $helper        The helper object.
	 * @param   PackageModel         $packageModel  The package model.
	 * @param   Input                $input         The input object.
	 * @param   AbstractApplication  $app           The application object.
	 */
	public function __construct(Helper $helper, PackageModel $packageModel, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->helper       = $helper;
		$this->packageModel = $packageModel;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle($this->getTitle());

		$packageNames   = array_keys($this->helper->getPackages()->extract('packages')->toArray());
		$loadedPackages = $this->packageModel->getPackageNames();

		foreach ($packageNames as $packageName)
		{
			$displayName = $this->helper->getPackageDisplayName($packageName);

			if (in_array($packageName, $loadedPackages))
			{
				$packageId = array_search($packageName, $loadedPackages);

				$this->packageModel->updatePackage(
					$packageId,
					$packageName,
					$this->helper->getPackageDisplayName($packageName),
					$this->helper->getPackageRepositoryName($packageName),
					$this->helper->getPackageStable($packageName),
					$this->helper->getPackageDeprecated($packageName)
				);

				$this->getApplication()->out("<info>Updated $displayName package data.</info>");
			}
			else
			{
				$this->packageModel->addPackage(
					$packageName,
					$this->helper->getPackageDisplayName($packageName),
					$this->helper->getPackageRepositoryName($packageName),
					$this->helper->getPackageStable($packageName),
					$this->helper->getPackageDeprecated($packageName)
				);

				$this->getApplication()->out("<info>$displayName package added to the database.</info>");
			}
		}

		$this->getApplication()->out('<info>Package data synchronized.</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 */
	public function getDescription() : string
	{
		return 'Synchronizes the Framework package data to the database.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle() : string
	{
		return 'Sync Package Data';
	}
}
