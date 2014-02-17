<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
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
		$this->app->out('Updating server to git HEAD');

		// Pull from remote repo
		$this->app->runCommand('cd ' . JPATH_ROOT . ' && git pull 2>&1');

		$this->app->out('Updating Composer resources');

		// Run Composer update
		$this->app->runCommand('cd ' . JPATH_ROOT . ' && composer update 2>&1');

		// Write the current build to a local file
		$this->app->out('Writing build info');

		$path = JPATH_ROOT . '/current_SHA';

		// Generate the build information; compile branch and SHA data; TODO need a tag for describe to work
		//$info   = $this->app->runCommand('cd ' . JPATH_ROOT . ' && git describe --long --abbrev=10 --tags 2>&1');
		$branch = $this->app->runCommand('cd ' . JPATH_ROOT . ' && git rev-parse --abbrev-ref HEAD 2>&1');

		if (!file_put_contents($path, /*$info . ' ' . */$branch))
		{
			$this->app->out('Can not write to path: ', JPATH_ROOT);

			throw new \DomainException('Can not write to path: ' . $path);
		}

		$this->app->out('Update Finished');
	}
}
