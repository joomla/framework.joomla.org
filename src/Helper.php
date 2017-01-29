<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use PackageVersions\Versions;

/**
 * Utility helper class
 *
 * @since  1.0
 */
class Helper
{
	use PackageAware;

	/**
	 * Data container for the Composer data
	 *
	 * @var    array
	 * @since  1.0
	 */
	private static $packageList = [];

	/**
	 * Utility method to retrieve a package's display name
	 *
	 * @param   string  $package  Package name
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getPackageDisplayName(string $package) : string
	{
		return $this->getPackages()->get("packages.$package.display", ucfirst($package));
	}

	/**
	 * Parses the Composer installed.json file for package information
	 *
	 * @return  array  Composer package information
	 *
	 * @since   1.0
	 */
	public function parseComposer() : array
	{
		// Only process this once
		if (empty(self::$packageList))
		{
			// Loop through and extract the package name and version for all Joomla! Framework packages
			foreach ($this->getPackages()->get('packages') as $packageName => $packageData)
			{
				// Skip packages without a stable release
				if (is_object($packageData) && isset($packageData->stable) && $packageData->stable === false)
				{
					continue;
				}

				// We need to "normalize" the version string since the underlying API returns the version with the commit SHA
				list($version) = explode('@', Versions::getVersion("joomla/$packageName"));

				self::$packageList[$packageName] = ['version' => $version];
			}
		}

		return self::$packageList;
	}
}
