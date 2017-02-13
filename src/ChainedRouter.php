<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use Joomla\Controller\ControllerInterface;
use Joomla\Router\Router;

/**
 * Router supporting chained route handling for multiple routers
 *
 * @since  1.0
 */
class ChainedRouter
{
	/**
	 * Container holding the routers
	 *
	 * @var    Router[]
	 * @since  1.0
	 */
	private $routers = [];

	/**
	 * Constructor
	 *
	 * @param   Router[]  $routers  Array of routers to include
	 *
	 * @since   1.0
	 */
	public function __construct(array $routers = [])
	{
		foreach ($routers as $router)
		{
			$this->addRouter($router);
		}
	}

	/**
	 * Add a router to the chain
	 *
	 * @param   Router  $router  The router to add
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addRouter(Router $router)
	{
		$this->routers[] = $router;
	}

	/**
	 * Find and execute the appropriate controller based on a given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  ControllerInterface
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getController($route)
	{
		foreach ($this->routers as $router)
		{
			try
			{
				return $router->getController($route);
			}
			catch (\Exception $exception)
			{
				// Move on to the next router
			}
		}

		throw new \RuntimeException(sprintf('Unable to handle request for route `%s`.', $route), 404, $exception ?? null);
	}
}
