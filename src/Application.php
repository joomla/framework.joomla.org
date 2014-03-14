<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status;

use Joomla\Application\AbstractWebApplication;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\Router\Router;

use Joomla\Status\Model\DefaultModel;
use Joomla\Status\View\DefaultHtmlView;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Web application class
 *
 * @since  1.0
 */
final class Application extends AbstractWebApplication implements ContainerAwareInterface
{
	/**
	 * DI Container
	 *
	 * @var    Container
	 * @since  1.0
	 */
	private $container;

	/**
	 * Method to run the application routines
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		try
		{
			// Instantiate the router
			$router = (new Router($this->input))
				->setControllerPrefix('\\Joomla\\Status')
				->setDefaultController('\\Controller\\DefaultController')
				->addMap('/package/:package', '\\Controller\\PackageController');

			// Fetch the controller
			/* @type  \Joomla\Controller\AbstractController  $controller */
			$controller = $router->getController($this->get('uri.route'));

			// If the controller is ContainerAware, inject the DI container
			if ($controller instanceof ContainerAwareInterface)
			{
				$controller->setContainer($this->getContainer());
			}

			// Inject the application into the controller and execute it
			$controller->setApplication($this)->execute();
		}
		catch (\Exception $exception)
		{
			switch ($exception->getCode())
			{
				case 404 :
					$this->setHeader('HTTP/1.1 404 Not Found', 404, true);

					break;

				case 500 :
				default  :
					$this->setHeader('HTTP/1.1 500 Internal Server Error', 500, true);

					break;
			}

			// Render the message based on the format
			switch (strtolower($this->input->getWord('format', 'html')))
			{
				case 'json' :
					$data = [
						'code'    => $exception->getCode(),
						'message' => $exception->getMessage(),
						'error'   => true
					];

					$body = json_encode($data);

					break;

				case 'html' :
				default :
					// Build a default view object and render with the exception layout
					$view = new DefaultHtmlView($this, new DefaultModel($this->container->get('db')), [JPATH_TEMPLATES]);

					$view->setLayout('exception')
						->getRenderer()->set('exception', $exception);

					$body = $view->render();

					break;
			}

			$this->setBody($body);
		}
	}

	/**
	 * Get the DI container
	 *
	 * @return  Container
	 *
	 * @since   1.0
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Custom initialisation method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function initialise()
	{
		$logger = new Logger('Joomla-Framework-Status');
		$logger->pushHandler(new StreamHandler(JPATH_ROOT . '/logs/activity.log'));

		$this->setLogger($logger);

		// Set the MIME for the application based on format
		switch (strtolower($this->input->getWord('format', 'html')))
		{
			case 'json' :
				$this->mimeType = 'application/json';

				break;

			// Don't need to do anything for the default case
			default :
				break;
		}
	}

	/**
	 * Set the DI container
	 *
	 * @param   Container  $container  The DI container
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}
}
