<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Documentation;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\View\TwigHtmlView;
use Twig\Environment;

/**
 * Documentation error HTML view class for the application
 */
class ErrorHtmlView extends TwigHtmlView
{
    /**
     * The error message for display.
     *
     * @var  string
     */
    private $error = '';
    /**
         * The package model object.
         *
         * @var  PackageModel
         */
    private $packageModel;
    /**
         * Instantiate the view.
         *
         * @param   PackageModel       $packageModel  The package model object.
         * @param   Environment        $twig          The Twig environment.
         */
    public function __construct(PackageModel $packageModel, Environment $twig)
    {
        parent::__construct($twig);
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
                'error'    => $this->error,
                'packages' => $this->packageModel->getSortedPackages(),
                'route'    => 'docs',
            ]);
        return parent::render();
    }

    /**
     * Set the error message for display
     *
     * @param   string  $error  The error message to display
     *
     * @return  void
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }
}
