<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Documentation;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Renderer\RendererInterface;
use Joomla\View\HtmlView;
use Joomla\FrameworkWebsite\Helper\VersionHelper;

/**
 * Documentation page HTML view class for the application
 */
class PageHtmlView extends HtmlView
{
    /**
     * The page contents for display.
     *
     * @var  string
     */
    private $contents = '';
    /**
         * The active package.
         *
         * @var  \stdClass
         */
    private $package;
    /**
         * The package model object.
         *
         * @var  PackageModel
         */
    private $packageModel;
    /**
         * The sidebar contents for display.
         *
         * @var  string
         */
    private $sidebarContents = '';
    /**
     * The documentation version of the page.
     *
     * @var  string
     */
    private $version = '';
    /**
     * The page, relative to the package's documentation root and without the `.md` extension.
     *
     * @var  string
     */
    private $page = '';
    /**
     * The version helper.
     *
     * @var  VersionHelper|null
     */
    private $versionHelper;
    /**
         * Instantiate the view.
         *
         * @param   PackageModel       $packageModel  The package model object.
         * @param   RendererInterface  $renderer      The renderer object.
         */
    public function __construct(PackageModel $packageModel, RendererInterface $renderer)
    {
        parent::__construct($renderer);
        $this->packageModel = $packageModel;
    }

    /**
     * Method to render the view
     *
     * @return  string  The rendered view
     */
    public function render()
    {
        $this->setData([
                'activePackage'   => $this->package,
                'contents'        => $this->contents,
                'packages'        => $this->packageModel->getSortedPackages(),
                'sidebarContents' => $this->sidebarContents,
                'route'           => 'docs',
            ]);
        $this->addData('versionInfo', $this->getVersionInfo());
        return parent::render();
    }

    /**
     * Set the active package
     *
     * @param   \stdClass  $package  The active package for the page
     *
     * @return  void
     */
    public function setActivePackage(\stdClass $package): void
    {
        $this->package = $package;
    }

    /**
     * Set the content for display
     *
     * @param   string  $contents  The content to display
     *
     * @return  void
     */
    public function setPageContent(string $contents): void
    {
        $this->contents = $contents;
    }

    /**
     * Set the sidebar content for display
     *
     * @param   string  $contents  The content to display
     *
     * @return  void
     */
    public function setSidebarContent(string $contents): void
    {
        $this->sidebarContents = $contents;
    }

    /**
     * Set the documentation version and the page which is displayed
     *
     * @param   string  $version  The documentation version
     * @param   string  $page     The page, relative to the package's documentation root and without the `.md` extension
     *
     * @return  void
     */
    public function setPage(string $version, string $page): void
    {
        $this->version = $version;
        $this->page    = $page;
    }

    /**
     * Set the version helper
     *
     * @param   VersionHelper  $versionHelper  The version helper
     *
     * @return  void
     */
    public function setVersionHelper(VersionHelper $versionHelper): void
    {
        $this->versionHelper = $versionHelper;
    }

    /**
     * Get the information about the documented versions for the version switcher, the development notice and the edit link
     *
     * @return  \stdClass|null  Null if the version or the version helper is not set
     */
    private function getVersionInfo(): ?\stdClass
    {
        if ($this->versionHelper === null || $this->version === '') {
            return null;
        }

        $versions      = [];
        $stableVersion = null;
        $stablePage    = 'overview';

        foreach ($this->versionHelper->getDocumentedVersions($this->package) as $version) {
            $stable = $this->versionHelper->isStable($version);

            // Link to the same page in the other version, or to the overview if that version doesn't have it
            $versions[] = (object) [
                'version' => $version,
                'stable'  => $stable,
                'current' => $version === $this->version,
                'page'    => $this->versionHelper->hasPage($version, $this->package, $this->page) ? $this->page : 'overview',
            ];

            if ($stable) {
                $stableVersion = $version;
                $stablePage    = end($versions)->page;
            }
        }

        return (object) [
            'current'       => $this->version,
            'stable'        => $this->versionHelper->isStable($this->version),
            'stableVersion' => $stableVersion,
            'stablePage'    => $stablePage,
            'versions'      => $versions,
            'editUrl'       => sprintf(
                'https://github.com/joomla-framework/%1$s/edit/%2$s/docs/%3$s.md',
                $this->package->repo,
                $this->versionHelper->getBranch($this->version),
                $this->page
            ),
        ];
    }
}
