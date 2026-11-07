<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Helper;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\FrameworkWebsite\PackageAware;
use Joomla\Http\Http;

/**
 * Helper interacting with the Packagist API
 */
class PackagistHelper
{
    use PackageAware;

    /**
     * The database driver
     *
     * @var  DatabaseInterface
     */
    private $database;

    /**
     * The HTTP driver
     *
     * @var  Http
     */
    private $http;

    /**
     * Instantiate the helper.
     *
     * @param   Http               $http      The HTTP driver.
     * @param   DatabaseInterface  $database  The database driver.
     */
    public function __construct(Http $http, DatabaseInterface $database)
    {
        $this->database = $database;
        $this->http     = $http;
    }

    /**
     * Fetch the download counts for all packages.
     *
     * Packages for which the download count could not be determined are not included in the result.
     *
     * @param   string[]  $failedPackages  Receives the names of the packages for which the download count could not be fetched
     *
     * @return  integer[]  The download counts keyed by package name
     */
    private function fetchDownloadCounts(array &$failedPackages = []): array
    {
        $counts = [];
        foreach (array_keys((array) $this->getPackages()->get('packages')) as $packageName) {
            // Array keys which look like integers are converted by PHP, but package names are always strings
            $packageName = (string) $packageName;
            $url         = "https://packagist.org/packages/joomla/$packageName.json";
            try {
                $response = $this->http->get($url);
            } catch (\RuntimeException $exception) {
                $failedPackages[] = $packageName;

                continue;
            }

            $data = $response->getStatusCode() === 200 ? json_decode((string) $response->getBody()) : null;

            // Only store valid counts, a failed request must not reset the count stored in the database
            if (!isset($data->package->downloads->total) || !is_numeric($data->package->downloads->total)) {
                $failedPackages[] = $packageName;

                continue;
            }

            $counts[$packageName] = (int) $data->package->downloads->total;
        }

        return $counts;
    }

    /**
     * Fetch the download counts from Packagist and store them to the database
     *
     * @return  string[]  The names of the packages for which the download count could not be updated
     *
     * @throws  ExecutionFailureException
     */
    public function syncDownloadCounts(): array
    {
        $failedPackages = [];
        $counts         = $this->fetchDownloadCounts($failedPackages);

        // Begin a transaction in case of error
        $this->database->transactionStart();
        try {
            foreach ($counts as $package => $count) {
                $query = $this->database->getQuery(true)
                    ->update('#__packages')
                    ->set($this->database->quoteName('downloads') . ' = :downloads')
                    ->where($this->database->quoteName('package') . ' = :package');
                $query->bind('downloads', $count, ParameterType::INTEGER);
                $query->bind('package', $package, ParameterType::STRING);
                $this->database->setQuery($query)->execute();
            }

            $this->database->transactionCommit();
        } catch (ExecutionFailureException $exception) {
            $this->database->transactionRollback();
            throw $exception;
        }

        return $failedPackages;
    }
}
