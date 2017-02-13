<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\Database\DatabaseDriver;
use Joomla\FrameworkWebsite\CommandInterface;
use Joomla\Input\Input;

/**
 * Install command
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class InstallCommand extends AbstractController implements CommandInterface
{
	/**
	 * Database driver.
	 *
	 * @var    DatabaseDriver
	 * @since  1.0
	 */
	private $db = null;

	/**
	 * Instantiate the controller.
	 *
	 * @param   DatabaseDriver       $db     Database driver.
	 * @param   Input                $input  The input object.
	 * @param   AbstractApplication  $app    The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->db = $db;
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
		$this->getApplication()->outputTitle('Installer');

		try
		{
			// Check if the database "exists"
			$tables = $this->db->getTableList();

			if (!$this->getInput()->getBool('reinstall', false))
			{
				$this->getApplication()->out()
					->out('<fg=black;bg=yellow>WARNING: A database has been found!!</fg=black;bg=yellow>')
					->out()
					->out('Do you want to reinstall?')
					->out()
					->out('1) Yes')
					->out('2) No')
					->out()
					->out('<question>Select:</question>', false);

				$in = trim($this->getApplication()->in());

				if ((int) $in !== 1)
				{
					$this->getApplication()->out('<info>Aborting installation.</info>');

					return true;
				}
			}

			$this->cleanDatabase($tables);
		}
		catch (\RuntimeException $e)
		{
			// Check if the message is "Could not connect to database."  Odds are, this means the DB isn't there or the server is down.
			if (strpos($e->getMessage(), 'Could not connect to database.') === false)
			{
				throw $e;
			}

			$this->getApplication()->out('No database found.')
				->out('Creating the database...', false);

			$this->db->setQuery('CREATE DATABASE ' . $this->db->quoteName($this->getApplication()->get('database.name')))
				->execute();

			$this->db->select($this->getApplication()->get('database.name'));

			$this->getApplication()->out('<info>Database created.</info>');
		}

		// Perform the installation
		$this->processSql();

		$this->getApplication()->out()
			->out('<fg=green;options=bold>Installation has been completed successfully.</fg=green;options=bold>');

		return true;
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
	private function cleanDatabase(array $tables) : InstallCommand
	{
		$this->getApplication()->out('Removing existing tables...', false);

		// Foreign key constraint fails fix
		$this->db->setQuery('SET FOREIGN_KEY_CHECKS=0')
			->execute();

		foreach ($tables as $table)
		{
			$this->db->dropTable($table, true);
			$this->getApplication()->out('.', false);
		}

		$this->db->setQuery('SET FOREIGN_KEY_CHECKS=1')
			->execute();

		$this->getApplication()->out('<info>Tables removed.</info>');

		return $this;
	}

	/**
	 * Process the main SQL file.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	private function processSql() : InstallCommand
	{
		$fName = JPATH_ROOT . '/etc/schema.sql';

		if (!file_exists($fName))
		{
			throw new \RuntimeException('Install SQL file for MySQL not found.');
		}

		$sql = file_get_contents($fName);

		if (!$sql)
		{
			throw new \RuntimeException('SQL file corrupted.');
		}

		$this->getApplication()->out(sprintf('Creating tables from file %s', realpath($fName)), false);

		foreach ($this->db->splitSql($sql) as $query)
		{
			$q = trim($this->db->replacePrefix($query));

			if (trim($q) === '')
			{
				continue;
			}

			$this->db->setQuery($q)
				->execute();

			$this->getApplication()->out('.', false);
		}

		$this->getApplication()->out('<info>Database tables created successfully.</info>');

		return $this;
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
		return 'Installs the application.';
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
		return 'Install Application';
	}
}
