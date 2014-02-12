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
			$router = (new Router($this->input, $this))
				->setControllerPrefix('\\Joomla\\Status')
				->setDefaultController('\\Controller\\DefaultController');

			// Fetch the controller
			/* @type  \Joomla\Controller\AbstractController  $controller */
			$controller = $router->getController($this->get('uri.route'));

			$controller->execute();
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

			$this->setBody($exception->getMessage());
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
