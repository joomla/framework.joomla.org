<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Mock\Service;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Status\Tests\Mock\DatabaseDriver;

/**
 * Database service provider
 *
 * @since  1.0
 */
class DatabaseProvider implements ServiceProviderInterface
{
	/**
	 * @var    \PHPUnit_Framework_TestCase
	 * @since  1.0
	 */
	private $test;

	/**
	 * Constructor
	 *
	 * @param   \PHPUnit_Framework_TestCase  $test  A test object.
	 */
	public function __construct(\PHPUnit_Framework_TestCase $test)
	{
		$this->test = $test;
	}

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Container  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function register(Container $container)
	{
		$container->set('Joomla\\Database\\DatabaseDriver',
			function ()
			{
				return (new DatabaseDriver)->create($this->test, 'mysqli');
			}, true, true
		);

		// Alias the database
		$container->alias('db', 'Joomla\\Database\\DatabaseDriver');
	}
}
