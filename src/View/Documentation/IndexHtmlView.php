<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Documentation;

use Joomla\FrameworkWebsite\Helper\DocumentationHelper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Model\ReleaseModel;
use Joomla\FrameworkWebsite\View\TwigHtmlView;
use Twig\Environment;

/**
 * Documentation index HTML view class for the application
 */
class IndexHtmlView extends TwigHtmlView
{
    /**
     * The documentation helper.
     *
     * @var  DocumentationHelper
     */
    private $documentationHelper;

    /**
     * The package model object.
     *
     * @var  PackageModel
     */
    private $packageModel;

    /**
     * The release model object.
     *
     * @var  ReleaseModel
     */
    private $releaseModel;

    /**
     * Instantiate the view.
     *
     * @param   PackageModel         $packageModel         The package model object.
     * @param   ReleaseModel         $releaseModel         The release model object.
     * @param   DocumentationHelper  $documentationHelper  The documentation helper.
     * @param   Environment          $twig                 The Twig environment.
     */
    public function __construct(PackageModel $packageModel, ReleaseModel $releaseModel, DocumentationHelper $documentationHelper, Environment $twig)
    {
        parent::__construct($twig);
        $this->documentationHelper = $documentationHelper;
        $this->packageModel        = $packageModel;
        $this->releaseModel        = $releaseModel;
    }

    /**
     * Method to render the view
     *
     * @return  string  The rendered view
     */
    public function render()
    {
        $packages = $this->packageModel->getSortedPackages();

        $this->setData([
                'documentedPackages' => $this->getDocumentedPackages($packages),
                'packages'           => $packages,
                'route'              => 'docs',
            ]);
        return parent::render();
    }

    /**
     * Get the packages listed on the index, with their summary and latest release
     *
     * Like the package list in the sidebar, only stable packages are listed. Packages without documentation for the version the `latest` routes point to are
     * left out, the links to their documentation would fail.
     *
     * @param   \stdClass[]  $packages  The packages, sorted by name.
     *
     * @return  \stdClass[]
     */
    private function getDocumentedPackages(array $packages): array
    {
        $packages = array_filter(
            $packages,
            fn (\stdClass $package): bool => $package->stable && !$package->abandoned && $this->documentationHelper->hasDocumentation($package)
        );

        $reports = [];

        foreach ($this->releaseModel->getLatestReleases($packages) as $report) {
            $reports[$report->package->id] = $report;
        }

        $entries = [];

        foreach ($packages as $package) {
            $entries[] = (object) [
                'package'       => $package,
                'summary'       => $this->documentationHelper->getPackageSummary($package),
                'latestRelease' => $this->getNewestRelease($reports[$package->id] ?? null),
            ];
        }

        return $entries;
    }

    /**
     * Get the release of the highest major version from a package's release report
     *
     * @param   \stdClass|null  $report  The report from ReleaseModel::getLatestReleases(), with a `vN` property per major version.
     *
     * @return  \stdClass|null
     */
    private function getNewestRelease(?\stdClass $report): ?\stdClass
    {
        $newest = null;
        $major  = -1;

        foreach (get_object_vars($report ?? new \stdClass()) as $key => $release) {
            if (preg_match('/^v(\d+)$/', $key, $matches) && (int) $matches[1] > $major) {
                $major  = (int) $matches[1];
                $newest = $release;
            }
        }

        return $newest;
    }
}
