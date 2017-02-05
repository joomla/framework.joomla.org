<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

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
	 * Utility method to retrieve a package's repository name
	 *
	 * @param   string  $package  Package name
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getPackageRepositoryName(string $package) : string
	{
		return $this->getPackages()->get("packages.$package.repo", $package);
	}
}
