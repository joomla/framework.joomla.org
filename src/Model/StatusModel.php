<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Model;

use Joomla\Database\DatabaseDriver;
use Joomla\Model\StatefulModelInterface;
use Joomla\Model\StatefulModelTrait;
use Joomla\Registry\Registry;
use Joomla\Status\Helper;

/**
 * Model class for the status dashboard
 *
 * @since  1.0
 */
class StatusModel extends DefaultModel implements StatefulModelInterface
{
	use PackageAware, StatefulModelTrait;

	/**
	 * Helper object
	 *
	 * @var    Helper
	 * @since  1.0
	 */
	private $helper;

	/**
	 * Instantiate the model.
	 *
	 * @param   Helper          $helper  Helper object.
	 * @param   DatabaseDriver  $db      The database adapter.
	 * @param   Registry        $state   The model state.
	 *
	 * @since   1.0
	 */
	public function __construct(Helper $helper, DatabaseDriver $db, Registry $state = null)
	{
		parent::__construct($db);

		$this->setDb($db);

		$this->helper = $helper;
	}

	/**
	 * Fetches the requested data
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getItems() : array
	{
		// Parse installed.json to get the currently installed packages, should always be the latest version
		// TODO - Replace this with a package listing gathered from the Packagist API to decouple from needing all packages installed
		$packages = $this->helper->parseComposer();
		$reports  = array();

		// Get the package data for each of our packages
		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__packages'));

		foreach ($packages as $name => $package)
		{
			$query->where(
				$db->quoteName('package') . ' = ' . $db->quote($name) . ' AND ' . $db->quoteName('version') . ' = ' . $db->quote($package['version']),
				'OR'
			);
		}

		$packs = $db->setQuery($query)->loadObjectList();

		// Loop through the packs and get the reports
		foreach ($packs as $pack)
		{
			$query->clear()
				->select('*')
				->from($db->quoteName('#__test_results'))
				->where($db->quoteName('package_id') . ' = ' . (int) $pack->id)
				->order('id DESC')
				->setLimit(1);

			$result = $db->setQuery($query)->loadObject();

			// If we didn't get any data, build a new object
			if (!$result)
			{
				$result = new \stdClass;
			}
			// Otherwise compute report percentages
			else
			{
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
			$result->repoName    = $this->getPackages()->get('packages.' . $pack->package . '.repo', $pack->package);

			$reports[$pack->package] = $result;
		}

		// Sort the array
		ksort($reports);

		return $reports;
	}
}
