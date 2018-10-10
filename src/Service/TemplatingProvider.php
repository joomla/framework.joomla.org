<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\Application\AbstractApplication;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\FrameworkWebsite\Asset\MixPathPackage;
use Joomla\FrameworkWebsite\Renderer\ApplicationContext;
use Joomla\FrameworkWebsite\Renderer\FrameworkExtension;
use Joomla\FrameworkWebsite\Renderer\FrameworkTwigRuntime;
use Joomla\Preload\PreloadManager;
use Joomla\Renderer\RendererInterface;
use Joomla\Renderer\TwigRenderer;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Twig\Cache\CacheInterface;
use Twig\Cache\FilesystemCache;
use Twig\Cache\NullCache;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\ProfilerExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\Profiler\Profile;
use Twig\RuntimeLoader\ContainerRuntimeLoader;

/**
 * Templating service provider
 */
class TemplatingProvider implements ServiceProviderInterface
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

		// This service cannot be protected as it is decorated when the debug bar is available
		$container->alias(ProfilerExtension::class, 'twig.extension.profiler')
			->alias(\Twig_Extension_Profiler::class, 'twig.extension.profiler')
			->share('twig.extension.profiler', [$this, 'getTwigExtensionProfilerService']);

		$container->alias(LoaderInterface::class, 'twig.loader')
			->alias(\Twig_LoaderInterface::class, 'twig.loader')
			->share('twig.loader', [$this, 'getTwigLoaderService'], true);

		$container->alias(Profile::class, 'twig.profiler.profile')
			->alias(\Twig_Profiler_Profile::class, 'twig.profiler.profile')
			->share('twig.profiler.profile', [$this, 'getTwigProfilerProfileService'], true);

		$container->alias(FrameworkTwigRuntime::class, 'twig.runtime.framework')
			->share('twig.runtime.framework', [$this, 'getTwigRuntimeFrameworkService'], true);

		$container->alias(ContainerRuntimeLoader::class, 'twig.runtime.loader')
			->alias(\Twig_ContainerRuntimeLoader::class, 'twig.runtime.loader')
			->share('twig.runtime.loader', [$this, 'getTwigRuntimeLoaderService'], true);

		$this->tagTwigExtensions($container);
	}

	/**
	 * Get the `asset.packages` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Packages
	 */
	public function getAssetPackagesService(Container $container): Packages
	{
		/** @var AbstractApplication $app */
		$app = $container->get(AbstractApplication::class);

		$context = new ApplicationContext($app);

		$mediaPath = $app->get('uri.media.path', '/media/');

		$defaultPackage = new PathPackage($mediaPath, new EmptyVersionStrategy, $context);

		$mixStrategy = new MixPathPackage(
			$defaultPackage,
			$mediaPath,
			new JsonManifestVersionStrategy(JPATH_ROOT . '/www/media/mix-manifest.json'),
			$context
		);

		return new Packages(
			$defaultPackage,
			[
				'mix' => $mixStrategy,
			]
		);
	}

	/**
	 * Get the `renderer` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RendererInterface
	 */
	public function getRendererService(Container $container): RendererInterface
	{
		return new TwigRenderer($container->get('twig.environment'));
	}

	/**
	 * Get the `twig.cache` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  \Twig_CacheInterface
	 */
	public function getTwigCacheService(Container $container): \Twig_CacheInterface
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
	 */
	public function getTwigEnvironmentService(Container $container): Environment
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
		$environment->setExtensions($container->getTagged('twig.extension'));

		// Add a global tracking the debug states
		$environment->addGlobal('appDebug', $config->get('debug', false));
		$environment->addGlobal('fwDebug', $debug);

		return $environment;
	}

	/**
	 * Get the `twig.extension.debug` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugExtension
	 */
	public function getTwigExtensionDebugService(Container $container): DebugExtension
	{
		return new DebugExtension;
	}

	/**
	 * Get the `twig.extension.framework` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FrameworkExtension
	 */
	public function getTwigExtensionFrameworkService(Container $container): FrameworkExtension
	{
		return new FrameworkExtension;
	}

	/**
	 * Get the `twig.extension.profiler` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ProfilerExtension
	 */
	public function getTwigExtensionProfilerService(Container $container): ProfilerExtension
	{
		return new ProfilerExtension($container->get('twig.profiler.profile'));
	}

	/**
	 * Get the `twig.loader` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  \Twig_LoaderInterface
	 */
	public function getTwigLoaderService(Container $container): \Twig_LoaderInterface
	{
		return new FilesystemLoader([JPATH_TEMPLATES]);
	}

	/**
	 * Get the `twig.profiler.profile` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Profile
	 */
	public function getTwigProfilerProfileService(Container $container): Profile
	{
		return new Profile;
	}

	/**
	 * Get the `twig.runtime.framework` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FrameworkTwigRuntime
	 */
	public function getTwigRuntimeFrameworkService(Container $container): FrameworkTwigRuntime
	{
		return new FrameworkTwigRuntime(
			$container->get(AbstractApplication::class),
			$container->get(PreloadManager::class),
			JPATH_ROOT . '/www/media/sri-manifest.json'
		);
	}

	/**
	 * Get the `twig.runtime.loader` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ContainerRuntimeLoader
	 */
	public function getTwigRuntimeLoaderService(Container $container): ContainerRuntimeLoader
	{
		return new ContainerRuntimeLoader($container);
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
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		$debug = $config->get('template.debug', false);

		$twigExtensions = ['twig.extension.framework'];

		if ($debug)
		{
			$twigExtensions[] = 'twig.extension.debug';
		}

		$container->tag('twig.extension', $twigExtensions);
	}
}
