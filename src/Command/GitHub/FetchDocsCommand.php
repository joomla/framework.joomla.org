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
         * Flag tracking whether any part of the update failed
         *
         * @var  boolean
         */
    private $hasErrors = false;
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
        if ($packageName) {
            try {
                $this->processPackage($this->packageModel->getPackage($packageName), $symfonyStyle);
            } catch (PackageNotFoundException $exception) {
                $symfonyStyle->error(sprintf('There is no `%s` package.', $packageName));
                return 1;
            }
        } else {
            foreach ($this->packageModel->getPackages() as $package) {
                $symfonyStyle->comment("Processing {$package->display} package");
                $this->processPackage($package, $symfonyStyle);
            }
        }

        $symfonyStyle->comment('Cleaning cache');

        // Rendered files are validated against the source file when read, so a failure here only leaves orphaned items behind
        if ($this->fileCacheKeys && !$this->cache->deleteItems(array_unique($this->fileCacheKeys))) {
            $symfonyStyle->warning('Could not remove all outdated items from the cache pool, check the permissions of the cache directory.');
        }

        if ($this->hasErrors) {
            $symfonyStyle->error('Update completed with errors, the documentation may be incomplete or outdated.');
            return 1;
        }

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
        $this->setHelp(
            <<<'EOF'
The <info>%command.name%</info> command fetches the documentation for Framework packages from GitHub

<info>php %command.full_name% %command.name%</info>
EOF
        );
    }

    /**
     * Processes the documentation of a repository branch.
     *
     * @param   string        $branch        The repository branch to process.
     * @param   string        $version       The Framework package version.
     * @param   \stdClass     $package       The package record from the database.
     * @param   SymfonyStyle  $symfonyStyle  The I/O object
     *
     * @return  void
     */
    private function processBranch(string $branch, string $version, \stdClass $package, SymfonyStyle $symfonyStyle): void
    {
        // Fetch the full file listing of the branch in a single request
        try {
            $tree = $this->github->data->trees->getRecursively('joomla-framework', $package->repo, $branch);
        } catch (UnexpectedResponseException $exception) {
            if ($exception->getCode() === 404) {
                $symfonyStyle->warning(sprintf('The `%1$s` package does not have a `%2$s` branch', $package->display, $branch));
            } else {
                $this->hasErrors = true;
                $symfonyStyle->error(sprintf('Error fetching data for the `%1$s` package\'s `%2$s` branch: %3$s', $package->display, $branch, $exception->getMessage()));
            }

            return;
        }

        if (!isset($tree->tree) || !empty($tree->truncated)) {
            $this->hasErrors = true;
            $symfonyStyle->error(sprintf('Incomplete file listing for the `%1$s` package\'s `%2$s` branch.', $package->display, $branch));

            return;
        }

        $files = [];

        foreach ($tree->tree as $entry) {
            if (strncmp($entry->path, 'docs/', 5) !== 0 || $entry->type === 'tree') {
                continue;
            }

            // Only regular files are supported, no symlinks or submodules
            if ($entry->type !== 'blob' || $entry->mode === '120000') {
                $symfonyStyle->warning(sprintf('Unsupported file type `%1$s` while processing `%2$s` for the `%3$s` package `%4$s` branch.', $entry->type, $entry->path, $package->display, $branch));

                continue;
            }

            $path         = substr($entry->path, 5);
            $files[$path] = true;
            $this->processFile($branch, $version, $path, $entry->sha, $package, $symfonyStyle);
        }

        if (!$files) {
            $symfonyStyle->warning(sprintf('The `%1$s` package does not have documentation for the `%2$s` branch', $package->display, $branch));
        }

        $this->removeObsoleteFiles($version, $files, $package, $symfonyStyle);
    }

    /**
     * Processes a file's contents from the repository.
     *
     * @param   string        $branch        The repository branch to process.
     * @param   string        $version       The Framework package version.
     * @param   string        $path          The path to the file within the repository's docs directory.
     * @param   string        $sha           The blob SHA of the file in the repository.
     * @param   \stdClass     $package       The package record from the database.
     * @param   SymfonyStyle  $symfonyStyle  The I/O object
     *
     * @return  void
     */
    private function processFile(string $branch, string $version, string $path, string $sha, \stdClass $package, SymfonyStyle $symfonyStyle): void
    {
        $docsPath = JPATH_ROOT . '/docs/' . $version . '/' . $package->package . '/' . $path;

        // Skip the download if the local copy is identical to the file in the repository
        if (is_file($docsPath)) {
            $localContents = file_get_contents($docsPath);

            if ($localContents !== false && sha1('blob ' . \strlen($localContents) . "\0" . $localContents) === $sha) {
                return;
            }
        }

        try {
            $file = $this->github->data->blobs->get('joomla-framework', $package->repo, $sha);
        } catch (UnexpectedResponseException $exception) {
            $this->hasErrors = true;
            $symfonyStyle->error(sprintf('Error fetching data for the `%1$s` package\'s `%2$s` path on the  `%3$s` branch: %4$s', $package->display, $path, $branch, $exception->getMessage()));
            return;
        }

        switch ($file->encoding) {
            case 'base64':
                $fileContents = base64_decode($file->content);

                break;
            case 'utf-8':
                $fileContents = $file->content;

                break;
            default:
                $symfonyStyle->warning(sprintf('Unsupported file encoding `%1$s` while processing `%2$s` for the `%3$s` package `%4$s` branch.', $file->encoding, $path, $package->display, $branch));

                return;
        }

        // Ensure folder exists
        Folder::create(\dirname($docsPath));
        if (file_put_contents($docsPath, $fileContents) === false) {
            $this->hasErrors = true;
            $symfonyStyle->error(sprintf('Could not write docs file to `%s`', $docsPath));
            return;
        }

        $this->addFileCacheKey($version, $package, $path);
    }

    /**
     * Removes local documentation files which no longer exist in the repository.
     *
     * @param   string        $version       The Framework package version.
     * @param   array         $files         The files in the repository, relative to its docs directory, as array keys.
     * @param   \stdClass     $package       The package record from the database.
     * @param   SymfonyStyle  $symfonyStyle  The I/O object
     *
     * @return  void
     */
    private function removeObsoleteFiles(string $version, array $files, \stdClass $package, SymfonyStyle $symfonyStyle): void
    {
        $basePath = JPATH_ROOT . '/docs/' . $version . '/' . $package->package;

        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $item */
        foreach ($iterator as $item) {
            // Clean up directories which are empty now, this fails silently for the ones still in use
            if ($item->isDir()) {
                @rmdir($item->getPathname());

                continue;
            }

            $path = str_replace('\\', '/', substr($item->getPathname(), \strlen($basePath) + 1));

            if (isset($files[$path])) {
                continue;
            }

            if (!@unlink($item->getPathname())) {
                $this->hasErrors = true;
                $symfonyStyle->error(sprintf('Could not remove obsolete docs file `%s`', $item->getPathname()));

                continue;
            }

            $this->addFileCacheKey($version, $package, $path);
        }
    }

    /**
     * Registers the cache key of a rendered documentation file for removal.
     *
     * @param   string     $version  The Framework package version.
     * @param   \stdClass  $package  The package record from the database.
     * @param   string     $path     The path to the file within the package's docs directory.
     *
     * @return  void
     */
    private function addFileCacheKey(string $version, \stdClass $package, string $path): void
    {
        // Only Markdown files are rendered
        if (substr($path, -3) === '.md') {
            $this->fileCacheKeys[] = $this->githubHelper->generateDocsFileCacheKey($version, $package, substr($path, 0, -3));
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
    private function processPackage(\stdClass $package, SymfonyStyle $symfonyStyle): void
    {
        // Set docs branches
        $branches = ['3.x-dev', '4.x-dev'];
        $versions = ['3.x', '4.x'];

        foreach ($branches as $key => $branch) {
            $this->processBranch($branch, $versions[$key], $package, $symfonyStyle);
        }
    }
}
