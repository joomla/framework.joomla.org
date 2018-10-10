<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\GitHub;

use Joomla\FrameworkWebsite\Helper\GitHubHelper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to get contributor information from GitHub
 */
class ContributorsCommand extends Command
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'github:contributors';

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
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->setDescription('Fetches contributor information from GitHub');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command fetches the contributor information for the Framework packages from GitHub

<info>php %command.full_name%</info>
EOF
		);
	}
}
