<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\Helper;

use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Joomla\FrameworkWebsite\Tests\DatabaseTestCase;
use Joomla\Http\Http;
use Joomla\Http\Response;
use Joomla\Registry\Registry;
use Laminas\Diactoros\StreamFactory;

/**
 * Tests for the Packagist helper
 */
final class PackagistHelperTest extends DatabaseTestCase
{
    public function testSyncDownloadCountsStoresCountsAndKeepsThemForFailedPackages(): void
    {
        foreach (['application', 'di', 'event', 'filter', 'input'] as $package) {
            $this->addPackage($package, ['downloads' => 100]);
        }

        $http = $this->createStub(Http::class);
        $http->method('get')->willReturnCallback(
            static function (string $url): Response {
                return match (basename($url, '.json')) {
                    'application' => self::response(200, ['package' => ['downloads' => ['total' => 12345]]]),
                    'di'          => self::response(404, ['status' => 'error', 'message' => 'Package not found']),
                    'event'       => self::response(200, ['package' => ['downloads' => []]]),
                    'filter'      => throw new \RuntimeException('Connection timed out'),
                    'input'       => self::response(200, 'not JSON'),
                    default       => throw new \LogicException('Unexpected request to ' . $url),
                };
            }
        );

        $helper = new PackagistHelper($http, $this->db);
        $helper->setPackages(new Registry(['packages' => array_fill_keys(['application', 'di', 'event', 'filter', 'input'], [])]));

        $this->assertSame(['di', 'event', 'filter', 'input'], $helper->syncDownloadCounts());

        $downloads = $this->db->setQuery('SELECT package, downloads FROM packages')->loadAssocList('package', 'downloads');

        $this->assertEquals(
            ['application' => 12345, 'di' => 100, 'event' => 100, 'filter' => 100, 'input' => 100],
            $downloads,
            'Failed lookups must not reset the stored download counts'
        );
    }

    private static function response(int $status, array|string $body): Response
    {
        $contents = \is_array($body) ? json_encode($body) : $body;

        return new Response((new StreamFactory())->createStream($contents), $status);
    }
}
