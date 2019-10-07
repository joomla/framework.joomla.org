<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Package;

use Joomla\Console\Command\AbstractCommand;
use Joomla\FrameworkWebsite\Helper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to synchronize the package listing data
 */
class SyncCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'package:sync';

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
	 * Instantiate the command.
	 *
	 * @param   Helper        $helper        The helper object.
	 * @param   PackageModel  $packageModel  The package model.
	 */
	public function __construct(Helper $helper, PackageModel $packageModel)
	{
		$this->helper       = $helper;
		$this->packageModel = $packageModel;

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

		$symfonyStyle->title('Sync Package Data');

		$packageNames   = array_keys($this->helper->getPackages()->extract('packages')->toArray());
		$loadedPackages = $this->packageModel->getPackageNames();

		foreach ($packageNames as $packageName)
		{
			$displayName = $this->helper->getPackageDisplayName($packageName);

			if (\in_array($packageName, $loadedPackages))
			{
				$packageId = array_search($packageName, $loadedPackages);

				if ($packageId === false)
				{
					$symfonyStyle->warning("Could not find package ID for the $displayName package.");

					continue;
				}

				$this->packageModel->updatePackage(
					(int) $packageId,
					$packageName,
					$this->helper->getPackageDisplayName($packageName),
					$this->helper->getPackageRepositoryName($packageName),
					$this->helper->getPackageStable($packageName),
					$this->helper->getPackageDeprecated($packageName),
					$this->helper->getPackageAbandoned($packageName),
					$this->helper->getPackageHasVersion1($packageName),
					$this->helper->getPackageHasVersion2($packageName)
				);

				$symfonyStyle->comment("Updated $displayName package data.");
			}
			else
			{
				$this->packageModel->addPackage(
					$packageName,
					$this->helper->getPackageDisplayName($packageName),
					$this->helper->getPackageRepositoryName($packageName),
					$this->helper->getPackageStable($packageName),
					$this->helper->getPackageDeprecated($packageName),
					$this->helper->getPackageAbandoned($packageName),
					$this->helper->getPackageHasVersion1($packageName),
					$this->helper->getPackageHasVersion2($packageName)
				);

				$symfonyStyle->comment("$displayName package added to the database.");
			}
		}

		$symfonyStyle->success('Package data synchronized.');

		return 0;
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->setDescription('Synchronizes Framework package data to the database');
	}
}
