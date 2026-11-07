<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\Model;

use Joomla\FrameworkWebsite\Model\ReleaseModel;
use Joomla\FrameworkWebsite\Tests\DatabaseTestCase;

/**
 * Tests for the release model
 */
final class ReleaseModelTest extends DatabaseTestCase
{
    public function testGetLatestReleasesComparesVersionsSemantically(): void
    {
        $package = $this->addPackage('di');
        $this->addReleases($package, '1.9.0', '1.10.0', '1.2.0', '2.0.0', '2.0.10', '2.0.9');

        $reports = (new ReleaseModel($this->db))->getLatestReleases([$package->id => $package]);

        $this->assertSame('1.10.0', $reports[$package->id]->v1->version);
        $this->assertSame('2.0.10', $reports[$package->id]->v2->version);
    }

    public function testGetLatestReleasesGroupsByMultiDigitMajorVersionsAndVPrefix(): void
    {
        $package = $this->addPackage('di');
        $this->addReleases($package, 'v1.0.0', '1.1.0', '10.0.0', '10.2.0', 'v11.0.0');

        $report = (new ReleaseModel($this->db))->getLatestReleases([$package->id => $package])[$package->id];

        $this->assertSame('1.1.0', $report->v1->version);
        $this->assertSame('10.2.0', $report->v10->version);
        $this->assertSame('v11.0.0', $report->v11->version);
        $this->assertObjectNotHasProperty('v0', $report);
    }

    public function testGetLatestReleasesSkipsVersionsWithoutMajorVersionAndOtherPackages(): void
    {
        $package = $this->addPackage('di');
        $other   = $this->addPackage('event');
        $this->addReleases($package, 'dev-main', '3.0.0');
        $this->addReleases($other, '4.0.0');

        $reports = (new ReleaseModel($this->db))->getLatestReleases([$package->id => $package]);

        $this->assertEquals([$package->id], array_keys($reports));
        $this->assertSame($package, $reports[$package->id]->package);
        $this->assertSame('3.0.0', $reports[$package->id]->v3->version);
        $this->assertObjectNotHasProperty('v4', $reports[$package->id]);
    }

    public function testGetLatestReleasesReturnsReportForPackageWithoutReleases(): void
    {
        $package = $this->addPackage('di');

        $report = (new ReleaseModel($this->db))->getLatestReleases([$package->id => $package])[$package->id];

        $this->assertSame($package, $report->package);
        $this->assertSame(['package'], array_keys(get_object_vars($report)));
    }

    public function testGetPackageHistorySortsVersionsAscending(): void
    {
        $package = $this->addPackage('di');
        $this->addReleases($package, '1.10.0', 'v1.2.0', '1.9.0', '2.0.0');

        $history = (new ReleaseModel($this->db))->getPackageHistory($package);

        $this->assertSame(['v1.2.0', '1.9.0', '1.10.0', '2.0.0'], array_column($history, 'version'));
    }

    public function testGetPackageHistoryThrowsWithoutReleases(): void
    {
        $package = $this->addPackage('di');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(404);

        (new ReleaseModel($this->db))->getPackageHistory($package);
    }

    public function testAddHasAndUpdateRelease(): void
    {
        $package = $this->addPackage('di');
        $model   = new ReleaseModel($this->db);

        $this->assertFalse($model->hasRelease($package, '1.0.0'));

        $model->addRelease($package, '1.0.0', new \DateTime('2021-02-03'));

        $this->assertTrue($model->hasRelease($package, '1.0.0'));

        $release = $model->getRelease($package, '1.0.0');
        $this->assertSame('2021-02-03', $release->release_date);

        $model->updateRelease((int) $release->id, $package, '1.0.0', new \DateTime('2022-03-04'));

        $this->assertSame('2022-03-04', $model->getRelease($package, '1.0.0')->release_date);
    }
}
