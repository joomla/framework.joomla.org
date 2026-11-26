<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\Model;

use Joomla\FrameworkWebsite\Model\Exception\PackageNotFoundException;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Tests\DatabaseTestCase;

/**
 * Tests for the package model
 */
final class PackageModelTest extends DatabaseTestCase
{
    public function testGetPackageReturnsThePackage(): void
    {
        $this->addPackage('di', ['display' => 'DI', 'has_v4' => 1]);

        $package = (new PackageModel($this->db))->getPackage('di');

        $this->assertSame('DI', $package->display);
        $this->assertEquals(1, $package->has_v4);
    }

    public function testGetPackageThrowsForUnknownPackage(): void
    {
        $this->expectException(PackageNotFoundException::class);
        $this->expectExceptionCode(404);

        (new PackageModel($this->db))->getPackage('unknown');
    }

    public function testGetSortedPackagesSortsByDisplayName(): void
    {
        $this->addPackage('session', ['display' => 'Session']);
        $this->addPackage('di', ['display' => 'DI']);
        $this->addPackage('application', ['display' => 'Application']);

        $packages = (new PackageModel($this->db))->getSortedPackages();

        $this->assertSame(['Application', 'DI', 'Session'], array_column($packages, 'display'));
    }

    public function testAddAndUpdatePackageStoreAllBranchFlags(): void
    {
        $model = new PackageModel($this->db);
        $model->addPackage('di', 'DI', 'di', true, false, false, false, true, true, true, false);

        $package = $model->getPackage('di');
        $this->assertSame(
            ['has_v1' => 0, 'has_v2' => 1, 'has_v3' => 1, 'has_v4' => 1, 'has_v5' => 0],
            $this->getBranchFlags($package)
        );

        $model->updatePackage((int) $package->id, 'di', 'DI', 'di', true, false, false, false, false, true, true, true);

        $this->assertSame(
            ['has_v1' => 0, 'has_v2' => 0, 'has_v3' => 1, 'has_v4' => 1, 'has_v5' => 1],
            $this->getBranchFlags($model->getPackage('di'))
        );
    }

    /**
     * @return  array<string, int>
     */
    private function getBranchFlags(\stdClass $package): array
    {
        $flags = [];

        foreach (['has_v1', 'has_v2', 'has_v3', 'has_v4', 'has_v5'] as $column) {
            $flags[$column] = (int) $package->$column;
        }

        return $flags;
    }
}
