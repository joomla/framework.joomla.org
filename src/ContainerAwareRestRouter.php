<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use Joomla\Controller\ControllerInterface;
use Joomla\DI\{
	ContainerAwareInterface, ContainerAwareTrait
};
use Joomla\Router\RestRouter;

/**
 * Container aware RESTful web router
 *
 * @since  1.0
 */
class ContainerAwareRestRouter extends RestRouter implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Get a controller object for a given name.
	 *
	 * @param   string  $name  The controller name (excluding prefix) for which to fetch and instance.
	 *
	 * @return  ControllerInterface
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function fetchController($name) : ControllerInterface
	{
		// Derive the controller class name.
		$class = $this->controllerPrefix . ucfirst($name);

		// If the controller class does not exist panic.
		if (!class_exists($class))
		{
			throw new \RuntimeException(sprintf('Unable to locate controller `%s`.', $class), 404);
		}

		// If the controller does not follows the implementation.
		if (!is_subclass_of($class, ControllerInterface::class))
		{
			throw new \RuntimeException(
				sprintf('Invalid Controller `%1$s`. Controllers must implement %2$s.', $class, ControllerInterface::class), 500
			);
		}

		// Instantiate the controller.
		return $this->getContainer()->get($class);
	}
}
