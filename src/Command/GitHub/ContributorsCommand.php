<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\GitHub;

use Joomla\Console\AbstractCommand;
use Joomla\FrameworkWebsite\Helper\GitHubHelper;
use Joomla\FrameworkWebsite\Model\PackageModel;

/**
 * Command to get contributor information from GitHub
 */
class ContributorsCommand extends AbstractCommand
{
	/**
	 * The GitHub helper
	 *
	 * @var  GitHubHelper
	 */
	private $githubHelper;

	/**
	 * The package model
	 *
	 * @var  PackageModel
	 */
	private $packageModel;

	/**
	 * Instantiate the command.
	 *
	 * @param   PackageModel  $packageModel  The package model.
	 * @param   GitHubHelper  $githubHelper  The GitHub helper.
	 */
	public function __construct(PackageModel $packageModel, GitHubHelper $githubHelper)
	{
		$this->githubHelper = $githubHelper;
		$this->packageModel = $packageModel;

		parent::__construct();
	}

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Sync GitHub Contributors');

		foreach ($this->packageModel->getPackages() as $package)
		{
			$symfonyStyle->comment("Processing {$package->display} package");
			$this->githubHelper->syncPackageContributors($package->repo);
		}

		$symfonyStyle->comment('Processing user data.');

		$this->githubHelper->syncUserData();

		$symfonyStyle->comment('Processing commit counts.');

		$this->githubHelper->updateCommitCounts();

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
		$this->setName('github:contributors');
		$this->setDescription('Fetches contributor information from GitHub');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command fetches the contributor information for the Framework packages from GitHub

<info>php %command.full_name% %command.name%</info>
EOF
		);
	}
}
