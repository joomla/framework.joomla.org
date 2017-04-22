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
use Zend\Diactoros\Response\{
	HtmlResponse, JsonResponse
};

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
		catch (\Throwable $throwable)
		{
			$this->allowCache(false);

			// TODO - The error handler will need to be refactored to fully account for being aware of route formats and Response objects
			switch ($this->mimeType)
			{
				case 'application/json' :
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

				case 500 :
				default  :
					$response = $response->withStatus(500);

					break;
			}

			$this->setResponse($response);
		}
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 *
	 * @param   boolean  $forceNew  If true, force a new token to be created
	 *
	 * @return  string  Hashed var name
	 *
	 * @since   1.0
	 */
	public function getFormToken($forceNew = false)
	{
		return '';
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
