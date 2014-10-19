<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Unit\View\Status;

use Joomla\Status\View\Status\StatusHtmlView;

/**
 * Test class for \Joomla\Status\View\Status\StatusHtmlView
 */
class StatusHtmlViewTest extends StatusTestCase
{
	/**
	 * Test object
	 *
	 * @var  StatusHtmlView
	 */
	private $object;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new StatusHtmlView($this->mockModel, $this->mockRenderer);
	}

	/**
	 * @testdox  Ensure render() returns the data received from the renderer
	 *
	 * @covers   \Joomla\Status\View\Status\StatusHtmlView::render
	 * @uses     \Joomla\Status\Helper
	 * @uses     \Joomla\Status\Model\StatusModel
	 * @uses     \Joomla\Status\View\AbstractHtmlView
	 */
	public function testEnsureRenderReturnsTheDataReceivedFromTheRenderer()
	{
		$this->object->setLayout('layout');

		$this->assertNull($this->object->render());
	}
}
