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
use Joomla\FrameworkWebsite\View\Documentation\SearchHtmlView;
use Joomla\Input\Input;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * Controller handling the search in the documentation
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class SearchController extends AbstractController
{
    /**
     * The maximum length of a search query
     *
     * @var  integer
     */
    private const MAX_QUERY_LENGTH = 200;

    /**
     * The view object.
     *
     * @var  SearchHtmlView
     */
    private $view;

    /**
     * Constructor.
     *
     * @param   SearchHtmlView       $view   The view object.
     * @param   Input                $input  The input object.
     * @param   AbstractApplication  $app    The application object.
     */
    public function __construct(SearchHtmlView $view, ?Input $input = null, ?AbstractApplication $app = null)
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
        $query   = mb_substr(trim($this->getInput()->getString('q', '')), 0, self::MAX_QUERY_LENGTH);
        $version = $this->getInput()->getString('version', '');

        $this->view->setQuery($query, $version);

        // Enable browser caching
        $this->getApplication()->allowCache(true);
        $this->getApplication()->setResponse(new HtmlResponse($this->view->render()));
        return true;
    }
}
