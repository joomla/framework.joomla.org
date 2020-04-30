<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Controller\ContainerControllerResolver;
use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Application\Web\WebClient;
use Joomla\Console\Application as ConsoleApplication;
use Joomla\Console\Loader\ContainerLoader;
use Joomla\Console\Loader\LoaderInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\Command\DebugEventDispatcherCommand;
use Joomla\Event\DispatcherInterface;
use Joomla\FrameworkWebsite\Command\GenerateSriCommand;
use Joomla\FrameworkWebsite\Command\Package\SyncCommand as PackageSyncCommand;
use Joomla\FrameworkWebsite\Command\Packagist\DownloadsCommand;
use Joomla\FrameworkWebsite\Command\Packagist\SyncCommand as PackagistSyncCommand;
use Joomla\FrameworkWebsite\Command\Twig\ResetCacheCommand;
use Joomla\FrameworkWebsite\Command\UpdateCommand;
use Joomla\FrameworkWebsite\Controller\Api\PackageControllerGet;
use Joomla\FrameworkWebsite\Controller\Api\StatusControllerGet;
use Joomla\FrameworkWebsite\Controller\HomepageController;
use Joomla\FrameworkWebsite\Controller\PackageController;
use Joomla\FrameworkWebsite\Controller\PageController;
use Joomla\FrameworkWebsite\Controller\StatusController;
use Joomla\FrameworkWebsite\Controller\WrongCmsController;
use Joomla\FrameworkWebsite\Helper;
use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Model\ReleaseModel;
use Joomla\FrameworkWebsite\View\Package\PackageHtmlView;
use Joomla\FrameworkWebsite\View\Package\PackageJsonView;
use Joomla\FrameworkWebsite\View\Status\StatusHtmlView;
use Joomla\FrameworkWebsite\View\Status\StatusJsonView;
use Joomla\FrameworkWebsite\WebApplication;
use Joomla\Http\Http;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Renderer\RendererInterface;
use Joomla\Renderer\TwigRenderer;
use Joomla\Router\Command\DebugRouterCommand;
use Joomla\Router\Route;
use Joomla\Router\Router;
use Joomla\Router\RouterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Application service provider
 */
class ApplicationProvider implements ServiceProviderInterface
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
		/*
		 * Application Classes
		 */

		$container->share(ConsoleApplication::class, [$this, 'getConsoleApplicationService'], true);

		// This service cannot be protected as it is decorated when the debug bar is available
		$container->alias(WebApplication::class, AbstractWebApplication::class)
			->share(AbstractWebApplication::class, [$this, 'getWebApplicationClassService']);

		/*
		 * Application Helpers and Dependencies
		 */

		$container->alias(Analytics::class, 'analytics')
			->share('analytics', [$this, 'getAnalyticsService'], true);

		$container->alias(ContainerLoader::class, LoaderInterface::class)
			->share(LoaderInterface::class, [$this, 'getCommandLoaderService'], true);

		// This service cannot be protected as it is decorated when the debug bar is available
		$container->alias(ContainerControllerResolver::class, ControllerResolverInterface::class)
			->share(ControllerResolverInterface::class, [$this, 'getControllerResolverService']);

		$container->alias(Helper::class, 'application.helper')
			->share('application.helper', [$this, 'getApplicationHelperService'], true);

		$container->alias(PackagistHelper::class, 'application.helper.packagist')
			->share('application.helper.packagist', [$this, 'getApplicationHelperPackagistService'], true);

		$container->share('application.packages', [$this, 'getApplicationPackagesService'], true);

		$container->share(WebClient::class, [$this, 'getWebClientService'], true);

		// This service cannot be protected as it is decorated when the debug bar is available
		$container->alias(RouterInterface::class, 'application.router')
			->alias(Router::class, 'application.router')
			->share('application.router', [$this, 'getApplicationRouterService']);

		$container->share(Input::class, [$this, 'getInputClassService'], true);

		/*
		 * Console Commands
		 */

		$container->share(DebugEventDispatcherCommand::class, [$this, 'getDebugEventDispatcherCommandService'], true);
		$container->share(DebugRouterCommand::class, [$this, 'getDebugRouterCommandService'], true);
		$container->share(DownloadsCommand::class, [$this, 'getDownloadsCommandService'], true);
		$container->share(GenerateSriCommand::class, [$this, 'getGenerateSriCommandService'], true);
		$container->share(PackageSyncCommand::class, [$this, 'getPackageSyncCommandService'], true);
		$container->share(PackagistSyncCommand::class, [$this, 'getPackagistSyncCommandService'], true);
		$container->share(ResetCacheCommand::class, [$this, 'getResetCacheCommandService'], true);
		$container->share(UpdateCommand::class, [$this, 'getUpdateCommandService'], true);

		/*
		 * MVC Layer
		 */

		// Controllers
		$container->alias(PackageControllerGet::class, 'controller.api.package')
			->share('controller.api.package', [$this, 'getControllerApiPackageService'], true);

		$container->alias(StatusControllerGet::class, 'controller.api.status')
			->share('controller.api.status', [$this, 'getControllerApiStatusService'], true);

		$container->alias(HomepageController::class, 'controller.homepage')
			->share('controller.homepage', [$this, 'getControllerHomepageService'], true);

		$container->alias(PackageController::class, 'controller.package')
			->share('controller.package', [$this, 'getControllerPackageService'], true);

		$container->alias(PageController::class, 'controller.page')
			->share('controller.page', [$this, 'getControllerPageService'], true);

		$container->alias(StatusController::class, 'controller.status')
			->share('controller.status', [$this, 'getControllerStatusService'], true);

		$container->alias(WrongCmsController::class, 'controller.wrong.cms')
			->share('controller.wrong.cms', [$this, 'getControllerWrongCmsService'], true);

		// Models
		$container->alias(PackageModel::class, 'model.package')
			->share('model.package', [$this, 'getModelPackageService'], true);

		$container->alias(ReleaseModel::class, 'model.release')
			->share('model.release', [$this, 'getModelReleaseService'], true);

		// Views
		$container->alias(PackageHtmlView::class, 'view.package.html')
			->share('view.package.html', [$this, 'getViewPackageHtmlService'], true);

		$container->alias(PackageJsonView::class, 'view.package.json')
			->share('view.package.json', [$this, 'getViewPackageJsonService'], true);

		$container->alias(StatusHtmlView::class, 'view.status.html')
			->share('view.status.html', [$this, 'getViewStatusHtmlService'], true);

		$container->alias(StatusJsonView::class, 'view.status.json')
			->share('view.status.json', [$this, 'getViewStatusJsonService'], true);
	}

	/**
	 * Get the Analytics class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Analytics
	 */
	public function getAnalyticsService(Container $container)
	{
		return new Analytics(true);
	}

	/**
	 * Get the `application.helper` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Helper
	 */
	public function getApplicationHelperService(Container $container): Helper
	{
		$helper = new Helper;
		$helper->setPackages($container->get('application.packages'));

		return $helper;
	}

	/**
	 * Get the `application.helper.packagist` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackagistHelper
	 */
	public function getApplicationHelperPackagistService(Container $container): PackagistHelper
	{
		$helper = new PackagistHelper($container->get(Http::class), $container->get(DatabaseInterface::class));
		$helper->setPackages($container->get('application.packages'));

		return $helper;
	}

	/**
	 * Get the `application.packages` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Registry
	 */
	public function getApplicationPackagesService(Container $container): Registry
	{
		return (new Registry)->loadFile(JPATH_ROOT . '/packages.yml', 'YAML');
	}

	/**
	 * Get the `application.router` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RouterInterface
	 */
	public function getApplicationRouterService(Container $container): RouterInterface
	{
		$router = new Router;

		/*
		 * CMS Admin Panels
		 */
		$router->get(
			'/administrator',
			WrongCmsController::class
		);

		$router->get(
			'/administrator/*',
			WrongCmsController::class
		);

		$router->get(
			'/wp-admin',
			WrongCmsController::class
		);

		$router->get(
			'/wp-admin/*',
			WrongCmsController::class
		);

		$router->get(
			'wp-login.php',
			WrongCmsController::class
		);

		/*
		 * Web routes
		 */
		$router->addRoute(new Route(['GET', 'HEAD'], '/', HomepageController::class));

		$router->get(
			'/status',
			StatusController::class
		);

		$router->get(
			'/:view',
			PageController::class
		);

		$router->get(
			'/status/:package',
			PackageController::class
		);

		/*
		 * API routes
		 */
		$router->get(
			'/api/v1/packages',
			StatusControllerGet::class,
			[],
			[
				'_format' => 'json',
			]
		);

		$router->get(
			'/api/v1/packages/:package',
			PackageControllerGet::class,
			[],
			[
				'_format' => 'json',
			]
		);

		return $router;
	}

	/**
	 * Get the LoaderInterface service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LoaderInterface
	 */
	public function getCommandLoaderService(Container $container): LoaderInterface
	{
		$mapping = [
			DebugEventDispatcherCommand::getDefaultName() => DebugEventDispatcherCommand::class,
			DebugRouterCommand::getDefaultName()          => DebugRouterCommand::class,
			DownloadsCommand::getDefaultName()            => DownloadsCommand::class,
			PackageSyncCommand::getDefaultName()          => PackageSyncCommand::class,
			PackagistSyncCommand::getDefaultName()        => PackagistSyncCommand::class,
			GenerateSriCommand::getDefaultName()          => GenerateSriCommand::class,
			ResetCacheCommand::getDefaultName()           => ResetCacheCommand::class,
			UpdateCommand::getDefaultName()               => UpdateCommand::class,
		];

		return new ContainerLoader($container, $mapping);
	}

	/**
	 * Get the ConsoleApplication service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConsoleApplication
	 */
	public function getConsoleApplicationService(Container $container): ConsoleApplication
	{
		$application = new ConsoleApplication(new ArgvInput, new ConsoleOutput, $container->get('config'));

		$application->setCommandLoader($container->get(LoaderInterface::class));
		$application->setDispatcher($container->get(DispatcherInterface::class));
		$application->setLogger($container->get(LoggerInterface::class));
		$application->setName('Joomla! Framework Website');

		return $application;
	}

	/**
	 * Get the `controller.api.package` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackageControllerGet
	 */
	public function getControllerApiPackageService(Container $container): PackageControllerGet
	{
		$controller = new PackageControllerGet(
			$container->get(PackageJsonView::class),
			$container->get(Analytics::class),
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);

		$controller->setLogger($container->get(LoggerInterface::class));

		return $controller;
	}

	/**
	 * Get the `controller.api.status` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatusControllerGet
	 */
	public function getControllerApiStatusService(Container $container): StatusControllerGet
	{
		$controller = new StatusControllerGet(
			$container->get(StatusJsonView::class),
			$container->get(Analytics::class),
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);

		$controller->setLogger($container->get(LoggerInterface::class));

		return $controller;
	}

	/**
	 * Get the `controller.homepage` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  HomepageController
	 */
	public function getControllerHomepageService(Container $container): HomepageController
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
	 */
	public function getControllerPackageService(Container $container): PackageController
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
	 */
	public function getControllerPageService(Container $container): PageController
	{
		return new PageController(
			$container->get(RendererInterface::class),
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);
	}

	/**
	 * Get the controller resolver service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ControllerResolverInterface
	 */
	public function getControllerResolverService(Container $container): ControllerResolverInterface
	{
		return new ContainerControllerResolver($container);
	}

	/**
	 * Get the `controller.status` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatusController
	 */
	public function getControllerStatusService(Container $container): StatusController
	{
		return new StatusController(
			$container->get(StatusHtmlView::class),
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);
	}

	/**
	 * Get the `controller.wrong.cms` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WrongCmsController
	 */
	public function getControllerWrongCmsService(Container $container): WrongCmsController
	{
		return new WrongCmsController(
			$container->get(Input::class),
			$container->get(WebApplication::class)
		);
	}

	/**
	 * Get the DebugEventDispatcherCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugEventDispatcherCommand
	 */
	public function getDebugEventDispatcherCommandService(Container $container): DebugEventDispatcherCommand
	{
		return new DebugEventDispatcherCommand(
			$container->get(DispatcherInterface::class)
		);
	}

	/**
	 * Get the DebugRouterCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugRouterCommand
	 */
	public function getDebugRouterCommandService(Container $container): DebugRouterCommand
	{
		return new DebugRouterCommand(
			$container->get(Router::class)
		);
	}

	/**
	 * Get the DownloadsCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DownloadsCommand
	 */
	public function getDownloadsCommandService(Container $container): DownloadsCommand
	{
		return new DownloadsCommand(
			$container->get(PackagistHelper::class)
		);
	}

	/**
	 * Get the GenerateSriCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  GenerateSriCommand
	 */
	public function getGenerateSriCommandService(Container $container): GenerateSriCommand
	{
		return new GenerateSriCommand;
	}

	/**
	 * Get the Input class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Input
	 */
	public function getInputClassService(Container $container): Input
	{
		return new Input($_REQUEST);
	}

	/**
	 * Get the `model.package` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackageModel
	 */
	public function getModelPackageService(Container $container): PackageModel
	{
		return new PackageModel($container->get(DatabaseInterface::class));
	}

	/**
	 * Get the `model.release` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ReleaseModel
	 */
	public function getModelReleaseService(Container $container): ReleaseModel
	{
		return new ReleaseModel($container->get(DatabaseInterface::class));
	}

	/**
	 * Get the PackageSyncCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackageSyncCommand
	 */
	public function getPackageSyncCommandService(Container $container): PackageSyncCommand
	{
		return new PackageSyncCommand(
			$container->get(Helper::class),
			$container->get(PackageModel::class)
		);
	}

	/**
	 * Get the PackagistSyncCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackagistSyncCommand
	 */
	public function getPackagistSyncCommandService(Container $container): PackagistSyncCommand
	{
		return new PackagistSyncCommand(
			$container->get(Http::class),
			$container->get(PackageModel::class),
			$container->get(ReleaseModel::class)
		);
	}

	/**
	 * Get the ResetCacheCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ResetCacheCommand
	 */
	public function getResetCacheCommandService(Container $container): ResetCacheCommand
	{
		return new ResetCacheCommand(
			$container->get(TwigRenderer::class),
			$container->get('config')
		);
	}

	/**
	 * Get the UpdateCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  UpdateCommand
	 */
	public function getUpdateCommandService(Container $container): UpdateCommand
	{
		return new UpdateCommand;
	}

	/**
	 * Get the `view.package.html` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackageHtmlView
	 */
	public function getViewPackageHtmlService(Container $container): PackageHtmlView
	{
		$view = new PackageHtmlView(
			$container->get('model.package'),
			$container->get('model.release'),
			$container->get(Helper::class),
			$container->get('renderer')
		);

		$view->setLayout('package.twig');

		return $view;
	}

	/**
	 * Get the `view.package.json` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PackageJsonView
	 */
	public function getViewPackageJsonService(Container $container): PackageJsonView
	{
		return new PackageJsonView(
			$container->get('model.package'),
			$container->get('model.release')
		);
	}

	/**
	 * Get the `view.status.html` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatusHtmlView
	 */
	public function getViewStatusHtmlService(Container $container): StatusHtmlView
	{
		$view = new StatusHtmlView(
			$container->get('model.package'),
			$container->get('model.release'),
			$container->get('renderer')
		);

		$view->setLayout('status.twig');

		return $view;
	}

	/**
	 * Get the `view.status.json` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatusJsonView
	 */
	public function getViewStatusJsonService(Container $container): StatusJsonView
	{
		return new StatusJsonView(
			$container->get('model.package'),
			$container->get('model.release')
		);
	}

	/**
	 * Get the WebApplication class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebApplication
	 */
	public function getWebApplicationClassService(Container $container): WebApplication
	{
		$application = new WebApplication(
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
	 * Get the web client service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebClient
	 */
	public function getWebClientService(Container $container): WebClient
	{
		/** @var Input $input */
		$input          = $container->get(Input::class);
		$userAgent      = $input->server->getString('HTTP_USER_AGENT', '');
		$acceptEncoding = $input->server->getString('HTTP_ACCEPT_ENCODING', '');
		$acceptLanguage = $input->server->getString('HTTP_ACCEPT_LANGUAGE', '');

		return new WebClient($userAgent, $acceptEncoding, $acceptLanguage);
	}
}
