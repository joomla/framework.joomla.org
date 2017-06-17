<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\Application\AbstractApplication;
use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Joomla\FrameworkWebsite\Renderer\{
	ApplicationContext, FrameworkExtension, FrameworkTwigRuntime
};
use Joomla\Renderer\{
	RendererInterface, TwigRenderer
};
use Symfony\Component\Asset\{
	Packages, PathPackage
};
use Symfony\Component\Asset\VersionStrategy\{
	EmptyVersionStrategy, StaticVersionStrategy
};
use Twig\Cache\{
	FilesystemCache, NullCache
};
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\ContainerRuntimeLoader;

/**
 * Templating service provider
 *
 * @since  1.0
 */
class TemplatingProvider implements ServiceProviderInterface
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
		$container->alias(RendererInterface::class, 'renderer')
			->alias(TwigRenderer::class, 'renderer')
			->share('renderer', [$this, 'getRendererService'], true);

		$container->share(FrameworkTwigRuntime::class, [$this, 'getFrameworkTwigRuntimeClassService'], true);

		$container->share(Packages::class, [$this, 'getPackagesClassService'], true);
	}

	/**
	 * Get the FrameworkTwigRuntime class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FrameworkTwigRuntime
	 *
	 * @since   1.0
	 */
	public function getFrameworkTwigRuntimeClassService(Container $container) : FrameworkTwigRuntime
	{
		return new FrameworkTwigRuntime($container->get(AbstractApplication::class), $container->get(Packages::class));
	}

	/**
	 * Get the Packages class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FrameworkTwigRuntime
	 *
	 * @since   1.0
	 */
	public function getPackagesClassService(Container $container) : Packages
	{
		$version = file_exists(JPATH_ROOT . '/cache/deployed.txt') ? trim(file_get_contents(JPATH_ROOT . '/cache/deployed.txt')) : md5(get_class($this));
		$context = new ApplicationContext($container->get(AbstractApplication::class));

		return new Packages(
			new PathPackage('media', new StaticVersionStrategy($version), $context),
			[
				'img' => new PathPackage('media', new EmptyVersionStrategy, $context)
			]
		);
	}

	/**
	 * Get the `renderer` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RendererInterface
	 *
	 * @since   1.0
	 */
	public function getRendererService(Container $container) : RendererInterface
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		// Pull down the renderer config
		$cacheEnabled = $config->get('template.cache.enabled', false);
		$cachePath    = $config->get('template.cache.path', '');
		$debug        = $config->get('template.debug', false);

		// Instantiate the Twig environment
		$environment = new Environment(new FilesystemLoader([JPATH_TEMPLATES]), ['debug' => $debug]);

		// Add a global tracking the debug state
		$environment->addGlobal('fwDebug', $debug);

		// Set up the environment's caching mechanism
		$cacheService = new NullCache;

		if ($debug === false && $cacheEnabled !== false)
		{
			$cacheService = new FilesystemCache(JPATH_ROOT . '/' . $cachePath);
		}

		$environment->setCache($cacheService);

		// Add the Twig runtime loader
		$loader = new ContainerRuntimeLoader($container);

		$environment->addRuntimeLoader($loader);

		// Add our Twig extension
		$environment->addExtension(new FrameworkExtension);

		// Add the debug extension if enabled
		if ($debug)
		{
			$environment->addExtension(new DebugExtension);
		}

		return new TwigRenderer($environment);
	}
}
