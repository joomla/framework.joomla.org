<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Documentation;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Search\DocumentationSearch;
use Joomla\FrameworkWebsite\View\TwigHtmlView;
use Twig\Environment;

/**
 * Documentation search HTML view class for the application
 */
class SearchHtmlView extends TwigHtmlView
{
    /**
     * The version which is searched when no or an unknown version is requested, the version the `latest` documentation routes point to
     *
     * @var  string
     */
    private const DEFAULT_VERSION = '4.x';

    /**
     * The documentation search.
     *
     * @var  DocumentationSearch
     */
    private $documentationSearch;

    /**
     * The package model object.
     *
     * @var  PackageModel
     */
    private $packageModel;

    /**
     * The search query.
     *
     * @var  string
     */
    private $query = '';

    /**
     * The requested documentation version.
     *
     * @var  string
     */
    private $version = '';

    /**
     * Instantiate the view.
     *
     * @param   PackageModel         $packageModel         The package model object.
     * @param   DocumentationSearch  $documentationSearch  The documentation search.
     * @param   Environment          $twig                 The Twig environment.
     */
    public function __construct(PackageModel $packageModel, DocumentationSearch $documentationSearch, Environment $twig)
    {
        parent::__construct($twig);
        $this->documentationSearch = $documentationSearch;
        $this->packageModel        = $packageModel;
    }

    /**
     * Method to render the view
     *
     * @return  string  The rendered view
     */
    public function render()
    {
        $packages = $this->packageModel->getSortedPackages();
        $versions = $this->documentationSearch->getVersions();
        $version  = $this->resolveVersion($versions);

        // Like the package list in the sidebar, only the stable packages are searched
        $searchedPackages = array_filter(
            $packages,
            static fn (\stdClass $package): bool => $package->stable && !$package->abandoned
        );

        $this->setData([
                'packages' => $packages,
                'query'    => $this->query,
                'results'  => $this->query !== '' ? $this->documentationSearch->search($version, $this->query, $searchedPackages) : [],
                'route'    => 'docs',
                'version'  => $version,
                'versions' => $versions,
            ]);
        return parent::render();
    }

    /**
     * Set the search query
     *
     * @param   string  $query    The search query
     * @param   string  $version  The documentation version to search
     *
     * @return  void
     */
    public function setQuery(string $query, string $version): void
    {
        $this->query   = $query;
        $this->version = $version;
    }

    /**
     * Get the version to search
     *
     * @param   string[]  $versions  The versions which can be searched.
     *
     * @return  string
     */
    private function resolveVersion(array $versions): string
    {
        if (\in_array($this->version, $versions, true)) {
            return $this->version;
        }

        if (\in_array(self::DEFAULT_VERSION, $versions, true) || $versions === []) {
            return self::DEFAULT_VERSION;
        }

        return end($versions);
    }
}
