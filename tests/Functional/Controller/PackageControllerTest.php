<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Functional\Controller;

use Joomla\DI\Container;
use Joomla\Status\Controller\PackageController;
use Joomla\Status\Tests\Mock\Application;
use Joomla\Status\Tests\Mock\Input;
use Joomla\Status\Tests\Mock\Service\DatabaseProvider;
use Joomla\Test\TestHelper;

/**
 * Test class for \Joomla\Status\Controller\PackageController
 */
class PackageControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test object
	 *
	 * @var  PackageController
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

		$this->object = (new PackageController($mockInput, $mockApplication))->setContainer($container);
	}

	/**
	 * @testdox  Verify that the initialized model is registered to the DI container
	 *
	 * @covers   Joomla\Status\Controller\PackageController::initializeModel
	 * @uses     Joomla\Status\Controller\DefaultController::initializeModel
	 */
	public function testVerifyThatTheInitializedModelIsRegisteredToTheContainer()
	{
		TestHelper::invoke($this->object, 'initializeModel');

		$this->assertInstanceOf(
			'\\Joomla\\Status\\Model\\PackageModel',
			$this->object->getContainer()->get('Joomla\\Model\\ModelInterface')
		);
	}
}
