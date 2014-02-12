<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status;

use Joomla\Controller\ControllerInterface;
use Joomla\DI\ContainerAwareInterface;
use Joomla\Input\Input;
use Joomla\Router\Router as JoomlaRouter;

/**
 * Routing class
 *
 * @since  1.0
 */
class Router extends JoomlaRouter
{
	/**
	 * Application object to inject into controllers
	 *
	 * @var    StatusApplication
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   Input        $input  An optional input object from which to derive the route.  If none
	 *                               is given than the input from the application object will be used.
	 * @param   Application  $app    An optional application object to inject to controllers
	 *
	 * @since   1.0
	 */
	public function __construct(Input $input = null, Application $app = null)
	{
		parent::__construct($app->input);

		$this->app = $app;
	}

	/**
	 * Get a Controller object for a given name
	 *
	 * @param   string  $name  The controller name (excluding prefix) for which to fetch an instance of
	 *
	 * @return  ControllerInterface
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function fetchController($name)
	{
		$controller = parent::fetchController($name);

		if ($controller instanceof ContainerAwareInterface)
		{
			$controller->setContainer($this->app->getContainer());
		}

		$controller->setApplication($this->app);

		return $controller;
	}
}
