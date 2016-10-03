<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Functional\Model;

use Joomla\Status\Model\StatusModel;
use Joomla\Status\Tests\Mock\DatabaseDriver;
use Joomla\Test\TestHelper;

/**
 * Test class for \Joomla\Status\Model\StatusModel
 */
class StatusModelTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Mock DatabaseDriver
	 *
	 * @var  \PHPUnit_Framework_MockObject_MockObject
	 */
	private $mockDbo;

	/**
	 * Test object
	 *
	 * @var  StatusModel
	 */
	private $object;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->mockDbo = (new DatabaseDriver)->create($this, 'mysqli');

		$this->object = new StatusModel($this->mockDbo);
	}
	/**
	 * @testdox  Verify that getItems() returns a data array with the latest packages
	 *
	 * @covers   \Joomla\Status\Model\StatusModel::getItems
	 * @uses     \Joomla\Status\Helper
	 */
	public function testVerifyThatGetItemsReturnsADataArrayWithLatestPackages()
	{
		$this->mockDbo->expects($this->any())
			->method('loadObjectList')
			->willReturnCallback([$this, 'mockLoadObjectListSimple']);

		$this->assertInternalType('array', $this->object->getItems());
	}

	/**
	 * @testdox  Verify that getItems() returns a data array with the latest packages and coverage changes noted
	 *
	 * @covers   \Joomla\Status\Model\StatusModel::getItems
	 * @uses     \Joomla\Status\Helper
	 */
	public function testVerifyThatGetItemsReturnsADataArrayWithLatestPackagesAndCoverageChangesNoted()
	{
		$this->mockDbo->expects($this->any())
			->method('loadObjectList')
			->willReturnCallback([$this, 'mockLoadObjectListComplex']);
		$this->mockDbo->expects($this->at(0))
			->method('loadObject')
			->willReturnCallback([$this, 'mockLoadObjectApplicationFirst']);
		$this->mockDbo->expects($this->at(1))
			->method('loadObject')
			->willReturnCallback([$this, 'mockLoadObjectApplicationSecond']);

		$this->assertInternalType('array', $this->object->getItems());
	}

	/**
	 * Mocks the DatabaseDriver's loadObject() method
	 *
	 * @return  array
	 */
	public static function mockLoadObjectApplicationFirst($class = 'stdClass')
	{
		$row = new \stdClass;
		$row->id = 1;
		$row->package_id = 1;
		$row->tests = 0;
		$row->assertions = 0;
		$row->errors = 0;
		$row->failures = 0;
		$row->total_lines = 0;
		$row->lines_covered = 0;
	}

	/**
	 * Mocks the DatabaseDriver's loadObjectList() method
	 *
	 * @return  array
	 */
	public static function mockLoadObjectListComplex()
	{
		$row1 = new \stdClass;
		$row1->id = 1;
		$row1->package = 'application';
		$row1->version = '1.0.0';

		$row2 = new \stdClass;
		$row2->id = 2;
		$row2->package = 'application';
		$row2->version = '1.0.1';

		$row3 = new \stdClass;
		$row3->id = 3;
		$row3->package = 'github';
		$row3->version = '1.0.0';

		$row4 = new \stdClass;
		$row4->id = 4;
		$row4->package = 'github';
		$row4->version = '1.0.1';

		return [$row1, $row2, $row3, $row4];
	}

	/**
	 * Mocks the DatabaseDriver's loadObjectList() method
	 *
	 * @return  array
	 */
	public static function mockLoadObjectListSimple()
	{
		$row1 = new \stdClass;
		$row1->id = 1;
		$row1->package = 'application';
		$row1->version = '1.0.0';

		$row2 = new \stdClass;
		$row2->id = 2;
		$row2->package = 'archive';
		$row2->version = '1.0.0';

		return [$row1, $row2];
	}
}
