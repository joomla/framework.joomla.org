<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Joomla\Application\AbstractApplication;
use Joomla\Application\Cli\ColorStyle;
use Joomla\Application\Cli\Output\Processor\ColorProcessor;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\{
	CommandInterface, Console
};
use Joomla\Input\Input;

/**
 * Help command
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class HelpCommand extends AbstractController implements CommandInterface
{
	/**
	 * The application's console object
	 *
	 * @var    Console
	 * @since  1.0
	 */
	private $console;

	/**
	 * Instantiate the controller.
	 *
	 * @param   Console              $console  The application's console object
	 * @param   Input                $input    The input object.
	 * @param   AbstractApplication  $app      The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(Console $console, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->console = $console;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		/** @var ColorProcessor $processor */
		$processor = $this->getApplication()->getOutput()->getProcessor();
		$processor->addStyle('cmd', new ColorStyle('magenta'));

		$executable = $this->getApplication()->input->executable;

		$commands = $this->getApplication()->getConsole()->getCommands();

		$this->getApplication()->outputTitle($this->getTitle());

		$this->getApplication()->out(
			sprintf('Usage: <info>%s</info> <cmd><command></cmd>',
				$executable
			)
		);

		$this->getApplication()->out()
			->out('Available commands:')
			->out();

		foreach ($this->getApplication()->getConsole()->getCommands() as $commandName)
		{
			$command = $this->console->getCommand($commandName);

			$this->getApplication()->out('<cmd>' . $commandName . '</cmd>');

			if ($command->getDescription())
			{
				$this->getApplication()->out('    ' . $command->getDescription());
			}

			$this->getApplication()->out();
		}

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
		return 'Provides basic use information for the website application.';
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
		return 'Joomla! Framework Website';
	}
}
