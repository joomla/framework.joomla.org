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

// Wrap in a try/catch so we can display an error if need be
try
{
	$container = (new Joomla\DI\Container)
		->registerServiceProvider(new Joomla\Status\Service\ConfigurationProvider)
		->registerServiceProvider(new Joomla\Status\Service\DatabaseProvider);
}
catch (\Exception $e)
{
	header('HTTP/1.1 500 Internal Server Error', null, 500);
	fwrite(STDOUT, "An error occurred while creating the DI container: " . $e->getMessage() . "\n");

	exit(500);
}

// Execute the application
(new Joomla\StatusCli\Application)
	->setContainer($container)
	->execute();
