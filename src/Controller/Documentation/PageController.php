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
use Joomla\FrameworkWebsite\Helper\GitHubHelper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\View\Documentation\ErrorHtmlView;
use Joomla\FrameworkWebsite\View\Documentation\PageHtmlView;
use Joomla\Input\Input;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;

/**
 * Controller handling a package's documentation page
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class PageController extends AbstractController
{
    /**
     * The error view object.
     *
     * @var  ErrorHtmlView
     */
    private $errorView;

    /**
     * The GitHub helper
     *
     * @var  GitHubHelper
     */
    private $githubHelper;

    /**
     * The model object.
     *
     * @var  PackageModel
     */
    private $model;

    /**
     * The page view object.
     *
     * @var  PageHtmlView
     */
    private $pageView;

    /**
     * Constructor.
     *
     * @param   PackageModel         $model         The model object.
     * @param   ErrorHtmlView        $errorView     The error view object.
     * @param   PageHtmlView         $pageView      The page view object.
     * @param   GitHubHelper         $githubHelper  The GitHub helper.
     * @param   Input                $input         The input object.
     * @param   AbstractApplication  $app           The application object.
     */
    public function __construct(PackageModel $model, ErrorHtmlView $errorView, PageHtmlView $pageView, GitHubHelper $githubHelper, Input $input = null, AbstractApplication $app = null)
    {
        parent::__construct($input, $app);
        $this->errorView    = $errorView;
        $this->pageView     = $pageView;
        $this->githubHelper = $githubHelper;
        $this->model        = $model;
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
        $filename    = $this->getInput()->getString('filename');
        if (!$packageName || !$version) {
            throw new \RuntimeException('Missing required package or version parameters.', 404);
        }

        $package = $this->model->getPackage($packageName);
        switch ($version) {
            case '3.x':
                if (!$package->has_v3) {
                    $this->errorView->setError(sprintf('The %s package does not have a 3.x branch to document.', $package->display));
                    $this->getApplication()->setResponse(new HtmlResponse($this->errorView->render(), 404));
                } else {
                    $this->pageView->setActivePackage($package);
                    $this->pageView->setPageContent($this->githubHelper->renderDocsFile($version, $package, $filename));
                    $this->pageView->setSidebarContent($this->githubHelper->renderDocsFile($version, $package, 'index'));
                    $this->getApplication()->setResponse(new HtmlResponse($this->pageView->render()));
                }

                break;
            case '4.x':
                if (!$package->has_v4) {
                    $this->errorView->setError(sprintf('The %s package does not have a 4.x branch to document.', $package->display));
                    $this->getApplication()->setResponse(new HtmlResponse($this->errorView->render(), 404));
                } else {
                    $this->pageView->setActivePackage($package);
                    $this->pageView->setPageContent($this->githubHelper->renderDocsFile($version, $package, $filename));
                    $this->pageView->setSidebarContent($this->githubHelper->renderDocsFile($version, $package, 'index'));
                    $this->getApplication()->setResponse(new HtmlResponse($this->pageView->render()));
                }

                break;
            case 'latest':
                $this->getApplication()->setResponse(new RedirectResponse($this->getApplication()->get('uri.base.path') . "docs/4.x/{$package->package}/{$filename}"));

                break;
            default:
                throw new \RuntimeException(sprintf('Unsupported version `%s` for documentation.', $version), 404);
        }

        return true;
    }
}
