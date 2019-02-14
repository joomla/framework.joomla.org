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
use Joomla\Router\Exception\RouteNotFoundException;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Controller handling the site's simple text pages
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class PageController extends AbstractController
{
	/**
	 * Container defining layouts which shouldn't be routable
	 *
	 * @const  array
	 */
	private const EXCLUDED_LAYOUTS = ['contributors', 'exception', 'homepage', 'index', 'package', 'status'];

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

		$view   = $this->getInput()->getString('view', '');
		$layout = "$view.twig";

		// Since this is a catch-all route, if the layout doesn't exist, or is an excluded layout, treat this as a 404
		if (!$this->renderer->pathExists($layout) || \in_array($view, self::EXCLUDED_LAYOUTS, true))
		{
			throw new RouteNotFoundException(sprintf('Unable to handle request for route `%s`.', $this->getApplication()->get('uri.route')), 404);
		}

		$this->getApplication()->setResponse(new HtmlResponse($this->renderer->render($layout)));

		return true;
	}
}
