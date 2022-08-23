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
use Joomla\FrameworkWebsite\View\Documentation\IndexHtmlView;
use Joomla\Input\Input;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Controller handling the site's documentation index
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class IndexController extends AbstractController
{
    /**
     * The view object.
     *
     * @var  IndexHtmlView
     */
    private $view;
/**
     * Constructor.
     *
     * @param   IndexHtmlView        $view   The view object.
     * @param   Input                $input  The input object.
     * @param   AbstractApplication  $app    The application object.
     */
    public function __construct(IndexHtmlView $view, Input $input = null, AbstractApplication $app = null)
    {
        parent::__construct($input, $app);
        $this->view = $view;
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
        $this->getApplication()->setResponse(new HtmlResponse($this->view->render()));
        return true;
    }
}
