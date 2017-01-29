<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\Database\DatabaseDriver;
use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Joomla\FrameworkWebsite\ContainerAwareRouter;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Router\Router;
use Joomla\Status\Helper;
use Joomla\Status\Model\{
	DefaultModel, PackageModel, StatusModel
};

/**
 * Application service provider
 *
 * @since  1.0
 */
class ApplicationProvider implements ServiceProviderInterface
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
		/*
		 * Application Helpers and Dependencies
		 */

		$container->alias(Helper::class, 'application.helper')
			->share('application.helper', [$this, 'getApplicationHelperService'], true);

		$container->share('application.packages', [$this, 'getApplicationPackagesService'], true);

		$container->alias(ContainerAwareRouter::class, 'application.router')
			->alias(Router::class, 'application.router')
			->share('application.router', [$this, 'getApplicationRouterService'], true);

		$container->share(Input::class, [$this, 'getInputClassService'], true);

		/*
		 * MVC Layer
		 */

		// Models
		$container->alias(DefaultModel::class, 'model.default')
			->share('model.default', [$this, 'getModelDefaultService'], true);

		$container->alias(PackageModel::class, 'model.package')
			->share('model.package', [$this, 'getModelPackageService'], true);

		$container->alias(StatusModel::class, 'model.status')
			->share('model.status', [$this, 'getModelStatusService'], true);
	}

	/**
	 * Get the `application.helper` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Helper
	 *
	 * @since   1.0
	 */
	public function getApplicationHelperService(Container $container) : Helper
	{
		$helper = new Helper;
		$helper->setPackages($container->get('application.packages'));

		return $helper;
	}

	/**
	 * Get the `application.packages` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Registry
	 *
	 * @since   1.0
	 */
	public function getApplicationPackagesService(Container $container) : Registry
	{
		return (new Registry)->loadFile(JPATH_ROOT . '/packages.yml', 'YAML');
	}

	/**
	 * Get the `application.router` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ContainerAwareRouter
	 *
	 * @since   1.0
	 */
	public function getApplicationRouterService(Container $container) : ContainerAwareRouter
	{
		$router = (new ContainerAwareRouter($container->get(Input::class)))
			->setControllerPrefix('Joomla\\FrameworkWebsite\\Controller\\')
			->setDefaultController('DefaultController')
			->addMap('/:view', 'DefaultController')
			->addMap('/status/:package', 'PackageController');
	}

	/**
	 * Get the Input class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Input
	 *
	 * @since   1.0
	 */
	public function getInputClassService(Container $container) : Input
	{
		return new Input($_REQUEST);
	}

	/**
	 * Get the `model.default` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DefaultModel
	 *
	 * @since   1.0
	 */
	public function getModelDefaultService(Container $container) : DefaultModel
	{
		return new DefaultModel($container->get(DatabaseDriver::class));
	}

	/**
	 * Get the `model.package` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackageModel
	 *
	 * @since   1.0
	 */
	public function getModelPackageService(Container $container) : PackageModel
	{
		$model = new PackageModel($container->get(DatabaseDriver::class));
		$model->setPackages($container->get('application.packages'));

		return $model;
	}

	/**
	 * Get the `model.status` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatusModel
	 *
	 * @since   1.0
	 */
	public function getModelStatusService(Container $container) : StatusModel
	{
		$model = new StatusModel($container->get(Helper::class), $container->get(DatabaseDriver::class));
		$model->setPackages($container->get('application.packages'));

		return $model;
	}
}
