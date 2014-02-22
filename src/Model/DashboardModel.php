<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Model;

use Joomla\Status\Helper;

/**
 * Model class for the application dashboard
 *
 * @since  1.0
 */
class DashboardModel extends DefaultModel
{
	/**
	 * Fetches the requested data
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getItems()
	{
		// Parse installed.json to get the currently installed packages, should always be the latest version
		$packages  = Helper::parseComposer();
		$reports   = array();

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

			$result->displayName = Helper::getPackageDisplayName($pack->package);
			$result->version     = $pack->version;

			// For repos with -api appended, handle separately
			if (in_array($pack->package, ['facebook', 'github', 'google', 'linkedin', 'twitter']))
			{
				$result->repoName = $pack->package . '-api';
			}
			else
			{
				$result->repoName = $pack->package;
			}

			$reports[$pack->package] = $result;
		}

		// Sort the array
		ksort($reports);

		return $reports;
	}
}
