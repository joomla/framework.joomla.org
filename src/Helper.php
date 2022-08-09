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
 */
class Helper
{
    use PackageAware;

/**
     * Utility method to retrieve a package's abandoned state
     *
     * @param   string  $package  Package name
     *
     * @return  boolean
     */


    public function getPackageAbandoned(string $package): bool
    {
        return $this->getPackages()->get("packages.$package.abandoned", false);
    }

    /**
     * Utility method to retrieve a package's deprecation status
     *
     * @param   string  $package  Package name
     *
     * @return  boolean
     */
    public function getPackageDeprecated(string $package): bool
    {
        return $this->getPackages()->get("packages.$package.deprecated", false);
    }

    /**
     * Utility method to retrieve a package's display name
     *
     * @param   string  $package  Package name
     *
     * @return  string
     */
    public function getPackageDisplayName(string $package): string
    {
        return $this->getPackages()->get("packages.$package.display", ucfirst($package));
    }

    /**
     * Utility method to retrieve a package's repository name
     *
     * @param   string  $package  Package name
     *
     * @return  string
     */
    public function getPackageRepositoryName(string $package): string
    {
        return $this->getPackages()->get("packages.$package.repo", $package);
    }

    /**
     * Utility method to retrieve a package's stable status
     *
     * @param   string  $package  Package name
     *
     * @return  boolean
     */
    public function getPackageStable(string $package): bool
    {
        return $this->getPackages()->get("packages.$package.stable", true);
    }
}
