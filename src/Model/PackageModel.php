<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Model;

/**
 * Model class for the package view
 *
 * @since  1.0
 */
class PackageModel extends DefaultModel
{
	/**
	 * Fetches the requested data
	 *
	 * @return  array
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getItems()
	{
		$package = $this->getState()->get('package.name');

		// Get the package data for the package specified via the route
		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__packages'))
			->where($db->quoteName('package') . ' = ' . $db->quote($package));

		$packs = $db->setQuery($query)->loadObjectList();

		// Bail if we don't have any data for the given package
		if (!count($packs))
		{
			throw new \RuntimeException(sprintf('Unable to find package data for the specified package `%s`', $package), 404);
		}

		// Loop through the packs and get the reports
		$i = 0;

		foreach ($packs as $pack)
		{
			$query->clear()
				->select('*')
				->from($db->quoteName('#__test_results'))
				->where($db->quoteName('package_id') . ' = ' . (int) $pack->id);

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

			$result->version = $pack->version;

			// For repos with -api appended, handle separately
			if (in_array($pack->package, ['facebook', 'github', 'google', 'linkedin', 'twitter']))
			{
				$result->repoName = $pack->package . '-api';
			}
			else
			{
				$result->repoName = $pack->package;
			}

			// Compute the delta to the previous build
			if ($i !== 0)
			{
				$previous = $reports[$i - 1];

				// Number of new tests
				$result->newTests = $result->tests - $previous->tests;

				// Number of new assertions
				$result->newAssertions = $result->assertions - $previous->assertions;

				// Added line coverage
				$result->addedCoverage = $result->lines_percentage - $previous->lines_percentage;
			}

			$reports[$i] = $result;
			$i++;
		}

		return $reports;
	}
}
