<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

require \dirname(__DIR__) . '/vendor/autoload.php';

/*
 * The application resolves its data directories (e.g. `docs/`) relative to JPATH_ROOT. The tests use a temporary
 * directory instead, so they never touch the files of a local installation.
 */
$testRoot = sys_get_temp_dir() . '/framework-website-tests-' . getmypid();

if (!is_dir($testRoot) && !mkdir($testRoot, 0777, true)) {
    throw new RuntimeException(sprintf('Could not create the test root directory `%s`.', $testRoot));
}

// The tests simulate web requests, the request method is read from the server environment
$_SERVER['REQUEST_METHOD'] = 'GET';

define('JPATH_ROOT', $testRoot);
define('JPATH_TEMPLATES', \dirname(__DIR__) . '/templates');

register_shutdown_function(
    static function () use ($testRoot): void {
        if (!is_dir($testRoot)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($testRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($testRoot);
    }
);
