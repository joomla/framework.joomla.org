<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Package;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Model\ReleaseModel;
use Joomla\FrameworkWebsite\View\TwigHtmlView;
use Twig\Environment;

/**
 * Package HTML view class for the application
 */
class PackageHtmlView extends TwigHtmlView
{
    /**
     * The active package
     *
     * @var  string
     */
    private $package = '';

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
     * @param   PackageModel       $packageModel     The package model object.
     * @param   ReleaseModel       $releaseModel     The release model object.
     * @param   Environment        $twig             The Twig environment.
     */
    public function __construct(PackageModel $packageModel, ReleaseModel $releaseModel, Environment $twig)
    {
        parent::__construct($twig);
        $this->packageModel = $packageModel;
        $this->releaseModel = $releaseModel;
    }

    /**
     * Method to render the view
     *
     * @return  string  The rendered view
     */
    public function render()
    {
        $package = $this->packageModel->getPackage($this->package);
        $this->setData([
                'releases' => $this->releaseModel->getPackageHistory($package),
                'package'  => $package,
                'route'    => 'status',
            ]);
        return parent::render();
    }

    /**
     * Set the active package
     *
     * @param   string  $package  The active package name
     *
     * @return  void
     */
    public function setPackage(string $package): void
    {
        $this->package = $package;
    }
}
