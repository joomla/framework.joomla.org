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
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;

/**
 * Logging service provider
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
	public function register(Container $container): void
	{
		/*
		 * Monolog Handlers
		 */
		$container->share('monolog.handler.application', [$this, 'getMonologHandlerApplicationService'], true);

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
	}

	/**
	 * Get the `monolog.handler.application` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StreamHandler
	 */
	public function getMonologHandlerApplicationService(Container $container): StreamHandler
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		$level = strtoupper($config->get('log.application', $config->get('log.level', 'error')));

		return new StreamHandler(JPATH_ROOT . '/logs/framework.log', \constant('\\Monolog\\Logger::' . $level));
	}

	/**
	 * Get the `monolog.logger.application.cli` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Logger
	 */
	public function getMonologLoggerApplicationCliService(Container $container): Logger
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
	 */
	public function getMonologLoggerApplicationWebService(Container $container): Logger
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
	 * Get the `monolog.processor.psr3` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PsrLogMessageProcessor
	 */
	public function getMonologProcessorPsr3Service(Container $container): PsrLogMessageProcessor
	{
		return new PsrLogMessageProcessor;
	}

	/**
	 * Get the `monolog.processor.web` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebProcessor
	 */
	public function getMonologProcessorWebService(Container $container): WebProcessor
	{
		return new WebProcessor;
	}
}
