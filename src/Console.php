<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use Joomla\DI\{
	ContainerAwareInterface, ContainerAwareTrait
};

/**
 * Console command container
 *
 * @since  1.0
 */
class Console implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Array of available commands
	 *
	 * @var    string[]
	 * @since  1.0
	 */
	private $commands = [];

	/**
	 * Get the specified command
	 *
	 * @param   string  $command  The command to retrieve
	 *
	 * @return  CommandInterface
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function getCommand(string $command) : CommandInterface
	{
		// Make sure the commands are loaded
		$this->getCommands();

		if (!array_key_exists($command, $this->commands))
		{
			throw new \InvalidArgumentException(sprintf('The "%s" command is not valid.', $command));
		}

		return $this->getContainer()->get($this->commands[$command]);
	}

	/**
	 * Get the available commands.
	 *
	 * @return  string[]
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getCommands() : array
	{
		if (empty($this->commands))
		{
			$this->commands = $this->loadCommands();
		}

		return array_keys($this->commands);
	}

	/**
	 * Load the application's commands
	 *
	 * @return  string[]
	 *
	 * @since   1.0
	 */
	private function loadCommands() : array
	{
		$commands = [];

		/** @var \DirectoryIterator $fileInfo */
		foreach (new \DirectoryIterator(__DIR__ . '/Command') as $fileInfo)
		{
			if ($fileInfo->isDot())
			{
				continue;
			}

			if ($fileInfo->isDir())
			{
				$namespace = $fileInfo->getFilename();

				/** @var \DirectoryIterator $subFileInfo */
				foreach (new \DirectoryIterator($fileInfo->getPathname()) as $subFileInfo)
				{
					if ($subFileInfo->isDot() || !$subFileInfo->isFile())
					{
						continue;
					}

					$command   = $subFileInfo->getBasename('.php');
					$className = __NAMESPACE__ . "\\Command\\$namespace\\$command";

					if (!class_exists($className))
					{
						throw new \RuntimeException(sprintf('Required class "%s" not found.', $className));
					}

					// If the class isn't instantiable, it isn't a valid command
					if ((new \ReflectionClass($className))->isInstantiable())
					{
						$commands[strtolower("$namespace:" . str_replace('Command', '', $command))] = $className;
					}
				}
			}
			else
			{
				$command   = $fileInfo->getBasename('.php');
				$className = __NAMESPACE__ . "\\Command\\$command";

				if (!class_exists($className))
				{
					throw new \RuntimeException(sprintf('Required class "%s" not found.', $className));
				}

				// If the class isn't instantiable, it isn't a valid command
				if ((new \ReflectionClass($className))->isInstantiable())
				{
					$commands[strtolower(str_replace('Command', '', $command))] = $className;
				}
			}
		}

		return $commands;
	}
}
