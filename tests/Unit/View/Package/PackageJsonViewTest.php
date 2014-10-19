<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Unit\View\Package;

use Joomla\Status\View\Package\PackageJsonView;

/**
 * Test class for \Joomla\Status\View\Package\PackageJsonView
 */
class PackageJsonViewTest extends PackageTestCase
{
	/**
	 * Test object
	 */
	private $object;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new PackageJsonView($this->mockModel);
	}

	/**
	 * @testdox  Ensure render() returns the data received from the renderer
	 *
	 * @covers   \Joomla\Status\View\Package\PackageJsonView::render
	 * @uses     \Joomla\Status\Helper
	 * @uses     \Joomla\Status\Model\PackageModel
	 * @uses     \Joomla\View\AbstractView
	 */
	public function testEnsureRenderReturnsTheDataReceivedFromTheRenderer()
	{
		$this->assertThat($this->object->render(), $this->isJson());
	}
}
