<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\Database\{
	DatabaseDriver, DatabaseFactory, DatabaseInterface
};
use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Joomla\Registry\Registry;

/**
 * Database service provider
 *
 * @since  1.0
 */
class DatabaseProvider implements ServiceProviderInterface
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
		$container->alias(DatabaseInterface::class, 'db')
			->alias(DatabaseDriver::class, 'db')
			->share('db', [$this, 'getDbService'], true);

		$container->alias(DatabaseFactory::class, 'db.factory')
			->share('db.factory', [$this, 'getDbFactoryService'], true);
	}

	/**
	 * Get the `db` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DatabaseInterface
	 *
	 * @since   1.0
	 */
	public function getDbService(Container $container) : DatabaseInterface
	{
		/** @var Registry $config */
		$config = $container->get('config');

		/** @var DatabaseFactory $factory */
		$factory = $container->get('db.factory');

		$options = [
			'host'     => $config->get('database.host'),
			'user'     => $config->get('database.user'),
			'password' => $config->get('database.password'),
			'database' => $config->get('database.name'),
			'prefix'   => $config->get('database.prefix'),
			'factory'  => $factory,
		];

		return $factory->getDriver($config->get('database.driver'), $options);
	}

	/**
	 * Get the `db.factory` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DatabaseFactory
	 *
	 * @since   1.0
	 */
	public function getDbFactoryService(Container $container) : DatabaseFactory
	{
		return new DatabaseFactory;
	}
}
