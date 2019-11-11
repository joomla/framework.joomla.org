<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use DebugBar\DebugBar;
use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Application\Web\WebClient;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Debug web application class
 */
class DebugWebApplication extends WebApplication
{
	/**
	 * The application's debug bar.
	 *
	 * @var  DebugBar
	 */
	private $debugBar;

	/**
	 * Class constructor.
	 *
	 * @param   DebugBar                     $debugBar            The application's debug bar
	 * @param   ControllerResolverInterface  $controllerResolver  The application's controller resolver
	 * @param   RouterInterface              $router              The application's router
	 * @param   Input                        $input               An optional argument to provide dependency injection for the application's
	 *                                                            input object.
	 * @param   Registry                     $config              An optional argument to provide dependency injection for the application's
	 *                                                            config object.
	 * @param   WebClient                    $client              An optional argument to provide dependency injection for the application's
	 *                                                            client object.
	 * @param   ResponseInterface            $response            An optional argument to provide dependency injection for the application's
	 *                                                            response object.
	 */
	public function __construct(
		DebugBar $debugBar,
		ControllerResolverInterface $controllerResolver,
		RouterInterface $router,
		Input $input = null,
		Registry $config = null,
		WebClient $client = null,
		ResponseInterface $response = null
	)
	{
		$this->debugBar = $debugBar;

		// Call the constructor as late as possible (it runs `initialise`).
		parent::__construct($controllerResolver, $router, $input, $config, $client, $response);
	}

	/**
	 * Method to run the application routines.
	 *
	 * @return  void
	 */
	protected function doExecute(): void
	{
		$route = $this->router->parseRoute($this->get('uri.route'), $this->input->getMethod());

		// Add variables to the input if not already set
		foreach ($route->getRouteVariables() as $key => $value)
		{
			$this->input->def($key, $value);
		}

		$controller = $this->controllerResolver->resolve($route);

		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];
		$label     = 'controller';

		$collector->startMeasure($label);

		try
		{
			$controller();
		}
		finally
		{
			$collector->stopMeasure($label);
		}
	}
}
