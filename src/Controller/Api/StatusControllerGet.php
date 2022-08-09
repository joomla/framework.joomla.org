<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller\Api;

use Joomla\Application\AbstractApplication;
use Joomla\FrameworkWebsite\Controller\AnalyticsController;
use Joomla\FrameworkWebsite\View\Status\StatusJsonView;
use Joomla\Input\Input;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * API Controller handling the site's package status listing
 */
class StatusControllerGet extends AnalyticsController
{
    /**
     * The view object.
     *
     * @var  StatusJsonView
     */
    private $view;
/**
     * Constructor.
     *
     * @param   StatusJsonView       $view       The view object.
     * @param   Analytics            $analytics  Analytics object.
     * @param   Input                $input      The input object.
     * @param   AbstractApplication  $app        The application object.
     */
    public function __construct(StatusJsonView $view, Analytics $analytics, Input $input = null, AbstractApplication $app = null)
    {
        parent::__construct($analytics, $input, $app);
        $this->view = $view;
    }

    /**
     * Execute the controller.
     *
     * @return  boolean
     */
    public function execute(): bool
    {
        $this->sendAnalytics();
// Disable browser caching
        $this->getApplication()->allowCache(false);
// This is a JSON response
        $this->getApplication()->mimeType = 'application/json';
        $this->getApplication()->setBody($this->view->render());
        return true;
    }
}
