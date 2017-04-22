<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\{
	PsrLogMessageProcessor, WebProcessor
};

/**
 * Logging service provider
 *
 * @since  1.0
 */
class LoggingProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
		/*
		 * Monolog Handlers
		 */
		$container->share('monolog.handler.application', [$this, 'getMonologHandlerApplicationService'], true);
		$container->share('monolog.handler.database', [$this, 'getMonologHandlerDatabaseService'], true);

		/*
		 * Monolog Processors
		 */
		$container->share('monolog.processor.psr3', [$this, 'getMonologProcessorPsr3Service'], true);
		$container->share('monolog.processor.web', [$this, 'getMonologProcessorWebService'], true);

		/*
		 * Application Loggers
		 */
		$container->share('monolog.logger.application.cli', [$this, 'getMonologLoggerApplicationCliService'], true);
		$container->share('monolog.logger.application.web', [$this, 'getMonologLoggerApplicationWebService'], true);
		$container->share('monolog.logger.database', [$this, 'getMonologLoggerDatabaseService'], true);
	}

	/**
	 * Get the `monolog.handler.application` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StreamHandler
	 *
	 * @since   1.0
	 */
	public function getMonologHandlerApplicationService(Container $container) : StreamHandler
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		$level = strtoupper($config->get('log.application', $config->get('log.level', 'error')));

		return new StreamHandler(JPATH_ROOT . '/logs/framework.log', constant('\\Monolog\\Logger::' . $level));
	}

	/**
	 * Get the `monolog.handler.database` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StreamHandler
	 *
	 * @since   1.0
	 */
	public function getMonologHandlerDatabaseService(Container $container) : StreamHandler
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		// If database debugging is enabled then force the logger's error level to DEBUG, otherwise use the level defined in the app config
		$level = $config->get('database.debug', false) ? 'DEBUG' : strtoupper($config->get('log.database', $config->get('log.level', 'error')));

		return new StreamHandler(JPATH_ROOT . '/logs/framework.log', constant('\\Monolog\\Logger::' . $level));
	}

	/**
	 * Get the `monolog.logger.application.cli` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Logger
	 *
	 * @since   1.0
	 */
	public function getMonologLoggerApplicationCliService(Container $container) : Logger
	{
		return new Logger(
			'Framework',
			[
				$container->get('monolog.handler.application'),
			],
			[
				$container->get('monolog.processor.psr3'),
			]
		);
	}

	/**
	 * Get the `monolog.logger.application.web` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Logger
	 *
	 * @since   1.0
	 */
	public function getMonologLoggerApplicationWebService(Container $container) : Logger
	{
		return new Logger(
			'Framework',
			[
				$container->get('monolog.handler.application'),
			],
			[
				$container->get('monolog.processor.psr3'),
				$container->get('monolog.processor.web'),
			]
		);
	}

	/**
	 * Get the `monolog.logger.database` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Logger
	 *
	 * @since   1.0
	 */
	public function getMonologLoggerDatabaseService(Container $container) : Logger
	{
		return new Logger(
			'Framework',
			[
				$container->get('monolog.handler.database'),
			],
			[
				$container->get('monolog.processor.psr3'),
			]
		);
	}

	/**
	 * Get the `monolog.processor.psr3` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PsrLogMessageProcessor
	 *
	 * @since   1.0
	 */
	public function getMonologProcessorPsr3Service(Container $container) : PsrLogMessageProcessor
	{
		return new PsrLogMessageProcessor;
	}

	/**
	 * Get the `monolog.processor.web` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebProcessor
	 *
	 * @since   1.0
	 */
	public function getMonologProcessorWebService(Container $container) : WebProcessor
	{
		return new WebProcessor;
	}
}
