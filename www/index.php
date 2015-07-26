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

// Ensure we've initialized Composer
if (!file_exists(JPATH_ROOT . '/vendor/autoload.php'))
{
	header('HTTP/1.1 500 Internal Server Error', null, 500);
	echo '<html><head><title>Server Error</title></head><body><h1>Composer Not Installed</h1><p>Composer is not set up properly, please run "composer install".</p></body></html>';

	exit(500);
}

require JPATH_ROOT . '/vendor/autoload.php';

// Wrap in a try/catch so we can display an error if need be
try
{
	$container = (new Joomla\DI\Container)
		->registerServiceProvider(new Joomla\Status\Service\ConfigurationProvider)
		->registerServiceProvider(new Joomla\Status\Service\DatabaseProvider);

	// Set error reporting based on config
	$errorReporting = (int) $container->get('config')->get('errorReporting', 0);
	error_reporting($errorReporting);
}
catch (\Exception $e)
{
	header('HTTP/1.1 500 Internal Server Error', null, 500);
	echo '<html><head><title>Container Initialization Error</title></head><body><h1>Container Initialization Error</h1><p>An error occurred while creating the DI container: ' . $e->getMessage() . '</p></body></html>';

	exit(500);
}

// Execute the application
(new Joomla\Status\Application(null, $container->get('config')))
	->setContainer($container)
	->execute();
