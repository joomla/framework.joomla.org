<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatusCli;

use Joomla\Application\AbstractCliApplication;
use Joomla\Application\Cli\Output\Processor\ColorProcessor;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;

use Joomla\StatusCli\Command\RunTests;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * CLI application for the Framework Status Application
 *
 * @since  1.0
 */
class Application extends AbstractCliApplication implements ContainerAwareInterface
{
	/**
	 * DI Container
	 *
	 * @var    Container
	 * @since  1.0
	 */
	private $container = null;

	/**
	 * Event Dispatcher
	 *
	 * @var    Dispatcher
	 * @since  1.0
	 */
	private $dispatcher;

	/**
	 * Class constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		// Set up the output processor
		$this->getOutput()->setProcessor(new ColorProcessor);
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function doExecute()
	{
		(new RunTests($this))->execute();
	}

	/**
	 * Get the DI container
	 *
	 * @return  Container
	 *
	 * @since   1.0
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Custom initialisation method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function initialise()
	{
		$logger = new Logger('Joomla-Framework-Status');
		$logger->pushHandler(new StreamHandler(JPATH_ROOT . '/logs/cron.log'));

		$this->setLogger($logger);
	}

	/**
	 * Set the DI container
	 *
	 * @param   Container  $container  The DI container
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}
}
