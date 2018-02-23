<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use DebugBar\
{
	DebugBar, StandardDebugBar
};
use DebugBar\Bridge\{
	MonologCollector, TwigProfileCollector
};
use DebugBar\Bridge\Twig\TimeableTwigExtensionProfiler;
use DebugBar\DataCollector\PDO\{
	PDOCollector, TraceablePDO
};
use Joomla\Application\AbstractWebApplication;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\
{
	Container, Exception\DependencyResolutionException, ServiceProviderInterface
};
use Joomla\Event\DispatcherInterface;
use Joomla\FrameworkWebsite\DebugBar\JoomlaHttpDriver;
use Joomla\FrameworkWebsite\Event\DebugDispatcher;
use Joomla\FrameworkWebsite\EventListener\DebugSubscriber;
use Joomla\FrameworkWebsite\Http\HttpFactory;
use Joomla\Http\HttpFactory as BaseHttpFactory;

/**
 * Debug bar service provider
 */
class DebugBarProvider implements ServiceProviderInterface
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
		$container->alias(DebugBar::class, 'debug.bar')
			->alias(StandardDebugBar::class, 'debug.bar')
			->share('debug.bar', [$this, 'getDebugBarService'], true);

		$container->alias(MonologCollector::class, 'debug.collector.monolog')
			->share('debug.collector.monolog', [$this, 'getDebugCollectorMonologService'], true);

		$container->alias(PDOCollector::class, 'debug.collector.pdo')
			->share('debug.collector.pdo', [$this, 'getDebugCollectorPdoService'], true);

		$container->alias(TwigProfileCollector::class, 'debug.collector.twig')
			->share('debug.collector.twig', [$this, 'getDebugCollectorTwigService'], true);

		$container->alias(JoomlaHttpDriver::class, 'debug.http.driver')
			->share('debug.http.driver', [$this, 'getDebugHttpDriverService'], true);

		$container->alias(DebugSubscriber::class, 'event.subscriber.debug')
			->share('event.subscriber.debug', [$this, 'getEventSubscriberDebugService'], true);

		$container->extend('dispatcher', [$this, 'getDecoratedDispatcherService']);

		$container->extend('http.factory', [$this, 'getDecoratedHttpFactoryService']);

		$container->extend('twig.extension.profiler', [$this, 'getDecoratedTwigExtensionProfilerService']);

		$this->tagDebugCollectors($container);
		$this->tagTwigExtensions($container);
	}

	/**
	 * Get the `debug.bar` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugBar
	 */
	public function getDebugBarService(Container $container) : DebugBar
	{
		if (!class_exists(StandardDebugBar::class))
		{
			throw new DependencyResolutionException(sprintf('The %s class is not loaded.', StandardDebugBar::class));
		}

		$debugBar = new StandardDebugBar;

		// Add collectors
		foreach ($container->getTagged('debug.collector') as $collector)
		{
			$debugBar->addCollector($collector);
		}

		// Ensure the assets are dumped
		$renderer = $debugBar->getJavascriptRenderer();
		$renderer->dumpCssAssets(JPATH_ROOT . '/www/media/css/debugbar.css');
		$renderer->dumpJsAssets(JPATH_ROOT . '/www/media/js/debugbar.js');

		return $debugBar;
	}

	/**
	 * Get the `debug.collector.monolog` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MonologCollector
	 */
	public function getDebugCollectorMonologService(Container $container) : MonologCollector
	{
		$collector = new MonologCollector;
		$collector->addLogger($container->get('monolog.logger.application.web'));

		return $collector;
	}

	/**
	 * Get the `debug.collector.pdo` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PDOCollector
	 */
	public function getDebugCollectorPdoService(Container $container) : PDOCollector
	{
		/** @var DatabaseInterface $db */
		$db = $container->get(DatabaseInterface::class);
		$db->connect();

		return new PDOCollector(new TraceablePDO($db->getConnection()));
	}

	/**
	 * Get the `debug.collector.twig` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  TwigProfileCollector
	 */
	public function getDebugCollectorTwigService(Container $container) : TwigProfileCollector
	{
		return new TwigProfileCollector($container->get('twig.profiler.profile'), $container->get('twig.loader'));
	}

	/**
	 * Get the `debug.http.driver` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaHttpDriver
	 */
	public function getDebugHttpDriverService(Container $container): JoomlaHttpDriver
	{
		return new JoomlaHttpDriver($container->get(AbstractWebApplication::class));
	}

	/**
	 * Get the decorated `dispatcher` service
	 *
	 * @param   DispatcherInterface  $dispatcher  The original DispatcherInterface service.
	 * @param   Container            $container   The DI container.
	 *
	 * @return  DispatcherInterface
	 */
	public function getDecoratedDispatcherService(DispatcherInterface $dispatcher, Container $container): DispatcherInterface
	{
		$dispatcher = new DebugDispatcher($dispatcher, $container->get('debug.bar'));
		$dispatcher->addSubscriber($container->get('event.subscriber.debug'));

		return $dispatcher;
	}

	/**
	 * Get the decorated `http.factory` service
	 *
	 * @param   BaseHttpFactory  $httpFactory  The original HttpFactory service.
	 * @param   Container        $container    The DI container.
	 *
	 * @return  HttpFactory
	 */
	public function getDecoratedHttpFactoryService(BaseHttpFactory $httpFactory, Container $container): HttpFactory
	{
		return new HttpFactory($container->get('debug.bar'));
	}

	/**
	 * Get the decorated `twig.extension.profiler` service
	 *
	 * @param   \Twig_Extension_Profiler  $profiler   The original \Twig_Extension_Profiler service.
	 * @param   Container                 $container  The DI container.
	 *
	 * @return  TimeableTwigExtensionProfiler
	 */
	public function getDecoratedTwigExtensionProfilerService(\Twig_Extension_Profiler $profiler, Container $container): TimeableTwigExtensionProfiler
	{
		return new TimeableTwigExtensionProfiler($container->get('twig.profiler.profile'), $container->get('debug.bar')['time']);
	}

	/**
	 * Get the `event.subscriber.debug` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugSubscriber
	 */
	public function getEventSubscriberDebugService(Container $container): DebugSubscriber
	{
		return new DebugSubscriber($container->get('debug.bar'));
	}

	/**
	 * Tag services which are collectors for the debug bar
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	private function tagDebugCollectors(Container $container)
	{
		$container->tag('debug.collector', ['debug.collector.monolog', 'debug.collector.pdo', 'debug.collector.twig']);
	}

	/**
	 * Tag services which are Twig extensions
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	private function tagTwigExtensions(Container $container)
	{
		$container->tag('twig.extension', ['twig.extension.profiler']);
	}
}
