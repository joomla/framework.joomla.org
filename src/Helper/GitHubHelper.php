<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Helper;

use Joomla\Application\AbstractApplication;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Github\Github;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Helper interacting with the GitHub API
 */
class GitHubHelper
{
    /**
     * Accounts to exclude from the contributor listing
     *
     * @const  string[]
     */
    private const IGNORE_ACCOUNTS = [
        'joomla-jenkins',
    ];
/**
     * Application object
     *
     * @var  AbstractApplication
     */
    private $application;
/**
     * Cache pool
     *
     * @var  CacheItemPoolInterface
     */
    private $cache;
/**
     * Array tracking commit counts for each contributor
     *
     * @var  array
     */
    private $commitCounts = [];
/**
     * The database driver
     *
     * @var  DatabaseInterface
     */
    private $database;
/**
     * The GitHub API adapter
     *
     * @var  Github
     */
    private $github;
/**
     * Instantiate the helper.
     *
     * @param   Github                  $github       The GitHub API adapter.
     * @param   DatabaseInterface       $database     The database driver.
     * @param   CacheItemPoolInterface  $cache        Cache pool.
     * @param   AbstractApplication     $application  The application object.
     */
    public function __construct(Github $github, DatabaseInterface $database, CacheItemPoolInterface $cache, AbstractApplication $application)
    {
        $this->application = $application;
        $this->cache       = $cache;
        $this->database    = $database;
        $this->github      = $github;
    }

    /**
     * Generate the cache key for a documentation file
     *
     * @param   string     $version  The Framework version to fetch documentation for.
     * @param   \stdClass  $package  The Framework package the documentation belongs to.
     * @param   string     $path     The path to the documentation file.
     *
     * @return  string
     */
    public function generateDocsFileCacheKey(string $version, \stdClass $package, string $path): string
    {
        return str_replace('/', '.', $version . '/' . $package->package . '/' . $path);
    }

    /**
     * Get the contributor commit count
     *
     * @return  array
     */
    public function getCommitCounts(): array
    {
        return $this->commitCounts;
    }

    /**
     * Render a documentation file
     *
     * @param   string     $version  The Framework version to fetch documentation for.
     * @param   \stdClass  $package  The Framework package the documentation belongs to.
     * @param   string     $path     The path to the documentation file.
     *
     * @return  string
     */
    public function renderDocsFile(string $version, \stdClass $package, string $path): string
    {
        $docsPath = JPATH_ROOT . '/docs/' . $version . '/' . $package->package . '/' . $path . '.md';
        if (!file_exists($docsPath)) {
            throw new \InvalidArgumentException(sprintf('No documentation found for `%s` in the `%2$s` package for version `%3$s`.', $path, $package->display, $version), 404);
        }

        $key = $this->generateDocsFileCacheKey($version, $package, $path);
        $item = $this->cache->getItem($key);
// Make sure we got a hit on the item, otherwise we'll have to re-cache
        if ($item->isHit()) {
            $rendered = $item->get();
        } else {
            $rendered = $this->github->markdown->render(file_get_contents($docsPath), 'gfm', 'joomla-framework/' . $package->repo);
            $routePrefix = $this->application->get('uri.base.path') . 'docs/' . $version . '/' . $package->package . '/';
        // Fix links - TODO: This should only change relative links for the docs files
            $rendered = preg_replace('/href=\"(.*)\.md\"/', 'href="' . $routePrefix . '$1"', $rendered);
        // Cache the result for 7 days
            $sevenDaysInSeconds = 60 * 60 * 24 * 7;
            $item->set($rendered);
            $item->expiresAfter($sevenDaysInSeconds);
            $this->cache->save($item);
        }

        return $rendered;
    }

    /**
     * Sync the contributors for a package
     *
     * @param   string  $package  The package to synchronize
     *
     * @return  void
     *
     * @throws  ExecutionFailureException
     */
    public function syncPackageContributors(string $package): void
    {
        $contributors = $this->github->repositories->getListContributors('joomla-framework', $package);
// Begin a transaction in case of error
        $this->database->transactionStart();
        try {
            foreach ($contributors as $contributor) {
                if (\in_array($contributor->login, self::IGNORE_ACCOUNTS)) {
                    continue;
                }

                /** @var DatabaseQuery $query */
                $query = $this->database->getQuery(true);
                $query->setQuery('INSERT INTO `#__contributors` (github_id, username, avatar, profile) VALUES (:github, :username, :avatar, :profile) ON DUPLICATE KEY UPDATE username = :username, avatar = :avatar, profile = :profile');
                $query->bind('github', $contributor->id, ParameterType::INTEGER);
                $query->bind('username', $contributor->login, ParameterType::STRING);
                $query->bind('avatar', $contributor->avatar_url, ParameterType::STRING);
                $query->bind('profile', $contributor->html_url, ParameterType::STRING);
                $this->database->setQuery($query)->execute();
                if (isset($this->commitCounts[$contributor->login])) {
                    $this->commitCounts[$contributor->login] += $contributor->contributions;
                } else {
                    $this->commitCounts[$contributor->login] = $contributor->contributions;
                }
            }

            $this->database->transactionCommit();
        } catch (ExecutionFailureException $exception) {
            $this->database->transactionRollback();
            throw $exception;
        }
    }

    /**
     * Sync the contributor user data
     *
     * @return  void
     *
     * @throws  ExecutionFailureException
     */
    public function syncUserData(): void
    {
        $query = $this->database->getQuery(true);
        $query->select($this->database->quoteName(['username']))
            ->from($this->database->quoteName('#__contributors'));
        $usernames = $this->database->setQuery($query)->loadColumn();
        $this->database->transactionStart();
        try {
            foreach ($usernames as $username) {
                $userData = $this->github->users->get($username);
                $query = $this->database->getQuery(true);
                $query->update($this->database->quoteName('#__contributors'))
                    ->set($this->database->quoteName('name') . ' = :name')
                    ->where($this->database->quoteName('username') . ' = :username');
                $name = $userData->name ?: '';
                $query->bind('name', $name, ParameterType::STRING);
                $query->bind('username', $username, ParameterType::STRING);
                $this->database->setQuery($query)->execute();
            }

            $this->database->transactionCommit();
        } catch (ExecutionFailureException $exception) {
            $this->database->transactionRollback();
            throw $exception;
        }
    }

    /**
     * Update the stored commit counts for contributors
     *
     * @return  void
     *
     * @throws  ExecutionFailureException
     */
    public function updateCommitCounts(): void
    {
        $this->database->transactionStart();
        try {
            foreach ($this->getCommitCounts() as $username => $count) {
                $query = $this->database->getQuery(true);
                $query->update($this->database->quoteName('#__contributors'))
                    ->set($this->database->quoteName('commits') . ' = :commits')
                    ->where($this->database->quoteName('username') . ' = :username');
                $query->bind('username', $username, ParameterType::STRING);
                $query->bind('commits', $count, ParameterType::INTEGER);
                $this->database->setQuery($query)->execute();
            }

            $this->database->transactionCommit();
        } catch (ExecutionFailureException $exception) {
            $this->database->transactionRollback();
            throw $exception;
        }
    }
}
