<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Renderer;

use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

/**
 * Twig runtime loader
 *
 * @since  1.0
 */
class TwigRuntimeLoader implements \Twig_RuntimeLoaderInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Creates the runtime implementation of a Twig element (filter/function/test).
	 *
	 * @param   string  $class  A runtime class
	 *
	 * @return  object|null  The runtime instance or null if the loader does not know how to create the runtime for this class
	 *
	 * @since   1.0
	 */
	public function load($class)
	{
		if ($this->getContainer()->exists($class))
		{
			return $this->getContainer()->get($class);
		}
	}
}
