<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\Renderer;

use Joomla\FrameworkWebsite\Renderer\FrameworkTwigRuntime;
use Joomla\FrameworkWebsite\Tests\Stub\TestWebApplication;
use Joomla\Preload\PreloadManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Twig runtime
 */
final class FrameworkTwigRuntimeTest extends TestCase
{
    public function testGetSriAttributesReturnsTheAttributesFromTheManifest(): void
    {
        $manifest = JPATH_ROOT . '/sri-manifest.json';
        file_put_contents($manifest, json_encode(['/css/template.css' => ['crossorigin' => 'anonymous', 'integrity' => 'sha384-abc']]));

        $runtime = new FrameworkTwigRuntime(new TestWebApplication(), new PreloadManager(), $manifest);

        $this->assertSame(' crossorigin="anonymous" integrity="sha384-abc"', $runtime->getSriAttributes('css/template.css'));
        $this->assertSame('', $runtime->getSriAttributes('css/unknown.css'));
    }

    public function testGetSriAttributesThrowsForMissingManifest(): void
    {
        $runtime = new FrameworkTwigRuntime(new TestWebApplication(), new PreloadManager(), JPATH_ROOT . '/missing.json');

        $this->expectException(\RuntimeException::class);

        $runtime->getSriAttributes('css/template.css');
    }

    public function testGetSriAttributesThrowsForInvalidManifest(): void
    {
        $manifest = JPATH_ROOT . '/invalid-sri-manifest.json';
        file_put_contents($manifest, '{invalid');

        $runtime = new FrameworkTwigRuntime(new TestWebApplication(), new PreloadManager(), $manifest);

        $this->expectException(\RuntimeException::class);

        $runtime->getSriAttributes('css/template.css');
    }

    public function testGetRouteUriPrefixesTheBasePath(): void
    {
        $runtime = new FrameworkTwigRuntime(new TestWebApplication(), new PreloadManager(), JPATH_ROOT . '/missing.json');

        $this->assertSame('/status', $runtime->getRouteUri('status'));
    }
}
