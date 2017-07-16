<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Model;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\Mysql\MysqlQuery;
use Joomla\FrameworkWebsite\Helper;
use Joomla\Model\{
	DatabaseModelInterface, DatabaseModelTrait
};

/**
 * Model class for the package view
 */
class PackageModel implements DatabaseModelInterface
{
	use DatabaseModelTrait;

	/**
	 * Helper object
	 *
	 * @var  Helper
	 */
	private $helper;

	/**
	 * Instantiate the model.
	 *
	 * @param   Helper          $helper  Helper object.
	 * @param   DatabaseDriver  $db      The database adapter.
	 */
	public function __construct(Helper $helper, DatabaseDriver $db)
	{
		$this->setDb($db);

		$this->helper = $helper;
	}

	/**
	 * Add a package
	 *
	 * @param   string   $packageName   The package name as registered with Packagist
	 * @param   string   $displayName   The package's display name
	 * @param   string   $repoName      The package's repo name
	 * @param   boolean  $isStable      Flag indicating the package is stable
	 * @param   boolean  $isDeprecated  Flag indicating the package is deprecated
	 *
	 * @return  void
	 */
	public function addPackage(string $packageName, string $displayName, string $repoName, bool $isStable, bool $isDeprecated)
	{
		$db = $this->getDb();

		$data = (object) [
			'package'    => $packageName,
			'display'    => $displayName,
			'repo'       => $repoName,
			'stable'     => (int) $isStable,
			'deprecated' => (int) $isDeprecated,
		];

		$db->insertObject('#__packages', $data);
	}

	/**
	 * Add a release for a package
	 *
	 * @param   string  $package  The package to add the release for
	 * @param   string  $version  The package's release version
	 *
	 * @return  void
	 */
	public function addRelease(string $package, string $version)
	{
		$db = $this->getDb();

		$data = (object) [
			'package' => $package,
			'version' => $version,
		];

		$db->insertObject('#__packages', $data);
	}

	/**
	 * Fetches the latest releases for each Framework package
	 *
	 * @return  \stdClass[]
	 */
	public function getLatestReleases() : array
	{
		$reports = [];

		// Get the package data for each of our packages
		$db = $this->getDb();

		/** @var MysqlQuery $subQuery */
		$subQuery = $db->getQuery(true)
			->select($db->quoteName(['id', 'package', 'version']))
			->from($db->quoteName('#__packages'))
			->order('package ASC, version DESC');

		/** @var MysqlQuery $query */
		$query = $db->getQuery(true)
			->select('*')
			->from('(' . (string) $subQuery . ') AS sub');

		$packs = $db->setQuery($query)->loadObjectList();

		$inString = '';

		/** @var MysqlQuery $query */
		$query = $db->getQuery(true);

		$addedPacks = [];

		foreach ($packs as $key => $pack)
		{
			if (!in_array($pack->package, $addedPacks))
			{
				$addedPacks[] = $pack->package;
				$queryKey = "package$key";
				$inString .= ":$queryKey,";
				$query->bind($queryKey, $pack->id, \PDO::PARAM_INT);
			}
		}

		$query->select('*')
			->from($db->quoteName('#__test_results'))
			->where($db->quoteName('package_id') . ' IN (' . rtrim($inString, ',') . ')');

		$packageTests = $db->setQuery($query)->loadObjectList('package_id');

		$reports = [];

		// Loop through the packs and build the reports
		foreach ($packs as $pack)
		{
			// Skip if package is already included
			if (isset($reports[$pack->package]))
			{
				continue;
			}

			// If we didn't get any data, build a new object
			if (!isset($packageTests[$pack->id]))
			{
				$result = new \stdClass;
			}
			// Otherwise compute report percentages
			else
			{
				$result = $packageTests[$pack->id];

				if ($result->total_lines > 0)
				{
					$result->lines_percentage = round($result->lines_covered / $result->total_lines * 100, 2);
				}
				else
				{
					$result->lines_percentage = 0;
				}
			}

			$result->displayName = $this->helper->getPackageDisplayName($pack->package);
			$result->version     = $pack->version;
			$result->repoName    = $this->helper->getPackageRepositoryName($pack->package);
			$result->deprecated  = $this->helper->getPackageDeprecated($pack->package);

			$reports[$pack->package] = $result;
		}

		return $reports;
	}

	/**
	 * Get the release history for a package
	 *
	 * @param   string  $package  The package to retrieve the history for
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \RuntimeException
	 */
	public function getPackageHistory(string $package) : array
	{
		// Get the package data for the package specified via the route
		$db = $this->getDb();

		/** @var MysqlQuery $query */
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__packages'))
			->where($db->quoteName('package') . ' = :package')
			->order('version ASC');

		$query->bind('package', $package, \PDO::PARAM_STR);

		$packs = $db->setQuery($query)->loadObjectList();

		// Bail if we don't have any data for the given package
		if (!count($packs))
		{
			throw new \RuntimeException(sprintf('Unable to find package data for the specified package `%s`', $package), 404);
		}

		// Loop through the packs and get the reports
		$i = 0;

		$inString = '';

		/** @var MysqlQuery $query */
		$query = $db->getQuery(true);

		foreach ($packs as $key => $pack)
		{
			$queryKey = "package$key";
			$inString .= ":$queryKey,";
			$query->bind($queryKey, $pack->id, \PDO::PARAM_INT);
		}

		$query->select('*')
			->from($db->quoteName('#__test_results'))
			->where($db->quoteName('package_id') . ' IN (' . rtrim($inString, ',') . ')');

		$releaseTests = $db->setQuery($query)->loadObjectList('package_id');

		$reports = [];

		foreach ($packs as $pack)
		{
			// If we didn't get any data, build a new object
			if (!isset($releaseTests[$pack->id]))
			{
				$result = new \stdClass;
			}
			// Otherwise compute report percentages
			else
			{
				$result = $releaseTests[$pack->id];

				$result->lines_percentage = 0;

				if ($result->total_lines > 0)
				{
					$result->lines_percentage = $result->lines_covered / $result->total_lines * 100;
				}
			}

			$result->version = $pack->version;

			// Compute the delta to the previous build
			if ($i !== 0)
			{
				$previous = $reports[$i - 1];

				$result->newTests      = 0;
				$result->newAssertions = 0;
				$result->addedCoverage = 0;

				if (isset($result->tests) && isset($previous->tests))
				{
					$result->newTests = $result->tests - $previous->tests;
				}

				if (isset($result->assertions) && isset($previous->assertions))
				{
					$result->newAssertions = $result->assertions - $previous->assertions;
				}

				if (isset($result->lines_percentage) && isset($previous->lines_percentage))
				{
					$result->addedCoverage = $result->lines_percentage - $previous->lines_percentage;
				}
			}

			$reports[$i] = $result;
			$i++;
		}

		return $reports;
	}

	/**
	 * Get the package data
	 *
	 * @return  array
	 */
	public function getPackageNames() : array
	{
		$db = $this->getDb();

		/** @var MysqlQuery $query */
		$query = $db->getQuery(true)
			->select(['id', 'package'])
			->from($db->quoteName('#__packages'));

		return $db->setQuery($query)->loadAssocList('id', 'package');
	}

	/**
	 * Check if the package has a release at the given version
	 *
	 * @param   string  $package  The package to check for the release on
	 * @param   string  $version  The version to check for
	 *
	 * @return  boolean
	 */
	public function hasRelease(string $package, string $version) : bool
	{
		$db = $this->getDb();

		/** @var MysqlQuery $query */
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__packages'))
			->where($db->quoteName('package') . ' = :package')
			->where($db->quoteName('version') . ' = :version')
			->bind('package', $package, \PDO::PARAM_STR)
			->bind('version', $version, \PDO::PARAM_STR);

		$id = $db->setQuery($query)->loadResult();

		return $id !== null;
	}

	/**
	 * Add a package
	 *
	 * @param   integer  $packageId     The local package ID
	 * @param   string   $packageName   The package name as registered with Packagist
	 * @param   string   $displayName   The package's display name
	 * @param   string   $repoName      The package's repo name
	 * @param   boolean  $isStable      Flag indicating the package is stable
	 * @param   boolean  $isDeprecated  Flag indicating the package is deprecated
	 *
	 * @return  void
	 */
	public function updatePackage(int $packageId, string $packageName, string $displayName, string $repoName, bool $isStable, bool $isDeprecated)
	{
		$db = $this->getDb();

		$data = (object) [
			'id'         => $packageId,
			'package'    => $packageName,
			'display'    => $displayName,
			'repo'       => $repoName,
			'stable'     => (int) $isStable,
			'deprecated' => (int) $isDeprecated,
		];

		$db->updateObject('#__packages', $data, 'id');
	}
}
