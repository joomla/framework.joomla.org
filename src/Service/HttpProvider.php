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
use Joomla\Http\Http;
use Joomla\Http\HttpFactory;

/**
 * HTTP service provider
 */
class HttpProvider implements ServiceProviderInterface
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
		$container->alias(Http::class, 'http')
			->share('http', [$this, 'getHttpService'], true);

		$container->alias(HttpFactory::class, 'http.factory')
			->share('http.factory', [$this, 'getHttpFactoryService'], true);
	}

	/**
	 * Get the `http` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Http
	 */
	public function getHttpService(Container $container): Http
	{
		/** @var HttpFactory $factory */
		$factory = $container->get('http.factory');

		return $factory->getHttp();
	}

	/**
	 * Get the `http.factory` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  HttpFactory
	 */
	public function getHttpFactoryService(Container $container): HttpFactory
	{
		return new HttpFactory;
	}
}
