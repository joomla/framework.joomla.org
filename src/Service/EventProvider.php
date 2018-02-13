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
use Joomla\FrameworkWebsite\EventListener\PreloadSubscriber;
use Joomla\FrameworkWebsite\Manager\PreloadManager;

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
		$container->alias(DispatcherInterface::class, 'dispatcher')
			->alias(Dispatcher::class, 'dispatcher')
			->share('dispatcher', [$this, 'getDispatcherService'], true);

		$container->alias(PreloadSubscriber::class, 'event.subscriber.preload')
			->share('event.subscriber.preload', [$this, 'getEventSubscriberPreloadService'], true);
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

		$dispatcher->addSubscriber($container->get('event.subscriber.preload'));

		return $dispatcher;
	}

	/**
	 * Get the `event.subscriber.preload` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PreloadSubscriber
	 */
	public function getEventSubscriberPreloadService(Container $container): PreloadSubscriber
	{
		return new PreloadSubscriber(
			$container->get(PreloadManager::class)
		);
	}
}
