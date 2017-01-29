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

/**
 * Controller handling the site's simple text pages
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 *
 * @since          1.0
 */
class PageController extends AbstractController
{
	/**
	 * The template renderer.
	 *
	 * @var    RendererInterface
	 * @since  1.0
	 */
	private $renderer;

	/**
	 * Constructor.
	 *
	 * @param   RendererInterface    $renderer  The template renderer.
	 * @param   Input                $input     The input object.
	 * @param   AbstractApplication  $app       The application object.
	 *
	 * @since   1.0
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
	 *
	 * @since   1.0
	 */
	public function execute() : bool
	{
		// Enable browser caching
		$this->getApplication()->allowCache(true);

		$this->getApplication()->setBody($this->renderer->render($this->getInput()->getString('view', '') . '.twig'));

		return true;
	}
}
