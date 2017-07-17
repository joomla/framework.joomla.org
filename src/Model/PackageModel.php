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
 * Model class for packages
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
	 * Get the known package names
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
	 * Get the known package data
	 *
	 * @return  array
	 */
	public function getPackages() : array
	{
		$db = $this->getDb();

		/** @var MysqlQuery $query */
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
