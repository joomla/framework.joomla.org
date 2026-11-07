<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\Sqlite\SqliteDriver;
use PHPUnit\Framework\TestCase;

/**
 * Base test case providing an in-memory SQLite database with the application's tables
 */
abstract class DatabaseTestCase extends TestCase
{
    /**
     * The database driver
     *
     * @var  DatabaseDriver
     */
    protected DatabaseDriver $db;

    /**
     * Creates a fresh database for every test.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new SqliteDriver(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);

        // Columns as created by the migrations in etc/migrations
        $this->db->setQuery(
            'CREATE TABLE packages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                package TEXT NOT NULL,
                display TEXT NOT NULL,
                repo TEXT NOT NULL,
                stable INTEGER NOT NULL DEFAULT 0,
                deprecated INTEGER NOT NULL DEFAULT 0,
                downloads INTEGER NOT NULL DEFAULT 0,
                has_v1 INTEGER NOT NULL DEFAULT 0,
                has_v2 INTEGER NOT NULL DEFAULT 0,
                has_v3 INTEGER NOT NULL DEFAULT 0,
                has_v4 INTEGER NOT NULL DEFAULT 0,
                abandoned INTEGER NOT NULL DEFAULT 0,
                pullcount INTEGER NOT NULL DEFAULT 0
            )'
        )->execute();

        $this->db->setQuery(
            'CREATE TABLE releases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                package_id INTEGER NOT NULL,
                version TEXT NOT NULL,
                release_date TEXT NULL
            )'
        )->execute();
    }

    /**
     * Inserts a package and returns its record
     *
     * @param   string  $package  The package name
     * @param   array   $data     Additional column values
     *
     * @return  \stdClass
     */
    protected function addPackage(string $package, array $data = []): \stdClass
    {
        $record = (object) array_merge(['package' => $package, 'display' => ucfirst($package), 'repo' => $package], $data);
        $this->db->insertObject('#__packages', $record, 'id');

        return $record;
    }

    /**
     * Inserts releases for a package
     *
     * @param   \stdClass  $package   The package record
     * @param   string     ...$versions  The versions to add
     *
     * @return  void
     */
    protected function addReleases(\stdClass $package, string ...$versions): void
    {
        foreach ($versions as $version) {
            $release = (object) ['package_id' => $package->id, 'version' => $version, 'release_date' => '2020-01-01'];
            $this->db->insertObject('#__releases', $release, 'id');
        }
    }
}
