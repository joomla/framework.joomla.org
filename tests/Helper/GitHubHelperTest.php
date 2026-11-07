<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\Helper;

use Joomla\FrameworkWebsite\Helper\GitHubHelper;
use Joomla\FrameworkWebsite\Tests\Stub\TestWebApplication;
use Joomla\Github\Github;
use Joomla\Github\Package\Markdown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Tests for the GitHub helper
 */
final class GitHubHelperTest extends TestCase
{
    /**
     * The package record used by the tests
     *
     * @var  \stdClass
     */
    private \stdClass $package;

    /**
     * The documentation directory of the test package
     *
     * @var  string
     */
    private string $docsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->package  = (object) ['package' => 'di', 'display' => 'DI', 'repo' => 'di'];
        $this->docsPath = JPATH_ROOT . '/docs/4.x/di';

        if (!is_dir($this->docsPath . '/classes')) {
            mkdir($this->docsPath . '/classes', 0777, true);
        }

        file_put_contents($this->docsPath . '/index.md', '* [Overview](overview.md)');
        file_put_contents($this->docsPath . '/overview.md', '# Overview');
        file_put_contents($this->docsPath . '/classes/Container.md', '# Container');
        file_put_contents(JPATH_ROOT . '/docs/secret.md', 'outside of the package docs');
    }

    public function testGenerateDocsFileCacheKey(): void
    {
        $helper = new GitHubHelper($this->createStub(Github::class), new ArrayAdapter(), new TestWebApplication());

        $this->assertSame('4.x.di.classes.Container', $helper->generateDocsFileCacheKey('4.x', $this->package, 'classes/Container'));
    }

    /**
     * @return  array<string, array{string, string}>
     */
    public static function linkProvider(): array
    {
        return [
            'relative page'                 => ['overview.md', '/docs/4.x/di/overview'],
            'relative page in subdirectory' => ['classes/Container.md', '/docs/4.x/di/classes/Container'],
            'leading ./ is removed'         => ['./classes/Container.md', '/docs/4.x/di/classes/Container'],
            'fragment is kept'              => ['overview.md#usage', '/docs/4.x/di/overview#usage'],
            'query string is kept'          => ['overview.md?tab=1', '/docs/4.x/di/overview?tab=1'],
            'absolute URL'                  => ['https://github.com/joomla-framework/di/blob/4.x-dev/README.md', 'https://github.com/joomla-framework/di/blob/4.x-dev/README.md'],
            'protocol relative URL'         => ['//example.com/page.md', '//example.com/page.md'],
            'absolute path'                 => ['/docs/page.md', '/docs/page.md'],
            'anchor on the same page'       => ['#usage', '#usage'],
            'link leaving the docs'         => ['../README.md', '../README.md'],
            'non-Markdown file'             => ['images/diagram.png', 'images/diagram.png'],
            'mailto link'                   => ['mailto:security@joomla.org', 'mailto:security@joomla.org'],
        ];
    }

    #[DataProvider('linkProvider')]
    public function testRenderDocsFileRewritesRelativeMarkdownLinks(string $href, string $expected): void
    {
        $helper = $this->createHelper('<p><a href="' . $href . '">Link</a></p>');

        $this->assertSame('<p><a href="' . $expected . '">Link</a></p>', $helper->renderDocsFile('4.x', $this->package, 'overview'));
    }

    public function testRenderDocsFileUsesTheCacheUntilTheFileChanges(): void
    {
        $markdown = $this->createMock(Markdown::class);
        $markdown->expects($this->exactly(2))
            ->method('render')
            ->with('# Overview', 'gfm', 'joomla-framework/di')
            ->willReturnOnConsecutiveCalls('<h1>Overview</h1>', '<h1>Updated</h1>');

        $helper = new GitHubHelper($this->createGithub($markdown), new ArrayAdapter(), new TestWebApplication());

        $this->assertSame('<h1>Overview</h1>', $helper->renderDocsFile('4.x', $this->package, 'overview'));
        $this->assertSame('<h1>Overview</h1>', $helper->renderDocsFile('4.x', $this->package, 'overview'), 'The second call must use the cache');

        touch($this->docsPath . '/overview.md', time() + 10);

        $this->assertSame('<h1>Updated</h1>', $helper->renderDocsFile('4.x', $this->package, 'overview'), 'A changed file must be rendered again');
    }

    /**
     * @return  array<string, array{string}>
     */
    public static function invalidPathProvider(): array
    {
        return [
            'missing file'         => ['missing'],
            'path traversal'       => ['../../secret'],
            'path traversal to ..' => ['classes/../../secret'],
        ];
    }

    #[DataProvider('invalidPathProvider')]
    public function testRenderDocsFileRejectsFilesOutsideThePackageDocs(string $path): void
    {
        $markdown = $this->createMock(Markdown::class);
        $markdown->expects($this->never())->method('render');

        $helper = new GitHubHelper($this->createGithub($markdown), new ArrayAdapter(), new TestWebApplication());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(404);

        $helper->renderDocsFile('4.x', $this->package, $path);
    }

    public function testRenderDocsSidebarAddsClassesToListsAndLinks(): void
    {
        $helper = $this->createHelper(
            '<ul dir="auto"><li><a href="overview.md">Overview</a></li></ul>'
            . '<ul><li><a class="anchor" aria-hidden="true" href="#top">Top</a></li></ul>'
        );

        $this->assertSame(
            '<ul class="list-unstyled" dir="auto"><li><a class="text-decoration-none px-3 py-2 d-block" href="/docs/4.x/di/overview">Overview</a></li></ul>'
            . '<ul class="list-unstyled"><li><a class="text-decoration-none px-3 py-2 d-block anchor" aria-hidden="true" href="#top">Top</a></li></ul>',
            $helper->renderDocsSidebar('4.x', $this->package)
        );
    }

    public function testRenderDocsFileDoesNotAddSidebarClasses(): void
    {
        $helper = $this->createHelper('<ul><li><a href="#top">Top</a></li></ul>');

        $this->assertSame('<ul><li><a href="#top">Top</a></li></ul>', $helper->renderDocsFile('4.x', $this->package, 'overview'));
    }

    private function createHelper(string $rendered): GitHubHelper
    {
        $markdown = $this->createStub(Markdown::class);
        $markdown->method('render')->willReturn($rendered);

        return new GitHubHelper($this->createGithub($markdown), new ArrayAdapter(), new TestWebApplication());
    }

    private function createGithub(Markdown $markdown): Github
    {
        return new class ($markdown) extends Github {
            public function __construct(private Markdown $markdown)
            {
            }

            public function __get($name)
            {
                if ($name === 'markdown') {
                    return $this->markdown;
                }

                return parent::__get($name);
            }
        };
    }
}
