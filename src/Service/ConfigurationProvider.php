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
use Joomla\Registry\Registry;

/**
 * Configuration service provider
 */
class ConfigurationProvider implements ServiceProviderInterface
{
	/**
	 * Configuration instance
	 *
	 * @var  Registry
	 */
	private $config;

	/**
	 * Constructor.
	 *
	 * @param   string  $file  Path to the config file.
	 *
	 * @throws  \RuntimeException
	 */
	public function __construct(string $file)
	{
		// Verify the configuration exists and is readable.
		if (!is_readable($file))
		{
			throw new \RuntimeException('Configuration file does not exist or is unreadable.');
		}

		$this->config = (new Registry)->loadFile($file);

		// Hardcode database driver option
		$this->config->set('database.driver', 'mysql');
	}

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container): void
	{
		$container->share(
			'config',
			function (): Registry
			{
				return $this->config;
			},
			true
		);
	}
}
