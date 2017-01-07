<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatusCli\Command;

use Joomla\StatusCli\Application;

/**
 * CLI command for synchronizing a server with the primary git repository
 *
 * @since  1.0
 */
class UpdateServer
{
	/**
	 * Application object
	 *
	 * @var    Application
	 * @since  1.0
	 */
	private $app;

	/**
	 * Class constructor
	 *
	 * @param   Application  $app  Application object
	 *
	 * @since   1.0
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Execute the command.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \DomainException
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		$this->app->out('<info>Updating server to git HEAD</info>');

		// Pull from remote repo
		$this->app->runCommand('cd ' . JPATH_ROOT . ' && git pull 2>&1');

		$this->app->out('<info>Updating Composer resources</info>');

		// Run Composer install
		$this->app->runCommand('cd ' . JPATH_ROOT . ' && composer install --no-dev -o 2>&1');

		// Clear Twig cache
		(new ResetTwigCache($this->app))->execute();

		// Write the current build to a local file
		$this->app->out('<info>Writing build info</info>');

		$path = JPATH_ROOT . '/current_SHA';

		// Get the build information
		$sha = trim($this->app->runCommand('cd ' . JPATH_ROOT . ' && git rev-parse --short HEAD 2>&1'));

		if (!file_put_contents($path, $sha))
		{
			$this->app->out('<error>Can not write to path: ' . JPATH_ROOT . '</error>');

			throw new \DomainException('Can not write to path: ' . $path);
		}

		$this->app->out('Update Finished');
	}
}
