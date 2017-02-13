<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use Joomla\Application\AbstractWebApplication;
use Joomla\DI\{
	ContainerAwareInterface, ContainerAwareTrait
};
use Joomla\Renderer\RendererInterface;

/**
 * Web application class
 *
 * @since  1.0
 */
class WebApplication extends AbstractWebApplication implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Application router
	 *
	 * @var    ChainedRouter
	 * @since  1.0
	 */
	private $router;

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
			$this->router->getController($this->get('uri.route'))->execute();
		}
		catch (\Throwable $exception)
		{
			$this->setErrorHeaderResponse($exception);
			$this->setErrorOutput($exception);
		}
	}

	/**
	 * Set the body for error conditions
	 *
	 * @param   \Throwable  $exception  The Throwable object
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function setErrorOutput(\Throwable $exception)
	{
		switch ($this->mimeType)
		{
			case 'application/json' :
				$data = [
					'code'    => $exception->getCode(),
					'message' => $exception->getMessage(),
					'error'   => true
				];

				$body = json_encode($data);

				break;

			default :
				$body = $this->getContainer()->get(RendererInterface::class)->render('exception.twig', ['exception' => $exception]);

				break;
		}

		$this->setBody($body);
	}

	/**
	 * Set the HTTP Header response for error conditions
	 *
	 * @param   \Throwable  $exception  The Throwable object
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function setErrorHeaderResponse(\Throwable $exception)
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
	}

	/**
	 * Set the application's router
	 *
	 * @param   ChainedRouter  $router  Router object to set
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setRouter(ChainedRouter $router) : WebApplication
	{
		$this->router = $router;

		return $this;
	}
}
