<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

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

// Execute the application
try
{
	(new Joomla\StatusCli\Application)->execute();
}
catch (\Exception $e)
{
	fwrite(STDOUT, "\nERROR: " . $e->getMessage() . "\n");
	fwrite(STDOUT, "\n" . $e->getTraceAsString() . "\n");

	exit($e->getCode() ? : 255);
}
