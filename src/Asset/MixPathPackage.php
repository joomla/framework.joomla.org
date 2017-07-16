<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Asset;

use Symfony\Component\Asset\PathPackage as BasePathPackage;

/**
 * Extended path package for resolving assets from a Laravel Mix manifest
 */
class MixPathPackage extends BasePathPackage
{
	/**
	 * Returns an absolute or root-relative public path.
	 *
	 * @param   string  $path  A path
	 *
	 * @return  string  The public path
	 */
	public function getUrl($path)
	{
		if ($this->isAbsoluteUrl($path))
		{
			return $path;
		}

		$versionedPath = $this->getVersionStrategy()->applyVersion($this->getBasePath() . $path);

		// If absolute or begins with /, we're done
		if ($this->isAbsoluteUrl($versionedPath) || ($versionedPath && '/' === $versionedPath[0]))
		{
			return $versionedPath;
		}

		return $this->getBasePath() . ltrim($versionedPath, '/');
	}
}
