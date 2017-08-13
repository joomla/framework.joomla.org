<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Helper;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Mysql\MysqlQuery;
use Joomla\Github\Github;

/**
 * Helper interacting with the GitHub API
 */
class GitHubHelper
{
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
	 * Accounts to exclude from the contributor listing
	 *
	 * @var  string[]
	 */
	private $ignoreAccounts = [
		'joomla-jenkins',
	];

	/**
	 * Instantiate the helper.
	 *
	 * @param   Github             $github    The GitHub API adapter.
	 * @param   DatabaseInterface  $database  The database driver.
	 */
	public function __construct(Github $github, DatabaseInterface $database)
	{
		$this->database = $database;
		$this->github   = $github;
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
	 * Sync the contributors for a package
	 *
	 * @param   string  $package  The package to synchronize
	 *
	 * @return  void
	 *
	 * @throws  ExecutionFailureException
	 */
	public function syncPackageContributors(string $package)
	{
		$contributors = $this->github->repositories->getListContributors('joomla-framework', $package);

		// Begin a transaction in case of error
		$this->database->transactionStart();

		try
		{
			foreach ($contributors as $contributor)
			{
				if (in_array($contributor->login, $this->ignoreAccounts))
				{
					continue;
				}

				/** @var MysqlQuery $query */
				$query = $this->database->getQuery(true);
				$query->setQuery("INSERT INTO `#__contributors` (github_id, username, avatar, profile) VALUES (:github, :username, :avatar, :profile) ON DUPLICATE KEY UPDATE username = :username, avatar = :avatar, profile = :profile");

				$query->bind('github', $contributor->id, \PDO::PARAM_INT);
				$query->bind('username', $contributor->login, \PDO::PARAM_STR);
				$query->bind('avatar', $contributor->avatar_url, \PDO::PARAM_STR);
				$query->bind('profile', $contributor->html_url, \PDO::PARAM_STR);

				$this->database->setQuery($query)->execute();

				if (isset($this->commitCounts[$contributor->login]))
				{
					$this->commitCounts[$contributor->login] += $contributor->contributions;
				}
				else
				{
					$this->commitCounts[$contributor->login] = $contributor->contributions;
				}
			}

			$this->database->transactionCommit();
		}
		catch (ExecutionFailureException $exception)
		{
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
	public function syncUserData()
	{
		/** @var MysqlQuery $query */
		$query = $this->database->getQuery(true);
		$query->select($this->database->quoteName(['username']))
			->from($this->database->quoteName('#__contributors'));

		$usernames = $this->database->setQuery($query)->loadColumn();

		$this->database->transactionStart();

		try
		{
			foreach ($usernames as $username)
			{
				$userData = $this->github->users->get($username);

				/** @var MysqlQuery $query */
				$query = $this->database->getQuery(true);
				$query->update($this->database->quoteName('#__contributors'))
					->set($this->database->quoteName('name') . ' = :name')
					->where($this->database->quoteName('username') . ' = :username');

				$name = $userData->name ?: '';

				$query->bind('name', $name, \PDO::PARAM_STR);
				$query->bind('username', $username, \PDO::PARAM_STR);

				$this->database->setQuery($query)->execute();
			}

			$this->database->transactionCommit();
		}
		catch (ExecutionFailureException $exception)
		{
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
	public function updateCommitCounts()
	{
		$this->database->transactionStart();

		try
		{
			foreach ($this->getCommitCounts() as $username => $count)
			{
				/** @var MysqlQuery $query */
				$query = $this->database->getQuery(true);
				$query->update($this->database->quoteName('#__contributors'))
					->set($this->database->quoteName('commits') . ' = :commits')
					->where($this->database->quoteName('username') . ' = :username');

				$query->bind('username', $username, \PDO::PARAM_STR);
				$query->bind('commits', $count, \PDO::PARAM_INT);

				$this->database->setQuery($query)->execute();
			}

			$this->database->transactionCommit();
		}
		catch (ExecutionFailureException $exception)
		{
			$this->database->transactionRollback();

			throw $exception;
		}
	}
}
