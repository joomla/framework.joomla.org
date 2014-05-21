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
abstract class Helper
{
	/**
	 * Utility method to retrieve a package's display name
	 *
	 * @param   string  $package  Package name
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public static function getPackageDisplayName($package)
	{
		if ($package == 'di')
		{
			return 'DI';
		}
		elseif ($package == 'github')
		{
			return 'GitHub';
		}
		elseif ($package == 'http')
		{
			return 'HTTP';
		}
		elseif ($package == 'ldap')
		{
			return 'LDAP';
		}
		elseif ($package == 'linkedin')
		{
			return 'LinkedIn';
		}
		elseif ($package == 'oauth1')
		{
			return 'OAuth1';
		}
		elseif ($package == 'oauth2')
		{
			return 'OAuth2';
		}
		elseif ($package == 'openstreetmap')
		{
			return 'OpenStreetMap';
		}
		elseif ($package == 'uri')
		{
			return 'URI';
		}
		else
		{
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
	public static function parseComposer()
	{
		// Data container
		$packages = array();

		// Read the installed.json file
		$installed = json_decode(file_get_contents(JPATH_ROOT . '/vendor/composer/installed.json'));

		// Loop through and extract the package name and version for all Joomla! Framework packages
		foreach ($installed as $package)
		{
			if (strpos($package->name, 'joomla') !== 0)
			{
				continue;
			}

			$packages[str_replace('joomla/', '', $package->name)] = ['version' => $package->version];
		}

		return $packages;
	}
}
