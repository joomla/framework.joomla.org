<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View;

use Joomla\View\AbstractView;
use Twig\Environment;

/**
 * Base class for the HTML views, rendering a Twig template with the view's data
 */
abstract class TwigHtmlView extends AbstractView
{
    /**
     * The name of the template to render
     *
     * @var  string
     */
    protected $layout = 'default';

    /**
     * The Twig environment.
     *
     * @var  Environment
     */
    private $twig;

    /**
     * Constructor.
     *
     * @param   Environment  $twig  The Twig environment.
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Get the name of the template to render
     *
     * @return  string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Method to render the view
     *
     * @return  string  The rendered view
     */
    public function render()
    {
        return $this->twig->render($this->getLayout(), $this->getData());
    }

    /**
     * Set the name of the template to render
     *
     * @param   string  $layout  The template name.
     *
     * @return  $this
     */
    public function setLayout(string $layout)
    {
        $this->layout = $layout;

        return $this;
    }
}
