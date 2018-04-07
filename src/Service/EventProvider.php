<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\DI\{
	Container,
	ServiceProviderInterface
};
use Joomla\Event\{
	Dispatcher,
	DispatcherInterface
};
use Joomla\FrameworkWebsite\EventListener\ErrorSubscriber;
use Joomla\Renderer\RendererInterface;
use Psr\Log\LoggerInterface;

/**
 * Event service provider
 */
class EventProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
		// This service cannot be protected as it is decorated when the debug bar is available
		$container->alias(DispatcherInterface::class, 'dispatcher')
			->alias(Dispatcher::class, 'dispatcher')
			->share('dispatcher', [$this, 'getDispatcherService']);

		$container->alias(ErrorSubscriber::class, 'event.subscriber.error')
			->share('event.subscriber.error', [$this, 'getEventSubscriberErrorService'], true)
			->tag('event.subscriber', ['event.subscriber.error']);
	}

	/**
	 * Get the `dispatcher` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DispatcherInterface
	 */
	public function getDispatcherService(Container $container): DispatcherInterface
	{
		$dispatcher = new Dispatcher;

		foreach ($container->getTagged('event.subscriber') as $subscriber)
		{
			$dispatcher->addSubscriber($subscriber);
		}

		return $dispatcher;
	}

	/**
	 * Get the `event.subscriber.error` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ErrorSubscriber
	 */
	public function getEventSubscriberErrorService(Container $container): ErrorSubscriber
	{
		$subscriber = new ErrorSubscriber($container->get(RendererInterface::class));
		$subscriber->setLogger($container->get(LoggerInterface::class));

		return $subscriber;
	}
}
