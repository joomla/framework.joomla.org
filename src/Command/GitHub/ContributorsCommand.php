<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\GitHub;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\CommandInterface;
use Joomla\FrameworkWebsite\Helper\GitHubHelper;
use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Input\Input;

/**
 * Command to get contributor information from GitHub
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 */
class ContributorsCommand extends AbstractController implements CommandInterface
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
	 * Instantiate the controller.
	 *
	 * @param   PackageModel         $packageModel  The package model.
	 * @param   GitHubHelper         $githubHelper  The GitHub helper.
	 * @param   Input                $input         The input object.
	 * @param   AbstractApplication  $app           The application object.
	 */
	public function __construct(PackageModel $packageModel, GitHubHelper $githubHelper, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->githubHelper = $githubHelper;
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

		foreach ($this->packageModel->getPackages() as $package)
		{
			$this->getApplication()->out("<info>Processing {$package->display} package</info>");
			$this->githubHelper->syncPackageContributors($package->repo);
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
		return 'Fetches contributor information from GitHub.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle() : string
	{
		return 'Get GitHub Contributors';
	}
}
