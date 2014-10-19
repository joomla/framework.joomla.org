<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Unit\View\Status;

use Joomla\Status\View\Status\StatusJsonView;

/**
 * Test class for \Joomla\Status\View\Status\StatusJsonView
 */
class StatusJsonViewTest extends StatusTestCase
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

		$this->object = new StatusJsonView($this->mockModel);
	}

	/**
	 * @testdox  Ensure render() returns the data received from the renderer
	 *
	 * @covers   \Joomla\Status\View\Status\StatusJsonView::render
	 * @uses     \Joomla\Status\Helper
	 * @uses     \Joomla\Status\Model\StatusModel
	 * @uses     \Joomla\View\AbstractView
	 */
	public function testEnsureRenderReturnsTheDataReceivedFromTheRenderer()
	{
		$this->assertThat($this->object->render(), $this->isJson());
	}
}
