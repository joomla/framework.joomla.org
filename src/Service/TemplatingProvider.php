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
	CacheInterface, FilesystemCache, NullCache
};
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\{
	FilesystemLoader, LoaderInterface
};
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
		$container->alias(Packages::class, 'asset.packages')
			->share('asset.packages', [$this, 'getAssetPackagesService'], true);

		$container->alias(RendererInterface::class, 'renderer')
			->alias(TwigRenderer::class, 'renderer')
			->share('renderer', [$this, 'getRendererService'], true);

		$container->alias(CacheInterface::class, 'twig.cache')
			->alias(\Twig_CacheInterface::class, 'twig.cache')
			->share('twig.cache', [$this, 'getTwigCacheService'], true);

		$container->alias(Environment::class, 'twig.environment')
			->alias(\Twig_Environment::class, 'twig.environment')
			->share('twig.environment', [$this, 'getTwigEnvironmentService'], true);

		$container->alias(DebugExtension::class, 'twig.extension.debug')
			->alias(\Twig_Extension_Debug::class, 'twig.extension.debug')
			->share('twig.extension.debug', [$this, 'getTwigExtensionDebugService'], true);

		$container->alias(FrameworkExtension::class, 'twig.extension.framework')
			->share('twig.extension.framework', [$this, 'getTwigExtensionFrameworkService'], true);

		$container->alias(LoaderInterface::class, 'twig.loader')
			->alias(\Twig_LoaderInterface::class, 'twig.loader')
			->share('twig.loader', [$this, 'getTwigLoaderService'], true);

		$container->alias(FrameworkTwigRuntime::class, 'twig.runtime.framework')
			->share('twig.runtime.framework', [$this, 'getTwigRuntimeFrameworkService'], true);

		$container->alias(ContainerRuntimeLoader::class, 'twig.runtime.loader')
			->alias(\Twig_ContainerRuntimeLoader::class, 'twig.runtime.loader')
			->share('twig.runtime.loader', [$this, 'getTwigRuntimeLoaderService'], true);
	}

	/**
	 * Get the `asset.packages` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Packages
	 *
	 * @since   1.0
	 */
	public function getAssetPackagesService(Container $container) : Packages
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
		return new TwigRenderer($container->get('twig.environment'));
	}

	/**
	 * Get the `twig.cache` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  \Twig_CacheInterface
	 *
	 * @since   1.0
	 */
	public function getTwigCacheService(Container $container) : \Twig_CacheInterface
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		// Pull down the renderer config
		$cacheEnabled = $config->get('template.cache.enabled', false);
		$cachePath    = $config->get('template.cache.path', 'cache/twig');
		$debug        = $config->get('template.debug', false);

		if ($debug === false && $cacheEnabled !== false)
		{
			return new FilesystemCache(JPATH_ROOT . '/' . $cachePath);
		}

		return new NullCache;
	}

	/**
	 * Get the `twig.environment` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Environment
	 *
	 * @since   1.0
	 */
	public function getTwigEnvironmentService(Container $container) : Environment
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		$debug = $config->get('template.debug', false);

		$environment = new Environment(
			$container->get('twig.loader'),
			['debug' => $debug]
		);

		// Add the runtime loader
		$environment->addRuntimeLoader($container->get('twig.runtime.loader'));

		// Set up the environment's caching service
		$environment->setCache($container->get('twig.cache'));

		// Add the Twig extensions
		$environment->addExtension($container->get('twig.extension.framework'));

		if ($debug)
		{
			$environment->addExtension($container->get('twig.extension.debug'));
		}

		// Add a global tracking the debug state
		$environment->addGlobal('fwDebug', $debug);

		return $environment;
	}

	/**
	 * Get the `twig.extension.debug` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugExtension
	 *
	 * @since   1.0
	 */
	public function getTwigExtensionDebugService(Container $container) : DebugExtension
	{
		return new DebugExtension;
	}

	/**
	 * Get the `twig.extension.framework` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FrameworkExtension
	 *
	 * @since   1.0
	 */
	public function getTwigExtensionFrameworkService(Container $container) : FrameworkExtension
	{
		return new FrameworkExtension;
	}

	/**
	 * Get the `twig.loader` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  \Twig_LoaderInterface
	 *
	 * @since   1.0
	 */
	public function getTwigLoaderService(Container $container) : \Twig_LoaderInterface
	{
		return new FilesystemLoader([JPATH_TEMPLATES]);
	}

	/**
	 * Get the `twig.runtime.framework` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FrameworkTwigRuntime
	 *
	 * @since   1.0
	 */
	public function getTwigRuntimeFrameworkService(Container $container) : FrameworkTwigRuntime
	{
		return new FrameworkTwigRuntime($container->get(AbstractApplication::class), $container->get(Packages::class));
	}

	/**
	 * Get the `twig.runtime.loader` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ContainerRuntimeLoader
	 *
	 * @since   1.0
	 */
	public function getTwigRuntimeLoaderService(Container $container) : ContainerRuntimeLoader
	{
		return new ContainerRuntimeLoader($container);
	}
}
