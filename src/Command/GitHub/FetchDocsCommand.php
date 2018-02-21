<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\GitHub;

use Joomla\Console\AbstractCommand;
use Joomla\Filesystem\Folder;
use Joomla\FrameworkWebsite\Model\Exception\PackageNotFoundException;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Github\Github;
use Joomla\Http\Exception\UnexpectedResponseException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to get package documentation from GitHub
 */
class FetchDocsCommand extends AbstractCommand
{
	/**
	 * The GitHub API adapter
	 *
	 * @var  Github
	 */
	private $github;

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
	 * @param   Github        $github        The GitHub API adapter.
	 */
	public function __construct(PackageModel $packageModel, Github $github)
	{
		$this->github       = $github;
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

		$symfonyStyle->title('Fetch Package Documentation');

		$packageName = $this->getApplication()->getConsoleInput()->getOption('package');

		if ($packageName)
		{
			try
			{
				$this->processPackage($this->packageModel->getPackage($packageName), $symfonyStyle);
			}
			catch (PackageNotFoundException $exception)
			{
				$symfonyStyle->error(sprintf('There is no `%s` package.', $packageName));

				return 1;
			}
		}
		else
		{
			foreach ($this->packageModel->getPackages() as $package)
			{
				$symfonyStyle->comment("Processing {$package->display} package");
				$this->processPackage($package, $symfonyStyle);
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
		$this->setName('github:fetch-docs');
		$this->setDescription('Fetches package documentation from GitHub');
		$this->addOption('package', 'p', InputOption::VALUE_OPTIONAL, 'Package to limit documentation lookup for');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command fetches the documentation for Framework packages from GitHub

<info>php %command.full_name% %command.name%</info>
EOF
		);
	}

	/**
	 * Processes a directory's contents from the repository.
	 *
	 * @param   string        $branch        The repository branch to process.
	 * @param   string        $version       The Framework package version.
	 * @param   string        $directory     The directory within the repository.
	 * @param   \stdClass     $package       The package record from the database.
	 * @param   SymfonyStyle  $symfonyStyle  The I/O object
	 *
	 * @return  void
	 */
	private function processDirectory(string $branch, string $version, string $directory, \stdClass $package, SymfonyStyle $symfonyStyle)
	{
		try
		{
			$docsDirContents = $this->github->repositories->contents->get('joomla-framework', $package->repo, $directory, $branch);
		}
		catch (UnexpectedResponseException $exception)
		{
			if ($exception->getCode() === 404)
			{
				$symfonyStyle->warning(
					sprintf('The `%1$s` package does not have documentation for the `%2$s` branch', $package->display, $branch)
				);
			}
			else
			{
				$symfonyStyle->error(
					sprintf(
						'Error fetching data for the `%1$s` package\'s `%2$s` path on the  `%3$s` branch: %4$s',
						$package->display,
						$path,
						$branch,
						$exception->getMessage()
					)
				);
			}

			return;
		}

		foreach ($docsDirContents as $file)
		{
			switch ($file->type)
			{
				case 'dir':
					$this->processDirectory($branch, $version, $file->path, $package, $symfonyStyle);

					break;

				case 'file':
					$this->processFile($branch, $version, $file->path, $package, $symfonyStyle);

					break;

				default:
					$symfonyStyle->warning(
						sprintf(
							'Unsupported file type `%1$s` while processing `%2$s` for the `%3$s` package `%4$s` branch.',
							$file->type,
							$file->path,
							$package->display,
							$branch
						)
					);

					continue;
			}
		}
	}

	/**
	 * Processes a file's contents from the repository.
	 *
	 * @param   string        $branch        The repository branch to process.
	 * @param   string        $version       The Framework package version.
	 * @param   string        $path          The path to the file within the repository.
	 * @param   \stdClass     $package       The package record from the database.
	 * @param   SymfonyStyle  $symfonyStyle  The I/O object
	 *
	 * @return  void
	 */
	private function processFile(string $branch, string $version, string $path, \stdClass $package, SymfonyStyle $symfonyStyle)
	{
		try
		{
			$file = $this->github->repositories->contents->get('joomla-framework', $package->repo, $path, $branch);
		}
		catch (UnexpectedResponseException $exception)
		{
			$symfonyStyle->error(
				sprintf(
					'Error fetching data for the `%1$s` package\'s `%2$s` path on the  `%3$s` branch: %4$s',
					$package->display,
					$path,
					$branch,
					$exception->getMessage()
				)
			);

			return;
		}

		switch ($file->encoding)
		{
			case 'base64':
				$fileContents = base64_decode($file->content);

				break;

			default:
				$symfonyStyle->warning(
					sprintf(
						'Unsupported file encoding `%1$s` while processing `%2$s` for the `%3$s` package `%4$s` branch.',
						$file->encoding,
						$file->path,
						$package->display,
						$branch
					)
				);

				return;
		}

		$docsPath = JPATH_ROOT . '/docs/' . $version . '/' . str_replace('docs/', $package->package . '/', $file->path);

		// Ensure folder exists
		Folder::create(dirname($docsPath));

		if (!file_put_contents($docsPath, $fileContents))
		{
			$symfonyStyle->error(sprintf('Could not write docs file to `%s`', $docsPath));

			return;
		}
	}

	/**
	 * Processes the documentation for a package.
	 *
	 * @param   \stdClass     $package       The package record from the database.
	 * @param   SymfonyStyle  $symfonyStyle  The I/O object
	 *
	 * @return  void
	 */
	private function processPackage(\stdClass $package, SymfonyStyle $symfonyStyle)
	{
		// Set docs branches
		switch ($package->package)
		{
			case 'console':
			case 'crypt':
			case 'preload':
			case 'renderer':
				$branches = ['master'];
				$versions = ['2.x'];

				break;

			default:
				$branches = ['2.0-dev'];
				$versions = ['2.x'];

				break;
		}

		foreach ($branches as $key => $branch)
		{
			$this->processDirectory($branch, $versions[$key], 'docs', $package, $symfonyStyle);
		}
	}
}
