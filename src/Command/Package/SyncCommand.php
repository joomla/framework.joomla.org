<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Package;

use Joomla\FrameworkWebsite\Helper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to synchronize the package listing data
 */
class SyncCommand extends Command
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

		$symfonyStyle->title('Sync Package Data');

		$packageNames   = array_keys($this->helper->getPackages()->extract('packages')->toArray());
		$loadedPackages = $this->packageModel->getPackageNames();

		foreach ($packageNames as $packageName)
		{
			$displayName = $this->helper->getPackageDisplayName($packageName);

			if (\in_array($packageName, $loadedPackages))
			{
				$packageId = array_search($packageName, $loadedPackages);

				$this->packageModel->updatePackage(
					$packageId,
					$packageName,
					$this->helper->getPackageDisplayName($packageName),
					$this->helper->getPackageRepositoryName($packageName),
					$this->helper->getPackageStable($packageName),
					$this->helper->getPackageDeprecated($packageName),
					$this->helper->getPackageAbandoned($packageName)
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
					$this->helper->getPackageAbandoned($packageName)
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
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command synchronizes the Framework package data to the local database

<info>php %command.full_name%</info>
EOF
		);
	}
}
