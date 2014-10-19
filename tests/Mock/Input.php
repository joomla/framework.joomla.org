<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Status\Tests\Mock\BaseMock;

/**
 * Class to create a mock Joomla\Database\DatabaseDriver instance
 *
 * @since  1.0
 */
class Input extends BaseMock
{
	/**
	 * Array to hold mock get and set values.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $inputs;

	/**
	 * Creates an instance of the mock Input object.
	 *
	 * @param   \PHPUnit_Framework_TestCase  $test          A test object.
	 * @param   array|\ArrayAccess           $extraMethods  An array of additional methods to add to the mock.
	 *
	 * @return  \PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   1.0
	 */
	public function create($test, $extraMethods = array())
	{
		// Collect all the relevant methods in Input.
		$methods = array_merge($extraMethods, [
			'count',
			'def',
			'exists',
			'get',
			'getArray',
			'getInt',
			'getMethod',
			'loadAllInputs',
			'set',
			'serialize',
			'unserialize',
		]);

		// Create the mock.
		$mockObject = $test->getMock('\\Joomla\\Input\\Input', $methods, [], '', false);

		$this->assignMockCallbacks(
			$test,
			$mockObject,
			[
				'get' => [(is_callable([$this->test, 'mockInputGet']) ? $this->test : $this), 'mockInputGet'],
				'getArray' => [(is_callable([$this->test, 'mockInputGetArray']) ? $this->test : $this), 'mockInputGetArray'],
				'getInt' => [(is_callable([$this->test, 'mockInputGetInt']) ? $this->test : $this), 'mockInputGetInt'],
				'set' => [(is_callable([$this->test, 'mockInputSet']) ? $this->test : $this), 'mockInputSet'],
			]
		);

		$mockObject->get = $mockObject;
		$mockObject->post = $mockObject;
		$mockObject->request = $mockObject;

		return $mockObject;
	}

	/**
	 * Creates an instance of a mock Joomla\Input\Json object.
	 *
	 * @param   \PHPUnit_Framework_TestCase  $test  A test object.
	 *
	 * @return  \PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   1.0
	 */
	public function createJson()
	{
		$mockObject = $this->create($test, ['getRaw']);

		$this->assignMockCallbacks(
			$test,
			$mockObject,
			['getRaw' => [(is_callable([$this->test, 'mockInputGetRaw']) ? $this->test : $this), 'mockInputGetRaw']]
		);

		return $mockObject;
	}

	/**
	 * Callback for the Input object's get method.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function mockInputGet($name, $default = null, $filter = 'cmd')
	{
		return isset($this->inputs[$name]) ? $this->inputs[$name] : $default;
	}

	/**
	 * Callback for the Input object's getArray method.
	 *
	 * @param   array  $vars        Associative array of keys and filter types to apply.
	 *                              If empty and datasource is null, all the input data will be returned
	 *                              but filtered using the default case in JFilterInput::clean.
	 * @param   mixed  $datasource  Array to retrieve data from, or null
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function mockInputGetArray(array $vars = array(), $datasource = null)
	{
		return array();
	}

	/**
	 * Callback for the Input object's getInt method.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function mockInputGetInt($name, $default = null)
	{
		return (int) $this->mockInputGet($name, $default);
	}

	/**
	 * Callback for the Json object's getRaw method.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function mockInputGetRaw()
	{
		return '';
	}

	/**
	 * Callback for the Input object's set method.
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function mockInputSet($name, $value)
	{
		$this->inputs[$name] = $value;
	}
}
