<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\View\Status\StatusHtmlView;
use Joomla\Input\Input;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * Controller handling the site's package status listing
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class StatusController extends AbstractController
{
    /**
     * The view object.
     *
     * @var  StatusHtmlView
     */
    private $view;

    /**
     * Constructor.
     *
     * @param   StatusHtmlView       $view   The view object.
     * @param   Input                $input  The input object.
     * @param   AbstractApplication  $app    The application object.
     */
    public function __construct(StatusHtmlView $view, Input $input = null, AbstractApplication $app = null)
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
