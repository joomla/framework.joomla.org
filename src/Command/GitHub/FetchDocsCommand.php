<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\GitHub;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Filesystem\Folder;
use Joomla\FrameworkWebsite\Helper\GitHubHelper;
use Joomla\FrameworkWebsite\Model\Exception\PackageNotFoundException;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Github\Github;
use Joomla\Http\Exception\UnexpectedResponseException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to get package documentation from GitHub
 */
class FetchDocsCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'github:fetch-docs';

	/**
	 * Cache pool
	 *
	 * @var  CacheItemPoolInterface
	 */
	private $cache;

	/**
	 * Cache keys for rendered files which should be removed
	 *
	 * @var  string[]
	 */
	private $fileCacheKeys = [];

	/**
	 * The GitHub API adapter
	 *
	 * @var  Github
	 */
	private $github;

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
	 * @param   PackageModel            $packageModel  The package model.
	 * @param   Github                  $github        The GitHub API adapter.
	 * @param   GitHubHelper            $githubHelper  The GitHub helper.
	 * @param   CacheItemPoolInterface  $cache         Cache pool.
	 */
	public function __construct(PackageModel $packageModel, Github $github, GitHubHelper $githubHelper, CacheItemPoolInterface $cache)
	{
		$this->cache        = $cache;
		$this->github       = $github;
		$this->githubHelper = $githubHelper;
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

		$symfonyStyle->comment('Cleaning cache');

		$this->cache->deleteItems($this->fileCacheKeys);

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
	private function processDirectory(string $branch, string $version, string $directory, \stdClass $package, SymfonyStyle $symfonyStyle): void
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
						'Error fetching data for the `%1$s` package\'s `%2$s` branch: %3$s',
						$package->display,
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

					break;
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
	private function processFile(string $branch, string $version, string $path, \stdClass $package, SymfonyStyle $symfonyStyle): void
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
		Folder::create(\dirname($docsPath));

		if (!file_put_contents($docsPath, $fileContents))
		{
			$symfonyStyle->error(sprintf('Could not write docs file to `%s`', $docsPath));

			return;
		}

		$this->fileCacheKeys[] = $this->githubHelper->generateDocsFileCacheKey(
			$version,
			$package,
			substr(str_replace('docs/', '', $file->path), 0, -3)
		);
	}

	/**
	 * Processes the documentation for a package.
	 *
	 * @param   \stdClass     $package       The package record from the database.
	 * @param   SymfonyStyle  $symfonyStyle  The I/O object
	 *
	 * @return  void
	 */
	private function processPackage(\stdClass $package, SymfonyStyle $symfonyStyle): void
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
