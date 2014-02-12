<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

// Set error reporting for development - @TODO Move to config in app startup
error_reporting(-1);

// Application constants
define('JPATH_ROOT',      dirname(__DIR__));
define('JPATH_TEMPLATES', JPATH_ROOT . '/templates');
define('JPATH_TESTS',     JPATH_ROOT . '/testconfigs');

// Load the Composer autoloader
if (!file_exists(JPATH_ROOT . '/vendor/autoload.php'))
{
	fwrite(STDOUT, "Composer is not set up properly, please run 'composer install'.\n");

	exit(500);
}

require JPATH_ROOT . '/vendor/autoload.php';

runTests();

function runTests()
{
	// Use a DirectoryIterator object to loop over each package
	$iterator = new DirectoryIterator(JPATH_ROOT . '/vendor/joomla');

	/* @type  $directory  DirectoryIterator */
	foreach ($iterator as $directory)
	{
		if (!$directory->isDot() && $directory->isDir())
		{
			fwrite(STDOUT, 'Processing the ' . $directory->getFilename() . " package.\n");

			// Check if a test config exists for the package
			if (file_exists(JPATH_TESTS . '/phpunit.' . $directory->getFilename() . '.xml'))
			{
				$command = new PHPUnit_TextUI_Command;

				$options = [
					'--configuration=' . JPATH_TESTS . '/phpunit.' . $directory->getFilename() . '.xml'
				];

				$returnVal = $command->run($options, false);
			}
			else
			{
				fwrite(STDOUT, 'No test config exists for the ' . $directory->getFilename() . " package.\n");
			}
		}
	}
}
