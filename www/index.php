<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

// Application constants
\define('APP_START', microtime(true));
\define('JPATH_ROOT', \dirname(__DIR__));
\define('JPATH_TEMPLATES', JPATH_ROOT . '/templates');

// Ensure we've initialized Composer
if (!file_exists(JPATH_ROOT . '/vendor/autoload.php')) {
    header('HTTP/1.1 500 Internal Server Error', true, 500);
    echo '<html><head><title>Server Error</title></head><body><h1>Composer Not Installed</h1><p>Composer is not set up properly, please run "composer install".</p></body></html>';

    exit(500);
}

require JPATH_ROOT . '/vendor/autoload.php';

// Wrap in a try/catch so we can display an error if need be
try {
    $container = (new Joomla\DI\Container())
        ->registerServiceProvider(new Joomla\FrameworkWebsite\Service\ApplicationProvider())
        ->registerServiceProvider(new Joomla\FrameworkWebsite\Service\ConfigurationProvider(JPATH_ROOT . '/etc/config.json'))
        ->registerServiceProvider(new Joomla\Database\Service\DatabaseProvider())
        ->registerServiceProvider(new Joomla\FrameworkWebsite\Service\EventProvider())
        ->registerServiceProvider(new Joomla\FrameworkWebsite\Service\GitHubProvider())
        ->registerServiceProvider(new Joomla\FrameworkWebsite\Service\HttpProvider())
        ->registerServiceProvider(new Joomla\FrameworkWebsite\Service\LoggingProvider())
        ->registerServiceProvider(new Joomla\Preload\Service\PreloadProvider())
        ->registerServiceProvider(new Joomla\FrameworkWebsite\Service\TemplatingProvider());

    // Conditionally include the DebugBar service provider based on the app being in debug mode
    if ((bool) $container->get('config')->get('debug', false)) {
        $container->registerServiceProvider(new Joomla\FrameworkWebsite\Service\DebugBarProvider());
    }

    // Alias the web application to Joomla's base application class as this is the primary application for the environment
    $container->alias(Joomla\Application\AbstractApplication::class, Joomla\Application\AbstractWebApplication::class);

    // Alias the web logger to the PSR-3 interface as this is the primary logger for the environment
    $container->alias(Monolog\Logger::class, 'monolog.logger.application.web')
        ->alias(Psr\Log\LoggerInterface::class, 'monolog.logger.application.web');

    // Set error reporting based on config
    $errorReporting = (int) $container->get('config')->get('errorReporting', 0);
    error_reporting($errorReporting);

    // There is a circular dependency in building the HTTP driver while the application is being resolved, so it'll need to be set here for now
    if ($container->has('debug.bar')) {
        /** @var \DebugBar\DebugBar $debugBar */
        $debugBar = $container->get('debug.bar');
        $debugBar->setHttpDriver($container->get('debug.http.driver'));
    }
} catch (\Throwable $e) {
    error_log($e);

    header('HTTP/1.1 500 Internal Server Error', true, 500);
    echo '<html><head><title>Container Initialization Error</title></head><body><h1>Container Initialization Error</h1><p>An error occurred while creating the DI container: ' . $e->getMessage() . '</p></body></html>';

    exit(1);
}

if ($container->has('debug.bar')) {
    /** @var \DebugBar\DebugBar $debugBar */
    $debugBar = $container->get('debug.bar');
    $debugBar->setHttpDriver($container->get('debug.http.driver'));

    /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
    $collector = $debugBar['time'];
    $collector->addMeasure('initialisation', APP_START, microtime(true));
}

// Execute the application
try {
    $container->get(Joomla\Application\AbstractApplication::class)->execute();
} catch (\Throwable $e) {
    error_log($e);

    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<html><head><title>Application Error</title></head><body><h1>Application Error</h1><p>An error occurred while executing the application: ' . $e->getMessage() . '</p></body></html>';

    exit(1);
}
