<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Router;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\CommandInterface;
use Joomla\Input\Input;
use Joomla\Router\Router;

/**
 * Router cache command
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class CacheCommand extends AbstractController implements CommandInterface
{
	/**
	 * The application router
	 *
	 * @var    Router
	 * @since  1.0
	 */
	private $router;

	/**
	 * Instantiate the controller.
	 *
	 * @param   Router               $router  The application router.
	 * @param   Input                $input   The input object.
	 * @param   AbstractApplication  $app     The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(Router $router, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->router = $router;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Cache Router');

		// Check if caching is enabled
		$twigCache = $this->getApplication()->get('router.cache', false);

		if ($twigCache === false)
		{
			$this->getApplication()->out('<info>Router caching is disabled.</info>');

			return true;
		}

		$this->getApplication()->out('<info>Resetting Router Cache</info>');

		file_put_contents(JPATH_ROOT . '/cache/router.txt', serialize($this->router));

		$this->getApplication()->out('<info>The router has been cached.</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getDescription() : string
	{
		return 'Resets the router cache.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle() : string
	{
		return 'Reset Router Cache';
	}
}
