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

// Load the Composer autoloader
if (!file_exists(JPATH_ROOT . '/vendor/autoload.php'))
{
	header('HTTP/1.1 500 Internal Server Error', null, 500);
	echo '<html><head><title>Server Error</title></head><body><h1>Composer Not Installed</h1><p>Composer is not set up properly, please run "composer install".</p></body></html>';

	exit(500);
}

require JPATH_ROOT . '/vendor/autoload.php';

(new Joomla\Status\Application)
	->setContainer(new Joomla\DI\Container)
	->execute();
