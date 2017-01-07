<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatusCli\Command;

use Joomla\Status\Helper;
use Joomla\StatusCli\Application;

/**
 * CLI Command to parse the installed Composer packages and inject data into the database
 *
 * @since  1.0
 */
class ParseComposer
{
	/**
	 * Application object
	 *
	 * @var    Application
	 * @since  1.0
	 */
	private $app;

	/**
	 * Configuration data
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  1.0
	 */
	private $config;

	/**
	 * Database driver object
	 *
	 * @var    \Joomla\Database\DatabaseDriver
	 * @since  1.0
	 */
	private $db;

	/**
	 * Class constructor
	 *
	 * @param   Application  $app  Application object
	 *
	 * @since   1.0
	 */
	public function __construct(Application $app)
	{
		$this->app    = $app;
		$this->config = $this->app->getContainer()->get('config');
		$this->db     = $this->app->getContainer()->get('db');
	}

	/**
	 * Execute the command
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// Display status
		$this->app->out('<info>Parsing the Composer data.</info>');

		// Get the Composer data
		$packages = (new Helper)->parseComposer();

		// Insert the records into the database now
		foreach ($packages as $name => $package)
		{
			// Check to see if the package is already in the database
			$packageID = $this->db->setQuery(
				$this->db->getQuery(true)
					->select($this->db->quoteName('id'))
					->from($this->db->quoteName('#__packages'))
					->where($this->db->quoteName('package') . ' = ' . $this->db->quote($name))
					->where($this->db->quoteName('version') . ' = ' . $this->db->quote($package['version']))
			)->loadResult();

			// If not present, insert it
			if (!$packageID)
			{
				$this->db->setQuery(
					$this->db->getQuery(true)
						->insert($this->db->quoteName('#__packages'))
						->columns(array($this->db->quoteName('package'), $this->db->quoteName('version')))
						->values($this->db->quote($name) . ', ' . $this->db->quote($package['version']))
				)->execute();
			}
		}

		// Display status
		$this->app->out('<info>Finished parsing Composer data.</info>');
	}
}
