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
use Joomla\FrameworkWebsite\{
	CliApplication, Console, ContainerAwareRouter, Helper, WebApplication
};
use Joomla\FrameworkWebsite\Command as AppCommands;
use Joomla\FrameworkWebsite\Controller\{
	HomepageController, PackageController, PageController, StatusController
};
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\View\{
	Package\PackageHtmlView, Status\StatusHtmlView
};
use Joomla\Input\{
	Cli, Input
};
use Joomla\Registry\Registry;
use Joomla\Renderer\RendererInterface;
use Joomla\Renderer\TwigRenderer;
use Joomla\Router\Router;

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

		$container->alias(CliApplication::class, JoomlaApplication\AbstractCliApplication::class)
			->share(JoomlaApplication\AbstractCliApplication::class, [$this, 'getCliApplicationClassService'], true);

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
		$container->share(Cli::class, [$this, 'getInputCliClassService'], true);

		$container->share(Console::class, [$this, 'getConsoleClassService'], true);

		$container->share(JoomlaApplication\Cli\Output\Processor\ColorProcessor::class, [$this, 'getColorProcessorClassService'], true);
		$container->share(JoomlaApplication\Cli\CliInput::class, [$this, 'getCliInputClassService'], true);

		$container->alias(JoomlaApplication\Cli\CliOutput::class, JoomlaApplication\Cli\Output\Stdout::class)
			->share(JoomlaApplication\Cli\Output\Stdout::class, [$this, 'getCliOutputClassService'], true);

		/*
		 * Console Commands
		 */

		$container->share(AppCommands\HelpCommand::class, [$this, 'getHelpCommandClassService'], true);
		$container->share(AppCommands\InstallCommand::class, [$this, 'getInstallCommandClassService'], true);
		$container->share(AppCommands\Twig\ResetCacheCommand::class, [$this, 'getTwigResetCacheCommandClassService'], true);
		$container->share(AppCommands\UpdateCommand::class, [$this, 'getUpdateCommandClassService'], true);

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
	 * Get the CLI application service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CliApplication
	 *
	 * @since   1.0
	 */
	public function getCliApplicationClassService(Container $container) : CliApplication
	{
		$application = new CliApplication(
			$container->get(Cli::class),
			$container->get('config'),
			$container->get(JoomlaApplication\Cli\CliOutput::class),
			$container->get(JoomlaApplication\Cli\CliInput::class),
			$container->get(Console::class)
		);

		return $application;
	}

	/**
	 * Get the CliInput class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\CliInput
	 *
	 * @since   1.0
	 */
	public function getCliInputClassService(Container $container) : JoomlaApplication\Cli\CliInput
	{
		return new JoomlaApplication\Cli\CliInput;
	}

	/**
	 * Get the CliOutput class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\CliOutput
	 *
	 * @since   1.0
	 */
	public function getCliOutputClassService(Container $container) : JoomlaApplication\Cli\Output\Stdout
	{
		return new JoomlaApplication\Cli\Output\Stdout($container->get(JoomlaApplication\Cli\Output\Processor\ColorProcessor::class));
	}

	/**
	 * Get the ColorProcessor class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\Output\Processor\ColorProcessor
	 *
	 * @since   1.0
	 */
	public function getColorProcessorClassService(Container $container) : JoomlaApplication\Cli\Output\Processor\ColorProcessor
	{
		$processor = new JoomlaApplication\Cli\Output\Processor\ColorProcessor;

		/** @var Cli $input */
		$input = $container->get(Cli::class);

		if ($input->getBool('nocolors', false))
		{
			$processor->noColors = true;
		}

		// Setup app colors (also required in "nocolors" mode - to strip them).
		$processor->addStyle('title', new JoomlaApplication\Cli\ColorStyle('yellow', '', ['bold']));

		return $processor;
	}

	/**
	 * Get the console service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Console
	 *
	 * @since   1.0
	 */
	public function getConsoleClassService(Container $container) : Console
	{
		$console = new Console;
		$console->setContainer($container);

		return $console;
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
	 * Get the HelpCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\HelpCommand
	 *
	 * @since   1.0
	 */
	public function getHelpCommandClassService(Container $container) : AppCommands\HelpCommand
	{
		return new AppCommands\HelpCommand(
			$container->get(Console::class),
			$container->get(Input::class),
			$container->get(JoomlaApplication\AbstractApplication::class)
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
	 * Get the Input\Cli class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Cli
	 *
	 * @since   1.0
	 */
	public function getInputCliClassService(Container $container) : Cli
	{
		return new Cli;
	}

	/**
	 * Get the InstallCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\InstallCommand
	 *
	 * @since   1.0
	 */
	public function getInstallCommandClassService(Container $container) : AppCommands\InstallCommand
	{
		return new AppCommands\InstallCommand(
			$container->get(DatabaseDriver::class),
			$container->get(Input::class),
			$container->get(JoomlaApplication\AbstractApplication::class)
		);
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
		$model = new PackageModel($container->get(Helper::class), $container->get(DatabaseDriver::class));
		$model->setPackages($container->get('application.packages'));

		return $model;
	}

	/**
	 * Get the Twig\ResetCacheCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\Twig\ResetCacheCommand
	 *
	 * @since   1.0
	 */
	public function getTwigResetCacheCommandClassService(Container $container) : AppCommands\Twig\ResetCacheCommand
	{
		return new AppCommands\Twig\ResetCacheCommand(
			$container->get(TwigRenderer::class),
			$container->get(Input::class),
			$container->get(JoomlaApplication\AbstractApplication::class)
		);
	}

	/**
	 * Get the UpdateCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\UpdateCommand
	 *
	 * @since   1.0
	 */
	public function getUpdateCommandClassService(Container $container) : AppCommands\UpdateCommand
	{
		return new AppCommands\UpdateCommand(
			$container->get(Console::class),
			$container->get(Input::class),
			$container->get(JoomlaApplication\AbstractApplication::class)
		);
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
			$container->get('renderer'),
			$container->get(Helper::class)
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
			$container->get('model.package'),
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
