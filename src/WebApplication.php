<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use DebugBar\DebugBar;
use Joomla\Application\AbstractWebApplication;
use Joomla\Controller\ControllerInterface;
use Joomla\DI\{
	ContainerAwareInterface, ContainerAwareTrait
};
use Joomla\Renderer\RendererInterface;
use Joomla\Router\Exception\{
	MethodNotAllowedException, RouteNotFoundException
};
use Joomla\Router\Router;
use Zend\Diactoros\Response\{
	HtmlResponse, JsonResponse
};

/**
 * Web application class
 */
class WebApplication extends AbstractWebApplication implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Application debug bar
	 *
	 * @var  DebugBar
	 */
	private $debugBar;

	/**
	 * Application router
	 *
	 * @var  Router
	 */
	private $router;

	/**
	 * Checks the accept encoding of the browser and compresses the data before sending it to the client if possible.
	 *
	 * @return  void
	 */
	protected function compress()
	{
		if (!$this->get('debug', false))
		{
			parent::compress();
		}
	}

	/**
	 * Method to run the application routines
	 *
	 * @return  void
	 */
	protected function doExecute()
	{
		try
		{
			if ($this->debugBar)
			{
				/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
				$collector = $this->debugBar['time'];

				$collector->startMeasure('routing');
			}

			$route = $this->router->parseRoute($this->get('uri.route'), $this->input->getMethod());

			// Add variables to the input if not already set
			foreach ($route['vars'] as $key => $value)
			{
				$this->input->def($key, $value);
			}

			if ($this->debugBar)
			{
				/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
				$collector = $this->debugBar['time'];

				$collector->stopMeasure('routing');
			}

			if ($this->debugBar)
			{
				/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
				$collector = $this->debugBar['time'];

				$collector->startMeasure('controller');
			}

			/** @var ControllerInterface $controller */
			$controller = $this->getContainer()->get($route['controller']);
			$controller->execute();

			if ($this->debugBar)
			{
				/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
				$collector = $this->debugBar['time'];

				$collector->stopMeasure('controller');
			}
		}
		catch (MethodNotAllowedException $exception)
		{
			// Log the error for reference
			$this->getLogger()->error(
				sprintf('Route `%s` not supported by method `%s`', $this->get('uri.route'), $this->input->getMethod()),
				['exception' => $exception]
			);

			$this->handleThrowable($exception);

			$this->setHeader('Allow', implode(', ', $exception->getAllowedMethods()));
		}
		catch (RouteNotFoundException $exception)
		{
			// Log the error for reference
			$this->getLogger()->error(
				sprintf('Route `%s` not found', $this->get('uri.route')),
				['exception' => $exception]
			);

			$this->handleThrowable($exception);
		}
		catch (\Throwable $throwable)
		{
			// Log the error for reference
			$this->getLogger()->error(
				sprintf('Uncaught Throwable of type %s caught.', get_class($throwable)),
				['exception' => $throwable]
			);

			$this->handleThrowable($throwable);
		}
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 *
	 * @param   boolean  $forceNew  If true, force a new token to be created
	 *
	 * @return  string  Hashed var name
	 */
	public function getFormToken($forceNew = false)
	{
		return '';
	}

	/**
	 * Handle a Throwable
	 *
	 * @param   \Throwable  $throwable  The Throwable to handle
	 *
	 * @return  void
	 */
	private function handleThrowable(\Throwable $throwable)
	{
		if ($this->debugBar)
		{
			/** @var \DebugBar\DataCollector\ExceptionsCollector $collector */
			$collector = $this->debugBar['exceptions'];

			$collector->addThrowable($throwable);

			/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
			$collector = $this->debugBar['time'];

			if ($collector->hasStartedMeasure('routing'))
			{
				$collector->stopMeasure('routing');
			}

			if ($collector->hasStartedMeasure('controller'))
			{
				$collector->stopMeasure('controller');
			}
		}

		$this->allowCache(false);

		switch ($this->input->getString('_format', 'html'))
		{
			case 'json' :
				$data = [
					'code'    => $throwable->getCode(),
					'message' => $throwable->getMessage(),
					'error'   => true
				];

				$response = new JsonResponse($data);

				break;

			default :
				$response = new HtmlResponse(
					$this->getContainer()->get(RendererInterface::class)->render('exception.twig', ['exception' => $throwable])
				);

				break;
		}

		switch ($throwable->getCode())
		{
			case 404 :
				$response = $response->withStatus(404);

				break;

			case 405 :
				$response = $response->withStatus(405);

				break;

			case 500 :
			default  :
				$response = $response->withStatus(500);

				break;
		}

		$this->setResponse($response);
	}

	/**
	 * Set the application's debug bar
	 *
	 * @param   DebugBar  $debugBar  DebugBar object to set
	 *
	 * @return  $this
	 */
	public function setDebugBar(DebugBar $debugBar) : WebApplication
	{
		$this->debugBar = $debugBar;

		return $this;
	}

	/**
	 * Set the application's router
	 *
	 * @param   Router  $router  Router object to set
	 *
	 * @return  $this
	 */
	public function setRouter(Router $router) : WebApplication
	{
		$this->router = $router;

		return $this;
	}
}
