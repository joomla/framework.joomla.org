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
use Joomla\Database\ParameterType;
use Joomla\FrameworkWebsite\PackageAware;
use Joomla\Http\Http;

/**
 * Helper interacting with the Packagist API
 */
class PackagistHelper
{
	use PackageAware;

	/**
	 * The database driver
	 *
	 * @var  DatabaseInterface
	 */
	private $database;

	/**
	 * The HTTP driver
	 *
	 * @var  Http
	 */
	private $http;

	/**
	 * Instantiate the helper.
	 *
	 * @param   Http               $http      The HTTP driver.
	 * @param   DatabaseInterface  $database  The database driver.
	 */
	public function __construct(Http $http, DatabaseInterface $database)
	{
		$this->database = $database;
		$this->http     = $http;
	}

	/**
	 * Fetch the download counts for all packages.
	 *
	 * @return  array
	 */
	private function fetchDownloadCounts() : array
	{
		$counts = [];

		foreach (array_keys((array) $this->getPackages()->get('packages')) as $packageName)
		{
			$url = "https://packagist.org/packages/joomla/$packageName.json";

			try
			{
				$response = $this->http->get($url);
				$data     = json_decode($response->body);

				$counts[$packageName] = $data->package->downloads->total;
			}
			catch (\RuntimeException $exception)
			{
				$counts[$packageName] = false;
			}
		}

		return $counts;
	}

	/**
	 * Fetch the download counts from Packagist and store them to the database
	 *
	 * @return  void
	 *
	 * @throws  ExecutionFailureException
	 */
	public function syncDownloadCounts()
	{
		// Begin a transaction in case of error
		$this->database->transactionStart();

		try
		{
			foreach ($this->fetchDownloadCounts() as $package => $count)
			{
				/** @var MysqlQuery $query */
				$query = $this->database->getQuery(true)
					->update('#__packages')
					->set($this->database->quoteName('downloads') . ' = :downloads')
					->where($this->database->quoteName('package') . ' = :package');

				$query->bind('downloads', $count, ParameterType::INTEGER);
				$query->bind('package', $package, ParameterType::STRING);

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
