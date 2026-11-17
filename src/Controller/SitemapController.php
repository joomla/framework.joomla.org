<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Input\Input;
use Joomla\Uri\Uri;
use Laminas\Diactoros\Response\XmlResponse;

/**
 * Controller generating the XML sitemap from the package data and the documentation files
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class SitemapController extends AbstractController
{
    /**
     * Routes of the static pages, relative to the site root
     *
     * @var  string[]
     */
    private const STATIC_PAGES = ['', 'api', 'contribute', 'docs', 'status'];

    /**
     * Documentation versions and the package flag indicating the package has documentation for it
     *
     * @var  array<string, string>
     */
    private const DOCS_VERSIONS = [
        '3.x' => 'has_v3',
        '4.x' => 'has_v4',
    ];

    /**
     * The package model.
     *
     * @var  PackageModel
     */
    private $packageModel;

    /**
     * The path to the root of the documentation files.
     *
     * @var  string
     */
    private $docsPath;

    /**
     * Constructor.
     *
     * @param   PackageModel         $packageModel  The package model.
     * @param   string               $docsPath      The path to the root of the documentation files.
     * @param   Input                $input         The input object.
     * @param   AbstractApplication  $app           The application object.
     */
    public function __construct(PackageModel $packageModel, string $docsPath, ?Input $input = null, ?AbstractApplication $app = null)
    {
        parent::__construct($input, $app);

        $this->packageModel = $packageModel;
        $this->docsPath     = rtrim($docsPath, '/\\');
    }

    /**
     * Execute the controller.
     *
     * @return  boolean
     */
    public function execute(): bool
    {
        // Enable browser caching
        $this->getApplication()->allowCache(true);

        $this->getApplication()->setResponse(new XmlResponse($this->buildSitemap()));

        return true;
    }

    /**
     * Build the sitemap XML
     *
     * @return  string
     */
    public function buildSitemap(): string
    {
        $baseUrl = $this->getBaseUrl();

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach (self::STATIC_PAGES as $page) {
            $this->writeUrl($xml, $baseUrl . $page);
        }

        $packages = $this->packageModel->getSortedPackages();

        foreach ($packages as $package) {
            $this->writeUrl($xml, $baseUrl . 'status/' . $package->package);
        }

        foreach ($packages as $package) {
            foreach (self::DOCS_VERSIONS as $version => $flag) {
                if (empty($package->$flag)) {
                    continue;
                }

                foreach ($this->getDocsPages($version, $package->package) as $page => $modified) {
                    $route = implode('/', array_map('rawurlencode', explode('/', $page)));
                    $this->writeUrl($xml, $baseUrl . 'docs/' . $version . '/' . $package->package . '/' . $route, $modified);
                }
            }
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Get the documentation pages of a package for a version
     *
     * @param   string  $version  The documentation version
     * @param   string  $package  The package name
     *
     * @return  array<string, int>  The page routes, relative to the package's documentation root, mapped to their modification time
     */
    private function getDocsPages(string $version, string $package): array
    {
        $packagePath = $this->docsPath . '/' . $version . '/' . $package;

        if (!is_dir($packagePath)) {
            return [];
        }

        $pages    = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packagePath, \FilesystemIterator::SKIP_DOTS));

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'md') {
                continue;
            }

            $page = str_replace('\\', '/', substr($file->getPathname(), \strlen($packagePath) + 1, -3));

            // The index file is rendered as the sidebar of the documentation, not as a page
            if ($page === 'index') {
                continue;
            }

            $pages[$page] = (int) $file->getMTime();
        }

        ksort($pages);

        return $pages;
    }

    /**
     * Get the absolute URL of the site root, with a trailing slash
     *
     * Uses the `site_url` configuration value if set, otherwise the host detected from the request.
     *
     * @return  string
     */
    private function getBaseUrl(): string
    {
        $app     = $this->getApplication();
        $siteUrl = trim((string) $app->get('site_url', ''));
        $host    = $siteUrl !== '' ? (new Uri($siteUrl))->toString(['scheme', 'user', 'pass', 'host', 'port']) : $app->get('uri.base.host');

        return $host . $app->get('uri.base.path');
    }

    /**
     * Write a URL entry to the sitemap
     *
     * @param   \XMLWriter  $xml       The XML writer
     * @param   string      $location  The absolute URL
     * @param   int|null    $modified  The modification time of the page, if known
     *
     * @return  void
     */
    private function writeUrl(\XMLWriter $xml, string $location, ?int $modified = null): void
    {
        $xml->startElement('url');
        $xml->writeElement('loc', $location);

        if ($modified !== null) {
            $xml->writeElement('lastmod', gmdate('Y-m-d', $modified));
        }

        $xml->endElement();
    }
}
