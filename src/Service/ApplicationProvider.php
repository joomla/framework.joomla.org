<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\Application as JoomlaApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Joomla\FrameworkWebsite\
{
	ContainerAwareRouter, WebApplication
};
use Joomla\FrameworkWebsite\Controller\{
	HomepageController, PackageController, PageController, StatusController
};
use Joomla\FrameworkWebsite\Model\{
	PackageModel, StatusModel
};
use Joomla\FrameworkWebsite\View\{
	Package\PackageHtmlView, Status\StatusHtmlView
};
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Renderer\RendererInterface;
use Joomla\Router\Router;
use Joomla\Status\Helper;

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
		 * Application Classes
		 */

		$container->alias(WebApplication::class, JoomlaApplication\AbstractWebApplication::class)
			->share(JoomlaApplication\AbstractWebApplication::class, [$this, 'getWebApplicationClassService'], true);

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

		// Controllers
		$container->alias(HomepageController::class, 'controller.homepage')
			->share('controller.homepage', [$this, 'getControllerHomepageService'], true);

		$container->alias(PackageController::class, 'controller.package')
			->share('controller.package', [$this, 'getControllerPackageService'], true);

		$container->alias(PageController::class, 'controller.page')
			->share('controller.page', [$this, 'getControllerPageService'], true);

		$container->alias(StatusController::class, 'controller.status')
			->share('controller.status', [$this, 'getControllerStatusService'], true);

		// Models
		$container->alias(PackageModel::class, 'model.package')
			->share('model.package', [$this, 'getModelPackageService'], true);

		$container->alias(StatusModel::class, 'model.status')
			->share('model.status', [$this, 'getModelStatusService'], true);

		// Views
		$container->alias(PackageHtmlView::class, 'view.package.html')
			->share('view.package.html', [$this, 'getViewPackageHtmlService'], true);

		$container->alias(StatusHtmlView::class, 'view.status.html')
			->share('view.status.html', [$this, 'getViewStatusHtmlService'], true);
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
		$router = new ContainerAwareRouter($container->get(Input::class));
		$router->setControllerPrefix('Joomla\\FrameworkWebsite\\Controller\\')
			->setDefaultController('HomepageController')
			->addMap('/status', 'StatusController')
			->addMap('/:view', 'PageController')
			->addMap('/status/:package', 'PackageController');

		$router->setContainer($container);

		return $router;
	}

	/**
	 * Get the `controller.homepage` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  HomepageController
	 *
	 * @since   1.0
	 */
	public function getControllerHomepageService(Container $container) : HomepageController
	{
		return new HomepageController(
			$container->get(RendererInterface::class),
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);
	}

	/**
	 * Get the `controller.package` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackageController
	 *
	 * @since   1.0
	 */
	public function getControllerPackageService(Container $container) : PackageController
	{
		return new PackageController(
			$container->get(PackageHtmlView::class),
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);
	}

	/**
	 * Get the `controller.page` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PageController
	 *
	 * @since   1.0
	 */
	public function getControllerPageService(Container $container) : PageController
	{
		return new PageController(
			$container->get(RendererInterface::class),
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);
	}

	/**
	 * Get the `controller.status` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatusController
	 *
	 * @since   1.0
	 */
	public function getControllerStatusService(Container $container) : StatusController
	{
		return new StatusController(
			$container->get(StatusHtmlView::class),
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);
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

	/**
	 * Get the `view.package.html` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackageHtmlView
	 *
	 * @since   1.0
	 */
	public function getViewPackageHtmlService(Container $container) : PackageHtmlView
	{
		$view = new PackageHtmlView(
			$container->get('model.package'),
			$container->get('renderer')
		);

		$view->setLayout('package.twig');

		return $view;
	}

	/**
	 * Get the `view.status.html` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatusHtmlView
	 *
	 * @since   1.0
	 */
	public function getViewStatusHtmlService(Container $container) : StatusHtmlView
	{
		$view = new StatusHtmlView(
			$container->get('model.status'),
			$container->get('renderer')
		);

		$view->setLayout('status.twig');

		return $view;
	}

	/**
	 * Get the WebApplication class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebApplication
	 *
	 * @since   1.0
	 */
	public function getWebApplicationClassService(Container $container) : WebApplication
	{
		$application = new WebApplication($container->get(Input::class), $container->get('config'));

		// Inject extra services
		$application->setContainer($container);
		$application->setRouter($container->get(ContainerAwareRouter::class));

		return $application;
	}
}
