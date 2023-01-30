<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2023 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

$mainFinder = PhpCsFixer\Finder::create()
	->in(
		[
			__DIR__ . '/bin',
			__DIR__ . '/src',
			__DIR__ . '/www',
		]
	);

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setHideProgress(false)
    ->setUsingCache(false)
    ->setRules(
        [
            '@PSR12'                         => true,
            'array_syntax'                   => ['syntax' => 'short'],
            'no_trailing_comma_in_list_call' => true,
            'trailing_comma_in_multiline'    => ['elements' => ['arrays']],
            'binary_operator_spaces'         => ['operators' => ['=>' => 'align_single_space_minimal', '=' => 'align']]
        ]
    )
	->setFinder($mainFinder);

return $config;
