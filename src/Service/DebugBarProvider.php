<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use DebugBar\Bridge\MonologCollector;
use DebugBar\Bridge\Twig\TimeableTwigExtensionProfiler;
use DebugBar\Bridge\TwigProfileCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DebugBar;
use DebugBar\StandardDebugBar;
use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Application\Web\WebClient;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\Exception\DependencyResolutionException;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\FrameworkWebsite\Cache\Adapter\DebugAdapter;
use Joomla\FrameworkWebsite\Controller\DebugControllerResolver;
use Joomla\FrameworkWebsite\DebugBar\JoomlaHttpDriver;
use Joomla\FrameworkWebsite\DebugWebApplication;
use Joomla\FrameworkWebsite\Event\DebugDispatcher;
use Joomla\FrameworkWebsite\EventListener\DebugSubscriber;
use Joomla\FrameworkWebsite\Http\HttpFactory;
use Joomla\FrameworkWebsite\Router\DebugRouter;
use Joomla\Http\HttpFactory as BaseHttpFactory;
use Joomla\Input\Input;
use Joomla\Router\RouterInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

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
    public function register(Container $container): void
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
        $container->extend(CacheItemPoolInterface::class, [$this, 'getDecoratedCacheService']);
        $container->extend(ControllerResolverInterface::class, [$this, 'getDecoratedControllerResolverService']);
        $container->extend(DispatcherInterface::class, [$this, 'getDecoratedDispatcherService']);
        $container->extend(AbstractWebApplication::class, [$this, 'getDecoratedWebApplicationService']);
        $container->extend('application.router', [$this, 'getDecoratedRouterService']);
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
    public function getDebugBarService(Container $container): DebugBar
    {
        if (!class_exists(StandardDebugBar::class)) {
            throw new DependencyResolutionException(sprintf('The %s class is not loaded.', StandardDebugBar::class));
        }

        $debugBar = new StandardDebugBar();
        // Add collectors
        foreach ($container->getTagged('debug.collector') as $collector) {
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
    public function getDebugCollectorMonologService(Container $container): MonologCollector
    {
        $collector = new MonologCollector();
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
    public function getDebugCollectorPdoService(Container $container): PDOCollector
    {
        /** @var DatabaseInterface $db */
        $db = $container->get(DatabaseInterface::class);
        $db->connect();
        /** @var \PDO $pdo */
        $pdo = $db->getConnection();

        return new PDOCollector(new TraceablePDO($pdo));
    }

    /**
     * Get the `debug.collector.twig` service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  TwigProfileCollector
     */
    public function getDebugCollectorTwigService(Container $container): TwigProfileCollector
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
     * Get the decorated `cache` service
     *
     * @param   CacheItemPoolInterface  $cache      The original CacheItemPoolInterface service.
     * @param   Container               $container  The DI container.
     *
     * @return  CacheItemPoolInterface
     */
    public function getDecoratedCacheService(CacheItemPoolInterface $cache, Container $container): CacheItemPoolInterface
    {
        return new DebugAdapter($container->get('debug.bar'), $cache);
    }

    /**
     * Get the decorated controller resolver service
     *
     * @param   ControllerResolverInterface  $resolver   The original ControllerResolverInterface service.
     * @param   Container                    $container  The DI container.
     *
     * @return  ControllerResolverInterface
     */
    public function getDecoratedControllerResolverService(
        ControllerResolverInterface $resolver,
        Container $container
    ): ControllerResolverInterface {
        return new DebugControllerResolver($resolver, $container->get('debug.bar'));
    }

    /**
     * Get the decorated `dispatcher` service
     *
     * @param   DispatcherInterface  $dispatcher  The original DispatcherInterface service.
     * @param   Container            $container   The DI container.
     *
     * @return  DispatcherInterface
     */
    public function getDecoratedDispatcherService(
        DispatcherInterface $dispatcher,
        Container $container
    ): DispatcherInterface {
        $dispatcher = new DebugDispatcher($dispatcher, $container->get('debug.bar'));
        $dispatcher->addSubscriber($container->get('event.subscriber.debug'));

        return $dispatcher;
    }

    /**
     * Get the decorated `application.router` service
     *
     * @param   RouterInterface  $router     The original RouterInterface service.
     * @param   Container        $container  The DI container.
     *
     * @return  RouterInterface
     */
    public function getDecoratedRouterService(RouterInterface $router, Container $container): RouterInterface
    {
        return new DebugRouter($router, $container->get('debug.bar'));
    }

    /**
     * Get the decorated `twig.extension.profiler` service
     *
     * @param   \Twig_Extension_Profiler  $profiler   The original \Twig_Extension_Profiler service.
     * @param   Container                 $container  The DI container.
     *
     * @return  TimeableTwigExtensionProfiler
     */
    public function getDecoratedTwigExtensionProfilerService(
        \Twig_Extension_Profiler $profiler,
        Container $container
    ): TimeableTwigExtensionProfiler {
        return new TimeableTwigExtensionProfiler(
            $container->get('twig.profiler.profile'),
            $container->get('debug.bar')['time']
        );
    }

    /**
     * Get the decorated web application service
     *
     * @param   AbstractWebApplication  $application  The original AbstractWebApplication service.
     * @param   Container               $container    The DI container.
     *
     * @return  DebugWebApplication
     */
    public function getDecoratedWebApplicationService(
        AbstractWebApplication $application,
        Container $container
    ): DebugWebApplication {
        $application              = new DebugWebApplication(
            $container->get('debug.bar'),
            $container->get(ControllerResolverInterface::class),
            $container->get(RouterInterface::class),
            $container->get(Input::class),
            $container->get('config'),
            $container->get(WebClient::class)
        );
        $application->httpVersion = '2';
        // Inject extra services
        $application->setDispatcher($container->get(DispatcherInterface::class));
        $application->setLogger($container->get(LoggerInterface::class));

        return $application;
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
    private function tagDebugCollectors(Container $container): void
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
    private function tagTwigExtensions(Container $container): void
    {
        $container->tag('twig.extension', ['twig.extension.profiler']);
    }
}
