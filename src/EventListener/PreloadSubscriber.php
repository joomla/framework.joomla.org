<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\EventListener;

use Joomla\Application\{
	AbstractWebApplication,
	ApplicationEvents
};
use Joomla\Application\Event\ApplicationEvent;
use Joomla\Event\SubscriberInterface;
use Joomla\FrameworkWebsite\Manager\PreloadManager;
use Psr\Link\EvolvableLinkProviderInterface;
use Symfony\Component\WebLink\HttpHeaderSerializer;

/**
 * Asset preloading event subscriber
 */
class PreloadSubscriber implements SubscriberInterface
{
	/**
	 * The preload manager.
	 *
	 * @var  PreloadManager
	 */
	private $preloadManager;

	/**
	 * Event subscriber constructor.
	 *
	 * @param   PreloadManager  $preloadManager  The preload manager
	 */
	public function __construct(PreloadManager $preloadManager)
	{
		$this->preloadManager = $preloadManager;
	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			ApplicationEvents::BEFORE_RESPOND => 'sendLinkHeader',
		];
	}

	/**
	 * Sends the link header for preloaded assets.
	 *
	 * @param   ApplicationEvent  $event  Event object
	 *
	 * @return  void
	 */
	public function sendLinkHeader(ApplicationEvent $event)
	{
		/** @var AbstractWebApplication $application */
		$application = $event->getApplication();

		$linkProvider = $this->preloadManager->getLinkProvider();

		if ($linkProvider instanceof EvolvableLinkProviderInterface && $links = $linkProvider->getLinks())
		{
			$application->setHeader('Link', (new HttpHeaderSerializer)->serialize($links));
		}
	}
}
