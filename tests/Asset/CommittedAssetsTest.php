<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\Asset;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Checks that the committed asset manifests match the committed assets in www/media
 *
 * The browser refuses to load an asset whose SRI hash doesn't match, so forgetting to run
 * `bin/framework template:generate-sri` after rebuilding the assets breaks the site.
 */
final class CommittedAssetsTest extends TestCase
{
    private const MEDIA_PATH = __DIR__ . '/../../www/media';

    /**
     * @return  array<string, array{string, string}>
     */
    public static function sriManifestProvider(): array
    {
        $cases = [];

        foreach (self::readManifest('sri-manifest.json') as $asset => $attributes) {
            $cases[$asset] = [$asset, $attributes['integrity'] ?? ''];
        }

        return $cases;
    }

    #[DataProvider('sriManifestProvider')]
    public function testSriHashMatchesTheAsset(string $asset, string $integrity): void
    {
        $file = self::MEDIA_PATH . $asset;

        $this->assertFileExists($file);
        $this->assertSame(
            'sha384-' . base64_encode(hash_file('sha384', $file, true)),
            $integrity,
            sprintf('The SRI hash of `%s` is outdated, run `bin/framework template:generate-sri`.', $asset)
        );
    }

    public function testEveryVersionedAssetHasAnSriHash(): void
    {
        $versioned = array_keys(self::readManifest('manifest.json'));
        $sri       = array_keys(self::readManifest('sri-manifest.json'));

        sort($versioned);
        sort($sri);

        $this->assertSame($versioned, $sri);
    }

    public function testVersionedAssetsExist(): void
    {
        foreach (array_keys(self::readManifest('manifest.json')) as $asset) {
            $this->assertFileExists(self::MEDIA_PATH . $asset);
        }
    }

    private static function readManifest(string $name): array
    {
        $data = json_decode((string) file_get_contents(self::MEDIA_PATH . '/' . $name), true);

        if (!\is_array($data)) {
            throw new \RuntimeException(sprintf('Could not read `www/media/%s`.', $name));
        }

        return $data;
    }
}
