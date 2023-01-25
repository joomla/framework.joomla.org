<?php

$mainFinder = PhpCsFixer\Finder::create()
	->in(
		[
			__DIR__ . '/bin',
			__DIR__ . '/src',
			__DIR__ . '/www',
		]
	);

$config = new PhpCsFixer\Config();
return $config->setRules(
		[
		'@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
		]
	)
	->setRiskyAllowed(true)
	->setFinder($mainFinder);
