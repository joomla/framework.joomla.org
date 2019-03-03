<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

/**
 * Cache service provider
 */
class CacheProvider implements ServiceProviderInterface
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
		// This service cannot be protected as it is decorated when the debug bar is available
		$container->alias('cache', CacheItemPoolInterface::class)
			->alias(AdapterInterface::class, CacheItemPoolInterface::class)
			->share(CacheItemPoolInterface::class, [$this, 'getCacheService']);
	}

	/**
	 * Get the `cache` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CacheItemPoolInterface
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function getCacheService(Container $container): CacheItemPoolInterface
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		// If caching isn't enabled then just return a void cache
		if (!$config->get('cache.enabled', false))
		{
			return new NullAdapter;
		}

		$adapter   = $config->get('cache.adapter', 'file');
		$lifetime  = $config->get('cache.lifetime', 900);
		$namespace = $config->get('cache.namespace', 'jfw');

		switch ($adapter)
		{
			case 'file':
				$path = $config->get('cache.file.path', JPATH_ROOT . '/cache/pool');

				// If no path is given, fall back to the system's temporary directory
				if (empty($path))
				{
					$path = sys_get_temp_dir();
				}

				return new FilesystemAdapter($namespace, $lifetime, $path);

			case 'none':
				return new NullAdapter;

			case 'runtime':
				return new ArrayAdapter($lifetime);
		}

		throw new InvalidArgumentException(sprintf('The "%s" cache adapter is not supported.', $adapter));
	}
}
