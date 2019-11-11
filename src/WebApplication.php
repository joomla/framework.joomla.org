<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Application\Web\WebClient;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Web application class
 */
class WebApplication extends AbstractWebApplication
{
	/**
	 * The application's controller resolver.
	 *
	 * @var  ControllerResolverInterface
	 */
	protected $controllerResolver;

	/**
	 * The application's router.
	 *
	 * @var  RouterInterface
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
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
		ControllerResolverInterface $controllerResolver,
		RouterInterface $router,
		Input $input = null,
		Registry $config = null,
		WebClient $client = null,
		ResponseInterface $response = null
	)
	{
		$this->controllerResolver = $controllerResolver;
		$this->router             = $router;

		// Call the constructor as late as possible (it runs `initialise`).
		parent::__construct($input, $config, $client, $response);
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

		\call_user_func($this->controllerResolver->resolve($route));
	}
}
