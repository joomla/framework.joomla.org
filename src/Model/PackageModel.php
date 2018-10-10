<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Model;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Model\DatabaseModelInterface;
use Joomla\Model\DatabaseModelTrait;

/**
 * Model class for packages
 */
class PackageModel implements DatabaseModelInterface
{
	use DatabaseModelTrait;

	/**
	 * Instantiate the model.
	 *
	 * @param   DatabaseDriver  $db  The database adapter.
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->setDb($db);
	}

	/**
	 * Add a package
	 *
	 * @param   string   $packageName   The package name as registered with Packagist
	 * @param   string   $displayName   The package's display name
	 * @param   string   $repoName      The package's repo name
	 * @param   boolean  $isStable      Flag indicating the package is stable
	 * @param   boolean  $isDeprecated  Flag indicating the package is deprecated
	 * @param   boolean  $isAbandoned   Flag indicating the package is abandoned
	 *
	 * @return  void
	 */
	public function addPackage(string $packageName, string $displayName, string $repoName, bool $isStable, bool $isDeprecated, bool $isAbandoned): void
	{
		$db = $this->getDb();

		$data = (object) [
			'package'    => $packageName,
			'display'    => $displayName,
			'repo'       => $repoName,
			'stable'     => (int) $isStable,
			'deprecated' => (int) $isDeprecated,
			'abandoned'  => (int) $isAbandoned,
		];

		$db->insertObject('#__packages', $data);
	}

	/**
	 * Get the active package data
	 *
	 * @return  array
	 */
	public function getActivePackages(): array
	{
		$abandoned = false;

		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__packages'))
			->where($db->quoteName('abandoned') . ' = :abandoned')
			->bind('abandoned', $abandoned, ParameterType::INTEGER);

		return $db->setQuery($query)->loadObjectList('id');
	}

	/**
	 * Get a package's data
	 *
	 * @param   string  $packageName  The package to lookup
	 *
	 * @return  \stdClass
	 *
	 * @throws  \RuntimeException
	 */
	public function getPackage(string $packageName): \stdClass
	{
		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__packages'))
			->where($db->quoteName('package') . ' = :package');

		$query->bind('package', $packageName, ParameterType::STRING);

		$package = $db->setQuery($query)->loadObject();

		if (!$package)
		{
			throw new \RuntimeException(sprintf('Unable to find release data for the `%s` package', $package->display), 404);
		}

		return $package;
	}

	/**
	 * Get the known package names
	 *
	 * @return  array
	 */
	public function getPackageNames(): array
	{
		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select(['id', 'package'])
			->from($db->quoteName('#__packages'));

		return $db->setQuery($query)->loadAssocList('id', 'package');
	}

	/**
	 * Get the known package data
	 *
	 * @return  array
	 */
	public function getPackages(): array
	{
		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__packages'));

		return $db->setQuery($query)->loadObjectList('id');
	}

	/**
	 * Update a package
	 *
	 * @param   integer  $packageId     The local package ID
	 * @param   string   $packageName   The package name as registered with Packagist
	 * @param   string   $displayName   The package's display name
	 * @param   string   $repoName      The package's repo name
	 * @param   boolean  $isStable      Flag indicating the package is stable
	 * @param   boolean  $isDeprecated  Flag indicating the package is deprecated
	 * @param   boolean  $isAbandoned   Flag indicating the package is abandoned
	 *
	 * @return  void
	 */
	public function updatePackage(int $packageId, string $packageName, string $displayName, string $repoName, bool $isStable, bool $isDeprecated, bool $isAbandoned): void
	{
		$db = $this->getDb();

		$data = (object) [
			'id'         => $packageId,
			'package'    => $packageName,
			'display'    => $displayName,
			'repo'       => $repoName,
			'stable'     => (int) $isStable,
			'deprecated' => (int) $isDeprecated,
			'abandoned'  => (int) $isAbandoned,
		];

		$db->updateObject('#__packages', $data, 'id');
	}
}
