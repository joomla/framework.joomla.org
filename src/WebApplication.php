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
use Joomla\Router\Router;
use Psr\Link\EvolvableLinkProviderInterface;
use Symfony\Component\WebLink\HttpHeaderSerializer;
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
	 * Method to run the application routines
	 *
	 * @return  void
	 */
	protected function doExecute()
	{
		try
		{
			$route = $this->router->parseRoute($this->get('uri.route'), $this->input->getMethod());

			// Add variables to the input if not already set
			foreach ($route['vars'] as $key => $value)
			{
				$this->input->def($key, $value);
			}

			/** @var ControllerInterface $controller */
			$controller = $this->getContainer()->get($route['controller']);
			$controller->execute();
		}
		catch (\Throwable $throwable)
		{
			// Log the error for reference
			$this->getLogger()->error(
				sprintf('Uncaught Throwable of type %s caught.', get_class($throwable)),
				['exception' => $throwable]
			);

			if ($this->debugBar)
			{
				$this->debugBar['exceptions']->addThrowable($throwable);
			}

			$this->allowCache(false);

			switch ($this->mimeType)
			{
				case 'application/json' :
				case ($this->getResponse() instanceof JsonResponse) :
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
	 */
	public function getFormToken($forceNew = false)
	{
		return '';
	}

	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main application output data.
	 *
	 * @return  void
	 */
	protected function respond()
	{
		/** @var EvolvableLinkProviderInterface|null $linkProvider */
		$linkProvider = $this->input->getRaw('_links');

		if ($linkProvider && $linkProvider instanceof EvolvableLinkProviderInterface && $links = $linkProvider->getLinks())
		{
			$this->setHeader('Link', (new HttpHeaderSerializer)->serialize($links));
		}

		// Render the debug bar output if able
		if ($this->debugBar && !($this->mimeType === 'application/json' || $this->getResponse() instanceof JsonResponse))
		{
			$debugBarOutput = $this->debugBar->getJavascriptRenderer()->render();

			// Fetch the body
			$body = $this->getBody();

			// If for whatever reason we're missing the closing body tag, just append the scripts
			if (!stristr($body, '</body>'))
			{
				$body .= $debugBarOutput;
			}
			else
			{
				// Find the closing tag and put the scripts in
				$pos = strripos($body, '</body>');

				if ($pos !== false)
				{
					$body = substr_replace($body, $debugBarOutput . '</body>', $pos, strlen('</body>'));
				}
			}

			// Reset the body
			$this->setBody($body);
		}

		parent::respond();
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
