<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller\Documentation;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\View\Documentation\ErrorHtmlView;
use Joomla\Input\Input;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;

/**
 * Controller handling redirecting an empty page request to the overview
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class RedirectController extends AbstractController
{
    /**
     * The error view object.
     *
     * @var  ErrorHtmlView
     */
    private $errorView;

    /**
     * The model object.
     *
     * @var  PackageModel
     */
    private $model;

    /**
     * Constructor.
     *
     * @param   PackageModel         $model      The model object.
     * @param   ErrorHtmlView        $errorView  The error view object.
     * @param   Input                $input      The input object.
     * @param   AbstractApplication  $app        The application object.
     */
    public function __construct(PackageModel $model, ErrorHtmlView $errorView, Input $input = null, AbstractApplication $app = null)
    {
        parent::__construct($input, $app);
        $this->errorView = $errorView;
        $this->model     = $model;
    }

    /**
     * Execute the controller.
     *
     * @return  boolean
     */
    public function execute(): bool
    {
        $packageName = $this->getInput()->getString('package');
        $version     = $this->getInput()->getString('version');
        if (!$packageName || !$version) {
            throw new \RuntimeException('Missing required package or version parameters.', 404);
        }

        $package = $this->model->getPackage($packageName);
        switch ($version) {
            case '1.x':
                if (!$package->has_v1) {
                    $this->errorView->setError(sprintf('The %s package does not have a 1.x branch to document.', $package->display));
                    $this->getApplication()->setResponse(new HtmlResponse($this->errorView->render(), 404));
                } else {
                    $this->getApplication()->setResponse(new RedirectResponse($this->getApplication()->get('uri.base.path') . "docs/1.x/{$package->package}/overview"));
                }

                break;
            case '2.x':
                if (!$package->has_v2) {
                    $this->errorView->setError(sprintf('The %s package does not have a 2.x branch to document.', $package->display));
                    $this->getApplication()->setResponse(new HtmlResponse($this->errorView->render(), 404));
                } else {
                    $this->getApplication()->setResponse(new RedirectResponse($this->getApplication()->get('uri.base.path') . "docs/$version/{$package->package}/overview"));
                }

                break;
            case 'latest':
                $this->getApplication()->setResponse(new RedirectResponse($this->getApplication()->get('uri.base.path') . "docs/2.x/{$package->package}/overview"));

                break;
            default:
                throw new \RuntimeException(sprintf('Unsupported version `%s` for documentation.', $version), 404);
        }

        return true;
    }
}
