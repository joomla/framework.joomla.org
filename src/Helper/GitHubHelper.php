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
				$query->bind('profile', $contributor->url, \PDO::PARAM_STR);

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
