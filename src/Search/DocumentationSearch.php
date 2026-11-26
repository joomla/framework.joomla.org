<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Search;

/**
 * Full text search in the fetched package documentation
 *
 * The documentation of a version is a few hundred small Markdown files, so the files are searched directly instead of maintaining a search index.
 */
class DocumentationSearch
{
    /**
     * The maximum number of search terms which are used
     *
     * @var  integer
     */
    private const MAX_TERMS = 10;

    /**
     * The number of characters shown around the first match in a snippet
     *
     * @var  integer
     */
    private const SNIPPET_CONTEXT = 90;

    /**
     * The path to the root of the documentation files
     *
     * @var  string
     */
    private $docsPath;

    /**
     * Constructor.
     *
     * @param   string  $docsPath  The path to the root of the documentation files.
     */
    public function __construct(string $docsPath)
    {
        $this->docsPath = rtrim($docsPath, '/\\');
    }

    /**
     * Get the documentation versions which can be searched, oldest first
     *
     * @return  string[]
     */
    public function getVersions(): array
    {
        $versions = [];

        foreach (glob($this->docsPath . '/*', GLOB_ONLYDIR) ?: [] as $directory) {
            if (preg_match('/^\d+\.x$/', basename($directory))) {
                $versions[] = basename($directory);
            }
        }

        usort($versions, 'version_compare');

        return $versions;
    }

    /**
     * Split a search query into lowercase terms
     *
     * @param   string  $query  The search query.
     *
     * @return  string[]
     */
    public function getTerms(string $query): array
    {
        $terms = preg_split('/\s+/u', mb_strtolower(trim($query)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return \array_slice(array_values(array_unique($terms)), 0, self::MAX_TERMS);
    }

    /**
     * Search the documentation of a version
     *
     * A page matches when it contains all terms. Matches in the page title and the package name rank higher.
     *
     * @param   string       $version   The documentation version.
     * @param   string       $query     The search query.
     * @param   \stdClass[]  $packages  The packages to search, the records of the packages table.
     * @param   integer      $limit     The maximum number of results.
     *
     * @return  \stdClass[]  The results, best match first. Each result has the `package` record, the `page` route, the `title` and the `snippet`, a list of
     *                       objects with the `text` and whether it is a `match`.
     */
    public function search(string $version, string $query, array $packages, int $limit = 50): array
    {
        $terms = $this->getTerms($query);

        if ($terms === [] || !\in_array($version, $this->getVersions(), true)) {
            return [];
        }

        $results = [];

        foreach ($packages as $package) {
            $packagePath = $this->docsPath . '/' . $version . '/' . $package->package;

            if (!is_dir($packagePath)) {
                continue;
            }

            foreach ($this->getPages($packagePath) as $page => $file) {
                $result = $this->matchPage($package, $page, (string) file_get_contents($file), $terms);

                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }

        usort(
            $results,
            static fn (\stdClass $a, \stdClass $b): int => [$b->score, $a->package->display, $a->page] <=> [$a->score, $b->package->display, $b->page]
        );

        return \array_slice($results, 0, $limit);
    }

    /**
     * Get the documentation pages of a package
     *
     * The `index.md` file in the root of the documentation is the navigation, not a page, so it is not searched.
     *
     * @param   string  $packagePath  The path to the package's documentation.
     *
     * @return  array<string, string>  The page routes mapped to the file paths
     */
    private function getPages(string $packagePath): array
    {
        $pages    = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packagePath, \FilesystemIterator::SKIP_DOTS));

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (strtolower($file->getExtension()) !== 'md') {
                continue;
            }

            $page = str_replace('\\', '/', substr($file->getPathname(), \strlen($packagePath) + 1, -3));

            if ($page !== 'index') {
                $pages[$page] = $file->getPathname();
            }
        }

        ksort($pages);

        return $pages;
    }

    /**
     * Match a page against the search terms
     *
     * @param   \stdClass  $package   The package record.
     * @param   string     $page      The page route, relative to the package's documentation root.
     * @param   string     $markdown  The page's Markdown source.
     * @param   string[]   $terms     The lowercase search terms.
     *
     * @return  \stdClass|null  The result, null if the page doesn't contain all terms
     */
    private function matchPage(\stdClass $package, string $page, string $markdown, array $terms): ?\stdClass
    {
        $title = $this->getTitle($markdown, $page);
        $text  = $this->toPlainText($markdown);

        $lowerText    = mb_strtolower($text);
        $lowerTitle   = mb_strtolower($title);
        $lowerPackage = mb_strtolower($package->display . ' ' . $package->package);
        $score        = 0;

        foreach ($terms as $term) {
            $count = mb_substr_count($lowerText, $term);

            if ($count === 0 && mb_strpos($lowerTitle, $term) === false) {
                return null;
            }

            $score += min($count, 20);

            if (mb_strpos($lowerTitle, $term) !== false) {
                $score += 25;
            }

            if (mb_strpos($lowerPackage, $term) !== false) {
                $score += 10;
            }
        }

        return (object) [
            'package' => $package,
            'page'    => $page,
            'title'   => $title,
            'snippet' => $this->getSnippet($text, $terms),
            'score'   => $score,
        ];
    }

    /**
     * Get the title of a page, the text of its first heading
     *
     * @param   string  $markdown  The page's Markdown source.
     * @param   string  $page      The page route, used when the page has no heading.
     *
     * @return  string
     */
    private function getTitle(string $markdown, string $page): string
    {
        if (preg_match('/^#{1,6}\s+(.+?)\s*#*\s*$/m', $markdown, $matches)) {
            return $this->toPlainText($matches[1]);
        }

        return ucwords(str_replace(['-', '_', '/'], [' ', ' ', ' / '], $page));
    }

    /**
     * Get a snippet of the text around the first match, split into the matched terms and the text between them
     *
     * @param   string    $text   The plain text of the page.
     * @param   string[]  $terms  The lowercase search terms.
     *
     * @return  \stdClass[]
     */
    private function getSnippet(string $text, array $terms): array
    {
        $lowerText = mb_strtolower($text);
        $position  = null;

        foreach ($terms as $term) {
            $termPosition = mb_strpos($lowerText, $term);

            if ($termPosition !== false && ($position === null || $termPosition < $position)) {
                $position = $termPosition;
            }
        }

        $start   = max(0, ($position ?? 0) - self::SNIPPET_CONTEXT);
        $snippet = mb_substr($text, $start, 2 * self::SNIPPET_CONTEXT);

        // Cut at word boundaries
        if ($start > 0) {
            $snippet = '…' . preg_replace('/^\S*\s/u', '', $snippet);
        }

        if ($start + 2 * self::SNIPPET_CONTEXT < mb_strlen($text)) {
            $snippet = preg_replace('/\s\S*$/u', '', $snippet) . '…';
        }

        $pattern = '/(' . implode('|', array_map(static fn (string $term): string => preg_quote($term, '/'), $terms)) . ')/iu';
        $parts   = preg_split($pattern, $snippet, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];

        return array_map(
            static fn (string $part): \stdClass => (object) ['text' => $part, 'match' => \in_array(mb_strtolower($part), $terms, true)],
            $parts
        );
    }

    /**
     * Convert Markdown to plain text for searching
     *
     * Code is kept, because class and method names are typical search terms.
     *
     * @param   string  $markdown  The Markdown source.
     *
     * @return  string
     */
    private function toPlainText(string $markdown): string
    {
        $text = preg_replace(
            [
                '/<!--.*?-->/s',            // Comments
                '/^[\s|:-]*-{3,}[\s|:-]*$/m', // Table separator rows and horizontal rules
                '/^(```|~~~)[^\n]*$/m',     // Code fences, the code is kept
                '/!\[([^\]]*)\]\([^)]*\)/', // Images
                '/\[([^\]]*)\]\([^)]*\)/',  // Links
                '/<\/?[a-z][a-z0-9-]*(?:\s[^<>]*)?\/?>/i', // HTML tags, but not code like `<?php` or `$this->`
                '/^\s{0,3}(#{1,6}|>|[-*+]|\d+[.)])\s+/m', // Headings, quotes and list markers
                '/[`*|]+/',                 // Inline code, emphasis and table markers, underscores are kept for identifiers
            ],
            ['', '', '', '$1', '$1', '', '', ''],
            $markdown
        );

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
