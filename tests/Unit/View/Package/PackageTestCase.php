<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Unit\View\Package;

use Joomla\Status\Tests\Mock\DatabaseDriver;

/**
 * Abstract test case for the Package view classes
 */
abstract class PackageTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Mock PackageModel
	 */
	protected $mockModel;

	/**
	 * Mock RendererInterface
	 */
	protected $mockRenderer;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$mockRegistry = $this->getMock('\\Joomla\\Registry\\Registry', ['get']);
		$mockRegistry->expects($this->any())
			->method('get')
			->with('package.name')
			->willReturn('application');

		$mockDbo = (new DatabaseDriver)->create($this, 'mysqli');

		$this->mockModel = $this->getMock(
			'\\Joomla\\Status\\Model\\PackageModel',
			['getState', 'getItems'],
			[$mockDbo, $mockRegistry]
		);
		$this->mockModel->expects($this->any())
			->method('getState')
			->willReturn($mockRegistry);
		$this->mockModel->expects($this->any())
			->method('getItems')
			->willReturn(array());

		$this->mockRenderer = $this->getMock('\\Joomla\\Renderer\\RendererInterface');
	}
}
