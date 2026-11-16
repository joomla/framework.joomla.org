<?php

/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2023 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

// All PHP code of the application, the tests directory is only checked once it exists
$directories = array_filter(
    [
        __DIR__ . '/bin',
        __DIR__ . '/etc/migrations',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/www',
    ],
    'is_dir'
);

$mainFinder = PhpCsFixer\Finder::create()
    ->in($directories)
    ->append(
        [
            __DIR__ . '/.php-cs-fixer.dist.php',
            __DIR__ . '/bin/framework',
            __DIR__ . '/phinx.php',
            __DIR__ . '/phpstan_constants.php',
        ]
    );

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setHideProgress(false)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setRules(
        [
            '@PSR12'                         => true,
            'array_syntax'                   => ['syntax' => 'short'],
            'no_trailing_comma_in_list_call' => true,
            'trailing_comma_in_multiline'    => ['elements' => ['arrays']],
            'binary_operator_spaces'         => ['operators' => ['=>' => 'align_single_space_minimal', '=' => 'align']],
        ]
    )
    ->setFinder($mainFinder);

return $config;
