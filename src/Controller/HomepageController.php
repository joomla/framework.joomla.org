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
use Joomla\Input\Input;
use Joomla\Renderer\RendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * Controller handling the site's homepage
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class HomepageController extends AbstractController
{
    /**
     * The template renderer.
     *
     * @var  RendererInterface
     */
    private $renderer;

    /**
     * Constructor.
     *
     * @param   RendererInterface    $renderer  The template renderer.
     * @param   Input                $input     The input object.
     * @param   AbstractApplication  $app       The application object.
     */
    public function __construct(RendererInterface $renderer, Input $input = null, AbstractApplication $app = null)
    {
        parent::__construct($input, $app);
        $this->renderer = $renderer;
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
        $this->getApplication()->setResponse(new HtmlResponse($this->renderer->render('homepage.twig')));
        return true;
    }
}
