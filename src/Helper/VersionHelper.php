<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Helper;

/**
 * Helper for the Framework versions which are documented on the site
 */
class VersionHelper
{
    /**
     * The documented versions, oldest first
     *
     * Each version names the flag of the package record which tells whether the package has the version, the branch the documentation is fetched from,
     * and whether the version is released.
     *
     * @var  array<string, array{flag: string, branch: string, stable: bool}>
     */
    private const VERSIONS = [
        '3.x' => ['flag' => 'has_v3', 'branch' => '3.x-dev', 'stable' => true],
        '4.x' => ['flag' => 'has_v4', 'branch' => '4.x-dev', 'stable' => true],
        '5.x' => ['flag' => 'has_v5', 'branch' => '5.x-dev', 'stable' => false],
    ];

    /**
     * The path to the root of the documentation files
     *
     * @var  string
     */
    private $docsPath;

    /**
     * Constructor.
     *
     * @param   string  $docsPath  The path to the root of the documentation files.
     */
    public function __construct(string $docsPath)
    {
        $this->docsPath = rtrim($docsPath, '/\\');
    }

    /**
     * Get the versions a package has documentation for, oldest first
     *
     * @param   \stdClass  $package  The package record.
     *
     * @return  string[]
     */
    public function getDocumentedVersions(\stdClass $package): array
    {
        $versions = [];

        foreach (self::VERSIONS as $version => $info) {
            if (!empty($package->{$info['flag']}) && is_dir($this->docsPath . '/' . $version . '/' . $package->package)) {
                $versions[] = $version;
            }
        }

        return $versions;
    }

    /**
     * Get the branch the documentation of a version is fetched from
     *
     * @param   string  $version  The documentation version.
     *
     * @return  string
     */
    public function getBranch(string $version): string
    {
        if (!isset(self::VERSIONS[$version])) {
            throw new \InvalidArgumentException(sprintf('Unknown documentation version `%s`.', $version));
        }

        return self::VERSIONS[$version]['branch'];
    }

    /**
     * Check if a version is released
     *
     * @param   string  $version  The documentation version.
     *
     * @return  boolean
     */
    public function isStable(string $version): bool
    {
        return self::VERSIONS[$version]['stable'] ?? false;
    }

    /**
     * Check if a page exists in the documentation of a package
     *
     * @param   string     $version  The documentation version.
     * @param   \stdClass  $package  The package record.
     * @param   string     $page     The page, relative to the package's documentation root and without the `.md` extension.
     *
     * @return  boolean
     */
    public function hasPage(string $version, \stdClass $package, string $page): bool
    {
        // Pages leaving the package's documentation directory never exist
        if (preg_match('#(?:^|/)\.\.(?:/|$)#', $page)) {
            return false;
        }

        return is_file($this->docsPath . '/' . $version . '/' . $package->package . '/' . $page . '.md');
    }
}
