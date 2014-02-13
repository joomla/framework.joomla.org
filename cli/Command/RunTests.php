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
 * CLI Command to run the package test suites and generate reports
 *
 * @since  1.0
 */
class RunTests
{
	/**
	 * Application object
	 *
	 * @var    Application
	 * @since  1.0
	 */
	private $app;

	/**
	 * Database driver object
	 *
	 * @var    \Joomla\Database\DatabaseDriver
	 * @since  1.0
	 */
	private $db;

	/**
	 * Package data from Composer
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $packages;

	/**
	 * Package ID for the currently parsing package
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $packageId;

	/**
	 * Class constructor
	 *
	 * @param   Application  $app      Application object
	 * @param   array        $package  Array of package data via Composer
	 *
	 * @since   1.0
	 */
	public function __construct(Application $app, array $packages)
	{
		$this->app = $app;
		$this->db  = $this->app->getContainer()->get('db');
		$this->packages = $packages;
	}

	/**
	 * Check if a report exists for a specified package and version
	 *
	 * @param   string  $package  The package being processed
	 *
	 * @return  mixed  Database result
	 *
	 * @since   1.0
	 */
	private function checkForReport($package)
	{
		return $this->db->setQuery(
			$this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__test_results'))
				->where($this->db->quoteName('package_id') . ' = ' . (int) $this->packageId)
		)->loadResult();
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
		// Use a DirectoryIterator object to loop over each package
		$iterator = new \DirectoryIterator(JPATH_ROOT . '/vendor/joomla');

		/* @type  $directory  \DirectoryIterator */
		foreach ($iterator as $directory)
		{
			if (!$directory->isDot() && $directory->isDir())
			{
				$this->app->out('Processing the ' . $directory->getFilename() . ' package.');

				// Get the package ID
				$this->getPackageId($directory->getFilename());

				// Check to see if this version of the package already has a report
				if ($this->checkForReport($directory->getFilename()))
				{
					$this->app->out(
						sprintf(
							'A test report already exists for the %s package at version %s.',
							$directory->getFilename(),
							$this->packages[$directory->getFilename()]['version']
						)
					);

					continue;
				}

				// Check if a test config exists for the package
				if (file_exists(JPATH_TESTS . '/phpunit.' . $directory->getFilename() . '.xml'))
				{
					$command = new \PHPUnit_TextUI_Command;

					$options = [
						'--configuration=' . JPATH_TESTS . '/phpunit.' . $directory->getFilename() . '.xml'
					];

					$command->run($options, false);

					$this->recordResults($directory->getFilename());
				}
				else
				{
					$this->app->out('No test config exists for the ' . $directory->getFilename() . ' package.');
				}
			}
		}
	}

	/**
	 * Fetches the package ID for a given package and version
	 *
	 * @param   string  $package  The package being processed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function getPackageId($package)
	{
		$this->packageId = $this->db->setQuery(
			$this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__packages'))
				->where($this->db->quoteName('package') . ' = ' . $this->db->quote($package))
				->where($this->db->quoteName('version') . ' = ' . $this->db->quote($this->packages[$package]['version']))
		)->loadResult();
	}

	private function recordResults($package)
	{
		// Initialize variables.
		$report = array(
			'loc'         => 0,
			'loc_covered' => 0
		);

		// Make sure the files exist.
		if (!file_exists(JPATH_ROOT . '/coverage/logs/clover.' . $package . '.xml'))
		{
			throw new \UnexpectedValueException('The clover test report for the ' . $package . ' package is missing.');
		}

		if (!file_exists(JPATH_ROOT . '/coverage/logs/junit.' . $package . '.xml'))
		{
			throw new \UnexpectedValueException('The junit test report for the ' . $package . ' package is missing.');
		}

		// Load the Clover XML file.
		$xml = simplexml_load_file(JPATH_ROOT . '/coverage/logs/clover.' . $package . '.xml');

		// Get the project metrics element.
		$metrics = $xml->project[0]->metrics[0];

		// Add the data to the report
		$report['total_lines']   = (int) $metrics['statements'];
		$report['lines_covered'] = (int) $metrics['coveredstatements'];

		// Load the JUnit XML file
		$xml = simplexml_load_file(JPATH_ROOT . '/coverage/logs/junit.' . $package . '.xml');

		// Get the project testsuite element.
		$metrics = $xml->testsuite[0];

		// Add the data to the report
		$report['assertions'] = (int) $metrics['assertions'];
		$report['tests']      = (int) $metrics['tests'];
		$report['failures']   = (int) $metrics['failures'];
		$report['errors']     = (int) $metrics['errors'];

		// Insert the report data into the database
		$this->db->setQuery(
			$this->db->getQuery(true)
				->insert($this->db->quoteName('#__test_results'))
				->columns(
					array(
						$this->db->quoteName('package_id'), $this->db->quoteName('tests'), $this->db->quoteName('assertions'),
						$this->db->quoteName('errors'), $this->db->quoteName('failures'), $this->db->quoteName('total_lines'),
						$this->db->quoteName('lines_covered')
					)
				)
				->values(
					(int) $this->packageId . ', ' . $this->db->quote($report['tests']) . ', ' . $this->db->quote($report['assertions'])
					. ', ' . $this->db->quote($report['errors']) . ', ' . $this->db->quote($report['failures'])
					. ', ' . $this->db->quote($report['total_lines']) . ', ' . $this->db->quote($report['lines_covered'])
				)
		)->execute();
	}
}
