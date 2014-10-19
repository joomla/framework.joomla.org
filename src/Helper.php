<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status;

/**
 * Utility helper class
 *
 * @since  1.0
 */
class Helper
{
	/**
	 * Data container for the Composer data
	 *
	 * @var    array
	 * @since  1.0
	 */
	private static $packages = array();

	/**
	 * Utility method to retrieve a package's display name
	 *
	 * @param   string  $package  Package name
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getPackageDisplayName($package)
	{
		switch ($package)
		{
			case 'datetime':
				return 'DateTime';

			case 'di':
				return 'DI';

			case 'github':
				return 'GitHub';

			case 'http':
				return 'HTTP';

			case 'ldap':
				return 'LDAP';

			case 'linkedin':
				return 'LinkedIn';

			case 'oauth1':
				return 'OAuth1';

			case 'oauth2':
				return 'OAuth2';

			case 'openstreetmap':
				return 'OpenStreetMap';

			case 'uri':
				return 'URI';

			default:
				return ucfirst($package);
		}
	}

	/**
	 * Parses the Composer installed.json file for package information
	 *
	 * @return  array  Composer package information
	 *
	 * @since   1.0
	 */
	public function parseComposer()
	{
		// Only process this once
		if (empty(self::$packages))
		{
			// Read the installed.json file
			$installed = json_decode(file_get_contents(JPATH_ROOT . '/vendor/composer/installed.json'));

			// Loop through and extract the package name and version for all Joomla! Framework packages
			foreach ($installed as $package)
			{
				if (strpos($package->name, 'joomla') !== 0)
				{
					continue;
				}

				self::$packages[str_replace('joomla/', '', $package->name)] = ['version' => $package->version];
			}
		}

		return self::$packages;
	}
}
