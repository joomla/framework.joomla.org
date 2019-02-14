<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\EventListener;

use Joomla\Application\ApplicationEvents;
use Joomla\Application\Event\ApplicationErrorEvent;
use Joomla\Event\SubscriberInterface;
use Joomla\FrameworkWebsite\WebApplication;
use Joomla\Renderer\RendererInterface;
use Joomla\Router\Exception\MethodNotAllowedException;
use Joomla\Router\Exception\RouteNotFoundException;
use Joomla\SymfonyEventDispatcherBridge\Symfony\Event;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Error handling event subscriber
 */
class ErrorSubscriber implements SubscriberInterface, EventSubscriberInterface, LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * Layout renderer
	 *
	 * @var  RendererInterface
	 */
	private $renderer;

	/**
	 * Event subscriber constructor.
	 *
	 * @param   RendererInterface  $renderer  Layout renderer
	 */
	public function __construct(RendererInterface $renderer)
	{
		$this->renderer = $renderer;
	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			ApplicationEvents::ERROR => 'handleWebError',
			ConsoleEvents::ERROR     => 'handleConsoleError',
		];
	}

	/**
	 * Handle console application errors.
	 *
	 * @param   Event  $event  Event object
	 *
	 * @return  void
	 */
	public function handleConsoleError(Event $event): void
	{
		/** @var ConsoleErrorEvent $consoleErrorEvent */
		$consoleErrorEvent = $event->getEvent();

		$this->logError($consoleErrorEvent->getError());
	}

	/**
	 * Handle web application errors.
	 *
	 * @param   ApplicationErrorEvent  $event  Event object
	 *
	 * @return  void
	 */
	public function handleWebError(ApplicationErrorEvent $event): void
	{
		/** @var WebApplication $app */
		$app = $event->getApplication();

		switch (true)
		{
			case $event->getError() instanceof MethodNotAllowedException :
				// Log the error for reference
				$this->logger->error(
					sprintf('Route `%s` not supported by method `%s`', $app->get('uri.route'), $app->input->getMethod()),
					['exception' => $event->getError()]
				);

				$this->prepareResponse($event);

				$app->setHeader('Allow', implode(', ', $event->getError()->getAllowedMethods()));

				break;

			case $event->getError() instanceof RouteNotFoundException :
				// Log the error for reference
				$this->logger->error(
					sprintf('Route `%s` not found', $app->get('uri.route')),
					['exception' => $event->getError()]
				);

				$this->prepareResponse($event);

				break;

			default:
				$this->logError($event->getError());

				$this->prepareResponse($event);

				break;
		}
	}

	/**
	 * Log the error.
	 *
	 * @param   \Throwable  $throwable  The error being processed
	 *
	 * @return  void
	 */
	private function logError(\Throwable $throwable): void
	{
		$this->logger->error(
			sprintf('Uncaught Throwable of type %s caught.', \get_class($throwable)),
			['exception' => $throwable]
		);
	}

	/**
	 * Prepare the response for the event
	 *
	 * @param   ApplicationErrorEvent  $event  Event object
	 *
	 * @return  void
	 */
	private function prepareResponse(ApplicationErrorEvent $event): void
	{
		/** @var WebApplication $app */
		$app = $event->getApplication();

		$app->allowCache(false);

		switch (true)
		{
			case $app->mimeType === 'application/json' :
			case $app->getResponse() instanceof JsonResponse :
				$data = [
					'code'    => $event->getError()->getCode(),
					'message' => $event->getError()->getMessage(),
					'error'   => true,
				];

				$response = new JsonResponse($data);

				break;

			default :
				$response = new HtmlResponse(
					$this->renderer->render('exception.twig', ['exception' => $event->getError()])
				);

				break;
		}

		switch ($event->getError()->getCode())
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

		$app->setResponse($response);
	}
}
