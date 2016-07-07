<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Mock;

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
	private static $inputs;

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
		$mockObject = $test->getMockBuilder(\Joomla\Input\Input::class)
			->setMethods($methods)
			->disableOriginalConstructor()
			->getMock();

		$this->assignMockCallbacks(
			$test,
			$mockObject,
			[
				'get' => [(is_callable([$test, 'mockInputGet']) ? $test : get_called_class()), 'mockInputGet'],
				'getArray' => [(is_callable([$test, 'mockInputGetArray']) ? $test : get_called_class()), 'mockInputGetArray'],
				'getInt' => [(is_callable([$test, 'mockInputGetInt']) ? $test : get_called_class()), 'mockInputGetInt'],
				'set' => [(is_callable([$test, 'mockInputSet']) ? $test : get_called_class()), 'mockInputSet'],
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
			['getRaw' => [(is_callable([$test, 'mockInputGetRaw']) ? $test : $this), 'mockInputGetRaw']]
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
	public static function mockInputGet($name, $default = null, $filter = 'cmd')
	{
		return isset(self::$inputs[$name]) ? self::$inputs[$name] : $default;
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
	public static function mockInputGetArray(array $vars = array(), $datasource = null)
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
	public static function mockInputGetInt($name, $default = null)
	{
		return (int) self::mockInputGet($name, $default);
	}

	/**
	 * Callback for the Json object's getRaw method.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public static function mockInputGetRaw()
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
	public static function mockInputSet($name, $value)
	{
		self::$inputs[$name] = $value;
	}
}
