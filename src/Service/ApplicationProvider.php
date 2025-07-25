<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\Application\AbstractApplication;
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
use Joomla\FrameworkWebsite\Command\ClearCacheCommand;
use Joomla\FrameworkWebsite\Command\GenerateSriCommand;
use Joomla\FrameworkWebsite\Command\GitHub\FetchDocsCommand;
use Joomla\FrameworkWebsite\Command\Package\SyncCommand as PackageSyncCommand;
use Joomla\FrameworkWebsite\Command\Package\SyncPullsCommand;
use Joomla\FrameworkWebsite\Command\Packagist\DownloadsCommand;
use Joomla\FrameworkWebsite\Command\Packagist\SyncCommand as PackagistSyncCommand;
use Joomla\FrameworkWebsite\Command\Twig\ResetCacheCommand;
use Joomla\FrameworkWebsite\Command\UpdateCommand;
use Joomla\FrameworkWebsite\Controller\Api\PackageControllerGet;
use Joomla\FrameworkWebsite\Controller\Api\StatusControllerGet;
use Joomla\FrameworkWebsite\Controller\Documentation\IndexController;
use Joomla\FrameworkWebsite\Controller\Documentation\PageController as DocumentationPageController;
use Joomla\FrameworkWebsite\Controller\Documentation\RedirectController;
use Joomla\FrameworkWebsite\Controller\HomepageController;
use Joomla\FrameworkWebsite\Controller\PackageController;
use Joomla\FrameworkWebsite\Controller\PageController;
use Joomla\FrameworkWebsite\Controller\StatusController;
use Joomla\FrameworkWebsite\Controller\WrongCmsController;
use Joomla\FrameworkWebsite\Helper;
use Joomla\FrameworkWebsite\Helper\GitHubHelper;
use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Model\ReleaseModel;
use Joomla\FrameworkWebsite\View\Documentation\ErrorHtmlView;
use Joomla\FrameworkWebsite\View\Documentation\IndexHtmlView;
use Joomla\FrameworkWebsite\View\Documentation\PageHtmlView;
use Joomla\FrameworkWebsite\View\Package\PackageHtmlView;
use Joomla\FrameworkWebsite\View\Package\PackageJsonView;
use Joomla\FrameworkWebsite\View\Status\StatusHtmlView;
use Joomla\FrameworkWebsite\View\Status\StatusJsonView;
use Joomla\FrameworkWebsite\WebApplication;
use Joomla\Github\Github;
use Joomla\Http\Http;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Renderer\RendererInterface;
use Joomla\Renderer\TwigRenderer;
use Joomla\Router\Command\DebugRouterCommand;
use Joomla\Router\Route;
use Joomla\Router\Router;
use Joomla\Router\RouterInterface;
use Psr\Cache\CacheItemPoolInterface;
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
        $container->alias(GitHubHelper::class, 'application.helper.github')
            ->share('application.helper.github', [$this, 'getApplicationHelperGithubService'], true);
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
        $container->share(ClearCacheCommand::class, [$this, 'getClearCacheCommandService'], true);
        $container->share(DebugEventDispatcherCommand::class, [$this, 'getDebugEventDispatcherCommandService'], true);
        $container->share(DebugRouterCommand::class, [$this, 'getDebugRouterCommandService'], true);
        $container->share(DownloadsCommand::class, [$this, 'getDownloadsCommandService'], true);
        $container->share(FetchDocsCommand::class, [$this, 'getGitHubFetchDocsCommandService'], true);
        $container->share(GenerateSriCommand::class, [$this, 'getGenerateSriCommandService'], true);
        $container->share(PackageSyncCommand::class, [$this, 'getPackageSyncCommandService'], true);
        $container->share(SyncPullsCommand::class, [$this, 'getPullSyncCommandService'], true);
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
        $container->alias(IndexController::class, 'controller.documentation.index')
            ->share('controller.documentation.index', [$this, 'getControllerDocumentationIndexService'], true);
        $container->alias(DocumentationPageController::class, 'controller.documentation.page')
            ->share('controller.documentation.page', [$this, 'getControllerDocumentationPageService'], true);
        $container->alias(RedirectController::class, 'controller.documentation.redirect')
            ->share('controller.documentation.redirect', [$this, 'getControllerDocumentationRedirectService'], true);
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
        $container->alias(ErrorHtmlView::class, 'view.documentation.error.html')
            ->share('view.documentation.error.html', [$this, 'getViewDocumentationErrorHtmlService'], true);
        $container->alias(IndexHtmlView::class, 'view.documentation.index.html')
            ->share('view.documentation.index.html', [$this, 'getViewDocumentationIndexHtmlService'], true);
        $container->alias(PageHtmlView::class, 'view.documentation.page.html')
            ->share('view.documentation.page.html', [$this, 'getViewDocumentationPageHtmlService'], true);
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
        $helper = new Helper();
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
        return (new Registry())->loadFile(JPATH_ROOT . '/packages.yml', 'YAML');
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
        $router = new Router();
        /*
         * CMS Admin Panels
         */
        $router->get('/administrator', WrongCmsController::class);
        $router->get('/administrator/*', WrongCmsController::class);
        $router->get('/wp-admin', WrongCmsController::class);
        $router->get('/wp-admin/*', WrongCmsController::class);
        $router->get('wp-login.php', WrongCmsController::class);
        /*
         * Web routes
         */
        $router->addRoute(new Route(['GET', 'HEAD'], '/', HomepageController::class));
        $router->get('/docs', IndexController::class);
        $router->get('/docs/:version/:package', RedirectController::class);
        $router->get('/docs/:version/:package/:filename', DocumentationPageController::class, ['filename' => '.*']);
        $router->get('/status', StatusController::class);
        $router->get('/:view', PageController::class);
        $router->get('/status/:package', PackageController::class);
        /*
         * API routes
         */
        $router->get('/api/v1/packages', StatusControllerGet::class, [], [
                '_format' => 'json',
            ]);
        $router->get('/api/v1/packages/:package', PackageControllerGet::class, [], [
                '_format' => 'json',
            ]);
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
            ClearCacheCommand::getDefaultName()           => ClearCacheCommand::class,
            DebugEventDispatcherCommand::getDefaultName() => DebugEventDispatcherCommand::class,
            DebugRouterCommand::getDefaultName()          => DebugRouterCommand::class,
            DownloadsCommand::getDefaultName()            => DownloadsCommand::class,
            FetchDocsCommand::getDefaultName()            => FetchDocsCommand::class,
            PackageSyncCommand::getDefaultName()          => PackageSyncCommand::class,
            PackagistSyncCommand::getDefaultName()        => PackagistSyncCommand::class,
            SyncPullsCommand::getDefaultName()            => SyncPullsCommand::class,
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
        $application = new ConsoleApplication(new ArgvInput(), new ConsoleOutput(), $container->get('config'));
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
        $controller = new PackageControllerGet($container->get(PackageJsonView::class), $container->get(Analytics::class), $container->get(Input::class), $container->get(WebApplication::class));
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
        return new HomepageController($container->get(RendererInterface::class), $container->get(Input::class), $container->get(WebApplication::class));
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
        return new PackageController($container->get(PackageHtmlView::class), $container->get(Input::class), $container->get(WebApplication::class));
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
        return new PageController($container->get(RendererInterface::class), $container->get(Input::class), $container->get(WebApplication::class));
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
        return new WrongCmsController($container->get(Input::class), $container->get(WebApplication::class));
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
        return new DebugEventDispatcherCommand($container->get(DispatcherInterface::class));
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
        return new DebugRouterCommand($container->get(Router::class));
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
        return new DownloadsCommand($container->get(PackagistHelper::class));
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
        return new GenerateSriCommand();
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
     * Get the SyncPullsCommand service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  SyncPullsCommand
     */
    public function getPullSyncCommandService(Container $container): SyncPullsCommand
    {
        return new SyncPullsCommand($container->get(Github::class), $container->get(Helper::class), $container->get(DatabaseInterface::class));
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
        return new PackagistSyncCommand($container->get(Http::class), $container->get(PackageModel::class), $container->get(ReleaseModel::class));
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
        return new ResetCacheCommand($container->get(TwigRenderer::class), $container->get('config'));
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
        return new UpdateCommand();
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
        $view = new PackageHtmlView($container->get('model.package'), $container->get('model.release'), $container->get(Helper::class), $container->get('renderer'));
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
        return new PackageJsonView($container->get('model.package'), $container->get('model.release'));
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
        $view = new StatusHtmlView($container->get('model.package'), $container->get('model.release'), $container->get('renderer'));
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
        return new StatusJsonView($container->get('model.package'), $container->get('model.release'));
    }
    /**
     * Get the `application.helper.github` service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  GitHubHelper
     */
    public function getApplicationHelperGithubService(Container $container): GitHubHelper
    {
        return new GitHubHelper(
            $container->get(Github::class),
            $container->get(DatabaseInterface::class),
            $container->get(CacheItemPoolInterface::class),
            $container->get(AbstractApplication::class)
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
        $application              = new WebApplication($container->get(ControllerResolverInterface::class), $container->get(RouterInterface::class), $container->get(Input::class), $container->get('config'), $container->get(WebClient::class));
        $application->httpVersion = '2';
        // Inject extra services
        $application->setDispatcher($container->get(DispatcherInterface::class));
        $application->setLogger($container->get(LoggerInterface::class));
        return $application;
    }

    /**
     * Get the ClearCacheCommand service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  ClearCacheCommand
     */
    public function getClearCacheCommandService(Container $container): ClearCacheCommand
    {
        return new ClearCacheCommand($container->get(CacheItemPoolInterface::class));
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
     * Get the `controller.documentation.index` service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  IndexController
     */
    public function getControllerDocumentationIndexService(Container $container): IndexController
    {
        return new IndexController(
            $container->get(IndexHtmlView::class),
            $container->get(Input::class),
            $container->get(WebApplication::class)
        );
    }

    /**
     * Get the `controller.documentation.page` service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  DocumentationPageController
     */
    public function getControllerDocumentationPageService(Container $container): DocumentationPageController
    {
        return new DocumentationPageController(
            $container->get(PackageModel::class),
            $container->get(ErrorHtmlView::class),
            $container->get(PageHtmlView::class),
            $container->get(GitHubHelper::class),
            $container->get(Input::class),
            $container->get(WebApplication::class)
        );
    }

    /**
     * Get the `controller.documentation.redirect` service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  RedirectController
     */
    public function getControllerDocumentationRedirectService(Container $container): RedirectController
    {
        return new RedirectController(
            $container->get(PackageModel::class),
            $container->get(ErrorHtmlView::class),
            $container->get(Input::class),
            $container->get(WebApplication::class)
        );
    }

    /**
     * Get the FetchDocsCommand service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  FetchDocsCommand
     */
    public function getGitHubFetchDocsCommandService(Container $container): FetchDocsCommand
    {
        return new FetchDocsCommand(
            $container->get(PackageModel::class),
            $container->get(Github::class),
            $container->get(GitHubHelper::class),
            $container->get(CacheItemPoolInterface::class)
        );
    }

    /**
     * Get the `view.documentation.error.html` service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  ErrorHtmlView
     */
    public function getViewDocumentationErrorHtmlService(Container $container): ErrorHtmlView
    {
        $view = new ErrorHtmlView(
            $container->get('model.package'),
            $container->get('renderer')
        );

        $view->setLayout('docs/error.twig');

        return $view;
    }

    /**
     * Get the `view.documentation.index.html` service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  IndexHtmlView
     */
    public function getViewDocumentationIndexHtmlService(Container $container): IndexHtmlView
    {
        $view = new IndexHtmlView(
            $container->get('model.package'),
            $container->get('renderer')
        );

        $view->setLayout('docs/index.twig');

        return $view;
    }

    /**
     * Get the `view.documentation.page.html` service
     *
     * @param   Container  $container  The DI container.
     *
     * @return  PageHtmlView
     */
    public function getViewDocumentationPageHtmlService(Container $container): PageHtmlView
    {
        $view = new PageHtmlView(
            $container->get('model.package'),
            $container->get('renderer')
        );

        $view->setLayout('docs/page.twig');

        return $view;
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
