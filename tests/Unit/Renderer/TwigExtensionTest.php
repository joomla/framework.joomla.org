<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Unit\Renderer;

use Joomla\Status\Renderer\TwigExtension;
use Joomla\Status\Tests\Mock\Application;

/**
 * Test class for \Joomla\Status\Renderer\TwigExtension
 */
class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test object
	 *
	 * @var  TwigExtension
	 */
	private $object;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$mockApplication = (new Application)->create($this);

		$this->object = new TwigExtension($mockApplication);
	}

	/**
	 * @testdox  Verify that TwigExtension is instantiated correctly
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::__construct
	 */
	public function testVerifyThatClassIsInstantiatedCorrectly()
	{
		$object = new TwigExtension((new Application)->create($this));

		$this->assertAttributeInstanceOf('Joomla\\Status\\Application', 'app', $object);
	}

	/**
	 * @testdox  Verify the return from getName()
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::getName
	 * @uses     Joomla\Status\Renderer\TwigExtension::__construct
	 */
	public function testVerifyTheReturnFromGetName()
	{
		$this->assertSame('joomla-framework-status', $this->object->getName());
	}

	/**
	 * @testdox  Verify the return from getGlobals()
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::getGlobals
	 * @uses     Joomla\Status\Renderer\TwigExtension::__construct
	 */
	public function testVerifyTheReturnFromGetGlobals()
	{
		$this->assertArrayHasKey('uri', $this->object->getGlobals());
	}

	/**
	 * @testdox  Verify that getFunctions() returns an array containing only Twig_SimpleFunction instances
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::getFunctions
	 * @uses     Joomla\Status\Renderer\TwigExtension::__construct
	 */
	public function testVerifyThatGetFunctionsReturnsAnArrayOfTwigSimpleFunctionInstances()
	{
		$this->assertContainsOnlyInstancesOf('\\Twig_SimpleFunction', $this->object->getFunctions());
	}

	/**
	 * @testdox  Verify that getFilters() returns an array containing only Twig_SimpleFilter instances
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::getFilters
	 * @uses     Joomla\Status\Renderer\TwigExtension::__construct
	 */
	public function testVerifyThatGetFiltersReturnsAnArrayOfTwigSimpleFilterInstances()
	{
		$this->assertContainsOnlyInstancesOf('\\Twig_SimpleFilter', $this->object->getFilters());
	}

	/**
	 * @testdox  Verify that stripJRoot() replaces the JPATH_ROOT constant
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::stripJRoot
	 * @uses     Joomla\Status\Renderer\TwigExtension::__construct
	 */
	public function testVerifyThatStripJrootReplacesTheRootConstant()
	{
		$this->assertSame('APP_ROOT/tests', $this->object->stripJRoot(JPATH_ROOT . '/tests'));
	}
}
