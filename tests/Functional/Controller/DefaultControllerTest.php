<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Functional\Controller;

use Joomla\DI\Container;
use Joomla\Status\Controller\DefaultController;
use Joomla\Status\Tests\Mock\Application;
use Joomla\Status\Tests\Mock\Input;
use Joomla\Status\Tests\Mock\Service\DatabaseProvider;
use Joomla\Test\TestHelper;

/**
 * Test class for \Joomla\Status\Controller\DefaultController
 */
class DefaultControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test object
	 *
	 * @var  DefaultController
	 */
	private $object;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$mockInput       = (new Input)->create($this);
		$mockApplication = (new Application)->create($this);

		$container = (new Container)
			->registerServiceProvider(new DatabaseProvider($this));

		$this->object = (new DefaultController($mockInput, $mockApplication))->setContainer($container);
	}

	/**
	 * @testdox  Verify that the initialized model is registered to the DI container
	 *
	 * @covers   Joomla\Status\Controller\DefaultController::initializeModel
	 */
	public function testVerifyThatTheInitializedModelIsRegisteredToTheContainer()
	{
		TestHelper::invoke($this->object, 'initializeModel');

		$this->assertInstanceOf(
			'\\Joomla\\Status\\Model\\DefaultModel',
			$this->object->getContainer()->get('Joomla\\Model\\ModelInterface')
		);
	}
}
