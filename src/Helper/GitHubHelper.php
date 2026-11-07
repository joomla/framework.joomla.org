<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Helper;

use Joomla\Application\AbstractApplication;
use Joomla\Github\Github;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Helper interacting with the GitHub API
 */
class GitHubHelper
{
    /**
         * Application object
         *
         * @var  AbstractApplication
         */
    private $application;
    /**
         * Cache pool
         *
         * @var  CacheItemPoolInterface
         */
    private $cache;
    /**
         * The GitHub API adapter
         *
         * @var  Github
         */
    private $github;
    /**
         * Instantiate the helper.
         *
         * @param   Github                  $github       The GitHub API adapter.
         * @param   CacheItemPoolInterface  $cache        Cache pool.
         * @param   AbstractApplication     $application  The application object.
         */
    public function __construct(Github $github, CacheItemPoolInterface $cache, AbstractApplication $application)
    {
        $this->application = $application;
        $this->cache       = $cache;
        $this->github      = $github;
    }

    /**
     * Generate the cache key for a documentation file
     *
     * @param   string     $version  The Framework version to fetch documentation for.
     * @param   \stdClass  $package  The Framework package the documentation belongs to.
     * @param   string     $path     The path to the documentation file.
     *
     * @return  string
     */
    public function generateDocsFileCacheKey(string $version, \stdClass $package, string $path): string
    {
        return str_replace('/', '.', $version . '/' . $package->package . '/' . $path);
    }

    /**
     * Render a documentation file
     *
     * @param   string     $version  The Framework version to fetch documentation for.
     * @param   \stdClass  $package  The Framework package the documentation belongs to.
     * @param   string     $path     The path to the documentation file.
     *
     * @return  string
     */
    public function renderDocsFile(string $version, \stdClass $package, string $path): string
    {
        $basePath = JPATH_ROOT . '/docs/' . $version . '/' . $package->package;
        $docsPath = $basePath . '/' . $path . '.md';

        $realBasePath = realpath($basePath);
        $realDocsPath = realpath($docsPath);

        // Make sure the resolved file actually lives inside the package's docs directory to prevent path traversal
        if ($realBasePath === false || $realDocsPath === false || strncmp($realDocsPath, $realBasePath . \DIRECTORY_SEPARATOR, \strlen($realBasePath) + 1) !== 0) {
            throw new \InvalidArgumentException(sprintf('No documentation found for `%s` in the `%2$s` package for version `%3$s`.', $path, $package->display, $version), 404);
        }

        $key  = $this->generateDocsFileCacheKey($version, $package, $path);
        $item = $this->cache->getItem($key);
        // Make sure we got a hit on the item, otherwise we'll have to re-cache
        if ($item->isHit()) {
            $rendered = $item->get();
        } else {
            $rendered    = $this->github->markdown->render(file_get_contents($docsPath), 'gfm', 'joomla-framework/' . $package->repo);
            $routePrefix = $this->application->get('uri.base.path') . 'docs/' . $version . '/' . $package->package . '/';
            $rendered    = $this->rewriteDocsLinks($rendered, $routePrefix);
            // Cache the result for 7 days
            $sevenDaysInSeconds = 60 * 60 * 24 * 7;
            $item->set($rendered);
            $item->expiresAfter($sevenDaysInSeconds);
            $this->cache->save($item);
        }

        return $rendered;
    }

    /**
     * Rewrite relative links to other documentation files to the routes of the rendered pages
     *
     * Only relative links to Markdown files are changed, absolute links (i.e. to a README.md file on GitHub) are kept as is.
     *
     * @param   string  $rendered     The rendered documentation file.
     * @param   string  $routePrefix  The route prefix for the package's documentation pages.
     *
     * @return  string
     */
    private function rewriteDocsLinks(string $rendered, string $routePrefix): string
    {
        return preg_replace_callback(
            '/href="([^"]*)"/',
            static function (array $matches) use ($routePrefix): string {
                $href = $matches[1];

                // Skip absolute links (with a scheme or protocol relative), absolute paths and anchors on the same page
                if ($href === '' || preg_match('#^(?:[a-z][a-z0-9+.\-]*:|/|\#)#i', $href)) {
                    return $matches[0];
                }

                // Split off the query string and fragment so links to a section of another page keep working
                if (!preg_match('/^([^?#]+)\.md((?:[?#].*)?)$/i', $href, $parts)) {
                    return $matches[0];
                }

                $path = $parts[1];

                while (strpos($path, './') === 0) {
                    $path = substr($path, 2);
                }

                // Links leaving the docs directory can't be mapped to a documentation page
                if (preg_match('#(?:^|/)\.\.(?:/|$)#', $path)) {
                    return $matches[0];
                }

                return 'href="' . $routePrefix . $path . $parts[2] . '"';
            },
            $rendered
        );
    }

    /**
     * Render the sidebar (the index file) of a package's documentation
     *
     * @param   string     $version  The Framework version to fetch documentation for.
     * @param   \stdClass  $package  The Framework package the documentation belongs to.
     *
     * @return  string
     */
    public function renderDocsSidebar(string $version, \stdClass $package): string
    {
        // Style the navigation lists and links, this must only be applied to the sidebar and not to the page contents
        return preg_replace_callback(
            '/<(ul|a)\b([^>]*)>/i',
            static function (array $matches): string {
                $classes = strtolower($matches[1]) === 'ul' ? 'list-unstyled' : 'text-decoration-none px-3 py-2 d-block';

                // Merge with an existing class attribute, an element must not have the attribute twice
                if (preg_match('/\sclass\s*=\s*"/i', $matches[2])) {
                    $attributes = preg_replace('/(\sclass\s*=\s*")/i', '$1' . $classes . ' ', $matches[2], 1);
                } else {
                    $attributes = ' class="' . $classes . '"' . $matches[2];
                }

                return '<' . $matches[1] . $attributes . '>';
            },
            $this->renderDocsFile($version, $package, 'index')
        );
    }
}
