<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Unit\Renderer;

use Joomla\Status\Renderer\TwigExtension;

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

		$this->object = new TwigExtension;
	}

	/**
	 * @testdox  Verify that getFunctions() returns an array containing only Twig_SimpleFunction instances
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::getFunctions
	 */
	public function testVerifyThatGetFunctionsReturnsAnArrayOfTwigSimpleFunctionInstances()
	{
		$this->assertContainsOnlyInstancesOf('\\Twig_SimpleFunction', $this->object->getFunctions());
	}

	/**
	 * @testdox  Verify that getFilters() returns an array containing only Twig_SimpleFilter instances
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::getFilters
	 */
	public function testVerifyThatGetFiltersReturnsAnArrayOfTwigSimpleFilterInstances()
	{
		$this->assertContainsOnlyInstancesOf('\\Twig_SimpleFilter', $this->object->getFilters());
	}

	/**
	 * @testdox  Verify that stripJRoot() replaces the JPATH_ROOT constant
	 *
	 * @covers   Joomla\Status\Renderer\TwigExtension::stripJRoot
	 */
	public function testVerifyThatStripJrootReplacesTheRootConstant()
	{
		$this->assertSame('APP_ROOT/tests', $this->object->stripJRoot(JPATH_ROOT . '/tests'));
	}
}
