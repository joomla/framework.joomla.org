<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Service;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;
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
		$container->alias(Helper::class, 'application.helper')
			->share(
			'application.helper',
			function (Container $container) : Helper
			{
				$helper = new Helper;
				$helper->setPackages($container->get('application.packages'));

				return $helper;
			},
			true
		);

		$container->share(
			'application.packages',
			function () : Registry
			{
				$registry = new Registry;
				$registry->loadFile(JPATH_ROOT . '/packages.yml', 'YAML');

				return $registry;
			},
			true
		);
	}
}
