<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Status;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Model\ReleaseModel;
use Joomla\Renderer\RendererInterface;
use Joomla\View\HtmlView;

/**
 * Status dashboard HTML view class for the application
 */
class StatusHtmlView extends HtmlView
{
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
     * @param   PackageModel       $packageModel  The package model object.
     * @param   ReleaseModel       $releaseModel  The release model object.
     * @param   RendererInterface  $renderer      The renderer object.
     */
    public function __construct(PackageModel $packageModel, ReleaseModel $releaseModel, RendererInterface $renderer)
    {
        parent::__construct($renderer);
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
        $this->setData([
                'releases'  => $this->releaseModel->getLatestReleases($this->packageModel->getActivePackages()),
            ]);
        return parent::render();
    }
}
