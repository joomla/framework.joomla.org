<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Model;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Model\DatabaseModelInterface;
use Joomla\Model\DatabaseModelTrait;

/**
 * Model class for releases
 */
class ReleaseModel implements DatabaseModelInterface
{
    use DatabaseModelTrait;

    /**
     * Instantiate the model.
     *
     * @param   DatabaseDriver  $db  The database adapter.
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->setDb($db);
    }

    /**
     * Add a release for a package
     *
     * @param   \stdClass  $package      The package to add the release for
     * @param   string     $version      The package's release version
     * @param   \DateTime  $releaseDate  The date this release was created
     *
     * @return  void
     */
    public function addRelease(\stdClass $package, string $version, \DateTime $releaseDate): void
    {
        $db   = $this->getDb();
        $data = (object) [
            'package_id'   => $package->id,
            'version'      => $version,
            'release_date' => $releaseDate->format('Y-m-d'),
        ];
        $db->insertObject('#__releases', $data);
    }

    /**
     * Fetches the latest releases for each Framework package
     *
     * @param   \stdClass[]  $packages  The packages to fetch the releases for
     *
     * @return  \stdClass[]
     */
    public function getLatestReleases(array $packages): array
    {
        $db       = $this->getDb();
        $query    = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__releases'));
        $releases = $db->setQuery($query)->loadObjectList();
        $reports  = [];
        // Loop through the releases and build the reports
        foreach ($packages as $package) {
            $info                  = new \stdClass();
            $info->package         = $package;
            $reports[$package->id] = $info;
        }

        foreach ($releases as $release) {
            // Skip releases of packages which aren't included in the report
            if (!isset($reports[$release->package_id])) {
                continue;
            }

            // Skip versions without a numeric major version
            if (!preg_match('/^v?(\d+)/i', $release->version, $matches)) {
                continue;
            }

            $major = 'v' . $matches[1];

            // Keep the highest release per major version, the versions must be compared semantically as a string comparison sorts 1.9.0 above 1.10.0
            if (isset($reports[$release->package_id]->$major) && $this->compareVersions($reports[$release->package_id]->$major->version, $release->version) >= 0) {
                continue;
            }

            $reports[$release->package_id]->$major = $release;
        }

        return $reports;
    }

    /**
     * Get the release history for a package
     *
     * @param   \stdClass  $package  The package to retrieve the history for
     *
     * @return  \stdClass[]
     *
     * @throws  \RuntimeException
     */
    public function getPackageHistory(\stdClass $package): array
    {
        // Get the package data for the package specified via the route
        $db    = $this->getDb();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__releases'))
            ->where($db->quoteName('package_id') . ' = :packageId');
        $query->bind('packageId', $package->id, ParameterType::INTEGER);
        $releases = $db->setQuery($query)->loadObjectList();
        // Bail if we don't have any data for the given package
        if (!\count($releases)) {
            throw new \RuntimeException(sprintf('Unable to find release data for the `%s` package', $package->display), 404);
        }

        // Sort the releases semantically, the database would sort the versions as strings
        usort(
            $releases,
            function (\stdClass $a, \stdClass $b): int {
                return $this->compareVersions($a->version, $b->version);
            }
        );

        return $releases;
    }

    /**
     * Compare two version strings semantically
     *
     * @param   string  $a  The first version
     * @param   string  $b  The second version
     *
     * @return  integer  A negative value if the first version is lower, 0 if both are equal, a positive value if the first version is higher
     */
    private function compareVersions(string $a, string $b): int
    {
        return version_compare(ltrim($a, 'vV'), ltrim($b, 'vV'));
    }

    /**
     * Get the release for the package at the given version
     *
     * @param   \stdClass  $package  The package to check for the release on
     * @param   string     $version  The version to check for
     *
     * @return  \stdClass
     */
    public function getRelease(\stdClass $package, string $version): \stdClass
    {
        $db    = $this->getDb();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__releases'))
            ->where($db->quoteName('package_id') . ' = :packageId')
            ->where($db->quoteName('version') . ' = :version')
            ->bind('packageId', $package->id, ParameterType::INTEGER)
            ->bind('version', $version, ParameterType::STRING);
        return $db->setQuery($query)->loadObject();
    }

    /**
     * Check if the package has a release at the given version
     *
     * @param   \stdClass  $package  The package to check for the release on
     * @param   string     $version  The version to check for
     *
     * @return  boolean
     */
    public function hasRelease(\stdClass $package, string $version): bool
    {
        $db    = $this->getDb();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__releases'))
            ->where($db->quoteName('package_id') . ' = :packageId')
            ->where($db->quoteName('version') . ' = :version')
            ->bind('packageId', $package->id, ParameterType::INTEGER)
            ->bind('version', $version, ParameterType::STRING);
        $id = $db->setQuery($query)->loadResult();
        return $id !== null;
    }

    /**
     * Update a release for a package
     *
     * @param   integer    $releaseId    The release ID to be updated
     * @param   \stdClass  $package      The package to update the release for
     * @param   string     $version      The package's release version
     * @param   \DateTime  $releaseDate  The date this release was created
     *
     * @return  void
     */
    public function updateRelease(int $releaseId, \stdClass $package, string $version, \DateTime $releaseDate): void
    {
        $db   = $this->getDb();
        $data = (object) [
            'id'           => $releaseId,
            'package_id'   => $package->id,
            'version'      => $version,
            'release_date' => $releaseDate->format('Y-m-d'),
        ];
        $db->updateObject('#__releases', $data, ['id']);
    }
}
