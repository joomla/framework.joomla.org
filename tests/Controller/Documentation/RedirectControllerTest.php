<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\Controller\Documentation;

use Joomla\FrameworkWebsite\Controller\Documentation\RedirectController;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Tests\Stub\TestWebApplication;
use Joomla\FrameworkWebsite\View\Documentation\ErrorHtmlView;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the documentation redirect controller
 */
final class RedirectControllerTest extends TestCase
{
    /**
     * @return  array<string, array{string, bool, bool, string}>
     */
    public static function redirectProvider(): array
    {
        return [
            '3.x'    => ['3.x', true, false, '/docs/3.x/di/overview'],
            '4.x'    => ['4.x', false, true, '/docs/4.x/di/overview'],
            'latest' => ['latest', true, true, '/docs/4.x/di/overview'],
        ];
    }

    #[DataProvider('redirectProvider')]
    public function testRedirectsToTheOverviewPage(string $version, bool $hasV3, bool $hasV4, string $location): void
    {
        $app = $this->executeController($version, $hasV3, $hasV4);

        $response = $app->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($location, $response->getHeaderLine('Location'));
    }

    /**
     * @return  array<string, array{string, bool, bool, string}>
     */
    public static function missingBranchProvider(): array
    {
        return [
            '3.x without 3.x branch'    => ['3.x', false, true, '3.x'],
            '4.x without 4.x branch'    => ['4.x', true, false, '4.x'],
            'latest without 4.x branch' => ['latest', true, false, '4.x'],
        ];
    }

    #[DataProvider('missingBranchProvider')]
    public function testShowsAnErrorForMissingBranches(string $version, bool $hasV3, bool $hasV4, string $missing): void
    {
        $errorView = $this->createMock(ErrorHtmlView::class);
        $errorView->expects($this->once())
            ->method('setError')
            ->with(\sprintf('The DI package does not have a %s branch to document.', $missing));
        $errorView->method('render')->willReturn('error page');

        $app = $this->executeController($version, $hasV3, $hasV4, $errorView);

        $response = $app->getResponse();
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('error page', (string) $response->getBody());
    }

    public function testRejectsUnsupportedVersions(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(404);

        $this->executeController('1.x', true, true);
    }

    public function testRejectsMissingParameters(): void
    {
        $app        = new TestWebApplication(['package' => 'di']);
        $controller = new RedirectController($this->createStub(PackageModel::class), $this->createStub(ErrorHtmlView::class), $app->getInput(), $app);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(404);

        $controller->execute();
    }

    private function executeController(string $version, bool $hasV3, bool $hasV4, ?ErrorHtmlView $errorView = null): TestWebApplication
    {
        $model = $this->createStub(PackageModel::class);
        $model->method('getPackage')->willReturn(
            (object) ['package' => 'di', 'display' => 'DI', 'has_v3' => $hasV3 ? '1' : '0', 'has_v4' => $hasV4 ? '1' : '0']
        );

        $app        = new TestWebApplication(['package' => 'di', 'version' => $version]);
        $controller = new RedirectController($model, $errorView ?? $this->createStub(ErrorHtmlView::class), $app->getInput(), $app);

        $this->assertTrue($controller->execute());

        return $app;
    }
}
