<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatusCli\Command;

use Joomla\StatusCli\Application;
use Joomla\StatusCli\Exception\AbortException;

/**
 * Class to install the application.
 *
 * @since  1.0
 */
class Install
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
	 * Execute the command.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  AbortException
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	public function execute()
	{
		try
		{
			// Check if the database "exists"
			$tables = $this->db->getTableList();

			if (!$this->app->input->get('reinstall'))
			{
				$this->app->out('<fg=black;bg=yellow>WARNING: A database has been found !!</fg=black;bg=yellow>')
					->out('Do you want to reinstall ? [y]es / [[n]]o :', false);

				$in = trim($this->app->in());

				if (!in_array($in, ['yes', 'y']))
				{
					throw new AbortException;
				}
			}

			$this->cleanDatabase($tables);

			$this->app->out("\nFinished!");
		}
		catch (\RuntimeException $e)
		{
			// Check if the message is "Could not connect to database."  Odds are, this means the DB isn't there or the server is down.
			if (strpos($e->getMessage(), 'Could not connect to database.') !== false)
			{
				// ? really..
				$this->app->out('No database found.')->out('Creating the database...', false);

				$this->db->setQuery('CREATE DATABASE ' . $this->db->quoteName($this->config->get('database.name')))->execute();

				$this->db->select($this->config->get('database.name'));

				$this->app->out("\nFinished!");
			}
			else
			{
				throw $e;
			}
		}

		// Perform the installation
		$this->processSql()->out('<ok>Installer has terminated successfully.</ok>');
	}

	/**
	 * Cleanup the database.
	 *
	 * @param   array  $tables  Tables to remove.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	private function cleanDatabase(array $tables)
	{
		$this->app->out('Removing existing tables...', false);

		// Foreign key constraint fails fix
		$this->db->setQuery('SET FOREIGN_KEY_CHECKS=0')->execute();

		foreach ($tables as $table)
		{
			if ('sqlite_sequence' == $table)
			{
				continue;
			}

			$this->db->setQuery('DROP TABLE IF EXISTS ' . $table)->execute();
			$this->app->out('.', false);
		}

		$this->db->setQuery('SET FOREIGN_KEY_CHECKS=1')->execute();

		return $this;
	}

	/**
	 * Process the main SQL file.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	private function processSql()
	{
		$fName = JPATH_ROOT . '/etc/schema.sql';

		if (!file_exists($fName))
		{
			throw new \UnexpectedValueException('Install SQL file not found.');
		}

		$sql = file_get_contents($fName);

		if (!$sql)
		{
			throw new \UnexpectedValueException('Unable to read SQL file.');
		}

		$this->app->out(sprintf('Creating tables from file %s', realpath($fName)), false);

		foreach ($this->db->splitSql($sql) as $query)
		{
			$q = trim($this->db->replacePrefix($query));

			if (trim($q) == '')
			{
				continue;
			}

			$this->db->setQuery($q)->execute();

			$this->app->out('.', false);
		}

		$this->app->out("\nFinished!");

		return $this;
	}
}
