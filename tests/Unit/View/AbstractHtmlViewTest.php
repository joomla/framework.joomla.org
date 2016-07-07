<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Unit\View;

/**
 * Test class for \Joomla\Status\View\AbstractHtmlView
 */
class AbstractHtmlViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Mock ModelInterface
	 */
	private $mockModel;

	/**
	 * Mock RendererInterface
	 */
	private $mockRenderer;

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

		$this->mockModel    = $this->createMock('\\Joomla\\Model\\ModelInterface');
		$this->mockRenderer = $this->createMock('\\Joomla\\Renderer\\RendererInterface');
		$this->object       = $this->getMockForAbstractClass(
			'\\Joomla\\Status\\View\\AbstractHtmlView',
			[$this->mockModel, $this->mockRenderer]
		);
	}

	/**
	 * @testdox  Ensure the constructor sets the values correctly
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 */
	public function testEnsureTheConstructorSetsTheValuesCorrectly()
	{
		$this->assertAttributeSame($this->mockRenderer, 'renderer', $this->object);
	}

	/**
	 * @testdox  Ensure getData() returns an array
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::getData
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 */
	public function testEnsureGetDataReturnsAnArray()
	{
		$this->assertSame(array(), $this->object->getData());
	}

	/**
	 * @testdox  Ensure getLayout() throws an exception with no layout set
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::getLayout
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 *
	 * @expectedException  \RuntimeException
	 */
	public function testEnsureGetLayoutThrowsAnExceptionWithNoLayoutSet()
	{
		$this->object->getLayout();
	}

	/**
	 * @testdox  Ensure getLayout() returns the correct layout
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::getLayout
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setLayout
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 */
	public function testEnsureGetLayoutReturnsTheCorrectLayout()
	{
		$this->object->setLayout('default');
		$this->assertSame('default', $this->object->getLayout());
	}

	/**
	 * @testdox  Ensure getRenderer() returns the correct object
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::getRenderer
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 */
	public function testEnsureGetRendererReturnsTheCorrectObject()
	{
		$this->assertSame($this->mockRenderer, $this->object->getRenderer());
	}

	/**
	 * @testdox  Ensure getRenderer() throws an exception with no layout set
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::getRenderer
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 *
	 * @expectedException  \RuntimeException
	 */
	public function testEnsureGetRendererThrowsAnExceptionWithNoRendererSet()
	{
		$object = $this->getMockForAbstractClass(
			'\\Joomla\\Status\\View\\AbstractHtmlView',
			[$this->mockModel, $this->mockRenderer],
			'',
			false
		);

		$object->getRenderer();
	}

	/**
	 * @testdox  Ensure render() returns the data received from the renderer
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::render
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::getData
	 * @uses     \Joomla\Status\View\AbstractHtmlView::getLayout
	 * @uses     \Joomla\Status\View\AbstractHtmlView::getRenderer
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setLayout
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 */
	public function testEnsureRenderReturnsTheDataReceivedFromTheRenderer()
	{
		$this->object->setLayout('layout');

		$this->assertNull($this->object->render());
	}

	/**
	 * @testdox  Ensure setData() returns an instance of $this
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::setData
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 */
	public function testEnsureSetDataReturnsAnInstanceOfThisObject()
	{
		$this->assertSame($this->object, $this->object->setData(array()));
	}

	/**
	 * @testdox  Ensure setLayout() returns an instance of $this
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::setLayout
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 * @uses     \Joomla\Status\View\AbstractHtmlView::setRenderer
	 */
	public function testEnsureSetLayoutReturnsAnInstanceOfThisObject()
	{
		$this->assertSame($this->object, $this->object->setLayout('layout'));
	}

	/**
	 * @testdox  Ensure setRenderer() returns an instance of $this
	 *
	 * @covers   \Joomla\Status\View\AbstractHtmlView::setRenderer
	 * @uses     \Joomla\Status\View\AbstractHtmlView::__construct
	 */
	public function testEnsureSetRendererReturnsAnInstanceOfThisObject()
	{
		$this->assertSame($this->object, $this->object->setRenderer($this->mockRenderer));
	}
}
