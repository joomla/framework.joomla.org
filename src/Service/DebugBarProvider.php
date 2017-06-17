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
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\PDO\{
	PDOCollector, TraceablePDO
};
use Joomla\Database\DatabaseInterface;
use Joomla\DI\
{
	Container, Exception\DependencyResolutionException, ServiceProviderInterface
};
use Joomla\FrameworkWebsite\DebugBar\Twig\{
	TraceableTwigEnvironment, TwigCollector
};

/**
 * Debug bar service provider
 *
 * @since  1.0
 */
class DebugBarProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
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

		$container->alias(TwigCollector::class, 'debug.collector.twig')
			->share('debug.collector.twig', [$this, 'getDebugCollectorTwigService'], true);

		$container->extend(
			'twig.environment',
			function (\Twig_Environment $twig, Container $container) : TraceableTwigEnvironment
			{
				return new TraceableTwigEnvironment($twig);
			}
		);
	}

	/**
	 * Get the `debug.bar` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugBar
	 *
	 * @since   1.0
	 */
	public function getDebugBarService(Container $container) : DebugBar
	{
		if (!class_exists(StandardDebugBar::class))
		{
			throw new DependencyResolutionException(sprintf('The %s class is not loaded.', StandardDebugBar::class));
		}

		$debugBar = new StandardDebugBar;

		// Add collectors
		$debugBar->addCollector($container->get('debug.collector.monolog'));
		$debugBar->addCollector($container->get('debug.collector.pdo'));
		$debugBar->addCollector($container->get('debug.collector.twig'));

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
	 *
	 * @since   1.0
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
	 *
	 * @since   1.0
	 */
	public function getDebugCollectorPdoService(Container $container) : PDOCollector
	{
		/** @var DatabaseInterface $db */
		$db = $container->get('db');
		$db->connect();

		return new PDOCollector(new TraceablePDO($db->getConnection()));
	}

	/**
	 * Get the `debug.collector.pdo` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  TwigCollector
	 *
	 * @since   1.0
	 */
	public function getDebugCollectorTwigService(Container $container) : TwigCollector
	{
		return new TwigCollector($container->get('twig.environment'));
	}
}
